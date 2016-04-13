<?php
/**
 * 身份证资料输入错误后修改
 */
if(!defined('DOCROOT')) die("Attack error!");
require_once 'AresCard.class.php';

if (isset($_GET['doaction'])||isset($_POST['doaction']))
{
	$Card = new AresCard($g_db_sql,$_SESSION['user']['company_id']);
	
	if (isset($_GET['doaction']) && isset($_GET['doaction']) == 'getTmpData' && isset($_GET['tmpid']))
	{
		$emp_data = $Card->getCardData($_GET['tmpid']);
		$g_tpl->assign($emp_data);
	}
	
	if (isset($_POST['doaction']) && $_POST['doaction'] == 'saveTmpData' && isset($_POST['tmpid']) && !empty($_POST['tmpid']))
	{		
		unset($_POST['save']);unset($_POST['doaction']);
		if (isset($_POST['tmpid']) && !empty($_POST['tmpid']) && isset($_POST['cardno']) && !empty($_POST['cardno']))
		{
			$idcard_no = $_POST['cardno'];
			$black_reason = $Card->isBlacklist($idcard_no);
			if (!empty($black_reason))
			{
				//$r['is_blacklist'] = 'Y';
				$r['msg'] = '資料未保存,HCP 黑名單人員.原因 :'.$black_reason;
				showMsg($r['msg']);
				exit();
			}
			
			$is_child = $Card->isChidLabor($idcard_no);
			if ($is_child == 'T'){
				//$r['is_child'] = 'Y';
				$r['msg'] = '資料未保存,此人是童工(入職日期以今天為基準).';
				showMsg($r['msg']);
				exit();
			}
			$is_onjob = $Card->isRehireEmp($idcard_no,false);
			if ($is_onjob == 1 )
			{
				//$r['is_onjob'] = 'Y';
				$r['msg'] = '資料未保存,此人屬於在職員工.';
				showMsg($r['msg']);
				exit;
			}
			if ($Card->setCardData($_POST))
			{
				showMsg('身份證資料修改成功.');
				exit;
			}
		}
	}
}