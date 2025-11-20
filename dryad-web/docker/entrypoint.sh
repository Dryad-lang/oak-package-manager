#!/bin/sh

# Wait for database to be ready (PostgreSQL or SQLite)
echo "Waiting for services to be ready..."
sleep 10

# Ensure proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Handle SQLite database if needed
if [ "$DB_CONNECTION" = "sqlite" ]; then
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
        chown www-data:www-data /var/www/html/database/database.sqlite
        chmod 664 /var/www/html/database/database.sqlite
    fi
fi

# Wait for PostgreSQL if using it
if [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "Waiting for PostgreSQL ($DB_HOST:$DB_PORT)..."
    timeout=60
    while ! nc -z "$DB_HOST" "$DB_PORT" && [ $timeout -gt 0 ]; do
        echo "PostgreSQL is unavailable - sleeping (${timeout}s remaining)"
        sleep 2
        timeout=$((timeout - 2))
    done
    
    if [ $timeout -le 0 ]; then
        echo "❌ Timeout waiting for PostgreSQL!"
        exit 1
    fi
    echo "✅ PostgreSQL is up - continuing"
fi

# Run migrations and seed with error handling
echo "Running database migrations..."
if ! php artisan migrate --force --no-interaction; then
    echo "❌ Database migration failed!"
    exit 1
fi

echo "Seeding database..."
if ! php artisan db:seed --force --no-interaction; then
    echo "⚠️ Database seeding failed, but continuing..."
fi

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf