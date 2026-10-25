<?php

namespace Tests;

use App\Cat;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $baseUrl = 'http://localhost';

    protected function loginAsUser($overrides = [])
    {
        $userId = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $overrides = array_merge([
            'id'         => $userId
        ], $overrides);

        $user = factory(Cat::class)->create($overrides);
        $this->actingAs($user);

        return $user;
    }
}
