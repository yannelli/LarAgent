<?php

namespace Maestroerror\LarAgent\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maestroerror\LarAgent\LarAgentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Maestroerror\\LarAgent\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LarAgentServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laragent_table.php.stub';
        $migration->up();
        */
    }
}
