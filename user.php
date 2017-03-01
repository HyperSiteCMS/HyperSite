<?php
if ($user->logged_in = true)
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
            break;
        case 'logout':
            break;
        case 'register':
            break;
    }
}

