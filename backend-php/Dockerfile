FROM php:7.3-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    && docker-php-ext-install zip

# Install protobuf compiler
RUN apt-get install -y protobuf-compiler

# Install gRPC
RUN pecl install grpc-1.30.0 \
    && pecl install protobuf-3.12.0 \
    && docker-php-ext-enable grpc protobuf

# Install Composer
COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/app

# Copy composer files
COPY composer.json ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Copy application files
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize

# Expose port
EXPOSE 50051

# Start server
CMD ["php", "server.php"]