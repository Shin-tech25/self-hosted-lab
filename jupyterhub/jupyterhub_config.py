import os
c = get_config()

# OIDC 認証
c.JupyterHub.authenticator_class = 'oauthenticator.generic.GenericOAuthenticator'

c.GenericOAuthenticator.client_id = os.getenv("OIDC_CLIENT_ID")
c.GenericOAuthenticator.client_secret = os.getenv("OIDC_CLIENT_SECRET")

c.GenericOAuthenticator.authorize_url = os.getenv("OIDC_AUTHORIZE_URL")
c.GenericOAuthenticator.token_url = os.getenv("OIDC_TOKEN_URL")
c.GenericOAuthenticator.userdata_url = os.getenv("OIDC_USERDATA_URL")

c.GenericOAuthenticator.username_key = 'preferred_username'
c.GenericOAuthenticator.oauth_callback_url = os.getenv("OIDC_CALLBACK_URL")

c.GenericOAuthenticator.extra_params = {"scope": "openid profile email"}

c.Authenticator.auto_login = True
# 環境変数からユーザーリストを取得し、カンマ区切りで分割
allowed_users_env = os.getenv("ALLOWED_USERS", "")
c.Authenticator.allowed_users = set(allowed_users_env.split(",")) if allowed_users_env else set()

# Spawner
c.JupyterHub.spawner_class = 'jupyterhub.spawner.LocalProcessSpawner'
