FROM nextcloud:latest

# `ffmpeg` をインストール
RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg && \
    rm -rf /var/lib/apt/lists/*
