<?php
error_reporting(E_ALL);
$phpex = "php";
$root_path = "./";
require("{$root_path}includes/common.{$phpex}");

$template->assign_vars(array(
   'SCRIPT_NAME' => $script_name[0],
    'PAGE_TITLE' => 'Home'
));
$template->set_filenames(array(
    'body' => $template_file
));
$template->display('body');
