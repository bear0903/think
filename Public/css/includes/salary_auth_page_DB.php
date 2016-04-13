<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     查询薪资/奖金时再次验证密码
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/salary_auth_page_DB.php $
 *  $Id: salary_auth_page_DB.php 3363 2012-10-16 06:53:10Z dennis $
 *  $Rev: 3363 $ 
 *  $Date: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 *********************************************************/
if (! defined ('DOCROOT'))	die ('Attack Error.');
if (count($_POST)> 0) {
	$password_flag = false;
	$auth_flag = false;
	$msgs = get_multi_lang($GLOBALS['config']['default_lang'],'ESNC');
	if (empty ($_POST['password'])) {
		$g_tpl->assign('password_msg',$msgs['001']);
	} // end if

	if (empty($_POST['authcode'])) {
		$g_tpl->assign('authcode_msg',$msgs['002']);
	} // end if	

	if (! empty($_POST['password'])&& ! empty($_POST['authcode'])) {
		include_once 'AresUser.class.php';
		$User = new AresUser($_SESSION['user']['company_id'],
				 $_SESSION['user']['user_name']);		
		if ($User->ValidatePassword($_POST['password'])) {
			$password_flag = true;
		} else {
			$g_tpl->assign ('password_msg', $msgs['003']);
		} // end if
		if (strtoupper($_POST['authcode'])== strtoupper(@$_SESSION['securimage_code_value'])) {
			$auth_flag = true;
		} else {
			$g_tpl->assign ('authcode_msg',$msgs['004']);
			//return;
		} // end if
	}
	if ($password_flag && $auth_flag) {
		// 表示二次验证通过，在调用薪资条查询的程式的时候会验证这个变数
		//$_SESSION[$_GET['fromscript']]['is_auth'] = true;
		$_SESSION['salary_view_approve']['is_auth'] = 'Y';
		unset($_SESSION['securimage_code_value']);
		//pr($_GET);exit;
		header('Location: redirect.php?scriptname='.$_POST['fromscript'].'&appdesc='.$_POST['appdesc']);
		exit ();
	} // end if;
} // end if
