<?php
/*
 * Class.Template.PHP
 * (c) HyperSite 2017
 * Created by Ryan Morrison
 * License: http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class template 
{
    //Variable that holds all date to substitute into.
    var $_data = array('.' => array(0 => array()));
    var $_rootref;
    
    //Root Directory and hash of filenames
    var $root = '';
    var $files = '';
    var $filename = '';
    var $files_inherit = '';
    var $files_template = '';
    var $inherit_root = '';
    var $inherits_orig;
    
    //Hash handle names to compiled or uncompiled code for handle
    var $compiled_code = '';
    
    //Construct
    function __construct($template_path, $template_name, $fallback_template = false)
    {
        global $root_path;
        if (substr($template_path, -1) == '/')
        {
            $template_path = substr($template_path, 0, -1);
        }
        $this->root = $template_path;
        if ($fallback_template !== false)
        {
            if (substr($fallback_template, -1) == '/')
            {
                $fallback_template = substr($fallback_template, 0, -1);
            }
            $this->inherit_root = $fallback_template;
            $this->inherits_orig = true;
        }
        else
        {
            $this->inherits_orig = false;
        }
        $this->_rootref = &$this->_data['.'][0];
        return true;
    }
    function define_template($template_path, $template_name, $fallback_template = false)
    {
        global $root_path;
        if (substr($template_path, -1) == '/')
        {
            $template_path = substr($template_path, 0, -1);
        }
        $this->root = $template_path;
        if ($fallback_template !== false)
        {
            if (substr($fallback_template, -1) == '/')
            {
                $fallback_template = substr($fallback_template, 0, -1);
            }
            $this->inherit_root = $fallback_template;
            $this->inherits_orig = true;
        }
        else
        {
            $this->inherits_orig = false;
        }
        $this->_rootref = &$this->_data['.'][0];
        return true;
    }
    //Assign a group of variables from an array
    function assign_vars($array)
    {
        foreach ($array as $key => $value)
        {
            $this->_rootref[$key] = $value;
        }
        return true;
    }
    //Assign a single variable
    function assign_var($name, $value)
    {
        $this->_rootref[$name] = $value;
        return true;
    }
    //Assign an array as a block variable (array)
    function assign_array($array_name, $array)
    {
        if (strpos($array_name, '.') !== false)
        {
            //Nested block
            $blocks = explode('.', $array_name);
            $block_count = sizeof($blocks) - 1;
            
            $str = &$this->_data;
            for ($x = 0; $x < $block_count; $x++)
            {
                $str = &$str[$blocks[$x]];
                $str = &$str[sizeof($str) - 1];
            }
            $row_count = isset($str[$blocks[$block_count]]) ? sizeof($str[$blocks[$block_count]]) : 0;
            $array['ROW_COUNT'] = $row_count;
            
            //Assign first row
            if (!$row_count)
            {
                $array['FIRST_ROW'] = true;
            }
            
            //Always assign last row
            $array['LAST_ROW'] = true;
            if ($row_count > 0)
            {
                unset($str[$blocks[$block_count]][($row_count - 1)]['LAST_ROW']);
            }
            
            //Now assign the actual array
            $str[$blocks[$block_count]][] = $array;
        }
        else
        {
            //Top-level block
            $row_count = (isset($this->_data[$array_name])) ? sizeof($this->_data[$array_name]) : 0;
            $array['ROW_COUNT'] = $row_count;
            
            //Assign FIRST_ROW
            if (!$row_count)
            {
                $array['FIRST_ROW'] = true;
            }
            
            //Assign LAST_ROW and remove entry before
            $array['LAST_ROW'] = true;
            if ($row_count > 0)
            {
                unset($this->_data[$array_name][($row_count - 1)]['LAST_ROW']);
            }
            
            //Add new iteration with variables given
            $this->_data[$array_name][] = $array;
        }
        return true;
    }
}
