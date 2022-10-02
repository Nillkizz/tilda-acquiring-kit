<?php

namespace App\Services;

use App\Models\Order;

class Pushka
{
  static function register_ticket(Order $order)
  {
    $order_data = $order->toArray();
    $request = [
      ...map_array($order_data, [
        'order_id' => 'order_id',
        'barcode' => 'order_id',
      ]),
      'visitor' => [
        'full_name' => $order->get_full_name(),
        'first_name' => $order->name,
        'last_name' => $order->lastname,
        'middle_name' => $order->middlename,
      ],
      'buyer' => [
        'mobile_phone' => $order->phone,
      ],
      'session' => [
        'event_id' => CONFIG['PUSHKA_EVENT_ID'],
        'organization_id' => CONFIG['PUSHKA_ORGANIZATION_ID'],
        'date' => CONFIG['PUSHKA_SESSION_DATE'],
      ],
      'payment' => [
        'date' => $order->payment_datetime,
        'amount' => (string) $order->payment_amount,
      ]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => CONFIG['PUSHKA_URL'] . "/tickets",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($request),
      CURLOPT_HTTPHEADER => array(
        "accept: application/json",
        "Authorization: Bearer " . CONFIG['PUSHKA_API_KEY'],
        "Content-Type: application/json"
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    echo "<pre>";
    print_r([$request, $response]);
    echo "</pre>";
    die();


    return $response;
  }
}
