<?php
/*
 * @package         HyperSite CMS
 * @file            user.php
 * @file_desc       Handles anything on client-side for users (Login, logout, register, settings etc)
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
if ($user->user_info['logged_in'] == 1)
{
    switch ($act)
    {
        default:
        case 'overview':
            break;
        case 'settings':
            break;
        case 'logout':
            $session = $db->clean($_COOKIE['hs_user_sess']);
            $template_file = "user/message.html";
            if ($user->user_logout($session))
            {
                setcookie('hs_user_sess', '', time()-3600, '/');
                $template->assign_vars(array(
                    'PAGE_TITLE' => 'Logout',
                    'MESSAGE' => 'Logged out successfully'
                ));
            }
            else
            {
                $template->assign_vars(array(
                    'PAGE_TITLE' => 'Error',
                    'MESSAGE' => 'Failed to logout. Please contact an administrator if this issue keeps occurring.'
                ));
            }
            break;
    }
}
else if ($act != 'view')
{
    switch ($act)
    {
        default:
        case 'login':
            if (isset($_POST['login']))
            {
                $submitted_username = request_var('username', false);
                $submitted_password = request_var('password', false);
                if (!$submitted_username || !$submitted_password)
                {
                    $template_file = "user/login.html";
                    $template->assign_var('ERROR', 1);
                    $template->assign_var('MESSAGE', 'Error: Username or Password not supplied.');
                    break;
                }
                $user_info = $user->user_login($submitted_username, $submitted_password);
                if ($user_info)
                {
                    $user_info['permissions'] = $user->get_permissions($user_info['user_id']);
                    if (($config->config['allow_users'] < 1) && $user_info['permissions']['is_admin'] < 1)
                    {
                        $template_file = "user/message.html";
                        $template->assign_var('MESSAGE', 'Only administrators may log in at the moment. Please check back later');
                    }
                    else
                    {
                        $sess_id = unique_id();
                        setcookie('hs_user_sess', $sess_id, time()+(86400*30),'/'); //Set cookie for 30 days to auto login.
                        $session_info = array(
                            'user_id' => $user_info['user_id'],
                            'uniq_id' => $sess_id
                        );
                        $query = $db->build_query('insert', SESSION_TABLE, $session_info);
                        if ($db->query($query))
                        {
                            $template_file = "user/message.html";
                            $template->assign_var('ERROR', 0);
                            $template->assign_var('MESSAGE', 'Success. User Logged in');
                        }
                        else
                        {
                            $template_file = "user/message.html";
                            $template->assign_var('ERROR', 1);
                            $template->assign_var('MESSAGE', 'Error: Unable to save session information');
                            setcookie('hs_user_sess', '', time()-3600,'/');
                            break;
                        }
                        $userinfo = $user->get_user('session', $sess_id);
                        if ($userinfo)
                        {
                            //Valid session so lets renew cookie and get info from database
                            setcookie('hs_user_sess', $sess_id, time() + (86400*30),'/');
                            $permissions = $user->get_permissions($userinfo['user_id']);
                            $userinfo['permissions'] = $permissions;
                            $userinfo['logged_in'] = 1;
                            $user->user_info = $userinfo;
                        }
                    }
                }
                else
                {
                    $template_file = "user/login.html";
                    $template->assign_var('ERROR', 1);
                    $template->assign_var('MESSAGE', 'Error: Incorrect Username/Password combination');
                }
            }
            else
            {
                $template_file = "user/login.html";
            }
            break;
        case 'register':
            if ($config->config['allow_users'] < 1)
            {
                $template_file = "user/message.html";
                $template->assign_var('MESSAGE', 'This website is currently not accepting registrations.');
            }
            else
            {
                if (isset($_POST['submit']))
                {
                    $email = request_var('email', null);
                    $email_ver = request_var('email-verify', null);
                    $password = request_var('password', null);
                    $pass_ver = request_var('pass-verify', null);
                    $username = request_var('username');
                    //Verification Checks
                    $error = false;
                    $errors = array();
                    if ($email != $email_ver)
                    {
                        $error = true;
                        $errors[] = "Email addresses do not match";
                    }

                    if ($password !== $password_ver)
                    {
                        $error = true;
                        $errors[] = "Passwords do not match";
                    }
                    if ($user->get_user('username', $username))
                    {
                        $error = true;
                        $errors[] = "Username is already taken";
                    }
                    if ($error == true)
                    {
                        $template_file = "user/register.html";
                        $template->assign_block_vars('errors', $errors);
                    }
                    else
                    {
                        $usr_ary = array(
                            'username' => $username,
                            'email' => $email,
                            'password' => $password,
                            'user_level' => 1,
                            'date_registered' => time(),
                            'user_founder' => 0
                        );
                        $newuser = $user->create_user($usr_ary);
                        $template_file = "user/message.html";
                        $template->assign_var('Message', 'You have registered successfully. You may now login.');
                    }
                }
                else
                {
                    $template_file = "user/register.html";
                }
            }
            break;
        case 'verify':
            break;
    }
}
else
{
    //Part for viewing profiles.
}

