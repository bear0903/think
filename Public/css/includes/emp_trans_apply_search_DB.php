<?php
/*************************************************************\
 *  Created By Gracie at 2009/07/06
 *  Description:
 *     人事异动申请查询
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if

require_once 'AresTrans.class.php';
$Trans = new AresTrans ( $_SESSION ['user'] ['company_id'],
						   $_SESSION ['user'] ['emp_seq_no'] );
$g_parser->ParseSelect ('new_department_list',$Trans->GetNewdept(),'');	
$g_parser->ParseSelect ('transtype_list',$Trans->GetTranstype(),'');	

$g_parser->ParseSelect ('new_department_list',$Trans->GetNewdept(),'');	
$g_parser->ParseSelect ('new_nb_list',$Trans->GetNewnb(),'');	
$g_parser->ParseSelect ('new_contract_list',$Trans->GetNewcontract(),'');	
$g_parser->ParseSelect ('new_otype_list',$Trans->GetNewotype(),'');	
$g_parser->ParseSelect ('new_title_list',$Trans->GetNewtitle(),'');	
$g_parser->ParseSelect ('new_absence_list',$Trans->GetNewabsence(),'');	
$g_parser->ParseSelect ('new_jobcategory_list',$Trans->GetNewjobcategory(),'');	
$g_parser->ParseSelect ('new_year_list',$Trans->GetNewyear(),'');	
$g_parser->ParseSelect ('new_period_list',$Trans->GetNewperiod(),'');
$g_parser->ParseSelect ('new_job_list',$Trans->GetNewjd(),'');
$g_parser->ParseSelect ('new_costallocation_list',$Trans->GetNewcost(),'');
$g_parser->ParseSelect ('new_tax_list',$Trans->GetNewtax(),'');
$g_parser->ParseSelect ('transtype_list',$Trans->GetTranstype(),'');	
$g_parser->ParseSelect ('new_reason_list',$Trans->GetNewreason(),'');	
$g_parser->ParseSelect ( 'flow_status_list', 
						$Trans->getListMultiLang('ESNA013',
												  'WORK_FLOW_STAUS',
												   $GLOBALS ['config'] ['default_lang']),'');

												   
// who am i(得到当前查询人的身份)
$whoami = isset($whoami) && !empty($whoami) ? $whoami : 'myself';
// 共用模版，这里判断是不是管理员，给template 中的变量赋值
$g_tpl->assign('whoami',$whoami);												   
// 提交或是删除暂存的人事异动申请

if (isset ( $_GET ['action'] ) && 
	!empty ( $_GET ['action'] ) && 
	isset ( $_GET ['trans_flowseqno'] ) &&
	!empty ( $_GET ['trans_flowseqno'] )) {
	$result = '';
	$msg = '';
	$msg_type = 'information';
	
	switch (strtolower ( $_GET ['action'] ))
	{
		case 'submit':
			$result = $Trans->SubmitTransForm( $_SESSION ['user'] ['user_seq_no'], $_GET ['trans_flowseqno'] );
			$msg = $result['msg'];
			$msg_type = $result['is_success'] == 'Y'? 'success':'error';
			break;
		case 'delete':
			$result = $Trans->DeleteWorkflowApply($_GET ['trans_flowseqno'], 'trans');
			if (1 == $result)
			{
				$msg = 'Trans Apply Form :'.$_GET ['trans_flowseqno'].' was deleted successfully.';
				$msg_type = 'success';
			}else{
				$msg = 'Trans apply form not delete. please try again later.';
				$msg_type = 'error';
			}// end if
			break;
		case 'cancelflow': // admin cancle (作废)
			$result = $Trans->CancelWorkflow($_GET ['trans_flowseqno'],$_GET ['apply_type'],$_SESSION['user']['user_seq_no'],$_GET['cancel_comment']);
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
	if (isset ( $_POST ['trans_date'] ) && 
	  ! empty ( $_POST ['trans_date'] )) {
		$wherecond .= ' and a.validdate = \'' . $_POST ['trans_date'] . '\'';
	  }	

	if (isset ( $_POST ['new_department'] ) && 
	  ! empty ( $_POST ['new_department'])) {
		$wherecond .= ' and b.dept_seq_no = \'' . $_POST ['new_department'] . '\'';
	  }// end if

	if (isset ( $_POST ['trans_type'] ) && ! empty ( $_POST ['trans_type'] )) {
		$wherecond .= ' and a.issuetype = \'' . $_POST ['trans_type'] . '\'';
	}// end if

	if (isset ( $_POST ['db_flow_status'] ) && ! empty ( $_POST ['db_flow_status'] )) {
		$wherecond .= ' and a.status = \'' . $_POST ['db_flow_status'] . '\'';
	}// end if
	

	// 从流程管理首页或是助理桌面首页，有流程状态条件
	if(!empty($_GET['flowstatus']))
	{
		$wherecond .=  ' and a.status =\''.$_GET['flowstatus'].'\'';
	}// end if
	
	$totalrows = $Trans->getTransApply($wherecond,$whoami,true);
	
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
		$g_parser->ParseTable ('trans_workflow_list', 
								$Trans->getTransApply ($wherecond,$whoami,false,$pagesize,$pagesize*($pageIndex-1)));
	} // end 分页	
}

