<?php

namespace Maestroerror\LarAgent;

use Illuminate\Contracts\Auth\Authenticatable;

class Agent
{
    public static function forUser(Authenticatable $user): static
    {
        $instance = new static;
        $userId = $user->getAuthIdentifier();

        $instance->chatSessionId = sprintf(
            '%s_%s_%s',
            class_basename(static::class),
            $instance->model,
            $userId
        );

        return $instance;
    }
}
