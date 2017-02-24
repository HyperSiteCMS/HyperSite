<?php
/* 
 * Common.php
 * Loads all common functions and classes
 */
require($root_path . "includes/config." . $phpex);

//Next include required classes and functions
require($root_path . "includes/functions." . $phpex);
require($root_path . "includes/classes/class.dbal." . $phpex);
require($root_path . "includes/classes/class.template." . $phpex);
require($root_path . "includes/classes/class.user." . $phpex);
require($root_path . "includes/classes/class.website." . $phpex);

$script_name = explode('.', basename(str_replace(array('\\', '//'), '/', $_SERVER['PHP_SELF'])));

//Load classes
$config = new config();
//$db = new dbal($config->mysql_server, $config->mysql_user, $config->mysql_pass, $config->mysql_db, $config->mysql_port);

$template = new template($config->template_dir . '/elegant_black/template/','default');
$mode = request_var('mode', 'index');
$i = request_var('i', null);
$template_file = $script_name[0] . '/' . $mode . (($i) ? '_' . $i : '') . '.html';

