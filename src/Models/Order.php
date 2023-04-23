<?php

namespace App\Models;

/**
 * Order model class
 *
 * @method static void set_status(string $status) Set order status
 *
 * @property int $id
 * @property string $order_id
 * @property string $rrn
 * @property string $lastname
 * @property string $name
 * @property string $middlename
 * @property string $phone
 * @property string $email
 * @property string $payment_method
 * @property string $order_type
 * @property string $form_id
 * @property string $status
 * @property string $ticket_id
 * @property string $payment_id
 * @property string $payment_datetime
 * @property integer $payment_amount
 * @property array $_order_data Order data private field
 * @property array $_changed_fields Changed fields private field, used for update query
 * @property array $changed_fields Changed fields public field, used for update query
 */
class Order
{
  /**
   * @var array|mixed
   */
  private array $_order_data;

  private array $_changed_fields;

  const editable = [
    'status',
    'rrn',
    'payment_datetime',
    'payment_amount',
  ];

  public function __construct($order_data)
  {
    $this->_order_data = $order_data;
    $this->_changed_fields = [];
  }

  // Magic getter method for order_data
  public function __get($name)
  {
    if ($name == 'changed_fields') {
      return $this->changed_fields;
    }

    $value = $this->_order_data[$name];

    return $value;
  }

  // Magic setter method for order_data
  public function __set(string $name, int|string $value)
  {
    // If name starts with _, then it is a private property
    if (substr($name, 0, 2) == '_') {
      $this->$name = $value;
      return;
    }

    // If is not editable field, then return false
    if (!in_array($name, self::editable)) {
      return;
    }

    $this->_order_data[$name] = $value;
    $this->_changed_fields[] = $name;

    return $value;
  }

  // Set ticket_id if empty
  public function set_ticket_id($ticket_id): void
  {
    if (empty($this->ticket_id)) {
      $this->_order_data['ticket_id'] = $ticket_id;
      $this->_changed_fields[] = 'ticket_id';
    }
  }

  // Set payment_id if empty
  public function set_payment_id(string $payment_id): void
  {
    if (empty($this->payment_id)) {
      $this->_order_data['payment_id'] = $payment_id;
      $this->_changed_fields[] = 'payment_id';
    }
  }

  // Set payment_id if empty
  public function set_rrn(string $rrn): void
  {
    if (empty($this->rrn)) {
      $this->_order_data['rrn'] = $rrn;
      $this->_changed_fields[] = 'rrn';
    }
  }

  public static function update_rrn_for_order(string $order_id, string $rrn): void
  {
    $order = static::get($order_id);
    $order->rrn = $rrn;
    $order->save();
  }

  // Save method for update all fields
  public function save(): void
  {
    global $db;

    $query = 'UPDATE orders SET ';
    foreach ($this->_changed_fields as $field) {
      $query .= "$field = '{$this->_order_data[$field]}', ";
    }
    $query = substr($query, 0, -2);
    $query .= " WHERE order_id = '$this->order_id'";
    $db->exec($query);
    $this->_changed_fields = [];
  }

  /**
   * Get order by id
   *
   * @param $order_id int
   * @return Order|false
   */
  public static function get(int $order_id): Order|false
  {
    global $db;

    $order = $db->query("SELECT * FROM orders WHERE order_id = '$order_id'")->fetchArray(SQLITE3_ASSOC);
    if (!$order) {
      return false;
    }

    return new Order($order);
  }

  public static function get_last(): Order|false
  {
    global $db;

    $order = $db->query("SELECT TOP 1 * FROM orders ORDER BY ID DESC")->fetchArray(SQLITE3_ASSOC);
    if (!$order) {
      return false;
    }

    return new Order($order);
  }

  /**
   * @param $order_data array
   * @return Order saved order
   */
  public static function create(array $order_data): Order
  {
    global $db;

    $order_data['order_id'] = time() . mt_rand();
    $order_data['status'] = 'CREATED';
    $order_data['payment_datetime'] = null;
    $order_data['payment_amount'] = null;

    $phone = preg_replace('/[^0-9]/', '', $order_data['phone']);
    $phone = substr($phone, -10);
    $order_data['phone'] = $phone;

    $db->exec("INSERT INTO orders (
      lastname, 
      order_id,
      name, 
      middlename, 
      phone, 
      email, 
      payment_method, 
      form_id, 
      order_type,
      status
      ) 
    VALUES (
      '{$order_data['lastname']}', 
      '{$order_data['order_id']}',
      '{$order_data['name']}', 
      '{$order_data['middlename']}', 
      '{$order_data['phone']}', 
      '{$order_data['email']}', 
      '{$order_data['payment_method']}', 
      '{$order_data['form_id']}', 
      '{$order_data['order_type']}',
      '{$order_data['status']}'
    )");

    return new Order($order_data);
  }

  public function get_full_name(): string
  {
    return "$this->lastname $this->name $this->middlename";
  }

  public function toArray(): array
  {
    return $this->_order_data;
  }

}
