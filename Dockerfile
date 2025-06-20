FROM php:8.1-apache

# Copie les fichiers de l'API dans le dossier web
COPY . /var/www/html/

# Active les modules PHP nécessaires (exemple : PDO MySQL)
RUN docker-php-ext-install pdo pdo_mysql

EXPOSE 80
