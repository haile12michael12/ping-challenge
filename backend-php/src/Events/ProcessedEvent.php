<?php

declare(strict_types=1);

namespace Challenge01\Events;

use Proto\EchoRequest;

/**
 * Event dispatched when an echo request is processed
 */
class EchoProcessedEvent
{
    public function __construct(
        private EchoRequest $request,
        private string $processedMessage
    ) {
    }

    public function getRequest(): EchoRequest
    {
        return $this->request;
    }

    public function getProcessedMessage(): string
    {
        return $this->processedMessage;
    }

    public function getMessage(): string
    {
        return $this->request->getMessage();
    }

    public function getTimestamp(): int
    {
        return $this->request->getTimestamp();
    }

    public function getTags(): array
    {
        return iterator_to_array($this->request->getTags());
    }
}
