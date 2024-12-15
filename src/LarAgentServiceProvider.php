<?php

namespace Maestroerror\LarAgent;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Maestroerror\LarAgent\Commands\LarAgentCommand;

class LarAgentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laragent')
            ->hasConfigFile()
            ->hasCommand(LarAgentCommand::class);
    }
}
