#!/bin/sh

set -e

APP_VERSION=develop
VERSION=latest

docker build --no-cache --build-arg APP_VERSION=${APP_VERSION} --tag=danskernesdigitalebibliotek/follow-searches:${VERSION} --file="follow-searches/Dockerfile" follow-searches
docker build --no-cache --build-arg VERSION=${VERSION} --tag=danskernesdigitalebibliotek/follow-searches-nginx:${VERSION} --file="nginx/Dockerfile" nginx

docker push danskernesdigitalebibliotek/follow-searches:${VERSION}
docker push danskernesdigitalebibliotek/follow-searches-nginx:${VERSION}
