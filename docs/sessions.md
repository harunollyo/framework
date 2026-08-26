# Sessions

This guide covers reading, writing, and flashing session state. The API follows Laravel's session API, adapted to the framework's snake_case method naming and the WordPress request lifecycle.

## Table of contents

1. [Limits you must know first](#1-limits-you-must-know-first)
2. [Sessions start lazily](#2-sessions-start-lazily)
3. [Reading and writing](#3-reading-and-writing)
4. [Flash data](#4-flash-data)
5. [Redirects, old input, and errors](#5-redirects-old-input-and-errors)
6. [Validation errors are not flashed for you](#6-validation-errors-are-not-flashed-for-you)
7. [Session identity](#7-session-identity)
8. [Configuration and drivers](#8-configuration-and-drivers)
9. [How sessions are stored and pruned](#9-how-sessions-are-stored-and-pruned)

---

## 1. Limits you must know first

**Session payloads are not encrypted.** Unlike Laravel, this framework has no encrypter. Everything you put in the session is stored in plain text, readable by anyone with database or object-cache access — other plugins, database backups, and any operator.

Never store passwords, API tokens, payment details, or capability flags in the session.

**Storage is cache-durable, not guaranteed-durable.** With the `database` driver, payloads are WordPress transients. When an external object cache (Redis, Memcached) is installed, WordPress serves transients from that cache instead of the options table, so a cache flush or a Redis restart discards every live session. The framework logs a notice at boot when it detects this. Never make anything security-critical depend on session state alone.

**There is no locking.** Two concurrent requests that read, modify, and write the same session are last-write-wins, and one update is silently lost. This matches Laravel's default. For anything needing atomicity — counters, stock reservations, balances — use options, post meta, or a real table.

**Sessions are per-site on multisite.** A session started on one subsite is not visible on another.

**The parity token is not enforced.** `Session::token()` exists for API parity, but the framework checks nothing. WordPress nonces remain the cross-site request forgery defence:

```php
// In your form
wp_nonce_field('save_profile');

// In your handler
check_admin_referer('save_profile');
```

## 2. Sessions start lazily

This is the main deviation from Laravel, and it is deliberate.

Laravel's `StartSession` middleware starts a session, sets a cookie, and writes a record on **every** request through the `web` group — even for an empty session from a bot. In WordPress that would be costly: Varnish, Batcache, WP Rocket, Cloudflare, and every managed host bypass full-page caching once a response carries an unrecognized `Set-Cookie`. Reproducing Laravel's behaviour would make the whole front end uncacheable for anonymous visitors.

So here, **nothing happens until you write**:

```php
// A page that only reads. No cookie, no stored payload, fully cacheable.
$theme = session('theme', 'light');

// This is what actually creates the session.
session(['theme' => 'dark']);
```

The practical consequences:

- Reads on a visitor with no session return your default, silently.
- `Session::get_id()` and `Session::token()` **force** a start, because they need an identity to return. Don't call them casually on a cacheable page.
- `Session::is_started()` tells you whether the session was touched this request. `Session::has_session()` tells you whether this visitor has one at all, including one not yet loaded.

## 3. Reading and writing

Use the `session()` helper or the `Session` facade.

```php
use Framework\Supports\Facades\Session;

use function Framework\session;

// The three-way helper, same as Laravel
$manager = session();                  // the manager
$value   = session('key', 'default');  // read
session(['key' => 'value']);           // write

// The same through the facade
Session::put('key', 'value');
Session::get('key', 'default');
```

Dot notation works throughout:

```php
Session::put('user.preferences.theme', 'dark');
Session::get('user.preferences.theme');
```

The full verb set:

```php
Session::all();                    // every value
Session::only(['a', 'b']);         // a subset
Session::has('key');               // present and not null
Session::exists('key');            // present, even if null
Session::missing('key');           // absent
Session::push('items', 'sku-1');   // append to an array
Session::increment('views');       // and decrement()
Session::remember('key', fn () => expensive());
Session::pull('key');              // read and remove
Session::forget('key');            // or an array of keys
Session::flush();                  // remove everything
```

You can also reach the session from the request, which is often more natural inside a controller:

```php
public function store(Request $request)
{
    $request->session()->put('last_seen', time());
}
```

Session values are held by the store, never on the request attributes. They never appear in `$request->all()`, `$request->input()`, `$request->__get()`, or validation input — so a stored value can never satisfy or override a validated field.

## 4. Flash data

Flashed values live for exactly one following request.

```php
Session::flash('status', 'Profile saved.');   // readable on the next request
Session::now('status', 'Shown immediately');  // this request only
Session::reflash();                           // keep everything one more request
Session::keep(['status']);                    // keep only these
```

Ageing happens when the session is saved: values flashed during this request become readable on the next one, and values that were readable on this one are dropped from the stored payload.

Ageing applies to what gets written, not to what the current request can read. A flashed value stays readable for the rest of the request that consumed it, because the response is often rendered *after* the save that emits the identifier cookie. It also means a request that saves more than once — the router flushes cookies, then a redirect sends — cannot age its flash data twice and lose it.

## 5. Redirects, old input, and errors

Redirect responses can carry flash data:

```php
use function Framework\back;
use function Framework\redirect;

return redirect('/profile')->with('status', 'Profile saved.');

return back()
    ->with_errors(['email' => ['That email is already taken.']])
    ->with_input();
```

On the page that follows, read them with the `old()` and `errors()` helpers:

```php
<input type="email" name="email" value="<?php echo esc_attr(old('email')); ?>">

<?php if (errors()->has('email')) : ?>
    <p class="error"><?php echo esc_html(errors()->first('email')); ?></p>
<?php endif; ?>
```

`errors()` returns a read-only `ErrorBag` with `any()`, `has()`, `first()`, `get()`, `all()`, `keys()`, and `count()`. The bag is built at read time — only plain arrays are ever stored, so renaming a class can never break a session already in flight.

### Old input never includes secrets

`with_input()` and `$request->flash()` **always** strip `password`, `password_confirmation`, and `current_password`, and never write uploaded files. This is not optional: the framework has no encrypter, so a flashed password would sit in `wp_options` or Redis in plain text for the session's whole lifetime.

Extend the list for your own fields:

```php
// config/session.php
'dont_flash' => ['api_key', 'card_number'],
```

The filter applies even when you pass an array explicitly, so `with_input(['password' => '...'])` still drops it.

### `back()` resolution order

`back($fallback = null)` resolves the target in this order:

1. `wp_get_referer()` — WordPress's own helper, which validates the referer is same-host
2. `Session::previous_url()`, but only if a session is already started
3. the `$fallback` you passed
4. `home_url('/')`

Resolving never starts a session, so `back()` is safe to call on a cacheable page. This is why the framework does not store the current URL on every request the way Laravel does — that would be a session write per request.

## 6. Validation errors are not flashed for you

Validation failures behave exactly as they always have. A site route ends the request with a 422 error page; a REST route returns 422 JSON. **Nothing is written to the session automatically.**

If you want post-redirect-get, opt in:

```php
use Framework\Exceptions\ValidationException;

try {
    $data = $request->validate([
        'email' => 'required|email',
    ]);
} catch (ValidationException $exception) {
    return back()
        ->with_errors($exception->get_errors())
        ->with_input();
}
```

## 7. Session identity

```php
Session::regenerate();       // new id, data kept
Session::regenerate(true);   // new id, previous payload destroyed
Session::invalidate();       // new id, data discarded
Session::migrate($destroy);  // the underlying operation
```

The parity token is regenerated whenever the identifier is.

The framework registers two default hooks so you don't have to:

- `wp_login` → `migrate()`, giving the visitor a new identifier while keeping their data. This is the standard session-fixation defence.
- `wp_logout` → `invalidate()`, discarding the data.

Both do nothing when the visitor has no session, so the lazy model holds for sites that never use sessions.

## 8. Configuration and drivers

Every key in `config/session.php` is optional; the framework resolves the same defaults when one is absent.

| Key | Default | Meaning |
| --- | --- | --- |
| `driver` | `database` | `database` (transients) or `array` (memory only) |
| `lifetime` | `120` | Minutes; the stored payload's expiry resets to this on every save |
| `expire_on_close` | `false` | Emit the id cookie as a browser-session cookie |
| `cookie` | `<prefix>session` | Name of the identifier cookie |
| `path` / `domain` | `/` / `null` | Cookie scope |
| `secure` | follows `is_ssl()` | Restrict the cookie to TLS |
| `http_only` | `true` | Hide the cookie from scripts |
| `same_site` | `Lax` | Same-site policy |
| `dont_flash` | `[]` | Extra fields never written as old input |

**Driver selection is never silently substituted.** An unknown driver name raises an error rather than falling back, so a typo cannot leave you with a non-persisting session in production.

The `array` driver keeps values in memory for the current request and persists nothing. It is for tests and for contexts where persistence is unwanted.

> **The `array` driver cannot support redirect-back.** Flash data written before a redirect is gone by the time the visitor arrives — the redirect still succeeds, the errors and old input are simply missing, and nothing reports a failure. Use the `database` driver anywhere post-redirect-get matters.

## 9. How sessions are stored and pruned

With the `database` driver, a session is one WordPress transient — which is two `wp_options` rows, the value and its timeout. WordPress handles expiry, so the framework adds no table and no cleanup schedule.

The identifier is 40 hexadecimal characters from a cryptographically secure source. An identifier arriving from the client is rejected unless it matches that format exactly, before it is ever used to build a storage key.

The session is saved at each point the framework hands a response to WordPress — `template_redirect` dispatch, `template_include` dispatch, redirects, and REST dispatch — plus a `shutdown` hook that catches admin, AJAX, and anything the others miss. The `template_include` save matters as much as the rest: that filter is the **default** dispatch hook for site routes, and WordPress includes the returned template immediately afterwards, so it is the last moment at which the identifier cookie can still be sent. These overlap by design: a redirect passes through three of them. Saving is idempotent — a session that never started, one that has not changed, or one whose flash data has already been aged into storage writes nothing. Once a generated identifier's cookie has been queued for emission, later writes in the same request — from a template, say — persist normally rather than tripping the late-write rule.

**Bookkeeping-only sessions are pruned.** When a save finds a session holding nothing but internal bookkeeping — the parity token, empty flash bookkeeping, a recorded previous URL — the stored payload is destroyed and the identifier cookie is expired instead of being written.

This matters more than it sounds. The most common way a session gets created is a one-off flash: a visitor sees a message, reads it on the next page, and never uses the session again. Without pruning, that visitor would hold a session cookie for the full lifetime and stay uncacheable because of a single flash. With pruning, they are back to fully cacheable one request later.

The corollary: `Session::token()` on its own does not keep a session alive.

Finally, if the first write to a **new** session happens after response headers have already been sent, the identifier cookie can never reach the client — so the payload is not written, and a warning is logged instead. A session whose identifier came from the request cookie is unaffected, because the client already holds it.
