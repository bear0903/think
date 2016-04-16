<?php
/*----------------------------------------------------------------
 * 身份证读卡解决方案
 * TerryWang
 * 2011-9-5
 * Last Update by Dennis 2012-10-15
 * Last Update by Dennis 2012/12/12 add id card check function
 ----------------------------------------------------------------*/
if(!defined('DOCROOT')) die("Attack error!");

/**
 * Save Data to tmp table
 * add by dennis 2011-11-14
 */
require_once 'AresCard.class.php';
$Card = new AresCard($g_db_sql,$_SESSION['user']['company_id']);

if (isset($_POST['ajaxcall']) && $_POST['ajaxcall'] == '1' && 
	isset($_POST['cardno'])   && !empty($_POST['cardno']))
{	
	$idcard_no =  $_POST['cardno'];	
	$black_reason = $Card->isBlacklist($idcard_no);	
	if (!empty($black_reason))
	{
		$r['is_blacklist'] = 'Y';
		$r['msg'] = 'HCP 黑名單人員.原因 :'.$black_reason;
		echo json_encode($r);
		exit();
	}
	
	$is_tmp_exists = $Card->isTmpExists($idcard_no);
	if ($is_tmp_exists === 'Y')
	{
		$r['is_tmp_exists'] = 'Y';
		$r['msg'] = '已經讀過此人身份證(未轉正式檔或轉檔失敗)，請換下一個.';
		echo json_encode($r);
		exit();
	}
	$is_child = $Card->isChidLabor($idcard_no);
	if ($is_child == 'T'){
		$r['is_child'] = 'Y';
		$r['msg'] = '此人是童工(入職日期以今天為基準).';
		echo json_encode($r);
		exit();
	}
	$is_onjob = $Card->isRehireEmp($idcard_no,false);
	if ($is_onjob == 1 )
	{
		$r['is_onjob'] = 'Y';
		$r['msg'] = '此人屬於在職員工.';
		echo json_encode($r);
		exit;
	}
	unset($_POST['ajaxcall']);
	unset($_POST['keyin']);
	unset($_POST['cons_desc']);
	$res = $Card->save2Temp($_POST);
	if ($res)
	{
		$isrehire = $Card->isRehireEmp($idcard_no);
		$r['is_success'] = 'Y';
		$r['msg'] = '身份證資料儲存成功，請換下一個.';
		$r['msg'] = $isrehire > 0    ? '此人屬於二次錄用.'.$r['msg'] : $r['msg'];
		$r['msg'] = $is_child == 'W' ? '此人是未成年工(入職日期以今天為基準).'.$r['msg'] : $r['msg'];
		echo json_encode($r);
		exit();
	}
}else{
	$is_tw_com = $Card->isTWCompany();
	$g_tpl->assign('is_tw_com',$is_tw_com);
}

