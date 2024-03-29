# Intended use

This is a CMS, originally designed for https://vvz.phil.tu-dresden.de, that manages users and roles and allows to edit certain pages.

# Setup

For setup, you need a Linux-Webserver with PHP7+, MySQL/MariaDB/Percona and access to the file system.

You need to create a file in

> /etc/dbpw

containing only one line: the DB-password.

You can then go on by defining the database you want to use in 

> mysql.php

See and change the line

> $GLOBALS['dbname'] = 'basecms';

to what is appropriate to your use.

Then, 

> touch new_setup

in the main directory of this program. Run it in your browser. It will automatically generate all the databases needed and ask
you for administrator username and password.

Once this is set up, you can delete the new_setup again and login into the web interface.

# Conventions for subpages

## How to create a new subpage

First, create a .php-file in ./pages/, then, as administrator, go to "System" -> "Seiten" and create the new page. You can specificy
which user roles can access that site, too, and add additional information.

## How to do queries

If you want to execute a query, it is strong recommended that you use 

> rquery($query)

instead of any normal PHP-query-functions. This way, the system can be kept clean.

This way, the queries will show up to registered administrator-accounts in the "Query Analyzer" at the bottom of the page.

# Language support

Right now, the BaseCMS is only available in german.

# Screenshot


![BaseCMS](startseite.png?raw=true "BaseCMS")
