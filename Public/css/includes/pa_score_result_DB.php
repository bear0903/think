<?php
/**********************************************************************\
  * (C)  2008 ARES CHINA All Rights Reserved.  http://www.areschina.com
  *
  *  Desc
  *    员工考核成绩查询
  *  Create By: Dennis  Create Date: 2008-12-5 ����02:39:27
  *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_score_result_DB.php $
  *  $Id: pa_score_result_DB.php 931 2008-12-05 07:41:43Z dennis $
  *  $LastChangedDate: 2008-12-05 15:41:43 +0800 (周五, 05 十二月 2008) $
  *  $LastChangedBy: dennis $
  *  $LastChangedRevision: 931 $  
  * 
 \ **********************************************************************/
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
require_once 'AresPA.class.php';
$PA = new AresPA($_SESSION['user']['company_id'],
				 $_SESSION['user']['emp_seq_no']);

$pa_period_seqno = isset($_POST['pa_period_seqno']) ? $_POST['pa_period_seqno']: $PA->getLeastPAPeriod();

$g_parser->ParseSelect('pa_period_list',$PA->getPAPeriod(true),'pa_seqno',$pa_period_seqno);
// 自动查出考核成绩
$g_parser->ParseTable('pa_score_result',$PA->getPAScore($pa_period_seqno));