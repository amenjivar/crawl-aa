<h1>Web Crawler</h1>

Crawls a website given a single entry point to display:
- [x] Number of pages crawled
- [x] Number of unique images
- [x] Number of unique internal links
- [x] Number of unique external links
- [x] Average page load in seconds
- [x] Average word count
- [x] Average title length
- [x] A table showing each page crawled and the HTTP status code

MAJORITY OF WORK
-------------------

      controllers/SiteController    determines the entry point (if any)
      components/Crawler            the code used to crawl pages
      models/form/CrawlForm         form validation and executes the Crawler
      

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      components/         contains the crawler component
      config/             contains application configurations
      controllers/        contains Web controller classes
      models/             contains the CrawlForm model
      runtime/            contains files generated during runtime
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources

INSTALLATION
------------

### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project template using the following command:

~~~
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
~~~

Now you should be able to access the application through the following URL, assuming `basic` is the directory
directly under the Web root.

~~~
http://localhost/basic/web/
~~~

### Install with Docker

Update your vendor packages

    docker-compose run --rm php composer update --prefer-dist
    
Run the installation triggers (creating cookie validation code)

    docker-compose run --rm php composer install    
    
Start the container

    docker-compose up -d
    
You can then access the application through the following URL:

    http://127.0.0.1:8000

**NOTES:** 
- Minimum required Docker engine version `17.04` for development (see [Performance tuning for volume mounts](https://docs.docker.com/docker-for-mac/osxfs-caching/))
- The default configuration uses a host-volume in your home directory `.docker-composer` for composer caches
