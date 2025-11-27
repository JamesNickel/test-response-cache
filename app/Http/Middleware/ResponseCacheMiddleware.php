<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class ResponseCacheMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        //$routeName = $request->route()?->getName() ?? $request->path();
        $cacheKeyCallCount = 'response_cache_call_count:' . md5($request->fullUrl());
        $cacheKeyResponse = 'response_cache_response:' . md5($request->fullUrl());
        $cacheKeyCachedRoutes = 'response_cache_response:cached_routes';

        // Increase api call count for a specified route
        $isApiCacheable = $this->isApiCacheable($cacheKeyCallCount);

        if($isApiCacheable){
            // Fetch from cache if exists
            $cachedResponse = Cache::get($cacheKeyResponse);
            if(empty($cachedResponse)){

                // Create api response
                $response = $next($request);

                if ($response->isSuccessful()) {
                    $cachedResponse = [
                        'content' => $response->getContent(),
                        'status' => $response->getStatusCode(),
                        'headers' => $response->headers->all(),
                    ];
                }
                else{
                    return $next($request);
                }

            }
            $cachedResponse['last_called_at'] = now()->timestamp;

            $ttl = config('responsecaching.response_cache.ttl_minutes') * 60;

            // Cache the response
            Cache::put($cacheKeyResponse, $cachedResponse, $ttl);
            $this->removeOldCachesIfNecessary($cacheKeyCachedRoutes, $cacheKeyResponse);

            return response($cachedResponse['content'], $cachedResponse['status'])
                ->withHeaders($cachedResponse['headers']);
        }

        return $next($request);
    }

    private function isApiCacheable($cacheKeyCallCount): bool
    {

        $callCount = Cache::get($cacheKeyCallCount) ?? 0;
        $callCount++;
        Cache::put($cacheKeyCallCount, $callCount);

        return $callCount >= config('responsecaching.response_cache.threshold');
    }

    private function resetApiCallCount($cacheKeyResponse): void
    {
        $cacheKeyCallCount = str_replace('response_cache_response', 'response_cache_call_count', $cacheKeyResponse);
        Cache::put($cacheKeyCallCount, 0);
    }

    private function removeOldCachesIfNecessary($cacheKeyCachedRoutes, $cacheKeyResponse): void
    {

        $cachedRoutes = Cache::get($cacheKeyCachedRoutes) ?? [];
        if(!in_array($cacheKeyResponse, $cachedRoutes)){
            $cachedRoutes[] = $cacheKeyResponse;
        }

        if(count($cachedRoutes) > config('responsecaching.response_cache.max_routes')){
            // One route MUST be removed from the cache
            $oldestTime = now()->timestamp;
            $oldestIndex = 0;
            foreach($cachedRoutes as $index => $routeKey){
                //dump('Getting cache: ' . $routeKey);
                $cachedResponse = Cache::get($routeKey);
                if($cachedResponse['last_called_at'] < $oldestTime){
                    $oldestTime = $cachedResponse['last_called_at'];
                    $oldestIndex = $index;
                }
            }

            Cache::forget($cachedRoutes[$oldestIndex]);
            $this->resetApiCallCount($cachedRoutes[$oldestIndex]);
            unset($cachedRoutes[$oldestIndex]);
        }
        Cache::put($cacheKeyCachedRoutes, $cachedRoutes);
    }
}
