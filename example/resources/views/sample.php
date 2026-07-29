<?php

$name = \Framework\view_data('name');
?>
<div>Hello from template: <?php echo esc_html((string) $name); ?></div>
