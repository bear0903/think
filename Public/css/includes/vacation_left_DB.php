<?php
/**
 * 可休假查询
 * Create Date 2009-02-03 by Dennis
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/vacation_left_DB.php $
 *  $Id: vacation_left_DB.php 3731 2014-04-23 09:18:52Z dennis $
 *  $Rev: 3731 $ 
 *  $Date: 2014-04-23 17:18:52 +0800 (周三, 23 四月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-04-23 17:18:52 +0800 (周三, 23 四月 2014) $
 *********************************************************/
if (! defined ( 'DOCROOT' )) die ( 'Attack Error.' );
include_once 'AresAttend.class.php';

$Attend = new AresAttend($_SESSION['user']['company_id'],
						 $_SESSION['user']['emp_seq_no']);

require_once 'AresAttendanceSummary.php';	
$empSummaryData = new AresAttendanceSummary($_SESSION['user']['company_id'],
											$_SESSION['user']['emp_seq_no']);

$g_parser->ParseSelect('leave_list',$Attend->GetVacationList($_SESSION['user']['sex'],'1'),'','');
$g_parser->ParseTable('leave_name_list',$Attend->GetVacationLeftN(@$_POST['leave_name_id']));
$date = !empty($_POST['base_date']) ? $_POST['base_date'] : date('Y-m-d');
$g_parser->ParseTable('compensatory_leave',$empSummaryData->getCompensatorySummary($date));
/*
$g_parser->ParseTable('year_vacation',     $empSummaryData->getYearVacationSummary($date));
*/
// 共用 pim 程式多語
$g_parser->ParseMultiLang('ESNA018', $_SESSION['user']['language']);
