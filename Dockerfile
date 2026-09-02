FROM php:8.2-apache

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

ARG BUILD_ENV=local

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN if [ "$BUILD_ENV" = "local" ]; then \
        pecl install xdebug \
        && docker-php-ext-enable xdebug \
        && { \
            echo 'xdebug.mode=develop,debug'; \
            echo 'xdebug.start_with_request=yes'; \
            echo 'xdebug.client_host=host.docker.internal'; \
            echo 'xdebug.client_port=9003'; \
            echo 'xdebug.var_display_max_depth=10'; \
            echo 'xdebug.var_display_max_children=256'; \
            echo 'xdebug.var_display_max_data=2048'; \
        } > /usr/local/etc/php/conf.d/xdebug.ini; \
    fi

# Habilita mod_rewrite do Apache (necessário para o .htaccess funcionar)
RUN a2enmod rewrite

# Configura o Apache para permitir .htaccess na pasta do projeto
RUN echo '<Directory /var/www/html/moneta>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/moneta.conf \
    && a2enconf moneta

# Aponta o DocumentRoot para a pasta do projeto
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/moneta|' /etc/apache2/sites-available/000-default.conf

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/moneta

# Copia os arquivos do projeto
COPY . .

# Instala as dependências PHP (se a pasta vendor não existir)
RUN if [ ! -d "vendor" ]; then composer install --no-interaction --optimize-autoloader; fi

# Permissões para o Apache
RUN chown -R www-data:www-data /var/www/html/moneta \
    && chmod -R 755 /var/www/html/moneta

EXPOSE 80