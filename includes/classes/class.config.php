<?php
class config
{
    public $mysql = array();
    public $template_dir = 'styles';
    public $config = array();
    
    public function __construct()
    {
        global $root_path;
        $config_file = $root_path . '/includes/conf.json';
        $data = json_decode(file_get_contents($config_file),true);
        $this->mysql = $data;
        return true;
    }
}