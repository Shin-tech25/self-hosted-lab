#!/bin/bash

cd /opt

cd growi/
docker compose down
cd -
cd keycloak/
docker compose down
cd -
cd redmine/
docker compose down
cd -
cd nextcloud/
docker compose down
cd -
cd jupyterhub/
docker compose down
cd -
cd desktop/
docker compose down
cd -
cd https-portal
docker compose down
cd -