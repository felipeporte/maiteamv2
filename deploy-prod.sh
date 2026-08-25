#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

SSH_HOST="${DEPLOY_HOST:-ec2-54-204-121-77.compute-1.amazonaws.com}"
SSH_USER="${DEPLOY_USER:-ec2-user}"
SSH_KEY="${DEPLOY_KEY:-/Users/felipeporte/Documents/maiteam/artistico.pem}"
REMOTE_DIR="${DEPLOY_REMOTE_DIR:-/var/www/html}"

usage() {
    cat <<'EOF'
Uso:
  ./deploy-prod.sh
  ./deploy-prod.sh --migrate interno/migration/005_modalidades_competencia_asignacion.sql

Variables opcionales:
  DEPLOY_HOST
  DEPLOY_USER
  DEPLOY_KEY
  DEPLOY_REMOTE_DIR
EOF
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
    usage
    exit 0
fi

if [[ ! -f "$SSH_KEY" ]]; then
    echo "No existe la llave SSH configurada: $SSH_KEY" >&2
    exit 1
fi

migration_file=""
if [[ "${1:-}" == "--migrate" ]]; then
    migration_file="${2:-}"
    if [[ -z "$migration_file" ]]; then
        echo "Falta la ruta del archivo SQL." >&2
        usage
        exit 1
    fi
elif [[ $# -gt 0 ]]; then
    usage
    exit 1
fi

remote_cmd="cd \"$REMOTE_DIR\" && ./deploy.sh"
if [[ -n "$migration_file" ]]; then
    remote_cmd+=" --migrate $(printf '%q' "$migration_file")"
fi

ssh -i "$SSH_KEY" \
    -o BatchMode=yes \
    -o StrictHostKeyChecking=accept-new \
    "${SSH_USER}@${SSH_HOST}" \
    "$remote_cmd"
