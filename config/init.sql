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
  PRIMARY KEY  (`id`)
);