# $Id: mysql.php,v 1.0 2007/07/02 12:37:00 Igor Nikulin

CREATE TABLE `prefix_textanalysis` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `intro` text NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `course` varchar(255) NOT NULL default '',
  `teacher` varchar(255) NOT NULL default '',
  `time` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) COMMENT='textanalysis';

# --------------------------------------------------------
