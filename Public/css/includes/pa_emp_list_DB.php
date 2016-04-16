<?php
/**********************************************************************\
  * (C)  2008 ARES CHINA All Rights Reserved.  http://www.areschina.com
  *
  *  Desc
  *  
  *  Create By: Dennis  Create Date: 2008-12-2 ����03:24:14
  *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_emp_list_DB.php $
  *  $Id: pa_emp_list_DB.php 2333 2009-11-20 08:07:35Z dennis $
  *  $LastChangedDate: 2009-11-20 16:07:35 +0800 (周五, 20 十一月 2009) $
  *  $LastChangedBy: dennis $
  *  $LastChangedRevision: 2333 $  
  * 
 \ **********************************************************************/ 
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif

require_once 'AresPA.class.php';

// 記錄當前的 url, 為主管評核完後回到員工清單之時用到
$_SESSION['pa_emp_list_url'] = urlencode('?'.$_SERVER['QUERY_STRING']);

$PA = new AresPA($_SESSION['user']['company_id'],
				 $_SESSION['user']['emp_seq_no']);

// 输入分数即时得到等第　add by dennis 20090507
if(isset($_GET['ajaxcall'])          && 
   $_GET['ajaxcall']== 1             && 
   isset($_GET['std_master_seqno'])  &&
   !empty($_GET['std_master_seqno']) &&
   isset($_GET['mgr3_score'])        &&
   !empty($_GET['mgr3_score']))
{
	echo json_encode($PA->getPALevelIDByScore($_GET['std_master_seqno'],$_GET['mgr3_score']));
	exit;
}// end if
/**
 * 核定主管核等(Batch Approve)
 */				 
if(isset($_POST['approve_score']) && !empty($_POST['approve_score']))
{
	//pr($_POST);
	$rank_keys = array_keys($_POST['approve_score']);
	$rank_values = array_values($_POST['approve_score']);
	$comment_values =  array_values($_POST['approve_comment']);
	$cnt = count($rank_keys);
	//pr($rank_keys);
	for ($j=0; $j<$cnt; $j++)
	{
		if (!empty($rank_values[$j]))
		{
			$r = $PA->updateLastScore($rank_keys[$j],
			                          $rank_values[$j],
			                          $comment_values[$j],
			                          $PA->getFormStatus('mgr3','submitform'));
			if (1 != $r )
			{
				showMsg($r,'error');								
			}
		}
	}// end for loop
	$back_url = '?'.$_SERVER['QUERY_STRING'];
	if (isset($r) && $r)
	{
		showMsg('核定等第成功.','success',$back_url);
	}else{
		showMsg('沒有任何資料送出.','warning',$back_url);
	}// end if
}// end if			 

//pr($_GET);
/**
    [scriptname] => pa_emp_list
    [pa_period_seqno] => 8
    [group_seqno] => HR/AD
    [pa_level_seqno] => 8
    [who] => mgr1
    [std_seqno] => 3

	[scriptname] => pa_emp_list
    [pa_period_seqno] => 8
    [pa_period_desc] => 2009年上半年考績
    [group_seqno] => HR/AD
    [group_desc] => HR/AD
    [std_master_seqno] => 3
    [std_master_desc] => 非主管級
    [whoami] => mgr1
*/
//根據哪一階來查詢(emp/mgr1/mgr2/mgr3)
$count_by_who = isset($_GET['whoami']) && !empty($_GET['whoami']) ?
				$_GET['whoami'] : null;

//根據哪一群組來查詢
$group_no = isset($_GET['group_seqno']) && !empty($_GET['group_seqno']) ?
			$_GET['group_seqno'] : null;				 

//根據哪一評分標準來查詢
$std_master_seqno = isset($_GET['std_master_seqno']) && !empty($_GET['std_master_seqno']) ?
				  $_GET['std_master_seqno'] : null;
				
//根據哪一等級來查詢				
$pa_level_seqno = isset($_GET['pa_level_seqno']) && !empty($_GET['pa_level_seqno']) ?
				  $_GET['pa_level_seqno'] : null;

/* remark by dennis 20091119 for 分页
$emp_list = $PA->getPAEmpList($_GET['pa_period_seqno'],
	                          $GLOBALS['config']['default_lang'],
	                          $count_by_who,
	                          $group_no,
	                          $std_master_seqno,
	                          $pa_level_seqno);
*/
$pageindex = isset($_GET['pageIndex']) && (int)$_GET['pageIndex']>0 ? $_GET['pageIndex']:1;
$emp_list = $PA->getPAEmpList($_GET['pa_period_seqno'],
	                          $GLOBALS['config']['default_lang'],
	                          $count_by_who,
	                          $group_no,
	                          $std_master_seqno,
	                          $pa_level_seqno,
	                          $pageindex);
						                          
$cnt = count($emp_list);
if ($cnt>0)
{
	$show_mgr3_submit_btn = false;
	for($i=0; $i<$cnt; $i++)
	{
		$whoami = !empty($count_by_who) ? $count_by_who : $emp_list[$i]['WHOAMI'];
		
		// 如果核等栏位有值,(1,2,3/2,3阶主管一样时程式自动写的值 )
		if ($emp_list[$i]['PA_SCORE']       != '' && 
		    $_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR3_EMP_SEQNO'])
		{
			$whoami = $emp_list[$i]['WHOAMI'] = 'mgr3';
		}// end if
		
		$whoami = strtoupper($whoami);
		$mgr1_can_pa  = $PA->isCanPA('mgr1',
									 $emp_list[$i]['MGR1_BEGIN_DATE'],
									 $emp_list[$i]['MGR1_END_DATE'],
									 $emp_list[$i]['FORM_STATUS']);
		$mgr2_can_pa  = $PA->isCanPA('mgr2',
									 $emp_list[$i]['MGR2_BEGIN_DATE'],
									 $emp_list[$i]['MGR2_END_DATE'],
									 $emp_list[$i]['FORM_STATUS']);
		$mgr3_can_pa  = $PA->isCanPA('mgr3',
									 $emp_list[$i]['MGR3_BEGIN_DATE'],
									 $emp_list[$i]['MGR3_END_DATE'],
									 $emp_list[$i]['FORM_STATUS']);

		//echo 'MGR1 Can PA->'.$mgr1_can_pa.'<br/>';
		//echo 'MGR2 Can PA->'.$mgr2_can_pa.'<br/>';
		//echo 'MGR3 Can PA->'.$mgr3_can_pa.'<br/>';
		
    	$emp_list[$i]['WHOAMI'] = strtolower($whoami);
    	$emp_list[$i]['CAN_PA'] = false;
		// 第1/2/3 阶主管是同一人
		if ($_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR1_EMP_SEQNO'] &&
		    $_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR2_EMP_SEQNO'] &&
		    $_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR3_EMP_SEQNO']){
		    if ($mgr1_can_pa){
		    	$emp_list[$i]['MGR1']   = true;
		    	$emp_list[$i]['WHOAMI'] = 'mgr1';
		    	$emp_list[$i]['CAN_PA'] = true;
		    }elseif($mgr2_can_pa){
		    	$emp_list[$i]['MGR2']   = true;
		    	$emp_list[$i]['WHOAMI'] = 'mgr2';
		    	$emp_list[$i]['CAN_PA'] = true;
		    }elseif($mgr3_can_pa){
		    	$emp_list[$i]['MGR3']   = true;
		    	$emp_list[$i]['WHOAMI'] = 'mgr3';
		    	$emp_list[$i]['CAN_PA'] = true;
		    }
		    // 1,2 阶主管是同一人
		}elseif($_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR1_EMP_SEQNO'] &&
		        $_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR2_EMP_SEQNO'])
        {
        	if($mgr1_can_pa){
		    	$emp_list[$i]['MGR1']   = true;
		    	$emp_list[$i]['WHOAMI'] = 'mgr1';
		    	$emp_list[$i]['CAN_PA'] = true;
		    }elseif($mgr2_can_pa){
		    	$emp_list[$i]['MGR2']   = true;
		    	$emp_list[$i]['WHOAMI'] = 'mgr2';
		    	$emp_list[$i]['CAN_PA'] = true;
		    }// end if
		    // 2,3 阶主管是同一人
        }elseif($_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR2_EMP_SEQNO'] &&
		        $_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR3_EMP_SEQNO']){
		    if($mgr2_can_pa){
		    	$emp_list[$i]['MGR2']   = true;
		    	$emp_list[$i]['WHOAMI'] = 'mgr2';
		    	$emp_list[$i]['CAN_PA'] = true;
		    }elseif($mgr3_can_pa){
		    	$emp_list[$i]['MGR3']   = true;
		    	$emp_list[$i]['WHOAMI'] = 'mgr3';
		    	$emp_list[$i]['CAN_PA'] = true;
		    }// end if
		}else{
			$emp_list[$i]['CAN_PA'] = $whoami!='0' ? 
									  ${strtolower($whoami).'_can_pa'} : false;
			$emp_list[$i][strtoupper($whoami)] = $emp_list[$i]['CAN_PA'];
		}// end if
		//echo $emp_list[$i]['CAN_PA'].'<br/>';
		//echo $whoami.' -- '.$_SESSION['user']['emp_seq_no']. '=='. $emp_list[$i]['MGR3_EMP_SEQNO'];
		// 如果当前使用者是核定主管且已经可以核定, summary 最后一次评定的成绩为预设核定成绩
		// 逻辑:从后面往前面找,先复评->初评->自评 找到有值就 return
		if ($_SESSION['user']['emp_seq_no'] == $emp_list[$i]['MGR3_EMP_SEQNO'] && 
			$emp_list[$i]['CAN_PA'])
		{
			$emp_list[$i]['MGR3_DEF_SCORE'] = '';
			if(isset($emp_list[$i]['MGR3']) && $emp_list[$i]['MGR3']) $show_mgr3_submit_btn = true;
			if ($emp_list[$i]['MGR2_SCORE'] != '')
			{
				$emp_list[$i]['MGR3_DEF_SCORE'] = $emp_list[$i]['MGR2_SCORE'];
				$emp_list[$i]['MGR3_DEF_RANK'] = $emp_list[$i]['MGR2_RANK'];
			}else if($emp_list[$i]['MGR2_SCORE'] == '' && $emp_list[$i]['MGR1_SCORE'] != ''){
				$emp_list[$i]['MGR3_DEF_SCORE'] = $emp_list[$i]['MGR1_SCORE'];
				$emp_list[$i]['MGR3_DEF_RANK'] = $emp_list[$i]['MGR1_RANK'];
			}else {
				$emp_list[$i]['MGR3_DEF_SCORE'] = $emp_list[$i]['EMP_SCORE'];
				$emp_list[$i]['MGR3_DEF_RANK'] = $emp_list[$i]['EMP_RANK'];
			}// end if 
			//break; //  update by dennis 2009-03-01
		}// end if
	}// end for loop
	$g_tpl->assign('show_mgr3_submit_btn',$show_mgr3_submit_btn);
	//pr($emp_list);
	$g_parser->ParseTable('pa_emp_list',$emp_list);	
	// add by dennis 20091119 for 分页
	$g_tpl->assign('pager_toolbar',$PA->getPagerToolbar());					  
}// end if
