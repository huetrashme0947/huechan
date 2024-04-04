# Huechan

> Sei wie ich<br>
> \>Öffnet Twitter und sieht das Musk es gekauft hat<br>
> \>Öffnet HeuChan<br>
> \>Schreibt hier<br>
> \>Neue Lieblingswebsite
> 
> &mdash; [Simdutz, 2022](https://huechan.com/x/e7352ed59e7b0a2b)

This repository stores the source code for my online imageboard platform Huechan. Please keep in mind that the purpose of this is to give you an overview of the inner workings of the website. While setting up your own server is technically possible, the source code is not written in a very modular style, meaning that it's designed to run inside a very specific server environment.

## Setup

The following software is required for setting up your own server:

- Apache 2.4.29
- PHP 8.2.5
- MariaDB 10.1.48

The given versions are the ones used by the production server, so are guaranteed to be working with the source code.

Apache (or the web server you're using) needs to be configured to route all requests as follows:

- `/ext/images/*` > `/ext/images/`
- `/*` > `/index.php`

For PHP, the default php.ini should work just fine. MariaDB needs to have an existing database (create a new empty one) and a user account for the backend to be using. Execute `/setup.sql` on your database, then you're database is good to go. Lastly, enter the connection and user credentials into `/_database.php`. Your server should now be set up and ready to be used.
