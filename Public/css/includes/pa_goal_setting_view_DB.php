<?php
/**
 *  目标考核单
 *  $CreateBy: Dennis $
 *  $CreateDate: 2013/09/24 $
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_form_DB.php $
 *  $Id: pa_form_DB.php 3133 2011-07-07 09:03:26Z dennis $
 *  $LastChangedDate: 2011-07-07 17:03:26 +0800 (Thu, 07 Jul 2011) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3133 $  
 ****************************************************************************/
if (! defined('DOCROOT'))
    die('Attack Error.');

require_once 'AresPAGoal.class.php';
$myself = $_SESSION['user']['emp_seq_no'];
$PA = new AresPAGoal($_SESSION['user']['company_id'], $myself);
 pr($_GET);$_GET['pa_seqno'];
if (isset($_GET['pa_seqno']) && $_GET['pa_seqno'] != '') {
    $pa_form_seqno = $_GET['pa_seqno'];
    $g_parser->ParseOneRow($PA->getEmpInfo($pa_form_seqno));
    
    $goal_master = $PA->getGoalMasterList($pa_form_seqno);
    $goal_detail = $PA->getGoalDetailList($pa_form_seqno);
    $g_parser->ParseSelect('goal_type_list', $PA->getGoalTypeList(), '');
    $mcnt = count($goal_master);
    $dcnt = count($goal_detail);
    
    // recombine the array, set the detail data as master array sub-array
    for ($i = 0; $i < $mcnt; $i ++) {
        $k = 0;
        for ($j = 0; $j < $dcnt; $j ++) {
            if ($goal_detail[$j]['MASTER_GOAL_SEQNO'] ==
                     $goal_master[$i]['MASTER_GOAL_SEQNO']) {
                $goal_master[$i]['detail'][$k] = $goal_detail[$j];
                $k ++;
            }
        }
        //$goal_master[$i]['ROWSPAN'] = $k;
        $goal_master[$i]['ROWSPAN'] = $k>1 ? 'rowspan="'.$k.'"' : '';
    }
    // $g_tpl->assign('mgr_comment',(count($goal_master)>0?
    // $goal_master[0]['MGR_COMMENT']:''));
    $g_parser->ParseTable('pa_goal_list', $goal_master);
}

if (isset($_POST['doaction'])) {
    
    $pa_form_seqno = $_POST['pa_form_seqno'];
    if ($_POST['doaction'] == 'save') {
        $r = true;
        // 如果主管有调整员工输入的资料
        if ($_POST['is_changed'] == 'Y') {
            include 'pa_goal_rearray.php';
            $r = $PA->saveGoalSetting($master_row, $detail_row,'Y');
        }
        if ($r) {
            $r1 = $PA->approveGoalSetting($pa_form_seqno, $_POST['mgr_comment'],0,'Y');
            if ($r1) {
                showMsg('绩效目标审核提交成功');
            } else {
                //showMsg('绩效目标审核提交失败，请重试。');
            }
        } else {
            showMsg('绩效目标修改保存失败');
        }
    }
    
    if ($_POST['doaction'] == 'reject') {
        $r = $PA->rejectGoalSetting($pa_form_seqno, $_POST['mgr_comment']);
        
        if ($r) {
            showMsg('绩效目标设定已经被驳回');
        }
    }
}

