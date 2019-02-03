<?php
define(
    'TRIPLEDES_KEY',
    hash('sha256', 'adb859f71594cedc97376dbc678e42a8716409b042e1dd00')
);

define(
    'TRIPLEDES_IV',
    substr(
        hash('sha256', 'adb859f71594cedc97376dbc678e42a8716409b042e1dd00'),
        0,
        16
    )
);
