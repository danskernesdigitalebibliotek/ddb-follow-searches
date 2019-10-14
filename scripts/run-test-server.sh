#!/bin/bash

export APP_ENV=testing
export APP_DEBUG=true
export ADGANGSPLATFORMEN_DRIVER=testing
export DB_CONNECTION=sqlite
export DB_DATABASE=/tmp/follow-search-db.sqlite

function cleanup {
        kill $PID
        rm -f $DB_DATABASE
}

trap cleanup INT TERM ERR
trap cleanup EXIT

# (Re-)create database.
./artisan migrate:fresh

php -S 0.0.0.0:8080 -t public &
PID=$!

wait
