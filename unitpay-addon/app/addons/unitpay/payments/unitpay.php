<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

// Here are two different contexts for running the script.
if (defined('PAYMENT_NOTIFICATION')) {
    /**
     * Receiving and processing the answer
     * from third-party services and payment systems.
     *
     * Available variables:
     * @var string $mode The purpose of the request
     */
    callbackHandler($_GET);
} else {
    /**
     * Running the necessary logics for payment acceptance
     * after the customer presses the "Submit my order" button.
     *
     * Availablе variables:
     *
     * @var array $order_info     Full information about the order
     * @var array $processor_data Information about the payment processor
     */
    $sum = $order_info['total'];
    $account = $order_id;
    $desc = 'Заказ #' . $order_id;
    $currency = $order_info['secondary_currency'];

    $payment_url = 'https://' . $processor_data['processor_params']['unitpay_domain'] . '/pay/' . $processor_data['processor_params']['unitpay_public_key'];
    $secret_key = $processor_data['processor_params']['unitpay_secret_key'];
    $signature = hash('sha256', join('{up}', array(
        $account,
        $currency,
        $desc,
        $sum,
        $secret_key
    )));

    $data = array(
        'sum' => $sum,
        'currency' => $currency,
        'account'   =>  $account,
        'desc'  =>  $desc,
        'signature' => $signature
    );

    if (isset($order_info['email'])) {
        $data['customerEmail'] = $order_info['email'];
    }

    if (isset($order_info['phone'])) {
        $data['customerPhone'] = preg_replace('/\D/', '', $order_info['phone']);
    }

    if (isset($order_info['phone']) || isset($order_info['email'])) {
        $data['cashItems'] = getCashItems($order_info);
    }

    fn_change_order_status($order_id, 'O');

    fn_create_payment_form($payment_url, $data, 'Unitpay', false);
}


function callbackHandler($data)
{
    $method = '';
    $params = array();
    if ((isset($data['params'])) && (isset($data['method'])) && (isset($data['params']['signature']))){
        $params = $data['params'];
        $method = $data['method'];
        $signature = $params['signature'];
        if (empty($signature)){
            $status_sign = false;
        }else{
            $status_sign = verifySignature($params, $method);
        }
    }else{
        $status_sign = false;
    }

//    $status_sign = true;

    if ($status_sign){
        switch ($method) {
            case 'check':
                $result = check( $params );
                break;
            case 'pay':
                $result = pay( $params );
                break;
            case 'error':
                $result = error( $params );
                break;
            default:
                $result = array('error' =>
                                    array('message' => 'неверный метод')
                );
                break;
        }
    }else{
        $result = array('error' =>
                            array('message' => 'неверная сигнатура')
        );
    }
    hardReturnJson($result);
}


function check( $params )
{
    $order_id = $params['account'];
    $order_info = fn_get_order_info($order_id);

    if (is_null($order_info)){
        $result = array('error' =>
                            array('message' => 'заказа не существует')
        );
    }elseif ((float)$order_info['total'] != (float)$params['orderSum']) {
        $result = array('error' =>
                            array('message' => 'не совпадает сумма заказа')
        );
    }elseif (CART_PRIMARY_CURRENCY != $params['orderCurrency']) {
        $result = array('error' =>
                            array('message' => 'не совпадает валюта заказа')
        );
    }
    else{
        $result = array('result' =>
                            array('message' => 'Запрос успешно обработан')
        );
    }
    return $result;
}


function pay( $params )
{
    $order_id = $params['account'];
    $order_info = fn_get_order_info($order_id);

    if (is_null($order_info)){
        $result = array('error' =>
                            array('message' => 'заказа не существует')
        );
    }elseif ((float)$order_info['total'] != (float)$params['orderSum']) {
        $result = array('error' =>
                            array('message' => 'не совпадает сумма заказа')
        );
    }elseif (CART_PRIMARY_CURRENCY != $params['orderCurrency']) {
        $result = array('error' =>
                            array('message' => 'не совпадает валюта заказа')
        );
    }
    else{
        fn_change_order_status($order_id, 'P');

        $result = array('result' =>
                            array('message' => 'Запрос успешно обработан')
        );
    }
    return $result;
}


function error( $params )
{
    $order_id = $params['account'];
    $order_info = fn_get_order_info($order_id);
    if (is_null($order_info)){
        $result = array('error' =>
                            array('message' => 'заказа не существует')
        );
    }
    else{
        fn_change_order_status($order_id, 'F');

        $result = array('result' =>
                            array('message' => 'Запрос успешно обработан')
        );
    }
    return $result;
}


function getSignature($method, array $params, $secretKey)
{
    ksort($params);
    unset($params['sign']);
    unset($params['signature']);
    array_push($params, $secretKey);
    array_unshift($params, $method);
    return hash('sha256', join('{up}', $params));
}


function verifySignature($params, $method)
{
    $order_id = $params['account'];
    $order_info = fn_get_order_info($order_id);

    if (is_null($order_info)) {
        return false;
    }

    if (empty($processor_data)) {
        $processor_data = fn_get_processor_data($order_info['payment_id']);
    }
    $secret = $processor_data['processor_params']['unitpay_secret_key'];
    return $params['signature'] == getSignature($method, $params, $secret);
}


function hardReturnJson( $arr )
{
    header('Content-Type: application/json');
    $result = json_encode($arr);
    die($result);
}


function getCashItems($order_info)
{
    $currencyCode = $order_info['secondary_currency'];

    $orderProducts = array_map(function ($item) use ($currencyCode, $order_info) {
        $vat = 'none';

        if (isset($order_info['taxes'])) {
            foreach ($order_info['taxes'] AS $tax) {
                if (isset($tax['applies']['items']['P'][$item['item_id']]) &&
                    $tax['applies']['items']['P'][$item['item_id']]
                ) {
                    $vat = 'vat20';
                    break;
                }
            }
        }

        return array(
            'name'     => $item['product'],
            'count'    => $item['amount'],
            'price'    => round($item['price'], 2),
            'currency' => $currencyCode,
            'type'     => 'commodity',
            'nds'      => $vat,
        );
    }, $order_info['products']);

    if (isset($order_info['shipping'])) {
        foreach ($order_info['shipping'] AS $ship) {
            $vat = 'none';

            if (isset($order_info['taxes'])) {
                foreach ($order_info['taxes'] AS $tax) {
                    if (isset($tax['applies']['items']['S'][0][1]) &&
                        $tax['applies']['items']['S'][0][1]
                    ) {
                        $vat = 'vat20';
                        break;
                    }
                }
            }

            if (isset($ship['rate']) && ($ship['rate'] > 0)) {
                $orderProducts[] = array(
                    'name'     => $ship['shipping'],
                    'count'    => 1,
                    'price'    => round($ship['rate'], 2),
                    'currency' => $currencyCode,
                    'type'     => 'service',
                    'nds'      => $vat,
                );
            }
        }
    }

    return base64_encode(json_encode($orderProducts));
}
