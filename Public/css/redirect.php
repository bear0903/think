<?php
/****************************************************************************\
 *  Copyright (C) 2004 ARES China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     Redirect to the specify page via the script name
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/redirect.php $
 *  $Id: redirect.php 3828 2014-08-20 07:11:27Z dennis $
 *  $Rev: 3828 $
 *  $Date: 2014-08-20 15:11:27 +0800 (周三, 20 八月 2014) $
 *  $Author: dennis $
 *  $LastChangedDate: 2014-08-20 15:11:27 +0800 (周三, 20 八月 2014) $
 ****************************************************************************/
if (ob_get_length() === FALSE                &&
    !ini_get('zlib.output_compression')         &&
    ini_get('output_handler') != 'ob_gzhandler' &&
    ini_get('output_handler') != 'mb_output_handler') {
	ob_start('ob_gzhandler');
}// end if

define('DOCROOT', '..');

// system config file
require_once (DOCROOT . '/conf/config.inc.php');
$_POST = cleanArray($_POST);
// add by dennis for replace $_GET['scriptname'] 2012-08-20
$scriptname = isset($_GET['scriptname']) ? $_GET['scriptname'] : '';

// 非法访问,导航到 Login 页面
if(!isset($_SESSION['user']) && 
   !empty($scriptname)		  && 
   !isset($_GET['key'])		  &&
	$scriptname !== 'findpasswd' &&
	$scriptname !== 'view_flowchart'
){ // add findpasswd by dennis 2013-01-14
	header('Location: '.$GLOBALS['config']['ess_home']);
	exit();
}
/**
 * add session timeout detect by dennis 2012-08-07
 */
if (isset($_SESSION['timeout']) && time()-$_SESSION['timeout']> ini_get('session.gc_maxlifetime'))
{
	session_destroy(); // add by dennis 2012-08-13
	header('Location: '.$GLOBALS['config']['ess_home'].'/index.php?action=logout');
	exit();
}else{
	$_SESSION['timeout'] = time();
}
//add by Terry , 2011-8-24
//add session 判断，找回密码时，这里会出错，建议把此段代码拿到 login 中　by dennis 2011-09-22
// remark by dennis 2012-09-11 only use one layout style
/*
if(isset($_SESSION['user']) && getDefaultLayout($g_db_sql) == 'iframeLayout'){
	define('DEFAULT_APP', 'ESN0000_1');
}else{
	define('DEFAULT_APP', 'ESN0000');
}*/
define('DEFAULT_APP', 'ESN0000_1');
// add by dennis 20120302 for check first login
first_login_check();

// add by dennis 2011-05-30
function salary_security_check()
{
	foreach($GLOBALS['config']['double_auth_list'] as $sname)
	{
		foreach($_SESSION['sys_menu'] as $k=>$v)
		{
			if ($sname == $v['NODEID']) return true;
        }
	}
	return false;
}

// 检查 double check page 是否符合安全要求
if (!empty($scriptname) &&
	in_array($scriptname,$GLOBALS['config']['double_auth_list']))
{
	if (!salary_security_check())
	{
		$g_tpl->assign('DOCUMENT_TITLE','404 Page Not Found');
		showMsg('Page Not Found:'.$scriptname,'error');
		exit;
	}
}
// modify by Dennis 20090527
// 无需要验证的特殊程式,如从Mail中签核或是找回 Password

// add by dennis 2013/10/22 只授权 mss 权限时，自动跳到 mss 首页
if (isset($_SESSION['user'])){
    require_once 'AresUser.class.php';
    $User = new AresUser($_SESSION['user']['company_id'],$_SESSION['user']['user_name']);
    $ess_menu = $User->GetMenu($_SESSION['user']['user_seq_no'],'ess',$GLOBALS['config']['default_lang']);

    // end add
    if (!in_array($scriptname, $GLOBALS['config']['none_auth_list']) &&
        empty($_SESSION['user']['emp_seq_no']) &&
        !isset($_GET['empseqno']) ||
        count($ess_menu) == 0) // add by dennis for only grant mss moudle 2013/10/22 @sunon
    {
    	header('Location: '.DOCROOT.'/mgr/redirect.php');
    	exit();
    }// end if
}
//add by boll  处理共用的button多语问题 ?? 是否还有用到?? dennis 2010-09-08
include_once 'AresAction.class.php';

//add by boll oldurl  if salary  then  goto checkpage  after 1 minute
$g_tpl->assign('oldUrlString', '&'. http_build_query($_GET));

/**
 * Set default theme according the config
 * add by dennis 2011-09-05
 */
$g_tpl->assign('DEFAULT_THEME', $GLOBALS['config']['sys_param']['default_theme']);

$document_title = isset($_GET['appdesc']) ? $_GET['appdesc'] : '';
/* remark by dennis 2012-08-20 (rewrite)
if (isset($_GET['menu_offset']) && $_GET['menu_offset']>=0)
{
	$document_title = getAppDescByOffset($_GET['menu_offset']);
	$g_tpl->assign('menu_offset',$_GET['menu_offset']);
}else{
	// 处理薪资查询特殊验证页面
	if(isset($_SESSION['sys_menu']))
	{
		$sname = isset($_GET['fromscript']) && !empty($_GET['fromscript']) ? $_GET['fromscript'] : $scriptname;
		$document_title = getMenuByScriptname($sname,'ess',$_SESSION['sys_menu']);
	}
}
*/
if (isset($_SESSION['sys_menu']))
{
	$appid = isset($_GET['fromscript']) && !empty($_GET['fromscript']) ? $_GET['fromscript'] : $scriptname;
	$document_title = $document_title !== '' ? $document_title : getAppDescById($appid,$_SESSION['sys_menu']);
}
$g_tpl->assign ('BLOCK_TITLE',$document_title);
// end modify
if (!empty($_GET['destroy_salary_view_approve'])) $_SESSION['salary_view_approve']['is_auth'] = '';
// do security check
// mail 签核加班请假的 link 不做安全查检
if (!empty($scriptname) &&
    !in_array($scriptname, $GLOBALS['config']['none_auth_list'])) {
	// for security reason, here check use_seq_no not password
	security_check($_SESSION['user']['user_seq_no'], $GLOBALS['config']['ess_home'] . '/index.php');
}

// 取得当前请求的 scriptname[也即要显示哪支程式]
$scriptname = !empty($scriptname) ? $scriptname : DEFAULT_APP;
// add by dennis 2012-08-21 hasPermission 暂未完善
//if(!hasPermission($scriptname,$_SESSION['sys_menu'])) die('No Permission,Attack Error.');
//验证需要双重验证的页面
// add by dennis 2008-06-20
if (in_array($scriptname, $GLOBALS['config']['double_auth_list']))
{
    // 如果$_POST的值中没有输入验证码，直接跳转到验证码输入页面
	if (!isset($_POST['password']) &&
	    !isset($_POST['authcode']) &&
	    empty($_POST['password'])  &&
	    empty($_POST['authcode'])  &&
	    empty($_SESSION['salary_view_approve']['is_auth'])) {
		header ('Location: ?scriptname=salary_auth_page&fromscript='.$scriptname.'&empseqno='.$_GET['empseqno']);
		exit ();
	} // end double check
	// Set timer
    $js_code = <<<eof
        function auto_logout_salary(){
            location.href="?%s&scriptname=salary_auth_page&fromscript=%s&appdesc=%s&destroy_salary_view_approve=Y";
        }
eof;
    $js_timer = "setTimeout('auto_logout_salary()', ".$GLOBALS['config']['sys_param']['salary_timeout'].");"; // 30 seconds
	$g_tpl->assign('logoutscript', sprintf($js_code,http_build_query($_GET),$scriptname,$document_title));
    $g_tpl->assign('timer_js',$js_timer);
	//echo 'current script name ->'.$scriptname.'<br/>';
} else {
	//点到非双验证程式时，把已验证过的清除
	// echo '非双验证程式 -》'.$scriptname.'<br/>';
	foreach($GLOBALS['config']['double_auth_list'] as $appid) {
		//echo $appid.'<br/>';
		if (isset($_SESSION[$appid]['is_auth'])) {
			//echo $scriptname.'<br/>';
			unset($_SESSION[$appid]);
		} // end if
	} // end foreach
} // end if

// mss 中调用 ess 或是 ess 调用 mss 程式时,把 scriptname 重写
if (array_key_exists($scriptname, $GLOBALS['config']['pub_app'])) {
	$scriptname = $GLOBALS['config']['pub_app'][$scriptname];
	header('Location: '.DOCROOT.'/mgr/redirect.php?scriptname='.$scriptname.'&appdesc='.urlencode($document_title));
	exit;
} // end if

// default actual file name eq scriptname
$actual_file_name = $scriptname;


// 如果程式通过设定设定出来的程式，统一调用 template,实际文件名称为 'public_template'
if (is_app_defined($scriptname)){	
	$actual_file_name = 'public_template';
} // end if

// 调用的是程式代码, 这里转换成实际的 file name
if (array_key_exists($scriptname, $GLOBALS['config']['ess_app_map'])) {
    $actual_file_name = $GLOBALS['config']['ess_app_map'][$scriptname];
}// end if

// add by dennis 20091021
// user defined workflow applcation
if (is_array($GLOBALS['config']['ud_wf_app']) && in_array( $scriptname, $GLOBALS['config']['ud_wf_app'])) {
	$actual_file_name = 'user_define_wf';
}

if (file_exists($g_tpl->template_dir . $actual_file_name . '.html')) {
	// Get unique cache ID according login user / Mail 中的签核无 session
	$my_cache_id = '';
	if (isset($_SESSION['user'])) {
		$my_cache_id = $_SESSION['user']['company_id'] .
		               $_SESSION['user']['user_seq_no'] .
		               $GLOBALS['config']['default_lang'] .
		               $actual_file_name . $_SERVER['REQUEST_URI'];
	} // end if
	// Model File Here
	$db_file = $GLOBALS['config']['inc_dir'] . '/' . $actual_file_name . '_DB.php';
	if (file_exists($db_file)) {
		// no cache file
		if (!$g_tpl->is_cached($actual_file_name . '.html', $my_cache_id)) {
			require_once $db_file;
		} // end if
	} // end if

	// 只有定制的程式才会需要再 get 一次多语
	if (! in_array($scriptname, $GLOBALS['config']['no_multi_lang_app'])) {
		// 检查有没有共用某支程式的多语
		$multi_lang_key = isset($GLOBALS['config']['pub_lang_map'][$scriptname]) ? $GLOBALS['config']['pub_lang_map'][$scriptname] : $scriptname;
		// 程式中 call 的是实际的 filename 也即 actual_file_name, 再反向得到其多语 programno
		$lang_program_no = array_search ($multi_lang_key, $GLOBALS['config']['ess_app_map']);
		// add by dennis for user defined workflow form (multi-language)
		if (is_array($GLOBALS['config']['ud_wf_app']) && in_array($scriptname,$GLOBALS['config']['ud_wf_app']))
		{
			$lang_program_no = 'ESNW001';
		}
		$multi_lang_key = $lang_program_no ? $lang_program_no : $multi_lang_key;
		//echo 'least appid-> '.$multi_lang_key.'<br/>';
		$g_parser->ParseMultiLang($multi_lang_key, $GLOBALS['config']['default_lang']);
	} // end if

	// 根据 cache  list 中的设定 判断当前程式是否需要做 cache
	if (array_search($scriptname, $GLOBALS['config']['cache_app_list'])) {
		$g_tpl->caching = 2; // lifetime is per cache
		$g_tpl->compile_check = true;
		$g_tpl->cache_lifetime = $GLOBALS['config']['cache_left_time'];
	} // end cache
	// parse template and display to end-user
	// Assign relate path, eg. js, img, css
	$g_tpl->assign ($GLOBALS['config']['dir_array']);
	$g_tpl->assign('SCRIPT_NAME', $scriptname);
	if ($scriptname != 'grid' && $scriptname !== 'ESN0000_1'){  // modify by TerryWang
		$g_tpl->display('PageHeader.html');
	}
	// for get unique cache file
	if (empty($special_template)) $special_template = $actual_file_name;
	$g_tpl->display ($special_template . '.html', $my_cache_id);
	if ($scriptname != 'grid' && $scriptname !== 'ESN0000_1'){ // modify by TerryWang
		$g_tpl->display ('PageFooter.html');
	}
} else {
	// for product run
	showMsg('<h3>HTTP 404 - File not found</h3><br>' . '<strong>File Name:</strong>' . $scriptname . '<br/><strong>Application Name</strong>:' . urldecode ($_GET['appdesc']), 'error');
	exit ();
} //end if