# Utiliser l'image officielle PHP avec Apache
FROM php:8.1-apache

# Installer les bibliothèques PostgreSQL nécessaires
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# Copier tous les fichiers du projet dans le dossier web de l'image
COPY . /var/www/html/

# Donner les bons droits d'accès
RUN chown -R www-data:www-data /var/www/html

# Exposer le port HTTP par défaut
EXPOSE 80
