<?php

namespace App\Models;

/**
 * Order model class
 *
 * @method static Order get(int $id) Get order by id from database
 * @method static void save() Save order to database
 * @method static Order create(array $order_data) Create order from array
 * @method static void set_status(string $status) Set order status
 * @method static array toArray() Get order data as array
 * @method string get_full_name() Get full name of order owner
 *
 * @property int $id
 * @property string $order_id
 * @property string $lastname
 * @property string $name
 * @property string $middlename
 * @property string $phone
 * @property string $email
 * @property string $payment_method
 * @property string $order_type
 * @property string $form_id
 * @property string $status
 * @property array $_order_data Order data private field
 * @property array $_changed_fields Changed fields private field, used for update query
 */
class Order
{
    private $_order_data;

    private $_changed_fields;

    const editable = [
        'status',
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
    public function __set($name, $value)
    {
        // If name starts with _, then it is a private property
        if (substr($name, 0, 2) == '_') {
            $this->$name = $value;

            return;
        }

        if (! in_array($name, self::editable)) {
            return;
        }
        $this->_order_data[$name] = $value;
        $this->_changed_fields[] = $name;
    }

    // Save method for update all fields
    public function save()
    {
        global $db;

        $query = 'UPDATE orders SET ';
        foreach ($this->_changed_fields as $field) {
            $query .= "{$field} = '{$this->_order_data[$field]}', ";
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE order_id = '{$this->order_id}'";
        $db->exec($query);
        $this->_changed_fields = [];
    }

    /**
     * Get order by id
     *
     * @param $order_id int
     * @return Order|false
     */
    public static function get($order_id)
    {
        global $db;

        $order = $db->query("SELECT * FROM orders WHERE order_id = '{$order_id}'")->fetchArray(SQLITE3_ASSOC);
        if (! $order) {
            return false;
        }

        return new Order($order);
    }

    /**
     * @param $order_data array
     * @return Order saved order
     */
    public static function create($order_data)
    {
        global $db;

        $order_data['order_id'] = time().mt_rand();
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

    public function get_full_name()
    {
        return "{$this->lastname} {$this->name} {$this->middlename}";
    }

    public function toArray()
    {
        return $this->_order_data;
    }
}
