<?php
/**
 * 公司新闻
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/news_DB.php $
 *  $Id: news_DB.php 692 2008-11-19 05:28:28Z dennis $
 *  $Rev: 692 $ 
 *  $Date: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 ****************************************************************************/
	if (!defined('DOCROOT'))
    {
        die('Attack Error.');
    }// end if
    if (isset($_GET['newsid']) && !empty($_GET['newsid']) && 
    	isset($_GET['action']) && !empty($_GET['action']))
    {
		require_once 'AresDesktop.class.php';
		$Desktop = new AresDesktop($_SESSION['user']['company_id'],
								   $_SESSION['user']['user_seq_no']);
		$g_parser->ParseOneRow($Desktop->GetNewsDetail($_GET['newsid']));
    }// end if