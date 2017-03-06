<?php
if (!$user->user_info['logged_in'] || !$user->user_info['permissions']['is_admin'])
{
    $template_file = "admin_message.html";
    $template->assign_var('MESSAGE', 'Error: You must be logged in with administrative permissions to view this page.');
    $template->assign_var('PAGE_TITLE', 'Error');
}
else
{
    if (isset($module))
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
                    $template_file = "admin_addpage.html";
                }
                else
                {
                    $template_file = "admin_message.html";
                }
                break;
            case 'editpage':
                $template->assign_var('PAGE_TITLE', 'ACP: Edit Page');
                if (!isset($_POST['save']))
                {
                    $template_file = "admin_editpage.html";
                    $query = "SELECT * FROM " . PAGES_TABLE . " WHERE page_id={$i}";
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
                    $page_title = request_var('page_name');
                    $page_info = array(
                       'page_title' => request_var('page_name', false),
                       'page_identifier' => request_var('page_identifier', false),
                       'page_parent' => request_var('parent', 0),
                       'page_text' => $db->clean(htmlentities(request_var('page_text', '')))
                    );
                    $where = array('page_id' => $i);
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
            case 'delpage':
                $template->assign_var('PAGE_TITLE', 'ACP: Delete Page');
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
