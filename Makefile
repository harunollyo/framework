.PHONY: help up down restart init ssl-cert example-install library-install scope xdebug-log xdebug-wp wp-cli logs shell psql clean test\:unit

PLUGIN_DIR := /var/www/html/wp-content/plugins/framework
LIBRARY_DIR := /var/www/html/framework-library
SCOPED_DIR := $(PLUGIN_DIR)/libraries/themeum/framework

help:
	@echo "Framework playground commands:"
	@echo "  make up               Start all services in the background (generates SSL cert if needed)"
	@echo "  make ssl-cert         Generate the local self-signed HTTPS certificate"
	@echo "  make init             Run the one-shot WordPress installer (first run)"
	@echo "  make example-install  Composer install for the example plugin (example/composer.json)"
	@echo "  make library-install  Composer install for the framework library (repo root composer.json)"
	@echo "  make scope            Generate the prefixed Themeum\\\\Framework\\\\ tree (CI/release only)"
	@echo "  make xdebug-log       Tail the Xdebug connection log inside the php container"
	@echo "  make xdebug-wp CMD=\"...\"  Run WP-CLI with Xdebug trigger (IDE must be listening)"
	@echo "  make test:unit        Run library unit tests inside the php container"
	@echo "  make wp CMD=\"...\"  Run a WP-CLI command (e.g. CMD=\"plugin list\")"
	@echo "  make logs             Tail php and nginx logs"
	@echo "  make shell            Open a bash shell in the php container"
	@echo "  make down             Stop all services"
	@echo "  make clean            Stop all services and remove volumes"

ssl-cert:
	chmod +x docker/scripts/generate-ssl-cert.sh
	./docker/scripts/generate-ssl-cert.sh

up: ssl-cert
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

init:
	docker compose run --rm wordpress-init

example-install: example-composer-install

example-composer-install:
	docker compose run --rm php composer install --working-dir=$(PLUGIN_DIR)

example-composer-update:
	docker compose run --rm php composer update --working-dir=$(PLUGIN_DIR)

example-dump-autoload:
	docker compose run --rm php composer dump-autoload --working-dir=$(PLUGIN_DIR)

library-install: library-composer-install

library-composer-install:
	docker compose run --rm php composer install --working-dir=$(LIBRARY_DIR)

library-composer-update:
	docker compose run --rm php composer update --working-dir=$(LIBRARY_DIR)

scope:
	docker compose run --rm php sh -c 'set -e; \
	  mkdir -p $(SCOPED_DIR); \
	  [ -f $(SCOPED_DIR)/helpers.php ] || printf "<?php\n" > $(SCOPED_DIR)/helpers.php; \
	  composer run scope --working-dir=$(PLUGIN_DIR); \
	  composer dump-autoload --working-dir=$(PLUGIN_DIR)'

xdebug-log:
	docker compose exec php sh -c '\
	  echo "Tailing /tmp/xdebug.log — start Listen for Xdebug, then run a web or wp-cli request"; \
	  touch /tmp/xdebug.log; \
	  tail -F /tmp/xdebug.log'

wp:
	docker compose run --rm php wp --allow-root $(CMD)

xdebug-wp:
	docker compose run --rm -e XDEBUG_TRIGGER=PHPSTORM php wp --allow-root $(CMD)

logs:
	docker compose logs -f php nginx

shell:
	docker compose exec php bash

test\:unit:
	docker compose exec php bash -lc \
	  'cd $(LIBRARY_DIR) && composer install --no-interaction && vendor/bin/phpunit --testsuite Unit --testdox'

clean:
	docker compose down -v
