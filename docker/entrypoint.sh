#!/bin/sh
set -e

echo "🚀 Iniciando aplicação Laravel..."

# Aguardar MySQL estar pronto
echo "⏳ Aguardando MySQL..."
until php artisan db:show &> /dev/null; do
    echo "MySQL não está pronto ainda..."
    sleep 2
done

echo "✅ MySQL está pronto!"

# Executar migrações
echo "📦 Executando migrações..."
php artisan migrate --force

# Executar seeders (opcional, descomente se necessário)
# echo "🌱 Executando seeders..."
# php artisan db:seed --force

# Limpar cache
echo "🧹 Limpando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Otimizar aplicação
echo "⚡ Otimizando aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Criar link simbólico para storage (se necessário)
php artisan storage:link || true

echo "✅ Aplicação Laravel pronta!"

exec "$@"

