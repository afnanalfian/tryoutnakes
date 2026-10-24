#!/usr/bin/env bash
# Deploy / update aplikasi di VPS. Jalankan sebagai user "deploy" dari folder proyek:
#   cd /var/www/tryoutnakes && ./deploy.sh
set -euo pipefail
cd "$(dirname "$0")"

echo "▶ Mode maintenance"
php artisan down --retry=30 --refresh=15 || true

if [ -d .git ]; then
  echo "▶ Tarik kode terbaru"
  git pull --ff-only
fi

echo "▶ Dependensi PHP"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "▶ Build aset (Tailwind + Vite)"
npm ci --no-audit --no-fund || npm install --no-audit --no-fund
npm run build

echo "▶ Migrasi database"
php artisan migrate --force

echo "▶ Optimasi cache"
php artisan storage:link 2>/dev/null || true
php artisan optimize:clear
php artisan optimize
php artisan view:cache
php artisan sitemap:warm || true

echo "▶ Restart worker & PHP-FPM (OPcache)"
php artisan queue:restart
sudo systemctl reload php8.3-fpm

php artisan up
echo "✅ Deploy selesai"
