<?php

namespace App\Services\Payments;

use CurlHandle;

class Tinkoff
{
  public static function get_terminal_key()
  {
    return $_ENV['TINKOFF_TERMINAL_KEY'];
  }

  public static function get_terminal_password()
  {
    return $_ENV['TINKOFF_TERMINAL_PASSWORD'];
  }

  public static function get_payment_data($order, $item): array
  {
    $payment_data = [
      'TerminalKey' => self::get_terminal_key(),
      'Amount' => $item['price'],
      'OrderId' => $order['order_id'],
      'DATA' => [
        'Phone' => $order['phone'],
        'Email' => $order['email'],
      ],
      'Receipt' => [
        'Email' => $order['email'],
        'Phone' => $order['phone'],
        'Taxation' => CONFIG['TINKOFF_TAXATION'],
        'Items' => [
          [
            'Name' => $item['name'],
            'Price' => $item['price'],
            'Quantity' => 1.00,
            'Amount' => $item['price'],
            'PaymentMethod' => 'full_prepayment',
            'PaymentObject' => 'service',
            'Tax' => $item['tax'],
          ],
        ],
      ],
    ];

    return $payment_data;
  }

  public static function init_payment($payment_data): object
  {
    // Send payment data to Tinkoff
    $ch = static::curl_init('/Init', [
      'is_post' => 1,
      'data' => $payment_data,
    ]);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $response_data = json_decode($result);

    return $response_data;
  }

  public static function check_order($order_id)
  {
    $data['TerminalKey'] = static::get_terminal_key();
    $data['OrderId'] = $order_id;
    $data = static::sign_request_data($data);

    // Send payment data to Tinkoff
    $ch = static::curl_init('/CheckOrder', [
      'is_post' => 1,
      'data' => $data,
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $response_data = json_decode($result);

    return $response_data;
  }

  static function curl_init($url_path, $args = []): CurlHandle|false
  {
    $args = array_merge([
      'is_post' => 0,
      'data' => [],
      'headers' => ['Content-Type: application/json'],
      'add_headers' => []
    ], $args);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CONFIG['TINKOFF_URL'] . $url_path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if ($args['is_post']) {
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args['data']));
    }

    $headers = array_merge($args['headers'], $args['add_headers']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    return $ch;
  }

  /**
   * Filter data for frontend
   * Filter response data with fields Success, ErrorCode, Status, PaymentId, PaymentURL
   *
   * @param $response_data TinkoffInitPaymentResponse
   * @return array
   */
  public static function prepare_response(TinkoffInitPaymentResponse $response_data): array
  {
    $response_data = [
      'Success' => $response_data->Success,
      'ErrorCode' => $response_data->ErrorCode,
      'Status' => $response_data->Status,
      'PaymentId' => $response_data->PaymentId,
      'PaymentURL' => $response_data->PaymentURL,
    ];

    return $response_data;
  }

  public static function process_payment_status(): void
  {
    $order_id = $_POST['OrderId'];
    $payment_status = $_POST['Status'];
    order_status_changed($order_id, $payment_status);
  }

  public static function sign_request_data(array $data): array
  {
    $copy_data = array_filter($data, function ($v) {
      return !is_array($v);
    });
    $copy_data["Password"] = static::get_terminal_password();

    ksort($copy_data);
    $values = array_values($copy_data);
    $values_concatenated = join($values);
    $token = hash('sha256', $values_concatenated);

    $data['Token'] = $token;
    return $data;
  }
}

class TinkoffInitPaymentResponse
{ // TODO: Taken out to separate file
  public boolean $Success;
  public string $ErrorCode;
  public string $TerminalKey;
  public string $Status;
  public integer $PaymentId;
  public string $PaymentURL;
  public string $Details;
}