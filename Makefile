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
	/bin/bash .docker/wait-for-container.sh prestashop-1784

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
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# uninstalling module
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall saferpayofficial"
	# installing the module again
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install saferpayofficial"
	# chmod all folders
	docker exec -i prestashop-1784 sh -c "chmod -R 777 /var/www/html"

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
	/bin/bash .docker/wait-for-container.sh prestashop-1764

# Run e2e tests in headless way.
e2eh1764: test-e2e-headless-1764
test-e2e-headless-1764:
	make e2e1764p
