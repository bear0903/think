<?php
/****************************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     eHR Login Page[Login screen]
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/index.php $
 *  $Id: index.php 3756 2014-05-12 07:56:35Z dennis $
 *  $Rev: 3756 $ 
 *  $Date: 2014-05-12 15:56:35 +0800 (周一, 12 五月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-05-12 15:56:35 +0800 (周一, 12 五月 2014) $
 ****************************************************************************/
define('DOCROOT', '..');
require_once (DOCROOT . '/conf/config.inc.php');
// write log
//员工登出时间更新  add by jack 2006-8-22
if (isset($_GET['action'])   && 
	'logout' == $_GET['action'] && 
	isset ($_SESSION['user']['company_id']) && 
	isset ($_SESSION['user']['user_name'])) {
	require_once 'AresUser.class.php';	
	$User = new AresUser ($_SESSION['user']['company_id'],
						  $_SESSION['user']['user_name']);
	$User->UpdateLogouttime('eHR');	
	// destory global variabales
	//unset($GLOBALS);
} // end if
// 只要进到 Index 页面就销毁 Session
if (session_id() != ''){
    session_unset ();
    //session_destroy (); // 如果用 Browser 的Back,来到这一页销毁原来的 Session
    $_SESSION=array();  //Modified by hunk at 20160121 for bothhand cust
    session_regenerate_id(); 
    session_destroy();
}
/**
 * Authentication Intergration with Windows AD
 * Add by Dennis 2009-04-07
 */
//$_SERVER['REMOTE_USER'] = 'areschina\dlan';

if (isset($_SERVER['REMOTE_USER']) && !empty($_SERVER['REMOTE_USER']))
{
	//session_start();
	header('Location: ./includes/login.php?authtype=sspi&remote_user='.urlencode($_SERVER['REMOTE_USER']).'&sessid='.session_id());
	exit;
}// end if

/**
 * Authentication from EIP (Enterprise Information Portal) URL
 * http://your_host_name/ess/index.php?authtype=eip&usernmae=xxx&passwd=xxx&companyno=xxx
 * add by Dennis 2010-06-07
 */
if (isset($_GET['authtype'])  && $_GET['authtype'] == 'eip' &&
    isset($_GET['password'])  && !empty($_GET['password'])  &&
    isset($_GET['companyno']) && !empty($_GET['companyno']))
{
    header('Location: ./includes/login.php?'.$_SERVER['QUERY_STRING']);
	exit;
}

$g_tpl->assign ($GLOBALS['config']['dir_array']);
/**
 * Set default theme according the config
 * add by dennis 2011-09-05
 */
$g_tpl->assign('DEFAULT_THEME', $GLOBALS['config']['sys_param']['default_theme']);
include_once($GLOBALS['config']['inc_dir'].'/index_DB.php');
$g_parser->ParseMultiLang ('ESN0000', $GLOBALS['config']['default_lang']);
$g_tpl->display('PageHeader.html');
$g_tpl->display('index.html');
$g_tpl->display('PageFooter.html');


