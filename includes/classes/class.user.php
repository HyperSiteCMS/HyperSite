<?php
/*
 * @package         HyperSite CMS
 * @file            class.user.php
 * @file_desc       Class to store and fetch all user information for use throughout the package.
 * @author          Ryan Morrison
 * @website         http://www.hypersite.info
 * @copyright       (c) 2017 HyperSite CMS
 * @license         http://opensource.org/licenses/gpl-license.php GNU Public License
 */

 /* Check if we are in CMS otherwise exit code. */
if (!defined('IN_HSCMS'))
{
	exit;
}

/* Main Code here */
class user
{
    public $user_info = array();
    
    function __construct() {
        $this->user_info = array(
            'user_id' => 0,
            'user_level' => 0,
            'username' => '',
            'user_email' => '',
            'logged_in' => 0,
            'date_registered' => 0,
            'user_founder' => 0,
            'permissions' => array(
                'is_admin' => 0,
                'can_mod_pages' => 0,
                'can_load_mod' => 0,
                'can_edit_users' => 0
            )
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
    function hash_password($password)
    {
        $hash_pass = password_hash($password, PASSWORD_DEFAULT);
        return $hash_pass;
    }
    function user_login($username, $password)
    {
        global $db;
        //First get password hash from database
        $query = "SELECT * FROM " . USERS_TABLE . " WHERE username='{$username}'";
        $result = $db->query($query);
        $info = $db->fetchrow($result);
        //Verify password match
        if (password_verify($password, $info['password']))
        {
            //Now lets check if password hash needs updated
            if (password_needs_rehash($info['password'], PASSWORD_DEFAULT))
            {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $query = "UPDATE " . USERS_TABLE . " SET password='{$new_hash}' WHERE username='{$username}'";
                $db->query($query);
            } 
            return $info;
        }
        else
        {
            return false;
        }
    }
    function user_logout($session)
    {
        global $db;
        $query = $db->build_query('delete',SESSION_TABLE,false,array('uniq_id' => $session));
        return $db->query($query);
    }
    function create_user($user_array)
    {
        global $db;
        $user_array['password'] = $this->hash_password($user_array['password']);
        $user_array['username'] = $db->clean($user_array['username']);
        $query = $db->build_query('insert', USERS_TABLE, $user_array);
        $result = $db->query($query);
        return $result;
    }
}
