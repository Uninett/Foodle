<?php

#require_once('functions.');

class Event {

	
	var $event;
	
	public function Event($ne) {
		$this->event = $ne;
		// echo '<pre>';
		// print_r($ne);
		// exit;
	}
	
	private function parseTime($text) {
		// Handle zulu time
		if (substr($text,-1) == 'Z') {
			return strtotime($text);
		}
		
		$splitted = explode(':', $text);
		$key = $splitted[0];
		$value = $splitted[1];

		return strtotime($value);
		
	}
	
	/**
	 * Get start timestamp in epoch
	 */
	public function getStart() {
		return $this->parseTime($this->event->dtstart);
	}
	
	/**
	 * Get start timestamp in epoch
	 */
	public function getEnd() {
		return $this->parseTime($this->event->dtend);
	}
	
	/**
	 * Is this event in between begin and end?
	 */
	public function between($begin, $end) {
		$starttime = $this->getStart();
		return ($starttime >= $begin AND $starttime < $end);
	}
	
	/*
	 * Returns TRUE if the given interval overlaps this event.
	 */
	public function overlap($begin, $end) {
		$ebegin = $this->getStart();
		$eend = $this->getEnd();
		
		/*
		 * Sjekker om en aktuelt intervall (begin, end)
		 * overlapper med denne hendelsen (ebegin, eend)
		 */
		
		if ($begin >= $ebegin && $begin < $eend) return TRUE;
		if ($end > $ebegin && $end <= $eend ) return TRUE;
		if ($begin <= $ebegin && $end >= $eend ) return TRUE;
		
		return FALSE;
	}
	
	
	public function showShort() {
		$text = $this->event->summary; 
	
		$text = preg_replace('/\[.*?\]/', '', $text);
		return $text;
	}
	
	public function getLocation() {
		return $this->event->location; 
	}
	
	public function getDescription() {
		return $this->event->description; 
	}
	
	
	public function dump() {
		return '<li>' . $this->event->summary . ' (' . $this->getStart() . ' : ' . $this->event->dtstart . ')' . 
			join(',', $this->slotRange()) .
			'</li>';
	}

	public function slotRange() {
		$resolution = 15 * 60; // 15 minutes
	
		$start = $this->getStart();
		$end = $this->getEnd();
		
		$estart = floor($start / $resolution);
		$eend   = ceil($end / $resolution);
		$slots = $eend - $estart;
		
		return array($estart, $slots);
	}


}
?>