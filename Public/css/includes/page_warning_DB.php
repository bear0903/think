<?php
/**
 * Show Message
*  $HeadURL: https://222.92.64.107/svn/EHR/trunk/eHR/ess/includes/page_error_DB.php $
 *  $Id: page_error_DB.php 692 2008-11-19 05:28:28Z dennis $
 *  $Rev: 692 $ 
 *  $Date: 2008-11-19 13:28:28 +0800 (星期三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:28:28 +0800 (星期三, 19 十一月 2008) $
 */

	if (!defined('DOCROOT'))
    {
        die('Attack Error.');
    }// end if
    
    $g_tpl->assign('message_text',urldecode($_GET['msgtxt']));