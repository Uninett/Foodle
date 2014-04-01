#!/usr/bin/php
<?php



function getData($path) {
	$opts = array(
	    'http' => array(
		    'method' =>'GET',
		    'header' => sprintf('Authorization: Basic %s', base64_encode('erlang:42rVM8VjiyHE6s') )
		)
	);
	$ctx = stream_context_create($opts);
	return json_decode(file_get_contents('http://www.transifex.net' . $path, false, $ctx), TRUE);
}

function getResource($project, $resource) {
	$data = getData('/api/2/project/' . $project . '/resource/' . $resource . '/?details');
	return $data;
}

function getTranslationInfo($project, $resource, $lang) {
	$data = getData('/api/2/project/' . $project . '/resource/' . $resource . '/stats/' . $lang . '/');
	return $data;
}

function getTranslation($project, $resource, $lang) {
	$data = getData('/api/2/project/' . $project . '/resource/' . $resource . '/translation/' . $lang . '/');
	if (!$data['content']) throw new Exception('Invalid response');

	return json_decode($data['content'], true);
	// echo $data['content']; exit;
	/* eval('?>' . $data['content'] . '<?'); */
	// return $LANG;
}


function fill_en($en, &$dict) {

	foreach($en AS $k => $v) {
		if (!isset($dict[$k])) $dict[$k] = $v;
	}
}

$info = getResource('foodle', 'foodle');

// print_r($info); exit;

$base = dirname(dirname(__FILE__)) . '/dictionaries/';
$def_en = json_decode(file_get_contents($base . 'foodle.en.js'), true);


$langcodes = array('en');

foreach($info['available_languages'] AS $lang) {
	if ($lang['code'] === 'en') continue;

	echo 'Processing Language ' . $lang['name'] . "\n";
	$trans = getTranslation('foodle', 'foodle', $lang['code']);
	$transinfo = getTranslationInfo('foodle', 'foodle', $lang['code']);
	
	if ($transinfo['untranslated_entities'] > $transinfo['translated_entities']) {
		echo "Skipping language export, because too few translated terms.\n";
		continue;
	}
	// print_r($trans); exit;

	fill_en($def_en, $trans);

	$filename = $base . 'foodle.' . $lang['code'] . '.js';
	$filecontent = json_encode($trans, TRUE);
	file_put_contents($filename, $filecontent);
	echo "Wrote to " . $filename . "\n"	;
	$langcodes[] = $lang['code'];
	
}



file_put_contents($base . 'languages.json', json_encode($langcodes) );


