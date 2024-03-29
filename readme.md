Mirin.cz web site
=============

It's PHP site based on [Nette](https://nette.org) web framework.
Development is based on docker in VirtualBox guest Ubuntu server machine.

Requirements
------------
PHP 5.6 or higher, Apache, Docker for development

Works on Debian 12 (bookworm) with Apache, MariaDb from distribution.
- latest old PHP 7.4 [installed](https://d0m.me/2023/10/01/debian-12-bookworm-install-php-7-4/) from deb.sury.org backports 👍 - libapache2-mod-php7.4 for Apache
- apt-get install php7.4-mysql
- apt-get install php7.4-intl
 
Docker usage
------------
Docker is used for easy cross machine and operating system development. 
Docker setup is in the ```docker``` directory. There are docker images
and one bash script - ```manage.sh``` for easy managing containers. There are two containers
one for the web, one for the database administration. There are Dockerfiles
for these containers.
For help use ```manage.sh --help```. Common operations

    # go to docker directory
    $ cd docker
    
    # builds the image for web container
    $ manage.sh buildImage web 
    
    # builds the image for database administration container
    $ manage.sh buildImage dbm
    
    # just call the docker-compose for our project, prints the
    # all docker-compose help
    $ manage.sh compose
    
    # stars the web container, one or all project containers
    $ manage.sh compose up -d mirin.cz
    $ manage.sh compose up -d
    
    # stop all containers
    $ manage.sh compose stop
    
    # call the composer install inside web container
    $ manage.sh exec mirin.cz composer install
    
    #  import dump into database
    $ manage.sh exec db bash -c "mysql -u root -plocaldb mirin < /usr/src/dbumps/mirin.sql"
    
As you can see, ```compose``` sub command simple calls the anything with ```docker-compose```

Containers support development for inside native linux host, and inside
VirtualBox linux guest virtual machine inside Windows.
Virtual machine has the shared folders and host only network as the
connection with the Windows host. See one of my Gist - virtualBoxSetup.md - 
for the setup of the VirtualBox guest machine.

Webs are accessible in the Windows on the guest box VM, it's mostly 192.168.56.2, or another IP
which is setup as host only network interface for guest VM. It's necessary
to add the ```www.mirin.dkl``` into the Windows hosts file because container web
is the name based virtual host.
Database administration is directly on the 192.168.56.2:8081 url

Settings for temp, log directory
-------
Use setgid bit and appropriate group for the write access for developer and web server.

    $ cd appdir
    $ find temp -type d | xargs chmod -c 2775
    # with sudo e.g
    $ find temp -type d | xargs sudo chmod -c 2775
    
    $ find temp -type f | xargs chmod -c 664
    $ chown -Rc www-data:developers temp 
    
    # maybe .htaccess can be writtable for owner only,
    # but maybe for developers also, it's up to you
    $ find temp -name ".htaccess" | xargs chmod -c 644

    $ chmod -c 2775 log
    $ chmod -Rc 664 log/*
    $ chown -Rc www-data:developers log 
    
It's necessary to call these under root.
When new directory under ```temp``` is created, it's necessary create it with the permissions ```2775```
for correct setgid bit usage.

Settings for rotate logs
-------
App is installed e.g. in ```/var/www/html/mirin.cz```, so add this one into ```/etc/logrotate.d/mirin.cz```

    /var/www/mirin.cz/log/*.log /var/www/mirin.cz/log/*.html {
        rotate 7
        weekly
        compress
        missingok
    }

Then check the ```/var/lib/logrotate/status```

License
-------
[GPL 3](https://www.gnu.org/licenses/gpl-3.0.en.html)
