<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     Workflow administrator get employee overtime form apply in progress
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/admin_overtime_apply_search_DB.php $
 *  $Id: admin_overtime_apply_search_DB.php 692 2008-11-19 05:28:28Z dennis $
 *  $Rev: 692 $ 
 *  $Date: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 ****************************************************************************/
if (! defined ('DOCROOT')) {
	die ('Attack Error.');
}// end if
// workflow administrator 
$whoami = 'admin';
require_once 'emp_overtime_apply_search_DB.php';