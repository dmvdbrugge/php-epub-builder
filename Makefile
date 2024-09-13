.PHONY: devtools
devtools: codestyle stan tests

.PHONY: codestyle
codestyle: devtools/php-cs-fixer/vendor/bin/php-cs-fixer
	devtools/php-cs-fixer/vendor/bin/php-cs-fixer fix -v

.PHONY: stan
stan: devtools/phpstan/vendor/bin/phpstan
	devtools/phpstan/vendor/bin/phpstan analyze
	devtools/phpstan/vendor/bin/phpstan analyze --configuration=phpstan-tests.neon.dist

.PHONY: tests
tests: devtools/phpunit/vendor/bin/phpunit
	devtools/phpunit/vendor/bin/phpunit

devtools/php-cs-fixer/vendor/bin/php-cs-fixer: devtools/php-cs-fixer/composer.json
	composer install --working-dir=devtools/php-cs-fixer

devtools/phpstan/vendor/bin/phpstan: devtools/phpstan/composer.json
	composer install --working-dir=devtools/phpstan

devtools/phpunit/vendor/bin/phpunit: devtools/phpunit/composer.json
	composer install --working-dir=devtools/phpunit
