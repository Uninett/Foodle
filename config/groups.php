<?php

$access = array(
	0 => 'Private',
	1 => 'All feide users can read, no anonymous access',
	2 => 'Anonymous users can read',
	3 => 'Feide users can write, no anonymous access',
	4 => 'Feide users can write, anonymous users can read'
);

$groups = array(
	'@realm-uninett.no'	=> 'Everyone at UNINETT',
	'@affiliation-uninett.no-employee' => 'Employees at UNINETT',
	'@affiliation-uninett.no-member' => 'Members of UNINETT',
	'@orgunit-uninett.no-ou=SU_ou=TA_ou=UNINETT_ou=organization_dc=uninett_dc=no' => 'Systemutviklingsgruppa',
	'@orgunit-uninett.no-ou=TA_ou=UNINETT_ou=organization_dc=uninett_dc=no' => 'Tjenesteavdelingen',
	'@orgunitXouXUNINETTXSigmaXouXorganizationXdcXuninettXdcXnoXuninettXno' => 'Employees UNINETT Sigma',
	'@feidecore' => 'Feide prosjektgruppe'
);