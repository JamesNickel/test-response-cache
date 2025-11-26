<?php

return [
    'response_cache' => [
        'threshold' => 5, // Call count to qualify for caching
        'max_routes' => 2,  // Number of APIs to cache
        'ttl_minutes' => 60, // Cache TTL per response
    ],
];
