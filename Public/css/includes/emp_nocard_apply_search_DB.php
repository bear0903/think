<?php
/*************************************************************\
 *  Created By Gracie at 2009/07/06
 *  Description:
 *     忘刷申请申请查询
 ****************************************************************************/
if(! defined('DOCROOT')) {
	die('Attack Error.');
}// end if

require_once 'AresTrans.class.php';
$Nocard = new AresTrans($_SESSION['user']['company_id'],
						$_SESSION['user']['emp_seq_no']);

$g_parser->ParseSelect('nocard_reason_list',$Nocard->GetNocardReason(),'');	
$g_parser->ParseSelect('shifttype_list',    $Nocard->GetShifttype(),'');	
$g_parser->ParseSelect('flow_status_list', 
						$Nocard->getListMultiLang('ESNA013',
												  'WORK_FLOW_STAUS',
												   $GLOBALS['config']['default_lang']),'');

												   
// who am i(得到当前查询人的身份)
$whoami = isset($whoami) && !empty($whoami) ? $whoami : 'myself';
// 当前使用者或助理申请过的 flow 中的假别清单
if($whoami == AresTrans::ASSISTANT)
{
	$g_parser->ParseSelect('dept_list',$Nocard->GetDeptName($_SESSION['user']['user_seq_no']),'');
}// end if
else if($whoami == AresTrans::ADMIN)
{
	$g_parser->ParseSelect('dept_list',$Nocard->getWfDept(),'');
}// end if
// 共用模版，这里判断是不是管理员，给template 中的变量赋值
$g_tpl->assign('whoami',$whoami);
if(isset($_GET['action']) && 
	!empty($_GET['action']) && 
	isset($_GET['nocard_flowseqno']) &&
	!empty($_GET['nocard_flowseqno'])) {
	$result = '';
	$msg = '';
	$msg_type = 'information';
	
	switch(strtolower($_GET['action']))
	{
		case 'submit':
			$result = $Nocard->SubmitNocardForm( $_SESSION['user']['user_seq_no'], $_GET['nocard_flowseqno']);
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		case 'delete':
			$result = $Nocard->DeleteWorkflowApply($_GET['nocard_flowseqno'], 'nocard');
			if(1 == $result)
			{
				$msg = 'Nocard Apply Form :'.$_GET['nocard_flowseqno'].' was deleted successfully.';
				$msg_type = 'success';
			}else{
				$msg = 'Nocard apply form not delete. please try again later.';
				$msg_type = 'error';
			}// end if
			break;
		case 'cancelflow': // admin cancle(作废)
			$result = $Nocard->CancelWorkflow($_GET['nocard_flowseqno'],$_GET['apply_type'],$_SESSION['user']['user_seq_no'],$_GET['cancel_comment']);
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		default:break;
	}// end switch
	showMsg($msg,$msg_type,'?scriptname='.$_GET['scriptname']);
}// end if												   

if((isset($_POST['submit'])    && 
	!empty($_POST['submit']))  ||
	(isset($_GET['pageIndex']) && 
	$_GET['pageIndex']>0)      ||
	isset($_GET['flowstatus']))
{
	$wherecond = '';
	if(isset($_POST['start_dept']) && 
	  ! empty($_POST['start_dept']) && 
	  isset($_POST['end_dept']) &&  
	  ! empty($_POST['end_dept'])) {
		$wherecond .= ' and b.dept_seq_no between \'' . $_POST['start_dept'] . '\' and \'' . $_POST['end_dept'] . '\'';
	}
	if(isset($_POST['start_dept']) && 
	  ! empty($_POST['start_dept']) && 
	  empty($_POST['end_dept'])) {
		$wherecond .= ' and b.dept_seq_no = \'' . $_POST['start_dept'] . '\'';
	}// end if
	if(empty($_POST['start_dept']) && 
	  isset($_POST['end_dept']) &&  
	  ! empty($_POST['end_dept'])) {
		$wherecond .= ' and b.dept_seq_no = \'' . $_POST['end_dept'] . '\'';
	}// end if
	
	if(isset($_POST['start_emp']) && 
	  ! empty($_POST['start_emp']) && 
	  isset($_POST['end_emp']) &&  
	  ! empty($_POST['end_emp'])) {
		$wherecond .= ' and  b.emp_id between \'' . $_POST['start_emp'] . '\' and \'' . $_POST['end_emp'] . '\' ';
	}// end if
	if(isset($_POST['start_emp']) && 
	  ! empty($_POST['start_emp']) && 
	  empty($_POST['end_emp'])) {
		$wherecond .= ' and  b.emp_id = \'' . $_POST['start_emp'] . '\'';
	}// end if
	if(empty($_POST['start_emp']) && 
	  isset($_POST['start_emp']) &&  
	  ! empty($_POST['end_emp'])) {
		$wherecond .= ' and b.emp_id = \'' . $_POST['end_emp'] . '\'';
	}// end if
	
	if(isset($_POST['nocard_date']) && 
	  ! empty($_POST['nocard_date'])) {
		$wherecond .= ' and trunc(a.recordtime) = \'' . $_POST['nocard_date'] . '\'';
	  }	

	if(isset($_POST['nocard_reason']) && 
	  ! empty($_POST['nocard_reason'])) {
		$wherecond .= ' and a. nocarding_id = \'' . $_POST['nocard_reason'] . '\'';
	  }// end if

	if(isset($_POST['shifttype']) && ! empty($_POST['shifttype'])) {
		$wherecond .= ' and a.shifttype = \'' . $_POST['shifttype'] . '\'';
	}// end if

	if(isset($_POST['db_flow_status']) && ! empty($_POST['db_flow_status'])) {
		$wherecond .= ' and a.status = \'' . $_POST['db_flow_status'] . '\'';
	}// end if
	

	// 从流程管理首页或是助理桌面首页，有流程状态条件
	if(!empty($_GET['flowstatus']))
	{
		$wherecond .=  ' and a.status =\''.$_GET['flowstatus'].'\'';
	}// end if
	
	$totalrows = $Nocard->getNocardApply($wherecond,$whoami,true);
	
	//echo $totalrows.'<br>';
	
	if($totalrows > 0) {
		require_once 'GridView/Data_Paging.class.php';
		$pagesize = 10;
		$pageIndex = isset($_GET['pageIndex']) ? $_GET['pageIndex'] : 1;
		// 重置 pageIndex, 比如开始查的资料有5 页，点到第5页后又下了条件，结查查询出来的
		// 的资料只有 1 页了，因为 url 上 pageIndex 还是  5, 不会显示资料，所以这里重置
		$pageIndex = $pageIndex>ceil($totalrows/$pagesize) ? 1: $pageIndex;
		$Paging = new Data_Paging(array('total_rows' => $totalrows, 'page_size' => $pagesize));
		$Paging->openAjaxMode('gotopage');
		$g_tpl->assign('pagingbar', $Paging->outputToolbar(2));
		$g_parser->ParseTable('nocard_workflow_list', 
							   $Nocard->getNocardApply($wherecond,$whoami,false,$pagesize,$pagesize*($pageIndex-1)));
	} // end 分页	
}

