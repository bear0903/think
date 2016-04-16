<?php
/**************************************************************************\
 *   Created by Gracie at 20090702
 **************************************************************************/
class AresTrans{
	protected $companyID;
	protected $empSeqNo;
	private $DBConn;
	const MYSELF = 'myself';
	const ASSISTANT = 'assistant';
	const ADMIN = 'admin';
	/**
	 *   Counstructor of class AresAttend
	 *   init property companyid and emplyee seq no(psn_id)
	 *   @param $companyid string, the employee's company id
	 *   @param $emp_seqno string, the employee's sequence no(psn_id) in employee
	 */
	function __construct($companyid, $emp_seqno) {
		global $g_db_sql;
		$this->companyID = $companyid;
		$this->empSeqNo = $emp_seqno;
		$this->DBConn = &$g_db_sql;
	} // end class constructor

    function getListMultiLang($programno, $labelid, $lang, $where = '') {
		$sql = 'select seq as option_value, value as lable_text
				  from app_muti_lang
				 where program_no = :program_no
				   and name = :labelid
				   and lang_code  = :lang_code
				   and type_code = \'LL\'';
		//$this->DBConn->debug = true;
		 $this->DBConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->DBConn->GetArray($sql . $where, array('program_no' => $programno, 'labelid' => $labelid, 'lang_code' => $lang));
	} // end getFlowStatus()

	function CheckFlowType($flowtype) {
		$flow_type_array = array("absence", "overtime", "cancel_absence","trans","nocard","resign");
		if(! in_array( strtolower($flowtype), $flow_type_array)) {
			trigger_error( "<font color='red'>Programming Error: Unknow Workflow Type, Must be 'absence' or 'overtime'. Current Workflow Type is " . $flowtype . "</font>", E_USER_ERROR);
		}
	} // end function CheckFlowType();

	function DeleteWorkflowApply($workflow_seqno, $flowtype) {
		$this->CheckFlowType($flowtype);
		$sql = 'delete from hr_' . $flowtype . '_flow_sz where ' . $flowtype . "_flow_sz_id = '$workflow_seqno'";
		$this->DBConn->Execute($sql);
		$effectrows = $this->DBConn->Affected_Rows();
		if($effectrows > 0) {
			return $effectrows;
		}
		return $this->DBConn->ErrorMsg();
	} // end function DeleteWorkflowApply();

	/**
	 * Get 权限内部门清单
	 * @param string $user_seqno
	 * @return 2-d Array
	 * @author Dennis
	 */
	function GetDeptName($user_seqno) {
		// register current user
		$stmt = 'begin pk_erp.p_set_segment_no(:company_id); pk_erp.p_set_username(:user_seqno); end;';
		$this->DBConn->Execute($stmt, array('company_id' => $this->companyID, 'user_seqno' => $user_seqno));
		$sql = <<<eof
			select segment_no, segment_no_sz || ' ' || segment_name
			  from gl_segment
			 where seg_segment_no = :company_id
			   and sysdate between begindate and enddate
			   and pk_user_priv.f_dept_priv(segment_no) = 'Y'
			 order by segment_no_sz
eof;
		//print $sql;
		return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
	} // end GetDeptName()
	/**
	 * Workflow 管理员查询时的部门条件
	 * @param no
	 * @return array
	 * @author Dennis 2008-09-25
	 */
	public function getWfDept()
	{
		$sql = <<<eof
			select dept_seq_no,dept_id||' - '|| dept_name as dept_name
			from   ehr_department_v
			where  company_id = :company_id
			  and  dept_type = 'DEPARTMENT'
			  order by dept_id
eof;
		 return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
	}// end getWfDept()

	function CancelWorkflow($workflow_seqno,
							$flowtype,
							$user_seqno,
							$cancel_comment = null) {
		$this->CheckFlowType($flowtype);
		$companyid = $this->companyID;
		$emp_seqno = $this->empSeqNo;
		$_submit_result = array('msg' => '', 'is_success' => '');
		$_procedure_name = array('trans' => 'wf.pkg_work_flow.p_cancel_' . $flowtype . '_apply',
								  'nocard' => 'wf.pkg_work_flow.p_cancel_' . $flowtype . '_apply',
								  'resign' => 'wf.pkg_work_flow.p_cancel_' . $flowtype . '_apply');

		$stmt1 = 'begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin %s(pi_seg_segment_no=>:in_companyid,pi_%s_flow_sz_id=>:in_workflow_seqno,pi_reject_reason=>:in_cancel_comment,pi_admin_id=>:in_flowadmin_empseqno,po_errmsg=>:out_msg,po_success=>:out_issuccess); end; end;';
		$func_name = $_procedure_name[$flowtype];
		$stmt1 = sprintf($stmt1,$func_name,$flowtype);
		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $companyid, 'in_company_id1', 10);
		$this->DBConn->InParameter($stmt, $user_seqno, 'in_user_seqno', 10);
		$this->DBConn->InParameter($stmt, $companyid, 'in_companyid', 10);
		$this->DBConn->InParameter($stmt, $workflow_seqno, 'in_workflow_seqno', 10);
		$this->DBConn->InParameter($stmt, $cancel_comment, 'in_cancel_comment', 2000);
		$this->DBConn->InParameter($stmt, $emp_seqno, 'in_flowadmin_empseqno', 10);
		$this->DBConn->OutParameter($stmt, $_submit_result['msg'], 'out_msg', 2000);
		$this->DBConn->OutParameter($stmt, $_submit_result['is_success'], 'out_issuccess', 2);
		//$this->DBConn->debug = true;
		$this->DBConn->StartTrans(); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans(); // end transaction
		return $_submit_result;
	} // end function CancelWorkflow();

	function GetTranstype($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	  select transtype_master_id,
    	         transtype_master_no||' '||transtype_master_name transtype_desc,
    	         transtype_master_name
		    from hr_transtype_master
		   where is_active = 'Y'
		     and transtype_used='1'
		     and seg_segment_no = :company_id
		   order by transtype_master_no
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetTranstype()

    function GetNewdept($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	  select segment_no, segment_no_sz ||' '|| segment_name segment_desc, segment_name
		    from gl_segment
		   where seg_segment_no = :company_id
		   order by segment_no_sz
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewdept()

    function GetNewnb($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	  select codeid,codevalue
			from hr_codedetail
		   where hcd_seg_segment_no = :company_id
			 and hcd_codetype = 'FOURKINDS'
		   order by line_no
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewnb()

    function GetNewcontract($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	  select codeid,codevalue
			from hr_codedetail
		   where hcd_seg_segment_no = :company_id
			 and hcd_codetype = 'PERSONTYPE'
		   order by line_no
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewcontract()

    function GetNewotype($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	  select hr_overtimetype_id,overtimetype_code||' '||overtimetype_desc overtimetype_desc
		    from hr_overtimetype
		   where seg_segment_no = :company_id
		   order by overtimetype_code desc
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewotype()

    function GetNewtitle($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	  select title, title_no_sz ||' '|| titlename title_no_sz
			from hr_title
		   where seg_segment_no = :company_id
		   order by title_no_sz
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewotype()

    function GetNewabsence($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
		    	 select hr_absencetype_id,
				        hr_absencetype_code || ' ' || hr_absencetype_desc absence_code
				   from hr_absencetype
				  where seg_segment_no = :company_id
				  order by hr_absencetype_code
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewabsence()

    function GetNewjobcategory($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	  select codeid,codevalue
			from hr_codedetail
		   where hcd_seg_segment_no = :company_id
			 and hcd_codetype = 'JOB'
		   order by line_no
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewjobcategory()

    function GetNewyear($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
		    	  select hr_yeartype_id, yeartype_code ||' '|| yeartype_desc yeartype_code
                    from hr_yeartype
                   where seg_segment_no = :company_id
                   and is_active = 'Y'
                   order by yeartype_code
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewyear()

    function GetNewperiod($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
		    	  select period_master_id, period_master_no||' '||period_master_desc period_master_no
					from hr_period_master
				   WHERE SEG_SEGMENT_NO = :company_id
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewperiod()

    function GetNewjd($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
		    	  select JD_MASTER_ID, JD_MASTER_NO||' '||JD_MASTER_DESC JD_MASTER_NO
				    from HR_JD_MASTER
				   where seg_segment_no = :company_id
				    order by JD_MASTER_NO
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewjd()

    function GetNewcost($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
		    	  select PARAMETER_ID,PARAMETER_VALUE
					from PB_PARAMETERS
				   where PARAMETER_TYPE = 'HR_COSTALLOCATION'
					 and seg_segment_no = :company_id
				   ORDER BY PARAMETER_ID
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewcost()

    function GetNewtax($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
		    	  SELECT TAX_ID,
				         TAX_CODE||' '||TAX_NAME TAX_CODE
				    FROM HR_TAX_HEADER
				   where seg_segment_no = :company_id
				     and is_active = 'Y'
				    order by TAX_CODE
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetNewtax()

     function GetNewreason($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
				select a.TRANSTYPE_DETAIL_ID TRANSTYPE_DETAIL_ID,
		               a.TRANSTYPE_DETAIL_NO || ' ' || a.TRANSTYPE_DETAIL_NAME TRANSTYPE_DETAIL_NO,
		               a.TRANSTYPE_MASTER_ID TRANSTYPE_MASTER_ID
		            from HR_TRANSTYPE_DETAIL a,hr_transtype_master b
		           where a.seg_segment_no = b.seg_segment_no
		           and a.seg_segment_no = :company_id
		           and a.transtype_master_id=b.transtype_master_id
		           and transtype_used='1'
		           and is_active='Y'
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
     }// end GetNewreason()


    function SubmitNocardForm($user_seqno, $workflow_seqno) {
		$companyid = $this->companyID;
		$_submit_result = array("msg" => "", "is_success" => "");
		$stmt1 = "begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_submit_nocard_apply(pi_seg_segment_no=>:in_companyid,pi_nocard_flow_sz_id=>:in_workflow_seqno,po_errmsg=>:out_msg,po_success=>:out_issuccess); end;end;";
		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $companyid, "in_company_id1", 10);
		$this->DBConn->InParameter($stmt, $companyid, "in_companyid", 10);
		$this->DBConn->InParameter($stmt, $user_seqno, "in_user_seqno", 10);
		$this->DBConn->InParameter($stmt, $workflow_seqno, "in_workflow_seqno", 10);
		$this->DBConn->OutParameter($stmt, $_submit_result["msg"], "out_msg", 2000);
		$this->DBConn->OutParameter($stmt, $_submit_result["is_success"], "out_issuccess", 2);
		//$this->DBConn->debug = true;
		$this->DBConn->StartTrans(); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans(); // end transaction
		return $_submit_result;
	} // end function SubmitNocardForm();
	function SubmitTransForm($user_seqno, $workflow_seqno) {
		$companyid = $this->companyID;
		$_submit_result = array("msg" => "", "is_success" => "");
		$stmt1 = "begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_submit_trans_apply(pi_seg_segment_no=>:in_companyid,pi_trans_flow_sz_id=>:in_workflow_seqno,po_errmsg=>:out_msg,po_success=>:out_issuccess); end;end;";
		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $companyid, "in_company_id1", 10);
		$this->DBConn->InParameter($stmt, $companyid, "in_companyid", 10);
		$this->DBConn->InParameter($stmt, $user_seqno, "in_user_seqno", 10);
		$this->DBConn->InParameter($stmt, $workflow_seqno, "in_workflow_seqno", 10);
		$this->DBConn->OutParameter($stmt, $_submit_result["msg"], "out_msg", 2000);
		$this->DBConn->OutParameter($stmt, $_submit_result["is_success"], "out_issuccess", 2);
		//$this->DBConn->debug = true;
		$this->DBConn->StartTrans(); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans(); // end transaction
		return $_submit_result;
	} // end function SubmitTransForm();
	function SubmitResignForm($user_seqno, $workflow_seqno) {
		$companyid = $this->companyID;
		$_submit_result = array("msg" => "", "is_success" => "");
		$stmt1 = "begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_submit_resign_apply(pi_seg_segment_no=>:in_companyid,pi_resign_flow_sz_id=>:in_workflow_seqno,po_errmsg=>:out_msg,po_success=>:out_issuccess); end;end;";
		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $companyid, "in_company_id1", 10);
		$this->DBConn->InParameter($stmt, $companyid, "in_companyid", 10);
		$this->DBConn->InParameter($stmt, $user_seqno, "in_user_seqno", 10);
		$this->DBConn->InParameter($stmt, $workflow_seqno, "in_workflow_seqno", 10);
		$this->DBConn->OutParameter($stmt, $_submit_result["msg"], "out_msg", 2000);
		$this->DBConn->OutParameter($stmt, $_submit_result["is_success"], "out_issuccess", 2);
		//$this->DBConn->debug = true;
		$this->DBConn->StartTrans(); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans(); // end transaction
		return $_submit_result;
	} // end function SubmitResignForm();

	/**
	 * 暂存或提交人事异动申请
	 */
	function SaveTransApply($userseqno,
						    $dept_id,
						    $trans_date,
						    $transtype_id,
						    $new_dept_id,
						    $new_title_id,
						    $new_jobcategory,
						    $new_period_id,
						    $new_costallocation,
						    $new_reason,
						    $new_nb_newleader,
						    $new_contract,
						    $new_overtime_type_id,
						    $new_absence_type_id,
						    $new_yeartype_id,
						    $new_job_id,
						    $new_tax_id,
						    $remark,
						    $tmp_save,
						    $emp_seqno = null) {
		$emp_seqno = is_null($emp_seqno) ? $this->empSeqNo : $emp_seqno;
		$result = array('msg' => '',
						 'flow_seqno' => '',
						 'is_success' => '');
		$stmt1 = 'begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_save_trans_apply(pi_seg_segment_no => :in_companyid,pi_psn_id => :in_empseqno,pi_transtype_id => :in_transtype_id,pi_trans_date => :in_trans_date,pi_dept_id => :in_dept_id1,pi_title_id=>:in_title_id,pi_jobcategory=>:in_jobcategory,pi_period_id=>:in_period_id,pi_costallocation=>:in_costallocation,pi_reason=>:in_reason,pi_nb_newleader=>:in_nb_newleader,pi_contract=>:in_contract,pi_overtime_type_id=>:in_overtime_type_id,pi_absence_type_id=>:in_absence_type_id,pi_yeartype_id=>:in_yeartype_id,pi_job_id=>:in_job_id,pi_tax_id=>:in_tax_id,pi_remark => :in_remark,pi_submit => :in_submit,po_errmsg => :out_msg,po_trans_flow_sz_id => :out_trans_flowseqno,po_success => :out_issuccess); end; end;';
		//$this->DBConn->debug = true;
		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $this->companyID, 'in_company_id1', 10);
		$this->DBConn->InParameter($stmt, $userseqno, 'in_user_seqno', 10);
		$this->DBConn->InParameter($stmt, $this->companyID, 'in_companyid', 10);
		$this->DBConn->InParameter($stmt, $emp_seqno, 'in_empseqno', 10);
		//$this->DBConn->InParameter($stmt, $dept_id, 'in_dept_id', 10);
		$this->DBConn->InParameter($stmt, $new_dept_id, 'in_dept_id1', 20);
		$this->DBConn->InParameter($stmt, $transtype_id, 'in_transtype_id', 10);
		$this->DBConn->InParameter($stmt, $trans_date, 'in_trans_date', 20);

		$this->DBConn->InParameter($stmt, $new_title_id, 'in_title_id', 10);
		$this->DBConn->InParameter($stmt, $new_jobcategory, 'in_jobcategory', 10);
		$this->DBConn->InParameter($stmt, $new_period_id, 'in_period_id', 10);
		$this->DBConn->InParameter($stmt, $new_costallocation, 'in_costallocation', 10);
		$this->DBConn->InParameter($stmt, $new_reason, 'in_reason', 10);
		$this->DBConn->InParameter($stmt, $new_nb_newleader, 'in_nb_newleader', 10);
		$this->DBConn->InParameter($stmt, $new_contract, 'in_contract', 10);
		$this->DBConn->InParameter($stmt, $new_overtime_type_id, 'in_overtime_type_id', 10);
		$this->DBConn->InParameter($stmt, $new_absence_type_id, 'in_absence_type_id', 10);
		$this->DBConn->InParameter($stmt, $new_yeartype_id, 'in_yeartype_id', 10);
		$this->DBConn->InParameter($stmt, $new_job_id, 'in_job_id', 10);
		$this->DBConn->InParameter($stmt, $new_tax_id, 'in_tax_id', 10);

		$this->DBConn->InParameter($stmt, $remark, 'in_remark', 4000);
		$this->DBConn->InParameter($stmt, $tmp_save, 'in_submit', 2);

		$this->DBConn->OutParameter($stmt, $result['msg'], 'out_msg', 2000);
		$this->DBConn->OutParameter($stmt, $result['flow_seqno'], 'out_trans_flowseqno', 9);
		$this->DBConn->OutParameter($stmt, $result['is_success'], 'out_issuccess', 2);

		$this->DBConn->StartTrans(); // begin transaction
		$this->DBConn->Execute($stmt);

		$this->DBConn->CompleteTrans(); // end transaction

		return $result;

	} // end SaveTransApply()

	/**
	 * 人事异动申请查询
	 *
	 * @param string  $query_where		查询条件
	 * @param boolean $myself			只挑当前login user 的加班申请
	 * @param boolean $get_total_rows	只挑资料总笔数
	 * @param number  $numrows			从 offset 起显示多少笔
	 * @param number  $offset			从哪一笔资料开始显示
	 * @return array
	 * @author Dennis 2008-09-19
	 */
	function getTransApply($query_where,
							  $who = 'myself',
							  $countrow = false,
							  $numrows = -1,
							  $offset = -1) {
		$sql = <<<eof
					select a.trans_flow_sz_id as trans_flow_seqno,
					       a.seg_segment_no,
					       a.psn_id,
					       b.id_no_sz emp_id,
					       b.name_sz emp_name,
					       a.validdate as TRANS_DATE,
					       pk_personnel_msg.f_transtype_master_no(a.seg_segment_no, a.issuetype) trans_type,
					       pk_personnel_msg.f_transtype_master_desc(a.seg_segment_no,
					                                                a.issuetype) trans_name,
					       pk_department_message.f_dept_msg(a.seg_segment_no,
					                                        a.newdepartment,
					                                        a.validdate,
					                                        '01') segment_no_sz,
					       pk_department_message.f_dept_msg(a.seg_segment_no,
					                                        a.newdepartment,
					                                        a.validdate,
					                                        '02') new_dept_name,
					       c.titlename new_title,
					       d.codevalue new_jobcategory,
					       m.period_master_desc new_period,
					       i.parameter_value new_costallocation,
					       n.codevalue  new_contract,
					       r.codevalue new_nb_newleader,
					       o.transtype_detail_no||' '||o.transtype_detail_name new_transfer_reson,
					       e.overtimetype_code||' '||e.overtimetype_desc new_overtime_type,
					       f.hr_absencetype_code||' '||f.hr_absencetype_desc new_absence_type,
					       g.yeartype_code||' '||g.yeartype_desc new_year_type,
					       q.jd_master_no||' '||q.jd_master_desc new_job,
					       p.tax_code||' '||p.tax_name  NEW_TAX,
					       'trans' as apply_type,
	                       a.issuetype,
	                       a.status flow_status,
	                       to_char(a.create_date,'yyyy/mm/dd hh24:mi:ss') as create_date,
	                       decode(a.status,'00','暂存','01','提交','02','流程中','03','核准','04','驳回','05','作废','06','异常') as status_name
					  from hr_trans_flow_sz  a,
					       hr_personnel_base b,
					       hr_title          c,
					       hr_codedetail     d,
					       hr_overtimetype   e,
					       hr_absencetype    f,
					       hr_yeartype       g,
					       hr_codedetail     r,
					       hr_period_master  m,
					       pb_parameters     i,
					       hr_codedetail     n,
					       hr_transtype_detail o,
					       hr_tax_header     p,
					       hr_jd_master      q
					 where a.seg_segment_no = b.seg_segment_no(+)
					   and a.seg_segment_no = c.seg_segment_no(+)
					   and a.psn_id = b.id
					   and a.newtitle = c.title(+)
					   and a.seg_segment_no = d.hcd_seg_segment_no(+)
					   and a.new_jobcategory = d.codeid(+)
					   and d.hcd_codetype(+) = 'JOB'
					   and a.seg_segment_no = m.seg_segment_no(+)
					   and a.new_period_id = m.period_master_id(+)
					   and a.seg_segment_no = i.seg_segment_no(+)
					   and a.NEWCOSTALLOCATION = i.parameter_id(+)
					   and i.parameter_type(+)='HR_COSTALLOCATION'
					   and a.seg_segment_no = n.hcd_seg_segment_no(+)
					   and a.new_contract = n.codeid(+)
					   and n.hcd_codetype(+) = 'PERSONTYPE'
					   and a.seg_segment_no = r.hcd_seg_segment_no(+)
					   and a.nb_newleader = r.codeid(+)
					   and r.hcd_codetype(+) = 'FOURKINDS'
					   and a.seg_segment_no = o.seg_segment_no(+)
					   and a.new_transfer_reson_id = o.transtype_detail_id(+)
					    and a.seg_segment_no = e.seg_segment_no(+)
					   and a.new_overtime_type_id = e.hr_overtimetype_id(+)
					   and a.seg_segment_no = f.seg_segment_no(+)
					   and a.new_absence_type_id = to_number(f.hr_absencetype_id(+))
					   and a.seg_segment_no = g.seg_segment_no(+)
					   and a.new_year_type_id = g.hr_yeartype_id(+)
					   and a.seg_segment_no = q.seg_segment_no(+)
					   and a.job_id = q.jd_master_id(+)
					   and a.seg_segment_no = p.seg_segment_no(+)
					   and a.new_tw_tax_id = p.tax_id(+)
			           and a.seg_segment_no = :company_id
			             %s %s
			           order by a.validdate desc, b.id_no_sz asc
eof;
		$params = array('company_id' => $this->companyID);
		$who_where = '';
		// 根据查资料的人员的不同，组合不同的where条件
		switch($who) {
			case self::MYSELF:
				$who_where = 'and a.psn_id = :emp_seq_no';
				$params['emp_seq_no'] = $this->empSeqNo;
			break;
			case self::ASSISTANT:
				$who_where = 'and a.create_by = :user_seq_no';
				$params['user_seq_no'] = $_SESSION['user']['user_seq_no'];
				break;
			case self::ADMIN:
				break;
			default:break;
		}// end switch
		$sql = sprintf($sql, $who_where, $query_where);
		//$this->DBConn->debug =true;
		$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		if($countrow) {
			return $this->DBConn->GetOne( 'select count(1) from(' . $sql . ')', $params);
		} // end if
		//echo $sql;
		//echo 'offiset ->'.$offset.'<br>';
		//echo 'numrows ->'.$numrows.'<br>';
		$rs = $this->DBConn->SelectLimit($sql, $numrows, $offset, $params);
		return $rs->GetArray();
	} // end getOvertimeApply()

	function getNocardApply($query_where,
							$who = 'myself',
							$countrow = false,
							$numrows = -1,
							$offset = -1) {
		$sql = <<<eof
			select a.nocard_flow_sz_id as nocard_flow_seqno,
				   a.seg_segment_no,
				   a.psn_id,
				   b.id_no_sz as emp_id,
			       b.name_sz as emp_name,
			       c.segment_no_sz as dept_id,
			       c.segment_name as dept_name,
				   decode(a.shifttype,
			              '',
			              '',
			              f_nocard_msg(a.seg_segment_no, a.shifttype, '01') || ' :: ' ||
			              to_char(a.recordtime, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
			              f_nocard_msg(a.seg_segment_no, a.nocarding_id, '02')||'<hr/>')||
			       decode(a.shifttype2,
			              '',
			              '',
			              f_nocard_msg(a.seg_segment_no, a.shifttype2, '01') || ' :: ' ||
			              to_char(a.recordtime2, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
			              f_nocard_msg(a.seg_segment_no, a.nocarding_id2, '02')||'<hr/>')||
				   decode(a.shifttype3,
			              '',
			              '',
			              f_nocard_msg(a.seg_segment_no, a.shifttype3, '01') || ' :: ' ||
			              to_char(a.recordtime3, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
			              f_nocard_msg(a.seg_segment_no, a.nocarding_id3, '02')||'<hr/>')||
			       decode(a.shifttype4,
			              '',
			              '',
			              f_nocard_msg(a.seg_segment_no, a.shifttype4, '01') || ' :: ' ||
			              to_char(a.recordtime4, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
			              f_nocard_msg(a.seg_segment_no, a.nocarding_id4, '02')||'<hr/>')||
				  decode(a.shifttype5,
			              '',
			              '',
			              f_nocard_msg(a.seg_segment_no, a.shifttype5, '01') || ' :: ' ||
			              to_char(a.recordtime5, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
			              f_nocard_msg(a.seg_segment_no, a.nocarding_id5, '02')||'<hr/>')||
			       decode(a.shifttype6,
			              '',
			              '',
			              f_nocard_msg(a.seg_segment_no, a.shifttype6, '01') || ' :: ' ||
			              to_char(a.recordtime6, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
			              f_nocard_msg(a.seg_segment_no, a.nocarding_id6, '02')||'<hr/>') as nocard_desc,
				   'nocard' as apply_type,
				   a.status as flow_status,
				   to_char(a.create_date,'yyyy/mm/dd hh24:mi:ss') as create_date,
				   decode(a.status,
						  '00',
						  '暂存',
						  '01',
						  '提交',
						  '02',
						  '流程中',
						  '03',
						  '核准',
						  '04',
						  '驳回',
						  '05',
						  '作废',
						  '06',
						  '异常') as status_name
			  from hr_nocard_flow_sz a, hr_personnel_base b, gl_segment c
			 where a.seg_segment_no = b.seg_segment_no
			   and a.psn_id = b.id
			   and b.seg_segment_no = b.seg_segment_no
			   and b.seg_segment_no_department = c.segment_no
			   and a.seg_segment_no = :company_id
			   %s %s
			 order by a.recordtime desc, c.segment_no_sz asc, b.id_no_sz asc
eof;
		$params = array('company_id' => $this->companyID);
		$who_where = '';
		// 根据查资料的人员的不同，组合不同的where条件
		switch($who) {
			case self::MYSELF:
				$who_where = 'and a.psn_id = :emp_seq_no';
				$params['emp_seq_no'] = $this->empSeqNo;
			break;
			case self::ASSISTANT:
				$who_where = 'and a.create_by = :user_seq_no';
				$params['user_seq_no'] = $_SESSION['user']['user_seq_no'];
				break;
			case self::ADMIN:
				break;
			default:break;
		}// end switch
		$sql = sprintf($sql, $who_where, $query_where);
		//$this->DBConn->debug = false;
		$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		if($countrow) {
			return $this->DBConn->GetOne('select count(1) from(' . $sql . ')', $params);
		}
		$rs = $this->DBConn->SelectLimit($sql, $numrows, $offset, $params);
		return $rs->GetArray();
	} // end getNocardApply()

	function getResignApply($query_where,
							  $who = 'myself',
							  $countrow = false,
							  $numrows = -1,
							  $offset = -1) {
		$sql = <<<eof
					select a.resign_flow_sz_id as resign_flow_seqno,
					       a.seg_segment_no,
					       a.psn_id,
					       b.id_no_sz as emp_id,
					       b.name_sz as emp_name,
					       c.segment_no_sz as dept_id,
					       c.segment_name as dept_name,
					       a.out_date as resign_date,
					       decode(a.out_type,'1','离职','2','留停','离职') as out_type,
					       f_codename('RESIGNREASON',a.reason,a.seg_segment_no) as resign_reason,
					       'resign' as apply_type,
					       a.status flow_status,
					       a.create_date,
						   a.remark as emp_remark,
					       decode(a.status,'00','暂存','01','提交','02','流程中','03','核准','04','驳回','05','作废','06','异常') as status_name,
					   	   ehr_f_get_resign_comment(a.resign_flow_sz_id) as reject_comment 
					  from hr_resign_flow_sz a, hr_personnel_base b, gl_segment c
					 where a.seg_segment_no = b.seg_segment_no
					   and a.psn_id = b.id
					   and b.seg_segment_no = b.seg_segment_no
					   and b.seg_segment_no_department = c.segment_no
					   and a.seg_segment_no = :company_id %s %s
					 order by a.out_date desc, c.segment_no_sz asc, b.id_no_sz asc
eof;
		$params = array('company_id' => $this->companyID);
		$who_where = '';
		// 根据查资料的人员的不同，组合不同的where条件
		switch($who) {
			case self::MYSELF:
				$who_where = 'and a.psn_id = :emp_seq_no';
				$params['emp_seq_no'] = $this->empSeqNo;
			break;
			case self::ASSISTANT:
				$who_where = 'and a.create_by = :user_seq_no';
				$params['user_seq_no'] = $_SESSION['user']['user_seq_no'];
				break;
			case self::ADMIN:
				break;
			default:break;
		}// end switch
		$sql = sprintf($sql, $who_where, $query_where);
		//$this->DBConn->debug =true;
		$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		if($countrow) {
			return $this->DBConn->GetOne( 'select count(1) from(' . $sql . ')', $params);
		} // end if
		//echo $sql;
		//echo 'offiset ->'.$offset.'<br>';
		//echo 'numrows ->'.$numrows.'<br>';
		$rs = $this->DBConn->SelectLimit($sql, $numrows, $offset, $params);
		return $rs->GetArray();
	} // end getOvertimeApply()

	function GetNocardReason($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	   select nocarding_id, codeid || ' ' || reason nocard_reason
		     from hr_nocarding
		    where is_active = 'Y'
		      and seg_segment_no = :company_id
		    order by nocarding_id
eof;
        //$this->DBConn->debug = true;
        //$this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->CacheGetArray($GLOBALS['config']['cache_left_time'],
        									$sql,
        									array('company_id' => $this->companyID));
    }// end GetNocardReason()

    function GetResignReason($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	   select codeid, codevalue
			  from hr_codedetail
			 where hcd_seg_segment_no = :company_id
			   and hcd_codetype = 'RESIGNREASON'
			 order by line_no
eof;
        //$this->DBConn->debug = true;
        //$this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array('company_id' => $this->companyID));
    }// end GetResignReason()

    function GetShifttype($fetchmode = ADODB_FETCH_NUM) {
    	$sql = <<<eof
    	   select PARAMETER_ID, PARAMETER_VALUE
			  from PB_PARAMETERS
			 where PARAMETER_TYPE = 'HR_CARDING4'
			   and seg_segment_no = :company_id
			 ORDER BY PARAMETER_ID
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->CacheGetArray($GLOBALS['config']['cache_left_time'],$sql, array('company_id' => $this->companyID));
    }// end GetShifttype()


    /**
	 * 暂存或提交忘刷申请
	 */
	function SaveNocardApply($userseqno,
							$dept_id,
							$recordtime1,
							$recordtime2,
							$recordtime3,
							$recordtime4,
							$recordtime5,
							$recordtime6,
							$nocard_reason1,
							$nocard_reason2,
							$nocard_reason3,
							$nocard_reason4,
							$nocard_reason5,
							$nocard_reason6,
							$shifttype1,
							$shifttype2,
							$shifttype3,
							$shifttype4,
							$shifttype5,
							$shifttype6,
							$remark,
							$tmp_save,
							$emp_seqno = null) {
		$emp_seqno = is_null($emp_seqno) ? $this->empSeqNo : $emp_seqno;
		$result = array('msg' => '',
						'flow_seqno' => '',
						'is_success' => '');
		$stmt1 = 'begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_save_nocard_apply(pi_seg_segment_no => :in_companyid,pi_psn_id => :in_empseqno,pi_recordtime1 => :in_recordtime1,pi_recordtime2 => :in_recordtime2,pi_recordtime3 => :in_recordtime3,pi_recordtime4 => :in_recordtime4,pi_recordtime5 => :in_recordtime5,pi_recordtime6 => :in_recordtime6,pi_nocarding_id1 => :in_nocarding_id1,pi_nocarding_id2 => :in_nocarding_id2,pi_nocarding_id3 => :in_nocarding_id3,pi_nocarding_id4 => :in_nocarding_id4,pi_nocarding_id5 => :in_nocarding_id5,pi_nocarding_id6 => :in_nocarding_id6,pi_shifttype1 => :in_shifttype1,pi_shifttype2 => :in_shifttype2,pi_shifttype3 => :in_shifttype3,pi_shifttype4 => :in_shifttype4,pi_shifttype5 => :in_shifttype5,pi_shifttype6 => :in_shifttype6,pi_remark => :in_remark,po_errmsg => :out_msg,po_nocard_flow_sz_id  => :out_nocard_flowseqno,po_success => :out_issuccess,pi_submit => :in_submit); end; end;';
		//$this->DBConn->debug = true;
		// 同一笔申请中的忘记刷卡只有一个原因，传到后台用的是 $nocard_reason1 add by dennis 2014/11/06
		
		for($i=1;$i<7;$i++)
		{
		    $v = 'nocard_reason'.$i;
		    if (${$v} != ""){
		        $nocard_reason1 = ${$v};
		        break;
		    }
		}
		// end add
		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $this->companyID, 'in_company_id1', 10);
		$this->DBConn->InParameter($stmt, $userseqno, 		'in_user_seqno', 10);
		$this->DBConn->InParameter($stmt, $this->companyID, 'in_companyid', 10);
		$this->DBConn->InParameter($stmt, $emp_seqno, 		'in_empseqno', 10);
		$this->DBConn->InParameter($stmt, $recordtime1, 	'in_recordtime1', 100);
		$this->DBConn->InParameter($stmt, $recordtime2, 	'in_recordtime2', 100);
		$this->DBConn->InParameter($stmt, $recordtime3, 	'in_recordtime3', 100);
		$this->DBConn->InParameter($stmt, $recordtime4, 	'in_recordtime4', 100);
		$this->DBConn->InParameter($stmt, $recordtime5, 	'in_recordtime5', 100);
		$this->DBConn->InParameter($stmt, $recordtime6, 	'in_recordtime6', 100);
		$this->DBConn->InParameter($stmt, $nocard_reason1,	'in_nocarding_id1', 10);
		$this->DBConn->InParameter($stmt, $nocard_reason2,	'in_nocarding_id2', 10);
		$this->DBConn->InParameter($stmt, $nocard_reason3,	'in_nocarding_id3', 10);
		$this->DBConn->InParameter($stmt, $nocard_reason4,	'in_nocarding_id4', 10);
		$this->DBConn->InParameter($stmt, $nocard_reason5,	'in_nocarding_id5', 10);
		$this->DBConn->InParameter($stmt, $nocard_reason6,	'in_nocarding_id6', 10);
		$this->DBConn->InParameter($stmt, $shifttype1, 		'in_shifttype1', 10);
		$this->DBConn->InParameter($stmt, $shifttype2, 		'in_shifttype2', 10);
		$this->DBConn->InParameter($stmt, $shifttype3, 		'in_shifttype3', 10);
		$this->DBConn->InParameter($stmt, $shifttype4, 		'in_shifttype4', 10);
		$this->DBConn->InParameter($stmt, $shifttype5, 		'in_shifttype5', 10);
		$this->DBConn->InParameter($stmt, $shifttype6, 		'in_shifttype6', 10);
		$this->DBConn->InParameter($stmt, $remark, 			'in_remark', 4000);
		$this->DBConn->InParameter($stmt, $tmp_save,		'in_submit', 2);
		$this->DBConn->OutParameter($stmt, $result['msg'],	'out_msg', 2000);
		$this->DBConn->OutParameter($stmt, $result['flow_seqno'], 'out_nocard_flowseqno', 9);
		$this->DBConn->OutParameter($stmt, $result['is_success'], 'out_issuccess', 2);

		$this->DBConn->StartTrans(); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans(); // end transaction
		return $result;
	} // end SaveNocardApply()

	/**
	 * 暂存或提交离职留停申请
	 */
	function SaveResignApply($userseqno,
						    $dept_id,
							$resign_date,
							$resign_reason,
						    $out_type,
						    $remark,
						    $tmp_save,
						    $emp_seqno = null) {
		$emp_seqno = is_null($emp_seqno) ? $this->empSeqNo : $emp_seqno;
		//$this->DBConn->debug = 1;
		$result = array('msg' => '',
						 'flow_seqno' => '',
						 'is_success' => '');
		$stmt1 = 'begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_save_resign_apply(pi_seg_segment_no => :in_companyid,pi_psn_id => :in_empseqno,pi_out_type => :in_out_type,pi_out_date => :in_out_date,pi_reason => :in_reason,pi_remark => :in_remark,po_errmsg => :out_msg,po_resign_flow_sz_id  => :out_resign_flowseqno,po_success => :out_issuccess,pi_submit => :in_submit); end; end;';
		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $this->companyID, 'in_company_id1', 10);
		$this->DBConn->InParameter($stmt, $userseqno, 'in_user_seqno', 10);
		$this->DBConn->InParameter($stmt, $this->companyID, 'in_companyid', 10);
		$this->DBConn->InParameter($stmt, $emp_seqno, 'in_empseqno', 10);
		//$this->DBConn->InParameter($stmt, $dept_id, 'in_dept_id', 10);
		$this->DBConn->InParameter($stmt, $resign_date, 'in_out_date', 50);
		$this->DBConn->InParameter($stmt, $resign_reason, 'in_reason', 10);
		$this->DBConn->InParameter($stmt, $out_type, 'in_out_type', 10);

		$this->DBConn->InParameter($stmt, $remark, 'in_remark', 4000);
		$this->DBConn->InParameter($stmt, $tmp_save, 'in_submit', 2);

		$this->DBConn->OutParameter($stmt, $result['msg'], 'out_msg', 2000);
		$this->DBConn->OutParameter($stmt, $result['flow_seqno'], 'out_resign_flowseqno', 9);
		$this->DBConn->OutParameter($stmt, $result['is_success'], 'out_issuccess', 2);

		$this->DBConn->StartTrans(); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans(); // end transaction

		return $result;

	} // end SaveResignApply()
}// end class AresTrans
