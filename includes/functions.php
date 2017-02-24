<?php
function request_var($var_name, $default, $cookie = false)
{
    if (!$cookie && isset($_COOKIE[$var_name]))
    {
        if (!isset($_GET[$var_name]) && !isset($_POST[$var_name]))
        {
            return (is_array($default)) ? array() : $default;
        }
        $_REQUEST[$var_name] = isset($_POST[$var_name]) ? $_POST[$var_name] : $_GET[$var_name];
    }
    $super_global = ($cookie) ? '_COOKIE' : '_REQUEST';
    if (!isset($GLOBALS[$super_global][$var_name]) || is_array($GLOBALS[$super_global][$var_name]) != is_array($default))
    {
        return (is_array($default)) ? array() : $default;
    }
    return $GLOBALS[$super_global][$var_name]; 
}
function gen_rand_string_($num_chars = 8)
{
    $rand_str = unique_id();

    // Remove Z and Y from the base_convert(), replace 0 with Z and O with Y
    // [a, z] + [0, 9] - {z, y} = [a, z] + [0, 9] - {0, o} = 34
    $rand_str = str_replace(array('0', 'O'), array('Z', 'Y'), strtoupper(base_convert($rand_str, 16, 34)));

    return substr($rand_str, 0, $num_chars);
}
function unique_id()
{
	$val = microtime();
	$val = md5($val);
	return substr($val, 4, 16);
}
