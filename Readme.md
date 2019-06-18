# EnDouble (API Normaliser)

The application allows one to create a API service with a standardised interface to 3rd Party API.

## Getting Started
I would suggest making use of [Laravel Forge](https://forge.laravel.com/) to smooth this process. 
Alternatively you can get your Sysop to provide you a server running with the services specified below in 
prerequisites.


### Prerequisites

To get this application up and running you will need an [Nginx](http://nginx.org/en/docs/install.html) server running with 
[PHP7.2+](https://www.php.net/manual/en/install.unix.nginx.php), [Composer](https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md),
[Mysql](https://dev.mysql.com/doc/refman/8.0/en/installing.html) or [MariaDB](https://mariadb.com/kb/en/library/getting-installing-and-upgrading-mariadb/) database server,
and [Laravel 5.8+](https://laravel.com/docs/5.8).  

### Installing


1.  You will need the following from your Sysops team
- The IP, username and password to SSH to your webserver instance.
- The IP, username and password to you MySql instance.
- The root path of the folder served by your Nginx server.
2. Connect to your server with SSH and navigate to the Nginx folder for the website in question.
```
cd /folders/to/clone-into/
```

3. Clone this repo into that folder  (dont't forget the dot at the end).
```
git clone https://github.com/ajarti/endouble.git .
```

4. Make sure and required packages are installed.
```
composer install
```

5. Rename .env.example to .env and edit the file to insert the relevant details. Add the IP, Username & Password for the database. Set the APP_URL to your sites URL.
```
mv .env-example .env

```

6. Generate a new APP_KEY, this will be inserted in the .env file for you.
```
php artisan key:generate
```

7. Migrate the database and seed with the 2 sample feeds ([Space](https://api.spacexdata.com/v3/launches) & [XKCD](https://xkcd.com/info.0.json)).
```
php artisan migrate --seed
```


## Adding new feeds.

In order to add new feeds to the system, you will need to:

1.

2.

## Built With
Link provide installation instructions.

* Framework : [Laravel](https://laravel.com/docs/5.8)
* Package Manager : [Composer](https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md)
* Webserver : [Nginx](http://nginx.org/en/docs/install.html)
* Database  : [Mysql](https://dev.mysql.com/doc/refman/8.0/en/installing.html) or [MariaDB](https://mariadb.com/kb/en/library/getting-installing-and-upgrading-mariadb/) I suggest (utf8mb4 - charset, utf8mb4_unicode_ci collation for maximum language compatibility)


