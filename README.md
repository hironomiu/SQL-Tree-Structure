# SQL-Tree-Structure

## 事前準備
動作用のDB(MySQL),テーブル、レコードの作成

例
```
$ make setup DB_USER=root DB_NAME=sqltreestructure
```

## 階層SQL
### 隣接リスト(Adjacency List)
```
./src/adjacency_list.php
./src/adjacency_list_accounts.php
```

### 経路列挙(Path Enumeration)
```
./src/path_enumeration.php
```

## ビルトインWebサーバ

```
$ make builtinserver
```

