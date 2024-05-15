# Setup (Docker)
The Docker image will automatically download tested versions of php, apache, mariadb and phpmyadmin:

- php:8.3-apache
- mariadb:10.11
- phpmyadmin:latest

It will also automatically configure them.

You'll still have to set the Database credentials: You can do so by changing the environment variables for mariadb in `/docker-compose.yml` and by modifying the variables in `/_database.php`. Just remember that these have to stay the same.  

There is also the need to configure write permissions for the `ext/images` folder. You can do so by running `$ chmod -R a+wr ext/images` on the host system.  This wasn't possible to automate, as the entire repo is mounted as a volume.