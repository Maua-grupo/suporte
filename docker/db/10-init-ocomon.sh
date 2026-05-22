#!/bin/sh
set -eu

SQL_FILE="/docker-entrypoint-initdb.d/01-DB_OCOMON_5.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql"
TMP_SQL="/tmp/ocomon_init.sql"

# Ajusta o dump oficial para o banco/usuario definidos no compose.
sed \
  -e 's/USE `ocomon_5`;/USE `__DB_NAME__`;/g' \
  -e "/^CREATE DATABASE .*ocomon_5/d" \
  -e "/^CREATE USER 'ocomon_5'@'localhost'/d" \
  -e "/^GRANT .*ocomon_5/d" \
  -e "/^FLUSH PRIVILEGES;/d" \
  "$SQL_FILE" > "$TMP_SQL"

sed -i "s/__DB_NAME__/${MYSQL_DATABASE}/g" "$TMP_SQL"

mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}" < "$TMP_SQL"