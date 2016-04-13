<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     编辑个人资料
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/edit_personal_info_DB.php $
 *  $Id: edit_personal_info_DB.php 3782 2014-07-11 02:31:20Z dennis $
 *  $Rev: 3782 $ 
 *  $Date: 2014-07-11 10:31:20 +0800 (周五, 11 七月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-07-11 10:31:20 +0800 (周五, 11 七月 2014) $
 ****************************************************************************/

if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if
require_once 'AresEmployee.class.php';
$Employee = new AresEmployee ($_SESSION ['user'] ['company_id'], 
							  $_SESSION ['user'] ['emp_seq_no'] );

if (isset($_POST ['submit'])) {
	unset($_POST['submit']);
	//pr($_POST);exit;
	//pr($_SESSION);
	if ($Employee->UpdateEmpInfo($_POST ) > 0) {
	    echo getMultiLangMsg('ESNA017','SAVE_SUCCESS_MSG',$_SESSION['user']['language']);
	    
	    showMsg(getMultiLangMsg('ESNA017','SAVE_SUCCESS_MSG',$_SESSION['user']['language']),'success');
		//showMsg('資料暫存成功，等待 HR 相關人員審核後才會生效.','success');
	}else{
	    showMsg(getMultiLangMsg('ESNA017','SAVE_FAILURE_MSG',$_SESSION['user']['language']),'error');
		//showMsg('資料修改失敗，請稍後重試.','error');
	}// end if
}// end if
$g_parser->ParseOneRow($Employee->getEmpContactInfo());
unset ($Employee );