#!/bin/bash

## Generate documentation in ./public/documentation folder broken by schemas

## Get configuration parameters
source .env

declare -a arr=("auth" "user") ## API Schemas

echo "" > ../public/documentation/index.html
cat << EOF > ../public/documentation/index.html
<!doctype html>
<html>
    <meta charset=utf-8>
    <title>API Documentation</title>
    <body>
        <h1>API Documentation</h1>
        <ul>
EOF

for schema in "${arr[@]}"
do
    docker compose run php-fpm ./bin/console graphql:dump-schema --schema=$schema --classic --format=graphql --file=./var/$schema.gql
    docker compose run nodejs npx spectaql --schema-file ./var/$schema.gql --target-dir ./public/documentation/$schema/ -c ./config/packages/spectacul.yml
    cat << EOF >> ../public/documentation/index.html
        <li><strong><a href='./$schema/index.html'>$schema</a></strong></li>
EOF
done

cat << EOF >> ../public/documentation/index.html
        </ul>
    </body>
</html>
EOF

exit 0;
