<?php
/*************************************************************\
 *  Copyright (C) 2008 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     薪资明细查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/salary_slip_detail_DB.php $
 *  $Id: salary_slip_detail_DB.php 3473 2013-03-12 08:32:55Z dennis $
 *  $Rev: 3473 $ 
 *  $Date: 2013-03-12 16:32:55 +0800 (周二, 12 三月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-03-12 16:32:55 +0800 (周二, 12 三月 2013) $
 *********************************************************/
if (! defined('DOCROOT')) {
	die('Attack Error.');
}

if (isset($_GET ['salary_period_id']) && isset ($_GET['salary_key_id'])) {
	require_once 'AresSalary.class.php';
	// for security reason,encrypt before trans decrypt url value
	$encrypt_key 	  = md5($_SESSION['user']['emp_seq_no'].session_id());
	$detail_id        = (int)decrypt($_GET ['salary_period_id'],$encrypt_key); //强制转换成数字
	$salary_result_id = decrypt($_GET ['salary_key_id'],$encrypt_key);
	
	$emp_seqno = isset($_GET['emp_seq_no']) && !empty($_GET['emp_seq_no']) ? 
	             decrypt($_GET['emp_seq_no'],$encrypt_key) : 
	             $_SESSION['user']['emp_seq_no'];
	
	$default_lang = $GLOBALS['config']['default_lang'];	
	$Salary = new AresSalary ($_SESSION['user']['company_id'],$emp_seqno);
	// check data permission, add by dennis 2012-12-06
	// 员工自己可以查看自己的薪资,不受权限管控 add by dennis 2013-03-12
	if ($emp_seqno !== $_SESSION['user']['emp_seq_no'] && $Salary->checkSalaryPermission($emp_seqno)==='N'){
		session_destroy();
		showMsg('Attack Error, No Salary Permission.','error',DOCROOT.'/ess/index.php?action=logout');
		exit;
	}
	//页头上面的说明
	$master_row = $Salary->GetSalaryDetailList($salary_result_id,$emp_seqno);
	$g_parser->ParseOneRow ($master_row);
	/*
	if (is_array($master_row)){
		
	}else{
		// try get others salary, 徙劳 ~
		session_destroy();
		showMsg('Attack Error','error',DOCROOT.'/ess/index.php?action=logout');
		exit;
	}*/
	//START_add by wilson 20110706	for 0006084_[14300172/CVSH97頎邦科技-HCP系統升級新版方案2010-7-1~2010/08/01]_計算臨時薪資總和
	/**
	 * for get salary subtotal
	 * @param array $arr
	 * @param string $k
	 * @return number
	 * @author Dennis 2013-03-12
	 */
	function sumArray($arr,$k = 'AMOUNT'){
		$r = 0;
		if (is_array($arr)){
			foreach($arr as $row){
				$r += $row[$k];
			}
		}
		return $r;
	}
	//END_add by wilson 20110706	for 0006084_[14300172/CVSH97頎邦科技-HCP系統升級新版方案2010-7-1~2010/08/01]_計算臨時薪資總和
	// 固定薪资
	$fixed_sal_list =  $Salary->GetFixSalaryList ($detail_id);	
	$g_parser->ParseTable('fixed_salary_list', $fixed_sal_list);
	$g_tpl->assign('fixed_sal_total',sumArray($fixed_sal_list));
		
	//临时薪资
	$salary_temp = $Salary->GetTemporarySalaryList($detail_id);
	//pr($salary_temp);
	$salary_temp_total = '';
	for ($i=0; $i<count($salary_temp);$i++)
	{
		if ($salary_temp[$i]['PLUSTYPE'] == 'PLUS'){
			$salary_temp_total = $salary_temp_total + $salary_temp[$i]['AMOUNT'];
				//echo '+';
		}
		else if ($salary_temp[$i]['PLUSTYPE'] == 'MINUS'){
			$salary_temp_total = $salary_temp_total - $salary_temp[$i]['AMOUNT'];
				//echo '-';
		}
		//echo $salary_temp[$i]['AMOUNT'];
	}
	$g_parser->ParseTable('salary_temporary_list', $salary_temp);
	$g_tpl->assign('salary_temp_total',$salary_temp_total);
	//加班薪资
	$sal_ot_list = $Salary->GetOvertimeSalaryList($detail_id);
	$g_parser->ParseTable('salary_overtime_list', $sal_ot_list);
	$g_tpl->assign('ot_fee_total',sumArray($fixed_sal_list));
	
	//请假扣款
	$sal_abs_list = $Salary->GetAbsenceSalaryList($detail_id);
	$g_parser->ParseTable('salary_absence_list',$sal_abs_list );
	$g_tpl->assign('abs_fee_total',sumArray($sal_abs_list));
	
	//社保明细
	$sal_insure_list = $Salary->GetInsureSalaryList($detail_id);
	$g_parser->ParseTable('salary_insure_list', $sal_insure_list);
	$g_tpl->assign('psn_pay_total',sumArray($sal_insure_list,'EMP_PAY'));
	$g_tpl->assign('com_pay_total',sumArray($sal_insure_list,'COMPANY_PAY'));
	
	//一般奖金
	$sal_bonus_list = $Salary->GetBonusSalaryList($detail_id);
	$g_parser->ParseTable('salary_bonus_list',$sal_bonus_list);
	$g_tpl->assign('sal_bonus_total',sumArray($sal_bonus_list,'OLDER_AMOUNT'));
	
	//劳健团保
	$insure_tw_list = $Salary->GetInsureTW($detail_id, $default_lang, $salary_result_id);
	$g_parser->ParseTable('insure_tw_list', $insure_tw_list);
	$g_tpl->assign('tw_psn_pay_total',sumArray($insure_tw_list,'EMP_PAY'));
	$g_tpl->assign('tw_com_pay_total',sumArray($insure_tw_list,'COMPANY_PAY'));
		
	//公司提拔 
	// modified by Dennis 2008/03/27 16:38:43 PM 
	$com_tibo_list = $Salary->GetComTiBo ($default_lang, $detail_id);
	$g_parser->ParseTable('company_tibo_list', $com_tibo_list);
	$g_tpl->assign('com_tibo_total',sumArray($com_tibo_list));
	
	//特殊奖金
	$spec_bonus_list = $Salary->GetSpecBonus($detail_id);
	$g_parser->ParseTable('spec_bonus_list', $spec_bonus_list);
	$g_tpl->assign('init_bonus_total',sumArray($spec_bonus_list,'INIT_AMOUNT'));
	$g_tpl->assign('tax_bonus_total',sumArray($spec_bonus_list,'TAX_AMOUNT'));
	$g_tpl->assign('fact_bonus_total',sumArray($spec_bonus_list,'FACT_AMOUNT'));
	
	unset($Salary);
}
