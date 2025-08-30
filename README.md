## 🍻 Open Brewery DB API

This is a WIP/POC to move the API over to a Laravel backend.

### 🏃‍♂️ Developing

Open Brewery DB API utilizes [Laravel Sail](https://laravel.com/docs/11.x/sail) to create a Docker development environment. As a result [Docker](https://www.docker.com/) is the only requirement to get started.

### 🥇 First Time Setup

To get started developing follow the steps below after you've installed Docker, this will ensure a container is built and the necessary dependencies are installed.

1. Clone the repository `gh repo clone alexjustesen/obdb-api`
2. CD into the project `cd obdb-api`
3. Run the install script `./install.sh`

### 🔄️ Continuing developing

You only need to run "First Time Setup", the first time. After that you can use the core commands below.

- Start the development environment `./vendor/bin/sail up -d`
- Stop the development environment `./vendor/bin/sail down`
- Refresh the database structure `./vendor/bin/sail artisan migrate:fresh --force`
- Updating Dependencies `./vendor/bin/sail composer update`

### 👇 Importing Data

1. To import latest brewery data run `./vendor/bin/sail artisan app:import-breweries`
2. To refresh the search index run `./vendor/bin/sail artisan app:refresh-search-indexes`

### 📞 API Docs

Still working on 100% coverage but API specs can be found in the [wiki](https://github.com/alexjustesen/obdb-api/wiki/API) for now, Swagger docs are planned.
