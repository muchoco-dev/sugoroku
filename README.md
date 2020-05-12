# sugoroku


# 開発の流れ(開発入る前に以下一読お願いします)

1. やりたいIssueを選択
Discordのすごろく開発チャンネルにやりたいIssueの番号とURLを貼り付けてやりたいですと一言言っていただければアサインします！

2. ソースコードをローカルに持ってくる。
`git clone https://github.com/lachelier/sugoroku.git`
※clone後、以下を参考に環境を整えてみてください！
・既存のLaravelプロジェクトをcloneする手順(https://php-junkie.net/framework/laravel/laravel-clone/)

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

補足：分からないことなどあれば随時Discordのすごろくチャンネルで聞いていただければと。。。
