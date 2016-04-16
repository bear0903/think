<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     employee's calendar
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_schedule_DB.php $
 *  $Id: emp_schedule_DB.php 3461 2013-02-27 01:29:32Z dennis $
 *  $Rev: 3461 $ 
 *  $Date: 2013-02-27 09:29:32 +0800 (周三, 27 二月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-02-27 09:29:32 +0800 (周三, 27 二月 2013) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if
require_once 'AresCalendar.class.php';

// default current date
$curr_year  = isset($_GET ['year'] )  ? $_GET ['year']  : date ( 'Y' );
$curr_month = isset($_GET ['month'] ) ? $_GET ['month'] : date ( 'm' );
if ($curr_year < 1970 || $curr_year > 2050) {
	// add by dennis 20090531
	$msgs = get_multi_lang($GLOBALS['config']['default_lang'],'ESNA007');
	showMsg($msgs['001'],'error');
}
//add by boll 2008-10-17
$company_id = empty($_GET['companyid'])? $_SESSION ['user']['company_id'] : $_GET['companyid'];
$emp_seq_no = empty($_GET['empseqno']) ? $_SESSION ['user']['emp_seq_no'] : $_GET['empseqno'];

// add by dennis 2010-07-20 ESNA007 的 detail 是设定出来的，所以这里要重挑一次多语
$g_parser->ParseMultiLang ('ESNA007', $GLOBALS ['config'] ['default_lang']);

$oldparasting = '';	
foreach ($_GET as $key=>$value)  
	$oldparasting .= '&'.$key.'='.$value;

$calendar = new SolarCalendar($curr_year, 
                              $curr_month, 
						  	  $company_id,
						      $emp_seq_no, 
						      strtolower ($GLOBALS['config']['default_lang']),
						      '?'.$oldparasting);
$g_tpl->assign ('calendar', $calendar->getMonthView ( $curr_month, $curr_year ) );
$g_parser->ParseTable ('cal_list', $calendar->calendar );
unset ( $calendar );
