<?php
/*
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/systemsetting_password_DB.php $
 *  $Id: systemsetting_password_DB.php 3313 2012-03-05 08:53:05Z dennis $
 *  $Rev: 3313 $ 
 *  $Date: 2012-03-05 16:53:05 +0800 (周一, 05 三月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-03-05 16:53:05 +0800 (周一, 05 三月 2012) $
 ****************************************************************************/
if (!defined('DOCROOT'))die('Attack Error.');

if(!empty($_POST['old_password']))
{
	$get_params = isset($_GET['firstlogin']) && isset($_GET['token']) ? '&firstlogin='.$_GET['firstlogin'].'&token='.$_GET['token'] : '';
	$back_url = '?scriptname='.$_GET['scriptname'].$get_params;
	require_once 'AresUser.class.php';
	$user = new AresUser ( $_SESSION['user']['company_id'],
						   $_SESSION['user']['user_name'] );
	
	if($user->ValidatePassword ($_POST['old_password'])){
		if($user->ResetPassword($_POST['new_password'])){
			$back_url = $get_params ? $GLOBALS['config']['ess_home'] : $back_url;
			$errMsg=get_app_muti_lang('MDNS101','MSG_UPDATE_SUCCESS',$_SESSION['user']['language'],'IT');
		}else{
			$errMsg=get_app_muti_lang('MDNS101','MSG_UPDATE_FAILURE',$_SESSION['user']['language'],'IT');
		}
	}else{
		$errMsg=get_app_muti_lang('MDNS101','MSG_OLD_PWD_INCORRECT',$_SESSION['user']['language'],'IT');
	}
	showMsg($errMsg,'information',$back_url);
}