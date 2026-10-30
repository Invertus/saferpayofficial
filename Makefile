ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

############ PS1784 ############################

# All the commands required to build prestashop-1784 version locally
bps1784: build-ps-1784
build-ps-1784:
	# configuring your prestashop
	docker exec -i prestashop-1784 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_1784_2.sql
	# installing module
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# uninstalling module
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall saferpayofficial"
	# installing the module again
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# chmod all folders
	docker exec -i prestashop-1784 sh -c "chmod -R 777 /var/www/html"

# Preparing prestashop-1784 for e2e tests - this actually launched an app in background. You can access it already!
e2e1784p: e2e-1784-prepare
e2e-1784-prepare:
	# detaching containers
	docker-compose -f docker-compose.1784.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.1784.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh saferpayofficial-mysql-1784
	# preloads initial data
	make bps1784

# Run e2e tests in headless way.
e2eh1784: test-e2e-headless-1784
test-e2e-headless-1784:
	make e2e1784p

############ PS1764 ############################

# All the commands required to build prestashop-1764 version locally
bps1764: build-ps-1764
build-ps-1764:
	# configuring your prestashop
	docker exec -i prestashop-1764 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_1764.sql
	# installing module
	docker exec -i prestashop-1764 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# uninstalling module
	docker exec -i prestashop-1764 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall saferpayofficial"
	# installing the module again
	docker exec -i prestashop-1764 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# chmod all folders
	docker exec -i prestashop-1764 sh -c "chmod -R 777 /var/www/html"

# Preparing prestashop-1764 for e2e tests - this actually launched an app in background. You can access it already!
e2e1764p: e2e-1764-prepare
e2e-1764-prepare:
	# detaching containers
	docker-compose -f docker-compose.1764.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.1764.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh saferpayofficial-mysql-1764
	# preloads initial data
	make bps1764

# Run e2e tests in headless way.
e2eh1764: test-e2e-headless-1764
test-e2e-headless-1764:
	make e2e1764p

############ PS1770 ############################

# All the commands required to build prestashop-1764 version locally
bps1770: build-ps-1770
build-ps-1770:
	# configuring your prestashop
	docker exec -i prestashop-1770 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_1770.sql
	# installing module
	docker exec -i prestashop-1770 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# uninstalling module
	docker exec -i prestashop-1770 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall saferpayofficial"
	# installing the module again
	docker exec -i prestashop-1770 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# chmod all folders
	docker exec -i prestashop-1770 sh -c "chmod -R 777 /var/www/html"

# Preparing prestashop-1770 for e2e tests - this actually launched an app in background. You can access it already!
e2e1770p: e2e-1770-prepare
e2e-1770-prepare:
	# detaching containers
	docker-compose -f docker-compose.1770.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.1770.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh saferpayofficial-mysql-1770
	# preloads initial data
	make bps1770

# Run e2e tests in headless way.
e2eh1770: test-e2e-headless-1770
test-e2e-headless-1770:
	make e2e1770p

############ PS1786 ############################

# All the commands required to build prestashop-1786 version locally
bps1786: build-ps-1786
build-ps-1786:
	# configuring your prestashop
	docker exec -i prestashop-1786 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_1786.sql
	# installing module
	docker exec -i prestashop-1786 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# uninstalling module
	docker exec -i prestashop-1786 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall saferpayofficial"
	# installing the module again
	docker exec -i prestashop-1786 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# chmod all folders
	docker exec -i prestashop-1786 sh -c "chmod -R 777 /var/www/html"

# Preparing prestashop-1786 for e2e tests - this actually launched an app in background. You can access it already!
e2e1786p: e2e-1786-prepare
e2e-1786-prepare:
	# detaching containers
	docker-compose -f docker-compose.1786.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.1786.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh saferpayofficial-mysql-1786
	# preloads initial data
	make bps1786

# Run e2e tests in headless way.
e2eh1786: test-e2e-headless-1786
test-e2e-headless-1786:
	make e2e1786p

# target: build-react			- Install deps and build the React admin settings app
build-react:
	cd views/js/admin/settings-app && pnpm install && pnpm run build

# target: dev-react			- Start React dev server with HMR
dev-react:
	cd views/js/admin/settings-app && pnpm dev

# target: watch-react			- Build React app and watch for changes
watch-react:
	cd views/js/admin/settings-app && pnpm run build --watch

# target: lint-react			- Run TypeScript type check
lint-react:
	cd views/js/admin/settings-app && pnpm run tsc --noEmit

MODULE_NAME := saferpayofficial

# target: prepare-zip			- Strip the checkout in place into the shippable saferpayofficial/ folder (CI only, run build-react first)
prepare-zip:
	@[ -n "$$CI" ] || { echo "prepare-zip deletes .git and the dev files of this checkout. Run it in CI, or set CI=1 to force it."; exit 1; }
	@[ -f views/js/admin/dist/saferpay-settings.js ] || { echo "The settings app bundle is missing. Run make build-react first."; exit 1; }
	composer install --no-dev --no-autoloader
	# Drop the test, CI and dotfile leftovers of the dependencies before the classmap is built
	find vendor -mindepth 3 -maxdepth 3 -type d \( -name Tests -o -name tests -o -name .github \) -exec rm -rf {} +
	find vendor -mindepth 3 -type f \( -name '.*' -o -name 'phpunit.xml*' -o -name 'phpcs.xml*' -o -name composer.lock -o -name Makefile \) -delete
	composer dump-autoload --no-dev --optimize --classmap-authoritative
	cp .github/.htaccess vendor/.htaccess
	rm -rf .git .github tests .php-cs-fixer.php .php-cs-fixer.dist.php Makefile cypress* docker-compose*.yml package.json package-lock.json .docker README.md MAINTAINERS.md .editorconfig .gitignore .php_cs.dist browserstack.json views/js/admin/settings-app
	# The Addons validator requires an index.php in every shipped directory
	find . -type d | while read -r dir; do [ -f "$$dir/index.php" ] || cp index.php "$$dir/index.php"; done
	mkdir $(MODULE_NAME)
	find . -mindepth 1 -maxdepth 1 ! -name $(MODULE_NAME) -exec mv {} $(MODULE_NAME)/ \;
	chmod -R 777 $(MODULE_NAME)/var/cache

ci-lint:
	./vendor/bin/php-cs-fixer fix --diff --no-interaction --dry-run

# PHPStan ships a phar, which is what this target runs. `composer require phpstan/phpstan`
# re-resolves the whole dependency tree, and invertus/lock is declared with "no-api", so
# resolving it clones over git - which the PrestaShop image can no longer install, Debian 11
# being end of life. The phar also pins the version: the old command took whatever PHPStan
# had released that morning, so an analyser release could turn a green branch red.
PHPSTAN_VERSION := 2.2.14
PHPSTAN_PHAR := tests/phpstan/phpstan-$(PHPSTAN_VERSION).phar

$(PHPSTAN_PHAR):
	curl -fsSL -o $@ https://github.com/phpstan/phpstan/releases/download/$(PHPSTAN_VERSION)/phpstan.phar

ci-phpstan: $(PHPSTAN_PHAR)
	ps_version=$(firstword $(subst -, ,$(ps_version_tag))); \
	if [ -e tests/phpstan/phpstan-$$ps_version.neon ] ; then \
		phpstan_config_path=tests/phpstan/phpstan-$$ps_version.neon ; \
	else \
		phpstan_config_path=tests/phpstan/phpstan.neon ; \
	fi; \
	echo Using PHPStan config: $$phpstan_config_path ; \
	_PS_ROOT_DIR_=/var/www/html php $(PHPSTAN_PHAR) --configuration=$$phpstan_config_path analyse