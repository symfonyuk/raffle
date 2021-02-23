up:
	mkdir -p docker/dynamodb
	sudo chmod 777 docker/dynamodb
	docker-compose up -d
	docker-compose run symfony-uk-raffle_php_service composer install
	docker-compose run symfony-uk-raffle_php_service php bin/console dev:create-sessions-table
	docker-compose run symfony-uk-raffle_php_service php bin/console dev:create-raffle-table
	docker-compose run symfony-uk-raffle_node_service yarn install
	docker-compose run symfony-uk-raffle_node_service yarn encore dev

down:
	docker-compose down