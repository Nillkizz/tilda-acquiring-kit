<?php

use App\Models\Order;
use App\Services\Payments\Tinkoff;
use App\Services\Pushka;

require 'config.php';

if (has_get_fields(['update_status'])) {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    if (! has_keys(['OrderId', 'Status'], $data)) {
        w_log('order.php | Update_status | Invalid data: '.json_encode($data), 'Error');

        return;
    }

    $order_id = $data['OrderId'];
    $status = $data['Status'];

    $order = Order::get($order_id);

    if (! $order) {
        w_log("order.php | Update_status | Order({$order_id}) not found", 'Error');

        return;
    }

    $order->status = $status;
    w_log("order.php | Update_status | Order({$order_id}) status updated: ".$status);

    if ($status == 'CONFIRMED') {
        $order->payment_datetime = time();
        $order->payment_amount = $data['Amount'] / 100;
        w_log("order.php | Update_status | Order({$order_id}) payment updated: ".$order->payment_amount);
        w_log("order.php | Update_status | Order({$order_id}) saved");
        $response = Pushka::register_ticket($order);

        $order->ticket_id = $response['id'];
    } elseif ($status == 'REJECTED') {
        w_log("order.php | Update_status | Order({$order_id}) rejected");
    } elseif ($status == 'CANCELED') {
        w_log("order.php | Update_status | Order({$order_id}) canceled");
        $response = Pushka::refund_ticket($order);
    }

    $order->save();
}

if (has_get_fields(['create'])) {
    $order_data = parse_order();
    $order = Order::create($order_data);
    w_log('order.php | Create order'."\nRequest data: ".print_r($_POST, true)."\nOrder data: ".print_r($order, true));

    $response_data = process_tinkoff_payment($order->toArray());

    $response = json_encode(Tinkoff::prepare_response($response_data));

    header('Content-Type: application/json');
    echo $response;
}
