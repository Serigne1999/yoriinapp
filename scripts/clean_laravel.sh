#!/bin/bash

# Aller dans le dossier de ton projet Laravel
cd /home/u123456789/domains/yoriin.com/public_html
# Supprimer les logs Laravel
rm -f storage/logs/*.log

# Supprimer les vues compilées
rm -f storage/framework/views/*.php

# Supprimer les sessions Laravel expirées
find storage/framework/sessions/ -type f -mtime +1 -exec rm -f {} \;

# Nettoyer le cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

