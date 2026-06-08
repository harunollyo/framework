# Framework playground

A local WordPress runtime for developing `themeum/framework`. It runs a minimal sample plugin (slug `framework`) that consumes the library through a Composer **path repository with `symlink: true`** and then **scopes it with PHP-Scoper** (prefix `Themeum`). The plugin authors against the prefixed namespace `Themeum\Framework\`, exactly as a shipped plugin would.

All Docker tooling lives at the repository root (`../docker-compose.yml`, `../Makefile`, `../docker/`). This directory is the sample plugin itself.

## Requirements

- Docker (Docker Desktop or OrbStack)
- A free port block (defaults: `20200`, `20201`, `20202`)

## What this validates

- The application boots on the WordPress `init` hook via `bootstrap/app.php`.
- The options prefix is applied (`use_prefix('framework')`).
- Hooks are wired from `config/hooks.php` and service providers from `bootstrap/providers.php`.
- A REST route is registered: `GET /wp-json/framework/v1/ping`.
- The `is_dev_mode()` macro resolves from `WP_DEBUG`.

## First-run workflow

Run all commands from the repository root (`..`), where the `Makefile` lives.

```bash
cp .env.example .env          # adjust ports if needed
make up                       # start nginx, php, mariadb, phpmyadmin
make example-install          # example/composer.json — plugin + php-scoper deps (in container)
make scope                    # generate the prefixed Themeum\Framework\ tree
make init                     # one-shot: download + install WP, activate the plugin
```

Then open:

- Site: http://localhost:20200
- Admin: http://localhost:20200/wp-admin (user `admin`, password `demo`)
- phpMyAdmin: http://localhost:20202
- REST check: `curl http://localhost:20200/wp-json/framework/v1/ping`

Expected response:

```json
{ "status": "ok", "dev_mode": true, "prefix": "framework" }
```

> Always run `make example-install` (not host Composer) for the example plugin. The path repository URL (`/var/www/html/framework-library`) is a **container** path; the symlink only resolves inside Docker. Use `make library-install` for the repo-root `composer.json` (PHPUnit, PHPCS, etc.).

> `make scope` is **required before first activation**. The plugin authors against the prefixed `Themeum\Framework\` namespace, which only exists after php-scoper generates `libraries/themeum/framework/`. Activating the plugin before scoping will fatal on the missing scoped tree.

## Dev loop

The library is symlinked into `vendor/themeum/framework` (unscoped `Framework\`), but the plugin runs against the **scoped** copy in `libraries/themeum/framework/` (`Themeum\Framework\`). So after editing any file under `../src/`, regenerate the scoped tree:

```bash
make scope
```

This re-runs php-scoper and refreshes `libraries/themeum/framework/`. No container restart is needed; the next request picks up the change. Re-run `make example-install` only when you change the example plugin's own `composer.json`.

## WP-CLI

```bash
make wp-cli CMD="plugin list"
make wp-cli CMD="option get siteurl"
```

The framework registers its own WP-CLI commands (`migrate`, `make:model`, `make:controller`, `make:migration`, `db:seed`, `migrate:fresh`) once the application boots under WP-CLI.

## Xdebug

Xdebug is installed but disabled by default. To enable step debugging:

1. Set `XDEBUG_MODE=debug` in `.env`.
2. `docker compose restart php`.
3. Start **Listen for Xdebug** in the IDE (port `9003`, key `PHPSTORM`).
4. Trigger a session explicitly:
   - **Web:** use an Xdebug browser extension, or append `?XDEBUG_TRIGGER=PHPSTORM` to the URL.
   - **WP-CLI:** `make xdebug-wp CMD="kirki migrate"` (or `XDEBUG_TRIGGER=PHPSTORM make wp CMD="..."`).

> If the IDE pauses on `Symfony\Component\Console\Exception\RuntimeException` (e.g. `The "--once" option does not exist`), that is Symfony Console probing argv during WP-CLI/Composer bootstrap—not your app failing. Press **Continue** (F5), or rely on the `ignoreExceptions` entries in `.vscode/launch.json`.

## PHP version note

The stack uses **PHP 8.2** (Kirki Ecommerce uses 7.4). The framework declares `>=7.0`, so this is intentionally a wider compatibility surface: PHP 8.x deprecations in `src/` will surface as notices with `WP_DEBUG=true`.

## How scoping works (production parity by default)

This playground runs the library exactly as a shipped plugin would: prefixed with [PHP-Scoper](https://github.com/humbug/php-scoper). The `scoper.config.php` mirrors Kirki's setup: prefix `Themeum`, finder `vendor/themeum/framework/src`, output `libraries/themeum/framework/`.

- The path repository symlinks the **unscoped** library into `vendor/themeum/framework` (this is only php-scoper's *input*).
- `make scope` rewrites every `Framework\` namespace to `Themeum\Framework\` (and `Framework\app()` to `Themeum\Framework\app()`) into `libraries/themeum/framework/`.
- `scoper.config.php` uses a negative-lookahead in `exclude-namespaces` so only `Framework\` is prefixed; all other namespaces (e.g. `Carbon\`) stay unchanged and load from `vendor/` via Composer (Carbon is a transitive dependency of `themeum/framework`, not a separate plugin requirement).
- `example/composer.json` autoloads **only** the scoped copy:

```jsonc
// example/composer.json
"autoload": {
    "psr-4": {
        "Example\\": "src/",
        "Themeum\\Framework\\": "libraries/themeum/framework/"
    },
    "files": [
        "libraries/themeum/framework/helpers.php"
    ]
}
```

The plugin code references the prefixed namespace throughout: `Themeum\Framework\Application`, `Themeum\Framework\Route`, `\Themeum\Framework\response()`, and the `is_dev_mode` macro is registered on `Themeum\Framework\Application`. The unscoped `vendor/themeum/framework` is never autoloaded alongside the scoped copy.

`make scope` ensures a placeholder `libraries/themeum/framework/helpers.php` exists before running php-scoper (so the project autoloader, which references it, can load), then runs php-scoper and `composer dump-autoload`. `libraries/` is gitignored and regenerated on every CI/release run (see `.github/workflows/smoke-test.yml`).

## What is committed vs generated

| Committed | Generated (gitignored) |
|-----------|------------------------|
| `framework.php`, `bootstrap/`, `config/`, `routes/`, `src/` | `vendor/` |
| `composer.json`, `scoper.config.php` | `composer.lock` |
| Docker files, `Makefile`, `.env.example` | `libraries/` (scoped tree) |
| | `.env`, WordPress core, `wordpress_data` volume |

## Troubleshooting

| Symptom | Likely cause / fix |
|---------|--------------------|
| `Class Themeum\Framework\... not found` | `make scope` was not run, so `libraries/themeum/framework/` is missing. Run `make example-install` then `make scope`. |
| `Failed opening required '.../libraries/themeum/framework/helpers.php'` on activation | The scoped tree has not been generated yet. Run `make scope` before activating the plugin. |
| `vendor/themeum/framework` is empty or not a symlink | Composer ran outside Docker; the path URL only resolves in the container. Remove `example/vendor` and re-run `make example-install`. |
| `port is already allocated` | Another stack uses the `20200` block. Change `NGINX_HTTP_PORT` / `MARIADB_PORT` / `PHPMYADMIN_PORT` in `.env`. |
| `make init` hangs on "Database not ready" | MariaDB still starting; the script retries automatically. If it persists, check `docker compose logs mariadb`. |
| REST route returns 404 | Permalinks not flushed. Re-run `make init`, or `make wp-cli CMD="rewrite flush"`. |
| Library edits under `../src/` not reflected | The plugin runs the scoped copy. Re-run `make scope` after editing the library. |
| `Cannot redeclare class Themeum\Framework\...` | Both unscoped `vendor/themeum/framework` and the scoped `libraries/` tree are autoloaded. Only the scoped tree should be in `autoload`. |
| `Class "Carbon\Carbon" not found` | Scoped code references unprefixed `Carbon\`, but `vendor/nesbot/carbon` is missing. Run `make example-install` so Composer installs `themeum/framework` and its transitive deps (including Carbon) into `vendor/`. |
| Xdebug not connecting | Confirm `XDEBUG_MODE=debug` in `.env` and `docker compose restart php`; IDE must listen on `9003`. Use `?XDEBUG_TRIGGER=PHPSTORM` (web) or `make xdebug-wp` (CLI). |
| IDE stops on Symfony `--once` / `RuntimeException` | Harmless Symfony Console noise while debugging CLI; press Continue, or use **Listen for Xdebug** (has `ignoreExceptions` configured). |
