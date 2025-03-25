#!/bin/bash

if [ ! -d init ]; then
    mkdir -p init
fi

curl -L https://downloads.apache.org/guacamole/1.5.3/binary/guacamole-auth-jdbc-1.5.3.tar.gz \
  | tar -xz --strip-components=3 -C init guacamole-auth-jdbc-1.5.3/mysql/schema
