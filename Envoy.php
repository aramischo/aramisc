<?php
@servers(['web' => 'user@your-server.com'])

@task('deploy', ['on' => 'web'])
    cd /var/www/votre-projet
    git pull origin main
    composer install --no-interaction --prefer-dist --optimize-autoloader
    php artisan migrate --force
    php artisan db:seed --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
@endtask
