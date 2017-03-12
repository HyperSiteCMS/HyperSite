<?php
/*
 * @package         HyperSite CMS
 * @file            test_mod.php
 * @file_desc       A Test module
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
$template_file .= "modules/test_mod/styles/{$config->config['site_theme']}/template/test_mod.html";