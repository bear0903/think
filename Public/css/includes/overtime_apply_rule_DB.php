<?php
/*
 * 请假规则
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/overtime_apply_rule_DB.php $
 *  $Id: overtime_apply_rule_DB.php 3770 2014-06-09 06:52:22Z dennis $
 *  $Rev: 3770 $ 
 *  $Date: 2014-06-09 14:52:22 +0800 (周一, 09 六月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-06-09 14:52:22 +0800 (周一, 09 六月 2014) $
 ****************************************************************************/
include_once 'leave_apply_rule_class.php';
$applyRule = new ApplyRule();
$applyRule->code="overtime_apply_rule";
$applyRule->run();
