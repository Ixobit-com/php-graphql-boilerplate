# GraphQL API Boilerplate

## Background and Problem Statement
Simple template for quick start GraphQl API

# System requirements
* PHP 8.2+
  * ext-ctype
  * ext-iconv
* MySQL 8

## Installation


## Configuration

* GraphQL library settings: `config/packages/graphql.yaml`
* Settings format: Attributes (https://github.com/overblog/GraphQLBundle/blob/master/docs/attributes/index.md)
* API scheme divide by logical parts. Any part have dedicated entry point (`/api/graphql/<schema>`). I.e. `/api/graphql/user`.
* Queries and Mutations realized as Symfony service and placed in `src/Service/GraphQL`.
* Input types, Output types, DTO, other types - `src/GraphQL`

## Authorization

* JWT token authorization implemented

## Access control

* Basic access rights can be checked by framework using Symfony security mechanism (https://symfony.com/doc/current/security.html).
* On the API level security checking use GraphQL bundle "#[Access]" attribute (https://github.com/overblog/GraphQLBundle/blob/master/docs/attributes/attributes-reference.md#access)
* Validation in DTO implements utilise #[Assert] Symfony validation mechanism (https://symfony.com/doc/current/validation.html) 

## Links
https://github.com/overblog/GraphQLBundle \
https://github.com/lexik/LexikJWTAuthenticationBundle \
https://github.com/markitosgv/JWTRefreshTokenBundle