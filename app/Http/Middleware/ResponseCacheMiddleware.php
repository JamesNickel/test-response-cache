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
        $cacheKeyCacheSize = 'response_cache_response:max_routes';

        // Increase api call count for a specified route
        $callCount = Cache::get($cacheKeyCallCount) ?? 0;
        $callCount++;
        Cache::put($cacheKeyCallCount, $callCount);

        // Increase total cache count
        $maxRoutes = Cache::get($cacheKeyCacheSize) ?? 0;
        $maxRoutes++;
        Cache::put($cacheKeyCacheSize, $maxRoutes);

        if($maxRoutes > config('responsecaching.response_cache.max_routes')){
            // Remove the oldest route
        }

        // Check if api call threshold is passed
        if($callCount > config('responsecaching.response_cache.threshold')){
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

            return response($cachedResponse['content'], $cachedResponse['status'])
                ->withHeaders($cachedResponse['headers']);
        }
        else{
            // Cache is not necessary
            return $next($request);
        }

        //************************************************ //

        /*
        // Increment call count
        Redis::zincrby('api_call_counts', 1, $routeName);

        $cachedRoutes = Cache::get('cached_routes', []);

        if (!in_array($routeName, $cachedRoutes)) {
            return $next($request);
        }


        // Check cache hit
        $cachedResponse = Cache::get($cacheKeyResponse);
        if ($cachedResponse) {
            return response($cachedResponse['content'], $cachedResponse['status'])
                ->withHeaders($cachedResponse['headers']);
        }

        // Proceed to controller, get response
        $response = $next($request);

        // Cache if successful (2xx)
        if ($response->isSuccessful()) {
            $ttl = config('caching.response_cache.ttl_minutes') * 60;
            $maxEntries = config('caching.response_cache.max_entries_per_route');

            // Store response data
            Cache::put($cacheKeyResponse, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], $ttl);

            // Track entries per route (use Redis list for LRU eviction)
            $routeListKey = 'response_cache_list:' . $routeName;
            Redis::rpush($routeListKey, $cacheKeyResponse); // Add to end (most recent)
            if (Redis::llen($routeListKey) > $maxEntries) {
                $oldKey = Redis::lpop($routeListKey); // Remove oldest
                Cache::forget($oldKey);
            }
        }

        return $response;
        */
    }
}
