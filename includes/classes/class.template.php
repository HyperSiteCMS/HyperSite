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
}
