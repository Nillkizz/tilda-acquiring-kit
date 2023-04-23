<?php

use Dotenv\Dotenv;

if (!class_exists('SQLite3')) {
  exit('SQLite3 extension is not enabled');
}
require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required([
  'EMAIL_COMPANY',
  'TINKOFF_TERMINAL_KEY',
  'TINKOFF_TERMINAL_PASSWORD',
  'PUSHKA_API_KEY',
  'PUSHKA_EVENT_ID',
  'PUSHKA_ORGANIZATION_ID',
]);

define('CONFIG', [
  'DEBUG' => (bool)env('DEBUG', false),
  'DB_PATH' => env('DB_PATH', 'db.sqlite'),
  'EMAIL_COMPANY' => env('EMAIL_COMPANY'),

  'TINKOFF_URL' => env('TINKOFF_URL', 'https://securepay.tinkoff.ru/v2/Init'),
  'TINKOFF_TERMINAL_KEY' => env('TINKOFF_TERMINAL_KEY'),
  'TINKOFF_TERMINAL_PASSWORD' => env('TINKOFF_TERMINAL_PASSWORD'),
  'TINKOFF_TAXATION' => env('TINKOFF_TAXATION', 'usn_income'),

  'PUSHKA_URL' => env('PUSHKA_URL', 'https://pushka.gosuslugi.ru/api/v1'),
  'PUSHKA_API_KEY' => env('PUSHKA_API_KEY'),
  'PUSHKA_EVENT_ID' => env('PUSHKA_EVENT_ID'),
  'PUSHKA_ORGANIZATION_ID' => env('PUSHKA_ORGANIZATION_ID'),
  'PUSHKA_SESSION_DATE' => session_date(env('PUSHKA_SESSION_DATE')),

  'ITEMS' => [
    'group' => [
      'name' => 'Мастер-класс по рисованию. Групповое занятие.',
      'price' => 150000,
      'tax' => 'none',
    ],
    'individual' => [
      'name' => 'Мастер-класс по рисованию. Индивидуальное занятие.',
      'price' => 350000,
      'tax' => 'none',
    ],
  ],
]);

global $db;

$db = new \SQLite3(CONFIG['DB_PATH']);
