<?php
/*
 * @package         HyperSite CMS
 * @file            index.php
 * @file_desc       The main file. Basically the file that is called at all times.
 * @author          Ryan Morrison
 * @website         http://www.hypersite.info
 * @copyright       (c) 2017 HyperSite CMS
 * @license         http://opensource.org/licenses/gpl-license.php GNU Public License
 */


/* Main Code here */
error_reporting(E_ALL);
define('IN_HSCMS', true);
$phpex = "php";
$root_path = "./";
require("{$root_path}includes/common.{$phpex}");

//check if file exists in root directory for main modules
if (file_exists("{$root_path}{$mode}.{$phpex}"))
{
    include "{$root_path}{$mode}.{$phpex}";
}
//Now check if file exists in the modules directory
else if (file_exists("{$root_path}modules/{$mode}/{$mode}.{$phpex}"))
{
    $template_file = "../../../";
    include "{$root_path}modules/{$mode}/{$mode}.{$phpex}";
}
//Otherwise load from database.
else
{
    $template->assign_var('INTRO_TEXT', html_entity_decode($config->config['site_intro']));
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
         'PAGE_TEXT' => html_entity_decode($page['page_text'])
    ));
    $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$page['page_id']}";
    $result = $db->query($query);
    $subpages = $db->fetchall($result);
    if (count($subpages) > 0)
    {
        $template->assign_var('HAS_SUBPAGES', true);
        foreach ($subpages as $sub)
        {
            $template->assign_block_vars('subpages', array(
                'LINK' => $sub['page_identifier'],
                'TITLE' => $sub['page_title']
            ));
        }
    }
    //If current page is a sub-page, find other sub-pages with same parent
    if ($page['page_parent'] > 0)
    {
        $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$page['page_parent']}";
        $result = $db->query($query);
        $subpages = $db->fetchall($result);
        if (count($subpages) > 0)
        {
            $template->assign_var('HAS_SUBPAGES', true);
            foreach ($subpages as $sub)
            {
                $template->assign_block_vars('subpages', array(
                    'LINK' => $sub['page_identifier'],
                    'TITLE' => $sub['page_title']
                ));
            }
        }
    }
}
$template->set_filenames(array(
    'body' => $template_file
));
$template->display('body');
