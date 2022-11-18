<?php

global $db;

$db->exec('
  CREATE TABLE orders (
  id INTEGER PRIMARY KEY NOT NULL, 
  order_id TEXT,
  lastname TEXT,
  name TEXT,
  middlename TEXT,
  phone TEXT,
  email TEXT,
  payment_method TEXT,
  order_type TEXT,
  form_id TEXT,
  status TEXT,
  ticket_id TEXT,

  payment_datetime TEXT,
  payment_amount INT,

  CONSTRAINT unique_order_id UNIQUE (order_id)
)');
