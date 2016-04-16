<?php
/*************************************************************\
*  Copyright (C) 2004 Ares China Inc.
*  Created By Dennis Lan, Lan Jiangtao
*  Description:
*     宿舍在线查房    
*  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/bonus_DB.php $
*  $Id: bonus_DB.php 3144 2011-07-29 07:11:00Z dennis $
*  $Rev: 3144 $
*  $Date: 2011-07-29 15:11:00 +0800 (Fri, 29 Jul 2011) $
*  $Author: dennis $
*  $LastChangedDate: 2011-07-29 15:11:00 +0800 (Fri, 29 Jul 2011) $
****************************************************************************/
if (! defined ( 'DOCROOT' )) die ( 'Attack Error.' );

$a = isset($_POST['doaction']) ? $_POST['doaction'] : 'default';

include 'AresDorm.class.php';
$Dorm = new AresDorm($_SESSION['user']['company_id'],$_SESSION['user']['user_seq_no']);

switch($a)
{
	case 'ajaxcall':
		$func = $_POST['func'];
		switch ($func) {
			case 'getBuildingGrpByArea':
				echo json_encode($Dorm->$func($_POST['areacode']));
				break;
			case 'getBuildingByGrp':
				echo json_encode($Dorm->$func($_POST['areacode'],$_POST['building_grp_no']));
			default:break;
		}
		exit();
	break;
	case 'query':
		$check_item_list = $Dorm->getCheckItemByRoom($_POST['check_date'], 
											   $_POST['check_times'],
											   $_POST['area_code'], 
											   $_POST['building_grp_no'], 
											   $_POST['building_no'], 
											   $_POST['room_no']);
		//pr($room_emp_list);
		$g_parser->ParseTable('check_item_list', $check_item_list);
		break;
	case 'save':
		// unset unnecessary data
		unset($_POST['doaction']);
		$rows = array();
		// recombine the array for fit the update row data
		foreach ($_POST as $key => $row) {
			foreach($row as $k=>$v)
			{
				$rows[$k][$key] = $v;
			}
		}

		$r = $Dorm->SaveSanitationChkReslut($_POST['master_seqno'][0], 
				$_SESSION['user']['emp_seq_no'], 
				$_SESSION['user']['emp_id'], 
				$_SESSION['user']['emp_name'], 
				$rows);
		if ($r>0) showMsg('卫生评比资料保存成功','success');
		break;
	default:
		// assign value to condition field
		break;
}
// 无论是否是在查询,area 资料必须要有
$g_parser->ParseSelect('area_list',$Dorm->getAreaList(),'','');

// 查询时重新给  list 赋值
if(isset($_POST['area_code'])){
	$g_parser->ParseSelect('building_grp_list',$Dorm->getBuildingGrpByArea($_POST['area_code']),'','');
	$g_parser->ParseSelect('building_list',$Dorm->getBuildingByGrp($_POST['area_code'],$_POST['building_grp_no']),'','');
}
