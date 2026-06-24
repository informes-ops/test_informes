#!/bin/bash
set -e

mkdir -p /var/www/html/informes
chown -R www-data:www-data /var/www/html/informes 2>/dev/null || chmod 777 /var/www/html/informes

exec "$@"
