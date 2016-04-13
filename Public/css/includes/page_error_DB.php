<?php
/**
 * Show Message
*  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/page_error_DB.php $
 *  $Id: page_error_DB.php 692 2008-11-19 05:28:28Z dennis $
 *  $Rev: 692 $ 
 *  $Date: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 */
	if (!defined('DOCROOT'))
    {
        die('Attack Error.');
    }// end if
    
    $g_tpl->assign('message_text',urldecode($_GET['msgtxt']));