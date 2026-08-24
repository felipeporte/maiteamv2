#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

usage() {
    cat <<'EOF'
Uso:
  ./deploy.sh
  ./deploy.sh --migrate interno/migration/004_nombre.sql

Actualiza main desde origin con fast-forward only.
Si se pasa --migrate, aplica el SQL indicado contra la base definida en .env.
EOF
}

apply_migration() {
    local migration_file="$1"

    if [[ ! -f "$migration_file" ]]; then
        echo "Migracion no encontrada: $migration_file" >&2
        exit 1
    fi

    if [[ ! -f .env ]]; then
        echo "Falta .env en la raiz del proyecto." >&2
        exit 1
    fi

    set -a
    source ./.env
    set +a

    : "${DB_HOST:?DB_HOST no definido en .env}"
    : "${DB_NAME:?DB_NAME no definido en .env}"
    : "${DB_USER:?DB_USER no definido en .env}"
    : "${DB_PASS:?DB_PASS no definido en .env}"

    MYSQL_PWD="$DB_PASS" mariadb -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < "$migration_file"
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
    usage
    exit 0
fi

if [[ -n "$(git status --porcelain)" ]]; then
    echo "Hay cambios locales sin resolver en este servidor. Cancela el despliegue." >&2
    git status --short
    exit 1
fi

current_branch="$(git branch --show-current)"
if [[ "$current_branch" != "main" ]]; then
    echo "El deploy solo se ejecuta desde la rama main. Rama actual: $current_branch" >&2
    exit 1
fi

echo "Estado actual:"
git rev-parse --short HEAD

echo "Actualizando desde GitHub..."
git fetch origin main
git pull --ff-only origin main

echo "Revision actualizada:"
git rev-parse --short HEAD

if [[ "${1:-}" == "--migrate" ]]; then
    migration_file="${2:-}"
    if [[ -z "$migration_file" ]]; then
        echo "Falta la ruta del archivo SQL." >&2
        usage
        exit 1
    fi

    echo "Aplicando migracion: $migration_file"
    apply_migration "$migration_file"
fi

echo "Deploy finalizado."
