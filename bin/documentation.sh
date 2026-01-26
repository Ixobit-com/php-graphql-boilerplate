#!/usr/bin/env bash

## Generate documentation in ./public/documentation folder broken by schemas
## API Schemas
declare -a arr=("auth" "user")

mkdir -p ./documentation ./public/documentation ./schema ./public/schema
npm install

echo "" > ./documentation/index.html
cat << EOF > ./documentation/index.html
<!doctype html>
<html>
    <meta charset=utf-8>
    <head>
<style type="text/css">
ul { overflow-x:hidden; white-space:nowrap; width: 100%; }
li { display:inline; margin-left: 2em;}
</style>
    </head>
    <title>GraphQL API Documentation</title>
    <body>
        <h1>API Documentation</h1>
        <table><tr><td><strong>Schemas:</strong></td><td><ul>
EOF

for schema in "${arr[@]}"
do
    ./bin/console graphql:dump-schema --schema=$schema --classic --format=graphql --file=./schema/$schema.gql
    npx spectaql --schema-file ./schema/$schema.gql --target-dir ./documentation/$schema/ -c ./config/spectaql.yaml
    cat << EOF >> ./documentation/index.html
        <li><strong><a href='$schema/index.html' target='doc-content'>$schema</a></strong></li>
EOF
done

cat << EOF >> ./documentation/index.html
        </ul></td></tr></table>
        <iframe src="auth/index.html" name="doc-content" onload="javascript:(function(o){o.style.height=o.contentWindow.document.body.scrollHeight+&quot;px&quot;;}(this));" style="height: 7402px; width: 100%; border: none; overflow: hidden;"></iframe>
    </body>
</html>
EOF

rm -rf ./public/documentation/*
mv ./documentation/* ./public/documentation/
rm -rf ./documentation

rm -rf ./public/schema/*
mv ./schema/* ./public/schema/
rm -rf ./schema

exit 0;
