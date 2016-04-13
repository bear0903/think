<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *    Get 权限内的员工清单
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_lov_DB.php $
 *  $Id: emp_lov_DB.php 3152 2011-08-01 03:01:19Z dennis $$Rev: 3152 $   
 *  $LastChangedDate: 2008-11-21 09:26:45 +0800 (星期五, 21 十一月 2008) 
 *  $Author: dennis $ 
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if
require_once 'AresEmployee.class.php';
require_once 'AresUser.class.php';
$User = new AresUser ( $_SESSION ['user'] ['company_id'], 
					   $_SESSION ['user'] ['user_name'] );
$Employee = new AresEmployee ( $_SESSION ['user'] ['company_id'], 
							   $_SESSION ['user'] ['emp_seq_no']);

$g_parser->ParseSelect ('factory_area_list', $User->GetUserDataPrivileges ( $_SESSION ['user'] ['user_seq_no'], 'factory_area' ), '' );
$g_parser->ParseSelect ('dept_name_list', $User->GetUserDataPrivileges ( $_SESSION ['user'] ['user_seq_no'], 'department' ), '' );
//$g_parser->ParseSelect ('workgroup_list', $Employee->GetWorkGroupList(), '');

if(isset($_POST['submit']))
{
	$where = '';
	if (isset($_POST['factory_area1'])  && 
		!empty($_POST['factory_area1']) &&
        isset($_POST['factory_area2'])  && 
        !empty($_POST['factory_area2']))
    {
    	$where .= ' and a.factory_zone_id between \''.$_POST['factory_area1'].'\' and \''.$_POST['factory_area1'].'\'';
    }
    if (isset($_POST['dept_id1']) && !empty($_POST['dept_id1']) &&
        isset($_POST['dept_id2']) && !empty($_POST['dept_id2']))
    {
       $where .= ' and a.dept_seq_no between \''.$_POST['dept_id1'].'\' and \''.$_POST['dept_id2'].'\'';
    }

    if (isset($_POST['emp_id1']) && !empty($_POST['emp_id1']) &&
        isset($_POST['emp_id2']) && !empty($_POST['emp_id2']))
    {
        $where .= ' and a.emp_id between \''.$_POST['emp_id1'].'\' and \''.$_POST['emp_id2'].'\'';
    }
    /* 班别因为 performance 原因
    // 找班别的基准日期 add by dennis 2006-04-13 10:19:58 
    if (isset($_POST['base_date']) && !empty($_POST['base_date']))
    {
        $where .= ' and trunc(to_date(\''.$_POST['base_date'].'\',\'YYYY-MM-DD\')) = b.cday ';
    } else {
        $where .= ' and trunc(sysdate) = b.cday ';
    }
	*/
	$g_parser->ParseTable ( 'employee_list', 
							$Employee->GetEmpList($_SESSION ['user']['user_seq_no'],$_POST['base_date'],$where));
	$g_parser->ParseOneRow ($_POST );
}// end if
unset($User,$Employee);