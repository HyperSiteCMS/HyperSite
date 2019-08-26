<?php

/*
 * @package         HyperSite CMS
 * @file            functions.php
 * @file_desc       Various functions for use throughout the site.
 * @author          Ryan Morrison
 * @website         -
 * @copyright       (c) 2019 HyperSite CMS
 * @license         http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/* Check if we are in CMS otherwise exit code. */
if (!defined('IN_HSCMS')) {
    exit;
}

/* Main Code here */

function request_var($var_name, $default, $cookie = false) {
    if (!$cookie && isset($_COOKIE[$var_name])) {
        if (!isset($_GET[$var_name]) && !isset($_POST[$var_name])) {
            return (is_array($default)) ? array() : $default;
        }
        $_REQUEST[$var_name] = isset($_POST[$var_name]) ? $_POST[$var_name] : $_GET[$var_name];
    }
    $super_global = ($cookie) ? '_COOKIE' : '_REQUEST';
    if (!isset($GLOBALS[$super_global][$var_name]) || is_array($GLOBALS[$super_global][$var_name]) != is_array($default)) {
        return (is_array($default)) ? array() : $default;
    }
    return $GLOBALS[$super_global][$var_name];
}

function gen_rand_string_($num_chars = 8) {
    $rand_str = unique_id();

    // Remove Z and Y from the base_convert(), replace 0 with Z and O with Y
    // [a, z] + [0, 9] - {z, y} = [a, z] + [0, 9] - {0, o} = 34
    $rand_str = str_replace(array('0', 'O'), array('Z', 'Y'), strtoupper(base_convert($rand_str, 16, 34)));

    return substr($rand_str, 0, $num_chars);
}

function unique_id() {
    $val = microtime();
    $val = md5($val);
    return substr($val, 4, 16);
}

function splitNewLine($text) {
    $code = explode('\r\n', $text);
    return $code;
}

function splitMatchLine($text) {
    return explode("\n", $text);
}

function generate_parent_select($current_parent = 0) {
    global $db;
    $return_text = "<select name=\"parent\">";
    $sql = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent=0 ORDER BY page_title ASC";
    $result = $db->query($sql);
    if ($current_parent == 0) {
        $return_text .= "<option value=\"0\" selected=\"selected\">Root</option>";
    } else {
        $return_text .= "<option value=\"0\">Root</option>";
    }
    while ($row = $db->fetchrow($result)) {
        if ($current_parent == $row['page_id']) {
            $return_text .= "<option value=\"{$row['page_id']}\" selected=\"selected\">{$row['page_title']}</option>";
        } else {
            $return_text .= "<option value=\"{$row['page_id']}\">{$row['page_title']}</option>";
        }
        $sql = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$row['page_id']} ORDER BY page_title ASC";
        $newresult = $db->query($sql);
        foreach ($db->fetchall($newresult) as $thisRow) {
            if ($current_parent == $thisRow['page_id']) {
                $return_text .= "<option value=\"{$thisRow['page_id']}\" selected=\"selected\">>{$thisRow['page_title']}</option>";
            } else {
                $return_text .= "<option value=\"{$thisRow['page_id']}\">>{$thisRow['page_title']}</option>";
            }
            $sql = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$thisRow['page_id']} ORDER BY page_title ASC";
            $moreResults = $db->query($sql);
            foreach ($db->fetchall($moreResults) as $newRow) {
                if ($current_parent == $newRow['page_id']) {
                    $return_text .= "<option value=\"{$newRow['page_id']}\" selected=\"selected\">-->{$newRow['page_title']}</option>";
                } else {
                    $return_text .= "<option value=\"{$newRow['page_id']}\">-->{$newRow['page_title']}</option>";
                }
            }
        }
    }
    $return_text .= "</select>";
    return $return_text;
}

function generate_theme_select($current = 'elegant_black') {
    global $config, $root_path;
    $return = "<select name=\"theme\">";
    $files = array_slice(scandir("{$root_path}{$config->template_dir}"), 2);
    foreach ($files as $file) {
        if (is_dir($root_path . $config->template_dir . '/' . $file)) {
            $info = json_decode(file_get_contents($root_path . $config->template_dir . '/' . $file . '/info.json'), true);
            if ($file == $current) {
                $return .= "<option value=\"{$file}\" selected=\"selected\">" . $info['name'] . "</option>";
            } else {
                $return .= "<option value=\"{$file}\">" . $info['name'] . "</option>";
            }
        }
    }
    $return .= "</select>";
    return $return;
}
