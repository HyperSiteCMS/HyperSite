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
        //First get all loaded Modules
        $query = "SELECT * FROM " . MODULE_TABLE;
        $module_array = $db->fetchall($db->query($query));
        foreach ($module_array as $module)
        {
            $mod_info_file = "{$root_path}modules/{$module['mod_name']}/{$module['mod_name']}.json";
            $file_contents = file_get_contents($mod_info_file);
            $mod_info = json_decode($file_contents, true);
            $this->loaded[$module['mod_name']] = $mod_info;
        }
        
        //Now find all other modules
        $files = array_slice(scandir("{$root_path}modules"), 2);
        foreach ($files as $file)
        {
            if (!isset($this->loaded[$file]) && is_dir($root_path . '/modules/' . $file))
            {
                $mod_info_file = "{$root_path}modules/{$file}/{$file}.json";
                $file_contents = file_get_contents($mod_info_file);
                $mod_info = json_decode($file_contents, true);
                $this->unloaded[$file] = $mod_info;
            }
        }
    }
    
    function load_module($mod_name = '')
    {
        global $db, $root_path;
        if ($mod_name === '') { return false; }
        $mod_name = $db->clean($mod_name);
        //Lets check if Module already loaded or not
        $sql = "SELECT * FROM " . MODULE_TABLE . " WHERE mod_name='{$mod_name}'";
        $row = $db->fetchrow($db->query($sql));
        if ($row) 
        {
            return false;
        }
        else
        {
            $mod_info = json_decode(file_get_contents("{$root_path}modules/{$mod_name}/{$mod_name}.json"),true);
            $this->do_actions('install', $mod_name, $mod_info['mod_install_file']);
            //Update Database now to show module installed
            $query = $db->build_query('insert', MODULE_TABLE, $mod_info);
            $result = $db->query($query);
            return $result;
        }
    }
    function unload_module($mod_name = '')
    {
        global $db, $root_path;
        if ($mod_name === '') { return false; }
        $mod_name = $db->clean($mod_name);
        //Lets check if Module is actually loaded
        $sql = "SELECT * FROM " . MODULE_TABLE . " WHERE mod_name='{$mod_name}'";
        $row = $db->fetchrow($db->query($sql));
        if ($row) 
        {
            $this->do_actions('uninstall', $mod_name, $row['mod_install_file']);
            //Remove module from database
            $where = array(
                'mod_name' => $mod_name
            );
            $result = $db->query($db->build_query('delete', MODULE_TABLE, false, $where));
            return $result;
        }
        return false;
    }
    function do_actions($action = 'install', $mod_name, $filename)
    {
        global $db, $root_path, $config;
        include "{$root_path}modules/{$mod_name}/{$filename}";
        if ($action == 'install')
        {
            for ($x = 0; isset($install_actions[$x]); $x++)
            {
                if (key($install_actions[$x]) == 'create')
                {
                    $table_name = $config->table_prefix . key($install_actions[$x]['create']);
                    $fields = $install_actions[$x]['create'][key($install_actions[$x]['create'])];
                    $query = $db->build_query('create', $table_name, $fields);
                    $result = $db->query($query) or die('Unable to create table for Module<br/>' . print_r("Table: {$table_name}") . " <br/>" . print_r($fields));
                }
                if (key($install_actions[$x]) == 'insert')
                {
                    $assoc_array = $install_actions[$x]['insert']['entries'];
                    $table_name = $config->table_prefix . $install_actions[$x]['insert']['table'];
                    $query = $db->build_query('insert', $table_name, $assoc_array) or die("Unable to create query?");
                    $result = $db->query($query) or die('Unable to Insert data into database for Module: ' . $query);
                }
            }
        }
        if ($action == 'uninstall')
        {
            for ($x = count($install_actions)-1; $x >= 0; $x--)
            {
                if (key($install_actions[$x]) == 'create')
                {
                    $table_name = $config->table_prefix . key($install_actions[$x]['create']);
                    $query = "DROP TABLE {$table_name};";
                    $result = $db->query($query) or die("Unable to destroy table");
                }
                if (key($install_actions[$x]) == 'insert')
                {
                    $table_name = $config->table_prefix . $install_actions[$x]['insert']['table'];
                    $where = $install_actions[$x]['insert']['entries'];
                    $query = $db->build_query('delete', $table_name, false, $where);
                    $result = $db->query($query) or die("Unable to remove data from table");
                }
            }
        }
    }
}