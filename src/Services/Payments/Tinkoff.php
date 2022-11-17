<?php

namespace App\Services\Payments;

class Tinkoff
{

  static function get_terminal_key()
  {
    return $_ENV['TINKOFF_TERMINAL_KEY'];
  }
  static function get_terminal_password()
  {
    return $_ENV['TINKOFF_TERMINAL_PASSWORD'];
  }

  static function get_payment_data($order, $item)
  {
    $payment_data = [
      "TerminalKey" => self::get_terminal_key(),
      "Amount" =>  $item['price'],
      "OrderId" => $order['order_id'],
      "DATA" => [
        "Phone" => $order['phone'],
        "Email" => $order['email']
      ],
      "Receipt" => [
        "Email" => $order['email'],
        "Phone" => $order['phone'],
        "Taxation" => CONFIG['TINKOFF_TAXATION'],
        "Items" => [
          [
            "Name" => $item['name'],
            "Price" => $item['price'],
            "Quantity" => 1.00,
            "Amount" => $item['price'],
            "PaymentMethod" => "full_prepayment",
            "PaymentObject" => "service",
            "Tax" => $item['tax'],
          ],
        ]
      ]
    ];
    return $payment_data;
  }

  static function init_payment($payment_data)
  {
    // Send payment data to Tinkoff
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CONFIG['TINKOFF_URL']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $response_data = json_decode($result);

    return $response_data;
  }

  /**
   * Filter data for frontend
   * Filter response data with fields Success, ErrorCode, Status, PaymentId, PaymentURL
   * 
   * @param $response_data array
   * 
   * @return array
   */
  static function prepare_response($response_data)
  {
    $response_data = [
      'Success' => $response_data->Success,
      'ErrorCode' => $response_data->ErrorCode,
      'Status' => $response_data->Status,
      'PaymentId' => $response_data->PaymentId,
      'PaymentURL' => $response_data->PaymentURL
    ];
    return $response_data;
  }

  static function process_payment_status()
  {
    $order_id = $_POST['OrderId'];
    $payment_status = $_POST['Status'];
    order_status_changed($order_id, $payment_status);
  }
}
