#!/usr/bin/env bash

docker-compose run --rm php bash -c "./wait-for-it.sh elasticsearch:9200 && php -dmemory_limit=-1 vendor/bin/phpunit \"$@\""
