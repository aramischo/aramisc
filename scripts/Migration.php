<?php
#!/bin/bash

# Variables
REPO_URL="https://github.com/votre-repo/votre-projet.git"
REPO_BRANCH="main"
DEPLOY_PATH="C:\xampp\htdocsaramisc"

# Cloner ou mettre à jour le répertoire du projet
if [ -d "$DEPLOY_PATH" ]; then
    cd $DEPLOY_PATH
    git pull origin $REPO_BRANCH
else
    git clone -b $REPO_BRANCH $REPO_URL $DEPLOY_PATH
    cd $DEPLOY_PATH
fi

# Installer les dépendances Composer
composer install --no-interaction --prefer-dist --optimize-autoloader

# Copier le fichier .env.example en .env si .env n'existe pas
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Générer la clé d'application
php artisan key:generate

# Exécuter les migrations
php artisan migrate --force

# Exécuter les seeders
php artisan db:seed --force

# Nettoyer le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Redémarrer le serveur web (si nécessaire)
# sudo service apache2 restart
# sudo service nginx restart

echo "Déploiement terminé!"
