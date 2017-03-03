<?php
/*
 * Class.User.PHP
 * User management class
 * (c) HyperSite 2017
 * Created by Ryan Morrison
 * License:
 */
class user
{
    var $user_info = array();
    
    function __construct() {
        $this->user_info = array(
            'user_id' => 0,
            'user_level' => 0,
            'username' => '',
            'user_email' => '',
            'logged_in' => false,
            'date_registered' => 0,
            'permissions' => array()
        );
    }
    function get_user($method, $value)
    {
        global $db;
        $query = "SELECT * FROM " . USERS_TABLE . " WHERE ";
        if ($method == 'session')
        {
            $query2 = "SELECT * FROM " . SESSION_TABLE . " WHERE uniq_id='{$value}'";
            $result = $db->query($query2);
            $sessinfo = $db->fetchrow($result);
            if (!$sessinfo['sess_id'])
            {
                return false;
            }
            $query .= "user_id={$sessinfo['user_id']}";
        }
        else if ($method == 'username')
        {
            $query .= "username='{$value}'";
        }
        else if ($method == 'email')
        {
            $query .= "user_email='{$value}'";
        }
        else 
        {
            $query .= "user_id={$value}";
        }
        $array = $db->fetchrow($db->query($query));
        if ($array['user_id'] < 1)
        {
            return false;
        }
        return $array;
    }
    function get_permissions($user_id)
    {
        global $db;
        $user_row = $db->fetchrow($db->query("SELECT * FROM " . USERS_TABLE . " WHERE user_id={$user_id}"));
        if ($user_row['date_registered'] < 1)
        {
            return false;
        }
        $levels = $db->fetchrow($db->query("SELECT * FROM " . LEVELS_TABLE . " WHERE level_id={$user_row['user_level']}"));
        return $levels;
    }
    function hash_password($username, $password)
    {
        global $config;
        $key_len = floor(strlen($username)/2);
        $key1 = substr($username, 0, $key_len);
        $key2 = substr($username, $key_len);
        $salt_len = floor(strlen($config->config['password_salt'])/2);
        $salt1 = substr($config->config['password_salt'], 0 , $salt_len);
        $salt2 = substr($config->config['password_salt'], $salt_len);
        $newpass = $key1.$salt2.$password.$salt1.$key2;
        $hash_pass = sha1($newpass);
        return $hash_pass;
    }
}
