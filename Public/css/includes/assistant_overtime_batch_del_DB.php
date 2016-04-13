<?php
/**
 * 助理加班查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/assistant_overtime_batch_del_DB.php $
 *  $Id: assistant_overtime_batch_del_DB.php 3774 2014-06-23 07:25:45Z dennis $
 *  $Rev: 3774 $ 
 *  $Date: 2014-06-23 15:25:45 +0800 (周一, 23 六月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-06-23 15:25:45 +0800 (周一, 23 六月 2014) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) die ( 'Attack Error.' );
// Assistant get overtime apply forms
$whoami = isset($_GET['whoami']) && !empty($_GET['whoami']) ? $_GET['whoami'] : 'assistant';
$doaction = count($_POST)>0 ? 'batchdel' : '';
require_once 'emp_overtime_apply_search_DB.php';