<?php
/**
 * 重组 Data, 让其适合 insert 到 DB
 */
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
$master_row = array();
$detail_row = array();
/**
 * appraisal_id,
 * master_id,
 * psn_id,
 * seg_segment_no,
 * seq,
 * goal_type,
 * work_goal,
 * percent_goal
*/
// Collection the master row data to array
$i = 0;
foreach ($_POST['remark'] as $key => $val) {

    $master_row[$i]['appraisal_id'] = $_POST['pa_form_seqno'];
    $master_row[$i]['seq']          = $i + 1;
    $master_row[$i]['goal_type']    = isset($_POST['goal_type'][$key])      ? $_POST['goal_type'][$key] : '';
    $master_row[$i]['work_goal']    = isset($_POST['work_goal'][$key])      ? $_POST['work_goal'][$key] : '';
    $master_row[$i]['percent_goal'] = isset($_POST['wgoal_weight'][$key])   ? $_POST['wgoal_weight'][$key] : '';
    $master_row[$i]['master_id']    = isset($_POST['master_id'][$key])      ? $_POST['master_id'][$key] : '';
    $master_row[$i]['is_approved']  = isset($_POST['master_is_approved'][$key])    ? $_POST['master_is_approved'][$key] : '';
    /**
     * appraisal_id,
     * master_id,
     * detail_id,
     * psn_id,
     * seg_segment_no,
     * seq,
     * work_goal,
     * percent_goal,
     * complete_date,
     * mgr_psn_id
     */
    // Collection the detail row data to array
    $work_plan          = isset($_POST['work_plan'][$key])      ? array_values($_POST['work_plan'][$key]) : '';
    $plan_weight        = isset($_POST['plan_weight'][$key])    ? array_values($_POST['plan_weight'][$key]) : '';
    $archive_date       = isset($_POST['archive_date'][$key])   ? array_values($_POST['archive_date'][$key]) : '';
    $work_owner         = isset($_POST['work_owner'][$key])     ? array_values($_POST['work_owner'][$key]):'';
    $remark             = isset($_POST['remark'][$key])         ? array_values($_POST['remark'][$key]):'';
    $old_remark         = isset($_POST['old_remark'][$key])     ? array_values($_POST['old_remark'][$key]):'';
    $master_goal_seqno  = isset($_POST['master_goal_seqno'][$key]) ? array_values($_POST['master_goal_seqno'][$key]):'';
    $detail_goal_seqno  = isset($_POST['detail_goal_seqno'][$key]) ? array_values($_POST['detail_goal_seqno'][$key]):'';
    $is_approved        = isset($_POST['is_approved'][$key])    ? array_values($_POST['is_approved'][$key]):'';
    
    $detail_row_cnt     = count($_POST['remark'][$key]);
    
    for ($j = 0; $j < $detail_row_cnt; $j ++) {
        $detail_row[$i][$j]['appraisal_id']     = $_POST['pa_form_seqno'];
        $detail_row[$i][$j]['seq']              = $j + 1;
        $detail_row[$i][$j]['work_goal']        = isset($work_plan[$j]) ? $work_plan[$j]:'';
        $detail_row[$i][$j]['percent_goal']     = isset($plan_weight[$j])?$plan_weight[$j]:'';
        $detail_row[$i][$j]['complete_date']    = isset($archive_date[$j])?$archive_date[$j]:'';
        $detail_row[$i][$j]['mgr_psn_id']       = isset($work_owner[$j]) ? $work_owner[$j] : '';
        $detail_row[$i][$j]['remark']           = isset($old_remark[$j]) ? $old_remark[$j] : '';
        $detail_row[$i][$j]['remark']           .= isset($remark[$j]) ? $remark[$j] : '';
        $detail_row[$i][$j]['master_id']        = isset($master_goal_seqno[$j]) ? $master_goal_seqno[$j] : '';
        $detail_row[$i][$j]['detail_id']        = isset($detail_goal_seqno[$j]) ? $detail_goal_seqno[$j] : '';
        $detail_row[$i][$j]['is_approved']      = isset($is_approved[$j]) ? $is_approved[$j] : '';
    }
    $i ++;
}