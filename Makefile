ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

############ PS 817 ############################

# All the commands required to build prestashop-817 version locally
bps817: build-ps-817
build-ps-817:
	# configuring your prestashop
	docker exec -i prestashop-817 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_817.sql
	# installing module
	docker exec -i prestashop-817 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# uninstalling module
	docker exec -i prestashop-817 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall saferpayofficial"
	# installing the module again
	docker exec -i prestashop-817 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# chmod all folders
	docker exec -i prestashop-817 sh -c "chmod -R 777 /var/www/html"

# Preparing prestashop-817 for e2e tests - this actually launched an app in background. You can access it already!
e2e817p: e2e-817-prepare
e2e-817-prepare:
	# detaching containers
	docker compose -f docker-compose.817.yml up -d --force-recreate
	# sees what containers are running
	docker compose -f docker-compose.817.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh saferpayofficial-mysql-817
	# preloads initial data
	make bps817

# Run e2e tests in headless way.
e2eh817: test-e2e-headless-817
test-e2e-headless-817:
	make e2e817p

dump-db-local:
	mysqldump -h 127.0.0.1 -P $(port) --protocol=tcp -u root -pprestashop prestashop > ${PWD}/.docker/dump.sql
# sample: make dump-db-local port=9420