 # INSTRUCTION
- buka terminal ketikkan
```php
git clone git@github.com:djambred/ksi.git
atau
git clone https://github.com/djambred/ksi.git
```
- masuk ke dalam folder yang telah di clone
```php
cd ksi
docker compose up -d --build
docker exec -it ksi bash
composer install
mv .env.example .env
php artisan storage:link
php artisan key:generate
exit 
code .
```
- edit file .env
```php 
APP_TIMEZONE='Asia/Jakarta'
APP_URL=http://localhost
ASSET_URL=http://localhost
DB_CONNECTION=mariadb
DB_HOST=db_ksi
DB_PORT=3306
DB_DATABASE=ksi_uts
DB_USERNAME=root
DB_PASSWORD=p455w0rd
```
- masih didalam container (docker exec -it ksi bash)
```php
chown -R www-data:www-data storage/*
chown -R www-data:www-data bootstrap/*
php artisan migrate:fresh --seed
php artisan project:init
```
- buka browser dan akses localhost
```php
user: admin@example.com
pass: password
```
- buka postman dan lakukan get ke localhost/api/products
- set authentication in postman to bearer
