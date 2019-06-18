# EnDouble (API Normaliser)

The application allows one to create a API service with a standardised interface to 3rd Party API. 

In order to ensure this normalising proxy is not too onerous on the 3rd Party APIs, 
it uses a caching mechanism to store the data in a local MySql table.  Currently the system will check the remote API for the latest index on each call and only update the cache with the new items if there is a change,
this introduces a second or so lag to the query. 
This could be altered to update via a cron schedule to reduce the "latency", bandwidth and resources usage, 
but this would obviously come at the expense of the freshness of the results. 

The system is written to allow you to swap out the transport layer, it currently uses [GuzzleHttp PSR7](http://docs.guzzlephp.org/en/stable/psr7.html)
 with multiple concurrent connections to the source APIs to improve cache update speeds. 
You can also swap out the caching later which is currently configured to use Laravel's Eloquent(DB ORM). 

You can see a live DEMO of this system at: https://endouble.ajarti.com

## Getting Started
I would suggest making use of [Laravel Forge](https://forge.laravel.com/) to smooth this process. 
Alternatively you can get your Sysop team to provide you a server running with the services specified below in 
prerequisites.


### Prerequisites

To get this application up and running you will need an [Nginx](http://nginx.org/en/docs/install.html) server running with 
[PHP7.2+](https://www.php.net/manual/en/install.unix.nginx.php), [Composer](https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md),
[Mysql](https://dev.mysql.com/doc/refman/8.0/en/installing.html) or [MariaDB](https://mariadb.com/kb/en/library/getting-installing-and-upgrading-mariadb/) database server,
and [Laravel 5.8+](https://laravel.com/docs/5.8).  

### Installing

1.  You will need the following from your Sysops team
- The IP, username and password to SSH to your webserver instance.
- The IP, username and password to your MySql instance.
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

You should now be able to navigate to your new site. The home page will have links to the 2 sample feeds.

## Adding new feeds.

Given the variety of possible API interfaces and structures, it is impossible to provide a generic automated solution to add a new API to sources. 
Be sure to follow the file naming conventions.
In order to add new feeds to the system, you will need to:

1. Create a new Service.
- In \app\Services copy ComicsService.php for API's with content delivered one record at a time or 
copy ComicsService.php for APIs that deliver content in collections and alter as needed.


2. Add the service to  \app\Providers\AppServiceProvider.php
- Register the new service in the 'register' function using spaceService & comicService as a guide.


3. Create a new Transformer.
- in \app\Http\Resources copy and alter a pre-existing transformer to your needs. Mapping the required fields from the source API to the standard fields to ensure normalisation. N.B. the file should be named <slug>Transformer.

4. Set the config in your .env file.
- Add a new set of configs (using an existing source as a guide) to the .env file. Take note that the value of NEW-SOURCE_SLUG is what determines the source in the API query url as well as the naming convention of files. e.g. http://my.domain/api/query/[slug/source]?year=2019&limit=25&offset=25

5. Create a new record in the MySQL database "sources" table using existing records as a guide.


## Built With
Link provide installation instructions.

* Framework : [Laravel](https://laravel.com/docs/5.8)
* Package Manager : [Composer](https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md)
* Webserver : [Nginx](http://nginx.org/en/docs/install.html)
* Database  : [Mysql](https://dev.mysql.com/doc/refman/8.0/en/installing.html) or [MariaDB](https://mariadb.com/kb/en/library/getting-installing-and-upgrading-mariadb/) I suggest (utf8mb4 - charset, utf8mb4_unicode_ci collation for maximum language compatibility)


