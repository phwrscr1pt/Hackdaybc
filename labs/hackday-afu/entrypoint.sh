#!/bin/sh
set -e

# Make sure upload dir is there and writable
mkdir -p /app/uploads
chmod 777 /app/uploads

php-fpm -D
exec nginx -g 'daemon off;'