<?php

use Framework\Supports\Facades\Option;

use function Framework\migrator;

return [
    'before_each' => function () {
        migrator()->run();
    },
    '0.0.1' => function () {
        Option::set('check_v_0_0_1', 'v0.0.1');
    },
    '1.0.0' => function () {
        Option::set('check_v_1_0_0', 'v1.0.0');
    },
    // 'after_each' => function () {
    //     Option::set('after_each_check_' . uuid(), time());
    // },
];
