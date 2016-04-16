<?php
/*
 * eHR Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.areschina.com/license/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@areschina.com so we can send you a copy immediately.
 *
 * @category   eHR
 * @package    AuthCode
 * @subpackage AuthCode
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:AuthCode.php 2797 Jan 16, 2008 3:50:49 PM Dennis $
 */
 
 /**
 *
 * @category   eHR
 * @package    AuthCode
 * @subpackage AuthCode
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
    session_start();
    function random($len)
    {
    	$srcstr='ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
    	mt_srand();
    	$strs='';
    	for($i=0;$i<$len;$i++){
    		$strs.=$srcstr[mt_rand(time()%10,33)];
    	}// end loop
    	return $strs;
    }
    $str=random(5); //随机生成的字符串
    $width = isset($_GET['width']) ? $_GET['width'] : 60;	//验证码图片的宽度
    $height= isset($_GET['height']) ? $_GET['height'] :25;	//验证码图片的高度
     
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');             // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache'); // HTTP/1.0
    header('Content-Type:image/png');
    
    $im=imagecreate($width,$height);
    $back=imagecolorallocate($im,0xFF,0xFF,0xFF);	//背景色
    $pix=imagecolorallocate($im,187,190,247);		//模糊点颜色
    $font=imagecolorallocate($im,41,163,238);		//字体色
    
    //绘模糊作用的点
    mt_srand();
    for($i=0;$i<1000;$i++)
    {
    	imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$pix);
    }
    imagestring($im, 5, 7, 5,$str, $font);
    imagerectangle($im,0,0,$width-1,$height-1,$font);
    imagepng($im);
    imagedestroy($im);
    $_SESSION['authcode'] = $str;
?>