<?php

/*
 * @package         HyperSite CMS
 * @file            constants.php
 * @file_desc       List of constants, MySQL tables etc.
 * @author          Ryan Morrison
 * @website         -
 * @copyright       (c) 2019 HyperSite CMS
 * @license         http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/* Check if we are in CMS otherwise exit code. */
if (!defined('IN_HSCMS')) {
    exit;
}

/* Main Code here */
define('SETTINGS_TABLE', $config->mysql['table_prefix'] . 'settings');
define('PAGES_TABLE', $config->mysql['table_prefix'] . 'pages');
define('USERS_TABLE', $config->mysql['table_prefix'] . 'users');
define('SESSION_TABLE', $config->mysql['table_prefix'] . 'sessions');
define('LEVELS_TABLE', $config->mysql['table_prefix'] . 'user_levels');
define('MODULE_TABLE', $config->mysql['table_prefix'] . 'modules');
define('NAV_TABLE', $config->mysql['table_prefix'] . 'navigation');

//CHMOD
@define('CHMOD_ALL', 7);
@define('CHMOD_READ', 4);
@define('CHMOD_WRITE', 2);
@define('CHMOD_EXECUTE', 1);

//CMS Versioning
define('CMS_VERSION', '1.0.0');
