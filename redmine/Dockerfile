FROM redmine:latest

RUN apt-get update && apt-get install -y build-essential

# 作業ディレクトリを設定（Redmine公式イメージの作業ディレクトリは /usr/src/redmine）
WORKDIR /usr/src/redmine

# Gemfile と Gemfile.lock をコンテナ内にコピー
COPY Gemfile Gemfile.lock ./

# ビルド時に gem をインストール
RUN bundle install --without development test

# ※ 必要に応じて、その他のソースコードや設定ファイルをコピーすることも可能です
# COPY . .

# コンテナ起動時は公式イメージの CMD がそのまま利用されます
