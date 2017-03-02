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
require("{$root_path}includes/classes/class.website.{$phpex}");
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
   'CREDIT_LINE' => "Powered by <a href=\"http://www.hypersite.info\">HyperSite v1.0 &copy;</a>",
   'ALLOW_USER_LOGIN' => $config->config['allow_users'],
   'SITE_THEME' => $config->config['site_theme'],
   'SERVER_NAME' => $_SERVER['SERVER_NAME']
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