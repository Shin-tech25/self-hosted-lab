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

echo 'Starting nextcloud...'
cd nextcloud/
docker compose up -d
cd -
cd https-portal/
docker compose restart
echo 'Started nextcloud.'
cd -

echo 'Starting jupyterhub...'
cd jupyterhub/
docker compose up -d
cd -
cd https-portal/
docker compose restart
echo 'Started jupyterhub.'
cd -

echo 'Starting desktop...'
cd desktop/
docker compose up -d
cd -
cd https-portal/
docker compose restart
echo 'Started desktop.'
cd -