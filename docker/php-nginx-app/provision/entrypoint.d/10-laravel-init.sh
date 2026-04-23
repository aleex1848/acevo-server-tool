#!/bin/sh
cd /app

# Stelle sicher, dass alle Storage-Verzeichnisse existieren, bevor composer install ausgeführt wird
# (package:discover benötigt diese Verzeichnisse)
mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/framework/testing
mkdir -p /app/storage/logs
chmod -R 775 /app/storage
chown -R 1000:1000 /app/storage

composer install --no-interaction --no-dev --prefer-dist
#php artisan telescope:install
#php artisan horizon:publish
#mkdir -p /app/database
php artisan migrate --force
# SQLite (und -wal/-shm) müssen dem PHP-FPM-User (uid 1000) gehören — migrate läuft oft als root.
chown -R 1000:1000 /app/database
chmod -R ug+rwX /app/database
php artisan optimize:clear

#config:clear damit env() im code wieder funktioniert.
#php artisan config:clear
touch /app/storage/logs/laravel.log
chmod 666 /app/storage/logs/laravel.log
#cp -f composer.lock /app/storage/app/non-public/composer.lock
npm install
npm run build --production
php artisan optimize
php artisan storage:link
chown -R 1000:1000 /app/storage