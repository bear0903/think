<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 * 	助理批量加班申请
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/assistant_overtime_batch_apply_DB.php $
 *  $Id: assistant_overtime_batch_apply_DB.php 692 2008-11-19 05:28:28Z dennis $
 *  $Rev: 692 $ 
 *  $Date: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if

// 共用 ESNA013 加班申请的多语
$g_parser->ParseMultiLang ('ESNA013', $GLOBALS['config']['default_lang']);

// 逻辑处理共用 emp_overtime_apply 程式
require_once 'emp_overtime_apply_DB.php';

