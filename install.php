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
require("{$root_path}includes/classes/class.config.{$phpex}");

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
if ($stage > 1)
{
    $config = new config();
    require "{$root_path}includes/constants.php";
    $db = new dbal($config->mysql['server'], $config->mysql['user'], $config->mysql['pass'], $config->mysql['db'], $config->mysql['port']);
}
if ($stage > 3)
{
    $user = new user();
}
switch ($stage)
{
    default:
    case 0:
        //Display form to gather MySQL information.
        $template_file = "install/0.html";
        break;
    case 1:
        //Test Connection
        $conf = json_decode(file_get_contents("{$root_path}includes/conf.json"), true);
        $db = new dbal($conf['server'], $conf['user'], $conf['pass'], $conf['db'], $conf['port']);
        if ($db->error == true)
        {
            $template_file = "install/0.html";
            $template->assign_vars(array(
                'ERROR' => 1,
                'ERROR_MSG' => 'There was an error connecting to the database: ' . $db->error_msg
            ));
            break;
        }
        $template_file = "install/1.html";
        break;
    case 2:
        $sql_file = "{$root_path}includes/sql/hypersite.sql";
        $queries = explode(';', file_get_contents($sql_file));
        unset($queries[count($queries)-1]);
        foreach ($queries as $query)
        {
            $query = str_replace('hs_', $config->mysql['table_prefix'], $query);
            $result = $db->query($query);
            if ($result)
            {
                $template->assign_block_vars('log', array(
                    "TEXT" => 'Query Executed: ' . $query . '<br />'
                ));
            }
            else
            {
                $template->assign_block_vars('log', array(
                    'TEXT' => 'Query Failed: ' . $query . '<br />'
                ));
            }
        }
        $template_file = "install/2.html";
        break;
    case 3:
        $template_file = "install/3.html";
        break;
    case 4:
        $config->config['password_salt'] = md5(time());
        $admin_username = $db->clean(request_var('admin_user', null));
        $admin_password = $db->clean(request_var('admin_pass', null));
        $admin_email = $db->clean(request_var('admin_email', null));
        $site_title = $db->clean(request_var('site_title', 'HyperSite'));
        $site_desc = $db->clean(request_var('site_desc', 'A CMS for Everyone'));
        $intro = $db->clean(htmlentities(request_var('site_intro', 'Welcome to HyperSite')));
        $query = "INSERT INTO " . SETTINGS_TABLE . "(`setting_name`,`setting_value`) VALUES ";
        $query .= "('site_title', '{$site_title}'), ('site_desc', '{$site_desc}'), ";
        $query .= "('site_intro', '{$intro}'), ('site_theme', 'elegant black'), ('allow_users', '0'), ";
        $query .= "('password_salt', '" . $config->config['password_salt'] . "');";
        $result = $db->query($query);
        if (!$result)
        {
            $template->assign_vars(array(
               'ERROR' => 1,
                'MESSAGE' => 'Failed to write to database: ' . $db->error_msg . '<br/>'. $query
            ));
            $template_file = "install/3.html";
            break;
        }
        $user_array = array(
           'username' => $admin_username,
            'password' => $admin_password,
            'user_email' => $admin_email,
            'user_level' => 3,
            'date_registered' => time(),
            'user_founder' => 1
        );
        $newuser = $user->create_user($user_array);
        if (!$newuser)
        {
            $template->assign_vars(array(
               'ERROR' => 1,
                'MESSAGE' => 'Failed to write user to database: ' . $db->error_msg
            ));
            $template_file = "install/3.html";
            break;
        
        }
        $template_file = "install/4.html";
        break;
    case 5:
        //First lets add all the user levels to database
        $array = array(
            0 => array(
                'level_id' => 1,
                'is_admin' => 0,
                'is_mod' => 0,
                'level_name' => 'User'
            ),
            1 => array(
                'level_id' => 2,
                'is_admin' => 0,
                'is_mod' => 1,
                'level_name' => 'Moderator'
            ),
            2 => array(
                'level_id' => 3,
                'is_admin' => 1,
                'is_mod' => 1,
                'level_name' => 'Administrator'
            )
        );
        foreach ($array as $level)
        {
            $query = $db->build_query('insert', LEVELS_TABLE, $level);
            $result = $db->query($query);
            if ($result)
            {
                $template->assign_block_vars('log', array(
                    "TEXT" => 'Level Added: ' . $level['level_name'] . '<br />'
                ));
            }
            else
            {
                $template->assign_block_vars('log', array(
                    'TEXT' => 'Level not added: ' . $level['name'] . '<br />'
                ));
            }
        }
        //Now add the Home page and About page as default
        $home_array = array(
            'page_title' => 'Home',
            'page_identifier' => 'home',
            'page_text' => htmlentities("<p>Welcome to HyperSite CMS</p>"),
            'page_parent' => 0
        );
        $about_array = array(
            'page_title' => 'About Us',
            'page_identifier' => 'about',
            'page_text' => htmlentities("<p>This is a default secondary page for HyperSite CMS</p>"),
            'page_parent' => 0
        );
        $hquery = $db->build_query('insert', PAGES_TABLE, $home_array);
        $home = $db->query($hquery);
        if (!$home)
        {
            $template->assign_block_vars('log', array(
                    'TEXT' => 'Failed to add Home Page: ' . $db->error_msg . ' <br/>' . $hquery . '<br/>'
                ));
        }
        else 
        {
            $template->assign_block_vars('log', array(
                    'TEXT' => 'Added Home page<br/>'
                ));
        }
        $aquery = $db->build_query('insert', PAGES_TABLE, $about_array);
        $about = $db->query($aquery);
        if (!$about)
        {
            $template->assign_block_vars('log', array(
                    'TEXT' => 'Failed to add About Page: ' . $db->error_msg . ' <br />' . $aquery . '<br/>'
                ));
        }
        else 
        {
            $template->assign_block_vars('log', array(
                    'TEXT' => 'Added About page<br/>'
                ));
        }
        $template_file = "install/5.html";
                break;
}
$template->set_filenames(array(
    'body' => $template_file
));
$template->display('body');