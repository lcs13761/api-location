<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->call(
            "POST","/login",["email"=>"test@test.com" , "password"=>"password123"]
        );
        $this->assertEquals(200, $response->status());
    }
}
