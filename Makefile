PHP ?= /usr/bin/env php
COMPOSER_BIN ?= $(shell which composer)
COMPOSER = $(PHP) $(COMPOSER_BIN)

test: phpstan php-cs-fixer phpunit

vendor/autoload.php: composer.lock
	composer install

composer.lock: composer.json
	composer up

phproxy.phar: test
	$(COMPOSER) install --no-dev -a
	$(PHP) $(shell which box) build
	$(COMPOSER) install

phpunit: vendor/autoload.php
	$(PHP) ./vendor/bin/phpunit

php-cs-fixer: vendor/autoload.php
	$(PHP) ./vendor/bin/php-cs-fixer fix src --rules '@Symfony,@PSR2'

phpstan: vendor/autoload.php
	$(PHP) ./vendor/bin/phpstan analyse -l max src/
