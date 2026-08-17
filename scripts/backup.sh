#!/usr/bin/env bash
set -euo pipefail

ENV_FILE="/root/stockku/.env"
BACKUP_DIR="/root/stockku/storage/backups"
KEEP_DAYS=14

[ -f "$ENV_FILE" ] || { echo "ERROR: $ENV_FILE not found" >&2; exit 1; }

DB_HOST=$(grep -E '^DB_HOST=' "$ENV_FILE" | cut -d= -f2- | tr -d '"')
DB_PORT=$(grep -E '^DB_PORT=' "$ENV_FILE" | cut -d= -f2- | tr -d '"')
DB_DATABASE=$(grep -E '^DB_DATABASE=' "$ENV_FILE" | cut -d= -f2- | tr -d '"')
DB_USERNAME=$(grep -E '^DB_USERNAME=' "$ENV_FILE" | cut -d= -f2- | tr -d '"')
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' "$ENV_FILE" | cut -d= -f2- | tr -d '"')

[ -d "$BACKUP_DIR" ] || mkdir -p "$BACKUP_DIR"

STAMP=$(date +%Y%m%d-%H%M%S)
OUT_FILE="$BACKUP_DIR/stock-$STAMP.sql.gz"

mysqldump \
  --skip-ssl \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USERNAME" \
  --password="$DB_PASSWORD" \
  --single-transaction --routines --triggers \
  "$DB_DATABASE" | gzip > "$OUT_FILE"

find "$BACKUP_DIR" -name 'stock-*.sql.gz' -mtime +"$KEEP_DAYS" -delete

echo "Backup OK: $OUT_FILE ($(du -h "$OUT_FILE" | cut -f1))"