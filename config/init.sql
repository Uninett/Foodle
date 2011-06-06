CREATE TABLE `def` (
  `id` varchar(100) NOT NULL,
  `name` tinytext,
  `descr` text,
  `columns` text,
  `owner` text,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated` timestamp NULL default NULL,
  `expire` datetime default NULL,
  `maxdef` text,
  `anon` tinytext,
  `timezone` text,
  `columntype` tinytext,
  `responsetype` tinytext,
  `extrafields` text,
  `datetime` text,
  `groupid` int(11) default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `entries` (
  `id` int(11) NOT NULL auto_increment,
  `foodleid` varchar(100) NOT NULL,
  `userid` tinytext,
  `username` tinytext,
  `response` text,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated` timestamp NULL default NULL,
  `notes` text,
  `email` text,
  `invitation` tinyint(1) default '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `discussion` (
  `id` int(11) NOT NULL auto_increment,
  `foodleid` varchar(100) NOT NULL,
  `username` tinytext,
  `message` text,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `userid` tinytext,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `user` (
  `userid` varchar(100) NOT NULL default '',
  `username` tinytext,
  `email` tinytext,
  `org` tinytext,
  `orgunit` tinytext,
  `realm` tinytext,
  `photol` text,
  `photom` text,
  `photos` text,
  `notifications` text,
  `features` text,
  `calendar` text,
  `timezone` tinytext,
  `location` tinytext,
  `language` tinytext,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated` timestamp NULL default NULL,
  `role` tinytext,
  `idp` tinytext,
  `auth` tinytext,
  `shaddow` varchar(100) default NULL,
  PRIMARY KEY  (`userid`)
);

CREATE TABLE `contactlist` (
  `id` int(11) NOT NULL auto_increment,
  `userid` varchar(100) NOT NULL,
  `name` text,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `contactlistmembers` (
  `id` int(11) NOT NULL,
  `userid` varchar(100) NOT NULL,
  `role` tinytext,
  PRIMARY KEY  (`id`,`userid`)
);

CREATE TABLE `files` (
  `groupid` int(11) NOT NULL,
  `filename` varchar(200) NOT NULL,
  `mimetype` varchar(100) default NULL,
  `userid` varchar(100) default NULL,
  `stored_filename` varchar(100) NOT NULL,
  PRIMARY KEY  (`stored_filename`)
);

