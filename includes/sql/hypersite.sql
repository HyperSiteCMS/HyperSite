CREATE TABLE IF NOT EXISTS `hs_modules` (
  `mod_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mod_name` varchar(255) NOT NULL,
  `mod_version` varchar(5) NOT NULL,
  `mod_author` varchar(255) NOT NULL,
  `mod_description` varchar(1000) NOT NULL,
  `mod_install_file` varchar(255) NOT NULL,
  PRIMARY KEY (`mod_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `hs_navigation` (
  `nav_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `area` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`nav_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `hs_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_title` varchar(255) NOT NULL,
  `page_identifier` varchar(50) NOT NULL,
  `page_text` text NOT NULL,
  `page_parent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`),
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `hs_sessions` (
  `sess_id` int(11) NOT NULL AUTO_INCREMENT,
  `uniq_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`sess_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;
CREATE TABLE IF NOT EXISTS `hs_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(50) NOT NULL,
  `setting_value` varchar(1000),
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `hs_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_level` int(11) NOT NULL DEFAULT '1',
  `user_email` varchar(500) NOT NULL,
  `date_registered` int(11) NOT NULL,
  `user_founder` int(1) NOT NULL DEFAULT '0',
  `user_status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `hs_user_levels` (
  `level_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_admin` int(1) NOT NULL DEFAULT '0',
  `level_name` varchar(255) NOT NULL,
  `is_mod` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`level_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;