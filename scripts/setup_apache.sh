#!/usr/bin/env bash
# Instala y configura Apache2 + PHP + MariaDB para ZGROUP Informes (sin Docker).
# Ejecutar desde la raíz del proyecto:
#   bash scripts/setup_apache.sh
set -euo pipefail

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
  echo "Este script requiere permisos de administrador." >&2
  echo "Ejecute: sudo bash scripts/setup_apache.sh" >&2
  exit 1
fi

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DUMP="$ROOT/zgroupin_zgroupinformes.sql"
VHOST_SRC="$ROOT/deploy/apache/zgroup-informes.conf"
VHOST_DST="/etc/apache2/sites-available/zgroup-informes.conf"

DB_NAME="${DB_NAME:-zgroupin_zgroupinformes}"
DB_USER="${DB_USER:-zgroupin_zgroupuser}"
DB_PASS="${DB_PASS:-ZGROUP_2026}"
SITE_NAME="${SITE_NAME:-zgroup-informes.local}"

echo "==> Deteniendo stack Docker (si está activo en puerto 8877)..."
if command -v docker >/dev/null 2>&1; then
  COMPOSE="docker compose"
  if ! docker compose version >/dev/null 2>&1; then
    COMPOSE="docker-compose"
  fi
  (cd "$ROOT" && $COMPOSE down 2>/dev/null) || true
  docker stop zgroup-db zgroup-informes-web zgroup-informes-db 2>/dev/null || true
  docker rm zgroup-db zgroup-informes-web zgroup-informes-db 2>/dev/null || true
fi

echo "==> Instalando Apache2, PHP y MariaDB..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq \
  apache2 \
  libapache2-mod-php \
  php-cli \
  php-mysql \
  php-curl \
  php-mbstring \
  php-xml \
  php-gd \
  mariadb-server \
  mariadb-client

echo "==> Iniciando servicios..."
systemctl enable --now mariadb apache2

echo "==> Creando base de datos y usuario..."
mariadb <<EOF
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

if [[ -f "$DUMP" ]]; then
  ROWS=$(mariadb -N -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='$DB_NAME';" 2>/dev/null || echo 0)
  if [[ "$ROWS" == "0" ]]; then
    echo "==> Importando $DUMP ..."
    mariadb -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DUMP"
  else
    echo "==> Base de datos ya tiene tablas; omitiendo importación (use FORCE=1 para reimportar)."
    if [[ "${FORCE:-0}" == "1" ]]; then
      echo "==> Reimportando (FORCE=1)..."
      mariadb -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DUMP"
    fi
  fi
else
  echo "AVISO: no se encontró $DUMP — solo se creó la BD vacía." >&2
fi

echo "==> Carpetas y permisos..."
mkdir -p "$ROOT/informes"
chown -R www-data:www-data "$ROOT/informes"
chmod 775 "$ROOT/informes"

# Apache debe poder atravesar la ruta hasta el proyecto
for dir in "$(dirname "$ROOT")" "$ROOT"; do
  chmod o+x "$dir" 2>/dev/null || true
done
chmod o+r "$ROOT"/*.php 2>/dev/null || true

echo "==> Configurando VirtualHost Apache..."
sed "s|Define PROJECT_ROOT .*|Define PROJECT_ROOT $ROOT|" "$VHOST_SRC" > "$VHOST_DST"
a2enmod rewrite headers 2>/dev/null || a2enmod rewrite
a2dissite 000-default.conf 2>/dev/null || true
a2ensite zgroup-informes.conf
systemctl reload apache2

if ! grep -q "$SITE_NAME" /etc/hosts; then
  echo "127.0.0.1 $SITE_NAME" >> /etc/hosts
fi

echo ""
echo "=============================================="
echo " Instalación completada"
echo "=============================================="
echo " URL técnicos:  http://$SITE_NAME/index.php"
echo " URL panel:     http://$SITE_NAME/panel.php"
echo " BD:            $DB_NAME @ localhost"
echo " Usuario BD:    $DB_USER"
echo ""
echo " Verificar:     php $ROOT/database/install.php --verify"
echo ""
echo " Alternativa Docker (puerto 8877): bash scripts/docker_up.sh"
echo "=============================================="
