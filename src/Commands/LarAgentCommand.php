<?php

namespace Maestroerror\LarAgent\Commands;

use Illuminate\Console\Command;

class LarAgentCommand extends Command
{
    public $signature = 'laragent';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
