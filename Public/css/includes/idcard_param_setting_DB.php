<?php
/**
 * IDCard Reader Parameters Setting Here
 * @author Dennis
 */

if (!defined ('DOCROOT')) die ('Attack Error.');
require_once 'AresCard.class.php';
$IDCard = new AresCard($g_db_sql,$_SESSION['user']['company_id']);
if (isset($_POST['company_name']))
{
	$msg = '參數更新成功.';
	$msg_type = 'information';
	if (!empty($_FILES['logofile']['name']))
	{
		require_once 'AresUpload.class.php';
		$Upload = new AresUpload(DOCROOT.'/upload/org_pic/','idcard_company_log');
		$r = $Upload->_upload();
		if ($r['logofile']['error'] === 0)
		{
			$IDCard->updateIDCardParams($_POST['dept_level'], 
					$_POST['emp_id_rule'],$_POST['company_name'],
					$r['logofile']['savePath']);
		}else{
			$msg = '參數更新失敗,原因:'.$r['error'];
			$msg_type = 'error';
		}		
	}else{
		$r = $IDCard->updateIDCardParams($_POST['dept_level'],
				$_POST['emp_id_rule'],$_POST['company_name']);
		if($r != 1)
		{
			$msg = '參數更新失敗,請重試.';
			$msg_type = 'error';
		}
	}
	showMsg($msg,$msg_type);
	exit;
}else{
	$g_parser->ParseOneRow($IDCard->getIDCardParams());
}
