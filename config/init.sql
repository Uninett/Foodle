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
  PRIMARY KEY  (`userid`)
);