<?php
/**
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
 * @subpackage Util
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @version    $Id: Util.php 2797 2007-12-25 02:09:30 PM Dennis$
 */

/**
 * public funtions
 * @category   eHR
 * @subpackage Util
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
include_once 'CacheClass.php';
/**
 * 格式化数据
 * @param string $value  被格式化的数据
 * @param string $format 格式遮罩
 * @param string $data_type 资料类型
 * @return mixed 返回按指定格式,格式化的数据
 */
function formatData($data,$format,$data_type = 'string')
{
	if ('date'== $data_type)
	{
		// 预设的日期格式为 Y-m-d 相当于 oracle 中的 YYYY-MM-DD
		$fmt = $format ? $format : 'Y-m-d';
		$data = date($fmt,strtotime($data));
	}// end if
    return $data;
}// end formatData()

/**
 * 程式错误(如调用 function 参数不正确之类，与应用无关的 error)
 *
 * @param string $func_name 被调的函数名称
 * @param string $error_text 错误信息
 */
function error($func_name,$error_text)
{
    trigger_error('<font color="red">PHP Fatal Error: <b>'.__FILE__.'</b> ,Function <b>'.$func_name.'()</b> :'.$error_text.'</font>',E_USER_ERROR);
}// end errorMessage()
/**
 * dump variables all properties
 * @param mixed $var
 * @return string
 * @access global
 * @global true
 * @author Dennis
 */
function dd($var)
{
    echo '*************************** Start Debug Message *****************************<br/>';
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    echo '*************************** End   Debug Message *****************************<br/>';
}// end dd()

function getGUID($str = 'gridview')
{
    return $str.'-'.md5(mt_rand(0,time()));
}

function debug($dbAdapter)
{
    if ($GLOBALS['debug'])
    {
        $dbAdapter->getProfiler()->setEnabled(true);
        $profiler = $dbAdapter->getProfiler();
        $profiler->setEnabled(true);
        $totalTime    = $profiler->getTotalElapsedSecs();
        $queryCount   = $profiler->getTotalNumQueries();
        $longestTime  = 0;
        $longestQuery = null;
        foreach ($profiler->getQueryProfiles() as $query) {
            if ($query->getElapsedSecs() > $longestTime) {
                $longestTime  = $query->getElapsedSecs();
                $longestQuery = $query->getQuery();
            }
        }
        echo '执行了 ' . $queryCount . ' 次查询,耗时 ' . $totalTime . ' 秒' . "<br/>";
        echo '平均每次查询耗时: ' . $totalTime / $queryCount . ' 秒' . "<br/>";
        echo '每秒执行查询次数: ' . $queryCount / $totalTime . " 次<br/>";
        echo '耗时最长查询时间: ' . $longestTime . "秒<br/>";
        echo '耗时最长查询语句:' . $longestQuery . "<br/>";
    }
}// end debug
/**
 * 加密 URL string
 * @param string $url
 * @return string
 */
function encode_url($url)
{
    return rawurlencode(base64_encode($url));
}// end encode_url()

/**
 * 解密加密的 url
 * @param string $url
 * @return string
 */
function decode_url($url)
{
    return str_replace(array('&amp;', '&#38;'), '&', base64_decode(rawurldecode($url)));
}// end decode_url()
/*
function encrypt($string, $key) {
    $result = '';
    for($i=0; $i<strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key))-1, 1);
        $char = chr(ord($char)+ord($keychar));
        $result.=$char;
    }// end for loop
    return base64_encode($result);
}// end encrypt

function decrypt($string, $key) {
    $result = '';
    $string = base64_decode($string);
    for($i=0; $i<strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key))-1, 1);
        $char = chr(ord($char)-ord($keychar));
        $result.=$char;
    }// end for loop
    return $result;
}// end decrypt
*/
?>