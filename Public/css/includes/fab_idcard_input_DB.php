<?php
/**
 * input the fab idcard no according the employee id
 */
if (!defined('DOCROOT')) die('Attack Error.');
require_once 'AresCard.class.php';

$Card = new AresCard($g_db_sql,$_SESSION['user']['company_id']);

if (isset($_POST['ajaxcall']) && $_POST['ajaxcall'] == 1)
{
	$funcname = $_POST['func'];	
	//echo $funcname;
	exit(json_encode($Card->$funcname($_POST['empseqno'],$_POST['cardno'],$_POST['indate'])));
}

if (isset($_POST['do_action']) && $_POST['do_action'] == 'querydata')
{
	$where = '';
	if (!empty($_POST['emp_id_start']) && !empty($_POST['emp_id_end']))
	{
		$where .= " and id_no_sz between '".$_POST['emp_id_start']."' and '".$_POST['emp_id_end']."'";
	}
	
	if (!empty($_POST['emp_id_start']) && empty($_POST['emp_id_end']))
	{
		$where .= " and id_no_sz = '".$_POST['emp_id_start']."'";
	}
	
	if (!empty($_POST['dept_id_start']) && !empty($_POST['dept_id_end']))
	{
		$where .= " and segment_no_sz between '".$_POST['dept_id_start']."' and '".$_POST['dept_id_end']."'";
	}
	
	if (!empty($_POST['emp_id_start']) && empty($_POST['emp_id_end']))
	{
		$where .= " and id_no_sz = '".$_POST['emp_id_start']."'";
	}
	
	if (!empty($_POST['indate_begin']) && !empty($_POST['indate_end']))
	{
		$where .= " and trunc(indate) between to_date('".$_POST['indate_begin']."','yyyy-mm-dd') and to_date('".$_POST['indate_end']."','yyyy-mm-dd')";
	}

	if (!empty($_POST['indate_begin']) && empty($_POST['indate_end']))
	{
		$where .= " and trunc(indate) = to_date('".$_POST['indate_begin']."','yyyy-mm-dd')";
	}
	
	if (isset($_POST['only_no_cardno']))
	{
		$where .= ' and subid is null ';
	}
	
	$emp_list = $Card->getNoFabIDCardEmpList($where);
	$g_parser->ParseTable('emp_list',$emp_list);
}
