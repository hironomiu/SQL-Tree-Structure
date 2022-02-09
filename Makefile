MYSQL?=$(shell which mysql)
PHP?=$(shell which php)
CP?=$(shell which cp)
DB_HOST?=127.0.0.1
DB_PORT?=3306
DB_PROTOCOL=tcp
DB_USER?=root
DB_PASS?=mysql
DB_NAME?=sqltreestructure
PHP_PORT?=8888
setup:
	$(MYSQL) -h $(DB_HOST) --port $(DB_PORT) --protocol $(DB_PROTOCOL) -u $(DB_USER) -p$(DB_PASS) < ./db_setup/create_db.sql 
	$(MYSQL) -h $(DB_HOST) --port $(DB_PORT) --protocol $(DB_PROTOCOL) -u $(DB_USER) -p$(DB_PASS) $(DB_NAME) < ./db_setup/setup_table.sql
	$(CP) ./src/config/db_config_org.php ./src/config/db_config.php
server:
	$(PHP) -S $(DB_HOST):$(PHP_PORT)