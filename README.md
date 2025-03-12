# Self-hosted Lab

このドキュメントは、VPS 上の開発環境（`Self-hosted Lab`）の設計、運用、備忘録として活用される。

## 運用マニュアル

### コンテナ起動

```bash
./up-services.sh
```

### コンテナ停止

```bash
./down-services.sh
```

### GROWI - Keycloak 連携について

GROWI ログイン画面にて、

```
OidcStrategy has not been set up
```

のようなエラーが出力されてしまう。おそらくコンテナ起動のタイミングの問題と思われるが、GROWI コンテナの再起動を何回か行うと、接続に成功することがある。

```
growi                  | {"name":"growi:service:PassportService","hostname":"976b2fb25340","pid":1,"level":20,"msg":"OidcStrategy: setup is done","time":"2025-03-11T14:01:12.564Z","v":0}
```

上記のようなログが出力されていると、Keycloak 連携に成功している。

詳細な分析が必要ではあるが、現段階では運用における簡易的な対処法として対処する。

### Nextcloud

Nextcloud コンテナの `/var/www/html/config` をホスト側の `/opt/nextcloud/config` からバインドマウントしているが、コンテナの `www-data(uid: 33, gid:33)` にマッピングされるようにしないと、権限問題で `config.php` を書き込めずエラーが発生する。

このため、以下のように権限を設定する。

```bash
sudo chown 33:33 -R config
```

#### ファイルロック

同時アクセス時の競合やデータの不整合を防ぐためにファイルロック機構が搭載されているが、この影響でファイルロックのメタ情報が残存してしまい、ファイル削除できなくなってしまった場合は、`config.php` にて `'filelocking.enabled' => false` を指定して、ファイルを削除する。その後再び、`'filelocking.enable => true'` に戻す（デフォルトは `true`）。

その後、ファイルインデックスの再スキャンを行う。

```bash
docker exec -u 33 -it nextcloud php occ files:scan --all
```

スマホアプリでのロックがかかった際は、キャッシュが残っている可能性もあるので、クライアント側のアプリ再起動を行う。

### コンテナ運用に関する注意

バインドマウントされていないコンテナ内のデータがあるため、不用意にボリュームを削除しないように注意する。
