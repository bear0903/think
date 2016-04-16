<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *    根據條件查詢所有員工清單
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_full_lov_DB.php $
 *  $Id: emp_full_lov_DB.php 1615 2009-05-22 09:41:09Z dennis $$Rev: 1615 $   
 *  $LastChangedDate: 2009-05-22 17:41:09 +0800 (周五, 22 五月 2009) $ $Author: dennis $ 
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if
require_once 'AresEmployee.class.php';
$Employee = new AresEmployee ( $_SESSION ['user'] ['company_id'], 
							   $_SESSION ['user'] ['emp_seq_no']);

$g_parser->ParseSelect('title_level_list',$Employee->getGradeList(),'','');
$g_parser->ParseSelect('factory_zone_list',$Employee->getFactoryList(),'','');
//pr($_POST);							       
if(isset($_POST['submit']))
{
	// 廠區
	$where = '';
	$where_array = array();
	if (isset($_POST['factory_zone_id'])  && 
		!empty($_POST['factory_zone_id']))
    {
    	$where .= ' and factory_zone_id = :factory_zone_id ';
    	$where_array['factory_zone_id'] = $_POST['factory_zone_id'];
    }
    // 部門代碼
	if (isset($_POST['deptid_op']) && 
		!empty($_POST['deptid_op']) &&
		isset($_POST['dept_id']) && 
		!empty($_POST['dept_id']))
    {
    	$like = strtolower($_POST['deptid_op']) == 'like' ? '%' : '';
    	$where .= sprintf(' and dept_id %s :dept_id',$_POST['deptid_op']);
    	$where_array['dept_id'] = trim($_POST['dept_id']).$like;
    }
    // 部門名稱
	if (isset($_POST['deptname_op']) && 
		!empty($_POST['deptname_op']) &&
		isset($_POST['dept_name']) && 
		!empty($_POST['dept_name']))
    {
    	$like = strtolower($_POST['deptname_op']) == 'like' ? '%' : '';
    	$where .= sprintf(' and dept_name %s :dept_name',$_POST['deptname_op']);
    	$where_array['dept_name'] = trim($_POST['dept_name']).$like;
    }
    // 職等 
	if (isset($_POST['titlelevel_op']) && 
		!empty($_POST['titlelevel_op']) &&
		isset($_POST['title_grade']) && 
		!empty($_POST['title_grade']))
    {
    	$where .= sprintf(' and title_grade %s :title_grade',$_POST['titlelevel_op']);
    	$where_array['title_grade'] = $_POST['title_grade'];
    }
	// 職務代碼
	if (isset($_POST['titleid_op']) && 
		!empty($_POST['titleid_op']) &&
		isset($_POST['title_id']) && 
		!empty($_POST['title_id']))
    {
    	$like = strtolower($_POST['titleid_op']) == 'like' ? '%' : '';
    	$where .= sprintf(' and title_id %s :title_id',$_POST['titleid_op']);
    	$where_array['title_id'] = trim($_POST['title_id']).$like;;
    }
    // 職務名稱
	if (isset($_POST['titlename_op']) && 
		!empty($_POST['titlename_op']) &&
		isset($_POST['title_name']) && 
		!empty($_POST['title_name']))
    {
    	$like = strtolower($_POST['titlename_op']) == 'like' ? '%' : '';
    	$where .= sprintf(' and title_name %s :title_name', $_POST['titlename_op']);
    	$where_array['title_name'] = trim($_POST['title_name']).$like;;
    }
    // 員工代碼
	if (isset($_POST['empid_op']) && 
		!empty($_POST['empid_op']) &&
		isset($_POST['emp_id']) && 
		!empty($_POST['emp_id']))
    {
    	$like = strtolower($_POST['empid_op']) == 'like' ? '%' : '';
    	$where .= sprintf(' and emp_id %s :emp_id',$_POST['empid_op']);
    	$where_array['emp_id'] = trim($_POST['emp_id']).$like;
    }
    // 員工姓名
	if (isset($_POST['empname_op']) && 
		!empty($_POST['empname_op']) &&
		isset($_POST['emp_name']) && 
		!empty($_POST['emp_name']))
    {
    	$like = strtolower($_POST['empname_op']) == 'like' ? '%' : '';
    	$where .= sprintf(' and emp_name %s :emp_name',$_POST['empname_op']);
    	$where_array['emp_name'] = trim($_POST['emp_name']).$like;
    }
    //echo $where.'<hr/>';
	$g_parser->ParseTable ('employee_list',$Employee->getFullEmpList($where,$where_array));
	$g_parser->ParseOneRow ($_POST );
}// end if