FROM jupyterhub/jupyterhub:latest

# ローカルの requirements.txt をイメージにコピー
COPY requirements.txt .

# pip のアップデート
RUN pip install --upgrade pip

# パッケージをインストール
RUN pip install --no-cache-dir -r requirements.txt

# 内部で利用するポート (https-portal 経由でアクセス)
EXPOSE 8000

CMD ["jupyterhub", "--config", "/srv/jupyterhub/jupyterhub_config.py"]
