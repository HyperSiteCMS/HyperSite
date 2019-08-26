<?php

/*
 * @package         HyperSite CMS
 * @file            class.config.php
 * @file_desc       Deals with website and database settings
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

class config {

    public $mysql = array();
    public $template_dir = 'styles';
    public $config = array();

    public function __construct() {
        global $root_path;
        $config_file = $root_path . '/includes/conf.json';
        $data = json_decode(file_get_contents($config_file), true);
        $this->mysql = $data;
        return true;
    }

}
