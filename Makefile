test: phpstan php-cs-fixer

phproxy.phar: test
	composer install --no-dev
	box build
	composer install

php-cs-fixer: vendor
	composer install
	./vendor/bin/php-cs-fixer fix src --rules '@Symfony,@PSR2'

phpstan: vendor
	composer install
	./vendor/bin/phpstan analyse -l max src/
