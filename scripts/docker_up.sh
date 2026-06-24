#!/usr/bin/env bash
# Levanta ZGROUP Informes en Docker (Apache+PHP+MariaDB) en puerto 8877.
# Modo alternativo al Apache nativo (predeterminado).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker no está instalado." >&2
  exit 1
fi

COMPOSE="docker compose"
if ! docker compose version >/dev/null 2>&1; then
  COMPOSE="docker-compose"
fi

PORT="${WEB_PORT:-8877}"
mkdir -p informes

echo "==> Construyendo e iniciando contenedores (puerto $PORT)..."
$COMPOSE up -d --build

echo "==> Esperando base de datos..."
for i in $(seq 1 40); do
  if $COMPOSE exec -T db mariadb-admin ping -h127.0.0.1 -u"${DB_USER:-zgroupin_zgroupuser}" -p"${DB_PASS:-ZGROUP_2026}" --silent 2>/dev/null; then
    break
  fi
  sleep 2
done

echo ""
echo "=============================================="
echo " Docker activo — puerto $PORT"
echo "=============================================="
echo " Técnicos:  http://localhost:$PORT/index.php"
echo " Panel:     http://localhost:$PORT/panel.php"
echo " BD interna: db:3306 (solo red Docker)"
echo ""
echo " Detener:    bash scripts/docker_down.sh"
echo " Reimportar: USE_DOCKER=1 bash scripts/import_db.sh"
echo "=============================================="
