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

if (isset($_GET['pa_seqno']) && $_GET['pa_seqno'] != '') {
    $g_parser->ParseSelect('goal_type_list', $PA->getGoalTypeList(), '');
    $g_parser->ParseOneRow($PA->getEmpInfo($_GET['pa_seqno']));
}

//pr($_POST);exit;
/**
 * Save Data
 */
if (isset($_POST['doaction']) && ($_POST['doaction'] == 'save' || 
          $_POST['doaction'] == 'update' || 
          $_POST['doaction']=='tmpsave'))
{
    
    $pa_form_seqno = $_POST['pa_form_seqno'];
    if (isset($_POST['remark']) && count($_POST['remark'])>0)
    {
        include 'pa_goal_rearray.php';
        //pr($master_row);
        //pr($detail_row);
        //exit;
        // $i 在 file pa_goal_rearray.php 中
        if ($i > 0 && is_array($master_row)) {
            
            //$doaction  = $_POST['doaction'] == 'save' ? 'create' : 'update';
            
            $r = $PA->saveGoalSetting($master_row, $detail_row);
            $msg = $_POST['doaction'] == 'tmpsave' ? '目标考核暂存成功' : '目标设置提交成功,请等待主管审核';
            if ($r) {
                showMsg($msg,'information','?scriptname=pa_goal_setting');
            }
        }
    }else{
        $r = $PA->deleteGoalPAData($pa_form_seqno);
        if ($r) {
            showMsg('所有目标设定已经从暂存档删除（最后一次被核准的资料并未删除）。若要修改，请重要新到首页，修改后提交给主管审核。','information','?scriptname=pa_goal_setting');
        }
    }
}