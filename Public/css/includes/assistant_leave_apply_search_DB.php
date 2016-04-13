<?php
/**
 * 助理请假查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/assistant_leave_apply_search_DB.php $
 *  $Id: assistant_leave_apply_search_DB.php 2749 2010-06-17 05:21:58Z dlan $
 *  $Rev: 2749 $ 
 *  $Date: 2010-06-17 13:21:58 +0800 (周四, 17 六月 2010) $
 *  $Author: dlan $   
 *  $LastChangedDate: 2010-06-17 13:21:58 +0800 (周四, 17 六月 2010) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) die ( 'Attack Error.' );
// Assistant get leave apply forms
$whoami = isset($_GET['whoami']) && !empty($_GET['whoami']) ? $_GET['whoami'] : 'assistant';
$doaction = count($_POST)>0 ? 'batchsearch' : '';
require_once 'emp_leave_apply_search_DB.php';