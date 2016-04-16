<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     薪资异动历史查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/salary_adjust_his_detail_DB.php $
 *  $Id: salary_adjust_his_detail_DB.php 3414 2012-12-06 05:33:07Z dennis $
 *  $Rev: 3414 $ 
 *  $Date: 2012-12-06 13:33:07 +0800 (周四, 06 十二月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-12-06 13:33:07 +0800 (周四, 06 十二月 2012) $
 *********************************************************/
if (! defined ( 'DOCROOT' ) || 
	! isset ( $_GET ['vdate'] ) ||
	/*! $_SESSION [$_GET ['scriptname']] ['is_auth']*/
	! $_SESSION ['salary_view_approve'] ['is_auth']) {
	die ( 'Attack Error.');
}// end if
$encrypt_key = md5($_SESSION['user']['emp_seq_no'].session_id());
$company_id = $_SESSION['user']['company_id'];
$emp_seq_no = isset($_GET['empseqno']) && !empty($_GET['empseqno']) ? 
              decrypt($_GET['empseqno'], $encrypt_key) : 
              $_SESSION['user']['emp_seq_no'];
require_once 'AresSalary.class.php';
$Salary = new AresSalary ($company_id, 
						  $emp_seq_no);
//pr($_GET);
/**
 * 
 * 加总金额
 * @param array $rs
 */
function t($rs)
{
	$t = 0;
	if (is_array($rs))
	{
		for ($i=0; $i<count($rs); $i++)
		{
			$t+= $rs[$i]['AMOUNT'];
		}
	}
	return $t;
}

$p_detail_list = $Salary->getSalaryPromotionDetail($_GET ['vdate']);
$g_parser->ParseTable ('promotion_detail_list',$p_detail_list);
$g_tpl->assign('total_amount',t($p_detail_list));

if (isset ( $_GET ['nvdate'] ) && ! empty ( $_GET ['nvdate'] ))
{
	$p_detail_list1 = $Salary->getSalaryPromotionDetail($_GET ['nvdate'],'1');
	$g_parser->ParseTable ('pre_promotion_detail_list',  $p_detail_list1);
	$g_tpl->assign('total_amount1',t($p_detail_list1));
}


$g_parser->ParseMultiLang ('ESNC', $config['default_lang']);