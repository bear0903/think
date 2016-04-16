<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 * 	 请假申请单查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_cancel_leave_apply_search_DB.php $
 *  $Id: emp_cancel_leave_apply_search_DB.php 3083 2011-03-17 05:54:16Z dennis $
 *  $Rev: 3083 $ 
 *  $Date: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 *  $LastChangedDate: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
} // end if

require_once 'AresAttend.class.php';
$Attend = new AresAttend ( $_SESSION ['user'] ['company_id'], 
						   $_SESSION ['user'] ['emp_seq_no']);

						   //get workflow status from multi-language setting
$g_parser->ParseSelect ( 'flow_status_list', 
						$Attend->getListMultiLang('ESNA013',
												  'WORK_FLOW_STAUS',
												   $GLOBALS ['config'] ['default_lang']),
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
if ((isset ( $_POST ['submit'] ) && 
	!empty ( $_POST ['submit'] )) ||
	(isset($_GET ['pageIndex']) && 
	 $_GET ['pageIndex']>0)       ||
	 isset($_GET['flowstatus']))
{
	//pr($_GET);
	
	// 组合查询条件
	$wherecond = '';
	// 非 DB 栏位,不组合到 where 条件或特殊处理
	$none_db_columns = array('start_dept','end_dept','start_emp','end_emp','submit');
	foreach ($_POST as $columnname=>$value) {
		if (!in_array(strtolower($columnname),$none_db_columns) && !empty($value))
		{
			if ($columnname == 'my_day')
			{
				$wherecond .= ' a.'.$columnname.'=to_date(\''.$value . '\',\'YYYY-MM-DD\') and ';
				continue;
			}// end if
			$wherecond .= ' a.'.$columnname.'=\''.$value . '\' and ';
		}// end if
	}// end foreach	
	
	// 特殊处理后来加上的部门起讫条件
	if (isset ( $_POST ['start_dept'] ) && 
	  ! empty ( $_POST ['start_dept'] ) && 
	  isset ( $_POST ['end_dept']) &&  
	  ! empty ( $_POST ['end_dept'] )) {
		$wherecond .= ' b.dept_seq_no between \'' . $_POST ['start_dept'] . '\' and \'' . $_POST ['end_dept'] . '\' and';
	}// end if
	if (isset ( $_POST ['start_dept'] ) && 
	  ! empty ( $_POST ['start_dept'] ) && 
	  empty ( $_POST ['end_dept'] )) {
		$wherecond .= ' b.dept_seq_no = \'' . $_POST ['start_dept'] . '\' and';
	}// end if
	if (empty ( $_POST ['start_dept'] ) && 
	  isset ( $_POST ['end_dept']) &&  
	  ! empty ( $_POST ['end_dept'] )) {
		$wherecond .= ' b.dept_seq_no = \'' . $_POST ['end_dept'] . '\' and';
	}// end if
	
	// 特殊处理后来加上的员工起讫条件 add by dennis 2009-06-23
	if (isset ( $_POST ['start_emp'] ) && 
	  ! empty ( $_POST ['start_emp'] ) && 
	  isset ( $_POST ['end_emp']) &&  
	  ! empty ( $_POST ['end_emp'] )) {
		$wherecond .= ' b.emp_id between \'' . $_POST ['start_emp'] . '\' and \'' . $_POST ['end_emp'] . '\' and';
	}// end if
	if (isset ( $_POST ['start_emp'] ) && 
	  ! empty ( $_POST ['start_emp'] ) && 
	  empty ( $_POST ['end_emp'] )) {
		$wherecond .= ' b.emp_id = \'' . $_POST ['start_emp'] . '\' and';
	}// end if
	if (empty ( $_POST ['start_emp'] ) && 
	  isset ( $_POST ['start_emp']) &&  
	  ! empty ( $_POST ['end_emp'] )) {
		$wherecond .= ' b.emp_id = \'' . $_POST ['end_emp'] . '\' and';
	}// end if
	
	// 从流程管理首页或是助理桌面首页，有流程状态条件
	if(!empty($_GET['flowstatus']))
	{
		$wherecond .=  ' a.flow_status =\''.$_GET['flowstatus'].'\' and';
	}// end if
	$wherecond = !empty($wherecond) ? ' and '.substr($wherecond,0,-4) : '';
	//echo $wherecond.'<br/>';
	
	$totalrows = $Attend->getCancelLeaveApply($wherecond,$whoami,true);
	//echo 'total rows-> '.$totalrows.'<br/>';
	if ($totalrows > 0) {
		require_once 'GridView/Data_Paging.class.php';
		$pagesize = 10;
		$pageIndex = isset ($_GET ['pageIndex'] ) ? $_GET ['pageIndex'] : 1;
		// 重置 pageIndex, 比如开始查的资料有5 页，点到第5页后又下了条件，结查查询出来的
		// 的资料只有 1 页了，因为 url 上 pageIndex 还是  5, 不会显示资料，所以这里重置
		$pageIndex = $pageIndex>ceil($totalrows/$pagesize) ? 1: $pageIndex;
		$Paging = new Data_Paging ( array ('total_rows' => $totalrows, 'page_size' => $pagesize));
		$Paging->openAjaxMode ( 'gotopage' );
		$g_tpl->assign ( 'pagingbar', $Paging->outputToolbar(2) );
		$g_parser->ParseTable ('cancel_leave_workflow_list', 
								$Attend->getCancelLeaveApply ($wherecond,$whoami,false,$pagesize,$pagesize*($pageIndex-1)));
	} // end 分页
}// end if

// 提交或是删除暂存的请假申请
if (isset ( $_GET ['action'] ) && 
	!empty ( $_GET ['action'] ) && 
	isset ( $_GET ['cancel_leave_flowseqno'] ) && 
	!empty ( $_GET ['cancel_leave_flowseqno'] )) {
	$result = '';
	$msg = '';
	$msg_type = 'information';
	switch (strtolower ( $_GET ['action'] ))
	{
		case 'submit':
			$result = $Attend->SubmitLeaveForm( $_SESSION ['user'] ['user_seq_no'], $_GET ['cancel_leave_flowseqno'] );
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		case 'delete':
			$result = $Attend->DeleteWorkflowApply($_GET ['cancel_leave_flowseqno'], 'cancel_absence');
			if (1 == $result)
			{
				$msg = 'Leave Apply Form :'.$_GET ['cancel_leave_flowseqno'].' was deleted successfully.';
				$msg_type = 'success';
			}else{
				$msg = 'Leave apply form not delete. please try again later.';
				$msg_type = 'error';
			}// end if
			break;
		case 'cancelflow': // admin cancle (作废)
			$result = $Attend->CancelWorkflow($_GET ['cancel_leave_flowseqno'],
											  $_GET ['apply_type'],
											  $_SESSION['user']['user_seq_no'],
											  $_GET['cancel_comment']);
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		default:break;
	}// end switch
	showMsg($msg,$msg_type,'?scriptname='.$_GET['scriptname']);
}// end if

