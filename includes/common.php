<?php

/*
 * @package         HyperSite CMS
 * @file            common.php
 * @file_desc       Loads classes and default values used throughout the site.
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
//Include and load config
require("{$root_path}includes/classes/class.config.{$phpex}");
$config = new config();

//Set timezone
date_default_timezone_set('Europe/London');
//Next include required classes and functions
require("{$root_path}includes/constants.{$phpex}");
require("{$root_path}includes/functions.{$phpex}");
require("{$root_path}includes/classes/class.dbal.{$phpex}");
require("{$root_path}includes/classes/class.template.{$phpex}");
require("{$root_path}includes/classes/class.user.{$phpex}");
require("{$root_path}includes/classes/class.modules.{$phpex}");

//Open database connection
$db = new dbal($config->mysql['server'], $config->mysql['user'], $config->mysql['pass'], $config->mysql['db'], $config->mysql['port']);
//Load settings from Database
$query = "SELECT * FROM " . SETTINGS_TABLE;
$result = $db->query($query);
$ar = $db->fetchall($result);
foreach ($ar as $key => $val) {
    $config->config[$val['setting_name']] = $val['setting_value'];
}
$config->config['site_theme'] = str_replace(' ', '_', $config->config['site_theme']);
$script_name = explode('.', basename(str_replace(array('\\', '//'), '/', $_SERVER['PHP_SELF'])));
//Load classes
$user = new user();
//Lets check if current user is logged in
if (isset($_COOKIE['hs_user_sess']) && (!isset($_POST['login']))) {
    $session = $db->clean($_COOKIE['hs_user_sess']);
    $userinfo = $user->get_user('session', $session);
    if ($userinfo) {
        //Valid session so lets renew cookie and get info from database
        setcookie('hs_user_sess', $session, time() + (86400 * 30), '/');
        $permissions = $user->get_permissions($userinfo['user_id']);
        $userinfo['permissions'] = $permissions;
        $userinfo['logged_in'] = true;
        $user->user_info = $userinfo;
    } else {
        //Not valid session so lets remove cookie
        setcookie('hs_user_sess', '', time() - 3600, '/');
    }
}
$modules = new modules();
$template = new template($config->template_dir . '/' . $config->config['site_theme'] . '/template/', 'default');
$mode = $db->clean(request_var('mode', 'home'));
//Override mode if mode=index to stop a continuous loop
$mode = (($mode == 'index') ? 'home' : $mode);
$act = $db->clean(request_var('act', null));
$i = $db->clean(request_var('i', null));
$p = $db->clean(request_var('p', null));

//$mail = new PHPMailer;
//Load some sitewide variables into the template :)
$template->assign_vars(array(
    'SITE_TITLE' => $config->config['site_title'],
    'SITE_DESCRIPTION' => $config->config['site_desc'],
    'CREDIT_LINE' => "Powered by <a href=\"http://github.com/HyperSiteCMS/HyperSite\">HyperSite v1.0 &copy;</a>",
    'ALLOW_USER_LOGIN' => $config->config['allow_users_login'],
    'ALLOW_USER_REG' => $config->config['allow_users_reg'],
    'SITE_THEME' => $config->config['site_theme'],
    'SERVER_NAME' => urlencode($_SERVER['SERVER_NAME']),
    'USER_LOGGED_IN' => $user->user_info['logged_in'],
    'USER_IS_ADMIN' => $user->user_info['permissions']['is_admin']
));
//Load Navigation Bar
$query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent=0";
$result = $db->query($query);
$ar = $db->fetchall($result);
foreach ($ar as $navitem) {
    $template->assign_block_vars('nav', array(
        'URL' => './' . $navitem['page_identifier'] . '/',
        'TITLE' => $navitem['page_title']
    ));
}
//Load other navigation urls..
$query = "SELECT * FROM " . NAV_TABLE . " WHERE area=1 OR area=3";
$nav = $db->fetchall($db->query($query));
foreach ($nav as $item) {
    $template->assign_block_vars('nav', array(
        'URL' => $item['url'],
        'TITLE' => $item['title']
    ));
}
//Load Sidebar Navigation Urls
$query = "SELECT * FROM " . NAV_TABLE . " WHERE area=2 OR area=3";
$sbnav = $db->fetchall($db->query($query));
foreach ($sbnav as $sb) {
    $template->assign_block_vars('sbnav', array(
        'URL' => $sb['url'],
        'TITLE' => $sb['title']
    ));
}
$template->assign_var('CURRENT_URL', './' . $mode . '/');
//Lets load events from Events module if loaded
if (isset($modules->loaded['events']['mod_name'])) {
    $template->assign_var('EVENTS_ACTIVE', 1);
    define('EVENTS_TABLE', $config->mysql['table_prefix'] . 'events');
    $Query = "SELECT * FROM " . EVENTS_TABLE . " WHERE event_time>'" . date('Y-m-d H:i:s') . "' ORDER BY event_time ASC LIMIT 1";
    $Event = $db->fetchrow($db->query($Query));
    $Date = date_create($Event['event_time']);
    $template->assign_vars(array(
        'NEXT_EVENT_TITLE' => $Event['event_title'],
        'NEXT_EVENT_ID' => $Event['id'],
        'NEXT_EVENT_DATE' => date_format($Date, 'd F Y'),
        'NEXT_EVENT_TIME' => date_format($Date, 'H:i')
    ));
} else {
    $template->assign_var('EVENTS_ACTIVE', 0);
}
