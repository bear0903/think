<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     薪资异动历史详细资料查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/salary_adjust_his_DB.php $
 *  $Id: salary_adjust_his_DB.php 3414 2012-12-06 05:33:07Z dennis $
 *  $Rev: 3414 $ 
 *  $Date: 2012-12-06 13:33:07 +0800 (周四, 06 十二月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-12-06 13:33:07 +0800 (周四, 06 十二月 2012) $
 *********************************************************/
if (! defined ( 'DOCROOT' ) || 
	//! $_SESSION [$_GET ['scriptname']] ['is_auth']) {
	! $_SESSION ['salary_view_approve'] ['is_auth']) {
	die ( 'Attack Error.');
}// end if
require_once 'AresSalary.class.php';

$company_id = $_SESSION['user']['company_id'];
$emp_seq_no = isset($_GET['empseqno']) && !empty($_GET['empseqno']) ? 
              $_GET['empseqno'] : 
              $_SESSION['user']['emp_seq_no'];
$encrypt_key = md5($_SESSION['user']['emp_seq_no'].session_id());             
$Salary = new AresSalary ($company_id,$emp_seq_no );
$promo_list = $Salary->getSalaryPromotionMaster();
$cnt = count($promo_list);
for($i=0; $i<$cnt; $i++)
{
	$promo_list[$i]['PSN_ID'] = encrypt($promo_list[$i]['PSN_ID'], $encrypt_key);
}
$g_parser->ParseTable('salary_promotion_list', $promo_list);