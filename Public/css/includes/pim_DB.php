<?php
/**
 * 个人出勤相关信息汇总
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pim_DB.php $
 *  $Id: pim_DB.php 3769 2014-05-30 07:28:26Z dennis $
 *  $Rev: 3769 $ 
 *  $Date: 2014-05-30 15:28:26 +0800 (周五, 30 五月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-05-30 15:28:26 +0800 (周五, 30 五月 2014) $
 *********************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if
require_once 'AresAttendanceSummary.php';
$companyid = isset($_GET['company_id']) && !empty($_GET['companyid']) ? $_GET['companyid'] : $_SESSION['user']['company_id']; 	
$empseqno = isset($_GET['empseqno']) && !empty($_GET['empseqno']) ? $_GET['empseqno'] : $_SESSION['user']['emp_seq_no'];
$empSummaryData = new AresAttendanceSummary($companyid,$empseqno);

$g_parser->ParseTable('overtime_summary',  $empSummaryData->getOvertimeSummary());
$g_parser->ParseTable('leave_summary',     $empSummaryData->getLeaveSummary());

// remark by dennis 2011/8/12 改到在可休假况查询中显示
$g_parser->ParseTable('compensatory_leave',$empSummaryData->getCompensatorySummary());
/*
// 年假改为只在一个地方显示，以免用户困扰
$g_parser->ParseTable('year_vacation',$empSummaryData->getYearVacationSummary());
*/
// 预设显示当年的资料
$g_tpl->assign('year',date('Y'));

