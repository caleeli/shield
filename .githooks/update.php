<?php
runDocker(
    'audit-app',

    // Update libraries
    'cd /var/www;'.
    onchange(['composer.json'], 'composer update;') .
    // Rebuild database
    onchange(['database/migrations', 'database/seeds'], 'composer dumpautoload;php artisan migrate:fresh --seed;').
    // Rebuild frontend
    onchange(['resources'], 'npm install;npm run build;')
);
