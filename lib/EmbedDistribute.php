<?php

class EmbedDistribute {
	
	private $foodle;
	private $template;
	
	function __construct($foodle, $template) {
		$this->foodle = $foodle;
		$this->template = $template;
	}
	
	private static function wrap($text) {
		return '<pre style="border: 1px solid #ccc; padding: 4px 20px"><code>' . htmlspecialchars($text) . '</code></pre>';
	}
	
	private function embedIframe() {
		return self::wrap(
			'<iframe style="border: 1px solid #999; width: 100%; height: 20em; overflow-y: scroll" 
	src="' . FoodleUtils::getUrl() . 'embed/' . $this->foodle->identifier . '?output=htmliframe" >
</iframe>'
		);
	}
	
	function show() {
		
		echo('<h3><a href="#">' . $this->template->t('embed_header') . '</a></h3>');
		echo('<div>');
		echo('<p>' . $this->template->t('embed_text1') . '</p>');
		echo($this->embedIframe());
		echo('<p>' . $this->template->t('embed_api') . '</p>');
		echo('</div>');
	}
	
	
}



?>