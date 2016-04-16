<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     Employee Job Desc. Form
 *  $Id: job_desc_DB.php 3363 2012-10-16 06:53:10Z dennis $
 *  $LastChangedDate: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3363 $  
 ****************************************************************************/
if (! defined('DOCROOT')) {
	die('Attack Error.');
}
//echo get_include_path();
require_once 'AresJD.class.php';
//print_r($_SESSION);
// add by dennis
// 挑直属主管/权责主管(上司的上司)/部属 JD

$company_id=empty($_GET['companyid'])?$_SESSION ['user'] ['company_id']:$_GET['companyid'];
$emp_seq_no=empty($_GET['empseqno'])?$_SESSION ['user'] ['emp_seq_no']:$_GET['empseqno'];
$AresJD = new AresJD($company_id, $emp_seq_no);
//echo $company_id.'---'.$emp_seq_no;exit;
//pr($AresJD->getJDMaster ());exit;
$g_parser->ParseOneRow($AresJD->getJDMaster ());
$g_parser->ParseTable('sub_dept_list', $AresJD->getSubDept ());
$g_parser->ParseTable('competence_list', $AresJD->getCompetence ());
$g_parser->ParseTable('position_duty_list', $AresJD->getDutyList ());
$g_parser->ParseTable('pmd_list', $AresJD->getPMD ());
$g_tpl->assign('MY_BOSS_EMPSEQNO', $AresJD->getDeptLeaderId($_SESSION['user']['dept_seqno']));
unset($AresJD);