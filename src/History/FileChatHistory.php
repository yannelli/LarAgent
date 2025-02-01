<?php

namespace LarAgent\History;

use Illuminate\Support\Facades\Storage;
use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class FileChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected string $disk;   // The storage disk to use

    protected string $folder; // The folder path to store files

    public function __construct(string $name, array $options = [])
    {
        $this->disk = $options['disk'] ?? config('filesystems.default'); // Default to 'local' storage
        $this->folder = $options['folder'] ?? 'chat_histories'; // Default folder
        parent::__construct($name, $options);
    }

    public function readFromMemory(): void
    {
        $filePath = $this->getFullPath();

        if (Storage::disk($this->disk)->exists($filePath)) {
            $content = Storage::disk($this->disk)->get($filePath);

            try {
                $messages = json_decode($content, true);

                if (is_array($messages)) {
                    $this->setMessages($this->buildMessages($messages));
                } else {
                    $this->setMessages([]);
                }
            } catch (\Exception $e) {
                $this->setMessages([]);
            }
        } else {
            $this->setMessages([]);
        }
    }

    public function writeToMemory(): void
    {
        $filePath = $this->getFullPath();

        try {
            // Create directory if it doesn't exist
            $this->createFolderIfNotExists();

            // Write messages to the file
            Storage::disk($this->disk)->put($filePath, json_encode($this->toArrayForStorage(), JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to write chat history to file: {$filePath}");
        }
    }

    protected function createFolderIfNotExists(): void
    {
        $directory = $this->folder;

        if (! Storage::disk($this->disk)->exists($directory)) {
            Storage::disk($this->disk)->makeDirectory($directory);
        }
    }

    protected function getSafeName(): string
    {
        $name = $this->getIdentifier();

        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name); // Sanitize the name
    }

    protected function getFullPath(): string
    {
        return $this->folder.'/'.$this->getSafeName().'.json';
    }
}
