<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
    }

    public function test_if_response_is_cached(){

        $responseA = $this->get('/api/data-a');
        // TODO: assert response is not from cache
        for($i = 0; $i < 5; $i++){
            $responseA = $this->get('/api/data-a');
        }
        // TODO: assert response is from cache

        for($i = 0; $i < 5; $i++){
            $responseB = $this->get('/api/data-b');
        }
        for($i = 0; $i < 5; $i++){
            $responseC = $this->get('/api/data-c');
        }

        $responseA = $this->get('/api/data-a');
        // TODO: assert response is not from cache

        $responseA->assertStatus(200);

    }
}
