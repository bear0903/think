<?php
/**
 * 助理加班查询
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/assistant_overtime_apply_search_DB.php $
 *  $Id: assistant_overtime_apply_search_DB.php 2733 2010-06-07 08:46:43Z dlan $
 *  $Rev: 2733 $ 
 *  $Date: 2010-06-07 16:46:43 +0800 (周一, 07 六月 2010) $
 *  $Author: dlan $   
 *  $LastChangedDate: 2010-06-07 16:46:43 +0800 (周一, 07 六月 2010) $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) die ( 'Attack Error.' );
// Assistant get overtime apply forms
$whoami = isset($_GET['whoami']) && !empty($_GET['whoami']) ? $_GET['whoami'] : 'assistant';
require_once 'emp_overtime_apply_search_DB.php';