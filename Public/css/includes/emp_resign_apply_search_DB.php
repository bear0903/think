<?php
/*************************************************************\
 *  Created By Gracie at 2009/07/06
 *  Description:
 *     忘刷申请申请查询
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if

require_once 'AresTrans.class.php';
$Resign = new AresTrans ( $_SESSION ['user'] ['company_id'],
						   $_SESSION ['user'] ['emp_seq_no'] );

$g_parser->ParseSelect ('resign_reason_list',$Resign->GetResignReason(),'');	
$g_parser->ParseSelect ('out_type_list', 
							$Resign->getListMultiLang('ESNA025',
													  'OUT_TYPE',
													  $GLOBALS ['config'] ['default_lang']),
							'');  
$g_parser->ParseSelect ( 'flow_status_list', 
						$Resign->getListMultiLang('ESNA013',
												  'WORK_FLOW_STAUS',
												   $GLOBALS ['config'] ['default_lang']),'');

												   
// who am i(得到当前查询人的身份)
$whoami = isset($whoami) && !empty($whoami) ? $whoami : 'myself';
// 当前使用者或助理申请过的 flow 中的假别清单
if ($whoami == AresTrans::ASSISTANT)
{
	$g_parser->ParseSelect('dept_list',$Resign->GetDeptName($_SESSION['user']['user_seq_no']),'');
}// end if
else if($whoami == AresTrans::ADMIN)
{
	$g_parser->ParseSelect('dept_list',$Resign->getWfDept(),'');
}// end if
// 共用模版，这里判断是不是管理员，给template 中的变量赋值
$g_tpl->assign('whoami',$whoami);												   
// 提交或是删除暂存的人事异动申请

if (isset ( $_GET ['action'] ) && 
	!empty ( $_GET ['action'] ) && 
	isset ( $_GET ['resign_flowseqno'] ) &&
	!empty ( $_GET ['resign_flowseqno'] )) {
	$result = '';
	$msg = '';
	$msg_type = 'information';
	
	switch (strtolower ( $_GET ['action'] ))
	{
		case 'submit':
			$result = $Resign->SubmitResignForm( $_SESSION ['user'] ['user_seq_no'], $_GET ['resign_flowseqno'] );
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		case 'delete':
			$result = $Resign->DeleteWorkflowApply($_GET ['resign_flowseqno'], 'resign');
			if (1 == $result)
			{
				$msg = 'Resign Apply Form :'.$_GET ['resign_flowseqno'].' was deleted successfully.';
				$msg_type = 'success';
			}else{
				$msg = 'Resign apply form not delete. please try again later.';
				$msg_type = 'error';
			}// end if
			break;
		case 'cancelflow': // admin cancle (作废)
			$result = $Resign->CancelWorkflow($_GET ['resign_flowseqno'],$_GET ['apply_type'],$_SESSION['user']['user_seq_no'],$_GET['cancel_comment']);
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		default:break;
	}// end switch
	showMsg($msg,$msg_type,'?scriptname='.$_GET['scriptname']);
}// end if												   

if ((isset ( $_POST ['submit'] ) && 
	!empty ( $_POST ['submit'] )) ||
	(isset($_GET ['pageIndex']) && 
	$_GET ['pageIndex']>0)      ||
	 isset($_GET['flowstatus']))
{
	$wherecond = '';
	if (isset ( $_POST ['start_dept'] ) && 
	  ! empty ( $_POST ['start_dept'] ) && 
	  isset ( $_POST ['end_dept'] ) &&  
	  ! empty ( $_POST ['end_dept'] )) {
		$wherecond .= ' and b.seg_segment_no_department between \'' . $_POST ['start_dept'] . '\' and \'' . $_POST ['end_dept'] . '\'';
	}
	if (isset ( $_POST ['start_dept'] ) && 
	  ! empty ( $_POST ['start_dept'] ) && 
	  empty ( $_POST ['end_dept'] )) {
		$wherecond .= ' and b.seg_segment_no_department = \'' . $_POST ['start_dept'] . '\'';
	}// end if
	if (empty ( $_POST ['start_dept'] ) && 
	  isset ( $_POST ['end_dept']) &&  
	  ! empty ( $_POST ['end_dept'] )) {
		$wherecond .= ' and b.seg_segment_no_department = \'' . $_POST ['end_dept'] . '\'';
	}// end if
	
	if (isset ( $_POST ['start_emp'] ) && 
	  ! empty ( $_POST ['start_emp'] ) && 
	  isset ( $_POST ['end_emp']) &&  
	  ! empty ( $_POST ['end_emp'] )) {
		$wherecond .= ' and  b.id_no_sz between \'' . $_POST ['start_emp'] . '\' and \'' . $_POST ['end_emp'] . '\' ';
	}// end if
	if (isset ( $_POST ['start_emp'] ) && 
	  ! empty ( $_POST ['start_emp'] ) && 
	  empty ( $_POST ['end_emp'] )) {
		$wherecond .= ' and  b.id_no_sz = \'' . $_POST ['start_emp'] . '\'';
	}// end if
	if (empty ( $_POST ['start_emp'] ) && 
	  isset ( $_POST ['start_emp']) &&  
	  ! empty ( $_POST ['end_emp'] )) {
		$wherecond .= ' and b.id_no_sz = \'' . $_POST ['end_emp'] . '\'';
	}// end if
	
	if (isset ( $_POST ['resign_date'] ) && 
	  ! empty ( $_POST ['resign_date'] )) {
		$wherecond .= ' and a.out_date = \'' . $_POST ['resign_date'] . '\'';
	  }	

	if (isset ( $_POST ['resign_reason'] ) && 
	  ! empty ( $_POST ['resign_reason'])) {
		$wherecond .= ' and a.reason = \'' . $_POST ['resign_reason'] . '\'';
	  }// end if

	if (isset ( $_POST ['out_type'] ) && ! empty ( $_POST ['out_type'] )) {
		$wherecond .= ' and a.out_type = \'' . $_POST ['out_type'] . '\'';
	}// end if

	if (isset ( $_POST ['db_flow_status'] ) && ! empty ( $_POST ['db_flow_status'] )) {
		$wherecond .= ' and a.status = \'' . $_POST ['db_flow_status'] . '\'';
	}// end if
	

	// 从流程管理首页或是助理桌面首页，有流程状态条件
	if(!empty($_GET['flowstatus']))
	{
		$wherecond .=  ' and a.status =\''.$_GET['flowstatus'].'\'';
	}// end if
	
	$totalrows = $Resign->getResignApply($wherecond,$whoami,true);
	
	//echo $totalrows.'<br>';
	
	if ($totalrows > 0) {
		require_once 'GridView/Data_Paging.class.php';
		$pagesize = 10;
		$pageIndex = isset ($_GET ['pageIndex'] ) ? $_GET ['pageIndex'] : 1;
		// 重置 pageIndex, 比如开始查的资料有5 页，点到第5页后又下了条件，结查查询出来的
		// 的资料只有 1 页了，因为 url 上 pageIndex 还是  5, 不会显示资料，所以这里重置
		$pageIndex = $pageIndex>ceil($totalrows/$pagesize) ? 1: $pageIndex;
		$Paging = new Data_Paging ( array ('total_rows' => $totalrows, 'page_size' => $pagesize));
		$Paging->openAjaxMode ( 'gotopage' );
		$g_tpl->assign ( 'pagingbar', $Paging->outputToolbar (2) );
		$g_parser->ParseTable ('resign_workflow_list', 
								$Resign->getResignApply ($wherecond,$whoami,false,$pagesize,$pagesize*($pageIndex-1)));
	} // end 分页	
}

