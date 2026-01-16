run:
	npm run build && npm run dev

up:
	docker compose up -d

stop:
	docker compose stop

down:
	docker compose down

build:
	docker compose up --build -d

test:
	docker exec -it php php artisan test --testsuite=Feature --stop-on-failure

test-update:
	docker exec -it php php artisan test -d --update-snapshots --testsuite=Feature --stop-on-failure

restart: stop up

bash:
	docker exec -it php bash

db-bash:
	docker exec -it mysql bash

install:
	docker exec -it php composer install --ignore-platform-req=ext-http
	docker exec -it php php artisan migrate
	docker exec -it php php artisan db:seed

composer-install:
	docker exec -it php composer install --ignore-platform-req=ext-http

logs:
	tail -f storage/logs/*.log
