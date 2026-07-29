<?php

use function Framework\view_data;

$data = view_data('data');
?>

<h1>Info</h1>

<p>Name: <?php echo esc_html((string) view_data('data.name')); ?></p>
<p>Age: <?php echo esc_html((string) view_data('data.age')); ?></p>
<p>Email: <?php echo esc_html((string) view_data('data.email')); ?></p>
<p>Phone: <?php echo esc_html((string) view_data('data.phone')); ?></p>
<p>Address: <?php echo esc_html((string) view_data('data.address')); ?></p>
<p>City: <?php echo esc_html((string) view_data('data.city')); ?></p>
<p>State: <?php echo esc_html((string) view_data('data.state')); ?></p>
<p>Zip: <?php echo esc_html((string) view_data('data.zip')); ?></p>
