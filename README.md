# sugoroku

## 環境

PHP: v7.4.5

Laravel: v6.18.13

MySQL: v8.0.19

## 環境構築

1. ソースコードをローカルに持ってくる。
`git clone https://github.com/lachelier/sugoroku.git`
※clone後、以下を参考に環境を整えてみてください！
・既存のLaravelプロジェクトをcloneする手順(https://php-junkie.net/framework/laravel/laravel-clone/)

php artisan db:seed

php artisan passport:install

php artisan passport:keys

## Dockerによる環境構築

前提条件として [Docker Desktop](https://docs.docker.com/docker-for-mac/install/) がご自身のPCにインストールされている必要があります。
（Docker DesktopはWindows版もありますが動作確認はMac版でしか行っていません）

1. ソースコードをローカルに持ってくる。

`git clone https://github.com/lachelier/sugoroku.git`

2. コンテナを立ち上げる

`cd src/` で `src/` に移動します。（以降の手順は全て `src/` で作業します）

`cp .env.docker .env` でDocker用の `.env` をコピーします。

初回だけ `docker-compose up --build -d` を実行します。

2回目以降は `docker-compose up -d` でOKです。

3. composerパッケージのインストールなど

以下の順番で実行して下さい。

```
docker-compose exec php-fpm composer install

docker-compose exec php-fpm php artisan migrate

docker-compose exec php-fpm php artisan db:seed

docker-compose exec php-fpm php artisan passport:install

docker-compose exec php-fpm php artisan passport:keys
```

http://localhost:8080 でアクセスが可能です。

PHPUnit等は php-fpmのコンテナ上で実行します。

`docker-compose exec php-fpm sh` でコンテナに入って `./vendor/bin/phpunit` を実行して下さい。

### Dockerコンテナを停止させる

`docker-compose down` を実行します。

DBのデータやコンテナのイメージを全て削除したい場合は `docker-compose down --rmi all --volumes` を実行して下さい。

### Docker上のMySQLサーバーへの接続方法

ご自身のローカルPCに `brew install mysql` 等でMySQLクライアントをインストールします。

ご自身のローカルPCから `mysql -u root -h 127.0.0.1 -p -P 33060` で接続可能です。

パスワードは `docker-compose.yml` の `MYSQL_ROOT_PASSWORD` を参照して下さい。

## 開発の流れ(環境構築実施済みであるのが前提)

1. やりたいIssueを選択(各自で自分にアサインする)

2. masterブランチを最新に更新する。
'git pull origin master'

※この際、変更点は確認しておくと良い。

package.jsonが更新されてた時は'npm install'を実行。composer.jsonが更新されてた時は'composer update'を実行。

3. ブランチを切る。

`git checkout -b ブランチ名`

※ブランチ名はそのIssueの修正にひもづく名前にしてください

例：Laravelのプロジェクト作成ってIssue(Issue No.#1)ならブランチ名は、feature/#1_create_laravel_projectなど

4. 修正

5. 修正対象をステージング
`git add`コマンドで修正対象ファイルをステージに挙げる。
※全部まとめて挙げるときには`git add -A`

6. 修正対象をコミット

`git commit -m "コミットメッセージ"`

7. リモートリポジトリにpush(ブランチ名は"3. ブランチを切る。"で作ったブランチ名を使用)
`git push origin ブランチ名`

8. GitHub上でプルリクエストを送る。
レビュアーにむちょこさんを設定していただければと。

補足1：分からないことなどあれば随時Discordのすごろくチャンネルで聞いていただければと。。。

## Issueの難易度

🔰 < 🍒 < 無印 < 👑
