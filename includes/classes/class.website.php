<?php
/*
 * Class.Website.PHP
 * (c) HyperSite 2017
 * Created by Ryan Morrison
 * License: http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class website {
   function get_site_title()
   {
       global $db, $config;
       $query = "SELECT * FROM " . $config->table_prefix . "SETTINGS where setting_name='Site Title'";
       $result = $db->query($query);
       $row = $db->fetchrow($result);
       foreach ($row as $key => $val)
       {
           $key = preg_replace(' ','_',$key);
           $y[strtoupper($key)] = $val;
       }
       return $y['SITE_TITLE'];

   }
}
