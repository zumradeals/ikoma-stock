# Déploiement — IKOMA STOCK

Application Laravel 11 + Livewire, servie en production sur `https://stock.dgafrique.com`
depuis ce VPS (Apache + PHP-FPM 8.3 + MySQL, tous déjà en place pour les autres sous-domaines
`dgafrique.com`). Répertoire de l'app : `/var/www/html/ikoma-stock`.

## Variables d'environnement (`.env`)

```env
APP_NAME="IKOMA STOCK"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://stock.dgafrique.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ikomastock
DB_USERNAME=ikoma
DB_PASSWORD=<voir gestionnaire de secrets>

FILESYSTEM_DISK=public
SESSION_DRIVER=file
CACHE_STORE=file        # CACHE_STORE, pas CACHE_DRIVER, sur Laravel 11
QUEUE_CONNECTION=sync
MAIL_MAILER=log

SANCTUM_STATEFUL_DOMAINS=stock.dgafrique.com
SESSION_DOMAIN=.dgafrique.com
```

`APP_KEY` est généré une fois pour la prod via `php artisan key:generate --force` — ne jamais
réutiliser la clé de dev (les données déjà chiffrées deviendraient illisibles si on la change
après coup).

Le compte SUPER_ADMIN de démarrage a été créé manuellement (pas via `db:seed`, qui contient
aussi 2 fausses entreprises de démo non désirées en prod) : `superadmin@dgafrique.com`.
Mot de passe transmis séparément — à changer dès la première connexion.

## Base de données

MySQL tourne déjà sur ce VPS. Base et utilisateur dédiés créés une fois :

```sql
CREATE DATABASE ikomastock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ikoma'@'127.0.0.1' IDENTIFIED BY '<mot de passe>';
GRANT ALL PRIVILEGES ON ikomastock.* TO 'ikoma'@'127.0.0.1';
GRANT PROCESS ON *.* TO 'ikoma'@'127.0.0.1'; -- évite un warning mysqldump sur les tablespaces
```

## Serveur web (Apache + PHP-FPM, pas Nginx)

Ce VPS sert déjà plusieurs sous-domaines `dgafrique.com` en Apache — on suit le même schéma
plutôt que d'installer Nginx en parallèle.

- Vhost HTTP : `/etc/apache2/sites-available/stock.dgafrique.com.conf`
- Vhost HTTPS : `/etc/apache2/sites-available/stock.dgafrique.com-le-ssl.conf` (généré/complété
  par `certbot --apache`)
- `DocumentRoot` : `/var/www/html/ikoma-stock/public`
- PHP-FPM 8.3 via socket partagé : `/run/php/php8.3-fpm.sock`
- `storage/` et `bootstrap/cache/` appartiennent à `www-data:www-data` (Apache doit pouvoir y
  écrire — logs, cache, sessions fichier)

Le vhost SSL inclut :
- Headers de sécurité (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `HSTS`)
- Cache navigateur 1 an + `immutable` sur `public/build` (assets Vite, hashés donc invalidés
  automatiquement à chaque nouveau build)

### SSL

```bash
certbot --apache -d stock.dgafrique.com --agree-tos -m <email> --redirect
```

Renouvellement automatique déjà actif via `certbot.timer` (systemd), partagé avec les autres
domaines de ce VPS — rien de spécifique à IKOMA STOCK à reconfigurer.

## Build & mise en production

```bash
cd /var/www/html/ikoma-stock
composer install --no-dev --optimize-autoloader
php artisan config:cache && php artisan route:cache && php artisan view:cache
npm install && npm run build
php artisan migrate --force
php artisan storage:link   # déjà fait, no-op si le lien existe déjà
chown -R www-data:www-data storage bootstrap/cache
```

Ne pas lancer `php artisan db:seed --force` en production : le seeder par défaut crée 2
fausses entreprises de démo. Si un jour un jeu de données de démonstration est vraiment
nécessaire, isoler `CompanyDemoSeeder` du `SuperAdminSeeder` avant de l'exécuter.

## Mise à jour de l'application

```bash
cd /var/www/html/ikoma-stock
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
npm install && npm run build
chown -R www-data:www-data storage bootstrap/cache
```

## Cron (crontab système, pas cPanel)

```
* * * * * cd /var/www/html/ikoma-stock && php artisan schedule:run >> /dev/null 2>&1
0 2 * * * /usr/local/bin/ikoma-stock-backup.sh >> /var/log/ikoma-stock-backup.log 2>&1
```

`schedule:run` déclenche `RefreshOverdueStatuses` chaque nuit à 00:01 (défini dans
`routes/console.php` — Laravel 11 n'a plus de `app/Console/Kernel.php`, la planification
vit directement dans les routes console). Vérifier son exécution :

```bash
tail -f storage/logs/laravel.log | grep RefreshOverdueStatuses
```

## Backup

Script : `/usr/local/bin/ikoma-stock-backup.sh` (root, exécutable seul — `chmod 700`).
Identifiants MySQL dans `/root/.ikoma_stock_backup.cnf` (`chmod 600`, jamais en clair dans
le crontab ni dans le script, pour ne pas apparaître dans `ps aux`).

```bash
#!/bin/bash
set -euo pipefail

APP_DIR="/var/www/html/ikoma-stock"
BACKUP_DIR="/home/backups"
DATE=$(date +%Y%m%d)

mkdir -p "$BACKUP_DIR"

mysqldump --defaults-extra-file=/root/.ikoma_stock_backup.cnf ikomastock > "$BACKUP_DIR/db-$DATE.sql"
tar -czf "$BACKUP_DIR/storage-$DATE.tar.gz" -C "$APP_DIR" storage/app/public

find "$BACKUP_DIR" -type f -mtime +30 -delete
```

Rétention : 30 jours, purge automatique par le script lui-même.

## Process manager

Pas de Supervisor : `QUEUE_CONNECTION=sync` fait tourner les jobs (dont
`RefreshOverdueStatuses`) en synchrone dans le processus PHP-FPM qui les déclenche — rien à
superviser tant qu'aucune queue asynchrone n'est introduite. Si un besoin de queue
asynchrone apparaît, ajouter Supervisor + `QUEUE_CONNECTION=database` (ou `redis`) à ce
moment-là, pas avant.
