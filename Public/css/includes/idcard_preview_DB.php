<?php
if (!defined('DOCROOT')) die('Attack Error.');
require_once 'AresCard.class.php';

$Card = new AresCard($g_db_sql,$_SESSION['user']['company_id']);
$where = isset($_GET['empid']) ? ' and id_no_sz = \''.$_GET['empid'].'\'' : $_SESSION['idcard_where'];
$emp_list = $Card->getFabEmpList($where);
$params = $Card->getIDCardParams();
//pr($params);
//unset($_SESSION['idcard_where']);
$g_parser->ParseOneRow($params);
$g_parser->ParseTable('emp_list',$emp_list);