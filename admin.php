<?php

/*
 * @package         HyperSite CMS
 * @file            admin.php
 * @file_desc       Handles the administrative side for website owners.
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
define('IN_ACP', true);
if ($user->user_info['logged_in'] < 1 || $user->user_info['permissions']['is_admin'] < 1) {
    $template_file = "admin/message.html";
    $template->assign_var('MESSAGE', 'Error: You must be logged in with administrative permissions to view this page.');
    $template->assign_var('PAGE_TITLE', 'Error');
} else {
    $template->assign_var('IN_ACP', true);
    //Lets create some dynamic links for sidebar relating to modules.
    foreach ($modules->loaded as $amod) {

        $template->assign_block_vars('sidebaradminlinks', array(
            'URL' => './admin/' . strtolower(str_replace(' ', '_', $amod['mod_name'])) . '/',
            'TITLE' => $amod['mod_name']
        ));
    }
    if (isset($modules->loaded[$act]) && (file_exists("{$root_path}/modules/{$act}/admin_{$act}.{$phpex}"))) {
        $template_file = "../../../";
        include "{$root_path}/modules/{$act}/admin_{$act}.{$phpex}";
    } else {
        //Now all the default admin actions.
        switch ($act) {
            //Page management
            case 'addpage':
                $template->assign_var('PAGE_TITLE', 'ACP: New Page');
                if (!isset($_POST['save'])) {
                    $template_file = "admin/newpage.html";
                    $parent_select = generate_parent_select(0);
                    $template->assign_vars(array(
                        'PARENT_SELECT' => $parent_select,
                        'FORM_ACTION' => './admin/addpage/'
                    ));
                } else {
                    $template_file = "admin/message.html";
                    $page_info = array(
                        'page_title' => $db->clean(request_var('page_name', false)),
                        'page_identifier' => $db->clean(request_var('page_identifier', false)),
                        'page_parent' => $db->clean(request_var('parent', 0)),
                        'page_text' => $db->clean(htmlentities(request_var('page_text', '')))
                    );
                    $query = $db->build_query('insert', PAGES_TABLE, $page_info);
                    $result = $db->query($query);
                    if (!$result) {
                        $template->assign_var('MESSAGE', 'Error: Page failed to save');
                    } else {
                        $template->assign_var('MESSAGE', 'Success! Page saved.');
                    }
                }
                break;
            case 'editpage':
                $template->assign_var('PAGE_TITLE', 'ACP: Edit Page');
                if (!isset($_POST['save'])) {
                    $template_file = "admin/editpage.html";
                    $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_id={$db->clean($i)}";
                    $result = $db->query($query);
                    $page_info = $db->fetchrow($result);
                    $parent_select = generate_parent_select($page_info['page_parent']);
                    $template->assign_vars(array(
                        'THIS_TITLE' => $page_info['page_title'],
                        'THIS_IDENTIFIER' => $page_info['page_identifier'],
                        'PARENT_SELECT' => $parent_select,
                        'PAGE_TEXT' => $page_info['page_text'],
                        'FORM_ACTION' => "./admin/editpage/{$i}/"
                    ));
                } else {
                    $page_info = array(
                        'page_title' => $db->clean(request_var('page_name', false)),
                        'page_identifier' => $db->clean(request_var('page_identifier', false)),
                        'page_parent' => $db->clean(request_var('parent', 0)),
                        'page_text' => $db->clean(htmlentities(request_var('page_text', '')))
                    );
                    $where = array('page_id' => $db->clean($i));
                    $query = $db->build_query('update', PAGES_TABLE, $page_info, $where);
                    $result = $db->query($query);
                    if (!$result) {
                        $template->assign_var('MESSAGE', 'Error: Page failed to save');
                    } else {
                        $template->assign_var('MESSAGE', 'Success! Page information updated.');
                    }
                    $template_file = "admin/message.html";
                }
                break;
            case 'deletepage':
                $where = array('page_id' => $db->clean($i));
                //Before we delete, find any sub-pages and move them up a level
                $query = "UPDATE " . PAGES_TABLE . " SET page_parent=0 WHERE page_parent={$db->clean($i)};";
                $db->query($query);
                $query = $db->build_query('delete', PAGES_TABLE, false, $where);
                if ($db->query($query)) {
                    $template->assign_vars(array(
                        'PAGE_TITLE' => 'ACP: Delete Page Success',
                        'MESSAGE' => 'Page deleted successfully.'
                    ));
                } else {
                    $template->assign_vars(array(
                        'PAGE_TITLE' => 'ACP: Delete Page Error',
                        'MESSAGE' => 'Unable to delete page.'
                    ));
                }
                $template_file = "admin/message.html";
                break;
            case 'pages':
                $template_file = "admin/pages.html";
                $template->assign_vars(array(
                    'PAGE_TITLE' => "Page Management",
                ));
                $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent=0";
                $result = $db->query($query);
                $pages = $db->fetchall($result);
                foreach ($pages as $page) {
                    $subLevels1 = '';
                    $has_subs_1 = false;
                    $subpages = $db->fetchall($db->query("SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$page['page_id']}"));
                    if (count($subpages) > 0) {
                        $has_subs_1 = true;
                        foreach ($subpages as $subpage) {
                            $subLevels1 .= "<tr><td>`-{$subpage['page_title']}</td><td>{$page['page_identifier']}/{$subpage['page_identifier']}</td>";
                            $subLevels1 .= "<td><a href=\"./admin/editpage/{$subpage['page_id']}/\">Edit</a></td>";
                            $subLevels1 .= "<td><a href=\"./admin/deletepage/{$subpage['page_id']}/\">Delete</a></td></tr>";
                            //Now to check for level 2 sub-pages for each page...
                            $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$subpage['page_id']}";
                            $level2Subs = $db->fetchall($db->query($query));
                            if (count($level2Subs) > 0) {
                                foreach ($level2Subs as $Lv2) {
                                    $subLevels1 .= "<tr><td>&nbsp;`--{$Lv2['page_title']}</td><td>{$page['page_identifier']}/{$subpage['page_identifier']}/{$Lv2['page_identifier']}</td>";
                                    $subLevels1 .= "<td><a href=\"./admin/editpage/{$Lv2['page_id']}/\">Edit</a></td>";
                                    $subLevels1 .= "<td><a href=\"./admin/deletepage/{$Lv2['page_id']}/\">Delete</a></td></tr>";
                                    //And finally check for level 3 sub-pages
                                    $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_parent={$Lv2['page_id']}";
                                    $level3Subs = $db->fetchall($db->query($query));
                                    if (count($level3Subs) > 0) {
                                        foreach ($level3Subs as $Lv3) {
                                            $subLevels1 .= "<tr><td>&nbsp;&nbsp;&nbsp;`--->{$Lv3['page_title']}</td><td>{$page['page_identifier']}/{$subpage['page_identifier']}/{$Lv2['page_identifier']}/{$Lv3['page_identifier']}</td>";
                                            $subLevels1 .= "<td><a href=\"./admin/editpage/{$Lv3['page_id']}/\">Edit</a></td>";
                                            $subLevels1 .= "<td><a href=\"./admin/deletepage/{$Lv3['page_id']}/\">Delete</a></td></tr>";
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $template->assign_block_vars('pages', array(
                        'TITLE' => $page['page_title'],
                        'ID' => $page['page_id'],
                        'IDENTIFIER' => $page['page_identifier'],
                        'HAS_SUBS_1' => $has_subs_1,
                        'SUBS_1' => $subLevels1
                    ));
                }
                break;
            //Module Management
            case 'modules':
                foreach ($modules->loaded as $loaded_mod) {
                    $template->assign_block_vars('loaded', array(
                        'NAME' => $loaded_mod['mod_name'],
                        'VERSION' => $loaded_mod['mod_version'],
                        'DESC' => $loaded_mod['mod_description'],
                        'AUTHOR' => $loaded_mod['mod_author'],
                        'FRIENDLY_URL' => strtolower(str_replace(' ', '_', $loaded_mod['mod_name']))
                    ));
                }

                foreach ($modules->unloaded as $unloaded_mod) {
                    $template->assign_block_vars('unloaded', array(
                        'NAME' => $unloaded_mod['mod_name'],
                        'VERSION' => $unloaded_mod['mod_version'],
                        'DESC' => $unloaded_mod['mod_description'],
                        'AUTHOR' => $unloaded_mod['mod_author'],
                        'FRIENDLY_URL' => strtolower(str_replace(' ', '_', $unloaded_mod['mod_name']))
                    ));
                }
                $template_file = "admin/modules.html";
                $template->assign_var('PAGE_TITLE', 'ACP: Modules');
                break;
            case 'loadmod':
                $mod_name = $db->clean($i);
                $loaded = $modules->load_module($mod_name);
                if ($loaded) {
                    $template->assign_vars(array(
                        'MESSAGE' => 'Module ' . $mod_name . ' has been loaded successfully.',
                        'PAGE_TITLE' => 'Module Loaded'
                    ));
                    $template_file = "admin/message.html";
                } else {
                    $template->assign_vars(array(
                        'MESSAGE' => 'Module ' . $mod_name . ' has not been loaded.',
                        'PAGE_TITLE' => 'Module Failed to Load'
                    ));
                    $template_file = "admin/message.html";
                }
                break;
            case 'unloadmod':
                $mod_name = $db->clean($i);
                $unloaded = $modules->unload_module($mod_name);
                if ($unloaded) {
                    $template->assign_vars(array(
                        'MESSAGE' => 'Module ' . $mod_name . ' has been unloaded successfully',
                        'PAGE_TITLE' => 'Module Unloaded'
                    ));
                    $template_file = "admin/message.html";
                } else {
                    $template->assign_vars(array(
                        'MESSAGE' => 'Module ' . $mod_name . ' has not been unloaded!',
                        'PAGE_TITLE' => 'Module Failed to Unloaded'
                    ));
                    $template_file = "admin/message.html";
                }
                break;
            //User Management
            case 'users':
                $template_file = "admin/users.html";
                $template->assign_var('PAGE_TITLE', 'User Management');
                $template->assign_var('MESSAGE', 0);
                $sql = "SELECT * FROM " . USERS_TABLE;
                $all = $db->fetchall($db->query($sql));
                $template->assign_var('TOTAL_USERS', count($all));
                break;
            case 'edit-user':
                if (!isset($_POST['save'])) {
                    $template_file = "admin/edit-user.html";
                    $template->assign_var('PAGE_TITLE', 'Edit User');
                    $id = $db->clean(request_var('username', null));
                    $sql = "SELECT * FROM " . USERS_TABLE . " WHERE username='{$id}'";
                    $row = $db->fetchrow($db->query($sql));
                    if (!isset($row['user_id'])) {
                        $template_file = "admin/users.html";
                        $template->assign_var('PAGE_TITLE', 'User Management');
                        $template->assign_vars(array(
                            'MESSAGE' => 1,
                            'MSG_TEXT' => 'No user found with that Username!'
                        ));
                        $sql = "SELECT * FROM " . USERS_TABLE;
                        $all = $db->fetchall($db->query($sql));
                        $template->assign_var('TOTAL_USERS', count($all));
                        break;
                    } else if ($row['user_founder'] > 0 && $user->user_info['user_founder'] < 1) {
                        $template_file = "admin/users.html";
                        $template->assign_var('PAGE_TITLE', 'User Management');
                        $template->assign_vars(array(
                            'MESSAGE' => 1,
                            'MSG_TEXT' => 'Non-Founders may not edit founder accounts!'
                        ));
                        $sql = "SELECT * FROM " . USERS_TABLE;
                        $all = $db->fetchall($db->query($sql));
                        $template->assign_var('TOTAL_USERS', count($all));
                        break;
                    } else {
                        foreach ($row as $key => $val) {
                            $template->assign_var(strtoupper($key), $val);
                        }
                    }
                } else {
                    $sql = "SELECT * FROM " . USERS_TABLE;
                    $all = $db->fetchall($db->query($sql));
                    $template->assign_var('TOTAL_USERS', count($all));
                    $template->assign_var('PAGE_TITLE', 'User Management');
                    foreach ($_POST as $key => $val) {
                        if ($key != 'save') {
                            $post[$key] = $db->clean(request_var($key, null));
                        }
                    }
                    if ($post['password'] == "") {
                        unset($post['password']);
                    } else {
                        $post['password'] = $user->hash_password($post['password']);
                    }
                    unset($post['cur_pass']);
                    $where = array('user_id' => $post['user_id']);
                    $sql = $db->build_query('update', USERS_TABLE, $post, $where);
                    $template->assign_var('MESSAGE', 1);
                    if (!$db->query($sql)) {
                        $template->assign_var('MSG_TEXT', 'Error. Failed to update database:<br/>' . $db->error_msg);
                    } else {
                        $template->assign_var('MSG_TEXT', 'User updated successfully');
                    }
                    $template_file = "admin/users.html";
                }
                break;
            case 'del-user':
                $id = $db->clean($i);
                $sql = $db->build_query('delete', USERS_TABLE, false, array('user_id' => $id));
                if (!$db->query($sql)) {
                    $template->assign_var('MESSAGE', 1);
                    $template->assign_var('MSG_TEXT', 'Error. Failed to update database.<br/>' . $db->error_msg);
                } else {
                    $template->assign_var('MESSAGE', 1);
                    $template->assign_var('MSG_TEXT', 'User deleted Successfully');
                }
                $template_file = "admin/users.html";
                $template->assign_var('PAGE_TITLE', 'User Management');
                $sql = "SELECT * FROM " . USERS_TABLE;
                $all = $db->fetchall($db->query($sql));
                $template->assign_var('TOTAL_USERS', count($all));
                break;
            case 'ban-user':
                $id = $db->clean($i);
                $assoc_array = array(
                    'user_status' => 0,
                    'user_level' => 1
                );
                $where = array('user_id' => $id);
                $sql = $db->build_query('UPDATE', USERS_TABLE, $assoc_array, $where);
                if (!$db->query($sql)) {
                    $template->assign_var('MESSAGE', 1);
                    $template->assign_var('MSG_TEXT', 'Error. Failed to update database.<br/>' . $db->error_msg);
                } else {
                    $template->assign_var('MESSAGE', 1);
                    $template->assign_var('MSG_TEXT', 'User successfully updated (Suspension)');
                }
                $template_file = "admin/users.html";
                $template->assign_var('PAGE_TITLE', 'User Management');
                $sql = "SELECT * FROM " . USERS_TABLE;
                $all = $db->fetchall($db->query($sql));
                $template->assign_var('TOTAL_USERS', count($all));
                break;
            case 'unban-user':
                $id = $db->clean($i);
                $assoc_array = array(
                    'user_status' => 1,
                    'user_level' => 1
                );
                $where = array('user_id' => $id);
                $sql = $db->build_query('UPDATE', USERS_TABLE, $assoc_array, $where);
                if (!$db->query($sql)) {
                    $template->assign_var('MESSAGE', 1);
                    $template->assign_var('MSG_TEXT', 'Error. Failed to update database.<br/>' . $db->error_msg);
                } else {
                    $template->assign_var('MESSAGE', 1);
                    $template->assign_var('MSG_TEXT', 'User successfully updated (Un-Suspension)');
                }
                $template_file = "admin/users.html";
                $template->assign_var('PAGE_TITLE', 'User Management');
                $sql = "SELECT * FROM " . USERS_TABLE;
                $all = $db->fetchall($db->query($sql));
                $template->assign_var('TOTAL_USERS', count($all));
                break;
            case 'add-user':
                if (!isset($_POST['save'])) {
                    $template_file = "admin/add-user.html";
                    $template->assign_var('PAGE_TITLE', 'Add User');
                } else {
                    $sql = "SELECT * FROM " . USERS_TABLE;
                    $all = $db->fetchall($db->query($sql));
                    $template->assign_var('TOTAL_USERS', count($all));
                    $template->assign_var('PAGE_TITLE', 'User Management');
                    foreach ($_POST as $key => $val) {
                        if ($key != 'save') {
                            $post[$key] = $db->clean(request_var($key, null));
                            $user_array[$key] = $post[$key];
                        }
                    };
                    $user_array['password'] = $post['password'];
                    $user_array['user_status'] = 1;
                    $newuser = $user->create_user($user_array);
                    $template->assign_var('MESSAGE', 1);
                    if (!$db->query($sql)) {
                        $template->assign_var('MSG_TEXT', 'Error. Failed to update database:<br/>' . $db->error_msg);
                    } else {
                        $template->assign_var('MSG_TEXT', 'User Added successfully');
                    }
                    $template_file = "admin/users.html";
                }
                break;
            //Everything Else
            case 'themes':
                $template_file = "admin/themes.html";
                $template->assign_var('PAGE_TITLE', 'Themes');
                $files = array_slice(scandir("{$root_path}{$config->template_dir}"), 2);
                foreach ($files as $file) {
                    if (is_dir($root_path . $config->template_dir . '/' . $file)) {
                        $info = json_decode(file_get_contents($root_path . $config->template_dir . '/' . $file . '/info.json'), true);
                        $template->assign_block_vars('themes', array(
                            'NAME' => $info['name'],
                            'AUTHOR' => $info['author']
                        ));
                    }
                }
                break;
            case 'nav':
                $template_file = "admin/nav.html";
                $query = "SELECT * FROM " . NAV_TABLE;
                $links = $db->fetchall($db->query($query));
                foreach ($links as $link) {
                    $area = str_replace(array(1, 2, 3), array('Navigation Bar', 'Side Bar', 'Both'), $link['area']);
                    $template->assign_block_vars('cur_nav', array(
                        'URL' => $link['url'],
                        'TITLE' => $link['title'],
                        'ID' => $link['nav_id'],
                        'AREA' => $area
                    ));
                }
                $template->assign_var('PAGE_TITLE', 'Navigation');
                break;
            case 'del-nav':
                $template->assign_var('PAGE_TITLE', 'Navigation');
                $id = $db->clean($i);
                $where = array('nav_id' => $id);
                $query = $db->build_query('delete', NAV_TABLE, false, $where);
                if ($result = $db->query($query)) {
                    $template->assign_vars(array(
                        'MESSAGE' => 1,
                        'MSG_TEXT' => 'Link deleted successfully'
                    ));
                } else {
                    $template->assign_vars(array(
                        'MESSAGE' => 1,
                        'MSG_TEXT' => 'Link not deleted'
                    ));
                }
                $template_file = "admin/nav.html";
                $query = "SELECT * FROM " . NAV_TABLE;
                $links = $db->fetchall($db->query($query));
                foreach ($links as $link) {
                    $area = str_replace(array(1, 2, 3), array('Navigation Bar', 'Side Bar', 'Both'), $link['area']);
                    $template->assign_block_vars('cur_nav', array(
                        'URL' => $link['url'],
                        'TITLE' => $link['title'],
                        'ID' => $link['nav_id'],
                        'AREA' => $area
                    ));
                }
                break;
            case 'edit-nav':
                if (!isset($_POST['save'])) {
                    $template_file = 'admin/edit-nav.html';
                    $template->assign_var('PAGE_TITLE', 'Navigation');
                    $id = $db->clean($i);
                    $query = "SELECT * FROM " . NAV_TABLE . " WHERE nav_id={$id}";
                    $row = $db->fetchrow($db->query($query));
                    $template->assign_vars(array(
                        'NAV_TITLE' => $row['title'],
                        'NAV_URL' => $row['url'],
                        'AREA' => $row['area'],
                        'NAV_ID' => $row['nav_id']
                    ));
                    for ($x = 1; $x < 4; $x++) {
                        if ($x == $row['area']) {
                            $template->assign_block_vars('area_options', array(
                                'OP' => '<option value="' . $x . '" selected="selected">' . str_replace(array(1, 2, 3), array('Navigation Bar', 'Sidebar', 'Both'), $x) . '</option>'
                            ));
                        } else {
                            $template->assign_block_vars('area_options', array(
                                'OP' => '<option value="' . $x . '">' . str_replace(array(1, 2, 3), array('Navigation Bar', 'Sidebar', 'Both'), $x) . '</option>'
                            ));
                        }
                    }
                } else {
                    $template_file = 'admin/nav.html';
                    $template->assign_var('PAGE_TITLE', 'Navigation');
                    $nav_id = request_var('nav_id', 0);
                    $nav_array = array(
                        'title' => $db->clean(request_var('nav_title', '')),
                        'url' => $db->clean(request_var('nav_url', '')),
                        'area' => $db->clean(request_var('nav_area', ''))
                    );
                    if ($nav_id > 0) {
                        $where = array('nav_id' => $nav_id);
                        $query = $db->build_query('update', NAV_TABLE, $nav_array, $where);
                    } else {
                        $query = $db->build_query('insert', NAV_TABLE, $nav_array);
                    }
                    $result = $db->query($query);
                    $template->assign_var('MESSAGE', 1);
                    if ($result) {
                        $template->assign_var('MSG_TEXT', 'Link Added/Modified Successfully');
                    } else {
                        $template->assign_var('MSG_TEXT', 'Link not added/modified successfully!');
                    }
                    $query = "SELECT * FROM " . NAV_TABLE;
                    $links = $db->fetchall($db->query($query));
                    foreach ($links as $link) {
                        $area = str_replace(array(1, 2, 3), array('Navigation Bar', 'Side Bar', 'Both'), $link['area']);
                        $template->assign_block_vars('cur_nav', array(
                            'URL' => $link['url'],
                            'TITLE' => $link['title'],
                            'ID' => $link['nav_id'],
                            'AREA' => $area
                        ));
                    }
                }
                break;
            case 'add-nav':
                $template_file = 'admin/edit-nav.html';
                $template->assign_var('PAGE_TITLE', 'Navigation');
                $template->assign_vars(array(
                    'NAV_TITLE' => '',
                    'NAV_URL' => '',
                    'AREA' => 3,
                    'NAV_ID' => 0
                ));
                for ($x = 1; $x < 4; $x++) {
                    $template->assign_block_vars('area_options', array(
                        'OP' => '<option value="' . $x . '">' . str_replace(array(1, 2, 3), array('Navigation Bar', 'Sidebar', 'Both'), $x) . '</option>'
                    ));
                }
                break;
            case 'settings':
                $template->assign_var('PAGE_TITLE', 'Site Settings');
                if (!isset($_POST['save'])) {
                    $template_file = "admin/settings.html";
                    $sql = "SELECT * FROM " . SETTINGS_TABLE . " ORDER BY setting_id ASC";
                    $all_settings = $db->fetchall($db->query($sql));
                    foreach ($all_settings as $setting) {
                        $array[$setting['setting_name']] = $setting['setting_value'];
                    }
                    $template->assign_vars(array(
                        'SITE_INTRO' => html_entity_decode($array['site_intro']),
                        'TITLE' => $array['site_title'],
                        'DESC' => $array['site_desc'],
                        'ALLOW_USERS_LOGIN' => $array['allow_users_login'],
                        'ALLOW_USERS_REG' => $array['allow_users_reg'],
                        'THEME' => $array['site_theme'],
                        'THEME_SELECT' => generate_theme_select($array['site_theme']),
                    ));
                    for ($x = 7; isset($all_settings[$x]); $x++) {
                        $template->assign_block_vars('settings', array(
                            'NAME' => $all_settings[$x]['setting_name'],
                            'VALUE' => $all_settings[$x]['setting_value'],
                            'VIEW_NAME' => str_replace('_', ' ', ucwords($all_settings[$x]['setting_name']))
                        ));
                    }
                } else {
                    $post_array = $_POST;
                    $post = array();
                    foreach ($post_array as $key => $val) {
                        if ($key != 'save') {
                            $where = array('setting_name' => $key);
                            $what = array('setting_value' => $db->clean($val));
                            if ($key == 'site_intro') {
                                $val = htmlentities($db->clean($val));
                                $what = array('setting_value' => $val);
                            }
                            $sql = $db->build_query('update', SETTINGS_TABLE, $what, $where);
                            $db->query($sql) or die('Failed to update settings');
                        }
                    }
                    $template_file = "admin/message.html";
                    $template->assign_var('MESSAGE', 'Site Settings Updated');
                }
                break;
            default:
                $template_file = "admin/index.html";
                $template->assign_var('PAGE_TITLE', 'Admin CP');
                break;
        }
    }
}
