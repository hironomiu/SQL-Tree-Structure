MYSQL?=$(shell which mysql)
PHP?=$(shell which php)
DB_HOST?=localhost
DB_PORT?=3306
DB_PROTOCOL=tcp
DB_USER?=mysql
DB_PASS?=mysql
DB_NAME?=schema
setup:
	$(MYSQL) -h $(DB_HOST) --port $(DB_PORT) --protocol $(DB_PROTOCOL) -u $(DB_USER) -p$(DB_PASS) < ./db_setup/create_db.sql 
	$(MYSQL) -h $(DB_HOST) --port $(DB_PORT) --protocol $(DB_PROTOCOL) -u $(DB_USER) -p$(DB_PASS) $(DB_NAME) < ./db_setup/setup_table.sql 
builtinserver:
	$(PHP) -S $(DB_HOST):8000