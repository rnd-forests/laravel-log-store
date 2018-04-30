# Laravel - API For Storing Logs

A simple approach to build API endpoints to store logs from mobile applications. These log instances are typically stored in MongoDB (or any other NoSQL databases of the choice).

## Installation

We use Docker and Docker Compose for constructing the development environment of the application. Therefore, we first need to install these two softwares:

- Install Docker: https://docs.docker.com/install
- Install Docker Compose: https://docs.docker.com/compose/install

Make sure `docker` and `docker-compose` commands are available in current shell.

In order to run `docker` command without **sudo** privilege:
- Create **docker** group if it hasn't existed yet: `sudo groupadd docker`
- Add current user to **docker** group: `sudo gpasswd -a ${whoami} docker`
- You may need to logout in order to these changes to take effect.

Change current directory to application code folder and run the following commands:
- Copy file `.env.example` to `.env`, `docker-compose.yml.example` to `docker-compose.yml`
- Start up docker containers: `docker-compose up -d`. To stop docker containers, using `docker-compose stop` command
- Change to workspace environment: `docker exec -it logger_workspace bash`

Inside workspace container, run the following commands:
- Install composer packages: `composer install --no-suggest`
- Install the application: `php artisan app:install`
- Change permission for some directories: `chmod -R 777 storage/ bootstrap/`
- Seed the database: `php artisan db:seed`
- Create Passport clients: `php artisan passport:install`

The default database credentials for different environments (database, username, password):
- Local environment: **homestead**, **homestead**, **secret**

By default, port 80 of NGINX container is mapped to port 8000 of the host machine. If this port is currently used by another application, you can change that port by editing `docker-compose.yml`.

We can access the application at address `0.0.0.0:8000`
