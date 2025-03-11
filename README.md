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

のようなエラーが出力されてしまう。原因は不明だが、Keycloak、GROWI, https-potal コンテナの再起動を行うと、接続に成功する。

詳細な分析が必要。

### コンテナ運用に関する注意

バインドマウントされていないコンテナ内のデータがあるため、不用意にボリュームを削除しないように注意する。
