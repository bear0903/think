<?php
/*
 * 请假规则
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/leave_apply_rule_DB.php $
 *  $Id: leave_apply_rule_DB.php 692 2008-11-19 05:28:28Z dennis $
 *  $Rev: 692 $ 
 *  $Date: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:28:28 +0800 (周三, 19 十一月 2008) $
 ****************************************************************************/
include_once 'leave_apply_rule_class.php';

$applyRule = new ApplyRule();
$applyRule->code="leave_apply_rule";
$applyRule->run();

?>