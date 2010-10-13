<?php

class UNINETTDistribute {
	
	private $foodle;
	private $template;
	
	function __construct($foodle, $template) {
		$this->foodle = $foodle;
		$this->template = $template;
	}
	
	private function getId() {
		return $this->foodle->identifier;
	}
	
	private function getTitle() {
		return $this->foodle->name;
	}
	
	function show() {
		
		echo(
			'<h2>' . 
			$this->template->getTranslation(
			array(
				'en' => 'Publish to Eureka',
				'no' => 'Publiser på Eureka',
			)) . '</h2>'
		);

		echo('
			<form action="http://lab.nymedia.no/uninett/addfoodle" method="get" >
				<input type="hidden" name="id" value="' . htmlspecialchars($this->getId()) . '" />
				<input type="hidden" name="name" value="' . htmlspecialchars($this->getTitle()) . '" />
				<input type="submit" value="'  . 
					htmlspecialchars(
						$this->template->getTranslation(
						array(
							'en' => 'Publish this Foodle',
							'no' => 'Publiser denne Foodlen',
						)
					)
				) . 
			'" /></form>'
		);

		echo('<p>' .
			$this->template->getTranslation(
			array(
				'en' => 'If you have already published this Foodle to Eureka, re-publishing will create a duplicate. If you modify this Foodle, modifications wil automatically be reflected on Eureka (except from the title of the Foodle).',
				'no' => 'Dersom du allerede har publisert denne Foodlen på Eureka, vil du få en kopi dersom du publiserer på nytt. Dersom du endrer denne Foodlen vil alle endringer automatisk bli oppdatert på Eureka (med unntak av tittelen).',
			)) . '</p>'
		);

		
		
	}
	
	
}



?>