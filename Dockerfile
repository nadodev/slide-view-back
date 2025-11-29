# Dockerfile para Laravel Backend
FROM php:8.2-fpm-alpine

# Instalar dependências do sistema
RUN apk add --no-cache \
    git \
    curl \
    wget \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    mysql-client \
    nodejs \
    npm \
    nginx \
    supervisor

# Instalar extensões PHP necessárias
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos de configuração
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copiar código da aplicação
COPY . .

# Copiar configuração do nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copiar configuração do supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Instalar dependências do npm e build assets (se necessário)
RUN if [ -f "package.json" ]; then npm install && npm run build; fi

# Expor porta
EXPOSE 80

# Script de inicialização
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh || true

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

