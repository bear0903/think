<?php
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
include_once 'AresPAGoal.class.php';
$PA = new AresPAGoal($_SESSION['user']['company_id'], $_SESSION['user']['emp_seq_no']);
$g_parser->ParseSelect('period_list',$PA->getPaGoalPeriod(),'','','','Y'); // cache data 3600s
if (isset($_POST['doaction']) && $_POST['doaction'] != ''){
    $pa_goal_list = $PA->getApprovedGoalPA($_POST['pa_year'],$_POST['pa_period']);
    $g_parser->ParseTable('pa_goal_list', $pa_goal_list);
}
// 查看明细借用 pa_goal_edit.html