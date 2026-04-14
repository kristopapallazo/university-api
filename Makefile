.PHONY: setup dev migrate fresh lint fix analyse test ci docs env-local env-prod _guard-local

## ── Safety guard ───────────────────────────────────────────────
## Refuses to run destructive DB commands unless DB_HOST is localhost.
## This prevents accidentally migrating/wiping the production DB.
_guard-local:
	@host=$$(grep -E '^DB_HOST=' .env 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'"); \
	if [ "$$host" != "127.0.0.1" ] && [ "$$host" != "localhost" ] && [ "$$host" != "" ]; then \
		echo "❌ REFUSING: DB_HOST=$$host is not local."; \
		echo "   This command would touch a remote database (likely PRODUCTION)."; \
		echo "   Run 'make env-local' first if you really mean to work locally."; \
		exit 1; \
	fi

## ── Setup ──────────────────────────────────────────────────────
setup:
	@echo "→ Installing PHP dependencies..."
	composer install
	@echo "→ Copying .env if missing..."
	@test -f .env || cp .env.example .env
	@echo "→ Generating app key..."
	php artisan key:generate
	@echo "→ Wiring git hooks..."
	git config core.hooksPath .githooks
	@chmod +x .githooks/pre-commit 2>/dev/null || true
	@conn=$$(grep -E '^DB_CONNECTION=' .env | cut -d= -f2 | tr -d '"' | tr -d "'"); \
	if [ "$$conn" = "sqlite" ]; then \
		echo "→ SQLite detected, creating database file and running migrations..."; \
		touch database/database.sqlite; \
		php artisan migrate --seed; \
	else \
		host=$$(grep -E '^DB_HOST=' .env | cut -d= -f2 | tr -d '"' | tr -d "'"); \
		if [ "$$host" = "127.0.0.1" ] || [ "$$host" = "localhost" ]; then \
			echo "→ Local MySQL detected, running migrations..."; \
			php artisan migrate; \
		else \
			echo "→ Remote DB detected ($$host), skipping migrations."; \
			echo "   Schema is managed by the lead. You're ready to code."; \
		fi; \
	fi
	@echo ""
	@echo "✅ Setup complete. Run 'make dev' to start the server."

## ── Daily Commands ─────────────────────────────────────────────
dev:
	php artisan serve

migrate: _guard-local
	php artisan migrate

fresh: _guard-local
	php artisan migrate:fresh --seed

## ── Quality ────────────────────────────────────────────────────
lint:
	vendor/bin/pint --test

fix:
	vendor/bin/pint

analyse:
	vendor/bin/phpstan analyse --memory-limit=1G

test:
	php artisan test

ci: lint analyse test

## ── Docs ───────────────────────────────────────────────────────
docs:
	php artisan scribe:generate

## ── Environment switching ──────────────────────────────────────
env-local:
	@cp .env.example .env
	@echo "✅ Switched to LOCAL environment (.env.example → .env)"
	@echo "   Edit .env and set DB_PASSWORD if needed."

env-prod:
	@test -f .env.production || { echo "❌ .env.production not found. Create it first (see docs/backend/onboarding.md)."; exit 1; }
	@cp .env.production .env
	@echo "✅ Switched to PRODUCTION environment (.env.production → .env)"
	@echo "   ⚠️  You are now pointing at the PRODUCTION database. Be careful."
