#!/bin/bash

cd /opt

echo 'Starting keycloak...'
cd keycloak/
docker compose up -d
cd -
cd https-portal/
docker compose up -d
echo 'Started keycloak.'
cd -

echo 'Waiting for 180 seconds to boot up keycloak.'
sleep 180

echo 'Starting growi...'
cd growi/
docker compose up -d
cd -
cd https-portal/
docker compose restart
echo 'Started growi.'
cd -

echo 'Starting redmine...'
cd redmine/
docker compose up -d
cd -
cd https-portal/
docker compose restart
echo 'Started redmine.'
cd -
