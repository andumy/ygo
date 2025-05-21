SHL:=$(shell uname -s)
IS_MAC :=$(filter Darwin,$(SHL))
U_ID:=$(if $(IS_MAC),1000 ,$(shell id -u))
G_ID:=$(if $(IS_MAC),1000 ,$(shell id -g))

start: regenerate-env init-app setup restart-supervisor

init-app:
	HOST_UID=${U_ID} HOST_GID=${G_ID} docker compose up -d --build

fix-ownership:
	docker exec ygo-php chmod -R 775 storage
	docker exec ygo-php chown -R ygo:ygo ./

setup:
	docker exec -u ygo:ygo ygo-php composer install

stop:
	HOST_UID=${U_ID} HOST_GID=${G_ID} docker compose stop

stop-v:
	HOST_UID=${U_ID} HOST_GID=${G_ID} docker compose down -v

bash:
	docker exec -u ygo:ygo -it ygo-php bash

regenerate-env:
	cd deploy && ./generate_env.sh local

restart-supervisor:
	docker exec ygo-php service supervisor stop
	docker exec ygo-php service supervisor start
	docker exec ygo-php supervisorctl restart all

fetch:
	php artisan fetch:cards
	php artisan fetch:images
