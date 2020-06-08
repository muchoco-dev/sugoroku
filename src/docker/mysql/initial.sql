-- 'sugoroku_test' というデータベースを作成
-- 'sugoroku_test' というユーザー名のユーザーを作成
-- データベース 'sugoroku_test' への権限を付与
CREATE DATABASE IF NOT EXISTS sugoroku_test CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
CREATE USER sugoroku_test IDENTIFIED BY 'sugorokuPassword888';
GRANT ALL on sugoroku_test.* TO `sugoroku_test`@`%` WITH GRANT OPTION;
