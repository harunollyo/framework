---
name: appsec-audit
description: Performs source-to-sink security audits for SQL injection, XSS, RCE, insecure deserialization, and BOLA/IDOR. Use when the user requests a security audit, vulnerability scan, AppSec review, penetration test prep, or asks to find exploitable bugs in the codebase.
---

# AppSec Audit

Act as a Senior AppSec Engineer. Thoroughly audit this @Codebase for security vulnerabilities.
To eliminate false positives, you must only report a vulnerability if you can explicitly trace an untrusted user input (Source) directly to a vulnerable execution point (Sink) without proper sanitization or validation.

Scan specifically for:
1. SQL Injection (raw queries, improper concatenation)
2. Cross-Site Scripting (XSS in views/blades)
3. Remote Code Execution (RCE via eval, system, exec)
4. Insecure Deserialization
5. Broken Object Level Authorization (BOLA/IDOR)

For every valid vulnerability found, provide:
- The exact file and line numbers.
- The precise data flow path (Source -> Sink).
- A secure, refactored code snippet to fix it.

## Core rule: source-to-sink only

Do **not** report:
- Theoretical risks without a traced path
- Uses of `where_raw` / `select_raw` when user input is bound via placeholders
- Missing auth on routes that are intentionally public and expose no sensitive objects
- Dev-only helpers (e.g. `dd()`, CLI scripts) unless reachable in production

A valid finding requires **all** of:
1. **Source** — untrusted input (HTTP params, headers, cookies, `$_GET`/`$_POST`, REST body, file uploads, webhooks, cron secrets from config)
2. **Path** — no effective sanitizer, validator, allowlist, or ownership check between source and sink
3. **Sink** — dangerous execution (see reference)
4. **Exploitability** — attacker-controlled data reaches the sink at runtime

When tracing, read call chains across files. Name each hop: `Route.php:142 → Controller::show → Model::findRaw`.

## Audit workflow

Copy and track progress:

```
Audit Progress:
- [ ] Map entry points (REST routes, hooks, CLI, scheduler)
- [ ] SQLi: raw SQL + string concat sinks
- [ ] XSS: unescaped output sinks
- [ ] RCE: dynamic execution sinks
- [ ] Deserialization: unserialize / unsafe decode
- [ ] BOLA/IDOR: object access without ownership/policy check
- [ ] Write report (valid findings only)
- [ ] State "No findings" per category when clean
```

### Step 1: Map entry points

Search for:
- `Route::` registrations, `register_rest_route`, `WP_REST_Request`
- `Framework\Http\Request`, `$request->input`, `$request->get_params`
- `$_GET`, `$_POST`, `$_REQUEST`, `get_query_var`
- Scheduler/cron endpoints (`Scheduler.php`, background workers)
- Admin hooks and shortcodes

### Step 2: Category scans

Run targeted searches per category. Patterns and framework-specific sinks: [reference.md](reference.md).

**SQLi** — trace user input into:
- String concatenation in SQL (`"SELECT ... {$id}"`, `"WHERE col = '$val'"`)
- `where_raw` / `select_raw` / `Connection::select()` with interpolated input
- `Expression` values built from request data without binding

**XSS** — trace user input into:
- `echo`, `print`, inline HTML/JS without `esc_html`, `esc_attr`, `esc_url`, `wp_kses`
- JSON responses rendered in HTML without encoding
- Redirect URLs echoed into `<script>` tags

**RCE** — trace user input into:
- `eval`, `assert`, `create_function`, `preg_replace` with `/e`
- `exec`, `shell_exec`, `system`, `passthru`, `proc_open`, backticks
- `include`/`require` with user-controlled paths
- `call_user_func` / `call_user_func_array` with user-controlled callable

**Insecure deserialization** — trace user input into:
- `unserialize()` on request/cookie/session data
- `maybe_unserialize()` without type constraints
- JSON decoded then passed to dangerous magic methods without validation

**BOLA/IDOR** — trace object IDs from request into:
- `Model::find($id)`, `->where('id', $id)->first()` without `authorize()` / `PolicyManager::authorize()` / ownership check
- Update/delete by ID with only `AuthMiddleware` (logged-in) but no resource-level policy
- Compare: does the handler verify `$model->user_id === current_user_id()` (or equivalent)?

### Step 3: Validate sanitization

Before reporting, confirm the path lacks effective controls:
- **SQL**: parameterized bindings (`?` placeholders + `$wpdb->prepare` / `prepare_query`), integer casting for IDs, allowlisted columns
- **XSS**: context-appropriate escaping at output boundary
- **Auth**: `authorize()` on Form Request, `Guard::authorize()`, `PolicyManager::authorize()`, capability checks on the specific resource
- **Validation**: `Validator` rules (`IntegerRule`, `RegexRule`, `InRule`) — rules must constrain the value used at the sink, not a different field

### Step 4: Report

If **zero** valid findings: state that explicitly per category.

If findings exist, use this template for **each** vulnerability:

```markdown
## [SEVERITY] Title (e.g. SQL Injection in Order lookup)

**Category:** SQL Injection | XSS | RCE | Insecure Deserialization | BOLA/IDOR

**Location:** `path/to/file.php:42` (and additional lines if needed)

**Data flow:**
1. Source: `Request::input('order_id')` — `src/Http/Controllers/OrderController.php:28`
2. → passed to `OrderRepository::findById($id)` — `src/Repositories/OrderRepository.php:15`
3. → concatenated into SQL — `src/Repositories/OrderRepository.php:17`
4. Sink: `$this->connection->select("SELECT * FROM orders WHERE id = {$id}")` — `src/Repositories/OrderRepository.php:18`

**Impact:** [One sentence: what an attacker can do]

**Fix:**

\`\`\`php
// Before (vulnerable)
$sql = "SELECT * FROM orders WHERE id = {$id}";

// After (secure)
$sql = 'SELECT * FROM orders WHERE id = ?';
$rows = $this->connection->select($sql, [(int) $id]);
\`\`\`
```

Severity guide:
- **Critical** — RCE, SQLi with write access, auth bypass on sensitive data
- **High** — Stored/reflected XSS in authenticated context, IDOR on PII/financial records
- **Medium** — Reflected XSS low context, IDOR on non-sensitive resources
- **Low** — Defense-in-depth gaps with limited exploitability

## Additional resources

- Framework-specific sources, sinks, and grep patterns: [reference.md](reference.md)
