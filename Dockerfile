# 1. Usar la imagen oficial de PHP
FROM php:8.2-cli

# 2. Instalar herramientas del sistema necesarias para Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# 3. Instalar extensiones de PHP para conectar a bases de datos
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 4. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Establecer la carpeta de trabajo
WORKDIR /app

# 6. Copiar todo tu código al contenedor
COPY . .

# 7. Instalar las dependencias de tu proyecto
RUN composer install --optimize-autoloader --no-dev

# 8. Comando de arranque (Migra la base de datos y enciende el servidor)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
