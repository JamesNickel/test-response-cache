<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class UpdateCachedRoutes extends Command
{
    protected $signature = 'cache:update-routes';
    protected $description = 'Update list of routes to cache based on call counts';

    public function handle()
    {
        $threshold = config('caching.response_cache.threshold');
        $maxRoutes = config('caching.response_cache.max_routes');

        // Get top routes from Redis sorted set (descending order)
        $topRoutes = Redis::zrevrangebyscore('api_call_counts', '+inf', $threshold, ['limit' => [0, $maxRoutes]]);

        // Store the list of routes to cache (TTL: e.g., 1 hour, refresh periodically)
        Cache::put('cached_routes', $topRoutes, 3600);

        $this->info('Updated cached routes: ' . implode(', ', $topRoutes));
    }
}
