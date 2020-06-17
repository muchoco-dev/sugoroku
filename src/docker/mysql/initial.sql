-- 'sugoroku_test' というデータベースを作成
-- 'sugoroku_test' というユーザー名のユーザーを作成
-- データベース 'sugoroku_test' への権限を付与
CREATE DATABASE IF NOT EXISTS sugoroku_test CHARACTER SET utf8mb4 COLLATE utf8mb4_ja_0900_as_cs;
CREATE USER sugoroku_test IDENTIFIED BY 'sugorokuPassword(888';
GRANT ALL on sugoroku_test.* TO `sugoroku_test`@`%` WITH GRANT OPTION;
