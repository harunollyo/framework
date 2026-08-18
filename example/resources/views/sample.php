<?php

use Framework\Route;

use function Framework\include_view;
use function Framework\session;
use function Framework\view_data;

$name = \Framework\view_data('name');
?>
<div>Hello from template: <?php echo esc_html((string) $name); ?></div>
<?php include_view('info', ['data' => view_data('data')]); ?>

<?php if (session()->has('notice')): ?>
    <div class="notice"><?php echo esc_html((string) session()->get('notice')); ?></div>
<?php endif; ?>

<form action="<?php echo Route::site_url('cart.add'); ?>" method="post">
    <input type="hidden" name="product_id" value="1">
    <input type="number" name="quantity" value="1">
    <button type="submit">Add to cart</button>
</form>
