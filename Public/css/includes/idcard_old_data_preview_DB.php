<?php
/**
 * 录用时，身份证检查
 */
if(!defined('DOCROOT')) die("Attack error!");
require_once 'AresCard.class.php';

if (isset($_GET['idcardno']) && !empty($_GET['idcardno']))
{
	$IDCard = new AresCard($g_db_sql,$_SESSION['user']['company_id']);
	$old_data = $IDCard->getEmpOldData($_GET['idcardno']);
	$g_tpl->assign($old_data);

	$g_parser->ParseTable('mr_list',$IDCard->getMeritList($_GET['idcardno']));
}else{
	showMsg('身份證號碼不能爲空','ERROR');
}

