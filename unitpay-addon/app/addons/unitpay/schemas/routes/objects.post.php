<?php

defined('BOOTSTRAP') or die('Access denied');

$schema['/unitpay_callback'] = [
    'dispatch' => 'payment_notification.notify',
    'payment'  => 'unitpay',
];

return $schema;