FROM quay.io/keycloak/keycloak:latest

# 必要な環境変数を設定
ENV KEYCLOAK_ADMIN=admin \
    KEYCLOAK_ADMIN_PASSWORD=adminpassword \
    KC_HOSTNAME_URL=https://auth.mshin0509.com \
    KC_PROXY=edge \
    KC_HTTP_ENABLED=false \
    KC_HTTPS_CERTIFICATE_FILE=/etc/x509/https/tls.crt \
    KC_HTTPS_CERTIFICATE_KEY_FILE=/etc/x509/https/tls.key

# TLS証明書をコピー (自己署名証明書の場合)
COPY tls.crt /etc/x509/https/tls.crt
COPY tls.key /etc/x509/https/tls.key

# Keycloak を HTTPS で起動
ENTRYPOINT ["/opt/keycloak/bin/kc.sh", "start"]
