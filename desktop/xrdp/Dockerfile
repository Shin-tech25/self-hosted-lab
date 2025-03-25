FROM danielguerra/ubuntu-xrdp

# システムパッケージのアップデートと環境設定
RUN apt-get update && \
    apt-get install -y sudo locales language-pack-ja && \
    apt-get install -y tzdata && \
    ln -fs /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata

# 日本語表示用フォントのインストール
RUN apt-get update && \
    apt-get install -y fonts-noto-cjk

# Firefox 実行に必要なライブラリとツール群のインストール
RUN apt-get install -y wget libgtk-3-0 libdbus-glib-1-2 \
    libxt6 libx11-xcb1 libxcomposite1 libasound2

# 最新の Firefox をダウンロードして解凍
RUN wget -O /tmp/firefox.tar.xz "https://download.mozilla.org/?product=firefox-latest&os=linux64&lang=en-US" && \
    tar -xJf /tmp/firefox.tar.xz -C /opt && \
    rm /tmp/firefox.tar.xz && \
    ln -sf /opt/firefox/firefox /usr/bin/firefox

# テキストエディタやターミナルの追加
RUN apt-get install -y xterm gedit geany

# 不要ファイルの削除
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# ロケールの生成と環境変数の設定（日本語対応）
RUN locale-gen ja_JP.UTF-8
ENV LANG=ja_JP.UTF-8
ENV LANGUAGE=ja_JP:ja
ENV LC_ALL=ja_JP.UTF-8

# RDP ポート
EXPOSE 3389
