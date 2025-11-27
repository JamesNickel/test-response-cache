<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Sleep;
use Tests\TestCase;
use function PHPUnit\Framework\assertNotEmpty;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_response_cache_works(){

        $threshold = config('responsecaching.response_cache.threshold');

        $routes = ['/api/data-a', '/api/data-b', '/api/data-c'];

        foreach($routes as $route){

            for($i = 0; $i < $threshold; $i++){

                $response = $this->get($route);
                $responseObj = json_decode($response->getContent());
                assertNotEmpty($responseObj->value);
            }
            $cachedResponse = $responseObj;

            $response = $this->get($route);
            $responseObj = json_decode($response->getContent());
            assert($responseObj->value == $cachedResponse->value);

            Sleep::for(1000)->milliseconds();
        }

        // Check if API cache is cleared for route '/api/data-a'
        $response = $this->get('/api/data-a');
        $responseObj1 = json_decode($response->getContent());
        assertNotEmpty($responseObj1->value);

        $response = $this->get('/api/data-a');
        $responseObj2 = json_decode($response->getContent());
        assertNotEmpty($responseObj2->value);

        //dump($responseObj1->value);
        //dump($responseObj2->value);
        assert($responseObj1->value != $responseObj2->value);
    }
}
