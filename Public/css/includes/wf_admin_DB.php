<?php
/**
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/wf_admin_DB.php $
 *  $Id: wf_admin_DB.php 2733 2010-06-07 08:46:43Z dlan $
 *  $Rev: 2733 $ 
 *  $Date: 2010-06-07 16:46:43 +0800 (周一, 07 六月 2010) $
 *  $Author: dlan $   
 *  $LastChangedDate: 2010-06-07 16:46:43 +0800 (周一, 07 六月 2010) $
 */
if (! defined ('DOCROOT' )) die ( 'Attack Error.');

require_once 'AresAttend.class.php';
$Attend = new AresAttend ($_SESSION ['user'] ['company_id'],
						  $_SESSION ['user'] ['emp_seq_no']);

$whoami = AresAttend::ADMIN;
// modify by dennis 2010-06-04
$g_parser->ParseTable('leave_workflow_list', $Attend->getLeaveApplyCountByDept('admin'));
$g_parser->ParseTable('overtime_workflow_list',$Attend->getOvertimeApplyCountByDept('admin'));
	

/* remark by dennis 2010-05-35
//流程管理员预设查询流程中的申请
$wherecond = ' and a.flow_status =\'01\'';
$whoami = AresAttend::ADMIN;
$g_tpl->assign('whoami',$whoami);
// 管理员Get 所有未核准的请假单					  
$g_parser->ParseTable('leave_workflow_list',
					  $Attend->getLeaveApply($wherecond,$whoami,false,10,-1));
// 管理员Get 所有未核准的加班单
$g_parser->ParseTable('overtime_workflow_list',
					  $Attend->getOvertimeApply($wherecond,$whoami,false,10,-1));
*/