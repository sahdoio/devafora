# Variables
DC=docker compose --file docker-compose.yml --env-file .env
DC_TEST=docker compose --file docker-compose.yml --env-file ./src/.env.testing

.PHONY: go up setup down sh test paratest test-report logs db-migrate db-migrate-test db-seed db-rollback db-reset horizon log clear wayfinder

%:
	@:

go:
	make down
	make up
	sleep 3
	make setup
	make db-migrate
	make wayfinder
	make vite

go-test:
	make down
	docker volume rm -f devafora-db-test-volume
	make up
	sleep 3
	make setup
	make db-migrate-test

up:
	$(DC) up -d --build

setup:
	$(DC) exec devafora composer install
	$(DC) exec devafora-nodejs npm install

# Generate Wayfinder TS types in the php container (node container has no PHP)
wayfinder:
	$(DC) exec devafora php artisan wayfinder:generate --with-form

down:
	$(DC) down

sh:
	$(DC) exec devafora bash

node-sh:
	$(DC) exec devafora-nodejs sh

test:
	docker exec -it devafora php artisan test $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS)) --coverage

paratest:
	$(DC) exec devafora php artisan test --coverage --parallel --processes=10 $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))

test-coverage:
	$(DC) exec devafora php artisan test --coverage-html=coverage $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))

db-migrate:
	$(DC) exec devafora php artisan migrate

db-migrate-test:
	$(DC_TEST) exec devafora php artisan migrate --env=testing

db-seed:
	$(DC) exec devafora php artisan db:seed

db-rollback:
	$(DC) exec devafora php artisan migrate:rollback

db-reset:
	make db-rollback
	make db-migrate
	make db-seed

horizon:
	$(DC) exec devafora php artisan horizon

clear:
	$(DC) exec devafora php artisan cache:clear
	$(DC) exec devafora php artisan view:clear
	$(DC) exec devafora php artisan route:clear
	$(DC) exec devafora php artisan config:clear
	$(DC) exec devafora php artisan optimize:clear

vite:
	$(DC) exec devafora-nodejs npm run dev

logs:
	$(DC) logs -f -n 10

log:
	$(DC) exec devafora tail -f storage/logs/laravel.log -n 0
