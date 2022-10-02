<?php

use Dotenv\Dotenv;


if (!class_exists('SQLite3')) die('SQLite3 extension is not enabled');
require 'vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required([
  "EMAIL_COMPANY",
  "TINKOFF_TERMINAL_KEY",
  "TINKOFF_TERMINAL_PASSWORD"
]);

define('CONFIG', [
  "DB_PATH" =>  getenv('DB_PATH') ?: 'db.sqlite',
  "EMAIL_COMPANY" => getenv('EMAIL_COMPANY'),

  "TINKOFF_URL" => getenv('TINKOFF_URL') ?: 'https://securepay.tinkoff.ru/v2/Init',
  "TINKOFF_TERMINAL_KEY" => getenv('TINKOFF_TERMINAL_KEY'),
  "TINKOFF_TERMINAL_PASSWORD" => getenv('TINKOFF_TERMINAL_PASSWORD'),
  "TINKOFF_TAXATION" => getenv('TINKOFF_TAXATION') ?: 'usn_income',

  "ITEMS" => [
    'group' => [
      'name' => 'Мастер-класс по рисованию. Групповое занятие.',
      'price' => 150000,
      "tax" => "none",
    ],
    'individual' => [
      'name' => 'Мастер-класс по рисованию. Индивидуальное занятие.',
      'price' => 350000,
      "tax" => "none",
    ]
  ]
]);

global $db;
$db = new \SQLite3(CONFIG['DB_PATH']);
