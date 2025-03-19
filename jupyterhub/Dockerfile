FROM jupyterhub/jupyterhub:latest

# OIDC 連携に必要な oauthenticator をインストール
RUN pip install oauthenticator

# シングルユーザーサーバー用にjupyterlabをインストール
RUN pip install jupyterlab

# 内部で利用するポート (https-portal 経由でアクセス)
EXPOSE 8000

CMD ["jupyterhub", "--config", "/srv/jupyterhub/jupyterhub_config.py"]
