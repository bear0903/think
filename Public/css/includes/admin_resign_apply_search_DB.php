<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     Workflow administrator get employee overtime form apply in progress
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/admin_resign_apply_search_DB.php $
 *  $Id: admin_resign_apply_search_DB.php 3083 2011-03-17 05:54:16Z dennis $
 *  $Rev: 3083 $ 
 *  $Date: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 ****************************************************************************/
if (! defined ('DOCROOT')) {
	die ('Attack Error.');
}// end if
// workflow administrator 
$whoami = 'admin';
require_once 'emp_resign_apply_search_DB.php';