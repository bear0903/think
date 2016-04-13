<?php
/**
 * 用途:    新建session存储目录
 * 使用方法:  把$root的值 改为实际的session_save路径
 * 
 * 在定义session.save_path中可以定义多级存放的路径，修改php.ini
 * session.save_path = "2;/data/session_tmp"
 * 
**/
$root = dirname(__FILE__)."temp/";  //修为实际的session_save路径
//$root='d:/session/';
$string = '0123456789abcdefghijklmnopqrstuvwxyz';
$length = strlen($string);
for($i = 0; $i < $length; $i++) {
	$path_str=$root.$string[$i];
	//echo $path_str;
	@mkdir($path_str, 0777);
    for($j = 0; $j < $length; $j++) {
	   $path_str=$root.$string[$i]."/".$string[$j];
	   //echo $path_str;
       @mkdir($path_str, 0777);
    }
}
echo 'Session Directories Created Successfully.';
