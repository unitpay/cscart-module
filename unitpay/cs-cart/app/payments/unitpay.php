<?php

$sum = $order_info['total'];
$account = $order_id;
$desc = 'Заказ #' . $order_id;

$payment_url = 'https://unitpay.ru/pay/' . $processor_data['processor_params']['unitpay_public_key'];
$data = array(
    'sum' => $sum,
    'account'   =>  $account,
    'desc'  =>  $desc
);

fn_change_order_status($order_id, 'O');

fn_create_payment_form($payment_url, $data, 'Unitpay', false);