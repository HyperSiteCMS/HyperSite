<?php
if ($user->user_info['logged_in'] < 1 || $user->user_info['permissions']['is_admin'] < 1)
{
    $template_file = "admin_message.html";
    $template->assign_var('MESSAGE', 'Error: You must be logged in with administrative permissions to view this page.');
    $template->assign_var('PAGE_TITLE', 'Error');
}
else
{
    $module = request_var('mod', null);
    if (($module != null) && file_exists("{$root_path}/modules/{$module}/admin_{$module}.{$phpex}"))
    {
        include "{$root_path}/modules/{$module}/admin_{$module}.{$phpex}";
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
                    $template_file = "admin_message.html";
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
                    $template_file = "admin_editpage.html";
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
                    $template_file = "admin_message.html";
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
                $template_file = "admin_message.html";
                break;
            default:
                $template_file = "admin.html";
                $template->assign_vars(array(
                   'PAGE_TITLE' => "Admin CP",
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
        }
    }
}
