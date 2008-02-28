CREATE TABLE def (
	id varchar(100) NOT NULL PRIMARY KEY,
	name tinytext,
	descr text,
	columns text
);

CREATE TABLE entries (
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	foodleid varchar(100) NOT NULL,
	userid tinytext,
	username tinytext,
	response tinytext
);