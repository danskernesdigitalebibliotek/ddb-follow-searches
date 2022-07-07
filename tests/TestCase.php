<?php

namespace App;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

abstract class TestCase extends BaseTestCase
{
    use ProphecyTrait;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
