# Cookies

This guide covers creating, sending, and reading cookies. The API follows Laravel's cookie API, adapted to the framework's snake_case method naming and the WordPress request lifecycle.

## Table of contents

1. [Security notice](#1-security-notice)
2. [Creating cookies](#2-creating-cookies)
3. [Sending cookies](#3-sending-cookies)
4. [Reading cookies](#4-reading-cookies)
5. [Deleting cookies](#5-deleting-cookies)
6. [Defaults and configuration](#6-defaults-and-configuration)
7. [Outbound HTTP client cookies](#7-outbound-http-client-cookies)
8. [How cookies are emitted](#8-how-cookies-are-emitted)

---

## 1. Security notice

**Cookie values are not encrypted or signed.** Unlike Laravel, this framework has no encrypter, so values are stored in plain text exactly as WordPress core stores its own cookies.

This means anything you read back from a cookie is **untrusted client input**. A user can read, edit, or forge any cookie your application sets.

```php
// NEVER do this — the client can set this to anything.
if ($request->cookie('is_admin') === '1') {
    // ...
}

// Validate against a trusted source instead.
$user = user();

if ($user->can('manage_options')) {
    // ...
}
```

Never store passwords, tokens, capability flags, or user identifiers that are trusted without verification. Treat cookie values the same way you treat query strings and form input: validate and sanitize before use.

## 2. Creating cookies

Use the `cookie()` helper or the `Cookie` facade. Both build a cookie **without sending it**.

```php
use Framework\Supports\Facades\Cookie;

use function Framework\cookie;

// cookie(name, value, minutes)
$cookie = cookie('preference', 'dark', 60);

// The same thing through the facade
$cookie = Cookie::make('preference', 'dark', 60);
```

A lifetime of `0` (the default) creates a **session cookie** that expires when the browser closes:

```php
$cookie = cookie('flash', 'saved');
```

For a long-lived cookie, use `forever()` (about five years):

```php
$cookie = Cookie::forever('remember_choice', 'yes');
```

The full signature, with every attribute:

```php
cookie(
    'name',                 // string  cookie name
    'value',                // string  cookie value
    60,                     // int     lifetime in minutes, 0 for a session cookie
    '/shop',                // ?string path
    'example.test',         // ?string domain
    true,                   // ?bool   secure (HTTPS only)
    true,                   // bool    http_only (hidden from JavaScript)
    false,                  // bool    raw (skip URL encoding of the value)
    'Lax'                   // ?string same_site: Lax, Strict, or None
);
```

Notes:

- `same_site` accepts any casing and is normalized to `Lax`, `Strict`, or `None`. An unrecognized value throws an `InvalidArgumentException`.
- `same_site` of `None` forces `secure` on, because browsers reject a non-secure `SameSite=None` cookie.
- Cookie names containing `=`, `,`, `;`, whitespace, or control characters are rejected, which blocks header injection through a cookie name.

Calling `cookie()` with no arguments returns the cookie manager itself:

```php
$manager = cookie();          // Framework\Managers\CookieManager
$manager->queue('a', 'b', 5);
```

## 3. Sending cookies

A cookie is only sent once it is **queued**. There are two equivalent ways.

### Queue it from anywhere

Useful in services, middleware, or any code that does not build the response:

```php
use Framework\Supports\Facades\Cookie;

Cookie::queue('preference', 'dark', 60);

// Or queue a cookie you already built
Cookie::queue(cookie('preference', 'dark', 60));
```

### Attach it to a response

```php
use function Framework\cookie;
use function Framework\response;

return response()->json(['saved' => true])
    ->with_cookie(cookie('preference', 'dark', 60));
```

`with_cookie()` also accepts the factory arguments directly, and `with_cookies()` takes several at once:

```php
return response()->json($data)
    ->with_cookie('preference', 'dark', 60);

return response()->json($data)->with_cookies([
    'theme' => 'dark',                          // name => value pair
    cookie('locale', 'en_US', 60 * 24),         // a Cookie instance
]);
```

Redirects work the same way:

```php
use function Framework\redirect;

return redirect(home_url('/thanks'))
    ->with_cookie('checkout_done', '1', 5);
```

> **Note:** `with_cookie()` queues the cookie immediately rather than storing it on the response object. A response that is built and then discarded still sends its cookie. Use `without_cookie()` to take it back.

```php
$response = response()->json($data)->with_cookie('preference', 'dark', 60);

$response->without_cookie('preference');   // never sent
```

## 4. Reading cookies

Read cookies from the request:

```php
public function index(Request $request)
{
    $theme = $request->cookie('preference');            // null when absent
    $theme = $request->cookie('preference', 'light');   // with a default

    if ($request->has_cookie('preference')) {
        // ...
    }

    $all = $request->cookies();                         // every cookie as an array
}
```

Cookies are also available on REST requests, even though `WP_REST_Request` itself carries no cookie parameters.

### Cookies are not request input

Cookies live in their own bag. They are deliberately **kept out of** `all()`, `get()`, the typed accessors (`get_int()`, `get_email()`, and so on), dynamic property access, and validation input:

```php
// Request arrives with a cookie named "role" and no body field "role"

$request->cookie('role');   // 'admin'  — the cookie value
$request->get('role');      // null     — cookies are not input
$request->role;             // null     — cookies are not input
$request->all();            // does not contain 'role'
```

This is intentional. If cookies were merged into the input, a forged cookie could satisfy or silently override a validated body field.

## 5. Deleting cookies

`forget()` builds a cookie that instructs the browser to delete an existing one, and `expire()` queues that in a single call:

```php
use Framework\Supports\Facades\Cookie;

// Queue the deletion
Cookie::expire('preference');

// Or build it and attach it to a response
return response()->json(['ok' => true])
    ->with_cookie(Cookie::forget('preference'));
```

**Path and domain must match** the cookie you are deleting, or the browser will not match it:

```php
Cookie::queue(Cookie::forget('preference', '/shop', 'example.test'));
```

## 6. Defaults and configuration

Attributes you do not supply are resolved in this order:

1. `config/cookie.php` in your application, when present
2. The WordPress environment — `COOKIEPATH`, `COOKIE_DOMAIN`, and `is_ssl()`
3. Framework fallbacks — path `/`, no domain, non-secure, `http_only` on, `SameSite=Lax`

No configuration is required. Reading `COOKIEPATH` and `COOKIE_DOMAIN` means cookies are scoped correctly on subdirectory and multisite installs out of the box.

To override them centrally, add `config/cookie.php`:

```php
<?php

return [
    'path' => '/',
    'domain' => null,
    'secure' => true,
    'same_site' => 'Strict',
];
```

You can also set the defaults at runtime during bootstrap:

```php
use Framework\Supports\Facades\Cookie;

Cookie::set_default_path_and_domain('/', 'example.test', true, 'Lax');
```

Per-cookie arguments always win over every default.

## 7. Outbound HTTP client cookies

Send cookies with outbound requests through the `Http` client:

```php
use Framework\Supports\Facades\Http;

$response = Http::with_cookie('session', 'abc123')
    ->get('https://api.example.test/profile');

// Several at once, with an optional domain
$response = Http::with_cookies([
    'session' => 'abc123',
    'locale' => 'en_US',
], 'api.example.test')->get('https://api.example.test/profile');
```

Cookies may be given as name/value pairs, as `Framework\Http\Cookie` instances, or as `WP_Http_Cookie` instances — all are normalized before the request is sent:

```php
Http::with_cookies([
    'plain' => 'value',
    cookie('built', 'from-factory'),
    new WP_Http_Cookie(['name' => 'wp', 'value' => 'native']),
])->get($url);
```

Read cookies returned by the remote host from the response:

```php
$response = Http::get('https://api.example.test/login');

$cookies = $response->cookies();
```

## 8. How cookies are emitted

Queued cookies are written at every point the framework sends a response:

| Response path | Where the queue is flushed |
| --- | --- |
| Site route response | `SiteRouter::send_response()`, before any header or output |
| Site route template | Before the route template is included |
| Redirect | `RedirectResponse::send()`, before `wp_safe_redirect()` |
| REST API | The `rest_post_dispatch` filter, before WordPress serializes the response |
| Ordinary page load | The `send_headers` action |

Flushing clears the queue, so a cookie is never sent twice.

### When output has already started

Cookies are HTTP headers, so they must be written before any output. If the queue is flushed after output has begun, the framework **skips the write and logs a warning** naming the cookie — it never raises an error or halts the request.

If a cookie does not appear in the browser, check the application log for a message like:

```
Cookie "preference" was not sent because the headers were already sent.
```

The usual cause is a route or template echoing content before the cookie is queued. Queue the cookie earlier, or attach it to the returned response instead.
