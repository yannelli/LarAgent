<?php

namespace Maestroerror\LarAgent\History;

use Maestroerror\LarAgent\Core\Abstractions\ChatHistory;
use Maestroerror\LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use Maestroerror\LarAgent\Message;

class JsonChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected string $folder = "";

    public function __construct(string $name, array $options = [])
    {
        parent::__construct($name, $options);
        // By default store the JSON files in the json_storage folder at project root
        $this->folder = $options['folder'] ?? dirname(__DIR__, 2) . "/json_storage";
    }

    public function readFromMemory(): void
    {
        // Check the folder exists
        $this->createFolderIfNotExists();
        // Get full file location
        $file = $this->getFullPath();
        // Read JSON
        $content = file_get_contents($file);
        // Build messages
        $this->messages = json_decode($content, true);
    }

    public function writeToMemory(): void
    {
        $this->createFolderIfNotExists();
        // @todo Implement writeToMemory() method.

    }

    protected function createFolderIfNotExists(): void
    {
        if (!file_exists($this->folder)) {
            mkdir($this->folder, 0777, true);
        }
    }

    protected function getSafeName(): string
    {
        $name = $this->getIdentifier();
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name); // Replace unsafe characters with underscores
    }

    protected function getFullPath(): string
    {
        return $this->folder . "/" . $this->getSafeName() . ".json";
    }

    protected function buildMessages(array $data): array
    {
        return array_map(function ($message) {
            // @todo Implement buildMessages() method.
            return Message::create($message['role'], $message['content'], $message['metadata']);
        }, $data);
    }
}
