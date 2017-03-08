<?php
/*
 * @package         HyperSite CMS
 * @file            class.modules.php
 * @file_desc       Handles loading and unloading of modules
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
class modules {
    var $loaded = array();
    var $unloaded = array();
    
    function __construct()
    {
        global $db, $root_path;
        $query = "SELECT * FROM " . MODULE_TABLE;
        $module_array = $db->fetchall($db->query($query));
        foreach ($module_array as $module)
        {
            $mod_info_file = "{$root_path}modules/{$module['mod_name']}/{$module['mod_name']}.json";
            $file_contents = file_get_contents($mod_info_file);
            $mod_info = json_decode($file_contents, true);
            if ($module['mod_loaded'] > 0)
            {
                $this->loaded[$module['mod_name']] = $mod_info;
            }
            else
            {

                $this->unloaded[$module['mod_name']] = $mod_info;
            }
        }
    }
    
    function load_module($mod_name = '')
    {
        global $db, $root_path;
        if ($mod_name === '') { return false; }
        $set = array('mod_loaded' => 1);
        $where = array('mod_name' => $db->clean($mod_name));
        $query = $db->build_query('update', MODULE_TABLE, $set, $where);
        $this->loaded[$mod_name] = $this->unloaded[$mod_name];
        unset($this->unloaded[$mod_name]);
        return $db->query($query);
    }
}