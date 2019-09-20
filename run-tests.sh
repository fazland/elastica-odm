#!/usr/bin/env bash

docker-compose run --rm php php -dmemory_limit=-1 vendor/bin/phpunit "$@"
