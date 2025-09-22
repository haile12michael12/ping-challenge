<?php

declare(strict_types=1);

namespace Challenge01;

use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;

/**
 * MessageHistory service for storing and retrieving message history
 * with persistence capabilities
 */
class MessageHistory
{
    private const HISTORY_KEY = 'message_history';
    private const MAX_HISTORY_SIZE = 100;

    private CacheInterface $cache;
    private LoggerInterface $logger;
    private array $history = [];

    public function __construct(
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->loadHistory();
    }

    /**
     * Add a message to history
     */
    public function addMessage(string $originalMessage, string $processedMessage, array $metadata = []): void
    {
        $entry = [
            'id' => uniqid(),
            'original_message' => $originalMessage,
            'processed_message' => $processedMessage,
            'timestamp' => time(),
            'metadata' => $metadata
        ];

        array_unshift($this->history, $entry);
        $this->history = array_slice($this->history, 0, self::MAX_HISTORY_SIZE);
        
        $this->saveHistory();
        
        $this->logger->debug('Message added to history', [
            'message_id' => $entry['id'],
            'history_size' => count($this->history)
        ]);
    }

    /**
     * Get all message history
     */
    public function getHistory(int $limit = 10, int $offset = 0): array
    {
        return array_slice($this->history, $offset, $limit);
    }

    /**
     * Get message by ID
     */
    public function getMessageById(string $id): ?array
    {
        foreach ($this->history as $entry) {
            if ($entry['id'] === $id) {
                return $entry;
            }
        }
        
        return null;
    }

    /**
     * Search messages by content
     */
    public function searchMessages(string $query): array
    {
        $results = [];
        
        foreach ($this->history as $entry) {
            if (stripos($entry['original_message'], $query) !== false || 
                stripos($entry['processed_message'], $query) !== false) {
                $results[] = $entry;
            }
        }
        
        return $results;
    }

    /**
     * Clear message history
     */
    public function clearHistory(): void
    {
        $this->history = [];
        $this->saveHistory();
        $this->logger->info('Message history cleared');
    }

    /**
     * Load history from cache
     */
    private function loadHistory(): void
    {
        try {
            $history = $this->cache->get(self::HISTORY_KEY, []);
            $this->history = is_array($history) ? $history : [];
        } catch (\Exception $e) {
            $this->logger->error('Failed to load message history', [
                'error' => $e->getMessage()
            ]);
            $this->history = [];
        }
    }

    /**
     * Save history to cache
     */
    private function saveHistory(): void
    {
        try {
            $this->cache->set(self::HISTORY_KEY, $this->history, 86400 * 30); // 30 days
        } catch (\Exception $e) {
            $this->logger->error('Failed to save message history', [
                'error' => $e->getMessage()
            ]);
        }
    }
}