#!/bin/bash
## Output styles
txtyel=$(tput setaf 3)    # Yellow
txtgrn=$(tput setaf 2)    # Green
txtred=$(tput setaf 1)    # Red
txtrst=$(tput sgr0)       # Text reset

## Get configuration parameters
source .env

docker compose run php-fpm ./bin/console graphql:dump-schema --schema=user --classic --format=graphql --file=./var/schema.gql
docker compose run nodejs npx spectaql ./config/packages/spectacul_config.yml

exit 0;
