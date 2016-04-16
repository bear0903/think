<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     查询奖金
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/bonus_DB.php $
 *  $Id: bonus_DB.php 3144 2011-07-29 07:11:00Z dennis $
 *  $Rev: 3144 $ 
 *  $Date: 2011-07-29 15:11:00 +0800 (周五, 29 七月 2011) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2011-07-29 15:11:00 +0800 (周五, 29 七月 2011) $
 ****************************************************************************/

include_once 'salary_query.php';
$r = $Salary->GetBonus($y, $m);
$g_parser->ParseTable('bonus_item_list',$r[0]);
// assign total amount
if (isset($r[1]) && is_array ($r[1])) // added by dennis 1/28/2008
{
	$g_tpl->assign('init_amount_total', $r[1]['init_amount_total']);
	$g_tpl->assign('tax_amount_total', $r[1]['tax_amount_total']);
	$g_tpl->assign('act_amount_total', $r[1]['act_amount_total']);
}// end if
