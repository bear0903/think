<?php
/**
 * 助理请假查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/assistant_leave_batch_del_DB.php $
 *  $Id: assistant_leave_batch_del_DB.php 3083 2011-03-17 05:54:16Z dennis $
 *  $Rev: 3083 $ 
 *  $Date: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) die ( 'Attack Error.' );
// Assistant get leave apply forms
$whoami = isset($_GET['whoami']) && !empty($_GET['whoami']) ? $_GET['whoami'] : 'assistant';
require_once 'emp_leave_apply_search_DB.php';