<?php

class Utilities
{
	private function __construct() {
	}

	public static function env($name, $default = NULL) {
		$value = getenv($name);
		if (empty($value) && $default === NULL) {
            die('Environment variable ' . $name . ' not found or has no value');
        }
        return empty($value) ? $default : $value;
	}

	public static function hasValue($array, $key) {
		return is_array($array) && array_key_exists($key, $array) && !empty($array[$key]);
	}
}
