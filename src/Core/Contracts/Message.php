<?php

namespace Maestroerror\LarAgent\Core\Contracts;

interface Message
{
    public function getRole(): string;

    public function getContent(): string|array;

    public function getMetadata(): array;

    public function setMetadata(array $data): void;

    public function toArray(): array;
}
