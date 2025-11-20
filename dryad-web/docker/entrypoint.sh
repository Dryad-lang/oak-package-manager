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
    echo "Waiting for PostgreSQL..."
    while ! nc -z "$DB_HOST" "$DB_PORT"; do
        echo "PostgreSQL is unavailable - sleeping"
        sleep 2
    done
    echo "PostgreSQL is up - continuing"
fi

# Run migrations and seed
echo "Running database migrations..."
php artisan migrate --force --no-interaction

echo "Seeding database..."
php artisan db:seed --force --no-interaction

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf