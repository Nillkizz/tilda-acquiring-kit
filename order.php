<?php

use App\Models\Order;
use App\Services\Payments\Tinkoff;
use App\Services\Pushka;

require 'config.php';

if (has_get_fields(['update_status'])) {
  $data = json_decode(file_get_contents('php://input'), true) ?? [];

  if (!has_keys(['OrderId', 'Status'], $data)) {
    w_log('order.php | Update_status | Invalid data: ' . json_encode($data), 'Error');
    return;
  }

  $order_id = $data['OrderId'];
  $payment_id = $data['PaymentId'];
  $status = $data['Status'];

  $order = Order::get($order_id);

  if (!$order) {
    w_log("order.php | Update_status | Order({$order_id}) not found", 'Error');
    return;
  }

  $order->status = $status;
  w_log("order.php | Update_status | Order({$order_id}) status updated: " . $status);

  if ($status == 'CONFIRMED') {
    w_log("order.php | Update_status | Confirm Order({$order_id})");
    $order->payment_datetime = time();
    $order->payment_amount = $data['Amount'] / 100;
    $response = Pushka::register_ticket($order);
    $order->set_ticket_id($response['id']);
    w_log("order.php | Update_status | Order({$order_id}) Confirmed | Ticket registered: " . $order->ticket_id);
  } elseif ($status == 'REJECTED') {
    w_log("order.php | Update_status | Order({$order_id}) rejected");
  } elseif ($status == 'REFUNDED') {
    w_log("order.php | Update_status | Refund Order({$order_id})");
    $response = Pushka::refund_ticket($order);
    w_log("order.php | Update_status | Order({$order_id}) Refunded | Ticket refunded: " . $order->ticket_id);
  }

  $order->save();
  w_log("order.php | Update_status | Order({$order_id}) saved");
}

if (has_get_fields(['create'])) {
  $order_data = parse_order();
  $order = Order::create($order_data);
  w_log('order.php | Create order' . "\nRequest data: " . print_r($_POST, true) . "\nOrder data: " . print_r($order, true));

  $response_data = process_tinkoff_payment($order->toArray());

  $response = Tinkoff::prepare_response($response_data);
  w_log('order.php | Tinkoff response: \n' . print_r($response, true));
  w_log('order.php | Set order payment_id: ' . $response['PaymentId']);
  $order->set_payment_id($response['PaymentId']);
  $order->save();
  w_log("order.php | Order {$order->order_id} saved after set order id");

  header('Content-Type: application/json');
  $response_json = json_encode($response);
  echo $response_json;
}
