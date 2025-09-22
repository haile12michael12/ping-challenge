<?php

declare(strict_types=1);

namespace Challenge01;

use Spiral\GRPC\ContextInterface;
use Spiral\GRPC\ServiceInterface;
use Echo\EchoServiceInterface;
use Echo\EchoRequest;
use Echo\EchoResponse;
use Echo\StatsRequest;
use Echo\StatsResponse;
use Echo\HealthRequest;
use Echo\HealthResponse;

/**
 * Simple gRPC Echo Service
 */
class EchoService implements ServiceInterface, EchoServiceInterface
{
    private $stats = [
        'total_requests' => 0,
        'total_processing_time' => 0,
        'start_time' => 0,
        'request_counts_by_hour' => [],
        'recent_messages' => []
    ];

    public function __construct()
    {
        $this->stats['start_time'] = time();
    }
    
    /**
     * Echo method that returns the received message
     */
    public function Echo(ContextInterface $ctx, EchoRequest $in): EchoResponse
    {
        $startTime = microtime(true);
        
        // Log the request
        error_log('Echo request received: ' . $in->getMessage());
        
        // Create response
        $response = new EchoResponse();
        $response->setMessage($in->getMessage());
        $response->setOriginalMessage($in->getMessage());
        $response->setTimestamp(time());
        $response->setProcessingTimeMs((int)((microtime(true) - $startTime) * 1000));
        
        // Update stats
        $this->stats['total_requests']++;
        $this->stats['total_processing_time'] += $response->getProcessingTimeMs();
        
        $hour = date('Y-m-d H');
        if (!isset($this->stats['request_counts_by_hour'][$hour])) {
            $this->stats['request_counts_by_hour'][$hour] = 0;
        }
        $this->stats['request_counts_by_hour'][$hour]++;
        
        // Add to recent messages
        array_unshift($this->stats['recent_messages'], $in->getMessage());
        if (count($this->stats['recent_messages']) > 10) {
            array_pop($this->stats['recent_messages']);
        }
        
        return $response;
    }
    
    /**
     * Stream Echo method implementation
     */
    public function StreamEcho(ContextInterface $ctx): \Generator
    {
        while ($ctx->getStatus()->code === 0) {
            $in = yield;
            if ($in === null) {
                return;
            }
            
            $response = new EchoResponse();
            $response->setMessage($in->getMessage());
            $response->setOriginalMessage($in->getMessage());
            $response->setTimestamp(time());
            
            yield $response;
        }
    }
    
    /**
     * Get stats method implementation
     */
    public function GetStats(ContextInterface $ctx, StatsRequest $in): StatsResponse
    {
        $response = new StatsResponse();
        $response->setTotalRequests($this->stats['total_requests']);
        
        if ($this->stats['total_requests'] > 0) {
            $response->setAverageProcessingTimeMs($this->stats['total_processing_time'] / $this->stats['total_requests']);
        } else {
            $response->setAverageProcessingTimeMs(0);
        }
        
        $response->setUptimeSeconds(time() - $this->stats['start_time']);
        
        return $response;
    }
    
    /**
     * Health check method implementation
     */
    public function HealthCheck(ContextInterface $ctx, HealthRequest $in): HealthResponse
    {
        $response = new HealthResponse();
        $response->setStatus(1); // SERVING
        
        return $response;
    }
}
        
        $this->logger->info('Echo request received', [
            'message' => $in->getMessage(),
            'timestamp' => $in->getTimestamp(),
            'tags' => iterator_to_array($in->getTags())
        ]);

        // Validate input
        if (empty($in->getMessage())) {
            throw new \InvalidArgumentException('Message cannot be empty');
        }

        // Process message with advanced features
        $processedMessage = $this->processMessage($in->getMessage());
        
        // Update statistics
        $this->updateStats($in->getMessage(), $startTime);
        
        // Cache recent messages
        $this->cacheRecentMessage($processedMessage);

        // Dispatch event
        $this->eventDispatcher->dispatch(new EchoProcessedEvent($in, $processedMessage));

        $processingTime = (microtime(true) - $startTime) * 1000;

        $response = new EchoResponse();
        $response->setMessage($processedMessage);
        $response->setOriginalMessage($in->getMessage());
        $response->setTimestamp(time() * 1000000); // Convert to microseconds
        $response->setProcessingTimeMs((int)$processingTime);
        
        // Add metadata
        $metadata = [
            'processed_at' => date('Y-m-d H:i:s'),
            'request_id' => uniqid(),
            'server_version' => '1.0.0'
        ];
        foreach ($metadata as $key => $value) {
            $response->getMetadata()[$key] = $value;
        }

        // Add tags
        $response->getTags()->append('echoed');
        $response->getTags()->append('processed');

        $this->logger->info('Echo response sent', [
            'processing_time_ms' => $processingTime,
            'message_length' => strlen($processedMessage)
        ]);

        return $response;
    }

    public function StreamEcho(ContextInterface $ctx, \Iterator $in): \Iterator
    {
        $this->logger->info('Stream echo session started');

        foreach ($in as $request) {
            $startTime = microtime(true);
            
            $processedMessage = $this->processMessage($request->getMessage());
            $processingTime = (microtime(true) - $startTime) * 1000;

            $response = new EchoResponse();
            $response->setMessage($processedMessage);
            $response->setOriginalMessage($request->getMessage());
            $response->setTimestamp(time() * 1000000);
            $response->setProcessingTimeMs((int)$processingTime);

            $this->updateStats($request->getMessage(), $startTime);

            yield $response;
        }

        $this->logger->info('Stream echo session ended');
    }

    public function GetStats(ContextInterface $ctx, StatsRequest $in): StatsResponse
    {
        $this->logger->debug('Stats request received');

        $response = new StatsResponse();
        $response->setTotalRequests($this->stats['total_requests']);
        
        $avgProcessingTime = $this->stats['total_requests'] > 0 
            ? $this->stats['total_processing_time'] / $this->stats['total_requests']
            : 0;
        $response->setAverageProcessingTimeMs($avgProcessingTime);
        
        $response->setUptimeSeconds(time() - $this->stats['start_time']);

        if ($in->getIncludeDetailed()) {
            foreach ($this->stats['request_counts_by_hour'] as $hour => $count) {
                $response->getRequestCountsByHour()[$hour] = $count;
            }

            foreach ($this->stats['recent_messages'] as $message) {
                $response->getRecentMessages()->append($message);
            }
        }

        return $response;
    }

    public function HealthCheck(ContextInterface $ctx, HealthRequest $in): HealthResponse
    {
        $response = new HealthResponse();
        
        try {
            // Perform health checks
            $checks = $this->performHealthChecks($in->getIncludeDetails());
            
            $response->setStatus(Status::SERVING);
            $response->setMessage('Service is healthy');

            if ($in->getIncludeDetails()) {
                foreach ($checks as $check => $result) {
                    $response->getDetails()[$check] = $result;
                }
            }

            $this->logger->debug('Health check passed');
        } catch (\Exception $e) {
            $response->setStatus(Status::NOT_SERVING);
            $response->setMessage('Service is unhealthy: ' . $e->getMessage());
            
            $this->logger->error('Health check failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $response;
    }

    private function processMessage(string $message): string
    {
        // Advanced message processing
        $processed = $message;

        // Add echo prefix
        $processed = "ECHO: {$processed}";

        // Add timestamp
        $processed .= " [Processed at " . date('Y-m-d H:i:s') . "]";

        // Add random processing indicator
        $indicators = ['✓', '⚡', '🚀', '✨', '🎯'];
        $indicator = $indicators[array_rand($indicators)];
        $processed .= " {$indicator}";

        return $processed;
    }

    private function updateStats(string $message, float $startTime): void
    {
        $processingTime = (microtime(true) - $startTime) * 1000;
        
        $this->stats['total_requests']++;
        $this->stats['total_processing_time'] += $processingTime;

        // Update hourly counts
        $hour = date('Y-m-d H:00:00');
        $this->stats['request_counts_by_hour'][$hour] = 
            ($this->stats['request_counts_by_hour'][$hour] ?? 0) + 1;

        // Keep only last 24 hours
        $cutoff = time() - (24 * 60 * 60);
        foreach ($this->stats['request_counts_by_hour'] as $hour => $count) {
            if (strtotime($hour) < $cutoff) {
                unset($this->stats['request_counts_by_hour'][$hour]);
            }
        }
    }

    private function cacheRecentMessage(string $message): void
    {
        $key = 'recent_messages';
        $recent = $this->cache->get($key, []);
        
        array_unshift($recent, $message);
        $recent = array_slice($recent, 0, 10); // Keep only last 10 messages
        
        $this->cache->set($key, $recent, 3600);
        $this->stats['recent_messages'] = $recent;
    }

    private function performHealthChecks(bool $includeDetails): array
    {
        $checks = [];

        // Memory usage check
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $checks['memory_usage'] = "{$memoryUsage} / {$memoryLimit}";

        // Cache connectivity check
        try {
            $this->cache->set('health_check', 'ok', 10);
            $cacheResult = $this->cache->get('health_check');
            $checks['cache'] = $cacheResult === 'ok' ? 'OK' : 'FAILED';
        } catch (\Exception $e) {
            $checks['cache'] = 'FAILED: ' . $e->getMessage();
        }

        // Stats consistency check
        $checks['stats_consistency'] = $this->stats['total_requests'] >= 0 ? 'OK' : 'FAILED';

        if ($includeDetails) {
            $checks['uptime'] = time() - $this->stats['start_time'] . ' seconds';
            $checks['php_version'] = PHP_VERSION;
            $checks['grpc_version'] = phpversion('grpc') ?: 'Unknown';
        }

        return $checks;
    }
}
