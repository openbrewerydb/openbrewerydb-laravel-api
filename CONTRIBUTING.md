# Contributing to Open Brewery DB API

First off, thank you for considering contributing! We love our community and welcome any help we can get. This document provides guidelines for contributing to the project.

### Table of Contents

-   [Getting Started](#getting-started)
-   [Code Style & Linting](#code-style--linting)
-   [Running Tests](#running-tests)
-   [Pull Request Process](#pull-request-process)

### Getting Started

The project is built with Laravel and runs in a Docker environment managed by Laravel Sail. The only prerequisite is [Docker](https://www.docker.com/).

1.  **Fork the repository** on GitHub.
2.  **Clone your fork** locally: `git clone https://github.com/openbrewerydb/openbrewerydb-laravel-api.git`
3.  **Navigate to the project directory**: `cd openbrewerydb-laravel-api`
4.  **Run the installation script**: `./install.sh`. This will build the Docker containers, install Composer dependencies, and set up your `.env` file.
5.  **Create a new branch** for your feature or bug fix: `git checkout -b your-feature-name`
6.  **Import the data**:
    -   `./vendor/bin/sail artisan app:import-breweries`
    -   `./vendor/bin/sail artisan app:refresh-search-indexes`

You are now ready to start making changes!

### Code Style & Linting

We use [Laravel Pint](https://laravel.com/docs/11.x/pint) to ensure a consistent code style throughout the project. Before committing your changes, please format your code by running:

```bash
./vendor/bin/sail pint
```

The CI pipeline will fail if there are any code style violations, so running this command locally will save you time.

### Running Tests

We use Pest for testing. All contributions must be accompanied by passing tests. If you are adding a new feature, you must add tests for it.

To run the entire test suite, execute:

```bash
./vendor/bin/sail artisan test
```

To generate a code coverage report, you can use the helper script:

```bash
./run-tests-with-coverage.sh
```

The HTML report will be available in the coverage/html directory.

### Pull Request Process

Ensure that your code is formatted (`./vendor/bin/sail pint`) and that all tests are passing (`./vendor/bin/sail artisan test`).

Commit your changes with a clear and descriptive commit message.

Push your branch to your forked repository: `git push origin your-feature-name`

Open a Pull Request to the main branch of the original repository.

In your pull request description, please explain the changes you made and why. Use the pull request template provided (`.github/pull_request_template.md`) as a guide. Reference any related issues (e.g., "Fixes #123").

A project maintainer will review your PR, provide feedback, and merge it if everything looks good.
