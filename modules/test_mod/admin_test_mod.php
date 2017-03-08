<?php
/*
 * @package         HyperSite CMS
 * @file            admin_test_mod.php
 * @file_desc       Test Modul
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
/* Check if in ACP */
if (!defined('IN_ACP'))
{
    exit;
}
/* Main Code here */
$template_file = "admin_message.html";
$template->assign_vars(array(
   'MESSAGE' => "Test module is working ok.",
    'PAGE_TITLE' => 'Test Mod'
));


