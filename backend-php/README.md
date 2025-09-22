# Challenge 01: Advanced gRPC Echo Server

A sophisticated gRPC echo server built with modern PHP features, advanced error handling, logging, metrics, and health monitoring.

## Features

### Core Functionality
- **Echo Service**: Returns processed messages with metadata
- **Streaming Echo**: Bidirectional streaming support
- **Health Checks**: Comprehensive health monitoring
- **Statistics**: Real-time metrics and analytics

### Advanced Features
- **Dependency Injection**: Clean architecture with container
- **Event System**: Event-driven architecture with custom events
- **Caching**: Redis/File-based caching for performance
- **Logging**: Structured logging with multiple handlers
- **Metrics**: Request counting, processing time tracking
- **Error Handling**: Comprehensive error management
- **Validation**: Input validation and sanitization

## Architecture

```
├── src/
│   ├── EchoService.php          # Main gRPC service implementation
│   └── Events/
│       └── EchoProcessedEvent.php # Custom events
├── proto/
│   └── echo.proto              # Protocol buffer definitions
├── server.php                  # Server entry point
├── composer.json               # Dependencies
└── .env                        # Configuration
```

## Installation

1. **Install Dependencies**:
   ```bash
   composer install
   ```

2. **Generate Protocol Buffers** (requires protoc and grpc_php_plugin):
   ```bash
   composer run generate-proto
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

## Usage

### Start the Server
```bash
# Production mode
php server.php

# Development mode with debug
php server.php --env=development --debug
```

### gRPC Services

#### Echo
```protobuf
rpc Echo(EchoRequest) returns (EchoResponse);
```

**Request**:
- `message`: String to echo
- `metadata`: Key-value metadata
- `timestamp`: Request timestamp
- `tags`: Array of tags

**Response**:
- `message`: Processed echo message
- `original_message`: Original input
- `timestamp`: Response timestamp
- `processing_time_ms`: Processing duration
- `metadata`: Response metadata
- `tags`: Response tags

#### Stream Echo
```protobuf
rpc StreamEcho(stream EchoRequest) returns (stream EchoResponse);
```

Bidirectional streaming for real-time echo processing.

#### Get Stats
```protobuf
rpc GetStats(StatsRequest) returns (StatsResponse);
```

Returns server statistics including:
- Total requests
- Average processing time
- Uptime
- Request counts by hour
- Recent messages

#### Health Check
```protobuf
rpc HealthCheck(HealthRequest) returns (HealthResponse);
```

Comprehensive health monitoring including:
- Service status
- Memory usage
- Cache connectivity
- Stats consistency

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Environment (development/production) | `development` |
| `APP_DEBUG` | Debug mode | `true` |
| `LOG_LEVEL` | Logging level | `debug` |
| `CACHE_DRIVER` | Cache backend (file/redis) | `file` |
| `GRPC_HOST` | gRPC server host | `0.0.0.0` |
| `GRPC_PORT` | gRPC server port | `8080` |

### Logging

The server uses structured logging with multiple handlers:
- **File Handler**: Rotating log files
- **Console Handler**: Development output
- **JSON Handler**: Machine-readable logs
- **Syslog Handler**: Production logging

### Caching

Supports multiple cache backends:
- **File Cache**: Local file-based caching
- **Redis Cache**: Distributed caching

### Events

The server dispatches events for:
- Echo requests processed
- Health check failures
- Statistics updates

## Performance Features

- **Connection Pooling**: Efficient connection management
- **Request Validation**: Input sanitization
- **Metrics Collection**: Performance monitoring
- **Caching**: Response caching for repeated requests
- **Streaming**: Efficient bidirectional streaming

## Error Handling

Comprehensive error handling includes:
- Input validation errors
- Service unavailable errors
- Rate limiting
- Health check failures
- Graceful shutdown

## Monitoring

Built-in monitoring features:
- Request/response metrics
- Processing time tracking
- Memory usage monitoring
- Health status reporting
- Recent message tracking

## Development

### Testing
```bash
# Run tests (when implemented)
composer test

# Code quality checks
composer cs-check
composer phpstan
```

### Debugging
Enable debug mode for detailed logging:
```bash
php server.php --debug
```

## Production Deployment

1. **Set Production Environment**:
   ```bash
   export APP_ENV=production
   export APP_DEBUG=false
   ```

2. **Configure Logging**:
   Set up log rotation and monitoring

3. **Configure Caching**:
   Use Redis for distributed caching

4. **Health Monitoring**:
   Set up health check endpoints monitoring

## Integration

The server integrates with:
- **React Frontend**: Via gRPC-web
- **Monitoring Systems**: Via health checks
- **Log Aggregation**: Via structured logging
- **Cache Systems**: Via Redis/file backends

## Advanced Usage

### Custom Events
```php
$eventDispatcher->addListener(
    'Challenge01\Events\EchoProcessedEvent',
    function($event) {
        // Handle echo processed event
    }
);
```

### Metrics Collection
```php
$stats = $echoService->GetStats($ctx, new StatsRequest());
echo "Total requests: " . $stats->getTotalRequests();
```

### Health Monitoring
```php
$health = $echoService->HealthCheck($ctx, new HealthRequest());
if ($health->getStatus() !== Status::SERVING) {
    // Handle unhealthy state
}
```
