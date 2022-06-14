PHP ?= /usr/bin/env php
COMPOSER_BIN ?= $(shell which composer)
COMPOSER = $(PHP) $(COMPOSER_BIN)

VENDOR_BIN_PHPSTAN := bin/vendor/phpstan
VENDOR_BIN_PHPUNIT := bin/vendor/phpunit
VENDOR_BIN_CSFIXER := bin/vendor/php-cs-fixer

VENDOR_BIN := $(VENDOR_BIN_PHPSTAN) $(VENDOR_BIN_PHPUNIT) $(VENDOR_BIN_CSFIXER)

test: phpstan php-cs-fixer phpunit

$(addprefix ./vendor/bin/,phpstan phpunit php-cs-fixer):
	$(COMPOSER) install

$(VENDOR_BIN): vendor/autoload.php
	$(COMPOSER) install

vendor/autoload.php: composer.lock
	$(COMPOSER) install

composer.lock: composer.json
	$(COMPOSER) up

phproxy.phar: test
	$(COMPOSER) install --no-dev -a
	$(PHP) -d phar.readonly=no $(shell which box) build
	$(COMPOSER) install

phpunit: $(VENDOR_BIN_PHPUNIT)
	$(PHP) ./vendor/bin/phpunit

php-cs-fixer: $(VENDOR_BIN_CSFIXER)
	$(PHP) ./vendor/bin/php-cs-fixer fix src --rules '@Symfony,@PSR2'

phpstan: $(VENDOR_BIN_PHPSTAN)
	$(PHP) ./vendor/bin/phpstan analyse -l max src/
