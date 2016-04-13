<?php
/*----------------------------------------------
 * 身份证读卡解决方案，Excel补资料页面
 * TerryWang
 * 2011-9-9
 ----------------------------------------------*/
if(!defined('DOCROOT')) die("Attack error!");
require_once 'AresCard.class.php';
require_once 'AresExcel.class.php';

$Card = new AresCard($g_db_sql,$_SESSION['user']['company_id']);

$excel_template = "../docs/emp_excel_import_template.xls";
$g_tpl->assign('excel_template',$excel_template);

// Excel 导入
if(isset($_POST['excel_import'])){
	// T 读取的最后一个栏位
	$Excel = new AresExcel('../upload/excel/temp','T','Excel5');
	$data = $Excel->readExcel();
	$emp = array();
	$total_num = 0;
	$success_num = 0;
	$fail_num = 0;
	if(is_array($data) && !empty($data)){
		foreach($data as $k => $v){
			$emp = array(
				'id' 		=> $v[0],
				'id_no_sz'	=> $v[1],
				'name'	    => $v[2],
				'sex'		=> $v[3],
				'nation'	=> $v[4],
				'birth'		=> $v[5],
				'address'	=> $v[6],
				'title'		=> $v[7],
				'seg_segment_no' => $v[8],
				'seg_segment_no_department' => $v[9],
				'indate'	=> $v[10],
				'id_card_type' => $v[11],
				'id_card'	=> $v[12],
				'salay_type'=> $v[13],
				'period_master_id' => $v[14],
				'overtime_type_id' => $v[15],
				'absence_type_id'  => $v[16],
				'tw_tax_id'		   => $v[17],
				'year_leave_id'	   => $v[18],
				'jobcategory'	   => $v[19],
				'create_by'		   => $_SESSION['user']['user_seq_no'],
			);
		 $total_num ++;
		 if($Card->DoHrPersonnel($emp)){
			$success_num ++;
		 }else{
		 	$Card->DoHrPersonnelFail($emp);
			$fail_num ++;
		 }
	  }
	}
	$g_tpl->assign('total_num',$total_num);
	$g_tpl->assign('success_num',$success_num);
	$g_tpl->assign('fail_num',$fail_num);
}
