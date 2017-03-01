<?php
if (isset($module))
{
    include "admin_{$module}.{$phpex}";
}
else
{
    switch ($act)
    {
        case 'addpage':
            if (!isset($_POST['save']))
            {
                $template_file = "admin_editpage.html";
            }
            else
            {
                $template_file = "admin_message.html";
            }
            break;
        case 'editpage':
            if (!isset($_POST['save']))
            {
                $template_file = "admin_editpage.html";
            }
            else
            {
                $template_file = "admin_message.html";
            }
            break;
        case 'delpage':
            $template_file = "admin_message.html";
            break;
        default:
            $template_file = "admin.html";
            $template->assign_vars(array(
               'PAGE_TITLE' => "Administrator Control Panel",
            ));
    }
}

