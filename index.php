<?php
error_reporting(E_ALL);
$phpex = "php";
$root_path = "./";
require("{$root_path}includes/common.{$phpex}");

$template->assign_vars(array(
   'SCRIPT_NAME' => $script_name[0],
   'PAGE_TITLE' => ucfirst($mode),
));

switch ($mode)
{
    default:
    case 'home':
        //Manually override the template file variable
        $template_file = "{$script_name[0]}/index.html";
    break;
    case 'view':
        //Cover bases so that mode=view will show home page if no page is selected.
        if ($i == 'home' || $i == null)
        {
            //Manually set template file
            $template_file = "{$script_name[0]}/index.html";
        }
        else
        {
            //Manually set template file
            $template_file = "{$script_name[0]}/viewpage.html";
        }
    break;
}
$template->set_filenames(array(
    'body' => $template_file
));
$template->display('body');