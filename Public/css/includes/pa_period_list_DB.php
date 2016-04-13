<?php
/**
 * 绩效考核考核期间查询
 * 考核单状态:
 * 1_未提交，暫存（填寫一半）2_自評提交3_初評主管未提交
 * 4_初評主管提交且回送員工複簽 5_員工已複簽並提交
 * 6_初評主管填寫面談備註暫存7_初評主管填寫面談備註並提交
 * 8_複評主管填寫並暫存9_複評主管提交 10_核定提交 11_HR關帳
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_period_list_DB.php $
 *  $Id: pa_period_list_DB.php 3706 2014-03-21 03:03:02Z dennis $
 *  $LastChangedDate: 2014-03-21 11:03:02 +0800 (周五, 21 三月 2014) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevison: 812 $  
 *********************************************************/
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
require_once 'AresPAGoal.class.php';

$PA = new AresPAGoal($_SESSION['user']['company_id'],
				 	 $_SESSION['user']['emp_seq_no']);
// 判断绩效考核模组是否有安装
// add by dennis 20090531
if (1 == (int)$PA->iSPAModuleInstalled())
{				 
	// 当前有效期内"我"的 考核单单号
	$g_parser->ParseTable('pa_period_list',$PA->getMyPAForm());
	
	// 当前有效期内我可以考核的考核单, 我是初／复／核　三阶主管的任一主管
	$g_parser->ParseTable('pa_forms_list',$PA->getWaitForPAForms());
	
	// 防止 Patch 出去，未安装的客户报错
	if (1 == (int)$PA->isGoalPAInstalled()){
		// 目标考核单
		$g_parser->ParseTable('pa_goal_list',$PA->getWaitSettingGoalForms());
		// 编辑已存在的，尚可编辑的
		$g_parser->ParseTable('pa_goal_edit_list',$PA->GetExistsGoalSetting());
		// 待评审目标考核单
		$g_parser->ParseTable('pa_goal_emp_list',$PA->getWaitPAGoalForm());
	}
	
}
