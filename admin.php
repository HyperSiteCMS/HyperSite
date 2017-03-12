<?php
/*
 * @package         HyperSite CMS
 * @file            admin.php
 * @file_desc       Handles the administrative side for website owners.
 * @author          Ryan Morrison
 * @website         http://www.hypersite.info
 * @copyright       (c) 2017 HyperSite CMS
 * @license         http://opensource.org/licenses/gpl-license.php GNU Public License
 */

 /* Check if we are in CMS otherwise exit code. */
if (!defined('IN_HSCMS'))
{
	exit;
}

/* Main Code here */
define('IN_ACP', true);
if ($user->user_info['logged_in'] < 1 || $user->user_info['permissions']['is_admin'] < 1)
{
    $template_file = "admin/message.html";
    $template->assign_var('MESSAGE', 'Error: You must be logged in with administrative permissions to view this page.');
    $template->assign_var('PAGE_TITLE', 'Error');
}
else
{
    $template->assign_var('IN_ACP', true);
    //Lets create some dynamic links for sidebar relating to modules.
    foreach ($modules->loaded as $amod)
    {
        
        $template->assign_block_vars('sidebaradminlinks', array(
            'URL' => './admin/&mod=' . strtolower(str_replace(' ','_',$amod['mod_name'])),
            'TITLE' => $amod['mod_name']
        ));
    }
    $module = request_var('mod', null);
    if (($module != null) && file_exists("{$root_path}/modules/{$module}/admin_{$module}.{$phpex}"))
    {
        if (isset($modules->loaded[$module]))
        {
            $template_file = "../../../";
            include "{$root_path}/modules/{$module}/admin_{$module}.{$phpex}";
        }
        else
        {
            $template_file = "admin/message.html";
            $template->assign_vars(array(
                'PAGE_TITLE' => "Error",
                'MESSAGE' => "The selected module has not been loaded."
            ));
        }
    }
    else
    {
        switch ($act)
        {
            case 'addpage':
                $template->assign_var('PAGE_TITLE', 'ACP: New Page');
                if (!isset($_POST['save']))
                {
                    $template_file = "admin_newpage.html";
                    $parent_select = generate_parent_select(0);
                    $template->assign_vars(array(
                      'PARENT_SELECT' => $parent_select,
                        'FORM_ACTION' => './admin/addpage/'
                    ));
                }
                else
                {
                    $template_file = "admin/message.html";
                    $page_info = array(
                       'page_title' => $db->clean(request_var('page_name', false)),
                       'page_identifier' => $db->clean(request_var('page_identifier', false)),
                       'page_parent' => $db->clean(request_var('parent', 0)),
                       'page_text' => $db->clean(htmlentities(request_var('page_text', '')))
                    );
                    $query = $db->build_query('insert', PAGES_TABLE, $page_info);
                    $result = $db->query($query);
                    if (!$result)
                    {
                        $template->assign_var('MESSAGE', 'Error: Page failed to save');
                    }
                    else
                    {
                        $template->assign_var('MESSAGE', 'Success! Page saved.');
                    }
                }
                break;
            case 'editpage':
                $template->assign_var('PAGE_TITLE', 'ACP: Edit Page');
                if (!isset($_POST['save']))
                {
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
                }
                else
                {
                    $page_info = array(
                       'page_title' => $db->clean(request_var('page_name', false)),
                       'page_identifier' => $db->clean(request_var('page_identifier', false)),
                       'page_parent' => $db->clean(request_var('parent', 0)),
                       'page_text' => $db->clean(htmlentities(request_var('page_text', '')))
                    );
                    $where = array('page_id' => $db->clean($i));
                    $query = $db->build_query('update', PAGES_TABLE, $page_info, $where);
                    $result = $db->query($query);
                    if (!$result)
                    {
                        $template->assign_var('MESSAGE', 'Error: Page failed to save');
                    }
                    else
                    {
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
                if ($db->query($query))
                {
                    $template->assign_vars(array(
                        'PAGE_TITLE' => 'ACP: Delete Page Success',
                        'MESSAGE' => 'Page deleted successfully.'
                    ));
                    
                }
                else
                {
                    $template->assign_vars(array(
                        'PAGE_TITLE' => 'ACP: Delete Page Error',
                        'MESSAGE' => 'Unable to delete page.'
                    ));
                }
                $template_file = "admin/message.html";
                break;
            case 'modules':
                foreach ($modules->loaded as $loaded_mod)
                {
                    $template->assign_block_vars('loaded', array(
                        'NAME' => $loaded_mod['mod_name'],
                        'VERSION' => $loaded_mod['mod_version'],
                        'DESC' => $loaded_mod['mod_description'],
                        'AUTHOR' => $loaded_mod['mod_author'],
                        'FRIENDLY_URL' => strtolower(str_replace(' ', '_', $loaded_mod['mod_name']))
                    ));
                }
                
                foreach ($modules->unloaded as $unloaded_mod)
                {
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
                if ($loaded)
                {
                    $template->assign_vars(array(
                        'MESSAGE' => 'Module ' . $mod_name . ' has been loaded successfully.',
                        'PAGE_TITLE' => 'Module Loaded'
                    ));
                    $template_file = "admin/message.html";
                }
                else
                {
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
                if ($unloaded)
                {
                    $template->assign_vars(array(
                        'MESSAGE' => 'Module '.$mod_name.' has been unloaded successfully',
                        'PAGE_TITLE' => 'Module Unloaded'
                    ));
                    $template_file = "admin/message.html";
                }
                else 
                {
                    $template->assign_vars(array(
                        'MESSAGE' => 'Module '.$mod_name.' has not been unloaded!',
                        'PAGE_TITLE' => 'Module Failed to Unloaded'
                    ));
                    $template_file = "admin/message.html";
                }
                break;
            case 'users':
                break;
            case 'edit-user':
                break;
            case 'del-user':
                break;
            case 'ban-user':
                break;
            case 'nav':
                $template_file = "admin/nav.html";
                $query = "SELECT * FROM " . NAV_TABLE;
                $links = $db->fetchall($db->query($query));
                foreach ($links as $link)
                {
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
                if ($result = $db->query($query))
                {
                    $template->assign_vars(array(
                        'MESSAGE' => 1,
                        'MSG_TEXT' => 'Link deleted successfully'
                    ));
                }
                else
                {
                    $template->assign_vars(array(
                        'MESSAGE' => 1,
                        'MSG_TEXT' => 'Link not deleted'
                    ));
                }
                $template_file = "admin/nav.html";
                $query = "SELECT * FROM " . NAV_TABLE;
                $links = $db->fetchall($db->query($query));
                foreach ($links as $link)
                {
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
                if (!isset($_POST['save']))
                {
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
                    for ($x = 1; $x < 4; $x++)
                    {
                        if ($x == $row['area'])
                        {
                            $template->assign_block_vars('area_options', array(
                                'OP' => '<option value="' . $x . '" selected="selected">' . str_replace(array(1,2,3), array('Navigation Bar', 'Sidebar', 'Both'), $x) . '</option>'
                            ));
                        }
                        else
                        {
                            $template->assign_block_vars('area_options', array(
                                'OP' => '<option value="' . $x . '">' . str_replace(array(1,2,3), array('Navigation Bar', 'Sidebar', 'Both'), $x) . '</option>'
                            ));
                        }
                    }
                }
                else
                {
                    $template_file = 'admin/nav.html';
                    $template->assign_var('PAGE_TITLE', 'Navigation');
                    $nav_id = request_var('nav_id', 0);
                    $nav_array = array(
                        'title' => $db->clean(request_var('nav_title', '')),
                        'url' => $db->clean(request_var('nav_url', '')),
                        'area' => $db->clean(request_var('nav_area', ''))
                    );
                    if ($nav_id > 0)
                    {
                        $where = array('nav_id' => $nav_id);
                        $query = $db->build_query('update', NAV_TABLE, $nav_array, $where);
                    }
                    else
                    {
                        $query = $db->build_query('insert', NAV_TABLE, $nav_array);
                    }
                    $result = $db->query($query);
                    $template->assign_var('MESSAGE', 1);
                    if ($result)
                    {
                        $template->assign_var('MSG_TEXT', 'Link Added/Modified Successfully');
                    }
                    else
                    {
                        $template->assign_var('MSG_TEXT', 'Link not added/modified successfully!');
                    }
                    $query = "SELECT * FROM " . NAV_TABLE;
                    $links = $db->fetchall($db->query($query));
                    foreach ($links as $link)
                    {
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
                for ($x = 1; $x < 4; $x++)
                {
                    $template->assign_block_vars('area_options', array(
                        'OP' => '<option value="' . $x . '">' . str_replace(array(1,2,3), array('Navigation Bar', 'Sidebar', 'Both'), $x) . '</option>'
                    ));
                }
            break;
            case 'settings':
                break;
            case 'pages':
                $template_file = "admin/pages.html";
                $template->assign_vars(array(
                   'PAGE_TITLE' => "Page Management",
                ));
                $query = "SELECT * FROM " . PAGES_TABLE;
                $result = $db->query($query);
                $pages = $db->fetchall($result);
                foreach ($pages as $page)
                {
                    $template->assign_block_vars('pages', array(
                        'TITLE' => $page['page_title'],
                        'ID' => $page['page_id'],
                        'IDENTIFIER' => $page['page_identifier']
                    )); 
                }
                break;
            default:
                $template_file = "admin/index.html";
                $template->assign_var('PAGE_TITLE', 'Admin CP');
                break;
        }
    }
}
