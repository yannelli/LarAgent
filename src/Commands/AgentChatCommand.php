<?php

namespace LarAgent\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AgentChatCommand extends Command
{
    protected $signature = 'agent:chat {agent : The name of the agent to chat with} {--history= : Chat history name}';

    protected $description = 'Start an interactive chat session with an agent';

    protected function logError($message)
    {
        $logPath = 'logs/agent-chat-errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";

        if (! is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0755, true);
        }

        file_put_contents($logPath, $logMessage, FILE_APPEND);
    }

    public function handle()
    {
        $agentName = $this->argument('agent');
        $historyName = $this->option('history') ?? Str::random(10);

        // Try both namespaces
        $agentClass = "\\App\\AiAgents\\{$agentName}";
        if (! class_exists($agentClass)) {
            $agentClass = "\\App\\Agents\\{$agentName}";
            if (! class_exists($agentClass)) {
                $this->error("Agent not found: {$agentName}");
                $this->logError("Agent not found: {$agentName}");

                return 1;
            }
        }

        $agent = $agentClass::for($historyName);

        $this->info("Starting chat with {$agentName}");
        $this->line("Using history: {$historyName}");
        $this->line("Type 'exit' to end the chat\n");

        while (true) {
            $message = $this->ask('You');

            if ($message === null || strtolower($message) === 'exit') {
                $this->info('Chat ended');

                return 0;
            }

            try {
                $response = $agent->respond($message);
                $this->line("\n<comment>{$agentName}:</comment>");
                $this->line($response."\n");
            } catch (\Exception $e) {
                $this->error('Error: '.$e->getMessage());
                $this->logError("Error in {$agentName} response: ".$e->getMessage()."\nStack trace:\n".$e->getTraceAsString());

                return 1;
            }
        }
    }
}
