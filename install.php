<?php
error_reporting(E_ALL);
define('IN_HSCMS', true);
define('IN_INSTALL', true);
$phpex = "php";
$root_path = "./";

//Load the classes that DON'T require the config file to load.
require("{$root_path}includes/functions.{$phpex}");
require("{$root_path}includes/classes/class.dbal.{$phpex}");
require("{$root_path}includes/classes/class.template.{$phpex}");
require("{$root_path}includes/classes/class.user.{$phpex}");
require("{$root_path}includes/classes/class.modules.{$phpex}");

$template = new template($root_path . 'styles/elegant_black/template/','default');

$stage = request_var('stage', 0);

$template->assign_vars(array(
   'SITE_TITLE' => 'HyperSite Install',
   'SITE_DESCRIPTION' => 'Installing HyperSite CMS',
   'CREDIT_LINE' => "Powered by <a href=\"http://www.hypersite.info\">HyperSite v1.0 &copy;</a>",
   'ALLOW_USER_LOGIN' => 0,
   'SITE_THEME' => 'elegant_black',
   'SERVER_NAME' => $_SERVER['SERVER_NAME'],
   'USER_LOGGED_IN' => 0,
   'USER_IS_ADMIN' => 0
));

if (file_exists("{$root_path}includes/config.{$phpex}"))
{
    $stage = 3;
}
switch ($stage)
{
    default:
    case 0:
        //Display form to gather MySQL information.
        $template_file = "install/0.html";
        break;
    case 1:
        //Test Connection and write a config file.
        $mysql_user = request_var('mysql_user', 'root');
        $mysql_pass = request_var('mysql_pass', '');
        $mysql_host = request_var('mysql_host', 'localhost');
        $mysql_port = request_var('mysql_port', 3306);
        $mysql_db = request_var('mysql_db', 'hypersite');
        $table_prefix = request_var('table_prefix', 'hs_');
        $db = new dbal($mysql_host, $mysql_user, $mysql_pass, $mysql_db, $mysql_port);
        if ($db->error == true)
        {
            $template_file = "install/0.html";
            $template->assign_vars(array(
                'ERROR' => 1,
                'ERROR_MSG' => 'There was an error connecting to the database: ' . $db->error_msg
            ));
            break;
        }
        else
        {
            //Connection success, let's create config.php
            $file = "{$root_path}includes/config.php";
            $lines = array(
                '<?php\n',
                'class config\n',
                '{\n',
                '   var $mysql_server = \''.$mysql_host.'\';\n',
                '   var $mysql_port = \''.$mysql_port.'\';\n',
                '   var $mysql_user = \''.$mysql_user.'\';\n',
                '   var $mysql_pass = \''.$mysql_pass.'\';\n',
                '   var $mysql_db = \''.$mysql_db.'\';\n',
                '   var $table_prefix = \''.$table_prefix.'\';\n',
                '   var $template_dir = \'styles\';\n',
                '   var $mysql_server = array();\n',
                '}\n'
            );
            $writing = fopen($file, 'w+');
            foreach ($lines as $line)
            {
                fwrite($writing, $line);
            }
            chmod($file, 600);
        }
        if (!file_exists("{$root_path}includes/config.{$phpex}"))
        {
            $template_file = "install/0.html";
            $template->assign_vars(array(
                'ERROR' => 1,
                'ERROR_MSG' => 'There was an error writing the config file: '
            ));
            break;
        }
        $template_file = "install/1.html";
        break;
}
$template->set_filenames(array(
    'body' => $template_file
));
$template->display('body');