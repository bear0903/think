<?php
/*-----------------------------------------------------
* Description:
* 	The main layout
* Author: TerryWang 
* Date: 2011-8-16
* Version: 
* ----------------------------------------------------*/
if(!defined('DOCROOT')) die("Acess Denied!");
$user_seqno = $_SESSION['user']['user_seq_no'];
security_check($user_seqno,$GLOBALS['config']['ess_home'].'/index.php?action=logout');

require_once 'AresUser.class.php';
//require_once 'Layout/JMenu.php';

$User = new AresUser($_SESSION['user']['company_id'],$_SESSION['user']['user_name']);
$g_menu = $User->GetMenu($user_seqno,'ess',$GLOBALS['config']['default_lang']);
$_SESSION['sys_menu'] = $g_menu;

$menu_list = getMenuItem($g_menu,'ESN');
$g_parser->ParseTable('menu_list',$menu_list);

//$jmenu = new JMenu($g_menu);
//$g_tpl->assign('iframeUrl',$jmenu->getMenuUrl('ESNH000'));
$g_tpl->assign('title','Employee Self-Service');
$g_tpl->assign('company_logo',getLogoUrl());
$g_parser->ParseMultiLang('ESN0000', $GLOBALS['config']['default_lang']);
