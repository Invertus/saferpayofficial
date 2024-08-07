ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

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

dump-db-local:
	mysqldump -h 127.0.0.1 -P $(port) --protocol=tcp -u root -pprestashop prestashop > ${PWD}/.docker/dump.sql
# sample: make dump-db-local port=9420