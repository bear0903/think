<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) Lan Jiangtao 
 *	 
 *	Description:
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/viewflowchart.php $
 *  $Id: viewflowchart.php 3816 2014-08-12 03:17:45Z dennis $
 *  $Rev: 3816 $ 
 *  $Date: 2014-08-12 11:17:45 +0800 (周二, 12 八月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-08-12 11:17:45 +0800 (周二, 12 八月 2014) $
 ****************************************************************************/
$companyid = '';
$applytype = ''; // apply type 'absence', 'overtime'
$flowseqno = ''; // workflow seqno, leave_flowseqno,/overtime_flowseqno
$funcname = ''; // AresAttend class function name
$wherecond = ''; // where condition
$fromemail = false; // 是否从 email 中打开查看 flowchart
$cancel_absence = null; // 销假
// from mail 从 mail 中查看 flowchart
if (empty($_SESSION['user'] ) && !empty ($_GET ['key'] )) {	
	if (! defined ( 'DOCROOT' )) {
	    define ( 'DOCROOT', '..' );
	} // end if
	require_once (DOCROOT . '/conf/config.inc.php');
	require_once 'AresWorkflow.class.php';
	$Workflow = new AresWorkflow();
	//参数说明: false->not batch, true->view flowchart only
	// for decrypt key 
	$result = $Workflow->ParseWorkflowSecretCode ($_GET ['key'], false, true);	
	$companyid = $result ['company_id'];
	$applytype = $result ['apply_type'];
	$flowseqno = $result ['workflow_seqno'];
	$userseqno = $result ['approver_user_seqno'];
	$fromemail = true;
	// 为何要 assign value to $GET,因为 flowchart view 模版中有判断这个变数
	$_GET ['flow_seqno'] = $flowseqno;
	$_GET ['apply_type'] = $applytype;
} elseif(!empty ($_SESSION['user']) &&
         !empty ($_GET ['apply_type']) && 
         !empty ($_GET ['flow_seqno'])) {
    // 从 ESS/MD 某个程式中来查看 flowchart
	$companyid = $_SESSION ['user'] ['company_id'];
	$applytype = $_GET ['apply_type'];
	$flowseqno = $_GET ['flow_seqno'];
	$userseqno = $_SESSION ['user'] ['user_seq_no'];
}elseif(!isset($_SESSION['user']) &&
        !empty($_GET ['apply_type']) && 
        !empty($_GET ['flow_seqno']) &&
        !empty($_GET['companyid'])  &&
        !empty($_GET['userseqno']) ){
    // 从邮件中点批量签核后，打开一个网页，从这个上面link 去查看 flowchart        	
         	$companyid = $_GET['companyid'];
		    $applytype = $_GET ['apply_type'];
		    $flowseqno = $_GET ['flow_seqno'];
		    $userseqno = $_GET['userseqno'];
}// end if

// 处理从  ESS或是 MD 中查看 flowchart
if (! empty ( $companyid ) && 
	! empty ( $applytype ) && 
	! empty ( $flowseqno )) {
	require_once 'AresAttend.class.php';
	require_once 'AresTrans.class.php';
	/* rewirte by dennis 20091116	
	if ($applytype == 'absence') {
		$funcname = 'getLeaveApply';
		$wherecond = ' and a.leave_flow_seqno ='.$flowseqno;
	} else if ($applytype == 'overtime') {
		$funcname = 'getOvertimeApply';
		$wherecond = ' and a.overtime_flow_seqno ='. $flowseqno;
	}
	else if ($applytype == 'trans') {
		$funcname = 'getTransApply';
		$wherecond = ' and a.trans_flow_sz_id ='. $flowseqno;
	} 
	else if ($applytype == 'nocard') {
		$funcname = 'getNocardApply';
		$wherecond = ' and a.nocard_flow_sz_id ='. $flowseqno;
	}
	else if ($applytype == 'resign') {
		$funcname = 'getResignApply';
		$wherecond = ' and a.resign_flow_sz_id ='. $flowseqno;
	}
	else if ($applytype == 'cancel_absence') {
		$funcname = 'GetLeaveWorkflowList';
		$wherecond = ' and a.leave_flow_seqno = '.$flowseqno;
		$cancel_absence = 'cancel_';
	} else {
		showMsg('Undefined Workflow Type :' . $applytype,'error');
	}// end if
	*/
	$WFInstance = new AresAttend ($companyid, $userseqno);
	switch (strtolower($applytype)) {
		case 'absence':
			$funcname = 'getLeaveApply';
			$wherecond = ' and a.leave_flow_seqno ='.$flowseqno;
		break;
		case 'overtime':
			$funcname = 'getOvertimeApply';
			$wherecond = ' and a.overtime_flow_seqno ='. $flowseqno;
		break;
		case 'trans':
			$funcname = 'getTransApply';
			$wherecond = ' and a.trans_flow_sz_id ='. $flowseqno;
			$WFInstance = new AresTrans ($companyid, $userseqno);
		break;
		case 'nocard':
			$funcname = 'getNocardApply';
			$wherecond = ' and a.nocard_flow_sz_id ='. $flowseqno;
			$WFInstance = new AresTrans ($companyid, $userseqno);
		break;
		case 'resign':
			$funcname = 'getResignApply';
			$wherecond = ' and a.resign_flow_sz_id ='. $flowseqno;
			$WFInstance = new AresTrans ($companyid, $userseqno);
		break;
		case 'cancel_absence':
			$funcname = 'getCancelLeaveApply';
			$wherecond = ' and a.leave_flow_seqno = '.$flowseqno;
		break;
		default: // user defined workflow add by dennis 20091116			
			$funcname = 'getUDWFApply';
			$wherecond = ' and a.udwf_%s_flow_sz_id = '.$flowseqno;
		break;
	}// end switch
	
	// User define workflow 必须要传 menu_code 如 esnw201 也即程式代码
	// modify by dennis 20091116
	if (isset($_GET['menu_code']) && !empty($_GET['menu_code']))
	{
		require_once 'AresUserDefineWF.php';
		$WFInstance = new AresUserDefineWF($companyid,$userseqno);
		$record = $WFInstance->$funcname($_GET['menu_code'],
		                                 sprintf($wherecond,
		                                 $_GET['menu_code']),
		                                 $WFInstance->getDetailDefine($_GET['menu_code']),
		                                 false,false,1,0);
	}else{
		$record = $WFInstance->$funcname($wherecond,false,false,1,0);
	}
	
	/* rewrite by dennis 20091116
	if ($applytype == 'trans') {
		$Trans = new AresTrans ($companyid, $userseqno);
		$record = $Trans->{$funcname}($wherecond,false,false,1,0);
	}
    else if ($applytype == 'nocard') {
    	$Nocard = new AresTrans ($companyid, $userseqno);
		$record = $Nocard->{$funcname}($wherecond,false,false,1,0);
    }
	else if ($applytype == 'resign') {
    	$Resign = new AresTrans ($companyid, $userseqno);
		$record = $Resign->{$funcname}($wherecond,false,false,1,0);
    }
	else {
		$Attend = new AresAttend ($companyid, $userseqno);
	    $record = $Attend->{$funcname}($wherecond,false,false,1,0);
	}
	*/
	if (!empty($record) && is_array($record)) {
		require_once 'AresDrawFlowchart.class.php';
		$Flowchart = new AresDrawFlowchart($companyid, $flowseqno, $applytype );
		$g_tpl->assign('workflowchart', $Flowchart->DrawFlowchart(@$_GET['menu_code']));
		$g_parser->ParseOneRow($record[0]);
		if (isset($_GET['menu_code']) && !empty($_GET['menu_code']))
		{
			$g_tpl->assign('udwf_apply_form',$WFInstance->getApplyForm($_GET['menu_code'],$record[0]));
		}
		// from email view 
		if ($fromemail) {
			// add by dennis 2010-11-29 当从邮件中查看 flowchart 显示多语资料
			$g_parser->ParseMultiLang('ESNA013',$GLOBALS['config']['default_lang']);
			$g_tpl->assign($GLOBALS['config']['dir_array']);
			$g_tpl->display('PageHeader.html' );
			$g_tpl->display('view_flowchart.html' );
			$g_tpl->display('PageFooter.html' );
		}// end if
	} else {
		showMsg ( 'Error:Workflow Not Exists, Please Contact Your System Administrator.', 'error' );
	}// end if
} else {
	showMsg ( 'Attack Error.','error' );
}// end if
