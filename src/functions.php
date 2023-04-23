<?php

use App\Models\Order;
use App\Services\Payments\Tinkoff;

function parse_order(): array
{
  if (!has_post_fields(['lastname', 'name', 'middlename', 'Phone', 'Email', 'payment_method', 'tildaspec-formid', 'orderType'])) {
    throw new Exception('Not all required fields are set');
  }
  $order = map_array($_POST, [
    'lastname' => 'lastname',
    'name' => 'name',
    'middlename' => 'middlename',
    'phone' => 'Phone',
    'email' => 'Email',
    'payment_method' => 'payment_method',
    'form_id' => 'tildaspec-formid',
    'order_type' => 'orderType',
  ]);

  return $order;
}

/**
 * Gets order array and return response date for frontend
 *
 * @param $order array
 * @return Object
 */
function process_tinkoff_payment(array $order): object
{
  $item = CONFIG['ITEMS'][$order['order_type']];
  $payment_data = Tinkoff::get_payment_data($order, $item);
  $response_data = Tinkoff::init_payment($payment_data);

  return $response_data;
}

function order_status_changed($order_id, $status): void
{
  $order = Order::get($order_id);
  $order->set_status($status);

  if ($status == 'confirmed') {
    $item = CONFIG['ITEMS'][$order['order_type']];
    $payment_data = Tinkoff::get_payment_data($order, $item);
  }
}
