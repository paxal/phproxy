test: phpstan php-cs-fixer

phproxy.phar: test
	box build

vendor:
	composer install

php-cs-fixer: vendor
	./vendor/bin/php-cs-fixer fix src --rules '@Symfony,@PSR2'

phpstan: vendor
	./vendor/bin/phpstan analyse -l max src/
