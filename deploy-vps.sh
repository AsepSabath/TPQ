#!/usr/bin/env bash

set -euo pipefail

SERVICE_NAME="${SERVICE_NAME:-tpq}"
APP_DISPLAY_NAME="${APP_DISPLAY_NAME:-Sistem Manajemen Santri TPQ}"
APP_DIR="${APP_DIR:-/var/www/tpq}"
REPO_URL="${REPO_URL:-https://github.com/AsepSabath/TPQ.git}"
BRANCH="${BRANCH:-main}"
DOMAIN="${DOMAIN:-domain-anda.com}"
PHP_VERSION="${PHP_VERSION:-8.3}"
DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-tpq}"
DB_USERNAME="${DB_USERNAME:-tpq_user}"
DB_PASSWORD="${DB_PASSWORD:-ganti_password_ini}"
APP_URL="${APP_URL:-https://domain-anda.com}"
WA_ENABLED="${WA_ENABLED:-false}"
WA_ENDPOINT="${WA_ENDPOINT:-}"
WA_TOKEN="${WA_TOKEN:-}"
INSTALL_NODE="${INSTALL_NODE:-true}"
RUN_SEEDER="${RUN_SEEDER:-false}"
SKIP_NPM_BUILD="${SKIP_NPM_BUILD:-false}"

log() {
    printf '\n[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"
}

escape_sed_replacement() {
    printf '%s' "$1" | sed -e 's/[&|\\]/\\&/g'
}

set_env_value() {
    local env_file="$1"
    local key="$2"
    local value="$3"
    local escaped_value

    escaped_value="$(escape_sed_replacement "$value")"

    if grep -q "^${key}=" "$env_file"; then
        sed -i "s|^${key}=.*|${key}=${escaped_value}|" "$env_file"
    else
        printf '%s=%s\n' "$key" "$value" >> "$env_file"
    fi
}

require_root() {
    if [[ "${EUID}" -ne 0 ]]; then
        echo "Jalankan script ini sebagai root atau lewat sudo." >&2
        exit 1
    fi
}

install_packages() {
    log "Update paket dan install dependency sistem"
    apt-get update
    apt-get install -y \
        nginx \
        git \
        curl \
        unzip \
        supervisor \
        software-properties-common \
        ca-certificates \
        lsb-release \
        apt-transport-https

    apt-get install -y \
        "php${PHP_VERSION}" \
        "php${PHP_VERSION}-cli" \
        "php${PHP_VERSION}-fpm" \
        "php${PHP_VERSION}-mbstring" \
        "php${PHP_VERSION}-xml" \
        "php${PHP_VERSION}-curl" \
        "php${PHP_VERSION}-zip" \
        "php${PHP_VERSION}-bcmath" \
        "php${PHP_VERSION}-gd" \
        "php${PHP_VERSION}-intl" \
        "php${PHP_VERSION}-mysql"

    if [[ "$DB_CONNECTION" == "sqlite" ]]; then
        apt-get install -y "php${PHP_VERSION}-sqlite3"
    fi

    if [[ "$INSTALL_NODE" == "true" ]]; then
        if ! command -v node >/dev/null 2>&1; then
            log "Install Node.js LTS"
            curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
            apt-get install -y nodejs
        fi
    fi

    if ! command -v composer >/dev/null 2>&1; then
        log "Install Composer"
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        rm -f composer-setup.php
    fi
}

prepare_app_dir() {
    log "Siapkan folder aplikasi di ${APP_DIR}"
    mkdir -p "$APP_DIR"

    if [[ ! -d "$APP_DIR/.git" ]]; then
        git clone -b "$BRANCH" "$REPO_URL" "$APP_DIR"
    else
        git -C "$APP_DIR" fetch origin
        git -C "$APP_DIR" checkout "$BRANCH"
        git -C "$APP_DIR" pull origin "$BRANCH"
    fi
}

write_env_file() {
    log "Tulis file .env produksi"
    if [[ ! -f "$APP_DIR/.env" ]]; then
        cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    fi

    local env_file="$APP_DIR/.env"

    set_env_value "$env_file" "APP_NAME" "\"${APP_DISPLAY_NAME}\""
    set_env_value "$env_file" "APP_ENV" "production"
    set_env_value "$env_file" "APP_DEBUG" "false"
    set_env_value "$env_file" "APP_URL" "$APP_URL"
    set_env_value "$env_file" "DB_CONNECTION" "$DB_CONNECTION"
    set_env_value "$env_file" "DB_HOST" "$DB_HOST"
    set_env_value "$env_file" "DB_PORT" "$DB_PORT"
    set_env_value "$env_file" "DB_DATABASE" "$DB_DATABASE"
    set_env_value "$env_file" "DB_USERNAME" "$DB_USERNAME"
    set_env_value "$env_file" "DB_PASSWORD" "$DB_PASSWORD"
    set_env_value "$env_file" "QUEUE_CONNECTION" "database"
    set_env_value "$env_file" "CACHE_STORE" "database"
    set_env_value "$env_file" "SESSION_DRIVER" "database"
    set_env_value "$env_file" "WA_ENABLED" "$WA_ENABLED"
    set_env_value "$env_file" "WA_ENDPOINT" "$WA_ENDPOINT"
    set_env_value "$env_file" "WA_TOKEN" "$WA_TOKEN"
}

install_app_dependencies() {
    log "Install dependency Composer"
    cd "$APP_DIR"
    composer install --no-dev --optimize-autoloader

    if [[ "$SKIP_NPM_BUILD" != "true" ]]; then
        log "Install dependency frontend dan build asset"
        npm install
        npm run build
    fi
}

setup_runtime() {
    log "Generate key, migrasi, storage link, dan cache"
    cd "$APP_DIR"

    if ! grep -q '^APP_KEY=base64:' .env; then
        php artisan key:generate --force
    fi

    php artisan migrate --force

    if [[ "$RUN_SEEDER" == "true" ]]; then
        php artisan db:seed --force
    fi

    php artisan storage:link || true
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
}

setup_permissions() {
    log "Atur permission folder runtime"
    chown -R www-data:www-data "$APP_DIR"
    find "$APP_DIR/storage" -type d -exec chmod 775 {} \;
    find "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;
}

setup_nginx() {
    log "Buat konfigurasi Nginx"
    local nginx_conf="/etc/nginx/sites-available/${SERVICE_NAME}"
    cat > "$nginx_conf" <<EOF
server {
    listen 80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

    ln -sf "$nginx_conf" "/etc/nginx/sites-enabled/${SERVICE_NAME}"
    rm -f /etc/nginx/sites-enabled/default
    nginx -t
    systemctl reload nginx
}

setup_supervisor() {
    log "Konfigurasi Supervisor untuk queue worker"
    local supervisor_conf="/etc/supervisor/conf.d/${SERVICE_NAME}-worker.conf"
    cat > "$supervisor_conf" <<EOF
[program:${SERVICE_NAME}-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/${SERVICE_NAME}-worker.log
EOF

    supervisorctl reread
    supervisorctl update
    supervisorctl restart ${SERVICE_NAME}-worker:* || true
}

setup_cron() {
    log "Pasang cron scheduler Laravel"
    local cron_line="* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"
    local cron_tmp
    cron_tmp="$(mktemp)"
    crontab -l 2>/dev/null | grep -v "schedule:run" > "$cron_tmp" || true
    echo "$cron_line" >> "$cron_tmp"
    crontab "$cron_tmp"
    rm -f "$cron_tmp"
}

main() {
    require_root
    install_packages
    prepare_app_dir
    write_env_file
    install_app_dependencies
    setup_permissions
    setup_runtime
    setup_nginx
    setup_supervisor
    setup_cron

    log "Selesai. Aplikasi tersedia di ${APP_DIR}"
    log "Langkah berikutnya: pastikan DNS domain mengarah ke VPS dan aktifkan HTTPS dengan Certbot jika perlu."
}

main "$@"
