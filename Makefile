.PHONY: setup dev migrate fresh lint fix analyse test ci docs env-local env-prod

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
	@echo "→ Running migrations..."
	php artisan migrate
	@echo ""
	@echo "✅ Setup complete. Run 'make dev' to start the server."

## ── Daily Commands ─────────────────────────────────────────────
dev:
	php artisan serve

migrate:
	php artisan migrate

fresh:
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
