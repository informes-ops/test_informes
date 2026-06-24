#!/usr/bin/env bash
# Detiene el entorno Docker de ZGROUP Informes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

COMPOSE="docker compose"
if ! docker compose version >/dev/null 2>&1; then
  COMPOSE="docker-compose"
fi

$COMPOSE down
echo "Contenedores Docker detenidos."
