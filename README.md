# GraphQL API Boilerplate

## Background and Problem Statement
Simple template for quick start GraphQl API

## What is in the box
* Basic Symfony project with Doctrine ORM
* JWT authentication + JWT refresh tokens support
* GraphQL bundle with attribute-annotated entities
* Data model:
  * User - minimal data - for authentication only
  * Profile - additional user info
  * refresh_tokens - refresh tokens storage
* Test and code coverage by phpunit
* Code style checking by phpcs
* I18N translations for any messages

# System requirements
* PHP 8.2+
  * ext-ctype
  * ext-iconv
* MySQL 8
* composer

## Docker environment (optional)
Docker environment configure for development purposes only, and include some additional tools (like npm, xdebug bash etc...)
* Copy .docker/.env.dist to .docker/.env (`cp .docker/.env.dist .docker/.env`)
* Set required variables (USER_ID, GROUP_ID, HOSTS etc.) in .env
* Run ./start.sh (this script start or restart full environment)
* Add ./docker/nginx/ssl/rootCA.crt into trusted authorities in your browser. (Now you have HTTPS for *.local domains).

## Installation
* deploy as ordinary Symfony project
* setup commands:
  * `cp .env .env.local` or `cp .env.docker .env.local` for docker environment
  * `cp .env.test.docker .env.test.local` for tests in docker environment
  * Edit .env.local, .env.test.local as you wish.
  * `composer install`
  * `./bin/console doctrine:migrations:migrate` (add --env=test for test environment)
  * `./bin/console doctrine:fixtures:load` (add --env=test for test environment)
     Password for all users is: "password", predefined users are: user, admin, superadmin
  * `./bin/console lexik:jwt:generate-keypair`

## Configuration
* GraphQL's library settings: `config/packages/graphql.yaml`
* Settings format: Attributes (https://github.com/overblog/GraphQLBundle/blob/master/docs/attributes/index.md)
* API scheme divide by logical parts. Any part have dedicated entry point (`/api/graphql/<schema>`). I.e. `/api/graphql/user`.
* Queries and Mutations realized as Symfony service and placed in `src/Service/GraphQL`.
* Input types, Output types, DTO, other types - `src/GraphQL`

## Authorization
* JWT token authorization + JWT refresh token implemented

## Access control
* Basic access rights can be checked by framework using Symfony security mechanism (https://symfony.com/doc/current/security.html).
* On the API level security checking use GraphQL bundle "#[Access]" attribute (https://github.com/overblog/GraphQLBundle/blob/master/docs/attributes/attributes-reference.md#access)
* Validation in DTO implements utilise #[Assert] Symfony validation mechanism (https://symfony.com/doc/current/validation.html) 

## Tests
* Run `composer test` for tests
* Coverage report will be generated in public/coverage/index.html

## Code style
* Use `composer phpcs` for code style check and fix.

## API Documentation
* Documentation generate automatically (use `./bin/documentation.sh` script in docker environment)
* Main page will be accessible at https://graphql.local/documentation/index.html
* Also, GraphQL schemas in .gql files are stored in the public/schema directory

## Links
https://github.com/overblog/GraphQLBundle \
https://github.com/lexik/LexikJWTAuthenticationBundle \
https://github.com/markitosgv/JWTRefreshTokenBundle