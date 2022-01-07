# SQL-Tree-Structure

## 事前準備
動作用のDB(MySQL),テーブル、レコードの作成

例
```
$ make setup DB_USER=root DB_NAME=sqltreestructure
```

## DB接続
`make setup`で`./src/config/db_config_org.php`から複製した`./src/config/db_config.php`に適時編集

## 動作環境
PHP7系,8系で動作を確認

7系
```
$ php -v
PHP 7.3.11 (cli) (built: Apr 17 2020 19:14:14) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.3.11, Copyright (c) 1998-2018 Zend Technologies
```

8系
```
$ php -v
PHP 8.1.1 (cli) (built: Dec 17 2021 23:49:52) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.1.1, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.1, Copyright (c), by Zend Technologies
```

## ビルトインWebサーバ
8888番でListen

```
$ make server
```

PHP_PORTで指定可能

```
make server PHP_PORT=9999
```

## 階層構造データSQL

### 共通でできる事
- 行の折畳みを開き対象行の対してコメント
- 最下部から新規コメント

### 隣接リスト(Adjacency List)
ツリー全体を表示
```
http://localhost:8000/src/adjacency_list_accounts.php
```
特定のツリー(例では2)の表示
```
http://localhost:8000/src/adjacency_list_accounts.php?key=2
```

ソース(`./src/adjacency_list.php`はAccountsと未JOIN)
```
./src/adjacency_list.php
./src/adjacency_list_accounts.php
```

#### 実行計画
ツリーデータの取得
```
mysql> explain SELECT c.comment_id, c.comment, a.name FROM Comments_22 c inner join Accounts a on c.author = a.account_id WHERE c.comment_id = 1\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: c
   partitions: NULL
         type: const
possible_keys: PRIMARY,comment_id,author
          key: PRIMARY
      key_len: 8
          ref: const
         rows: 1
     filtered: 100.00
        Extra: NULL
*************************** 2. row ***************************
           id: 1
  select_type: SIMPLE
        table: a
   partitions: NULL
         type: const
possible_keys: PRIMARY
          key: PRIMARY
      key_len: 8
          ref: const
         rows: 1
     filtered: 100.00
        Extra: NULL
2 rows in set, 1 warning (0.01 sec)
```

### 経路列挙(Path Enumeration)
ツリー全体を表示
```
http://localhost:8000/src/path_enumeration.php
```
特定のツリー(例では2)の表示
```
http://localhost:8000/src/path_enumeration.php?key=2
```

ソース
```
./src/path_enumeration.php
```

#### 実行計画
親レコードの取得
```
mysql> explain SELECT c.comment_id , c.path , c.bug_id , c.author , c.comment_date , c.comment , a.name FROM Comments_251 c inner join Accounts a on c.author = a.account_id WHERE (LENGTH(path) - LENGTH(REPLACE(path, '/', '')))   / LENGTH('/') = 1\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: c
   partitions: NULL
         type: ALL
possible_keys: author
          key: NULL
      key_len: NULL
          ref: NULL
         rows: 7
     filtered: 100.00
        Extra: Using where
*************************** 2. row ***************************
           id: 1
  select_type: SIMPLE
        table: a
   partitions: NULL
         type: eq_ref
possible_keys: PRIMARY
          key: PRIMARY
      key_len: 8
          ref: sqltreestructure.c.author
         rows: 1
     filtered: 100.00
        Extra: NULL
2 rows in set, 1 warning (0.00 sec)
```

ツリーデータの取得
```
mysql> explain SELECT c.comment_id,c.path,c.comment,a.name FROM Comments_251 c inner join Accounts a on c.author = a.account_id WHERE c.path like concat("1/",'%') and c.comment_id != 1 order by c.path \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: c
   partitions: NULL
         type: range
possible_keys: PRIMARY,comment_id,author
          key: PRIMARY
      key_len: 8
          ref: NULL
         rows: 7
     filtered: 14.29
        Extra: Using where; Using filesort
*************************** 2. row ***************************
           id: 1
  select_type: SIMPLE
        table: a
   partitions: NULL
         type: eq_ref
possible_keys: PRIMARY
          key: PRIMARY
      key_len: 8
          ref: sqltreestructure.c.author
         rows: 1
     filtered: 100.00
        Extra: NULL
2 rows in set, 1 warning (0.01 sec)
```

### 入れ子集合(Nested sets)
ツリー全体を表示
```
http://localhost:8000/src/nested_set.php
```
特定のツリー(例では2)の表示
```
http://localhost:8000/src/nested_set.php?key=2
```

ソース
```
./src/nested_set.php
```

#### 実行計画
ツリーデータの取得
```
mysql> explain select c2.*,a.name from Comments_252 as c1 inner join Comments_252 as c2 on c2.nsleft between c1.nsleft and c1.nsright inner join Accounts a on c2.author = a.account_id where c1.comment_id = 1 order by c2.nsleft\G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: c1
   partitions: NULL
         type: const
possible_keys: PRIMARY
          key: PRIMARY
      key_len: 8
          ref: const
         rows: 1
     filtered: 100.00
        Extra: Using filesort
*************************** 2. row ***************************
           id: 1
  select_type: SIMPLE
        table: c2
   partitions: NULL
         type: ALL
possible_keys: author
          key: NULL
      key_len: NULL
          ref: NULL
         rows: 7
     filtered: 14.29
        Extra: Using where
*************************** 3. row ***************************
           id: 1
  select_type: SIMPLE
        table: a
   partitions: NULL
         type: eq_ref
possible_keys: PRIMARY
          key: PRIMARY
      key_len: 8
          ref: sqltreestructure.c2.author
         rows: 1
     filtered: 100.00
        Extra: NULL
3 rows in set, 1 warning (0.00 sec)
```

### 閉包テーブル(Closure Table)
ツリー全体を表示
```
http://localhost:8000/src/closure_table.php
```
特定のツリー(例では2)の表示
```
http://localhost:8000/src/closure_table.php?key=2
```

ソース
```
./src/closure_table.php
```

#### 実行計画
ツリーデータの取得
```
mysql> explain select descendant ,comment_id, comment, name, path from (select t1.descendant ,c.comment_id, c.comment, a.name, concat(group_concat(t1.ancestor separator'/'),'/') as path from TreePaths t1 inner join TreePaths t2 on t1.descendant = t2.descendant inner join Comments_253 c on c.comment_id = t1.descendant inner join Accounts a on a.account_id = c.author where t2.ancestor = 1 group by t2.descendant) a order by path\G
*************************** 1. row ***************************
           id: 1
  select_type: PRIMARY
        table: <derived2>
   partitions: NULL
         type: ALL
possible_keys: NULL
          key: NULL
      key_len: NULL
          ref: NULL
         rows: 17
     filtered: 100.00
        Extra: Using filesort
*************************** 2. row ***************************
           id: 2
  select_type: DERIVED
        table: t2
   partitions: NULL
         type: ref
possible_keys: PRIMARY,descendant
          key: PRIMARY
      key_len: 8
          ref: const
         rows: 7
     filtered: 100.00
        Extra: Using index
*************************** 3. row ***************************
           id: 2
  select_type: DERIVED
        table: c
   partitions: NULL
         type: eq_ref
possible_keys: PRIMARY,comment_id,author
          key: PRIMARY
      key_len: 8
          ref: sqltreestructure.t2.descendant
         rows: 1
     filtered: 100.00
        Extra: NULL
*************************** 4. row ***************************
           id: 2
  select_type: DERIVED
        table: a
   partitions: NULL
         type: eq_ref
possible_keys: PRIMARY
          key: PRIMARY
      key_len: 8
          ref: sqltreestructure.c.author
         rows: 1
     filtered: 100.00
        Extra: NULL
*************************** 5. row ***************************
           id: 2
  select_type: DERIVED
        table: t1
   partitions: NULL
         type: ref
possible_keys: descendant
          key: descendant
      key_len: 8
          ref: sqltreestructure.t2.descendant
         rows: 2
     filtered: 100.00
        Extra: Using index
5 rows in set, 1 warning (0.00 sec)
```

