<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 * 	 请假申请单查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_leave_apply_search_DB.php $
 *  $Id: emp_leave_apply_search_DB.php 3784 2014-07-14 08:45:08Z dennis $
 *  $Rev: 3784 $ 
 *  $Date: 2014-07-14 16:45:08 +0800 (周一, 14 七月 2014) $
 *  $LastChangedDate: 2014-07-14 16:45:08 +0800 (周一, 14 七月 2014) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
} // end if

require_once 'AresAttend.class.php';
$Attend = new AresAttend ( $_SESSION ['user'] ['company_id'], 
						   $_SESSION ['user'] ['emp_seq_no']);

						   //get workflow status from multi-language setting
$g_parser->ParseSelect('flow_status_list', 
					   $Attend->getListMultiLang('ESNA013',
												 'WORK_FLOW_STAUS',
												 $GLOBALS ['config'] ['default_lang']),
						'');
// add by dennis 2010-05-35
$dept_seqno = isset($_GET['dept_seqno']) ? $_GET['dept_seqno'] : '';
// end add

// who am i(得到当前查询人的身份)
$whoami = isset($whoami) && !empty($whoami) ? $whoami : 'myself';

// 当前使用者或助理申请过的 flow 中的假别清单
if ($whoami == AresAttend::ASSISTANT)
{
	$g_parser->ParseSelect('dept_list',$Attend->GetDeptName($_SESSION['user']['user_seq_no']),'s_dept_seqno',$dept_seqno);
}// end if
else if($whoami == AresAttend::ADMIN)
{
	$g_parser->ParseSelect('dept_list',$Attend->getWfDept(),'');
}// end if
// 共用模版，这里判断是不是管理员，给template 中的变量赋值
$g_tpl->assign('whoami',$whoami);
$g_parser->ParseSelect ('leave_name_list', $Attend->GetWfLeaveName($_SESSION['user']['user_seq_no'],$whoami),'');

//处理查询
if ((isset ( $_POST ['submit'] )  && 
	!empty ( $_POST ['submit'] )) ||
	(isset($_GET ['pageIndex'])   && 
	 $_GET ['pageIndex']>0)       ||
	 isset($_GET['flowstatus']))
{
	//pr($_GET);
	// 组合查询条件
	$wherecond = '';
	// add by dennis 2011-04-14
	if (isset ( $_POST ['absence_seq_no'] ) && ! empty ( $_POST ['absence_seq_no'] )) {
		$wherecond .= ' a.absence_seq_no = \'' . $_POST ['absence_seq_no'] . '\' and';
	}// end if
	if (isset ( $_POST ['db_my_day1'] ) && 
	  ! empty ( $_POST ['db_my_day1'] ) && 
	  isset ( $_POST ['db_my_day2'] ) &&  
	  ! empty ( $_POST ['db_my_day2'] )) {
		$wherecond .= ' my_day between \'' . 
		              $_POST ['db_my_day1'] . 
		              '\' and \'' . $_POST ['db_my_day2'] . '\' and';
	}	
	if (isset ( $_POST ['db_my_day1'] ) && 
	  ! empty ( $_POST ['db_my_day1'] ) && 
	  empty ( $_POST ['db_my_day2'] )) {
		$wherecond .= ' my_day = \'' . $_POST ['db_my_day1'] . '\' and';
	}
	
	if (empty ( $_POST ['db_my_day1'] ) && 
	  isset ( $_POST ['db_my_day2'] ) &&  
	  ! empty ( $_POST ['db_my_day2'] )) {
		$wherecond .= ' my_day = \'' . $_POST ['db_my_day2'] . '\' and';
	}
	// 特殊处理后来加上的部门起讫条件
	if (isset ( $_POST ['start_dept'] ) && 
	  ! empty ( $_POST ['start_dept'] ) && 
	  isset ( $_POST ['end_dept']) &&  
	  ! empty ( $_POST ['end_dept'] )) {
		//$wherecond .= ' b.dept_seq_no between \'' . $_POST ['start_dept'] . '\' and \'' . $_POST ['end_dept'] . '\' and';
		// change by dennis 2013-03-12 for improve performance 
		$wherecond .= ' c.segment_no between \'' . $_POST ['start_dept'] . '\' and \'' . $_POST ['end_dept'] . '\' and';
	}// end if
	if (isset ( $_POST ['start_dept'] ) && 
	  ! empty ( $_POST ['start_dept'] ) && 
	  empty ( $_POST ['end_dept'] )) {
		//$wherecond .= ' b.dept_seq_no = \'' . $_POST ['start_dept'] . '\' and';
		$wherecond .= ' c.segment_no = \'' . $_POST ['start_dept'] . '\' and';
	}// end if
	if (empty ( $_POST ['start_dept'] ) && 
	  isset ( $_POST ['end_dept']) &&  
	  ! empty ( $_POST ['end_dept'] )) {
		//$wherecond .= ' b.dept_seq_no = \'' . $_POST ['end_dept'] . '\' and';
		$wherecond .= ' c.segment_no = \'' . $_POST ['end_dept'] . '\' and';
	}// end if
	
	// 特殊处理后来加上的员工起讫条件 add by dennis 2009-06-23
	if (isset ( $_POST ['start_emp'] ) && 
	  ! empty ( $_POST ['start_emp'] ) && 
	  isset ( $_POST ['end_emp']) &&  
	  ! empty ( $_POST ['end_emp'] )) {
		//$wherecond .= ' b.emp_id between \'' . $_POST ['start_emp'] . '\' and \'' . $_POST ['end_emp'] . '\' and';
		$wherecond .= ' b.id_no_sz between \'' . $_POST ['start_emp'] . '\' and \'' . $_POST ['end_emp'] . '\' and';
	}// end if
	if (isset ( $_POST ['start_emp'] ) && 
	  ! empty ( $_POST ['start_emp'] ) && 
	  empty ( $_POST ['end_emp'] )) {
		//$wherecond .= ' b.emp_id = \'' . $_POST ['start_emp'] . '\' and';
		$wherecond .= ' b.id_no_sz = \'' . $_POST ['start_emp'] . '\' and';
	}// end if
	if (empty ( $_POST ['start_emp'] ) && 
	  isset ( $_POST ['start_emp']) &&  
	  ! empty ( $_POST ['end_emp'] )) {
		//$wherecond .= ' b.emp_id = \'' . $_POST ['end_emp'] . '\' and';
		$wherecond .= ' b.id_no_sz = \'' . $_POST ['end_emp'] . '\' and';
	}// end if
	
	if (isset ( $_POST ['flow_status'] ) && ! empty ( $_POST ['flow_status'] )) {
		$wherecond .= ' a.flow_status = \'' . $_POST ['flow_status'] . '\' and';
	}// end if
	
	// 从流程管理首页或是助理桌面首页，有流程状态条件
	// add by dennis 2010-05-35
	if(!empty($_GET['dept_seqno']))
	{
		//$wherecond .=  ' b.dept_seq_no =\''.$_GET['dept_seqno'].'\' and';
		$wherecond .=  ' b.seg_segment_no_department =\''.$_GET['dept_seqno'].'\' and';
	}// end if
	// end add
	
	// 从流程管理首页或是助理桌面首页，有流程状态条件
	if(!empty($_GET['flowstatus']))
	{
		$wherecond .=  ' a.flow_status =\''.$_GET['flowstatus'].'\' and';
	}
	// remark by dennis 2014/07/14, 加这个条件助理无法查到已核准的历史申请单
	/*else{
	   if ($whoami == AresAttend::ASSISTANT){
	        $wherecond .= " a.flow_status < '02' and";
	    }
	}// end if
	*/
	$wherecond = !empty($wherecond) ? ' and '.substr($wherecond,0,-4) : '';
	//echo $wherecond.'<br/>';
	
	$totalrows = $Attend->getLeaveApply($wherecond,$whoami,true);
	//echo 'total rows-> '.$totalrows.'<br/>';
	if ($totalrows > 0) {
		require_once 'GridView/Data_Paging.class.php';
		$pagesize  = 200;
		$pageIndex = isset ($_GET ['pageIndex'] ) ? $_GET ['pageIndex'] : 1;
		// 重置 pageIndex, 比如开始查的资料有5 页，点到第5页后又下了条件，结查查询出来的
		// 的资料只有 1 页了，因为 url 上 pageIndex 还是  5, 不会显示资料，所以这里重置
		$pageIndex = $pageIndex>ceil($totalrows/$pagesize) ? 1: $pageIndex;
		$Paging = new Data_Paging ( array ('total_rows' => $totalrows, 'page_size' => $pagesize));
		$Paging->openAjaxMode ( 'gotopage' );
		$g_tpl->assign ( 'pagingbar', $Paging->outputToolbar(2) );
		$g_parser->ParseTable ('leave_workflow_list', 
								$Attend->getLeaveApply ($wherecond,$whoami,false,$pagesize,$pagesize*($pageIndex-1)));
	} // end 分页
}// end if

// 提交或是删除暂存的请假申请
if (isset ($_GET ['action'] ) && 
	!empty ($_GET ['action'] ) && 
	isset ($_GET ['leave_flowseqno'] ) && 
	!empty ($_GET ['leave_flowseqno'] )) {
	$result   = '';
	$msg      = '';
	$msg_type = 'information';
	switch (strtolower ($_GET['action']))
	{
		case 'submit':
			$result = $Attend->SubmitLeaveForm( $_SESSION ['user'] ['user_seq_no'], $_GET ['leave_flowseqno'] );
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		case 'delete':
			$result = $Attend->DeleteWorkflowApply($_GET ['leave_flowseqno'], 'absence');
			if (1 == $result)
			{
				$msg = '單號:'.$_GET ['leave_flowseqno'].' 的申請單刪除成功.';
				$msg_type = 'success';
			}else{
				$msg = $result;
				$msg_type = 'error';
			}// end if
			break;
		case 'cancelflow': // admin cancle (作废)
			$result = $Attend->CancelWorkflow($_GET ['leave_flowseqno'],
											  $_GET ['apply_type'],
											  $_SESSION['user']['user_seq_no'],
											  $_GET['cancel_comment']);exit;
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		default:break;
	}// end switch
	showMsg($msg,$msg_type,'?scriptname='.$_GET['scriptname']);
}// end if


// 处理 Batch Delete
// add by dennis 2010-06-11
if(isset($_POST['doaction']) && $_POST['doaction'] == 'batchdel')
{
	if (isset($_POST['leave_flowseqno']))
	{
		$result = $Attend->BatchDeleteWorkflowApply($_POST['leave_flowseqno'], 'absence');
		if ((int)$result > 0)
		{
			showMsg('成功删除'.$result.'笔请假申请资料.','information','?scriptname='.$_GET['scriptname']);
		}else{
			showMsg('删除请假资料失败.<br/>原因:'.$result,'error','?scriptname='.$_GET['scriptname']);
		}
	}else{
		showMsg('无任何资料删除','information','?scriptname='.$_GET['scriptname']);
		exit;
	}
}
