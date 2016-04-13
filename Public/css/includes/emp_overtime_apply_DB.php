<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     Employee overtime apply
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_overtime_apply_DB.php $
 *  $Id: emp_overtime_apply_DB.php 3575 2013-10-30 08:13:28Z dennis $
 *  $Rev: 3575 $
 *  $Date: 2013-10-30 16:13:28 +0800 (周三, 30 十月 2013) $
 *  $Author: dennis $
 *  $LastChangedDate: 2013-10-30 16:13:28 +0800 (周三, 30 十月 2013) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if
// back 保留表单资料
session_cache_limiter('private');
require_once 'AresAttend.class.php';
$Attend = new AresAttend ( $_SESSION['user']['company_id'],
						   $_SESSION['user']['emp_seq_no']);

$g_tpl->assign ('overtime_rule',html_entity_decode($Attend->getRuleText()));
if (isset ( $_POST ['overtime_date'] ) &&
	! empty ( $_POST ['overtime_date'] )) {
	global $result;
	// 计算加班起始结束日期时间
	$_date  = explode ( '-', $_POST ['overtime_date'] );
	$_btime = explode ( ':', $_POST ['begin_time'] );
	$_etime = explode ( ':', $_POST ['end_time'] );
	$_begin_date = mktime ( $_btime [0], $_btime [1], 0, $_date [1], $_date [2], $_date [0] );
	$_end_date   = mktime ( $_etime [0], $_etime [1], 0, $_date [1], $_date [2], $_date [0] );
	$begin_time  = date ( 'Y-m-d H:i', $_begin_date );
	$end_time    = date ( 'Y-m-d H:i', $_end_date );

	// 如果加班结束时间小于开始时间, 表示其跨天
	if ($_end_date < $_begin_date) {
		$end_time = date ('Y-m-d H:i', mktime($_etime[0],$_etime [1],0,$_date[1],$_date[2] + 1, $_date[0]));
	}// end if

	//$tmp_save = isset($_POST['submit']) ? 'Y' : 'N';
	$tmp_save = 'Y'; // modify by dennis 2013/10/21 已经无暂存功能，所有记录都即时提交
	// 批量输入加班资料
	if (isset ( $_POST ['action'] ) 		&&
		$_POST ['action'] == 'batch_apply'  &&
		isset ( $_POST ['emp_seqno'] )      &&
		is_array ($_POST ['emp_seqno'] )) {
		$max_batch_count = 10;  // 直接处理的最大笔数
		$n = count($_POST['emp_seqno']);
		if($n>$max_batch_count){
			include_once DOCROOT.'/libs/AresConcurrentRequest.class.php';
			$concurrentRequest = new AresConcurrentRequest();
			$concurrentRequest->saveRequestOvertimeApply($_SESSION ['user'] ['user_seq_no'],
														 $begin_time,
														 $end_time,
														 floatval($_POST['overtime_hours']),
														 $_POST['overtime_reason'],
														 $_POST['overtime_fee_type'],
														 $_POST['overtime_type'],
														 $_POST['remark'],
														 $tmp_save,
														 $_POST['emp_seqno'],
														 $_POST['dept_seqno'],
														 $_SESSION ['user'] ['company_id']);
			exit;
		}
		$result = $Attend->batchOvertimeApply($_SESSION ['user'] ['user_seq_no'],
											   $begin_time,
											   $end_time,
											   floatval($_POST['overtime_hours']),
											   $_POST['overtime_reason'],
											   $_POST['overtime_fee_type'],
											   $_POST['overtime_type'],
											   $_POST['remark'],
											   $tmp_save,
											   $_POST['emp_seqno'],
											   $_POST['dept_seqno']);
		//pr($result);
		//exit;
		$success_count=0;
		$failure_count=0;
		for ($i=0; $i<count($result);$i++)
		{
			$result[$i]['dept_id']   = $_POST['dept_id'][$i];
			$result[$i]['dept_name'] = $_POST['dept_name'][$i];
			$result[$i]['emp_id']    = $_POST['emp_id'][$i];
			$result[$i]['emp_name']  = $_POST['emp_name'][$i];
			if($result[$i]['is_success']=='Y'){
				$success_count++;
			}else{
				$failure_count++;
			}
		}// end for loop

		// 显示提交的结果
		$g_tpl->assign ('success_count', $success_count);
		$g_tpl->assign ('failure_count', $failure_count);
		$g_parser->ParseOneRow($_POST);
		$g_parser->ParseTable('apply_result',$result);
		// rewrite 最后显示的画面的模板(显示申请结果的模版)
		$actual_file_name = 'apply_result';

	} else {
		// only for current login user
		//$g_db_sql->debug = 1;
		$result = $Attend->SaveOvertimeApply($_SESSION ['user'] ['user_seq_no'],
											 $_SESSION ['user'] ['dept_seqno'],
											 $begin_time,
											 $end_time,
											 floatval($_POST['overtime_hours']),
											 $_POST['overtime_reason'],
											 $_POST['overtime_fee_type'],
											 $_POST['overtime_type'],
											 $_POST['remark'],
											 $tmp_save);
		//pr($result);
		if ($result ['is_success'] == 'Y') {
			if (! empty ( $_POST ['submit'] )) {
				showMsg($result ['msg'], 'success');
			}// end if
			if (! empty ( $_POST ['save'] )) {
				showMsg($result ['msg']);
			}// end if
		} else {
			showMsg($result ['msg'],'error' );
		}// end if
	}// end if
}else{	
	$g_parser->ParseSelect ('overtime_fee_type',
							$Attend->getListMultiLang('ESNA014',
													  'OVERTIME_FEE_TYPE',
													  $GLOBALS ['config'] ['default_lang']),'');
	$g_parser->ParseSelect ('overtime_type',
							$Attend->getListMultiLang('ESNA014',
													  'OVERTIME_TYPE',
													  $GLOBALS ['config'] ['default_lang']),'');
	$g_parser->ParseSelect ('overtime_reason_list',$Attend->GetOvertimeReason(),'');
	// add by dennis 2011-12-09 22:18
	$g_tpl->assign('otfeejs_code',$Attend->getOTTypeFee());
}// end if
