<?php
/**
 * 录用时，身份证检查
 */
if(!defined('DOCROOT')) die("Attack error!");
require_once 'AresCard.class.php';
// remove 18 位長度校驗 2012/11/16 by dennis
if (isset($_POST['isajaxcall']) && $_POST['isajaxcall'] == 1 && 
	isset($_POST['cardno']))
{
	$idcard_no = $_POST['cardno'];
	$IDCard = new AresCard($g_db_sql,$_SESSION['user']['company_id']);
	if (isset($_POST['isgetage']))
	{
		echo json_encode($IDCard->getPersonalAge($idcard_no));
		exit;
	}
	$black_reason = $IDCard->isBlacklist($idcard_no);	
	if (!empty($black_reason))
	{
		$r['is_blacklist'] = 'Y';
		$r['msg'] = 'HCP 黑名單成員.<br/>原因:'.$black_reason;
		echo json_encode($r);
		exit();
	}
	$is_child = $IDCard->isChidLabor($idcard_no);
	if ($is_child == 'T'){
		$r['is_child'] = 'Y';
		$r['msg'] = '此人是童工(入職日期以今天為基準).';
		echo json_encode($r);
		exit();
	}
	if ($is_child == 'W'){
		$r['is_child'] = 'N';
		$r['msg'] = '此人是未成年工(入職日期以今天為基準).';
		echo json_encode($r);
		exit();
	}
	$is_onjob = $IDCard->isRehireEmp($idcard_no,false);
	if ($is_onjob == 1 )
	{
		$r['is_onjob'] = 'Y';
		$r['msg'] = '此人屬於在職員工.';
		echo json_encode($r);
		exit;
	}
	$isrehire = $IDCard->isRehireEmp($idcard_no);
	if ($isrehire > 0) // 多次离职的情况
	{
		$r['is_rehire'] = 'Y';
		$r['msg'] = '此人屬於二次錄用.';
		echo json_encode($r);
		exit;
	}
	// 檢查無誤 add by dennis 2012/11/16
	$r['is_success'] = 'Y';
	$r['msg'] = '此人檢查無異常';
	echo json_encode($r);
	exit;
}

