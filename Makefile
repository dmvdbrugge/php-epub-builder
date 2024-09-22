# See Makefile.local.dist
-include Makefile.local
COMPOSER ?= composer
CS_CMD ?= fix

# "Executables"
PHP_CS_FIXER = devtools/php-cs-fixer/vendor/bin/php-cs-fixer
PHPSTAN = devtools/phpstan/vendor/bin/phpstan
PHPUNIT = devtools/phpunit/vendor/bin/phpunit

.PHONY: devtools
devtools: codestyle stan tests

.PHONY: codestyle
codestyle: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) $(CS_CMD) --verbose

.PHONY: stan
stan: $(PHPSTAN)
	$(PHPSTAN) analyze
	$(PHPSTAN) analyze --configuration=phpstan-tests.neon.dist

.PHONY: tests
tests: $(PHPUNIT)
	$(PHPUNIT)

.PHONY: validate
validate:
	$(COMPOSER) validate --strict
	$(COMPOSER) validate --no-check-publish --quiet --working-dir=devtools/php-cs-fixer
	$(COMPOSER) validate --no-check-publish --quiet --working-dir=devtools/phpstan
	$(COMPOSER) validate --no-check-publish --quiet --working-dir=devtools/phpunit

devtools/%/vendor/bin/%: devtools/%/composer.json
	$(COMPOSER) install --working-dir=devtools/%
