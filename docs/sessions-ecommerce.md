# Sessions in practice: an ecommerce walkthrough

Every session verb the framework exposes, shown through the flows a shop actually
needs: a cart, a coupon, a multi-step checkout, flash notices, form redisplay, and
the login and logout transitions.

Read [sessions.md](sessions.md) first for the mechanics. This document is the
cookbook, and it ends with the case matrix that
[`tests/Unit/Session/EcommerceSessionTest.php`](../tests/Unit/Session/EcommerceSessionTest.php)
asserts.

## Table of contents

1. [Configuration for a shop](#1-configuration-for-a-shop)
2. [The cart](#2-the-cart)
3. [Recently viewed products](#3-recently-viewed-products)
4. [Coupons and one-time values](#4-coupons-and-one-time-values)
5. [A multi-step checkout](#5-a-multi-step-checkout)
6. [Flash notices](#6-flash-notices)
7. [Redisplaying a failed form](#7-redisplaying-a-failed-form)
8. [Guest to customer](#8-guest-to-customer)
9. [Reading sessions in templates](#9-reading-sessions-in-templates)
10. [AJAX and REST endpoints](#10-ajax-and-rest-endpoints)
11. [What will bite you](#11-what-will-bite-you)
12. [Test case matrix](#12-test-case-matrix)

---

## 1. Configuration for a shop

```php
// example/config/session.php

return [
    'driver' => 'database',

    // A cart that dies after two hours annoys customers. Four hours is a
    // reasonable shop default; the expiry slides forward on every save, so
    // this is four hours of inactivity, not four hours total.
    'lifetime' => 240,

    'expire_on_close' => false,

    // Never write these to storage as old input, on top of the framework's
    // own password list.
    'dont_flash' => ['card_number', 'card_cvc', 'card_expiry'],
];
```

Card fields belong in `dont_flash` even if you never intend to flash them.
`with_input()` on a failed checkout would otherwise write a PAN into `wp_options`
in plaintext. The list is the guard against a mistake you haven't made yet.

---

## 2. The cart

The cart is the canonical "real" session value: written on one request, read on
many, and the thing that keeps the session alive.

```php
use Framework\Http\Request;
use Framework\Route;

use function Framework\back;
use function Framework\session;
use function Framework\view;

Route::site(function () {
    Route::post('cart/add', function (Request $request) {
        $id = $request->int('product_id');
        $quantity = max(1, $request->int('quantity', 1));

        // Dot notation writes into a nested array.
        $current = session("cart.items.{$id}.quantity", 0);
        session()->put("cart.items.{$id}", [
            'product_id' => $id,
            'quantity' => $current + $quantity,
        ]);

        return back()->with('notice', 'Added to your cart.');
    })->name('cart.add');

    Route::post('cart/remove', function (Request $request) {
        session()->forget('cart.items.' . $request->int('product_id'));

        return back()->with('notice', 'Item removed.');
    })->name('cart.remove');

    Route::get('cart', function (Request $request) {
        return view('cart', [
            'items' => session('cart.items', []),
        ]);
    })->name('cart.show');
});
```

The verbs, and when each earns its place:

| Call | Use |
| --- | --- |
| `session()->put($key, $value)` | Set one value, or pass an array to set many |
| `session($key, $default)` | Read with a fallback |
| `session()->has('cart.items')` | Present **and not null** |
| `session()->exists('cart.items')` | Present, even if null |
| `session()->missing('cart.items')` | The inverse of `exists` |
| `session()->forget($key)` | Remove one key or an array of keys |
| `session()->push('cart.coupons', $code)` | Append to an array without reading it first |
| `session()->increment('cart.count')` | Read-modify-write a counter in one call |
| `session()->all()` | Everything, including `_flash` and `_token` |
| `session()->only(['cart', 'currency'])` | A subset, as an array |
| `session()->flush()` | Clear everything but keep the identity |

An emptied cart is worth handling explicitly:

```php
Route::post('cart/clear', function (Request $request) {
    session()->forget('cart');

    // Nothing but bookkeeping is left, so the next save() prunes the session
    // entirely: transient deleted, cookie expired, visitor cacheable again.
    return back()->with('notice', 'Cart cleared.');
})->name('cart.clear');
```

That pruning is deliberate. A customer who empties their cart should stop paying
the cache-bypass cost of having one.

---

## 3. Recently viewed products

`push` appends; cap the list yourself, because nothing else will.

```php
Route::get('product/{slug}', function (Request $request, string $slug) {
    $recent = session('recently_viewed', []);

    // Move to the front, de-duplicate, cap at 6.
    $recent = array_values(array_unique(array_merge([$slug], $recent)));
    session()->put('recently_viewed', array_slice($recent, 0, 6));

    return view('product', ['slug' => $slug]);
})->name('product.show');
```

This is the one pattern to think twice about. Writing on a plain product view
means every visitor who lands on a product page gets a cookie and a transient,
and their product pages stop being cacheable. If your shop leans on full-page
caching, do recently-viewed in `localStorage` instead and keep the session for
state that genuinely must live server-side.

---

## 4. Coupons and one-time values

`pull` reads and removes in one call — right for anything that must not be
consumed twice.

```php
Route::post('checkout/apply-coupon', function (Request $request) {
    session()->put('checkout.coupon', $request->key('code'));

    return back()->with('notice', 'Coupon applied.');
});

// At the point of order creation:
$coupon = session()->pull('checkout.coupon');   // gone from the session now
```

`remember` computes once and caches for the rest of the session:

```php
$shipping_zone = session()->remember('shipping.zone', function () {
    return ShippingZone::resolve_for_current_visitor();   // expensive
});
```

`remember` uses `exists`, not `has`, so a legitimately cached `null` is not
recomputed on every request.

---

## 5. A multi-step checkout

Dot notation gives each step its own namespace under one key.

```php
Route::post('checkout/shipping', function (Request $request) {
    session()->put('checkout.shipping', $request->all([
        'name', 'line1', 'city', 'postcode',
    ]));

    return redirect(Route::site_url('checkout.payment'));
});

Route::get('checkout/payment', function (Request $request) {
    // Guard the step order: no shipping details, no payment step.
    if (session()->missing('checkout.shipping')) {
        return redirect(Route::site_url('checkout.shipping'))
            ->with('error', 'Enter your delivery address first.');
    }

    return view('checkout/payment');
});
```

After the order is placed, clear the working state but keep the session if the
cart or anything else still needs it:

```php
session()->forget(['checkout', 'cart']);
session()->flash('order_confirmation', $order->id);

return redirect(Route::site_url('order.thanks'));
```

`forget` accepts an array, so both keys go in one call.

---

## 6. Flash notices

Flash values survive exactly one following request.

| Call | Lifetime |
| --- | --- |
| `session()->flash('notice', $msg)` | Readable on the **next** request only |
| `session()->now('notice', $msg)` | Readable on **this** request only, never stored for the next |
| `session()->reflash()` | Keep every flashed value one more request |
| `session()->keep(['notice'])` | Keep only the named values one more request |
| `back()->with('notice', $msg)` | The redirect-flavoured form of `flash` |

`now` is for the case where you render directly instead of redirecting:

```php
Route::post('cart/add', function (Request $request) {
    if (!$product->is_in_stock()) {
        session()->now('error', 'That size just sold out.');

        return view('product', ['product' => $product]);   // no redirect
    }
    // ...
});
```

`reflash` matters when a request that should have displayed the notice bounces
somewhere else instead:

```php
Route::get('checkout', function (Request $request) {
    if (!is_user_logged_in()) {
        // The notice was flashed for this request; carry it through the detour
        // to the login page so it is not silently swallowed.
        session()->reflash();

        return redirect(wp_login_url(Route::site_url('checkout')));
    }

    return view('checkout');
});
```

---

## 7. Redisplaying a failed form

Validation errors are **not** flashed automatically — that was a deliberate
decision. `$request->validate()` throws, and the site exception handler renders a
422. If you want the redirect-back-with-errors experience, catch it and say so:

```php
use Framework\Exceptions\ValidationException;

Route::post('checkout/place-order', function (Request $request) {
    try {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'postcode' => 'required|string',
        ]);
    } catch (ValidationException $exception) {
        return back()
            ->with_errors($exception->get_errors())
            ->with_input();
    }

    // ... place the order ...
});
```

Then in the template:

```php
<?php if (errors()->any()): ?>
    <div class="notice notice-error">
        <?php foreach (errors()->all(true) as $message): ?>
            <p><?php echo esc_html($message); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<input
    name="email"
    value="<?php echo esc_attr(old('email')); ?>"
    class="<?php echo errors()->has('email') ? 'is-invalid' : ''; ?>">

<?php if (errors()->has('email')): ?>
    <span class="field-error"><?php echo esc_html(errors()->first('email')); ?></span>
<?php endif; ?>
```

`ErrorBag` surface: `any()`, `has($field)`, `first($field, $default)`,
`get($field)`, `all($flatten = false)`, `keys()`, `count()`.

### Old input is filtered, always

`with_input()` and `$request->flash()` both route through
`$request->flashable_input()`, which reads `$attributes` only and strips the
never-flash list. That means:

- Uploaded files are excluded by construction — they are not in `$attributes`.
- `password`, `password_confirmation`, `current_password` are always removed.
- Anything in `config('session.dont_flash')` is removed.

Passing an array explicitly is still filtered, so
`with_input(['card_cvc' => '123'])` cannot smuggle a value past the list.

To flash a narrower slice:

```php
$request->flash_only(['email', 'name']);        // just these
$request->flash_except(['card_number']);        // everything else
```

---

## 8. Guest to customer

The default hooks handle identity. You do not call these yourself.

| Event | What happens | Result for the shop |
| --- | --- | --- |
| `wp_login` | `migrate(false)` | New session id, **cart survives** the login |
| `wp_logout` | `invalidate()` | Payload destroyed, new empty id issued |

A guest builds a cart, logs in at checkout, and the cart is still there — the id
changes underneath them, which is the session-fixation defence, but the data
moves across. That is why the hook calls `migrate(false)` and not `invalidate()`.

If you want a server-side cart merged into a persisted customer cart, hook
`wp_login` at a later priority and do the merge yourself:

```php
add_action('wp_login', function ($login, $user) {
    $guest_items = session('cart.items', []);

    if (empty($guest_items)) {
        return;
    }

    CustomerCart::for_user($user->ID)->merge($guest_items);
    session()->forget('cart');
}, 20, 2);
```

Priority 20 runs after the framework's migration, so the session is already on
its new id when your merge runs.

The manual verbs, if you need them:

| Call | Effect |
| --- | --- |
| `session()->regenerate()` | New id, data kept, old payload left in storage |
| `session()->regenerate(true)` | New id, data kept, old payload destroyed |
| `session()->migrate($destroy)` | The same thing `regenerate` delegates to |
| `session()->invalidate()` | New id, data discarded, old payload destroyed |
| `session()->flush()` | Data cleared, **id unchanged** |

Reach for `regenerate(true)` after a privilege change — a customer becoming a
shop manager, say — so no readable payload is left behind on the old id.

---

## 9. Reading sessions in templates

```php
<?php
use function Framework\errors;
use function Framework\old;
use function Framework\session;
?>

<?php if ($notice = session('notice')): ?>
    <div class="notice"><?php echo esc_html($notice); ?></div>
<?php endif; ?>

<span class="cart-count">
    <?php echo (int) count(session('cart.items', [])); ?>
</span>
```

Reading in a template is safe for a visitor without a session: `session('notice')`
returns `null` and no session is created. Reading in a template for a visitor
**with** one starts it, which is fine — they already hold the cookie.

Never render `session()->all()` into a page. It contains `_token`, and it may
contain `_old_input` from a form the customer just submitted.

---

## 10. AJAX and REST endpoints

Sessions work in REST and `admin-ajax` exactly as they do on site routes; the
save happens on the REST cookie filter or the `shutdown` net.

```php
Route::post('cart/add', function (Request $request) {
    session()->increment('cart.count');

    return response()->json(['count' => session('cart.count')]);
});
```

Two things to know:

- A response that is already streaming cannot receive a `Set-Cookie`. If a
  **new** session is created after headers are sent, the write is skipped and a
  warning is logged, because the customer could never send that id back. An
  existing session still saves normally.
- Two concurrent AJAX writes are last-write-wins. Two "add to cart" clicks fired
  in the same instant can lose one. If that matters for your shop, keep the cart
  in a table and use the session only for the cart's id.

---

## 11. What will bite you

| Situation | What happens | What to do |
| --- | --- | --- |
| Cart written on every product view | Every visitor gets a cookie; page caching stops working for them | Only write when the customer acts |
| Object cache (Redis) flushed | Every live cart disappears | Persist orders-in-progress to a table; treat the session as cache-durable only |
| Two AJAX writes at once | Last write wins, one is lost | Session holds an id, the database holds the cart |
| Large product objects in the session | Every request serialises and unserialises the lot | Store ids, hydrate from the database |
| `session()->all()` rendered to the page | Leaks `_token` and old input | Read explicit keys |
| Multisite network | Sessions are per-site, not shared | Documented limit; do not rely on cross-site carts |
| Relying on `token()` for CSRF | Nothing enforces it | Use WordPress nonces |

---

## 12. Test case matrix

Every row below is asserted in
[`tests/Unit/Session/EcommerceSessionTest.php`](../tests/Unit/Session/EcommerceSessionTest.php).
Run them with:

```bash
composer test -- --filter EcommerceSessionTest
```

### Cart

| # | Case | Expected |
| --- | --- | --- |
| 1 | Browsing without adding anything | No id cookie, no storage write |
| 2 | Adding a product | One cookie, one payload, cart in the payload |
| 3 | Returning with the cookie | Cart reads back identically |
| 4 | Adding the same product twice | Quantity accumulates, one entry |
| 5 | Adding a second product | Both entries coexist under `cart.items` |
| 6 | Removing one of two products | The other survives |
| 7 | Clearing the cart | Session is pruned: payload destroyed, cookie expired |
| 8 | Reading a cart key that was never set | Default returned, no session started |

### Values and counters

| # | Case | Expected |
| --- | --- | --- |
| 9 | `push` onto recently viewed | Appends in order |
| 10 | `increment` / `decrement` on a cart count | Arithmetic is applied and stored |
| 11 | `remember` for a shipping zone | Callback runs once, second call reuses the value |
| 12 | `pull` on a coupon | Value returned once, absent afterwards |
| 13 | `has` vs `exists` on a null value | `has` false, `exists` true |
| 14 | `only` on a mixed session | Returns just the requested keys |
| 15 | `flush` on a cart | Data cleared, id unchanged |

### Flash

| # | Case | Expected |
| --- | --- | --- |
| 16 | Flash a notice, save, read next request | Value present |
| 17 | Same value one request later | Value gone, session pruned |
| 18 | `now` | Readable this request, absent after the save |
| 19 | `reflash` through a detour | Value survives the extra request |
| 20 | `keep` one of two flashed values | Named one survives, the other expires |

### Forms

| # | Case | Expected |
| --- | --- | --- |
| 21 | `with_input` on a checkout form | Fields readable through `old()` next request |
| 22 | `with_input` with a card number in `dont_flash` | Card number absent from the written payload |
| 23 | `with_input(['password' => …])` passed explicitly | Password absent from the written payload |
| 24 | `with_errors` from a `ValidationException` | Errors readable as a plain array; `ErrorBag` wraps them |
| 25 | `flash_only` / `flash_except` | Exactly the named subset is stored |

### Identity

| # | Case | Expected |
| --- | --- | --- |
| 26 | `migrate(false)` at login | Id changes, cart survives |
| 27 | `invalidate()` at logout | Id changes, cart gone, old payload destroyed |
| 28 | `regenerate(true)` | Id changes, data kept, old payload destroyed |
| 29 | A tampered id cookie | Rejected, storage never read with it, fresh id issued |
| 30 | `has_session()` before any read | True when a valid cookie is present, false otherwise |

### Persistence

| # | Case | Expected |
| --- | --- | --- |
| 31 | Save twice without changes | Second save is a no-op |
| 32 | New session created after headers sent | Write skipped, warning logged |
| 33 | Existing session saved after headers sent | Write proceeds |
| 34 | Lifetime from config | Payload written with `lifetime × 60` seconds |
| 35 | Array driver | Nothing survives into a second manager |

### Save points

| # | Case | Expected |
| --- | --- | --- |
| 36 | The redirect path saves twice (router flush, then send) | The flashed value still reaches storage |
| 37 | A save happens before the view renders | The flashed value is still readable during render |
