<?php

namespace App\Services;

use App\Models\Order;

class Pushka
{
    public static function authenticate_headers($headers = [])
    {
        $headers[] = 'Authorization: Bearer '.CONFIG['PUSHKA_API_KEY'];

        return $headers;
    }

    public static function register_ticket(Order $order)
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
            ],
        ];
        $url = CONFIG['PUSHKA_URL'].'/tickets';
        w_log("src/Services/Pushka.php | Register_ticket | Order({$order->order_id}) | Request: ".print_r($request, true));

        ['status' => $status, 'response' => $response] = self::post($url, $request, static::authenticate_headers());
        $response = json_decode($response, true);

        w_log("src/Services/Pushka.php | Register_ticket | Order({$order->order_id}) | Response status: ".$status."\nResponse: ".print_r($response, true));
        if ($status == 400) {
            w_log("src/Services/Pushka.php | Register_ticket | Order({$order->order_id}) | Request Error: ".' ['.$response['code'].'] '.$response['description']);
            throw new \Exception('Pushka API error: '.$response['description']);
        }

        w_log("src/Services/Pushka.php | Register_ticket | Order({$order->order_id}) | Response: ".print_r($response, true));

        return $response;
    }

    public static function refund_ticket(Order $order)
    {
        $ticket_id = $order->ticket_id;
        w_log("src/Services/Pushka.php | Cancel_order | Order({$order->order_id}) | ticket_id: ".$ticket_id);
        $url = CONFIG['PUSHKA_URL'].'/tickets';
        $url = CONFIG['PUSHKA_URL']."/tickets/{$ticket_id}/refund";
        $body = [
            'refund_date' => time(),
            'refund_reason' => 'Посещение отменено',
        ];
        w_log("src/Services/Pushka.php | Cancel_order | Order({$order->order_id}) | Request: ".print_r($body, true));
        [ 'status' => $status, 'response' => $response ] = self::put($url, $body, static::authenticate_headers());
        $response = json_decode($response, true);
        w_log("src/Services/Pushka.php | Cancel_order | Order({$order->order_id}) | Response status: ".$status."\nResponse: ".print_r($response, true));
        if ($status == 400) {
            w_log("src/Services/Pushka.php | Cancel_order | Order({$order->order_id}) | Request Error: ".' ['.$response['code'].'] '.$response['description']);
            throw new \Exception('Pushka API error: '.$response['description']);
        }

        w_log("src/Services/Pushka.php | Cancel_order | Order({$order->order_id}) | Response: ".print_r($response, true));

        return $response;
    }

    public static function request($url, $opts = ['method' => 'GET', 'headers' => []])
    {
        extract($opts);
        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'Content-Type: application/json',
                ...$headers,
            ],
        ];
        if ($method == 'POST' || $method == 'PUT') {
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($opts['body']);
        }
        curl_setopt_array($curl, $curl_options);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return ['status' => $status, 'response' => $response];
    }

    public static function get($url, $headers = [])
    {
        return self::request($url, ['method' => 'GET', 'headers' => $headers]);
    }

    public static function post($url, $body, $headers = [])
    {
        return self::request($url, ['method' => 'POST', 'body' => $body, 'headers' => $headers]);
    }

    public static function put($url, $data = [], $headers = [])
    {
        $args = [
            'method' => 'PUT',
            'body' => json_encode($data),
            'headers' => $headers,
        ];

        return self::request($url, $args);
    }
}
