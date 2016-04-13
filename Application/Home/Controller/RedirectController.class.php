<?php

namespace Home\Controller;
use Think\Controller;
define('APP_PATH','./Application/');

class RedirectController extends Controller{
	function index(){
		//echo "hello";
		if (ob_get_length() === FALSE                &&
				!ini_get('zlib.output_compression')         &&
				ini_get('output_handler') != 'ob_gzhandler' &&
				ini_get('output_handler') != 'mb_output_handler') {
					ob_start('ob_gzhandler');
				}
		
		define('DOCROOT', '..');
		define('APP_PATH','./Application/');
		include '/Conf/config.inc.php';
		
		$cleanArray = new \
		$_POST=\cleanArray($_POST);
		
		$this->display('home');
	}
}
