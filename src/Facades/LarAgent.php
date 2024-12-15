<?php

namespace Maestroerror\LarAgent\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Maestroerror\LarAgent\LarAgent
 */
class LarAgent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Maestroerror\LarAgent\LarAgent::class;
    }
}
