<?php
/**********************************************************************\
  * (C)  2008 ARES CHINA All Rights Reserved.  http://www.areschina.com
  *
  *  Desc
  *    接班人维护查询
  *  Create By: Dennis  Create Date: 2008-11-19 ����05:44:56
  *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_successor_plan_DB.php $
  *  $Id: pa_successor_plan_DB.php 1663 2009-06-01 08:07:17Z dennis $
  *  $Date: 2009-06-01 16:07:17 +0800 (周一, 01 六月 2009) $ 
  *  $Author: dennis $
  *  $Revision: 1663 $  
  *  $LastChangedDate: 2009-06-01 16:07:17 +0800 (周一, 01 六月 2009) $
 \ **********************************************************************/ 
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
require_once 'AresPA.class.php';
$PA = new AresPA($_SESSION['user']['company_id'],
				 $_SESSION['user']['emp_seq_no']);

$pa_period_seqno = isset($_POST['pa_period_seqno']) ? 
                   $_POST['pa_period_seqno']        : 
                   $PA->getLeastPAPeriod();

$g_parser->ParseSelect('pa_period_list',$PA->getPAPeriod(true),'pa_seqno',$pa_period_seqno);
$is_need_maintian = $PA->isNeededMaintain($pa_period_seqno);
//echo $is_need_maintian.'<br>';
$g_tpl->assign('is_need_maintian',$is_need_maintian);

// 自动查出当前还在自评期间的考核期间接班人维护的资料
if ($pa_period_seqno>0)
{
	//echo $pa_period_seqno.'<hr/>';
	$g_parser->ParseOneRow($PA->getPAEmpInfoByPeriod($pa_period_seqno));

	if ($is_need_maintian)
	{
		$g_parser->ParseTable('pa_successor_list',$PA->getSuccessorList($pa_period_seqno));
		// modify by dennis 20090601 兒童節快樂呀....
		$g_tpl->clear_assign('NO_NEED_MANTAIN_MSG');
	}
}// end if