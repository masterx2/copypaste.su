<?php
namespace App;

class Router {

	public static $fenom;
	public static $url;

	public static function start() {
		self::$url = isset($_GET['url']) && $_GET['url'];
	        self::$fenom = \Fenom::factory('../templates', '../templates/cache', [
        		'force_compile' => true,
            		'strip' => true
        	]);
		if (!isset(self::$url) || self::$url == '') {
			self::$fenom->display('index.tpl',[]);
		} else {
			echo 'Error';
		}
	}
}
