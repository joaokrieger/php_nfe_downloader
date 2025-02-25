# Escolher uma imagem base do PHP com Apache
FROM php:8.0-apache

# Instalar dependências do sistema para as extensões PHP
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \  
    autoconf \
    gcc \
    make \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    curl \
    dom \
    gd \
    mbstring \
    soap \
    xml \
    zip \
    && rm -rf /usr/src/php/src/*  

# Instalar o Composer (gerenciador de dependências PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copiar repositório para diretório de trabalho
COPY . /var/www/html/

# Instalar as dependências do Composer
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Expor a porta 80
EXPOSE 80

# Iniciar o Apache
CMD ["apache2-foreground"]