<?php
/**
 *  目标考核单
 *  $CreateBy: Dennis $
 *  $CreateDate: 2013/09/24 $
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_form_DB.php $
 *  $Id: pa_form_DB.php 3133 2011-07-07 09:03:26Z dennis $
 *  $LastChangedDate: 2011-07-07 17:03:26 +0800 (Thu, 07 Jul 2011) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3133 $  
 ****************************************************************************/
if (!defined('DOCROOT')) die ('Attack Error.');
//!!! 保存资料调用的是 pa_goal_setting_DB.php !!!

require_once 'AresPAGoal.class.php';
$myself = $_SESSION['user']['emp_seq_no'];
$PA = new AresPAGoal($_SESSION['user']['company_id'],$myself);
//pr($_GET);
if (isset($_GET['pa_seqno']) && $_GET['pa_seqno'] != '')
{
	$pa_form_seqno = $_GET['pa_seqno'];
	$g_parser->ParseSelect('goal_type_list',$PA->getGoalTypeList(),'');
	$g_parser->ParseOneRow($PA->getEmpInfo($pa_form_seqno));
	
	// 如果 _if 表里有资料就以挑 _if(暂存)档里的资料 
	$data_source_flag = $PA->getDataSourceFlag($pa_form_seqno)>0 ? '_if' : '';
	$goal_master = $PA->getGoalMasterList($pa_form_seqno,$data_source_flag);
	$goal_detail = $PA->getGoalDetailList($pa_form_seqno,$data_source_flag);
	//pr($goal_detail);
	
	$mcnt = count($goal_master);
	$dcnt = count($goal_detail);
	
	// recombine the array, set the detail data as master array sub-array
	for($i=0; $i<$mcnt;$i++){
		$k = 0;
		for($j=0;$j<$dcnt; $j++){
			if ($goal_detail[$j]['MASTER_GOAL_SEQNO'] == $goal_master[$i]['MASTER_GOAL_SEQNO']){
				$goal_master[$i]['detail'][$k] = $goal_detail[$j];
				$k++;
			}
		}
		$goal_master[$i]['ROWSPAN'] = $k>1 ? 'rowspan="'.$k.'"' : '';
	}
	//$g_tpl->assign('mgr_comment',(count($goal_master)>0? $goal_master[0]['MGR_COMMENT']:''));
	$g_parser->ParseTable('pa_goal_list', $goal_master);
}
