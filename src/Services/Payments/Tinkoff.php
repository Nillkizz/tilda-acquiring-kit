<?php

namespace App\Services\Payments;

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

    public static function get_payment_data($order, $item)
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

  public static function init_payment($payment_data)
  {
    // Send payment data to Tinkoff
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CONFIG['TINKOFF_URL'] . "/Init");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    $headers = [];
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CONFIG['TINKOFF_URL'] . '/CheckOrder');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $headers = [];
    $headers[] = 'Content-Type: application/json';
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
   * @return array
   */
  public static function prepare_response($response_data)
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

  public static function process_payment_status()
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
    $values_concated = join($values);
    $token = hash('sha256', $values_concated);

    $data['Token'] = $token;
    return $data;
  }
}
