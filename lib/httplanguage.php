<?php
/**
 * This function parses the Accept-Language http header and returns an associative array with each
 * language and the score for that language.
 *
 * If an language includes a region, then the result will include both the language with the region
 * and the language without the region.
 *
 * The returned array will be in the same order as the input.
 *
 * @return An associative array with each language and the score for that language.
 */
function getAcceptLanguage() {

	if(!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
		/* No Accept-Language header - return empty set. */
		return array();
	}

	$languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

	$ret = array();

	foreach($languages as $l) {
		$opts = explode(';', $l);

		$l = trim(array_shift($opts)); /* The language is the first element.*/

		$q = 1.0;

		/* Iterate over all options, and check for the quality option. */
		foreach($opts as $o) {
			$o = explode('=', $o);
			if(count($o) < 2) {
				/* Skip option with no value. */
				continue;
			}

			$name = trim($o[0]);
			$value = trim($o[1]);

			if($name === 'q') {
				$q = (float)$value;
			}
		}

		/* Remove the old key to ensure that the element is added to the end. */
		unset($ret[$l]);

		/* Set the quality in the result. */
		$ret[$l] = $q;

		if(strpos($l, '-') || strpos($l, '_')) {
			/* The language includes a region part. */

			/* Extract the language without the region. */
			$l = explode('-', $l);
			$l = $l[0];

			/* Add this language to the result (unless it is defined already). */
			if(!array_key_exists($l, $ret)) {
				$ret[$l] = $q;
			}
		}
	}

	return $ret;
}


/**
 * This function gets the prefered language for the user based on the Accept-Language http header.
 *
 * @return The prefered language based on the Accept-Language http header, or NULL if none of the
 *         languages in the header were available.
 */
function getHTTPLanguage($availableLanguages) {
	$languageScore = getAcceptLanguage();

	/* For now we only use the default language map. We may use a configurable language map
	 * in the future.
	 */
	$languageMap = array('no' => 'nb');

	/* Find the available language with the best score. */
	$bestLanguage = 'en';
	$bestScore = -1.0;

	foreach($languageScore as $language => $score) {

		/* Apply the language map to the language code. */
		if(array_key_exists($language, $languageMap)) {
			$language = $languageMap[$language];
		}

		if(!in_array($language, $availableLanguages, TRUE)) {
			/* Skip this language - we don't have it. */
			continue;
		}

		/* Some user agents use very limited precicion of the quality value, but order the
		 * elements in descending order. Therefore we rely on the order of the output from
		 * getAcceptLanguage() matching the order of the languages in the header when two
		 * languages have the same quality.
		 */
		if($score > $bestScore) {
			$bestLanguage = $language;
			$bestScore = $score;
		}
	}

	return str_replace("-", "_", $bestLanguage);
}
