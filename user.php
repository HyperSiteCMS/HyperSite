<?php
if ($user->user_info['logged_in'] == 1)
{
    switch ($act)
    {
        default:
        case 'overview':
            break;
        case 'settings':
            break;
    }
}
else 
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
                    $template_file = "user_login.html";
                    $template->assign_var('ERROR', 1);
                    $template->assign_var('MESSAGE', 'Error: Username or Password not supplied.');
                    break;
                }
                $user_info = $user->user_login($submitted_username, $submitted_password);
                if ($user_info)
                {
                    $sess_id = unique_id();
                    setcookie('hs_user_sess', $sess_id, time()+(86400*30)); //Set cookie for 30 days to auto login.
                    $session_info = array(
                        'user_id' => $user_info['user_id'],
                        'uniq_id' => $sess_id
                    );
                    $query = $db->build_query('insert', SESSION_TABLE, $session_info);
                    if ($db->query($query))
                    {
                        $template_file = "user_message.html";
                        $template->assign_var('ERROR', 0);
                        $template->assign_var('MESSAGE', 'Success. User Logged in');
                    }
                    else
                    {
                        $template_file = "user_message.html";
                        $template->assign_var('ERROR', 1);
                        $template->assign_var('MESSAGE', 'Error: Unable to save session information');
                        setcookie('hs_user_sess', '', time()-3600);
                        break;
                    }
                    $userinfo = $user->get_user('session', $sess_id);
                    if ($userinfo)
                    {
                        //Valid session so lets renew cookie and get info from database
                        setcookie('hs_user_sess', $session, time() + (86400*30));
                        $permissions = $user->get_permissions($userinfo['user_id']);
                        $userinfo['permissions'] = $permissions;
                        $userinfo['logged_in'] = 1;
                        $user->user_info = $userinfo;
                    }
                }
                else
                {
                    $template_file = "user_login.html";
                    $template->assign_var('ERROR', 1);
                    $template->assign_var('MESSAGE', 'Error: Incorrect Username/Password combination');
                }
            }
            else
            {
                $template_file = "user_login.html";
            }
            break;
        case 'logout':
            break;
        case 'register':
            break;
    }
}

