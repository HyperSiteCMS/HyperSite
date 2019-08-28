<?php

/*
 * @package         HyperSite CMS
 * @version         1.0.0
 * @file            index.php
 * @file_desc       The main file. Basically the file that is called at all times.
 * @author          Ryan Morrison
 * @website         -
 * @copyright       (c) 2019 HyperSite CMS
 * @license         http://opensource.org/licenses/gpl-license.php GNU Public License
 */


/* Main Code here */
error_reporting(E_ALL);
define('IN_HSCMS', true);
$phpex = "php";
$root_path = getcwd() . '/';
if (file_exists('install.' . $phpex)) {
    header("Location: ../install.{$phpex}");
    exit();
} else {
    require("{$root_path}includes/common.{$phpex}");
    $template->assign_var('MODULE_SUBLINKS', 0);
    $mode = strtolower(str_replace(' ', '_', $mode));
    $has_subs = false;
    //check if file exists in root directory for main modules
    if (file_exists("{$root_path}{$mode}.{$phpex}")) {
        include "{$root_path}{$mode}.{$phpex}";
    }
    //Now check if file exists in the modules directory
    else if (file_exists("{$root_path}modules/{$mode}/{$mode}.{$phpex}") && isset($modules->loaded[$mode])) {
        $template_file = "../../../";
        include "{$root_path}modules/{$mode}/{$mode}.{$phpex}";
    }
    //Otherwise load from database.
    else {
        $template->assign_var('INTRO_TEXT', html_entity_decode($config->config['site_intro']));
        if (file_exists($config->template_dir . '/' . $config->config['site_theme'] . '/template/' . $mode . '.html')) {
            $template_file = "{$mode}.html";
        } else if (file_exists($config->template_dir . '/' . $config->config['site_theme'] . '/template/' . $mode . '/index.html')) {
            $template_file = "{$mode}/index.html";
        } else {
            $template_file = "viewpage.html";
        }
        //Load the first level page. Ensure that it is a root page.
        $ModePage = $db->fetchrow($db->query("SELECT * FROM " . PAGES_TABLE . " WHERE page_identifier='{$mode}' AND page_parent=0"));
        if (isset($act) && $act != null) {
            //Load in first sub-page ensuring it's parent is the one used in $mode
            $ActPage = $db->fetchrow($db->query("SELECT * FROM " . PAGES_TABLE . " WHERE page_identifier='{$act}' AND page_parent={$ModePage['page_id']}"));
            if (isset($i) && $i != null) {
                //Load second level sub-page and ensure parent is from $act
                $IPage = $db->fetchrow($db->query("SELECT * FROM " . PAGES_TABLE . " WHERE page_identifier='{$i}' AND page_parent={$ActPage['page_id']}"));
                if (isset($p) && $p != null) {
                    //Now load the very last page ensuring parent is $i
                    $PPage = $db->fetchrow($db->query("SELECT * FROM " . PAGES_TABLE . " WHERE page_identifier='{$p}' AND page_parent={$IPage['page_id']}"));
                    if (isset($PPage['page_title'])) {
                        $template->assign_vars(array(
                            'PAGE_TITLE' => $PPage['page_title'],
                            'PAGE_TEXT' => html_entity_decode($PPage['page_text']) . "<br/><br/><br/>&laquo; <a href='./{$mode}/{$act}/{$i}/'>Return to Parent</a>"
                        ));
                        $page_parent = $PPage['page_parent'];
                        $this_page = $PPage['page_id'];
                    } else {
                        $template_file = "user/message.html";
                        $template->assign_vars(array(
                            'PAGE_TITLE' => '404 Error',
                            'MESSAGE' => 'The page you are trying to find does not exist, or the module is not loaded.'
                        ));
                    }
                } else {
                    if (isset($IPage['page_title'])) {
                        $template->assign_vars(array(
                            'PAGE_TITLE' => $IPage['page_title'],
                            'PAGE_TEXT' => html_entity_decode($IPage['page_text']) . "<br/><br/><br/>&laquo; <a href='./{$mode}/{$act}/'>Return to Parent</a>"
                        ));
                        $page_parent = $IPage['page_parent'];
                        $this_page = $IPage['page_id'];
                    } else {
                        $template_file = "user/message.html";
                        $template->assign_vars(array(
                            'PAGE_TITLE' => '404 Error',
                            'MESSAGE' => 'The page you are trying to find does not exist, or the module is not loaded.'
                        ));
                    }
                }
            } else {
                if (isset($ActPage['page_title'])) {
                    $template->assign_vars(array(
                        'PAGE_TITLE' => $ActPage['page_title'],
                        'PAGE_TEXT' => html_entity_decode($ActPage['page_text']) . "<br/><br/><br/>&laquo; <a href='./{$mode}/'>Return to Parent</a>"
                    ));
                    $page_parent = $ActPage['page_parent'];
                    $this_page = $ActPage['page_id'];
                } else {
                    $template_file = "user/message.html";
                    $template->assign_vars(array(
                        'PAGE_TITLE' => '404 Error',
                        'MESSAGE' => 'The page you are trying to find does not exist, or the module is not loaded.'
                    ));
                }
            }
        } else {
            if (!isset($ModePage['page_title'])) {
                $template_file = "user/message.html";
                $template->assign_vars(array(
                    'PAGE_TITLE' => '404 Error',
                    'MESSAGE' => 'The page you are trying to find does not exist, or the module is not loaded.'
                ));
            } else {
                $template->assign_vars(array(
                    'PAGE_TITLE' => $ModePage['page_title'],
                    'PAGE_TEXT' => html_entity_decode($ModePage['page_text'])
                ));
                $page_parent = $ModePage['page_parent'];
                $this_page = $ModePage['page_id'];
            }
        }
        $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$this_page}";
        $result = $db->query($query);
        $subpages = $db->fetchall($result);
        if (count($subpages) > 0) {
            $has_subs = true;
            $subs = '';
            foreach ($subpages as $sub) {
                $subs .= "<li><a href=\"./{$mode}/";
                if (isset($act) && $act != null && $act != $sub['page_identifier']) {
                    $subs .= "{$act}/";
                }
                if (isset($i) && $i != null && $i != $sub['page_identifier']) {
                    $subs .= "{$i}/";
                }
                $subs .= "{$sub['page_identifier']}/\">{$sub['page_title']}</a></li>";
            }
            $template->assign_var('SUB_PAGES', $subs);
        }
        //Now see if the parent page has subs
        if ($page_parent > 0) {
            $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$page_parent}";
            $parent_subs = $db->fetchall($db->query($query));
            if (count($parent_subs) > 0) {
                $parents = '';
                foreach ($parent_subs as $parent) {
                    $parents .= "<li><a href=\"./{$mode}/";
                    if (isset($act) && $act != null && $act != $parent['page_identifier']) {
                        $parents .= "{$act}/";
                    }
                    if (isset($i) && $i != null && $i != $parent['page_identifier']) {
                        $parents .= "{$i}/";
                    }
                    $parents .= "{$parent['page_identifier']}/\">{$parent['page_title']}</a></li>";
                }
            }
            $template->assign_vars(array(
                'HAS_PARENT_SUBS' => 1,
                'PARENT_SUBS' => $parents
            ));
        } else {
            $template->assign_var('HAS_PARENT_SUBS', 0);
        }
    }
}
$template->assign_var('HAS_SUBPAGES', $has_subs);
$template->set_filenames(array(
    'body' => $template_file
));

$template->display('body');
