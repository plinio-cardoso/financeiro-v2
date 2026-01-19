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
	docker exec -it php_financeiro php artisan test --testsuite=Feature --stop-on-failure

test-update:
	docker exec -it php_financeiro php artisan test -d --update-snapshots --testsuite=Feature --stop-on-failure

test-coverage:
	@echo "Running tests with code coverage (minimum 80%)..."
	@docker exec -e XDEBUG_MODE=coverage php_financeiro vendor/bin/phpunit \
		tests/Feature/Models \
		tests/Feature/Services \
		tests/Feature/Controllers \
		tests/Feature/Commands \
		--coverage-text \
		--colors=always

test-coverage-html:
	@echo "Generating HTML coverage report..."
	@docker exec -e XDEBUG_MODE=coverage php_financeiro vendor/bin/phpunit \
		tests/Feature/Models \
		tests/Feature/Services \
		tests/Feature/Controllers \
		tests/Feature/Commands \
		--coverage-html coverage-report \
		--colors=always
	@echo "Coverage report generated at coverage-report/index.html"
	@echo "Opening coverage report in Chrome..."
	@open -a "Google Chrome" coverage-report/index.html

restart: stop up

bash:
	docker exec -it php_financeiro bash

db-bash:
	docker exec -it mysql_financeiro bash

refresh:
	docker exec -it php_financeiro php artisan migrate:fresh
	docker exec -it php_financeiro php artisan db:seed

install:
	docker exec -it php_financeiro composer install --ignore-platform-req=ext-http
	docker exec -it php_financeiro php artisan migrate
	docker exec -it php_financeiro php artisan db:seed

composer-install:
	docker exec -it php_financeiro composer install --ignore-platform-req=ext-http

logs:
	tail -f storage/logs/*.log
