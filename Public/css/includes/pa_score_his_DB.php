<?php
/**
 * 考核成績歷史
 *  $CreateBy: Dennis $
 *  $CreateDate: 2009-03-12$
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_score_his_DB.php $
 *  $Id: pa_score_his_DB.php 1385 2009-04-07 07:39:21Z dennis $
 *  $LastChangedDate: 2009-04-07 15:39:21 +0800 (周二, 07 四月 2009) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 1385 $  
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
require_once 'AresPA.class.php';
$PA = new AresPA($_SESSION['user']['company_id'],
				 $_SESSION['user']['emp_seq_no']);
				 

if(isset($_GET['emp_seqno']) && !empty($_GET['emp_seqno']) )
{
	// 最近三次绩考成绩
	//pr($_SESSION['user']);
	$g_parser->ParseTable('pa_his_list',$PA->getPAHis($_GET['emp_seqno']));
	$g_parser->ParseMultiLang('ESNB007',$_SESSION['user']['language']);
}