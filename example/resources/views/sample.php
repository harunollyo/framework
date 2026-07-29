<?php

use function Framework\include_view;
use function Framework\view_data;

$name = \Framework\view_data('name');
?>
<div>Hello from template: <?php echo esc_html((string) $name); ?></div>
<?php include_view('info', ['data' => view_data('data')]); ?>

