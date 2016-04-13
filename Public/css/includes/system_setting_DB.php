<?php
/**
 * 系统设置
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/system_setting_DB.php $
 *  $Id: system_setting_DB.php 1158 2009-02-02 07:10:44Z boll $
 *  $Rev: 1158 $ 
 *  $Date: 2009-02-02 15:10:44 +0800 (周一, 02 二月 2009) $
 *  $Author: boll $   
 *  $LastChangedDate: 2009-02-02 15:10:44 +0800 (周一, 02 二月 2009) $
 *********************************************************/
//include_once 'systemsetting_password_DB.php';
	if (! defined ( 'DOCROOT' )) {
		die ( 'Attack Error.' );
	}// end if
	
	header('Location: ../mgr/redirect.php?scriptname=systemsetting_password&menu_offset=17');
	exit();