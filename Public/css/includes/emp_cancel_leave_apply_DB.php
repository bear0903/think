<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 * 	 请假申请单查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_cancel_leave_apply_DB.php $
 *  $Id: emp_cancel_leave_apply_DB.php 3338 2012-04-09 09:28:03Z dennis $
 *  $Rev: 3338 $ 
 *  $Date: 2012-04-09 17:28:03 +0800 (周一, 09 四月 2012) $
 *  $LastChangedDate: 2012-04-09 17:28:03 +0800 (周一, 09 四月 2012) $
 ****************************************************************************/
if (! defined('DOCROOT')) {
	die('Attack Error.');
} // end if

require_once 'AresAttend.class.php';
$Attend = new AresAttend($_SESSION['user']['company_id'], 
						   $_SESSION['user']['emp_seq_no']);

						   //get workflow status from multi-language setting
$g_parser->ParseSelect('flow_status_list', 
						$Attend->getListMultiLang('ESNA013',
												  'WORK_FLOW_STAUS',
												   $GLOBALS['config']['default_lang']),
						'');
												   
// who am i(得到当前查询人的身份)
$whoami = isset($whoami) && !empty($whoami) ? $whoami : 'myself';
// 当前使用者或助理申请过的 flow 中的假别清单
if ($whoami == AresAttend::ASSISTANT)
{
	$g_parser->ParseSelect('dept_list',$Attend->GetDeptName($_SESSION['user']['user_seq_no']),'');
}// end if
else if($whoami == AresAttend::ADMIN)
{
	$g_parser->ParseSelect('dept_list',$Attend->getWfDept(),'');
}// end if
// 共用模版，这里判断是不是管理员，给template 中的变量赋值
$g_tpl->assign('whoami',$whoami);
$g_parser->ParseSelect ('leave_name_list', $Attend->GetWfLeaveName($_SESSION['user']['user_seq_no'],$whoami),'');

//处理查询
if ((isset($_POST['submit'])  && 
	!empty($_POST['submit'])) ||
	(isset($_GET['pageIndex'])&& 
	$_GET['pageIndex']>0)     ||
	isset($_GET['flowstatus']))
{
	// 组合查询条件
	$wherecond = '';
	
	// 特殊处理后来加上的部门起讫条件
	if (isset($_POST['begin_date']) && 
	  ! empty($_POST['begin_date']) && 
	  isset($_POST['end_date']) &&  
	  ! empty($_POST['end_date'])) {
		$wherecond .= ' ha.cday between \'' . $_POST['begin_date'] . '\' and \'' . $_POST['end_date'] . '\' and';
	}// end if
	if (isset($_POST['begin_date']) && 
	  ! empty($_POST['begin_date']) && 
	  empty($_POST['end_date'])) {
		$wherecond .= ' ha.cday = \'' . $_POST['begin_date'] . '\' and';
	}// end if
	if (empty($_POST['begin_date']) && 
	  isset($_POST['end_date']) &&  
	  ! empty($_POST['end_date'])) {
		$wherecond .= ' ha.cday = \'' . $_POST['end_date'] . '\' and';
	}// end if
	
	// 特殊处理后来加上的员工起讫条件 add by dennis 2009-06-23
	if (isset($_POST['absence_id1']) && 
	  ! empty($_POST['absence_id1']) && 
	  isset($_POST['absence_id2']) &&  
	  ! empty($_POST['absence_id2'])) {
		$wherecond .= ' ha.reason between \'' . $_POST['absence_id1'] . '\' and \'' . $_POST['absence_id2'] . '\' and';
	}// end if
	if (isset($_POST['absence_id1']) && 
	  ! empty($_POST['absence_id1']) && 
	  empty($_POST['absence_id2'])) {
		$wherecond .= ' ha.reason = \'' . $_POST['absence_id1'] . '\' and';
	}// end if
	if (empty($_POST['absence_id1']) && 
	  isset($_POST['absence_id1']) &&  
	  ! empty($_POST['absence_id2'])) {
		$wherecond .= ' ha.reason = \'' . $_POST['absence_id2'] . '\' and';
	}// end if
	
	// 从流程管理首页或是助理桌面首页，有流程状态条件
	if(!empty($_GET['flowstatus']))
	{
		$wherecond .=  ' a.flow_status =\''.$_GET['flowstatus'].'\' and';
	}// end if
	$wherecond = !empty($wherecond) ? ' and '.substr($wherecond,0,-4) : '';
	//echo $wherecond.'<br/>';
	$totalrows = $Attend->getHR_ABSENCE($wherecond,$whoami,true);
	//echo 'total rows-> '.$totalrows.'<br/>';
	if ($totalrows > 0) {
		require_once 'GridView/Data_Paging.class.php';
		$pagesize = 10;
		$pageIndex = isset ($_GET['pageIndex']) ? $_GET['pageIndex'] : 1;
		// 重置 pageIndex, 比如开始查的资料有5 页，点到第5页后又下了条件，结查查询出来的
		// 的资料只有 1 页了，因为 url 上 pageIndex 还是  5, 不会显示资料，所以这里重置
		$pageIndex = $pageIndex>ceil($totalrows/$pagesize) ? 1: $pageIndex;
		$Paging = new Data_Paging(array ('total_rows' => $totalrows, 'page_size' => $pagesize));
		$Paging->openAjaxMode('gotopage');
		$g_tpl->assign('pagingbar', $Paging->outputToolbar(2));
		$g_parser->ParseTable ('leave_list', 
								$Attend->getHR_ABSENCE($wherecond,$whoami,false,$pagesize,$pagesize*($pageIndex-1)));
	} // end 分页
}// end if

// 提交或是删除暂存的请假申请
if (isset($_GET['action']) && 
	!empty($_GET['action']) && 
	isset($_GET['leave_seqno']) && 
	!empty($_GET['leave_seqno'])) {
	$result = '';
	$msg = '';
	$msg_type = 'information';
	switch (strtolower($_GET['action']))
	{
		case 'delete':  //($user_seqno, $postarray, $emp_seqno = NULL)

			$result = $Attend->CancelLeaveofAbsence1($_SESSION['user']['user_seq_no'],
			                                         $_GET['leave_seqno']);  
            //pr($result);
			if ($result['is_success'] == 'Y') {
				showMsg($result['msg'], 'success','?scriptname='.$_GET['scriptname']);
			} else {
				showMsg($result['msg'],'error','?scriptname='.$_GET['scriptname']);
			}// end if        
			break;
		default:break;
	}// end switch
	showMsg($msg,$msg_type);
}// end if

