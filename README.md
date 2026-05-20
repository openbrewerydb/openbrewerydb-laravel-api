# 🍻 Open Brewery DB API

[![Tests](https://github.com/alexjustesen/obdb-api/actions/workflows/ci.yml/badge.svg)](https://github.com/alexjustesen/obdb-api/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This is the official Laravel-powered backend for the Open Brewery DB API.

### Table of Contents

- [Features](#features)
- [API Documentation](#api-documentation)
- [Development Setup](#-developing)
- [Running Tests](#-running-tests)
- [Code Style](#-code-style)
- [Contributing](#-contributing)
- [License](#-license)

### Features

- **RESTful API**: A clean, modern API for accessing brewery data.
- **Powerful Search**: Full-text search powered by Meilisearch.
- **Extensive Filtering**: Filter breweries by city, state, country, postal code, type, name, and distance.
- **Flexible Sorting**: Sort results by multiple fields in ascending or descending order.
- **Pagination**: Simple and predictable pagination for all list endpoints.
- **Metadata Endpoint**: Get total counts of breweries aggregated by state and type.
- **Random Brewery**: Fetch a random brewery, perfect for discovery.

### API Documentation

Interactive API spec, Postman collection, and Swagger/OpenAPI are
available at [https://api.openbrewerydb.org/docs](https://api.openbrewerydb.org/docs).

Generated via [Scribe](https://scribe.knuckles.wtf/).

#### Running locally

Local docs can be viewed at http://localhost:8000/docs
From the root of the repo run:

```shell
./vendor/bin/sail artisan serve # Starts the server on port 8000
./vendor/bin/sail artisan scribe:generate # Generates the API docs after changes
```

### 🏃‍♂️ Developing

Open Brewery DB API utilizes [Laravel Sail](https://laravel.com/docs/11.x/sail) to create a Docker development environment. As a result, [Docker](https://www.docker.com/) is the only prerequisite to get started.

#### 🥇 First Time Setup

This will build the Docker container, install all dependencies, and set up your local environment.

1.  Clone the repository: `git clone https://github.com/openbrewerydb/openbrewerydb-laravel-api.git`
2.  Navigate into the project directory: `cd openbrewerydb-laravel-api`
3.  Run the install script: `./install.sh`

#### 🔄️ Continuing Development

After the first-time setup, you can manage the development environment with these commands:

- **Start the environment**: `./vendor/bin/sail up -d`
- **Stop the environment**: `./vendor/bin/sail down`

#### 👇 Importing Data

The database needs to be populated with brewery data from the official dataset.

1.  To import the latest brewery data, run: `./vendor/bin/sail artisan app:import-breweries`
2.  To refresh the search index after importing, run: `./vendor/bin/sail artisan app:refresh-search-indexes`

### 🧪 Running Tests

The application has a comprehensive test suite built with Pest.

- To run all tests, use: `./vendor/bin/sail artisan test`
- To run tests with code coverage, use the provided script: `./run-tests-with-coverage.sh` (The report will be generated in the `coverage/` directory).

### ✨ Code Style

This project uses [Laravel Pint](https://laravel.com/docs/11.x/pint) to enforce a consistent code style.

- To automatically format your code, run: `./vendor/bin/sail pint`

### 🤝 Contributing

We welcome contributions! Please see the [**CONTRIBUTING.md**](CONTRIBUTING.md) file for guidelines on how to get started.

### 📜 License

This project is open-sourced software licensed under the [MIT license](LICENSE).
