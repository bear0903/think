<?php

namespace Home\Controller;
use Think\Controller;
define('APP_PATH','./Application/');

class RedirectController extends Controller{
	function index(){
		/* $user = D("user");
		$user = _initialize(); */
		//echo "hello";
		if (ob_get_length() === FALSE                &&
				!ini_get('zlib.output_compression')         &&
				ini_get('output_handler') != 'ob_gzhandler' &&
				ini_get('output_handler') != 'mb_output_handler') {
					ob_start('ob_gzhandler');
				}
		
		
		
		$this->display('ESN0000_1');
	}
}
