<?php
/**
 * for Compal 
 *  Create By: Dennis
 *  Create Date: 2008-12-25 11:20
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresBottom5.class.php $
 *  $Id: AresBottom5.class.php 3584 2013-11-08 02:09:47Z dennis $
 *  $LastChangedDate: 2013-11-08 10:09:47 +0800 (周五, 08 十一月 2013) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3584 $  
 \****************************************************************************/
class AresBottom5 {
	/**
	 * Company ID
	 *
	 * @var string
	 */
	protected $_companyId;
	
	/**
	 * PA 填写人 user seq no, 初评主管
	 *
	 * @var string
	 */
	protected $_userSeqNo;
	
	/**
	 * Database Connection Handler
	 *
	 * @var Database Connection Handler
	 */
	private $_dBConnection;
	
	/**
	 * @var string 程式代码
	 *
	 */
	const APP_NO = 'MDNC901';
		
	/**
	 * Constructor of class AresBottom5
	 *
	 * @param string $companyid
	 * @param string $managerid
	 */
	function __construct($companyid,$user_seqno) {
		global $g_db_sql;
		$this->_dBConnection = &$g_db_sql;
		$this->_companyId = $companyid;
		$this->_userSeqNo = $user_seqno;
		//$this->_dBConnection->debug = true;
	}// end class constructor
	
	public function getLeastPAPeriod()
	{
		$sql = <<<eof
		select pa_period_seqno
		  from ehr_pa_period_v
		 where company_id = :company_id
		   and to_char(sysdate, 'YYYYMMDD') >= to_char(pa_begin_date, 'YYYYMMDD')
		   and to_char(sysdate, 'YYYYMMDD') <= to_char(pa_end_date, 'YYYYMMDD')
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetOne($sql,array('company_id'=>$this->_companyId));
	}// end getLeastPAPeriod()
	
	public function getPAPeriodList($pa_period_seqno = null)
	{
		$where = is_null($pa_period_seqno) ? '' : ' and evaluation_period_id = '.$pa_period_seqno;
		$sql = <<<eof
			select evaluation_period_id   as pa_period_seqno,
			       evaluation_period_no ||'-'||
			       evaluation_period_desc as pa_period_desc
			  from hr_evaluation_periods
			 where seg_segment_no = :company_id
			 $where
			 order by evaluation_period_no desc
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId));
	}// end getPAPeriodList()
	
	public function getBottom5List($pa_period_seqno,$user_empseqno,$whoami='mgr3')
	{
		$who = strtolower($whoami) == 'mgr3' ?  'third' : 'first';
		$where = $who == 'first' ? ' and confirm_flag = \'Y\' ' : '';
		$sql = <<<eof
		select list_id                    as bottom5_seqno,
		       appraisal_id               as pa_form_seqno,
	           a.psn_id                   as emp_seqno,
	           emp_no                     as emp_id,
	           emp_name                   as emp_name,
	           a.evaluation_period_id     as pa_period_seqno,
	           evaluation_period_no       as pa_period_id,
	           evaluation_period_desc     as pa_period_desc,
	           appraisal_rank             as pa_rank,
	           appraisal_result           as pa_result,
	           a.mgr1_score               as mgr1_score,
	           a.mgr1_rank                as mgr1_rank,
	           a.mgr2_score               as mgr2_score,
	           a.mgr2_rank                as mgr2_rank,
	           a.mgr3_score               as mgr3_score,
	           a.mgr3_rank                as mgr3_rank,
	           dept_no                    as dept_id,
	           dept_name                  as dept_name,
	           jobstatus_name             as job_status,
	           jobtitle_name              as pos_desc,
	           joblevel                   as pos_level,
	           jobrank                    as pos_grade,
	           nation_value               as national_desc,
	           before_last_appraisal_rank as pre_pa_rank,
	           last_appraisal_rank        as last_pa_rank,
	           agreement_end_date         as con_end_date,
	           agreement_count            as con_cnt,
	           last_is_bottom5            as last_is_bt5,
	           confirm_flag               as mgr_confirm_flag,
	           md_remark                  as mgr_comments,
	           /*b.psn_id                   as imp_form_flag,
	           b.assessment_result        as improve_form_status,
       		   d.close_type2              as is_closed */
       		   decode(b.psn_id,null,'N','Y') as imp_form_flag,
       		   b.improve_form_status		 as improve_form_status,
       		   nvl(d.close_type2,'N')		 as is_closed  ----modify by liuping
	      from hr_bt5_out_list_v         a, 
	           hr_bt5_out_record         b,
	           hr_evaluation_subordinate c,
	           hr_eva_close_tw           d
	     where a.seg_segment_no = b.seg_segment_no(+)
	       and a.psn_id = b.psn_id(+)
	       and a.evaluation_period_id = b.evaluation_period_id(+)
	       and a.seg_segment_no = c.seg_segment_no
	       and a.psn_id = c.personnel_id
	       and a.evaluation_period_id = c.evaluation_period_id
	       and a.seg_segment_no = d.seg_segment_no(+)
	       and a.evaluation_period_id = d.evaluation_period_id(+)
	       and c.manager_%s_id = :mgr_emp_seqno
	       and a.evaluation_period_id = :pa_period_seqno
	       and a.seg_segment_no = :company_id
	       and b.record_type(+)    = '1'
	       $where
	       order by appraisal_rank asc
eof;
		 /* 仅查通知用联  and b.record_type    = '1'  add by dennis 20090507*/
		//$this->_dBConnection->debug = true;		
		$this->_registerUser();
		$sql = sprintf($sql,$who);
		//echo $sql;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('pa_period_seqno'=>$pa_period_seqno,
													     'company_id'=>$this->_companyId,
													     'mgr_emp_seqno'=>$user_empseqno));
	}// end getBottom5List()
	
	public function update($bottom5_seqno,$mgr_confirm,$mgr_comment) {
		$sql = <<<eof
			update hr_bt5_out_list
			   set confirm_flag    = :v_mgr_confirm_flag,
			       md_remark       = :v_mgr_remark,
			       update_date     = sysdate,
			       update_by       = :v_user_seqno,
			       update_program  = :v_app_no
			 where list_id = :v_bottom5_seqno
			   and seg_segment_no = :v_company_id
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->Execute($sql,array('v_mgr_confirm_flag'=>$mgr_confirm,
												 'v_mgr_remark'=>$mgr_comment,
												 'v_user_seqno'=>$this->_userSeqNo,
												 'v_app_no'=>self::APP_NO,
												 'v_bottom5_seqno'=>$bottom5_seqno,
												 'v_company_id'=>$this->_companyId));

		$ok = $this->_dBConnection->Affected_Rows();
		
		if (1 != $ok)
		{
			return $this->_dBConnection->ErrorMsg();
		}// end if
		return $ok;		
	}// end update
	
	private function _registerUser() {
        $plsql_stmt = 'begin pk_erp.p_set_date(sysdate);pk_erp.p_set_segment_no(:company_id);pk_erp.p_set_username(:v_user_seq_no);end;';
        $this->_dBConnection->Execute($plsql_stmt, array ('company_id'  => $this->_companyId,
                                                   		  'v_user_seq_no' =>$this->_userSeqNo));
    } // end function RegisterUser()
    
    public function insertImproveForm($pa_period_seqno,
    								  $emp_seqno,
    								  $reason_item,
    								  $begin_date,
    								  $end_date,
    								  $ass_result,
    								  $other_desc)
    {
    	$sql = <<<eof
    		insert into hr_bt5_out_record
			  (record_id,
			   seg_segment_no,
			   evaluation_period_id,
			   psn_id,
			   record_type,
			   improve_matters,
			   improve_date_start,
			   improve_date_end,
			   assessment_result,
			   other_desc,
			   create_by,
			   create_date,
			   create_program,
			   improve_form_status)
			values
			  (hr_bt5_out_record_s.nextval,
			   :v_company_id,
			   :v_pa_period_seqno,
			   :v_emp_seqno,
			   1,
			   :v_reason_item,
			   :v_begin_date,
			   :v_end_date,
			   :v_ass_result,
			   :v_other_desc,
			   :v_create_by,
			   sysdate,
			   :v_create_program,
			   1)
eof;
		//$this->_dBConnection->debug = true;
		$ok = $this->_dBConnection->Execute($sql,array('v_company_id'=>$this->_companyId,
													   'v_pa_period_seqno'=>$pa_period_seqno,
													   'v_emp_seqno'=>$emp_seqno,
													   'v_reason_item'=>$reason_item,
													   'v_begin_date'=>$begin_date,
													   'v_end_date'=>$end_date,
													   'v_ass_result'=>$ass_result,
													   'v_create_by'=>$this->_userSeqNo,
													   'v_create_program'=>self::APP_NO,
													   'v_other_desc'=>$other_desc));
		
		if ($ok)
		{
			return 1;
		}else{
			return $this->_dBConnection->ErrorMsg();
		}// end if
		
    }// end insertImproveForm()
    
    public function updateImproveForm($imp_form_seqno,
    								  $reason_item,
    								  $begin_date,
    								  $end_date,
    								  $ass_result,
    								  $other_desc) {
    	$sql = <<<eof
		update hr_bt5_out_record
		   set improve_matters    = :improve_reason,
		       improve_date_start = to_date(:begin_date,'yyyy-mm-dd'),
		       improve_date_end   = to_date(:end_date,'yyyy-mm-dd'),
		       assessment_result  = :ass_result,
		       improve_form_status = '1',  ----add by liuping 2009/06/19
		       other_desc         = :other_desc,
		       update_by          = :update_by,
		       update_date        = sysdate,
		       update_program     = :update_program
		 where record_id = :imp_form_seqno
eof;
		//$this->_dBConnection->debug = 1;
		$ok = $this->_dBConnection->Execute($sql,array('improve_reason'=>$reason_item,
													   'begin_date'=>$begin_date,
													   'end_date'=>$end_date,
													   'ass_result'=>$ass_result,
													   'other_desc'=>$other_desc,
													   'update_by'=>$this->_userSeqNo,
													   'update_program'=>self::APP_NO,
													   'imp_form_seqno'=>$imp_form_seqno));
		
		if ($ok)
		{
			return 1;
		}else{
			return $this->_dBConnection->ErrorMsg();
		}// end if
    }// end updateImproveForm()
        
    public function getBottom5EmpInfo($bottom_senqo,$pa_period_seqno,$emp_seqno) {
    	$sql = <<<eof
    		select list_id                as bottom5_seqno,
			       appraisal_id           as pa_form_seqno,
			       a.psn_id               as emp_seqno,
			       emp_no                 as emp_id,
			       emp_name               as emp_name,
			       a.evaluation_period_id as pa_period_seqno,
			       evaluation_period_no   as pa_period_id,
			       evaluation_period_desc as pa_period_desc,
			       appraisal_rank         as pa_rank,
			       appraisal_result       as pa_result,
			       dept_no                as dept_id,
			       dept_name              as dept_name,
			       jobstatus_name         as job_status,
			       jobtitle_name          as pos_desc,
			       joblevel               as pos_level,
			       jobrank                as pos_grade,
			       b.record_id            as imp_form_seqno,
			       b.improve_matters      as improve_reason,
			       b.improve_date_start   as begin_date,
			       b.improve_date_end     as end_date,
			       b.assessment_result    as ass_result,
			       b.other_desc			  as other_desc,
			       b.improve_form_status  as improve_form_status
			       --b.assessment_result    as improve_form_status ---modify by liuping
			  from hr_bt5_out_list_v a, hr_bt5_out_record b
			 where a.seg_segment_no = b.seg_segment_no(+)
			   and a.psn_id = b.psn_id(+)
			   and a.evaluation_period_id = b.evaluation_period_id(+)
			   and a.evaluation_period_id = :pa_period_seqno
			   and a.confirm_flag = 'Y'
			   and a.seg_segment_no = :company_id
			   and a.psn_id = :emp_seqno
			   and a.list_id = :bottom_seqno
			   and b.record_type(+)=1
eof;
		//$this->_dBConnection->debug = true;
		$this->_registerUser();
		return $this->_dBConnection->GetRow($sql,array('company_id'=>$this->_companyId,
												'pa_period_seqno'=>$pa_period_seqno,
												'emp_seqno'=>$emp_seqno,
												'bottom_seqno'=>$bottom_senqo));
    }// end getBottom5EmpInfo()
    
	
}// end class AresBottom5

?>