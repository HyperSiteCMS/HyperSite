<?php
/* 
 * Common.php
 * Loads all common functions and classes
 */
//Include and load config
require("{$root_path}includes/config.{$phpex}");
$config = new config();

//Next include required classes and functions
require("{$root_path}includes/functions.{$phpex}");
require("{$root_path}includes/classes/class.dbal.{$phpex}");
require("{$root_path}includes/classes/class.template.{$phpex}");
require("{$root_path}includes/classes/class.user.{$phpex}");
require("{$root_path}includes/constants.{$phpex}");

//Open database connection
$db = new dbal($config->mysql_server, $config->mysql_user, $config->mysql_pass, $config->mysql_db, $config->mysql_port);
//Load settings from Database
$query = "SELECT * FROM " . SETTINGS_TABLE;
$result = $db->query($query);
$ar = $db->fetchall($result);
foreach ($ar as $key => $val)
{
    $config->config[$val['setting_name']] = $val['setting_value'];
}
$config->config['site_theme'] = str_replace(' ','_',$config->config['site_theme']);
$script_name = explode('.', basename(str_replace(array('\\', '//'), '/', $_SERVER['PHP_SELF'])));

//Load classes
$user = new user();
//Lets check if current user is logged in
if (isset($_COOKIE['hs_user_sess']))
{
    die('Cookie exists');
    $session = $_COOKIE['hs_user_sess'];
    $userinfo = $user->get_user('session', $session);
    if ($userinfo)
    {
        //Valid session so lets renew cookie and get info from database
        setcookie('hs_user_sess', $session, time()+(86400*30));
        $permissions = $user->get_permissions($userinfo['user_id']);
        $userinfo['permissions'] = $permissions;
        $userinfo['logged_in'] = true;
        $user->user_info = $userinfo;
    }
    else
    {
        //Not valid session so lets remove cookie
        setcookie('hs_user_sess', '', time() - 3600);
    }
}

$template = new template($config->template_dir . '/' . $config->config['site_theme'] . '/template/','default');
$mode = request_var('mode', 'home');
//Override mode if mode=index to stop a continuous loop
$mode = (($mode == 'index') ? 'home' : $mode);
$act = request_var('act', null);
$i = request_var('i', null);

//Load some sitewide variables into the template :)
$template->assign_vars(array(
   'SITE_TITLE' => $config->config['site_title'],
   'SITE_DESCRIPTION' => $config->config['site_desc'],
   'TINY_MCE_API' => $config->config['tiny_mce_api'],
   'CREDIT_LINE' => "Powered by <a href=\"http://www.hypersite.info\">HyperSite v1.0 &copy;</a>",
   'ALLOW_USER_LOGIN' => $config->config['allow_users'],
   'SITE_THEME' => $config->config['site_theme'],
   'SERVER_NAME' => $_SERVER['SERVER_NAME'],
   'USER_LOGGED_IN' => $user->user_info['logged_in'],
   'USER_IS_ADMIN' => $user->user_info['permissions']['is_admin']
       
));

//Load Navigation Bar
$query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent=0";
$result = $db->query($query);
$ar = $db->fetchall($result);
foreach ($ar as $navitem)
{
    $template->assign_block_vars('nav', array(
        'URL' => $navitem['page_identifier'],
        'TITLE' => $navitem['page_title']
    ));
}