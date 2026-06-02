.PHONY: help up down restart init composer-install scope wp-cli logs shell psql clean

PLUGIN_DIR := /var/www/html/wp-content/plugins/framework
SCOPED_DIR := $(PLUGIN_DIR)/libraries/themeum/framework

help:
	@echo "Framework playground commands:"
	@echo "  make up               Start all services in the background"
	@echo "  make init             Run the one-shot WordPress installer (first run)"
	@echo "  make composer-install Install the example plugin dependencies inside the container"
	@echo "  make scope            Generate the prefixed Themeum\\\\Framework\\\\ tree with php-scoper"
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

composer:
	docker compose run --rm php composer $(CMD) --working-dir=$(PLUGIN_DIR)

composer-install:
	docker compose run --rm php composer install --working-dir=$(PLUGIN_DIR)

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

clean:
	docker compose down -v
