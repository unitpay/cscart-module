<?php

define('AREA', 'C');
require(dirname(__FILE__) . '/../init.php');

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
                $result = $this->error( $params );
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

callbackHandler($_GET);