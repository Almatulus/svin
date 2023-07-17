MYCRM.KZ
======

Site made with [Yii 2](http://www.yiiframework.com/), [PostgreSQL](https://www.postgresql.org), [Redis](https://redis.io).
Basic template is used for application.

Setup guide
-----------

**Run these commands:**  
`npm install`
`npm install -g bower`  
`composer self-update`
`composer global require "fxp/composer-asset-plugin:~1.2.0"`
`composer install`  

**Database import**  
install php pgsql extension `sudo apt install php7.0-pgsql`  
create database `CREATE DATABASE db_mycrm`
create `db_user_mycrm` in your PostgreSQL database using command `CREATE USER db_user_mycrm WITH PASSWORD 'password';`      
grant privileges for user `db_user_mycrm` using command `GRANT ALL PRIVILEGES ON DATABASE db_mycrm TO db_user_mycrm;`  
and use it in `.env` config  
import initial database from specified file(link will be provided)  
here is an example script for import
`sudo -u postgres psql db_name < 'file_path'`  
then run migrations
`php yii migrate`  

**Redis**  
Install Redis by this [guide](https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-redis-on-ubuntu-16-04)

**Queue**
Run `yii queue/listen` via supervisor or systemd, [more info](https://github.com/yiisoft/yii2-queue/blob/master/docs/guide-ru/driver-redis.md).

**Config:**  
create `.env` in `root directory` (take as basis `.env.example`)

**Configure nginx:**  
Here is working nginx configuration:

```
server {
    set $project_root /var/www/mycrm;
    set $fcgi_server unix:/run/php/php7.1-fpm.sock;

    charset utf-8;
    client_max_body_size 128M;

    listen 80;
    server_name shop.dev;
    root $project_root/frontend/web;
    index index.php;

    #access_log  /var/log/nginx/mycrm.kz.access.log  main;
    #error_log   /var/log/nginx/mycrm.kz.error.log  main;
   

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location /api {
        try_files $uri $uri/ /api/index.php$is_args$args;
    }

    location ~* \.php$ {
        try_files $uri =404;
        fastcgi_pass $fcgi_server;
        include fastcgi.conf;
    }

    location ~* \.(htaccess|htpasswd|svn|git) {
        deny all;
    }

    location ~* ^.+\.(?:css|cur|js|jpe?g|gif|htc|ico|png|html|otf|ttf|eot|woff|svg)$ {
        access_log off;
        expires 30d;
        ## No need to bleed constant updates. Send the all shebang in one
        ## fell swoop.
        tcp_nodelay off;
        ## Set the OS file cache.
        open_file_cache max=3000 inactive=120s;
        open_file_cache_valid 45s;
        open_file_cache_min_uses 2;
        open_file_cache_errors off;
    }}  
```  

**Or configure apache:**  
Here is working `.htaccess` file, place it in the root folder

```
Options FollowSymLinks
AddDefaultCharset utf-8

<IfModule mod_rewrite.c>
    RewriteEngine On

    # the main rewrite rule for the frontend application
    RewriteCond %{REQUEST_URI} !^/(backend/web|backend|customer/web|customer/web|customer|provider/web|provider)
    RewriteRule !^frontend/web /frontend/web%{REQUEST_URI} [L]

    # redirect to the page without a trailing slash (uncomment if necessary)
    #RewriteCond %{REQUEST_URI} ^/api/$
    #RewriteRule ^(api)/ /$1 [L,R=301]
    # the main rewrite rule for the backend application
    RewriteCond %{REQUEST_URI} ^/api
    RewriteRule ^api(.*) /api/web/$1 [L]

    # if a directory or a file of the frontend application exists, use the request directly
    RewriteCond %{REQUEST_URI} ^/frontend/web
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # otherwise forward the request to index.php
    RewriteRule . /frontend/web/index.php [L]

    # if a directory or a file of the api application exists, use the request directly
    RewriteCond %{REQUEST_URI} ^/api/web
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # otherwise forward the request to index.php
    RewriteRule . /api/web/index.php [L]

    RewriteCond %{REQUEST_URI} \.(htaccess|htpasswd|svn|git)
    RewriteRule \.(htaccess|htpasswd|svn|git) - [F]
</IfModule>
```
  
and add this `.htaccess` file to `web` folder of each app  

```
RewriteEngine on

# if a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# otherwise forward it to index.php
RewriteRule . index.php
```

RUN TEST
-------------------
"для тестов используем отдельную пустую базу (все таблицы должны быть пустые кроме `crm_auth_*` и `crm_images` (и `crm_migration`)).
C корневой папки проета запускаешь `./vendor/bin/codecept run`"
Ануар ©

Запустить миграцию  
https://github.com/nhkey/yii2-activerecord-history  
`php yii migrate --migrationPath=@vendor/nhkey/yii2-activerecord-history/migrations`

BEST PRACTICES
-------------------
1) yii\behaviors\BlameableBehavior
Sets up created_user_id and updated_user_id.
http://www.yiiframework.com/doc-2.0/yii-behaviors-blameablebehavior.html
2) yii\behaviors\TimestampBehavior
Sets up created_time and updated_time.
http://www.yiiframework.com/doc-2.0/yii-behaviors-timestampbehavior.html
3) la-haute-societe/yii2-save-relations-behavior
Automatically validate and save related Active Record models.
https://github.com/la-haute-societe/yii2-save-relations-behavior
4) yii2tech/ar-softdelete
This extension provides support for ActiveRecord soft delete.
https://github.com/yii2tech/ar-softdelete
5) Use deleted_at instead of deleted status and isDeleted attribute

DIRECTORY STRUCTURE
-------------------

```
api
    config/              contains api configurations
    modules/             
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    web/                 contains the entry script and Web resources
common
    bootstrap            
    components
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    messages             contains translation files
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    runtime/             contains files generated during runtime
core
    calculators/
    forms/
    helpers/
    models/
    rbac/
    repositories/
    services/
    tests/
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    modules/
    runtime/             contains files generated during runtime
    search/
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```


ERROR HANDLING
-------------------

For errors in domain layer DomainException should be thrown. In API domain exceptions has to be caught and thrown as 
ServerErrorException with message from domain error.


RUN AFTER THE SERVER RESTARTED
-----------------------------
- Redis queue
`cd /home/ubuntu/hosts/crm_mycrm_prod/`
`php ./yii queue/listen &`
- Nodejs listener
`cd /home/ubuntu/hosts/socket.mycrm.kz`
`rm output.log`
`nodejs server.js > output.log &`
- PDF converter
`/opt/openoffice4/program/soffice "-accept=socket,host=127.0.0.1,port=2002,tcpNoDelay=1;urp;" -headless -nodefault -nofirststartwizard -nolockcheck -nologo -norestore &`


DEPLOY A BUGFIX
-------------------
Following steps to fix a bug:
- Merge the master branch with the bugfix
- Push master to repository
- Run in terminal
`ssh root@mycrm.kz`
`cd /home/ubuntu/hosts/crm_mycrm_prod && sudo su ubuntu && git pull && php ./yii migrate --interactive=0`
