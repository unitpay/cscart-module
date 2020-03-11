<?php

$sum = $order_info['total'];
$account = $order_id;
$desc = 'Заказ #' . $order_id;

$payment_url = 'https://' . $processor_data['processor_params']['unitpay_domain'] . '/pay/' . $processor_data['processor_params']['unitpay_public_key'];
$secret_key = $processor_data['processor_params']['unitpay_secret_key'];
$signature = hash('sha256', join('{up}', array(
    $account,
    $desc,
    $sum,
    $secret_key
)));

$data = array(
    'sum' => $sum,
    'account'   =>  $account,
    'desc'  =>  $desc,
    'signature' => $signature
);

fn_change_order_status($order_id, 'O');

fn_create_payment_form($payment_url, $data, 'Unitpay', false);