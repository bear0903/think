<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) ARES China Inc.
 *	 
 *	Description:
 *     employee performance appraisal
 *	last update: 2006-03-13 13:23:55   by dennis
 *
 *	!!! notice get_query_where() function reference to "functions.php"
 *
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAppraisal.class.php $
 *  $Id: AresAppraisal.class.php 698 2008-11-19 05:51:54Z dennis $
 *  $Rev: 698 $ 
 *  $Date: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 \****************************************************************************/
class AresAppraisal {
	private $companyID;
	private $managerID;
	private $DBConn;
	
	/**
	 *   Counstructor of class AresAttend
	 *   init property companyid and emplyee seq no (psn_id)
	 *   @param $companyid string, the employee's company id
	 *   @param $emp_seqno string, the employee's sequence no(psn_id) in employee
	 */
	function AresAppraisal($companyid, $managerid) {
		global $g_db_sql;
		$this->companyID = $companyid;
		$this->managerID = $managerid;
		$this->DBConn    = $g_db_sql;
	} // end class constructor
	

	/**
	 *   Get employee appraisal period list (考核主管是我或是我被考核的考核期间)
	 *   @param $empseqno number, 被考核员工的员工代码流水号
	 *   @param $who      string, "mgr" or "ess" default "mgr" 
	 *   @return sql string
	 *	@author : dennis
	 *	@last update : 2006-03-21 10:08:27 by dennis
	 */
	function GetAppraisalPeriod($empseqno = null, $type = "mgr") {
		//$this->DBConn->debug = true;
		$companyid = $this->companyID;
		$_empseqno = is_null ( $empseqno ) ? $this->managerID : $empseqno;
		$sql_string_mgr = <<<_AppraisalPeriodList_
                select a.appraisal_period_seqno,
                       b.appraisal_period_id || ' ' || b.appraisal_period_name as label_text
                  from ehr_appraisal_emp_list_v a, ehr_appraisal_period_v b
                 where a.appraisal_manager_id = '$_empseqno'
                   and a.company_id = '$companyid'
                   and a.appraisal_period_seqno = b.appraisal_period_seqno
                   and a.company_id = b.company_id
_AppraisalPeriodList_;
		
		$sql_string_ess = <<<_AppraisalPeriodList_
                select a.appraisal_period_seqno,
                       b.appraisal_period_id || ' ' || b.appraisal_period_name as label_text
                  from ehr_appraisal_emp_list_v a, ehr_appraisal_period_v b
                 where a.emp_seq_no = '$_empseqno'
                   and a.company_id = '$companyid'
                   and a.appraisal_period_seqno = b.appraisal_period_seqno
                   and a.company_id = b.company_id
_AppraisalPeriodList_;
		$sql_string = is_null ( $empseqno ) ? $sql_string_mgr : $sql_string_ess;
		//print $sql_string;
		/* add by dennis 2008-06-13 */
		$sql_string .= ' order by b.appraisal_begin_date desc';
		//echo $sql_string;
		return $sql_string;
		//return $this->DBConn->GetArray($sql_string);
	} // end function GetAppraisalPeriod()
	/**
	 * 取得考核期清单,返回一个 2-D Array
	 *
	 * @param string $empseqno
	 * @param string $type
	 * @return 2-d array
	 * @author Dennis 
	 */
	function GetAppraisalPeriodList($empseqno, $type = 'mgr') {
		$sql_string = $this->GetAppraisalPeriod ( $empseqno, $type );
		return $this->DBConn->GetArray ( $sql_string );
	} // end GetAppraisalPeriodList()
	

	/**
	 *   Get employee appraisal period list
	 *   @param no parameter
	 *   @return sql string
	 *	 @author : dennis
	 *	 @last update : 2006-05-25 14:54:41 by dennis
	 *   @log
	 *       1. 按部门生成考核单时, 如果考核 Manager 就是被核人时, 不显示这笔资料 by Dennis 2006-05-25 14:55:56 
	 */
	function GetEmployeeAppraisalList($appraisal_period_seqno, $empseqno) {
		$companyid = $this->companyID;
		$_where = ($appraisal_period_seqno) ? "and a.appraisal_period_seqno = '$appraisal_period_seqno'" : "";
		$sql_string = <<<_sql_
                select a.appraisal_period_seqno,
                       a.emp_seq_no,
                       b.emp_begin_date,
                       b.emp_end_date,
                       c.emp_name as manager_name,
                       a.is_emp_confirmed,
                       a.appraisal_item_pseqno as item_master_id,
                       a.appraisal_manager_id,
                       a.is_mgr_confirmed,
                       decode(nvl(d.psn_id, 'N'), 'N', 'N', 'Y') as is_unneeded_appraisal,
                       (case
                         when b.emp_begin_date <= sysdate and b.emp_end_date >= sysdate then
                          'Y'
                         else
                          'N'
                       end) as is_emp_begin
                  from ehr_appraisal_emp_list_v a,
                       ehr_appraisal_period_v   b,
                       ehr_employee_v           c,
                       hr_no_evaluation         d
                 where a.company_id = '$companyid'
                   $_where
                   and a.emp_seq_no = '$empseqno'
                   and a.company_id = b.company_id
                   and a.appraisal_period_seqno = b.appraisal_period_seqno
                   and a.company_id = c.company_id
                   and a.appraisal_manager_id = c.emp_seq_no
                   and a.company_id = d.seg_segment_no(+)
                   and a.appraisal_period_seqno = d.evaluation_period_id(+)
                   and a.emp_seq_no = d.psn_id(+)
                   and a.appraisal_manager_id <> '$empseqno'
_sql_;
		//print $sql_string;
		return $this->DBConn->GetArray ( $sql_string );
	} // end function
	

	/**
	 *   Get employee appraisal period list
	 *   @param no parameter
	 *   @return sql string
	 *	@author : dennis
	 *	@last update : 2006-03-13 16:42:23 by dennis
	 */
	function GetAppraisalPeriodDetail($appraisal_period_seqno) {
		$companyid = $this->companyID;
		$sql_string = <<<_AppraisalPeriodDetail_
				select appraisal_period_id,
                       appraisal_period_name,
                       appraisal_begin_date,
                       appraisal_end_date,
                       emp_begin_date,
                       emp_end_date,
                       manager_begin_date,
                       manager_end_date
                  from ehr_appraisal_period_v
                 where company_id = '$companyid'
                   and appraisal_period_seqno = '$appraisal_period_seqno'
_AppraisalPeriodDetail_;
		return $this->DBConn->GetRow ( $sql_string );
	} // end function GetAppraisalPeriodDetail()
	

	/**
	 *   Get performance appraisal employee list, 
	 *   @param $appraisal_period_id number, appraisal period seq no
	 *   @return array, a 2-dimensional array of records
	 *   @author: dennis 2006-03-13 15:44:08 
	 *   @last update : 2006-03-16 10:54:00 
	 */
	function GetAppraisalEmpList($appraisal_period_seqno) {
		$companyid = $this->companyID;
		$managerid = $this->managerID;
		$sql_string = <<<_AppraisalEmpList_
                select appraisal_unit_seqno,
                       appraisal_period_seqno,
                       unit_id,
                       unit_name,
                       company_id,
                       dept_seqno,
                       dept_id,
                       dept_name,
                       emp_seq_no,
                       emp_id,
                       emp_name,
                       is_gen_app_form,
                       is_emp_confirmed,
                       is_mgr_confirmed,
                       appraisal_form_seqno,
                       appraisal_item_pseqno,
                       appform_date,
                       decode(nvl(b.psn_id, 'N'), 'N', 'N', 'Y') as is_unneeded_appraisal,
                       appraisal_manager_id
                  from ehr_appraisal_emp_list_v a, hr_no_evaluation b
                 where company_id = '$companyid'
                   and appraisal_manager_id = '$managerid'
                   and appraisal_period_seqno  = '$appraisal_period_seqno'
                   and a.company_id = b.seg_segment_no(+)
                   and a.appraisal_period_seqno = b.evaluation_period_id(+)
                   and a.emp_seq_no = b.psn_id(+)
                 order by emp_id
_AppraisalEmpList_;
		return $this->DBConn->GetArray ( $sql_string );
	} // end function GetAppraisalEmpList()
	

	/**
	 *   Get performance appraisal employee basic information
	 *   @param $emp_seqno number, employee psn id
	 *   @param $appraisal_pid number, performance appraisal period id
	 *   @return 2-d array, the appraisal employee information
	 *   @author: dennis 2006-03-16 10:06:10 
	 *   @last update: 2006-03-16 10:06:16 by dennis
	 */
	function GetAppraisalEmpInfo($emp_seqno, $appraisal_pid) {
		$companyid = $this->companyID;
		$sql_string = <<<_AppraisalEmpInfo_
                select a.appraisal_period_seqno,
                       a.appraisal_form_seqno,
                       a.appform_date,
                       a.appraisal_item_pseqno,
                       a.dept_id,
                       a.dept_name,
                       a.emp_seq_no,
                       a.emp_id,
                       a.emp_name,
                       pk_history_data.f_get_value(a.company_id,
                                                   a.emp_seq_no,
                                                   a.appform_date,
                                                   'A') as salary_level,
                       c.join_date,
                       c.job_id,
                       c.job_name,
                       a.unit_id,
                       a.unit_name,
                       b.appraisal_period_id,
                       b.appraisal_period_name,
                       b.manager_begin_date,
                       b.manager_end_date
                  from ehr_appraisal_emp_list_v a,
                       ehr_appraisal_period_v   b,
                       ehr_employee_v           c
                 where a.company_id = :companyid
                   and a.emp_seq_no = :empseqno
                   and b.appraisal_period_seqno = :appraisalid
                   and a.company_id = b.company_id
                   and a.company_id = c.company_id
                   and a.appraisal_period_seqno = b.appraisal_period_seqno
                   and a.emp_seq_no = c.emp_seq_no
_AppraisalEmpInfo_;
		return $this->DBConn->GetRow ( $sql_string, array ("companyid" => $companyid, "appraisalid" => $appraisal_pid, "empseqno" => $emp_seqno ) );
	}
	
	/**
	 *   Get appraisal data
	 *   @param $appraisal_id number, appraisal form id
	 *   @return array, a 2-dimensional array of records
	 *   @author: dennis 2006-03-14 17:32:07 
	 *   @last update : 2006-03-14 18:34:30 
	 */
	function GetAppraisalFormData($appraisal_id) {
		$companyid = $this->companyID;
		$sql_string = <<<_AppraisalData_
                select appraisal_id,
                       evaluation_period_id,
                       evaluation_item_master_id,
                       emp_achievement,
                       manager_commends,
                       self_appraisal_remark,
                       mgr_appraisal_remark,
                       next_goal,
                       excepted_achievement,
                       next_goal2,
                       excepted_achievement2,
                       edu_plan,
                       advantage1,
                       advantage2,
                       advantage3,
                       need_enhance1,
                       need_enhance2,
                       need_enhance3,
                       work_achievement,
                       work_achievement_remark,
                       work_ethic,
                       work_ethic_remark,
                       appraisal_result,
                       appraisal_result_remark,
                       manager_id,
                       last_date,
                       emp_confirmed,
                       mgr_confirmed
                  from hr_appraisals
                 where appraisal_id = :appraisalid
                   and seg_segment_no = :companyid
_AppraisalData_;
		return $this->DBConn->GetRow ( $sql_string, array ("appraisalid" => $appraisal_id, "companyid" => $companyid ) );
	} // end function GetAppraisalFormData()
	

	/**
	 *   Get employee appraisal item level score
	 *   @param $appraisalid number, employee performance appraisal form id
	 *   @param $empseqno number, employee seq no(psn_id)
	 *   @param $ptype string, who appraisal 1_employee 2_manager, default 1
	 *   @return array, a 2-dimensional array of records
	 *   @author: dennis 2006-03-16 15:27:47 
	 *   @last update : 2006-03-16 15:27:48 
	 */
	function GetAppraisalDetail($appraisal_formseqno, $ptype = "1") {
		$companyid = $this->companyID;
		$sql_string = <<<_EmployeeAppraisalDetail_
                select appraisal_form_seqno,
                       detail_seqno,
                       item_level_master_seqno,
                       item_seqno,
                       item_id    as appraisal_item_id,
                       item_desc  as appraisal_item_desc,
                       item_power as appraisal_item_weighted,
                       level_seqno,
                       level_id,
                       level_id ||' - '|| level_desc as level_desc,
                       mgr_appraisal_detail_seqno,
                       mgr_appraisal_level_seqno,
                       who_appraisal,
                       pa_emp_seqno
                  from ehr_appraisal_detail_v
                 where company_id = '$companyid'
                   and appraisal_form_seqno = $appraisal_formseqno
                   and who_appraisal = '$ptype'
_EmployeeAppraisalDetail_;
		//print $sql_string;
		return $this->DBConn->GetArray ( $sql_string );
	} //end GetAppraisalDetail();
	

	/**
	 *   Get employee appraisal item level score
	 *   @param $appraisalid number, employee performance appraisal form id
	 *   @param $empseqno number, employee seq no(psn_id)
	 *   @param $ptype string, who appraisal 1_employee 2_manager, default 1
	 *   @return array, a 2-dimensional array of records
	 *   @author: dennis 2006-03-16 15:27:47 
	 *   @last update : 2006-05-24 16:58:58  by dennis
	 */
	function GetEmpAppraisalDetail($appraisal_formseqno) {
		$companyid = $this->companyID;
		$sql_string = <<<_EmployeeAppraisalDetail_
                select appraisal_form_seqno,
                       detail_seqno,
                       item_level_master_seqno,
                       item_seqno,
                       item_id    as appraisal_item_id,
                       item_desc  as appraisal_item_desc,
                       item_power as appraisal_item_weighted,
                       level_seqno,
                       level_id,
                       level_id||' '||level_desc as level_desc,
                       who_appraisal,
                       pa_emp_seqno
                  from ehr_appraisal_detail_v
                 where company_id = '$companyid'
                   and appraisal_form_seqno = $appraisal_formseqno
                   and who_appraisal = '1'
_EmployeeAppraisalDetail_;
		//print $sql_string;
		return $this->DBConn->GetArray ( $sql_string );
	} //end GetAppraisalDetail();
	

	/**
	 *   Get appraisal item list
	 *   @param $appraisal_id number, appraisal form id
	 *   @return array, a 2-dimensional array of records
	 *   @author: dennis 2006-03-14 17:32:07 
	 *   @last update : 2006-03-29 15:23:32 by dennis 
	 */
	function GetAppraisalItemList($appraisal_item_pseqno) {
		$companyid = $this->companyID;
		$sql_string = <<<_AppraisalItemList_
                select item_master_id       as appraisal_item_pseqno,
                       item_level_master_id as item_level_master_seqno,
                       item_id              as item_seqno,
                       item_no              as appraisal_item_id,
                       item_name            as appraisal_item_desc,
                       item_power           as appraisal_item_weighted
                  from ehr_appraisal_item_v
                 where item_master_id = '$appraisal_item_pseqno'
                   and company_id = '$companyid'
_AppraisalItemList_;
		//print $sql_string;
		return $this->DBConn->GetArray ( $sql_string );
	} // end function GetAppraisalItemList()
	

	/**
	 *   Get performance appraisal apply course training list
	 *   @param $appraisal_id number, appraisal form id
	 *   @return array, a 2-dimensional array of records
	 *   @author: dennis 2006-03-14 17:32:07 
	 *   @last update : 2006-03-14 18:34:30 
	 *   @note "ESS-PA" from ESS performance appraisal 
	 */
	function GetPAApplyCourseList($appraisal_period_seqno, $emp_seqno) {
		$companyid = $this->companyID;
		$sql_string = <<<_PerformanceAppraisalCourse_
                select c.id as course_id,
                       nvl(a.require_explain, c.subject) as course_desc,
                       a.require_time,
                       decode(a.require_level, '2', '低', '3', '一般', '4', '高', 'N/A') as require_level,
                       decode(a.require_type, 1, '年度申请', 2, '临时申请', 'N/A') as require_type,
                       b.deatil_reason_desc as reason,
                       a.require_insert as is_new_add
                  from hr_emp_require a, hr_deatil_reason b, hr_subject c
                 where a.seg_segment_no = '$companyid'
                   and a.psn_id = '$emp_seqno'
                   and a.evaluation_period_id = '$appraisal_period_seqno'
                   and a.seg_segment_no = b.seg_segment_no(+)
                   and a.master_reason_id = b.master_reason_id(+)
                   and a.seg_segment_no = c.seg_segment_no(+)
                   and a.require_subject = c.subject_id(+)
                   and a.create_program = 'ESS-PA'
_PerformanceAppraisalCourse_;
		return $this->DBConn->GetArray ( $sql_string );
	} // end function GetPAApplyCourseList()
	

	/**
	 *   Get appraisal item level list
	 *   @param $appraisal_id number, appraisal form id
	 *   @return array, a 2-dimensional array of records
	 *   @author: dennis 2006-03-14 17:32:07 
	 *   @last update : 2006-03-14 18:34:30 
	 */
	function GetAppraisalItemLevel($master_seqno) {
		$companyid = $this->companyID;
		$sql_string = <<<_AppraisalItemLevelList_
                select level_id,
                       level_no ||' '|| level_desc
                  from ehr_appraisal_level_v
                  where item_level_master_id = '$master_seqno'
                    and company_id = '$companyid'
_AppraisalItemLevelList_;
		//$this->DBConn->debug = true;
		return $sql_string;
	} // end function GetAppraisalItemLevel()
	

	/**
	 *   Save manager appraisal result 
	 *   @param $post_array array,POST array
	 *   @param $who_save string,1_employee , 2_manager
	 *   @return number ,if update success, else return false
	 *   @author: dennis 2006-03-14 17:32:07 
	 *   @last update : 2006-03-19 16:30:31  by dennis 
	 *   @change Log
	 *   1. add save detail. 
	 *       主档和子档同时存档,同时成功同时失败,放在一个 Transaction 中
	 *   2. fix bug save detail failure. 2006-03-21 15:45:57 
	 */
	function SaveAppraisal($post_array, $who_save = "1") {
		$companyid = $this->companyID;
		$sysdate = date ( 'Y-m-d' );
		$appraisal_formseqno = $post_array ["appraisal_form_id"]; // for save master and detail
		$appraisal_empseqno = $post_array ["appraisal_emp_seqno"]; // for  save master and detail
		$next_goal = $post_array ["next_goal"]; // 未来目标(员工主管共同填写) 
		$next_goal2 = $post_array ["next_goal2"]; // 未来目标个人生涯(员工主管共同填写) 
		$excepted_achievement = $post_array ["excepted_achievement"]; // 期望成果(员工主管共同填写)
		$excepted_achievement2 = $post_array ["excepted_achievement2"]; // 期望成果个人生涯(员工主管共同填写) 
		$edu_plan = $post_array ["edu_plan"]; // 培训计划(员工主管共同填写) 
		// Save employee self-appraisal data
		//$this->DBConn->debug=true;
		if ($who_save == "1") {
			$update_by = $appraisal_empseqno;
			$update_program = "PA-ESS";
			$self_appraisal_remark = $post_array ["self_appraisal_remark"];
			$emp_achievement = $post_array ["emp_achievement"];
			$emp_confirmed = $post_array ["emp_confirmed"];
			
			$update_columns = array ("appraisal_id" => $appraisal_formseqno, "seg_segment_no" => $companyid, "self_appraisal_remark" => $self_appraisal_remark, "emp_achievement" => $emp_achievement, "emp_confirmed" => $emp_confirmed );
		} else { // Save manager appraisal data
			$update_by = $this->managerID;
			$update_program = "PA-MGR";
			$manager_commends = $post_array ["manager_commends"];
			$mgr_appraisal_remark = $post_array ["mgr_appraisal_remark"];
			$advantage1 = $post_array ["advantage1"];
			$advantage2 = $post_array ["advantage2"];
			$advantage3 = $post_array ["advantage3"];
			$need_enhance1 = $post_array ["need_enhance1"];
			$need_enhance2 = $post_array ["need_enhance2"];
			$need_enhance3 = $post_array ["need_enhance3"];
			$work_achievement = $post_array ["work_achievement"] == 0 ? null : $post_array ["work_achievement"];
			$work_achievement_remark = $post_array ["work_achievement_remark"];
			$work_ethic = $post_array ["work_ethic"] == 0 ? null : $post_array ["work_ethic"];
			$work_ethic_remark = $post_array ["work_ethic_remark"];
			$appraisal_result = $post_array ["appraisal_result"] == 0 ? null : $post_array ["appraisal_result"];
			$appraisal_result_remark = $post_array ["appraisal_result_remark"];
			$mgr_confirmed = $post_array ["manager_confirmed"];
			
			$update_columns = array ("manager_commends" => $manager_commends, "mgr_appraisal_remark" => $mgr_appraisal_remark, "advantage1" => $advantage1, "advantage2" => $advantage2, "advantage3" => $advantage3, "need_enhance1" => $need_enhance1, "need_enhance2" => $need_enhance2, "need_enhance3" => $need_enhance3, "work_achievement" => $work_achievement, "work_achievement_remark" => $work_achievement_remark, "work_ethic" => $work_ethic, "work_ethic_remark" => $work_ethic_remark, "appraisal_result" => $appraisal_result, "appraisal_result_remark" => $appraisal_result_remark, "mgr_confirmed" => $mgr_confirmed );
		}
		// 员工主管共同填写项目
		$public_columns = array ("appraisal_id" => $appraisal_formseqno, "seg_segment_no" => $companyid, "next_goal" => $next_goal, "excepted_achievement" => $excepted_achievement, "next_goal2" => $next_goal2, "excepted_achievement2" => $excepted_achievement2, "edu_plan" => $edu_plan, "last_date" => $sysdate, "updat_by" => $update_by, "updat_program" => $update_program );
		
		$this->DBConn->StartTrans (); // begin transaction
		//$this->DBConn->debug = true;
		// Save master data to hr_appraisals
		$this->DBConn->Replace ( "hr_appraisals", array_merge ( $public_columns, $update_columns ), "appraisal_id",/* primary key  */
                                   $autoquote = true );
		
		// Save Appraisal Detail data(employee & manager) to hr_appraisals_detail,
		// if exists exeucte udpate else insert
		

		$appraisal_detail_seqno = $post_array ["appraisal_detail_seqno"]; // for save detail
		$appraisal_item_seqno = $post_array ["appraisal_item_seqno"]; // for save detail
		$appraisal_item_level = $post_array ["appraisal_item_level"]; // for save detail
		$table = "hr_appraisals_detail";
		$_cnt = count ( $appraisal_item_seqno );
		for($_i = 0; $_i < $_cnt; $_i ++) {
			$_level_id = intval ( $appraisal_item_level [$_i] ) > 0 ? $appraisal_item_level [$_i] : "null";
			$_record = array ("appraisal_psn_id" => $appraisal_empseqno, "appraisal_id" => $appraisal_formseqno, "evaluation_item_detail_id" => $appraisal_item_seqno [$_i], "evaluation_level_detail_id" => $_level_id, "evaluation_date" => $sysdate );
			//$this->DBConn->debug = true;
			if (intval ( $appraisal_detail_seqno [$_i] ) > 0) {
				$appraisal_detail_id = $appraisal_detail_seqno [$_i];
				$_record ["updat_by"] = $update_by;
				$_record ["updat_date"] = $sysdate;
				$_record ["updat_program"] = $update_program;
				$this->DBConn->AutoExecute ( $table, $_record, "UPDATE", "appraisal_detail_id =$appraisal_detail_id" );
			} else {
				$_record ["seg_segment_no"] = $companyid;
				$_record ["appraisal_type"] = $who_save;
				$_record ["create_by"] = $update_by;
				$_record ["create_date"] = $sysdate;
				$_record ["create_program"] = $update_program;
				$_record ["appraisal_detail_id"] = "hr_appraisals_detail_s.nextval";
				$this->DBConn->AutoExecute ( $table, $_record, "INSERT" );
			} // end if
		} // end for loop
		$this->DBConn->CompleteTrans (); // end transaction
		return $this->DBConn->Affected_Rows ();
	} // end function SaveAppraisal()
	

	function GetPeriodGoal($app_peroid_seqno, $emp_seqno) {
		$sql = "select pre_goal, next_goal, next_goal2
                      from hr_appraisals
                     where seg_segment_no = '%s'
                       and evaluation_period_id = '%s'
                       and psn_id = '%s'";
		return $this->DBConn->GetRow ( sprintf ( $sql, $this->companyID, $app_peroid_seqno, $emp_seqno ) );
	} // end GetPeriodGoal()
} // end class AresAttend
?>