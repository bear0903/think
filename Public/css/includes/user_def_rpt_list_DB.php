<?php
/*************************************************************\
 *  Copyright (C) 2008 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *    Edit user define report
 *     
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/user_def_rpt_list_DB.php $
 *  $Id: user_def_rpt_list_DB.php 3152 2011-08-01 03:01:19Z dennis $
 *  $Rev: 3152 $ 
 *  $Date: 2011-08-01 11:01:19 +0800 (周一, 01 八月 2011) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2011-08-01 11:01:19 +0800 (周一, 01 八月 2011) $
 ****************************************************************************/
if (!defined('DOCROOT')) die ('Attack Error.');
require_once 'EUC/Wizard.php';
$w = new EUC_Wizard();
$g_tpl->assign('mod_list',$w->getModule());

// default query report list
$g_tpl->assign('rpt_list',$w->getMenu(@$_POST['module_id'],@$_POST['rpt_name'],1));

/**
 * delete or update user defined report
 */
if (isset($_GET['action']) && isset($_GET['rptid']))
{
	if ($_GET['action'] == 'delete')
	{
		$r = $w->deleteRpt($_GET['moduleid'],$_GET['rptid']);
		if($r !== false)
		{
			showMsg('删除成功','information','?scriptname='.$_GET['scriptname']);
		}else{
			showMsg('删除失败');
		}
	}
}

/* end file */