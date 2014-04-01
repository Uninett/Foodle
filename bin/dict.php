#!/usr/bin/php
<?php


$oldbase = dirname(dirname(__FILE__)) . '/dictionaries/';
$base = $oldbase . 'old/';
$def = json_decode(file_get_contents($oldbase . 'foodle.en.js'), true);


// $def = json_decode(file_get_contents($base . 'foodle_foodle.definition.json'), true);
// $trans = json_decode(file_get_contents($base . 'foodle_foodle.translation.json'), true);


// $obj = array();
// foreach($def AS $key => $t) {
// 	$obj[$key] = $t['en'];
// }


// file_put_contents($base . 'foodle.en.js', json_encode($obj));


// echo $base . 'foodle.en.js' . "\n";


$trans = json_decode(file_get_contents($oldbase . 'foodle_foodle.translation.json'), true);
$lang = array('no', 'da', 'nl', 'de', 'sv', 'es', 'sl', 'nn', 'hr', 'fi', 'fr', 'cs', 'it', 'et', 'ja', 'pl');


foreach($lang AS $currentLang) {

	$dict = array();

	// print_r($def);

	foreach($def AS $d => $text) {

		if (isset($trans[$d]) && isset($trans[$d][$currentLang])) {
			$dict[$d] = $trans[$d][$currentLang];

		} else {
			// $dict[$d] = $text;
		}

	}

	file_put_contents($base . 'foodle.' . $currentLang . '.js', json_encode($dict));



}


