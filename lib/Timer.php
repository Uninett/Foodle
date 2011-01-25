<?php


class Timer {

	protected static $last;
	protected static $list = array();

	public static function start() {
		self::$last = microtime(TRUE);
// 		error_log('start ' . self::$last);
	}

	public static function tick($tag) {

		if (!self::$last) self::start();

		$now = microtime(TRUE);
		// error_log('tick ' . $now . ' '. $tag);
		self::$list[] = array(  ((float)$now - (float)self::$last), $tag);
		self::$last = $now;
	}
	
	public static function getList() {
		return self::$list;
	}

}