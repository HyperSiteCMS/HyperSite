<?php
/* 
 * Common.php
 * Loads all common functions and classes
 */
//Include Files
include 'config.php';
include './includes/classes/class.dbal.php';
include './includes/classes/class.forum.php';
include './includes/classes/class.shoutbox.php';
include './includes/classes/class.template.php';
include './includes/classes/class.user.php';
include './includes/classes/class.website.php';

//Load classes
$config = new $config;
$db = new dbal($config->mysql_server, $config->mysql_user, $config->mysql_pass, $config->mysql_db, $config->mysql_port);
$user = new user();
$template = new template();
$forum = new forum();
$shoutbox = new ShoutBox();
$website = new website();

