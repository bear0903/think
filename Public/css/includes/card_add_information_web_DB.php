<?php
/*----------------------------------------------
 * 身份证读卡解决方案，web补资料页面
 * TerryWang
 * 2011-9-9
 * Last Update by Dennis 2012-10-16
 ----------------------------------------------*/
if(!defined('DOCROOT')) die("Attack error!");
require_once 'AresCard.class.php';
//require_once 'GridView/Data_Paging.class.php'; 

$Card = new AresCard($g_db_sql,$_SESSION['user']['company_id']);

if (isset($_POST['ajaxcall']) && $_POST['ajaxcall'] == 1)
{
	$funcname = $_POST['func'];	
	exit(json_encode($Card->$funcname($_POST['q'])));
}
// get 计税区判断是否是台湾公司, add by dennis 2012-12-18
$is_tw_com = $Card->isTWCompany();
$g_tpl->assign('is_tw_com',$is_tw_com);

/**
 * 转档时 時不執行以下查詢
 */
if (!isset($_POST['do_action']) || (isset($_POST['do_action']) && $_POST['do_action'] == 'querydata'))
{
	/**
	* Get Top10 Dept
	*/
	$g_parser->ParseTable('top10_dept_list',$Card->getTop10Dept());
	/**
	* Get top 10 title
	*/
	$g_parser->ParseTable('top10_title_list',$Card->getTop10Title());
	/**
	* get Employee type List
	*/
	$g_parser->ParseSelect('emp_type_list',$Card->getEmpTypeList(),'','');
	
	/**
	 * Get Nation & Regions List
	 */
	$g_parser->ParseSelect('nation_region_list',$Card->getNationRegionList(),'','');
	/**
	 * get personnel type
	 */
	$g_parser->ParseSelect('person_type_list',$Card->getPersonTypeList(),'','');
	
	/**
	 * 员工入职来源
	 */
	$g_parser->ParseSelect('emp_from_list',$Card->getEmpFromList(),'','');
	/**
	 * 工时制度 (打包了以下参数)
	 */
	$g_parser->ParseSelect('pkg_param_list',$Card->getParamPkgList(),'','');
	/**
	 * 给薪类别
	 */
	$g_parser->ParseSelect('salary_type_list',$Card->getSalaryTypeList(),'','');
	/**
	 * 税别
	 */
	$g_parser->ParseSelect('personal_tax_list',$Card->getPersonalTaxList(),'','');
	/**
	 * 计薪期间
	 */
	$g_parser->ParseSelect('salary_period_list',$Card->getSalaryPeroidList(),'','');
	
	/**
	 * 刷卡别
	 */
	$g_parser->ParseSelect('carding_type_list',$Card->getCardingTypeList(),'','');
	
	/**
	 * 加班费代号
	 */
	$g_parser->ParseSelect('ot_fee_type_list',$Card->getOTFeeTypeList(),'','');
	
	/**
	 * 假扣代号
	 */
	$g_parser->ParseSelect('abs_fee_type_list',$Card->getABSFeeTypeList(),'','');
	
	/**
	 * 年假代号
	 */
	$g_parser->ParseSelect('year_abs_type_list',$Card->getYearAbsTypeList(),'','');
	/**
	 * DL/IDL
	 */
	$g_parser->ParseSelect('job_cate_list',$Card->getEmpCateList(),'','');
	
	/**
	 * Get Absence Bonus List
	 */
	$g_parser->ParseSelect('abs_bonus_list',$Card->getAbsBonusList(),'','');
	
	$where = '';
	
	if (isset($_POST['do_action']) && $_POST['do_action'] == 'querydata')
	{
		
		if (!empty($_POST['indate_begin']) && !empty($_POST['indate_end']))
		{
			$where = " and trunc(indate) between to_date('".$_POST['indate_begin']."','yyyy-mm-dd') and to_date('".$_POST['indate_end']."','yyyy-mm-dd')"; 
		}
		
		if (!empty($_POST['indate_begin']) && empty($_POST['indate_end']))
		{
			$where .= " and trunc(indate) = to_date('".$_POST['indate_begin']."','yyyy-mm-dd')";
		}
	}
	$where .= " and nvl(is_fail,'N') = '". (isset($_POST['contain_fail']) ? 'Y' : 'N')."'";
	$emp_list = $Card->GetEmpList($where);
	/*
	// 分页显示
	$numrows = 10;
	$pageIndex = isset($_GET['pageIndex']) ? intval($_GET['pageIndex']) : 1;
	$pageIndex = $pageIndex >= 1 ? $pageIndex : 1;
	$offset    = ($pageIndex - 1)*$numrows;
	$emp_list = $Card->GetEmpList($where,false,$numrows,$offset);
	$emp_count = $Card->GetEmpList($where,true);
	
	if($emp_count >0 ){
		$Paging = new Data_Paging(array('total_rows'=>$emp_count,'page_size'=>$numrows));
		$g_tpl->assign('paging_toolbar',$Paging->outputToolbar());
		unset($Paging);
	}
	*/
	$g_tpl->assign('emp_list',$emp_list);	
}else{	
	$doaction = $_POST['do_action'];
	$cnt 	  = count($_POST['tmp_seqno']);
	
	function getFactVal($idx1,$idx2,$data)
	{
		/*
		 * 如果 detail 有值就以 detail 为准否则为 Master value
		 */
		return empty($data[$idx1][$idx2]) ? $data[$idx1.'_master'] : $data[$idx1][$idx2];
	}
	
	function getKeyStr($arr,$idx)
	{
		$tmp_ids = '';
		foreach ($arr[$idx] as $v)
		{
			$tmp_ids .= '\''.$v . '\',';
		}
		$tmp_ids = substr($tmp_ids,0,-1);
		return $tmp_ids;
	}
	
	if ($cnt>0)
	{
		switch($doaction)
		{
			case 'delete':
				$tmp_ids = getKeyStr($_POST,'tmp_seqno');
				$rows = $Card->delEmpFromTmp($tmp_ids);
				$delete_notice = '成功從臨時檔刪除所選的 '.$rows.'筆資料';
				showMsg($delete_notice,'information','?scriptname='.$_GET['scriptname']);
				break;
			case 'transtohcp':
				$row_data = array();
				//pr($_POST);
				for($i=0; $i<$cnt;$i++)
				{
					$tmp_seqno = $_POST['tmp_seqno'][$i];
					$row_data[$i]['tmp_seqno'] = $tmp_seqno;
					$row_data[$i]['id_card']= $_POST['id_card'][$tmp_seqno];
					$row_data[$i]['title_seqno']= getFactVal('title_seqno',$tmp_seqno,$_POST);
					$row_data[$i]['dept_seqno'] = getFactVal('dept_seqno',$tmp_seqno,$_POST);
					$row_data[$i]['indate'] 	= getFactVal('indate',$tmp_seqno,$_POST);
					$row_data[$i]['salary_type'] = getFactVal('salary_type',$tmp_seqno,$_POST);
					$row_data[$i]['period_master_id'] = getFactVal('period_master_id',$tmp_seqno,$_POST);
					$row_data[$i]['overtime_type_id'] = getFactVal('overtime_type_id',$tmp_seqno,$_POST);
					$row_data[$i]['absence_type_id'] = getFactVal('absence_type_id',$tmp_seqno,$_POST);
					$row_data[$i]['tw_tax_id'] = getFactVal('tw_tax_id',$tmp_seqno,$_POST);
					$row_data[$i]['year_leave_id'] = getFactVal('year_leave_id',$tmp_seqno,$_POST);
					$row_data[$i]['jobcategory'] = getFactVal('jobcategory',$tmp_seqno,$_POST);
					$row_data[$i]['abs_bonus_id'] = getFactVal('abs_bonus_id',$tmp_seqno,$_POST);
					$row_data[$i]['experiencestartdate'] = getFactVal('experiencestartdate',$tmp_seqno,$_POST);
					$row_data[$i]['temp_company'] = getFactVal('temp_company',$tmp_seqno,$_POST);
					$row_data[$i]['carding'] 	  = getFactVal('carding',$tmp_seqno,$_POST);
					$row_data[$i]['contract'] = getFactVal('contract',$tmp_seqno,$_POST);
					$row_data[$i]['costallocation'] = getFactVal('costallocation',$tmp_seqno,$_POST);
					$row_data[$i]['contract1'] = $row_data[$i]['contract'];
					$row_data[$i]['costallocation1'] = $row_data[$i]['costallocation'];
					$row_data[$i]['employee_history_data_id'] = getFactVal('param_pkg_list', $tmp_seqno, $_POST);
					//$Card->trans2HCP($row_data[$i]);
				}
				//pr($row_data);exit;
				$r = $Card->trans2HCP($row_data);
				if (is_array($r)){
					/*
					Array
					(
						[success] => 0
						[fail] => 2
						[success_psnids] => Array
						(
						)
				
						[fail_tmpids] => Array
						(
								[0] => 128
								[1] => 129
						)
					)*/
					$g_tpl->assign('success_cnt',$r['success']);
					$g_tpl->assign('fail_cnt',$r['fail']);
					if ($r['success']>0)
					{
						$psnids = getKeyStr($r, 'success_psnids');
						$success_list = $Card->getTransSuccessEmpList($psnids);
						$g_parser->ParseTable('success_list',$success_list);
					}
					if ($r['fail']>0)
					{
						$tmpids = getKeyStr($r, 'fail_tmpids');
						$fail_list = $Card->getTransTmpErrList($tmpids);
						$g_parser->ParseTable('fail_list',$fail_list);
					}
				}else{
					showMsg($r,'error');
				}
				break;
			default:break;
		}
	}
}
/*
// 导出查询结果到Excel ,export_all 不分页
if(isset($_POST['export']) || isset($_POST['export_all'])){
	/*$col_title = array(
		'id'			=> '员工ID',
		'id_no_sz' 		=> '员工代码',
		'name' 			=> '姓名',
		'sex'			=> '性别',
		'nation'		=> '名族',
		'birth'			=> '生日',
		'address'		=> '户籍地址',
		'title'			=> '职称',
		'seg_segment_no_department' => '部门',
		'indate'		=> '到职日期',
		'id_card'		=> '身份证 编号',
		'is_approve'	=> '是否转到正式档',
		'is_fail'		=> '转档是否失败',
		'fail_reason'	=> '转档失败原因',
	);
	$Card->WriteToExcel(array(
		'col_title' => $col_title,
		'data' => $emp,
	));
	/
	$col_title = array(
		'id'			=> '员工ID',
		'id_no_sz' 		=> '员工代码',
		'name' 			=> '姓名',
		'sex'			=> '性别',
		'nation'		=> '名族',
		'birth'			=> '生日',
		'address'		=> '户籍地址',
		'title'			=> '职称',
		'seg_segment_no'=> '公司',
		'seg_segment_no_department' => '部门',
		'indate'		=> '到职日期',
		'id_card_type'	=> '证件类型',
		'id_card'		=> '身份证 编号',
		'salary_type'	=> '薪别',
		'period_master_id' => '计薪期间',
		'overtime_type_id' => '加班费代号',
		'absence_type_id'  => '假扣代号',
		'tw_tax_id'		=> '税别',
		'year_leave_id' => '年假代号',
		'jobcategory'	=> '直/间接',
	);
	foreach ($emp as $k => $v){
		$id = $Card->GetEmpId($v['ID_NO_SZ']);   //  获取转档后的员工的id,未转档的员工ID为空
		$v['id'] = $id;
		$export_emp[] = assEmp($v);
	}
	$Card->WriteToExcel(array(
		'col_title' => $col_title,
		'data' => $export_emp,
	));
}*/
