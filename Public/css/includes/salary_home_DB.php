<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     薪资查询首页
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/salary_slip_DB.php $
 *  $Id: salary_slip_DB.php 3414 2012-12-06 05:33:07Z dennis $
 *  $Rev: 3414 $ 
 *  $Date: 2012-12-06 13:33:07 +0800 (Thu, 06 Dec 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-12-06 13:33:07 +0800 (Thu, 06 Dec 2012) $
 *********************************************************/
if (!defined('DOCROOT')) {
    die ( 'Attack Error.' );
} // end if

function isFuncGrant($menus,$menu_id)
{
    foreach ($menus as $val)
    {
        if ($val['NODEID'] === $menu_id){
            return 'Y';
        }
    }
    return 'N';
}

$g_tpl->assign('is_sal_adjust_grant',isFuncGrant($_SESSION['sys_menu'],'ESNC001'));
$g_tpl->assign('is_ss_pay_grant',isFuncGrant($_SESSION['sys_menu'],'ESNC002'));
$g_tpl->assign('is_personal_tax_grant',isFuncGrant($_SESSION['sys_menu'],'ESNC003'));
$g_tpl->assign('is_salary_slip_grant',isFuncGrant($_SESSION['sys_menu'],'ESNC006'));
$g_tpl->assign('is_bonus_grant',isFuncGrant($_SESSION['sys_menu'],'ESNC005'));

