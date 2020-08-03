# SQL-Tree-Structure

## 事前準備
動作用のDB(MySQL),テーブル、レコードの作成

例
```
$ make setup DB_USER=root DB_NAME=sqltreestructure
```

## ビルトインWebサーバ
8000番でListen
```
$ make builtinserver
```

## 階層SQL
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


