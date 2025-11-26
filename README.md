# test-response-cache



This application caches API responses for GET methods.



1. This cache does not cache more than 'config.response\_cache.max\_routes' routes.
2. This cache does not cache if the API is called less than 'config.response\_cache.threshold' times.
3. All API caches has a TTL 'config.response\_cache.ttl\_minutes';
