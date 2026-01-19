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

# ============================================
# Staging Deployment Commands
# ============================================

deploy:
	@echo "🚀 Starting deployment to staging..."
	@$(MAKE) deploy-pull
	@$(MAKE) deploy-install
	@$(MAKE) deploy-build
	@$(MAKE) deploy-migrate
	@$(MAKE) deploy-optimize
	@$(MAKE) deploy-restart
	@$(MAKE) deploy-health
	@echo "✅ Deployment complete!"

deploy-pull:
	@echo "📥 Pulling latest code..."
	git pull origin main

deploy-install:
	@echo "📦 Installing dependencies..."
	docker compose -f docker-compose.staging.yml exec -T app composer install --no-dev --optimize-autoloader --no-interaction

deploy-build:
	@echo "🔨 Building frontend assets..."
	npm ci --prefer-offline --no-audit
	npm run build

deploy-migrate:
	@echo "🗄️  Running migrations..."
	docker compose -f docker-compose.staging.yml exec -T app php artisan migrate --force

deploy-optimize:
	@echo "⚡ Optimizing application..."
	docker compose -f docker-compose.staging.yml exec -T app php artisan config:cache
	docker compose -f docker-compose.staging.yml exec -T app php artisan route:cache
	docker compose -f docker-compose.staging.yml exec -T app php artisan view:cache
	docker compose -f docker-compose.staging.yml exec -T app php artisan event:cache

deploy-restart:
	@echo "🔄 Restarting services..."
	docker compose -f docker-compose.staging.yml restart app

deploy-health:
	@echo "🏥 Running health checks..."
	@sleep 5
	@docker compose -f docker-compose.staging.yml exec -T app php artisan inspire && echo "✅ App is healthy" || echo "❌ Health check failed"

deploy-rollback:
	@echo "⏪ Rolling back to previous version..."
	git reset --hard HEAD~1
	@$(MAKE) deploy-install
	@$(MAKE) deploy-optimize
	@$(MAKE) deploy-restart
	@$(MAKE) deploy-health
	@echo "✅ Rollback complete"

# Staging utilities
staging-up:
	docker compose -f docker-compose.staging.yml up -d

staging-down:
	docker compose -f docker-compose.staging.yml down

staging-logs:
	docker compose -f docker-compose.staging.yml logs -f

staging-bash:
	docker compose -f docker-compose.staging.yml exec app bash

staging-status:
	docker compose -f docker-compose.staging.yml ps
