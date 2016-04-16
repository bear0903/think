<?php
/*-----------------------------------------------------
* Description:
* Author: TerryWang 
* Date: 2011-8-24
* Version: 
* ----------------------------------------------------*/
if(!defined('DOCROOT')){
	die('Attack Error!');
}

$companyid = $_SESSION['user']['company_id'];
$username = $_SESSION['user']['user_name'];
if(isset($_POST['default_layout']) && !empty($_POST['default_layout'])){
	
	$default_layout = strval($_POST['default_layout']);
	$sql = "update ehr_md_sys_setting set default_layout = '{$default_layout}' where 
			company_no = '{$companyid}' and 
			user_name = '{$username}'
		";
	$re = $g_db_sql->Execute($sql);
	if($g_db_sql->Affected_Rows() == 0){
		$sql = "insert into ehr_md_sys_setting(
				company_no,
				user_name,
				default_layout
		)values(
				'{$companyid}',
				'{$username}',
				'{$default_layout}'
		)";
		$re = $g_db_sql->Execute($sql);
		if($re){
			$_SESSION['layout'] = $default_layout;
			echo "<script>window.top.location = './redirect.php';</script>";
			exit;
		}else{
			echo $g_db_sql->ErrorMsg();
		}
	}else{
		$_SESSION['layout'] = $default_layout;
		echo "<script>window.top.location = './redirect.php';</script>";
		exit;
	}
}

// 获取用户设置
$sql = "select * from ehr_md_sys_setting where company_no = '{$companyid}' 
		and user_name = '{$username}'";
$arr = $g_db_sql->GetRow($sql);
$g_tpl->assign('row',$arr);
