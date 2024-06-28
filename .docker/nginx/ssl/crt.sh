#!/bin/bash
## Get configuration parameters
source ../../.env
cp v3.ext.default v3.ext

if [ -z "$HOSTS" ]; then echo 'ERROR: No hosts configured into .env file'; exit 1; fi; 

IFS=',' read -ra HOSTS_ARRAY <<< "$HOSTS"
I=1;
for HOST in "${HOSTS_ARRAY[@]}"; do
  echo "DNS.$I = $HOST" >> v3.ext;
  ((I=I+1))
done

DOMAIN="*.local"

FILENAME=$(echo "${DOMAIN//[^[:alnum:]]/_}" | sed 's/^_*//')

# Create the certificate key
openssl genrsa -out $FILENAME.key 2048

# Create the signing (csr)
openssl req -new -sha256 -key $FILENAME.key \
-subj "/C=HU/ST=Budapest/L=Budapest/O=ACME/OU=ACME Inc/emailAddress=wh@local/CN=$DOMAIN" \
-config <(cat /etc/ssl/openssl.cnf) \
-out $FILENAME.csr

# Verify the csr's content
openssl req -in $FILENAME.csr -noout -text

# Generate the certificate using the csr and key along with the CA Root key
openssl x509 -req -in $FILENAME.csr -CA rootCA.crt -CAkey rootCA.key -CAcreateserial -days 500 -sha256 -extfile v3.ext -out $FILENAME.crt

# Verify the certificate's content
openssl x509 -in $FILENAME.crt -text -noout
