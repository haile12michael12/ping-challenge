<?php

use PHPUnit\Framework\TestCase;
use Echo\EchoRequest;
use Echo\EchoResponse;
use Echo\StatsRequest;
use Echo\StatsResponse;
use Echo\HealthCheckRequest;
use Echo\HealthCheckResponse;

class EchoServiceTest extends TestCase
{
    private $service;

    protected function setUp(): void
    {
        $this->service = new \Echo\EchoService();
    }

    public function testEcho()
    {
        $request = new EchoRequest();
        $request->setMessage('Test message');

        $response = $this->service->Echo($request);

        $this->assertInstanceOf(EchoResponse::class, $response);
        $this->assertEquals('Test message', $response->getMessage());
        $this->assertNotEmpty($response->getTimestamp());
    }

    public function testGetStats()
    {
        // First send a few echo messages to generate stats
        $request1 = new EchoRequest();
        $request1->setMessage('Test message 1');
        $this->service->Echo($request1);

        $request2 = new EchoRequest();
        $request2->setMessage('Test message 2');
        $this->service->Echo($request2);

        // Now get stats
        $statsRequest = new StatsRequest();
        $response = $this->service->GetStats($statsRequest);

        $this->assertInstanceOf(StatsResponse::class, $response);
        $this->assertGreaterThanOrEqual(2, $response->getTotalRequests());
        $this->assertGreaterThan(0, $response->getAverageProcessingTime());
    }

    public function testHealthCheck()
    {
        $request = new HealthCheckRequest();
        $response = $this->service->HealthCheck($request);

        $this->assertInstanceOf(HealthCheckResponse::class, $response);
        $this->assertEquals('OK', $response->getStatus());
    }
}