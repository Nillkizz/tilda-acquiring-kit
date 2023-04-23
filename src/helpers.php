<?php

/**
 * Check required fields in array
 *
 * @return bool True if all required fields are set
 */
function has_keys($needle_keys, $array): bool
{
  foreach ($needle_keys as $key) {
    if (!array_key_exists($key, $array)) {
      return false;
    }
  }

  return true;
}

/**
 * Make new array by keys
 *
 * @param array $array Data array
 * @param array $mapping Array New Key => Key
 * @return array Renamed array
 */
function map_array(array $array, array $mapping): array
{
  $result = [];
  foreach ($mapping as $new_key => $key) {
    $result[$new_key] = $array[$key];
  }

  return $result;
}

/**
 * Check required fields in $_POST
 */
function has_post_fields($keys): bool
{
  return has_keys($keys, $_POST);
}

/**
 * Check required fields in $_GET
 */
function has_get_fields($keys): bool
{
  return has_keys($keys, $_GET);
}

function env($key, $default = null)
{
  if (!isset($_ENV[$key])) {
    return $default;
  }

  return $_ENV[$key];
}

function session_date($date = null)
{
  // If date is null - return tomorrow
  if ($date == null) {
    $date = date('Y-m-d', time() + 86400);
  }
  // If date is string - convert to timestamp
  if (is_string($date)) {
    $date = strtotime($date);
  }

  return $date;
}

function w_log($message, $level = 'Debug'): void
{
  if (!env('DEBUG')) {
    return;
  }
  global $logger;

  if (!isset($logg)) {
    $logger = new Monolog\Logger('app');
    $handler = new Monolog\Handler\RotatingFileHandler('logs/app.log', 5);

    $formatter = new Monolog\Formatter\LineFormatter(
      null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
      null, // Datetime format
      true, // allowInlineLineBreaks option, default false
      true  // discard empty Square brackets in the end, default false
    );
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);
  }

  $level = (new ReflectionEnum("Monolog\Level"))->getCase($level)->getValue();
  $logger->log($level, $message);
}
