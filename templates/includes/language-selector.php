

<?php
$languages = $this->getLanguageList();
$langnames = array(
			'no' => 'Bokmål',
			'nn' => 'Nynorsk',
			'se' => 'Sámegiella',
			'sam' => 'Åarjelh-saemien giele',
			'da' => 'Dansk',
			'en' => 'English',
			'de' => 'Deutsch',
			'sv' => 'Svenska',
			'fi' => 'Suomeksi',
			'es' => 'Español',
			'fr' => 'Français',
			'it' => 'Italiano',
			'nl' => 'Nederlands',
			'lb' => 'Luxembourgish', 
			'cs' => 'Czech',
			'sl' => 'Slovenščina', // Slovensk
			'lt' => 'Lietuvių kalba', // Lithuanian
			'hr' => 'Hrvatski', // Croatian
			'hu' => 'Magyar', // Hungarian
			'pl' => 'Polski', // Polish
			'pt' => 'Português', // Portuguese
			'pt-BR' => 'Português brasileiro', // Portuguese
			'ru' => 'русский язык', // Russian
			'et' => 'Eesti keel',
			'tr' => 'Türkçe',
			'el' => 'ελληνικά',
			'ja' => '日本語',
			'zh-tw' => '中文',
			'ar' => 'العربية', // Arabic
			'fa' => 'پارسی', // Persian
			'ur' => 'اردو', // Urdu
			'he' => 'עִבְרִית', // Hebrew
);



if (empty($_POST) ) {

	foreach ($languages AS $lang => $current) {
		if ($current) {
			echo '<li class="pull-right dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">' . 
			'<span class="glyphicon glyphicon-flag"></span> ' . $langnames[$lang] . ' <b class="caret"></b></a>';
		} 
	}

	echo '<ul class="dropdown-menu">';

	$textarray = array();

	foreach ($languages AS $lang => $current) {
		if (!$current) {

			$url = htmlspecialchars(
				SimpleSAML_Utilities::addURLparameter(
						SimpleSAML_Utilities::selfURL(), array(
							'language' => $lang,
						)));
			$title = $langnames[$lang];
			echo '<li><a href="' . $url . '">' . $title . '</a></li>';

		}
	}
	echo '</ul>';

	

}

