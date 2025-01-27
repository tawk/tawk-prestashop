Prestashop
============

Docker container for Prestashop.

## Information
- Prestashop Versions: 1.7.6, 1.7.7, latest
- MySQL Version: 5.7

## Pre-Requisites

- install docker-compose [http://docs.docker.com/compose/install/](http://docs.docker.com/compose/install/)

## Usage

Start the container:

- ```docker-compose up```

Stop the container:

- ```docker-compose stop```

Destroy the container:

- ```docker-compose down```

Start the container of a specific version:

- ```docker-compose --env-file envs/<env-file> up```

Stop the container of a specific version:

- ```docker-compose --env-file envs/<env-file> stop```

Destroy the container of a specific version:

- ```docker-compose --env-file envs/<env-file> down```

## Plugin setup
You can follow the instruction in [Prestashop Github Repo](https://github.com/tawk/tawk-prestashop).

## Accessing Admin Page
To access the admin page, go to `localhost:<prestashop-version-port>/admin_ps`. Here's the login credentials:
- username: admin@example.com
- password: adminps
