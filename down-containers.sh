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
cd https-portal
docker compose down
cd -