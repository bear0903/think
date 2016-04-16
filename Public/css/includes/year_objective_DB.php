<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     Employee 年度目标
 *  $Id: year_objective_DB.php 3363 2012-10-16 06:53:10Z dennis $
 *  $Rev: 3363 $ 
 *  $Date: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 **************************************************************/
if (! defined ( "DOCROOT" )) {
	die ( "Attack Error." );
}// end if
require_once 'AresPA.class.php';
$emp_seqno = isset($_GET['empseqno']) && !empty($_GET['empseqno']) ? 
             $_GET['empseqno'] :  
             $_SESSION['user']['emp_seq_no'];
$PA = new AresPA($_SESSION['user']['company_id'],$emp_seqno);
$g_tpl->assign('year_goal',$PA->getCurrentYearGoal());