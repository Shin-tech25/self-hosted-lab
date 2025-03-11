#!/bin/bash

cd /opt

cd growi/
docker compose up -d
cd -
cd keycloak/
docker compose up -d
cd -
cd redmine/
docker compose up -d
cd -
cd https-portal
docker compose up -d
cd -
