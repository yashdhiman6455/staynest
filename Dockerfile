FROM php:8.4-cli

# ---------------------------------------------------------
# Install system dependencies and PHP extensions
# ---------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        bcmath \
        zip \
        exif \
        pcntl \
    && rm -rf /var/lib/apt/lists/*


# ---------------------------------------------------------
# Install Composer
# ---------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# ---------------------------------------------------------
# Install Node.js and npm
# ---------------------------------------------------------
COPY --from=node:20 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:20 /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm


# ---------------------------------------------------------
# Set application directory
# ---------------------------------------------------------
WORKDIR /var/www/html


# ---------------------------------------------------------
# Copy application source code
# ---------------------------------------------------------
COPY . .


# ---------------------------------------------------------
# TiDB Cloud SSL certificate
# ---------------------------------------------------------
COPY certs/cacert.pem /var/www/html/certs/cacert.pem


# ---------------------------------------------------------
# Install Laravel/PHP dependencies
# ---------------------------------------------------------
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction


# ---------------------------------------------------------
# Install Vue/Vite dependencies
# ---------------------------------------------------------
RUN npm install


# ---------------------------------------------------------
# Build Vue/Vite frontend
# ---------------------------------------------------------
RUN npm run build


# ---------------------------------------------------------
# Create Laravel storage directories
# ---------------------------------------------------------
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache


# ---------------------------------------------------------
# Set Laravel permissions
# ---------------------------------------------------------
RUN chmod -R 775 storage bootstrap/cache


# ---------------------------------------------------------
# Create storage symlink
# ---------------------------------------------------------
RUN php artisan storage:link || true


# ---------------------------------------------------------
# Clear Laravel cached configuration
# ---------------------------------------------------------
RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan view:clear


# ---------------------------------------------------------
# Render exposes the PORT environment variable
# ---------------------------------------------------------
EXPOSE 10000


# ---------------------------------------------------------
# Start Laravel
# ---------------------------------------------------------
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
