<?php
/*
 * @package         HyperSite CMS
 * @file            constants.php
 * @file_desc       List of constants, MySQL tables etc.
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
define('SETTINGS_TABLE', $config->table_prefix . 'settings');
define('PAGES_TABLE', $config->table_prefix . 'pages');
define('USERS_TABLE', $config->table_prefix . 'users');
define('SESSION_TABLE', $config->table_prefix . 'sessions');
define('LEVELS_TABLE', $config->table_prefix . 'user_levels');
define('MODULE_TABLE', $config->table_prefix . 'modules');
