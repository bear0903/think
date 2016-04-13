<?php
/*************************************************************\
 *  Copyright (C) 2008 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     ESS Home Page
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/home_DB.php $
 *  $Id: home_DB.php 3841 2014-09-17 08:18:33Z dennis $
 *  $Rev: 3841 $ 
 *  $Date: 2014-09-17 16:18:33 +0800 (周三, 17 九月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-09-17 16:18:33 +0800 (周三, 17 九月 2014) $
 ****************************************************************************/
if (!defined('DOCROOT')) die('Attack Error.');
$companyid = $_SESSION['user']['company_id'];
$empseqno  = $_SESSION['user']['emp_seq_no'];
$userseqno = $_SESSION['user']['user_seq_no'];
require_once 'AresDesktop.class.php';
require_once 'AresCalendar.class.php';
$MyDesktop = new AresDesktop ($companyid,$userseqno);
$g_parser->ParseTable ('company_news_list', $MyDesktop->GetCompanyNews());
$g_parser->ParseTable ('personal_news_list', $MyDesktop->GetMyNotices());
// show employee calendar | add by dennis 2014/02/07
if (!isset($hide_calendar)){
    $y = date('Y');
    $m = date('m');
    $calendar = new SolarCalendar($y,$m,$companyid,$empseqno,strtolower ($GLOBALS['config']['default_lang']));
    $calendar->setHeaderBarOff();
    $g_tpl->assign('calendar', $calendar->getMonthView($m,$y,'N'));
}
// check workflow schema installed
if($GLOBALS['config']['is_wf_installed'])
{
	// workflow task list
	require_once 'AresWorkflow.class.php';
	$Workflow = new AresWorkflow();
	$leave_apply_count = $Workflow->GetWaitforApproveList($companyid,$empseqno,'absence',true);                                        
	$overtime_apply_count = $Workflow->GetWaitforApproveList($companyid,$empseqno,'overtime',true);
	$cancel_leave_apply_count = $Workflow->GetWaitforApproveList($companyid,$empseqno,'cancel_absence',true);
	$nocard_apply_count = $Workflow->GetWaitforApproveList($companyid,$empseqno,'nocard',true);
	$trans_apply_count  = $Workflow->GetWaitforApproveList($companyid,$empseqno,'trans',true);
	$resign_apply_count = $Workflow->GetWaitforApproveList($companyid,$empseqno,'resign',true);
	// rewrite by Dennis 2014/01/09
	/* Performance Tuning 还是用原来的，原来的 GetWaitforApproveList() 有修改，如果 count 的时候就不加 order by 子句
	$leave_apply_count = $Workflow->getWaitApproveCnt('absence', $companyid, $empseqno);
	$overtime_apply_count = $Workflow->getWaitApproveCnt('overtime', $companyid, $empseqno);
	$cancel_leave_apply_count = $Workflow->getWaitApproveCnt('cancel_absence', $companyid, $empseqno);
	$nocard_apply_count = $Workflow->getWaitApproveCnt('nocard', $companyid, $empseqno);
	$trans_apply_count  = $Workflow->getWaitApproveCnt('trans', $companyid, $empseqno);
	$resign_apply_count = $Workflow->getWaitApproveCnt('resign', $companyid, $empseqno);*/
	
	
	$g_tpl->assign('leave_apply_count',$leave_apply_count);
	$g_tpl->assign('cancel_leave_apply_count',$cancel_leave_apply_count);
	$g_tpl->assign('overtime_apply_count',$overtime_apply_count);
	$g_tpl->assign('trans_apply_count',$trans_apply_count);
	$g_tpl->assign('nocard_apply_count',$nocard_apply_count);
	$g_tpl->assign('resign_apply_count',$resign_apply_count);
	                   
	// 使用者自定义 workflow 待签核清单 add by dennis 20091109
	require_once 'AresUserDefineWF.php';
	$udwf = new AresUserDefineWF($companyid,$userseqno);
	$g_parser->ParseTable('user_define_wf_list',$udwf->getAllWaitApprove($empseqno));
}
// 待评核绩考单
include_once 'pa_period_list_DB.php';
