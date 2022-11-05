<?php

use App\Models\Order;
use App\Services\Payments\Tinkoff;
use App\Services\Pushka;

require 'config.php';

if (has_get_fields(['update_status'])) {
  $data = json_decode(file_get_contents('php://input'), true);
  $order_id = $data['OrderId'];
  $status = $data['Status'];

  $order = Order::get($order_id);

  $order->status = $status;

  if ($status == 'CONFIRMED') {
    $order->payment_datetime = time();
    $order->payment_amount = $data['Amount'] / 100;
  }
  
  $order->save();
  Pushka::register_ticket($order);

}

if (has_get_fields(['create'])) {
  $order_data = parse_order();
  $order = Order::create($order_data);

  $response_data = process_tinkoff_payment($order->toArray());

  $response = json_encode(Tinkoff::prepare_response($response_data));

  header('Content-Type: application/json');
  echo $response;
}