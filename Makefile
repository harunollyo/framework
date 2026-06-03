.PHONY: help up down restart init example-install library-install scope wp-cli logs shell psql clean test\:unit

PLUGIN_DIR := /var/www/html/wp-content/plugins/framework
LIBRARY_DIR := /var/www/html/framework-library
SCOPED_DIR := $(PLUGIN_DIR)/libraries/themeum/framework

help:
	@echo "Framework playground commands:"
	@echo "  make up               Start all services in the background"
	@echo "  make init             Run the one-shot WordPress installer (first run)"
	@echo "  make example-install  Composer install for the example plugin (example/composer.json)"
	@echo "  make library-install  Composer install for the framework library (repo root composer.json)"
	@echo "  make scope            Generate the prefixed Themeum\\\\Framework\\\\ tree with php-scoper"
	@echo "  make test:unit        Run library unit tests inside the php container"
	@echo "  make wp-cli CMD=\"...\"  Run a WP-CLI command (e.g. CMD=\"plugin list\")"
	@echo "  make logs             Tail php and nginx logs"
	@echo "  make shell            Open a bash shell in the php container"
	@echo "  make down             Stop all services"
	@echo "  make clean            Stop all services and remove volumes"

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

init:
	docker compose run --rm wordpress-init

example-install:
	docker compose run --rm php composer install --working-dir=$(PLUGIN_DIR)

example-update:
	docker compose run --rm php composer update --working-dir=$(PLUGIN_DIR)

library-install:
	docker compose run --rm php composer install --working-dir=$(LIBRARY_DIR)

scope:
	docker compose run --rm php sh -c 'set -e; \
	  mkdir -p $(SCOPED_DIR); \
	  [ -f $(SCOPED_DIR)/helpers.php ] || printf "<?php\n" > $(SCOPED_DIR)/helpers.php; \
	  composer run scope --working-dir=$(PLUGIN_DIR); \
	  composer dump-autoload --working-dir=$(PLUGIN_DIR)'

wp:
	docker compose run --rm wpcli $(CMD)

logs:
	docker compose logs -f php nginx

shell:
	docker compose exec php bash

test\:unit:
	docker compose exec php bash -lc \
	  'cd $(LIBRARY_DIR) && composer install --no-interaction && vendor/bin/phpunit --testsuite Unit --testdox'

clean:
	docker compose down -v
