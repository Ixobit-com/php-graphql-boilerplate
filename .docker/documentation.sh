#!/bin/bash
## Output styles
txtyel=$(tput setaf 3)    # Yellow
txtgrn=$(tput setaf 2)    # Green
txtred=$(tput setaf 1)    # Red
txtrst=$(tput sgr0)       # Text reset

## Get configuration parameters
source .env

declare -a arr=("auth" "user")
for schema in "${arr[@]}"
do
    docker compose run php-fpm ./bin/console graphql:dump-schema --schema=$schema --classic --format=graphql --file=./var/$schema.gql
    docker compose run nodejs npx spectaql --schema-file ./var/$schema.gql --target-dir ./public/documentation/$schema/ -c ./config/packages/spectacul.yml
done

exit 0;
