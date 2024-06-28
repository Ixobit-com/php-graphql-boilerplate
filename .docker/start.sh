#!/bin/bash
## Output styles
txtyel=$(tput setaf 3)    # Yellow
txtgrn=$(tput setaf 2)    # Green
txtred=$(tput setaf 1)    # Red
txtrst=$(tput sgr0)       # Text reset

## Get configuration parameters
source .env

## current user - will use in containers
export UID

## Configure SSL certs for domains in .env
if ! [ -f ./nginx/ssl/v3.ext ]; then
  cd ./nginx/ssl/ && ./crt.sh && cd ../../
fi

## Run command in php-fpm container
function dc_run() {
  if [ ! $# -eq 1 ]; then echo 'ERROR: use dc_run "command"'; exit 1; fi

  docker compose exec php-fpm sh -c "cd /var/www/html/ && $1"

  if [ $? -eq 0 ]; then
    printf "\n%-120s %s\n" "Command run: ${txtyel}$1${txtrst} [${txtgrn}Ok${txtrst}]"
  else
    printf "\n%-120s %s\n" "Command run: ${txtyel}$1${txtrst} [${txtred}Error${txtrst}]"
    exit $?
  fi
}

## Down containers before start
docker compose down
if [ $? -ne 0 ]; then printf "\n%-120s %s\n" "${txtred}Error${txtrst}"; exit 1; fi

## Run php-fpm container
docker compose up --build -d php-fpm
printf "\n%-120s %s\\nn" "PHP-FPM server start [${txtgrn}Ok${txtrst}]"

#echo "$(docker network inspect ${COMPOSE_PROJECT_NAME}_${NETWORK_NAME})"; # --format='{{range .Containers}}{{if eq .Name "nginx"}}{{ .IPv4Address }}{{end}}{{end}}')"
#exit 0;
## Run MySQL container
docker compose up --build -d mysql
printf "%-120s %s\n\n" "Starting MySQL server"
## Wait MySQL response at 3306 port
dc_run "while ! nc -z $MYSQL_HOST 3306 > /dev/null 2>&1; do sleep 1 && echo -n .; done;"
printf "\n%-120s %s\n\n" "MySQL server start [${txtgrn}Ok${txtrst}]"

docker compose up --build -d nginx
printf "%-120s %s\n\n" "Webserver start [${txtgrn}Ok${txtrst}]"

docker compose up --build -d mail
printf "%-120s %s\n\n" "Mailserver start [${txtgrn}Ok${txtrst}]"

## Configure hosts in php-fpm container
WEB_IP="$(docker network inspect ${COMPOSE_PROJECT_NAME}_${NETWORK_NAME} --format='{{range .Containers}}{{if eq .Name "nginx"}}{{ .IPv4Address }}{{end}}{{end}}' | sed 's/\/[[:digit:]]*//g')";
IFS=',' read -ra HOSTS_ARRAY <<< "$HOSTS"
for HOST in "${HOSTS_ARRAY[@]}"; do
  dc_run "sudo -- sh -c -e \"echo '$WEB_IP $HOST' >> /etc/hosts\"";
done
printf "%-120s %s\n\n" "Hosts configured:$WEB_IP $HOSTS [${txtgrn}Ok${txtrst}]"

dc_run "sudo -- sh -c -e \"update-ca-certificates\"";
printf "%-120s %s\n\n" "CA Certificates updated: [${txtgrn}Ok${txtrst}]"

## Show running containers
printf "%-120s %s\n\n" "Containers ready to work: [${txtgrn}Ok${txtrst}]"
docker ps --format "{{.Names}}:\t{{.Status}}\t{{.Ports}}"

exit 0;
