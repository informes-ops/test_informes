#!/usr/bin/env bash
# Importa zgroupin_zgroupinformes.sql en MariaDB local o en el contenedor Docker.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DUMP="$ROOT/zgroupin_zgroupinformes.sql"
DB_NAME="${DB_NAME:-zgroupin_zgroupinformes}"
DB_USER="${DB_USER:-zgroupin_zgroupuser}"
DB_PASS="${DB_PASS:-ZGROUP_2026}"
DB_HOST="${DB_HOST:-localhost}"
USE_DOCKER="${USE_DOCKER:-0}"

if [[ ! -f "$DUMP" ]]; then
  echo "No se encontró: $DUMP" >&2
  exit 1
fi

# Auto-detectar stack Docker si está corriendo
if [[ "$USE_DOCKER" == "0" ]] && command -v docker >/dev/null 2>&1; then
  if docker ps --format '{{.Names}}' 2>/dev/null | grep -qx 'zgroup-informes-db'; then
    USE_DOCKER=1
  fi
fi

run_mysql() {
  if [[ "$USE_DOCKER" == "1" ]]; then
    cd "$ROOT"
    COMPOSE="docker compose"
    if ! docker compose version >/dev/null 2>&1; then
      COMPOSE="docker-compose"
    fi
    $COMPOSE exec -T db mariadb -u"$DB_USER" -p"$DB_PASS" "$@"
  elif command -v mariadb >/dev/null 2>&1; then
    mariadb -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$@"
  elif command -v mysql >/dev/null 2>&1; then
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$@"
  else
    echo "Instale MariaDB nativo: sudo bash scripts/setup_apache.sh" >&2
    echo "O use Docker: bash scripts/docker_up.sh" >&2
    exit 1
  fi
}

echo "==> Modo: $([[ "$USE_DOCKER" == "1" ]] && echo 'Docker' || echo 'Apache/MariaDB nativo')"
echo "==> Verificando base de datos..."

if ! run_mysql -e "SELECT 1" "$DB_NAME" >/dev/null 2>&1; then
  echo "No se pudo conectar a $DB_NAME." >&2
  echo "  Nativo: sudo bash scripts/setup_apache.sh" >&2
  echo "  Docker: bash scripts/docker_up.sh" >&2
  exit 1
fi

ROWS=$(run_mysql -N -e "SELECT COALESCE(SUM(TABLE_ROWS),0) FROM information_schema.TABLES WHERE TABLE_SCHEMA='$DB_NAME';" "$DB_NAME" 2>/dev/null || echo 0)
if [[ "${FORCE:-0}" != "1" && "$ROWS" != "0" ]]; then
  echo "La base ya tiene datos ($ROWS filas aprox.). Use FORCE=1 para reimportar."
  exit 0
fi

echo "==> Importando $DUMP ..."
run_mysql "$DB_NAME" < "$DUMP"

echo "==> Resumen:"
run_mysql "$DB_NAME" -e "
SELECT 'tecnicos' t, COUNT(*) c FROM tecnicos UNION ALL
SELECT 'informes', COUNT(*) FROM informes UNION ALL
SELECT 'inspecciones_preliminares', COUNT(*) FROM inspecciones_preliminares UNION ALL
SELECT 'clientes_catalogo', COUNT(*) FROM clientes_catalogo;
"
echo "Listo."
