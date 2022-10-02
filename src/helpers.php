<?php

/**
 * Check required fields in array
 * @param array $args Array of required fields keys
 * @return bool True if all required fields are set
 */
function has_keys($needle_keys, $array)
{
  foreach ($needle_keys as $key) {
    if (!array_key_exists($key, $array)) return false;
  }
  return true;
}

/**
 * Rename array keys
 * @param array $array Data array
 * @param array $mapping Array Key replcemnent
 * 
 * @return array Renamed array
 */
function map_array($array, $mapping)
{
  $result = [];
  foreach ($array as $key => $value) {
    if (isset($mapping[$key])) {
      $result[$mapping[$key]] = $value;
    }
  }
  return $result;
}


/**
 * Check required fields in $_POST
 */
function has_post_fields($keys)
{
  return has_keys($keys, $_POST);
}

/**
 * Check required fields in $_GET
 */
function has_get_fields($keys)
{
  return has_keys($keys, $_GET);
}
