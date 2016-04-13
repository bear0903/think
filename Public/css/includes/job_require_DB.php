<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     职缺查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/job_require_DB.php $
 *  $Id: job_require_DB.php 3824 2014-08-14 02:07:58Z dennis $
 *  $Rev: 3824 $ 
 *  $Date: 2014-08-14 10:07:58 +0800 (周四, 14 八月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-08-14 10:07:58 +0800 (周四, 14 八月 2014) $
 ****************************************************************************/
if (! defined ( "DOCROOT" )) {
	die ( "Attack Error." );
}
require_once $config ['lib_dir'] . '/AresJD.class.php';
//pr($_SESSION);
if (isset ( $_GET ['empseqno'] ) && ! empty ( $_GET ['empseqno'] ))
	$emp_seq_no = $_GET ['empseqno'];

$AresJD = new AresJD ( $_SESSION ['user'] ['company_id'], $_SESSION ['user'] ['emp_seq_no'] );
//pr($AresJD->getCompetence());
$g_parser->ParseTable ( "competence_list", $AresJD->getCompetence () );

//$g_tpl->assign ( 'MY_BOSS_EMPSEQNO', $AresJD->getDeptLeaderId ( $_SESSION ['user'] ['dept_seqno'] ) );