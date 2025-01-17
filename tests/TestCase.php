<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Console\Kernel;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        // Hash::driver('bcrypt')->setRounds(4);
        return $app;
    }
}
