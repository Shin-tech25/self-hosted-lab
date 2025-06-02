#!/bin/bash

# Nextcloud コンテナ名
CONTAINER_NAME=nextcloud

# ユーザーIDリストを取得（全ユーザーが対象）
USER_IDS=$(docker exec -u www-data "$CONTAINER_NAME" php /var/www/html/occ user:list --output=json \
    | jq -r 'keys[]')

# 各ユーザーに対して feed を取得し更新
for USER_ID in $USER_IDS; do
    echo "Fetching feeds for user: $USER_ID"

    FEED_IDS=$(docker exec -u www-data "$CONTAINER_NAME" php /var/www/html/occ news:feed:list -- "$USER_ID" \
        | jq -r '.[].id')

    for FEED_ID in $FEED_IDS; do
        echo "Updating feed $FEED_ID for user $USER_ID"
        docker exec -u www-data "$CONTAINER_NAME" php /var/www/html/occ news:updater:update-feed "$USER_ID" "$FEED_ID"
    done
done
