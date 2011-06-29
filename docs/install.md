# Installing Foodle

Third party installs are NOT prioritised with Foodle (yet). Instead people are encourage to use the central foodl.org service. Contact Andreas for more details.


## Download the stuff you need

In this example, we install Foodle in `/home/www/deploy.foodl.org`.

	mkdir /home/www/deploy.foodl.org
	cd /home/www/deploy.foodl.org
	svn checkout http://foodle.googlecode.com/svn/trunk/ foodle

Install SimpleSAMLphp:

* [Install simpleSAMLphp](http://simplesamlphp.org/docs/1.6/simplesamlphp-install)
* [SimpleSAMLphp Service Provider QuickStart](http://simplesamlphp.org/docs/1.6/simplesamlphp-sp)

If you install simpleSAMLphp from subversion:

	svn checkout http://simplesamlphp.googlecode.com/svn/trunk/ simplesamlphp




## Apache setup

Configure apache.

Example:

	<VirtualHost *:80>
		ServerName deploy.foodl.org
		
		Alias /simplesaml "/home/www/deploy.foodl.org/simplesamlphp/www"
		Alias /res "/home/www/deploy.foodl.org/foodle/www/res"
		Alias / /home/www/deploy.foodl.org/foodle/www/index.php/
		
		ErrorLog /var/log/apache2/error-deploy.foodl.org.log
		CustomLog /var/log/apache2/access-deploy.foodl.org.log combined
	</VirtualHost>


## Setup the database

First create a user and a database in mysql:

	mysql -u root -h sql.foo.org
	create database feidefoodle_deploytest;
	GRANT ALL PRIVILEGES ON feidefoodle_deploytest.* TO "dbuser"@"%" IDENTIFIED BY "xxxxxxx";
	flush privileges;

Initialise the table definitions:

	mysql -u dbuser -h sql.foo.org -p feidefoodle_deploytest < foodle/config/init.sql


## Fix the configuration


First; you need to edit `foodle/www/_include.php` and set the path to your simplesamlphp installation:

	$SIMPLESAMLPATH = '/home/www/deploy.foodl.org/simplesamlphp/';

Then, make the Foodle config file:

	cp foodle/config/config-template.php foodle/config/config.php

And edit it.

First, fix the paths:

	'simplesamlphpdir'   => '/home/www/deploy.foodl.org/simplesamlphp',
	'basedir'            => '/home/www/deploy.foodl.org/foodle',

Then setup the database connection details:

	'db.host'	=> 'sql.foo.org',
	'db.name'	=> 'feidefoodle_deploytest',
	'db.user'	=> 'dbuser',
	'db.pass'	=> 'xxxxxxxx',

Also, you may want to configure your user ID as an administrator:

	'adminUsers' => array('andreas@rnd.feide.no', 'andreas@uninett.no'),



## Test the installation

Go to the installed URL, in this example: `http://deploy.foodl.org/`.

You should see a page like this:

![](http://clippings.erlang.no/ZZ5CB3C973.jpg)



## Test authentication

Then, next step is to test if the login works. First, I reccomend testing with Feide OpenIdP.

Then the support integration with GetSatisfaction requires a key and a secret. That is a problem for third party installations; I have no solution for that yet. Probably I should add an config option to turn of integration with GetSatisfaction.


## Configuring attributes

One thing that you probably MUST think about and configure is attribute names.

Foodle will need this attributes form simpleSAMLphp:

One of these is required for user ID:

* eduPersonTargetedID
* eduPersonPrincipalName
* mail

One of these is highly reccomended for name:

* smartname-fullname
* displayName
* cn

For calendar integration, free busy url goes in this attribute:

* freebusyurl

Reccomended attribute:

* mail


If the IdP is using the MACE oid naming scheme, then proceed as this:

The simpleSAMLphp core module includes a *authentication processing* filter called `AttributeMap`, that is used to translate attribute names.

  * [Read more about Attribute Processing Filters in SimpleSAMLphp](http://rnd.feide.no/content/authentication-processing-filters-simplesamlphp)

The `core` module is enabled by default in simpleSAMLphp, and if you run an installation of simpleSAMLphp for the purpose of Foodle only, you may apply the attribute name mapping filter globally. To apply it globally, edit the `config.php` file.

		'authproc.sp' => array(
			10 => array(
				'class' => 'core:AttributeMap', 'oid-feide'
			),
			60 => 'smartnameattribute:SmartName',
			// More filters..
		),

The attribute maps are defined in the `attributemap/` directory of simpleSAMLphp, and as you can see there is already a file called `oid-feide`. This map will translate oid attributes to short names as used by Foodle.

As you can see, I have also enabled a filter called `SmartName`. If you add this filter in the global config, you must remember to **enable** the `smartnameattribute` module. To enable this module, do:

	cd modules/smartnameattribute
	touch enable

This filter will generate a new attribute named smartname-fullname (if I remember correctly). This is useful because it differ from IdP to IdP what attributes are available, and while at some IdP you can create the fullname by concatenating `givenName` + `sn`, at another IdP only `cn` is available. Take a look at the source code of this module if you are interested in how it works.

After you can setup attribute name translation, visit the SP example login page at the installation page of your installation, and verify that you are logged in with the needed attributes.



### If it does not work as expected

If the page is blank, look in the Apache error log:

	tail -f /var/log/apache2/error-deploy.foodl.org.log 

If it shows an exception, try to understand what the error message is saying.

If you stil have problems, you may ask on the Foodle support forum:

  * <http://tjenester.ecampus.no/ecampus/products/ecampus_foodle>




