CREATE TABLE def (
	id varchar(100) NOT NULL PRIMARY KEY,
	name tinytext,
	descr text,
	columns text,
	
	owner text,
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated TIMESTAMP null DEFAULT null,
	expire DATETIME null,
	maxdef text,
	anon tinytext
);

CREATE TABLE entries (
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	foodleid varchar(100) NOT NULL,
	userid tinytext,
	username tinytext,
	response tinytext,
	
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated TIMESTAMP null DEFAULT null,
	
	notes text,
	email text
);

CREATE TABLE discussion (
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	foodleid varchar(100) NOT NULL,
	username tinytext,
	message text,
	
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
