# AppSec Audit Reference — PHP / WordPress Framework

## Trusted vs untrusted sources

| Source | Examples in this codebase |
|--------|---------------------------|
| REST params | `WP_REST_Request::get_params()`, `Request::make_request()`, `$request->input()` |
| Superglobals | `$_GET`, `$_POST`, `$_REQUEST`, `$_FILES`, `$_COOKIE` |
| Headers | `$request->headers`, `getallheaders()` |
| Route params | URL segments resolved in `Route.php` |
| Referer / redirect | `wp_get_referer()`, query args from `add_query_arg()` |
| Webhooks / cron | `Scheduler.php` secret validation, external POST bodies |
| Options / meta | User-writable post meta, options — untrusted if editable via API |

**Not untrusted by default:** hardcoded constants, env/config loaded server-side, values from authenticated DB reads *after* ownership is verified.

## SQL injection sinks

Search patterns:

```bash
rg 'where_raw|select_raw|or_where_raw' --glob '*.php'
rg '\$\w+\s*\.\s*["\'].*SELECT|INSERT|UPDATE|DELETE' --glob '*.php'
rg 'get_results\s*\(\s*["\']' --glob '*.php'
rg '->query\s*\(\s*["\']' --glob '*.php'
rg 'Expression\s*\(\s*\$' --glob '*.php'
```

Framework hotspots:
- `QueryBuilder::where_raw()`, `select_raw()` — safe only when `$bindings` hold user values and SQL has `?` placeholders
- `QueryCompiler::where_raw()` — compiles raw SQL verbatim (`src/Database/Query/QueryCompiler.php`)
- `Connection::select()`, `prepare_query()` — final execution via `$wpdb->get_results`
- String building in `Expression` from request data

Safe patterns:
- Placeholder bindings through `Connection::prepare_query()`
- Query builder column/value methods (not raw) with bound parameters
- `(int)` cast for numeric IDs **plus** authorization check (cast alone ≠ secure for BOLA)

## XSS sinks

Search patterns:

```bash
rg 'echo\s+\$|<script|innerHTML|document\.write' --glob '*.php'
rg 'echo\s+["\'].*\$' --glob '*.php'
rg 'wp_redirect\s*\(\s*\$' --glob '*.php'
```

Framework hotspots:
- `Supports/Url.php` — `echo "<script>window.location.href = '$redirect_url';"</script>"` when headers sent
- `helpers.php` — debug output (`dd`, `dump`) — report only if reachable in production
- Blade/views in consuming plugins (this repo is a library; scan `example/` if in scope)

Safe patterns:
- `esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()`, `wp_kses()`, `wp_kses_post()`
- `JsonResponse` returning JSON (XSS at API layer is usually N/A unless reflected in HTML elsewhere)

## RCE sinks

Search patterns:

```bash
rg '\b(eval|assert|exec|shell_exec|system|passthru|proc_open|popen)\s*\(' --glob '*.php'
rg 'preg_replace\s*\(.*/e' --glob '*.php'
rg 'create_function\s*\(' --glob '*.php'
rg '(include|require)(_once)?\s*\(\s*\$' --glob '*.php'
rg 'call_user_func(_array)?\s*\(\s*\$' --glob '*.php'
```

This framework has no built-in `eval`/`exec` usage in `src/` — focus on consuming code and dynamic includes.

## Insecure deserialization sinks

Search patterns:

```bash
rg 'unserialize\s*\(\s*\$' --glob '*.php'
rg 'maybe_unserialize\s*\(\s*\$' --glob '*.php'
```

Also check: user input → base64_decode → unserialize; cookie/session deserialization.

## BOLA / IDOR patterns

Search patterns:

```bash
rg '::find\s*\(\s*\$|->find\s*\(\s*\$' --glob '*.php'
rg '->where\s*\(\s*[\'"]id[\'"]\s*,\s*\$' --glob '*.php'
rg 'authorize\s*\(|PolicyManager|Guard::authorize' --glob '*.php'
```

Framework auth layers:
- `AuthMiddleware` — authentication only (`is_user_logged_in()`), **not** object-level authorization
- `Request::authorize()` — override in Form Request subclasses (`src/Http/Request.php`)
- `PolicyManager::authorize($ability, $model)` — resource policies (`src/Managers/PolicyManager.php`)
- `User::can($ability, $model)` — capability + policy gate

IDOR finding checklist:
1. Handler accepts resource ID from request
2. Loads/updates/deletes resource by that ID
3. No `authorize('view'|'update'|'delete', $model)` or equivalent ownership check
4. Another authenticated (or unauthenticated) user can access another user's resource

## Sanitization / validation in this framework

| Layer | Location | Limits |
|-------|----------|--------|
| Validation | `Framework\Validation\Validator`, rules in `src/Validation/Rules/` | Must validate the **same variable** used at sink |
| Sanitization | `Framework\Sanitizer`, `Validation/Rules/Sanitizer.php` | Trace whether sanitized value reaches sink |
| Mass assignment | `GuardAttributes`, `$fillable` / `$guarded` on models | Does not replace auth checks |

## Example: invalid vs valid reports

**Invalid (false positive):**
> `QueryBuilder::where_raw` exists — might be SQLi.

**Valid:**
> Source: `$request->input('status')` at `OrderController.php:34` → Sink: `->where_raw("status = '{$status}'")` at `OrderController.php:36`. No validation rule on `status`; string concatenated into SQL.

**Invalid (false positive):**
> Route has no `AuthMiddleware`.

**Valid:**
> Source: `$request->input('user_id')` at `ProfileController.php:22` → loads `User::find($id)` at line 24 → returns email/PII at line 26. Only `AuthMiddleware` applied; no check that `$id === current_user_id()`. Any logged-in user can read any profile.
