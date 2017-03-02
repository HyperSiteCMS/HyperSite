<?php
error_reporting(E_ALL);
$phpex = "php";
$root_path = "./";
require("{$root_path}includes/common.{$phpex}");

if (file_exists("{$mode}.{$phpex}"))
{
    include $mode . '.' . $phpex;
}
else
{
    $template->assign_var('INTRO_TEXT', $config->config['site_intro']);
    if (file_exists($config->template_dir . '/' . $config->config['site_theme'] . '/template/' . $mode . '.html'))
    {
        $template_file = "{$mode}.html";
    }
    else
    {
        $template_file = "viewpage.html";
    }
    $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_identifier='{$mode}'";
    $result = $db->query($query);
    $page = $db->fetchrow($result);
    $template->assign_vars(array(
         'PAGE_TITLE' => $page['page_title'],
         'PAGE_TEXT' => $page['page_text']
    ));
}
$template->set_filenames(array(
    'body' => $template_file
));
$template->display('body');
