<?php
/**********************************************************************\
 * (C)  2008 ARES CHINA All Rights Reserved.  http://www.areschina.com
 *
 *  Desc
 *   维护接班人资料
 *  Create By: Dennis  Create Date: 2008-11-19 04:30:55
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_add_successor_DB.php $
 *  $Id: pa_add_successor_DB.php 2256 2009-11-09 07:16:29Z dennis $
 *  $Date: 2009-11-09 15:16:29 +0800 (周一, 09 十一月 2009) $ 
 *  $Author: dennis $
 *  $Revision: 2256 $  
 *  $LastChangedDate: 2009-11-09 15:16:29 +0800 (周一, 09 十一月 2009) $
 \ **********************************************************************/ 
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
// back 保留表单资料
header("Cache-control: private");
session_cache_limiter('private');

require_once 'AresPA.class.php';
if (isset($_GET['pa_period_seqno']) && !empty($_GET['pa_period_seqno']))
{
	$PA = new AresPA($_SESSION['user']['company_id'],$_SESSION['user']['emp_seq_no']);
	$g_parser->ParseSelect('approve_time_list',$PA->getApproveTimeList($GLOBALS['config']['default_lang']),'','');	
	$g_parser->ParseSelect('job_represent_list',$PA->getJobRepresentList($GLOBALS['config']['default_lang']),'','');
	$g_parser->ParseSelect('possabilities_list',$PA->getPossabilitiesList($GLOBALS['config']['default_lang']),'','');
}// end if

// ajax call get month list according the year
if (isset ( $_POST ['ajaxcall'] ) && 
    $_POST ['ajaxcall'] == '1'     && 
    isset ( $_POST ['emp_seqno'] ) && 
    !empty ( $_POST ['emp_seqno'] )) {
	$PA = new AresPA($_SESSION['user']['company_id'],$_SESSION['user']['emp_seq_no']);
	echo json_encode($PA->getPAHis($_POST['emp_seqno'],ADODB_FETCH_NUM));
	exit ();
} //end if

$msgs = get_multi_lang($GLOBALS['config']['default_lang'],'ESNB010');
$g_tpl->assign('COMMENT_MSG',$msgs['COMMENT_MSG']);
	
if (isset($_POST['submitform'])   || 
    isset($_POST['submitandadd']) || 
    (isset($_GET['doaction']) && isset($_GET['pa_successor_seq']) ||
    isset($_POST['doaction'])))
{
	$the_action = isset($_POST['pa_successor_key']) && !empty($_POST['pa_successor_key']) ? 
		          'save' :
		          (isset($_POST['doaction']) && !empty($_POST['doaction']) ? 
	                    $_POST['doaction']:
		                $_GET['doaction']);
	              
	
	/**
	 * 检查必须输入栏位
	 *
	 * @param number $gradus        序位
	 * @param string $emp_seqno     员工 psn_id
	 * @param string $approve_time  可接任时间
	 * @param string $job_represent 工作表现
	 * @param string $possable      发展潜力
	 * @param string $merit         优点
	 * @param string $demerit       缺点
	 * @param string $train_empasis 培训重点
	 * @return boolean
	 * @author Dennis 2009-03-02
	 */              
	function checkDataInput($gradus,
							$approve_time,
							$job_represent,
							$possable,
							$merit,
							$demerit,
							$train_empasis)
	{
		global $msgs;
		if (empty($gradus))
		{
			//showMsg('請輸入繼承人序位.','warning');
			showMsg($msgs['001'],'warning');
			return false;
		}
		if (!is_numeric($gradus))
		{
			//showMsg('繼承人序位必須為數字.','warning');
			showMsg($msgs['002'],'warning');
			return false;
		}
		if (empty($approve_time))
		{
			//showMsg('請輸入可繼任時間.','warning');
			showMsg($msgs['003'],'warning');
			return false;
		}
		
		if (empty($job_represent))
		{
			//showMsg('請輸入工作表現.','warning');
			showMsg($msgs['004'],'warning');
			return false;
		}
		if (empty($possable))
		{
			//showMsg('請輸入發展潛力.','warning');
			showMsg($msgs['005'],'warning');
			return false;
		}
		/* remark by dennis 20090604 客户要求不需要
		if (empty($merit))
		{
			//showMsg('請輸入優點.','warning');
			showMsg($msgs['006'],'warning');
			return false;
		}
		if (empty($demerit))
		{
			//showMsg('請輸入缺點.','warning');
			showMsg($msgs['007'],'warning');
			return false;
		}
		if (empty($train_empasis))
		{
			//showMsg('請輸入培育重點.','warning');
			showMsg($msgs['008'],'warning');
			return false;
		}
		*/
		return true;
	}// end checkDataInput()
	
	switch (strtolower($the_action))
	{
		case 'insert':
			$r = 0;
			$j = 0;
			$g_db_sql->BeginTrans();
			for ($i=0; $i<count($_POST['emp_seq_no']);$i++)
			{
				if (!empty($_POST['emp_seq_no'][$i]) && 
				    checkDataInput($_POST['gradus'][$i],
								   $_POST['approve_time'][$i],
								   $_POST['job_represent'][$i],
								   $_POST['possabilitiy'][$i],
								   $_POST['merit'][$i],
								   $_POST['demerit'][$i],
								   $_POST['train_emphasis'][$i]))
				{
					$r = $PA->insertSuccessor($_POST['pa_period_seqno'],
										      $_POST['gradus'][$i],
											  $_POST['emp_seq_no'][$i],
											  $_POST['approve_time'][$i],
											  $_POST['job_represent'][$i],
											  $_POST['possabilitiy'][$i],
											  $_POST['merit'][$i],
											  $_POST['demerit'][$i],
											  $_POST['train_emphasis'][$i],
											  $_POST['the_comments'][$i],
											  $_SESSION['user']['user_name'][$i],
											  'pa_add_successor');
					$j++;
					if (1 !== $r) break;
				}// end if
			}// end for loop
			//$msg = $j>0 ? '' : '沒有填寫任何資料.';
			//$msg = $j>0 ? '' : $msgs['009'];
			$msg = $r;
			$msg_type = 'error';
			$back_url = null;
			switch ($r)
			{
				case '0':
					// 沒有填寫任何資料
					$msg = $msgs['009'];
					$msg_type = 'warn';
				break;
				case '1':
					// 繼承人添加成功
					$msg = $msgs['010'];
					$msg_type = 'success';
					$back_url = '?scriptname=pa_successor_plan';
				break;
				case 'empno_error':
					// 接班人重复
					$msg = $msgs['011'];
					$msg_type = 'error';
					break;
				case 'seqno_error':
					// 接班人序位重复
					$msg = $msgs['017'];
					$msg_type = 'error';
					break;
				default:
					$msg = $r;
					$msg_type = 'error';
				break;
			}// end switch
			if (1 == $r)
			{
				$g_db_sql->CommitTrans(true);   // do commit
				$g_db_sql->CompleteTrans();
			}else{
				$g_db_sql->RollbackTrans();
			}// end if
			showMsg($msg,$msg_type,$back_url);			
			break;
		case 'update': //
			$g_parser->ParseOneRow($PA->selectSuccessor($_GET['pa_successor_seq']));
			break;
		case 'save':
			//pr($_POST);
			$r = $PA->updateSuccessor($_POST['pa_successor_key'],
									  $_POST['gradus'][0],
									  $_POST['emp_seq_no'][0],
									  $_POST['approve_time'][0],
									  $_POST['job_represent'][0],
									  $_POST['possabilitiy'][0],
								 	  $_POST['merit'][0],
									  $_POST['demerit'][0],
									  $_POST['train_emphasis'][0],
									  $_POST['the_comments'][0],
									  $_SESSION['user']['user_name'],
									  'pa_add_successor');
			if($r == '1')
			{
				//showMsg('資料修改成功.','success','?scriptname=pa_successor_plan');
				showMsg($msgs['013'],'success','?scriptname=pa_successor_plan');
			}else{
				//showMsg('資料修改失败.<br/>'.$r,'error','?scriptname=pa_successor_plan');
				showMsg($msgs['014'].'<br/><br/>'.$r,'error','?scriptname=pa_successor_plan');
			}// end if			
		case 'delete':
			$r = $PA->deleteSuccessor($_GET['pa_successor_seq']);
			if($r == 1)
			{
				///showMsg('刪除成功.','success','?scriptname=pa_successor_plan');
				showMsg($msgs['015'],'success','?scriptname=pa_successor_plan');
			}else{
				//showMsg('刪除失敗.<br/>'.$r,'error','?scriptname=pa_successor_plan');
				showMsg($msgs['016'].'<br/><br/>'.$r,'error','?scriptname=pa_successor_plan');
			}// end if
			break;
		default:break;	
	}// end switch
}// end if
	