<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     薪资条查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/salary_slip_DB.php $
 *  $Id: salary_slip_DB.php 3414 2012-12-06 05:33:07Z dennis $
 *  $Rev: 3414 $ 
 *  $Date: 2012-12-06 13:33:07 +0800 (周四, 06 十二月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-12-06 13:33:07 +0800 (周四, 06 十二月 2012) $
 *********************************************************/

include_once 'salary_query.php';
$salary_row = $Salary->GetEmployeeSalaryList($m);
// for security reason, encypt the salary query parameters
$cnt = count($salary_row);
$encrypt_key = md5($_SESSION['user']['emp_seq_no'].session_id());
for($i=0; $i<$cnt; $i++)
{
	$salary_row[$i]['PERIODSALARY_RESULT_ID'] = encrypt($salary_row[$i]['PERIODSALARY_RESULT_ID'], $encrypt_key);
	$salary_row[$i]['EMP_SEQ_NO']             = encrypt($salary_row[$i]['EMP_SEQ_NO'],$encrypt_key);
	$salary_row[$i]['PERIOD_DETAIL_ID']       = encrypt($salary_row[$i]['PERIOD_DETAIL_ID'],$encrypt_key);
}
$g_parser->ParseTable ('salary_form_list',$salary_row);
