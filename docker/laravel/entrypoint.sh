#!/bin/sh
set -e

until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'DB ready'; } catch (Exception $e) { exit(1); }"; do
  echo "Waiting for MySQL..."
  sleep 3
done

php artisan config:clear

if [ $# -gt 0 ]; then
  exec "$@"
fi

php artisan migrate --force
php artisan serve --host=0.0.0.0 --port=8000