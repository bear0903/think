<?php
/**********************************************************************\
  * (C)  2008 ARES CHINA All Rights Reserved.  http://www.areschina.com
  *
  *  Desc
  *    绩效考核汇总报表(考核时) 
  *  Create By: Dennis  Create Date: 2008-12-2 ����04:32:14
  *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_summary_rpt_DB.php $
  *  $Id: pa_summary_rpt_DB.php 1507 2009-04-28 06:17:57Z dennis $
  *  $LastChangedDate: 2009-04-28 14:17:57 +0800 (周二, 28 四月 2009) $
  *  $LastChangedBy: dennis $
  *  $LastChangedRevision: 1507 $  
  * 
 \ **********************************************************************/ 
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
require_once 'AresPA.class.php';
$PA = new AresPA($_SESSION['user']['company_id'],
				 $_SESSION['user']['emp_seq_no']);

/*
 *  [scriptname] => pa_summary_rpt
    [pa_period_seqno] => 1
    [pa_period_desc] => HR 2008 上半年考核
    [group_seqno] => A
    [group_desc] => 行政部
    [std_master_seqno] => 1
    [std_master_desc] => 主管适用
    [whoami] => 
 * 
 * 
 */
//pr($_GET);

if (isset($_GET['pa_period_seqno'])  && 
    !empty($_GET['pa_period_seqno']) &&
    isset($_GET['group_seqno'])      && 
    !empty($_GET['group_seqno'])     &&
    isset($_GET['std_master_seqno']) && 
    !empty($_GET['std_master_seqno'])){
    	
    function _array_sum($arr) {
    	$c = 0;
    	for ($i=0; $i<count($arr); $i++)
    	{
    		$c += $arr[$i]['HEADCOUNT'];
    	}// end loop
    	return $c;
    }//end _array_sum()
    $max_role_id = $PA->getMaxRole($_GET['pa_period_seqno'],
    							   $_GET['group_seqno'],
    							   $_GET['std_master_seqno']);
	//echo $max_role_id;
    $g_tpl->assign('max_role_id',$max_role_id);
    $had_pa_headcount  = $PA->getHeadcountByRole($_GET['pa_period_seqno'],
    											 $_GET['group_seqno'],
    											 $_GET['std_master_seqno'],
    											 $max_role_id,
    											 $max_role_id);
    											 
    $default_headcount = $PA->getDefaultHeadcountByPercent($_GET['pa_period_seqno'],$_GET['group_seqno'],$_GET['std_master_seqno'],$max_role_id);
    
    function _getLastSummaryRecords(array $had_pa_headcount,array $default_headcount)
    {
    	for ($i=0; $i<count($default_headcount); $i++)
	    {
	    	for($j=0; $j<count($had_pa_headcount);$j++)
	    	{
	    		if ($had_pa_headcount[$j]['LEVEL_SEQNO'] == $default_headcount[$i]['LEVEL_SEQNO'])
	    		{
	    			$default_headcount[$i]['HAD_SCORE_HEADCOUNT'] = $had_pa_headcount[$j]['HAD_SCORE_HEADCOUNT'];
	    			$default_headcount[$i]['DIFF_HEADCOUNT'] = $had_pa_headcount[$j]['HAD_SCORE_HEADCOUNT'] - $default_headcount[$i]['RULE_HEADCOUNT'];
	    			$default_headcount[$i]['HAD_SCORE_PERCENT'] = $had_pa_headcount[$j]['HAD_SCORE_HEADCOUNT'] / $default_headcount[$i]['HEADCOUNT']*100;
	    			$default_headcount[$i]['DIFF_PERCENT'] = $default_headcount[$i]['HAD_SCORE_PERCENT'] - $default_headcount[$i]['RULE_PERCENT'];
	    		}
	    	}
	    }// end for loop
	    //pr($default_headcount);
	    return $default_headcount;
    }// end _getLastSummaryRecords()
    
    $def_hc = _getLastSummaryRecords($had_pa_headcount,$default_headcount);
    switch(strtolower($max_role_id))
    {
    	case 'mgr3':
    		$g_tpl->assign('mgr3_score_summary',$def_hc);
    		$had_pa_headcount1 = $PA->getHeadcountByRole($_GET['pa_period_seqno'],
    											         $_GET['group_seqno'],
    											         $_GET['std_master_seqno'],
		    											 $max_role_id,
		    											 'mgr2');
			$g_tpl->assign('mgr2_score_summary',_getLastSummaryRecords($had_pa_headcount1,$default_headcount));		    											 
    		break;
    	case 'mgr2':
    		$g_tpl->assign('mgr2_score_summary',$def_hc);
    		$had_pa_headcount1 = $PA->getHeadcountByRole($_GET['pa_period_seqno'],
    											         $_GET['group_seqno'],
    											         $_GET['std_master_seqno'],
		    											 $max_role_id,
		    											 'mgr1');
			$g_tpl->assign('mgr1_score_summary',_getLastSummaryRecords($had_pa_headcount1,$default_headcount));		    											 
    		break;
    	case 'mgr1':
    		$g_tpl->assign('mgr1_score_summary',$def_hc);
    		$had_pa_headcount1 = $PA->getHeadcountByRole($_GET['pa_period_seqno'],
    											         $_GET['group_seqno'],
    											         $_GET['std_master_seqno'],
		    											 $max_role_id,
		    											 'emp');
			$g_tpl->assign('emp_score_summary',_getLastSummaryRecords($had_pa_headcount1,$default_headcount));		    											 
    		break;
    	default:break;
    }
    /*
    $emp_score_summary = $PA->getHeadCountByGroupSTD($_GET['pa_period_seqno'],
													$_GET['group_seqno'],
													$_GET['std_master_seqno'],
													$_GET['std_master_desc'],
													'emp');
	$g_tpl->assign('emp_score_summary',$emp_score_summary);
	$g_tpl->assign('emp_headcount_total',_array_sum($emp_score_summary));
	//pr($emp_score_summary);
	$mgr1_score_summary = $PA->getHeadCountByGroupSTD($_GET['pa_period_seqno'],
													 $_GET['group_seqno'],
													 $_GET['std_master_seqno'],
													 $_GET['std_master_desc'],
													 'mgr1');
	$g_tpl->assign('mgr1_score_summary',$mgr1_score_summary);
	$g_tpl->assign('mgr1_headcount_total',_array_sum($mgr1_score_summary));													 
    if ($_GET['whoami'] == 'mgr2') {    	
		$mgr2_score_summary = $PA->getHeadCountByGroupSTD($_GET['pa_period_seqno'],
														  $_GET['group_seqno'],
														  $_GET['std_master_seqno'],
														  $_GET['std_master_desc'],
														  'mgr2');
		$g_tpl->assign('mgr2_score_summary',$mgr2_score_summary);
		$g_tpl->assign('mgr2_headcount_total',_array_sum($mgr2_score_summary));																	
     }elseif ($_GET['whoami'] == 'mgr3') {
		$mgr2_score_summary = $PA->getHeadCountByGroupSTD($_GET['pa_period_seqno'],
														  $_GET['group_seqno'],
														  $_GET['std_master_seqno'],
														  $_GET['std_master_desc'],
														  'mgr2');
		$g_tpl->assign('mgr2_score_summary',$mgr2_score_summary);
		$g_tpl->assign('mgr2_headcount_total',_array_sum($mgr2_score_summary));	
		$mgr3_score_summary = $PA->getHeadCountByGroupSTD($_GET['pa_period_seqno'],
														 $_GET['group_seqno'],
														 $_GET['std_master_seqno'],
														 $_GET['std_master_desc'],
														 'mgr3');
		$g_tpl->assign('mgr3_score_summary',$mgr3_score_summary);
		$g_tpl->assign('mgr3_headcount_total',_array_sum($mgr3_score_summary));															 
    }// end if
	*/
    
}// end if
