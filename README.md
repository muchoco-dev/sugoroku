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

php artisan passport:install

php artisan passport:keys

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
