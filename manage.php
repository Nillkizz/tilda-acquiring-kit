<?php

use App\Models\Order;

include 'config.php';

$command = $argv[1] ?? null;
$modificator = $argv[2] ?? null;

if ($command == 'migrate') {
  // If fresh and db exists - remove db.sqlite
  if ($modificator == 'fresh' && file_exists('db.sqlite')) {
    unlink('db.sqlite');
  }

  $db = new SQLite3('db.sqlite');
  include 'src/migrations/create_orders_table.php';
}

if ($command == 'show_last_order') {
  print_r(Order::get_last());
}
//if ($command == 'check_last_order') {
//}
