#!/usr/bin/env bash
set -e

cd /app

echo "🚀 Vision backend bootstrap"

[ -f .env ] || cp .env.example .env 2>/dev/null || touch .env

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --no-progress
fi
composer dump-autoload --quiet

grep -q "^APP_KEY=base64:" .env || php artisan key:generate --force

echo "⏳ czekam na bazę"
until php artisan db:monitor --databases=mysql >/dev/null 2>&1; do sleep 2; done

# Pierwszy start rozpoznajemy po fladze w .env — to jedyne źródło prawdy o stanie instalacji.
# Po finalize wizarda APP_INSTALLED jest przełączane na true i tę samą wartość widzi Laravel
# (InstallGate, InstallStateRepository::isInstalled) oraz entrypoint przy kolejnym starcie.
if ! grep -qE "^APP_INSTALLED=true" .env; then
    php artisan migrate:fresh --force --seed --seeder=Database\\Seeders\\RoleAndPermissionsSeeder
    php artisan passport:keys --force --no-interaction || true
    # --public → klient bez secret (Passport 12 wymaga secret dla non-public clients przy
    # password grant; AuthorizationService::login secretu nie przekazuje, więc public = OK dla SPA).
    php artisan passport:client --password --public --no-interaction --name="Vision Web" --provider=users || true
else
    php artisan migrate --force
    [ -f /app/storage/oauth-private.key ] || php artisan passport:keys --no-interaction || true
fi

php artisan config:clear
php artisan route:clear

# Symlink public/storage -> storage/app/public so Storage::url() served by `artisan serve` works.
# Idempotent — `storage:link` no-ops when the link already exists.
php artisan storage:link --quiet 2>/dev/null || true

chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true
# Passport wymaga restrykcyjnych uprawnień na kluczach RSA (600 lub 660) —
# ustawiamy po masowym chmodzie 775 powyżej, żeby Laravel nie odmówił startu.
[ -f /app/storage/oauth-private.key ] && chmod 600 /app/storage/oauth-private.key || true
[ -f /app/storage/oauth-public.key ] && chmod 600 /app/storage/oauth-public.key || true
mkdir -p /var/log/supervisor

echo "✅ start: $*"
exec "$@"
