<?php

namespace LarAgent\History;

use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class JsonChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected string $folder = '';

    public function __construct(string $name, array $options = [])
    {
        // By default store the JSON files in the json_storage folder at project root
        $this->folder = $options['folder'] ?? dirname(__DIR__, 2).'/json_storage';
        parent::__construct($name, $options);
    }

    public function readFromMemory(): void
    {
        // Check the folder exists
        $this->createFolderIfNotExists();
        // Get full file location
        $file = $this->getFullPath();
        if (file_exists($file) === false) {
            $this->setMessages([]);

            return;
        }
        // Read JSON
        $content = file_get_contents($file);
        // Build messages
        $this->setMessages($this->buildMessages(json_decode($content, true)));

    }

    public function writeToMemory(): void
    {
        $this->createFolderIfNotExists();
        // Get full file location
        $file = $this->getFullPath();
        // Create json file
        file_put_contents($file, json_encode($this->toArrayForStorage()));
    }

    protected function createFolderIfNotExists(): void
    {
        if (! file_exists($this->folder)) {
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
        return $this->folder.'/'.$this->getSafeName().'.json';
    }
}
