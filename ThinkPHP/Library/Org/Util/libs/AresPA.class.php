<?php
/**
 * Performance Assessment Module
 * 
 *  员工可以填单的条件
 *    1. 在员工自评考核期间内
 *    and
 *    2. 未在不考人员名单
 *    and
 *    3. 接班人已经维护
 *    and
 *    4. 资料只是暂存
 *    5. 或者是Interview 回签 (form_status == 4)
 * 
 *  初评主管(MGR1)可以填单的条件
 * 	  1.员工初评提交
 *    2.员工 interview 提交 (form_status == 5)
 *    3.在初评起讫时间内
 * 
 * 	复评主管可以填单条件
 *    1.初评主管提交
 *  Create By: Dennis ☆☆☆☆☆
 *  Create Date: 2008-11-18 16:10
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresPA.class.php $
 *  $Id: AresPA.class.php 3773 2014-06-19 07:46:53Z dennis $
 *  $LastChangedDate: 2014-06-19 15:46:53 +0800 (周四, 19 六月 2014) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3773 $  
 \****************************************************************************/
class AresPA {
	
	/**
	 *  考核单状态参考:
	 *  1_未提交，暫存（填寫一半）
	 *  2_自評提交
	 *  3_初評主管未提交
	 *  4_初評主管提交且回送員工複簽
	 *  5_員工已複簽並提交
	 *  6_初評主管填寫面談備註暫存
	 *  7_初評主管填寫面談備註並提交
	 *  8_複評主管填寫並暫存
	 *  9_複評主管提交
	 *  10_核定主管已提交
	 *  11_HR關帳
	 * @var array	
	 */
	/*
	const NULL_STATUS       				 = NULL;
	const EMP_TEMP_SUBMIT   				 = 1;
	const EMP_SUBMIT_FORM					 = 2;
	const MGR1_TEMP_SUBMIT					 = 3;
	const MGR1_SUBMIT_FORM					 = 4;
	const EMP_SUBMIT_INTERVIEW  			 = 5;
	const MGR1_TEMP_SUBMIT_INTERVIEW_COMMENT = 6;
	const MGR1_SUBMIT_FORM_INTERVIEW_COMMENT = 7;
	const MGR2_TEMP_SUBMIT					 = 8;
	const MGR2_SUBMIT_FORM					 = 9;
	*/
	/**
	 * 一二階主管是同一人時
	 * @var number
	 */
	/*
	const MGR2_SUBMIT_FORM_INTERVIEW_COMMENT = 9;
	const MGR3_SUBMIT_FORM					 = 10;
	*/
	/**
	 * 一二三階主管是同一人時
	 * @var number
	 */
	/*
	const MGR3_SUBMIT_FORM_INTERVIEW_COMMENT = 10;
	const HR_CLOSED							 = 11;
	*/
		
	private $_formStatus = array('emp' =>array('tempsubmit'=>1,
											   'submitform'=>2,
											   'submitinterview'=>5),
								 'mgr1'=>array('tempsubmit'=>3,
											   'submitform'=>4,
											   'tempsubmit_interview_comment'=>6,
											   'submitform_interview_comment'=>7),
								 'mgr2'=>array('tempsubmit'=>8,
											   'submitform'=>9,
											   'submitform_interview_comment'=>9),
								 'mgr3'=>array('submitform'=>10,
								 			   'submitform_interview_comment'=>10));
	/**
	 * Company ID
	 *
	 * @var string
	 */
	protected $_companyId;
	
	/**
	 * PA 填写人 emp seq no, 可能是员工,初评主管,复评主管,核定主管
	 *
	 * @var string
	 */
	protected $_empSeqNo;
	
	/**
	 * Database Connection Handler
	 *
	 * @var Database Connection Handler
	 */
	protected $_dBConnection;
	
	/**
	 * 分页工具列  add by dennis 2009119
	 *
	 * @var string
	 */
	private $_pagerToolbar;
	
	/**
	 * 可接任时间清单,多语 Key
	 * 
	 * @var string
	 */
	const APPROVE_TIME_LIST = 'HR_INHERITANCE_PEOPLE_V.APPROVE_TIME';
	/**
	 * 工作表现,多语 Key
	 * @var string
	 */
	const JOB_REPRESENT_LIST = 'HR_INHERITANCE_PEOPLE_V.JOB_REPRESENT';
	
	/**
	 * 发展潜力,多语 Key
	 * @var string
	 */
	const POSSIBILITIES_LIST = 'HR_INHERITANCE_PEOPLE_V.POSSIBILITIES';
	
	/**
	 * 挑多少笔历史考核成绩
	 * @var int
	 */
	const PA_HIS_ROW_LIMIT = 4;
	
	/**
	 * 程式名稱
	 * @var string
	 */
	const APP_NAME = 'PA_FORM';
	
	/**
	 * Data Cache Seconds
	 * @var int
	 */
	const DATA_CACHE_SECONDS = 0;
	
	/**
	 * Constructor of class AresPA
	 *
	 * @param string $companyid
	 * @param string $managerid
	 */
	function __construct($companyid,$empseqno) {
		global $g_db_sql;
		$this->_dBConnection = &$g_db_sql;
		$this->_companyId = $companyid;
		$this->_empSeqNo = $empseqno;
		//$this->_dBConnection->debug = true;
	}// end class constructor
	
	/**
	 * check 绩效考核模组是否有安装
	 *
	 * @return boolean
	 * @author Dennis 20090531
	 */
	public function iSPAModuleInstalled()
	{
		$sql = 'select 1 from user_views where view_name = upper(:view_name)';
		//$this->_dBConnection->debug = true;
		return $this->_dBConnection->GetOne($sql,array('view_name'=>'EHR_PA_FORM_V'));
	}// end iSPAModuleInstalled()
	
	/**
	 * Get 指定考核期间起讫日期资料
	 * 
	 * @param mixed $pa_period_seqno 是布尔值 的时候挑全部考核期间
	 * @return array
	 * @author Dennis 
	 */
	public function getPAPeriod($pa_period_seqno)
	{
		$where = is_bool($pa_period_seqno) ? '' : 'and pa_period_seqno = '.$pa_period_seqno; 
		$sql = <<<eof
			select pa_period_seqno,
			       pa_period_desc,
			       pa_period_id,
			       pa_begin_date,
			       pa_end_date,
			       emp_begin_date,
			       emp_end_date,
			       mgr1_begin_date,
			       mgr1_end_date,
			       mgr2_begin_date,
			       mgr2_end_date,
			       mgr3_begin_date,
			       mgr3_end_date,
			       stts_begin_date,
			       stts_end_date
			  from ehr_pa_period_v
			 where company_id = :company_id
			 $where
			 order by pa_period_id desc
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dBConnection->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->_companyId));
	}// end getPAPeriod()
	
	/**
	 * 根据考核期间查询指定员工(default 查login user)考核成绩
	 *
	 * @param number $pa_period_seqno
	 * @param number $emp_seqno
	 * @return array
	 * @author Dennis
	 */
	public function getPAScore($pa_period_seqno=null,$emp_seqno=null)
	{
		$emp_seqno = is_null($emp_seqno) ? $this->_empSeqNo : $emp_seqno;
		$where = empty($pa_period_seqno) ? '' : ' and pa_period_seqno = '.$pa_period_seqno;
		$sql = <<<eof
			select pa_year,
			       pa_form_seqno,
			       pa_period_seqno,
			       pa_period_id,
			       pa_period_desc,
			       mgr3_score as pa_score,
			       mgr3_rank as pa_rank
			  from ehr_pa_emp_list_v
			 where company_id = :company_id
			   and emp_seqno = :emp_seqno
			   and form_status >=10
			   $where
eof;
		//$this->_dBConnection->debug = true;
        $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetArray($sql,array('emp_seqno'=>$emp_seqno,
                                                         'company_id'=>$this->_companyId));		
	}// end getPAScore()
		
	/**
	 * Get 考核员工的基本信息
	 *
	 * @param number $pa_form_seqno 考核单单号
	 * @return array
	 * @author Dennis 2008-11-13
	 */
	public function getPAEmpInfo($pa_form_seqno)
	{
		$sql = <<<eof
		select pa_period_seqno,
		       pa_period_id,
		       pa_period_desc,
		       pa_begin_date,
		       pa_end_date,
		       mgr1_begin_date,
		       mgr1_end_date,
		       mgr2_begin_date,
		       mgr2_end_date,
		       mgr3_begin_date,
		       mgr3_end_date,
		       stts_begin_date,
		       stts_end_date,
		       pa_emp_seqno,
		       pa_emp_id,
		       pa_emp_name,
		       to_char(emp_submit_date,'yyyy/mm/dd hh24:mi:ss') as emp_submit_date,
		       to_char(emp_confirm_date,'yyyy/mm/dd hh24:mi:ss') as emp_confirm_date,
		       mgr1_emp_name,
		       to_char(mgr1_submit_date,'yyyy/mm/dd hh24:mi:ss') as mgr1_submit_date,
		       to_char(mgr1_confirm_date,'yyyy/mm/dd hh24:mi:ss') as mgr1_confirm_date,
		       mgr2_emp_name,
		       to_char(mgr2_submit_date,'yyyy/mm/dd hh24:mi:ss') as mgr2_submit_date,
		       mgr3_emp_name,
		       to_char(mgr3_submit_date,'yyyy/mm/dd hh24:mi:ss') as mgr3_submit_date,
		       join_date,
		       job_date,
		       title_desc,
		       dept_id,
		       dept_name,
		       emp_goal,
		       emp_achieve_goal,
		       approve_rank_remark,
		       cast(pk_crypt_sz.decryptN(approve_rank_seqno) as number) as approve_rank_seqno,
		       pa_att_filename,
		       pa_att_filepath,
		       form_status,
		       pa_year,
		       pa_score,
		       gm_score,
		       pk_performance.get_level_desc(company_id, emp_level_seqno)  as emp_level,
		       pk_performance.get_level_desc(company_id, mgr1_level_seqno) as mgr1_level,
		       pk_performance.get_level_desc(company_id, mgr2_level_seqno) as mgr2_level,
		       pk_performance.get_level_desc(company_id, mgr3_level_seqno) as mgr3_level,
		       mgr3_rank
			   , y.sly_grade
		  from ehr_pa_form_v X, hr_personnel y
		 where pa_form_seqno = :pa_form_seqno
		   and company_id    = :company_id
		   and x.company_id  = y.seg_segment_no
		   and x.pa_emp_seqno = y.id
eof;
        //$this->_dBConnection->debug = true;
        $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetRow($sql,array('pa_form_seqno'=>$pa_form_seqno,
                                                       'company_id'=>$this->_companyId));
	}// end getPAEmpInfo()
	
	public function isNeededMaintain($pa_period_seqno)
	{
		$sql = <<<eof
		select 1
		  from hr_evaluation_inheritor
		 where evaluation_period_id = :pa_period_seqno
		   and psn_id = :emp_seqno
eof;
		return $this->_dBConnection->GetOne($sql,array('pa_period_seqno'=> $pa_period_seqno,
													   'emp_seqno'      => $this->_empSeqNo));		
	}
	
	/**
	 * 根据考核期间代码挑被考核人的基本资料
	 *
	 * @param  number $pa_period_seqno 考核期间代码
	 * @return array
	 */
	public function getPAEmpInfoByPeriod($pa_period_seqno)
	{
		$sql = <<<eof
		select pa_period_seqno,
		       pa_period_id,
		       pa_period_desc,
		       pa_begin_date,
		       pa_end_date,
		       stts_begin_date,
		       stts_end_date,
		       pa_emp_seqno,
		       pa_emp_id,
		       pa_emp_name,
		       to_char(emp_submit_date,'yyyy/mm/dd hh24:mi:ss') as emp_submit_date,
		       mgr1_emp_name,
		       to_char(mgr1_submit_date,'yyyy/mm/dd hh24:mi:ss') as mgr1_submit_date,
		       mgr2_emp_name,
		       to_char(mgr2_submit_date,'yyyy/mm/dd hh24:mi:ss') as mgr2_submit_date,
		       mgr3_emp_name,
		       to_char(mgr3_submit_date,'yyyy/mm/dd hh24:mi:ss') as mgr3_submit_date,
		       join_date,
		       job_date,
		       title_desc,
		       dept_id,
		       dept_name,
		       emp_goal,
		       approve_rank_remark,
		       cast(pk_crypt_sz.decryptN(approve_rank_seqno) as number) as approve_rank_seqno,
		       pa_att_filename,
		       pa_att_filepath,
		       form_status,
		       pa_year
		  from ehr_pa_form_v
		 where pa_period_seqno= :pa_period_seqno
		   and company_id = :company_id
		   and pa_emp_seqno = :emp_seqno
eof;
        //$this->_dBConnection->debug = true;
        $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetRow($sql,array('pa_period_seqno'=>$pa_period_seqno,
                                                       'company_id'=>$this->_companyId,
                                                       'emp_seqno'=>$this->_empSeqNo));
	}// end getPAEmpInfoByPeriod()
	
	/**
	 * Get 员工指定时段奖惩记录的次数统计
	 *
	 * @param string $begin_date
	 * @param string $end_date
	 * @return array
	 * @author Dennis 2008-11-13
	 * 
	 */
	public function getRewardsPunishment($emp_seqno,$begin_date,$end_date)
	{
		$sql = <<<eof
		select rewards_id, rewards_name, count(1) as cnt
		  from ehr_emp_rewards_v
		 where company_id = :company_id
		   and emp_seq_no = :emp_seqno
		   and occur_date >= to_date(:begin_date,'yyyy-mm-dd') 
		   and occur_date < to_date(:end_date,'yyyy-mm-dd')
		 group by rewards_id, rewards_name
eof;
        //$this->_dBConnection->debug = true;
        $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetArray($sql,array('begin_date'=>$begin_date,
							                             'end_date'=>$end_date,
							                             'company_id'=>$this->_companyId,
							                             'emp_seqno'=>$emp_seqno));		
	}// end getRewardsPunishment()
	
	/**
	 * Get 员工指定时段内请假状况统计(时数，工时天数)
	 *
	 * @param string $begin_date
	 * @param string $end_date
	 * @return array
	 * @author Dennis 2008-11-13
	 */
	public function getAbsSummary($emp_seqno,$begin_date,$end_date)
	{
		$sql = <<<eof
		select absence_id, absence_name, sum(hours) as hours,sum(work_days) as days
		  from ehr_absence_v
		 where company_id = :company_id
		   and emp_seq_no = :emp_seqno
		   and my_day >= to_date(:begin_date,'yyyy-mm-dd') 
           and my_day < to_date(:end_date,'yyyy-mm-dd')
		 group by absence_id, absence_name
eof;
        //$this->_dBConnection->debug = true;
        $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetArray($sql,
                                              array('begin_date'=>$begin_date,
                                                    'end_date'=>$end_date,
                                                    'company_id'=>$this->_companyId,
                                                    'emp_seqno'=>$emp_seqno));
	}//end getAbsSummary()
	
	/**
	 * Get 员工指定时段内请假状况统计(时数，工时天数) 帛汉客制程式
	 *
	 * @param string $begin_date
	 * @param string $end_date
	 * @return array
	 * @author Hunk 2015-12-10
	 */
	public function getAbsSummary_new($emp_seqno,$evaluation_period_id)
	{
		$sql = <<<eof
		select abs_score, early_score, late_score, sick_score, things_score, jc_dagong, jc_xiaogong, jc_daguo, jc_xiaoguo,
			   abs_score+early_score+late_score+sick_score+things_score ab_sum_score,
			   jc_dagong+jc_xiaogong+jc_daguo+jc_xiaoguo jc_sum_score
		  from xxboth_hr_jxkz_score a
		 where a.psn_id = :emp_seqno
		   and a.seg_seg_segment_no = :company_id
		   and a.evaluation_period_id = :evaluation_period_id
eof;
        //$this->_dBConnection->debug = true;
        $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetRow($sql,
                                              array('evaluation_period_id'=>$evaluation_period_id,
                                                    'company_id'=>$this->_companyId,
                                                    'emp_seqno'=>$emp_seqno));
	}//end getAbsSummary()
	
	/**
	 * Get 前 n 次的考核成绩
	 *
	 * @return array
	 * @author Dennis 2008-11-13 last update 2009-05-06
	 */
	public function getPAHis($emp_seqno,$fetch_mode = ADODB_FETCH_ASSOC) {
		/*
		$sql = <<<eof
		select *
		  from (select a.pa_year,
				       b.eva_period_type_desc as pa_type_desc,
				       a.pa_period_desc,
				       a.pa_score,
				       a.pa_rank,
				       rownum as row_num
				  from ehr_pa_emp_list_v     a, 
				       hr_eva_period_type_tw b, 
				       hr_eva_close_tw       c
				 where a.company_id           = b.seg_segment_no
				   and a.pa_period_type_seqno = b.eva_period_type_id
				   and a.company_id           = c.seg_segment_no
				   and a.pa_period_seqno      = c.evaluation_period_id
				   and c.close_type2          = 'Y'
				   and a.company_id           = :company_id
				   and a.emp_seqno            = :emp_seqno
				 order by a.pa_end_date desc)
		 where row_num < :limit
eof;
		*/
		$sql = <<<eof
		select *
		  from (select pa_year,
				       pa_period_desc,
				       pa_score,
				       pa_rank,
				       rownum as row_num
				  from ehr_pa_emp_list_v
				 where close_type2          = 'Y'
				   and company_id           = :company_id
				   and emp_seqno            = :emp_seqno
				 order by pa_end_date desc)
		 where row_num < :limit
eof;
        //$this->_dBConnection->debug = true;
        $this->_dBConnection->SetFetchMode($fetch_mode);
        return $this->_dBConnection->GetArray($sql,
                                              array('company_id'=>$this->_companyId,
                                                    'emp_seqno'=>$emp_seqno,
                                                    'limit'=>self::PA_HIS_ROW_LIMIT));
		                                             
	}// end getPAHis()
	
	function returnBytes($val) {
	    $val = trim($val);
	    $last = strtolower($val{strlen($val)-1});
	    switch($last) {
	        // The 'G' modifier is available since PHP 5.1.0
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }// end switch
	    return $val;
	}//end returnBytes()
	
	/**
	 * Get 考核项目 (行为伦理态度)
	 *
	 * @param number $pa_form_seqno
	 * @return array
	 * @author Dennis 2008-11-13 
	 * @since  3.2.2575 last update by dennis  2010-01-13 添加项目评分说明
	 */
	public function getPAItem($pa_form_seqno)
	{
		$sql = <<<eof
		select eva_period_type2_tw_id       as pa_detail_seqno,
		       a.seq_no                     as order_seqno,
		       a.evaluation_item_no         as pa_item_seqno,
		       a.evaluation_item_desc       as pa_item_desc,
		       c.evaluation_power           as pa_item_weight,
		       c.evaluation_score_detail_id as pa_item_range_seqno,
		       b.evaluation_level_master_id as pa_std_master_seqno, 
		       c.evaluation_item_master_id  as pa_item_master_seqno, -- add by dennis 20091230
		       c.evaluation_item_detail_id  as pa_item_detail_id,    -- add by dennis 20091230
		       cast(pk_crypt_sz.decryptN(rank1) as number) as emp_score,
		       cast(pk_crypt_sz.decryptN(rank2) as number) as mgr1_score,
		       cast(pk_crypt_sz.decryptN(rank3) as number) as mgr2_score,
		       emp_selfcomm                 as emp_remark,
		       mgr1_selfcomm                as mgr1_remark,
		       mgr2_selfcomm                as mgr2_remark,
		       c.item_reference             as pa_ref_comment
		  from hr_eva_period_type2_tw_base a,
		       hr_evaluation_items_master  b,
		       hr_evaluation_items_detail  c
		 where a.seg_segment_no = b.seg_segment_no
		   and a.seg_segment_no = c.seg_segment_no
		   and a.evaluation_item_detail_id = c.evaluation_item_detail_id
		   and b.evaluation_item_master_id = c.evaluation_item_master_id
		   and a.appraisal_id = :pa_form_seqno
		   and a.seg_segment_no = :company_id
		 order by a.seq_no
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetArray($sql,array('pa_form_seqno'=>$pa_form_seqno,
                                                         'company_id'=>$this->_companyId));
	}// end getPAItem()
	
	/**
	 * Get 工作伦理权重每一项的评分标准准
	 *
	 * @param number $pa_form_seqno 考核单号
	 * @return array
	 * @author Dennis 2008-12-05 16:14
	 */
	public function getPAItemScoreStd($pa_form_seqno)
	{
		$sql = <<<eof
		select b.eva_period_type2_tw_id     as pa_detail_seqno,
		       d.evaluation_score_detail_id as pa_item_range_seqno,
		       c.score1                     as min_score,
		       c.score2                     as max_score
		  from hr_appraisals_base          a,
		       hr_eva_period_type2_tw_base b,
		       hr_eva_score_detail_tw      c,
		       hr_evaluation_items_detail  d
		 where a.seg_segment_no = b.seg_segment_no
		   and a.appraisal_id = b.appraisal_id
		   and a.seg_segment_no = d.seg_segment_no
		   and b.evaluation_item_detail_id = d.evaluation_item_detail_id
		   and a.evaluation_item_master_id = d.evaluation_item_master_id
		   and c.seg_segment_no = d.seg_segment_no
		   and c.eva_score_master_id = d.evaluation_score_detail_id /* !!! use master id join detail id*/
		   and a.appraisal_id = :pa_form_seqno
		   and a.seg_segment_no = :company_id
eof;
		//$this->_dBConnection->debug =true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetArray($sql,array('pa_form_seqno'=>$pa_form_seqno,
                                                         'company_id'=>$this->_companyId));	
	}// end getPAItemScoreStd()
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $assess_std_master_seqno
	 * @return unknown
	 */
	public function getAsscessSTD($assess_std_master_seqno)
	{
		$sql = <<<eof
			select eva_score_detail_id as pa_assess_std_detail_seqno,
			       seq_no              as seqno,
			       eva_score_no        as score_id,
			       eva_score_desc      as score_desc,
			       eva_score_level     as score_level,
			       score1              as score_start,
			       score2              as score_end
			  from hr_eva_score_detail_tw
			 where seg_segment_no = :company_id
			   and eva_score_master_id = :assess_std_master_seqno
			   and is_active = 'Y'
			   order by seq_no
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
   		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
                                                         'assess_std_master_seqno'=>$assess_std_master_seqno));
	}
	private function _getId($id)
	{
		$whoami = false;
		switch($id)
		{
			case 'emp':
				$whoami = '1';
				break;
			case 'mgr1':
				$whoami = '2';
				break;
			case 'mgr2':
				$whoami = '3';
				break;
			default:break;
		}// end switch
		return $whoami;
	}// end _getWhoAmI()
	/**
	 * Get 问答型
	 *
	 * @param number $pa_form_seqno
	 * @return array
	 * @author Dennis 2008-11-13
	 */
	public function getQAItem($pa_form_seqno,$type)
	{
		$view_name = $type == 'improve_item' ? 'hr_eva_period_type5_tw_v' : 'hr_eva_period_type4_tw_v';
		$seqno_column = $type == 'improve_item' ? 'eva_period_type5_tw_id' : 'eva_period_type4_tw_id';
		$item_key_column = $type == 'improve_item' ? 'selfdevelope_id' :'interview_id';
		$item_desc_column = $type == 'improve_item' ? 'selfdevelope_item' : 'interview_name';
		$sql = <<<eof
			select %s                     as pa_detail_seqno,
			       seq_no                 as order_seqno,
			       reply_subject          as item_owner,
			       reply_way              as answer_type,
			       eva_score_master_id    as answer_list_key,
			       %s                     as item_seqno,
			       %s                     as item_desc,
			       animadversion_emp      as emp_answer_val,
			       animadversion_mgr      as mgr1_answer_val,
			       animadversion_mgr2     as mgr2_answer_val
			  from %s
			 where appraisal_id = :pa_form_seqno
			   and seg_segment_no = :company_id
			order by seq_no,item_owner
eof;
        //$this->_dBConnection->debug = true;
        $sql = sprintf($sql,$seqno_column,$item_key_column,$item_desc_column,$view_name);
        $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetArray($sql,array('pa_form_seqno'=>$pa_form_seqno,
                                                         'company_id'=>$this->_companyId));
	}// end getSelfImproveItem()
	
	/**
	 * Get 自我改善与发展问题(类型是单选)的答案选项清单
	 *
	 * @param number $answer_key
	 * @return array
	 * @author Dennis 2008-11-25 17:52
	 */
	public function getAnswerItemList($answer_key) {
		$sql = <<<eof
		select eva_score_detail_id as item_seqno,
		       seq_no              as order_seq,
		       eva_score_no        as item_id,
		       eva_score_desc      as item_desc
		  from hr_eva_score_detail_tw
		 where eva_score_master_id = :answer_key
		 order by seq_no
eof;
        //$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('answer_key'=>$answer_key));
	}// end getAnswerItemList()
	
	
	/**
	 * Get 绩校考核面谈确认项目()
	 *
	 * @param number $pa_form_seqno
	 * @return array
	 */
	public function getInterviewConfirmItem($pa_form_seqno)
	{
		$sql = <<<eof
		select appraisal_id,
		       eva_period_type4_tw_id,
		       seg_segment_no,
		       interview_id,
		       seq_no,
		       interview_name  as interview_item     
		  from hr_eva_period_type4_tw_v
		 where appraisal_id = :pa_form_seqno
           and seg_segment_no = :company_id
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->_dBConnection->GetArray($sql,array('pa_form_seqno'=>$pa_form_seqno,
                                                         'company_id'=>$this->_companyId));        
	}// end getInterviewConfirmItem()
	
	/**
	 * Get 绩考评等标准
	 *
	 * @param number $eva_level_id 评等标准建档的 seqno
	 * @return array
	 * @author Dennis 2008-11-13
	 */
	public function getPARankStd($eva_level_id)
	{
		$sql = <<<eof
		select evaluation_level_detail_id,
			   evaluation_level_no as level_desc,
		       evaluation_level_no,
		       seq_no,
		       remark,
		       score1,
		       score2,
		       eva_percent
		  from hr_evaluation_levels_detail
		 where seg_segment_no = :company_id
		   and evaluation_level_master_id = :eva_level_id
		order by seq_no
eof;
        //$this->_dBConnection->debug = true;
        $this->_dBConnection->SetFetchMode(ADODB_FETCH_DEFAULT);
        return $this->_dBConnection->GetArray($sql,array('eva_level_id'=>$eva_level_id,
                                                         'company_id'=>$this->_companyId));
	}// end getPARankStd()
	/**
	 *  此 function 废弃 2009-03-15
	public function getPARankStdByPeriod($pa_period_seqno)
	{
		$sql = <<<eof
		select b.evaluation_level_detail_id as rank_seqno,
		       b.evaluation_level_no  as rank_desc
		  from ehr_pa_period_v a, hr_evaluation_levels_detail b
		 where a.company_id = b.seg_segment_no
		   and a.pa_rank_std_seqno = b.evaluation_level_master_id
		   and a.company_id = :company_id
		   and a.pa_period_seqno = :pa_period_seqno
		 order by b.seq_no
eof;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_DEFAULT);
		return $this->_dBConnection->GetArray($sql,array('pa_period_seqno'=>$pa_period_seqno,
                                                         'company_id'=>$this->_companyId));
	}// end getPARankStdByPeriod()
*/
	
	/**
	 * Get是否有接班人维护要做
	 *
	 * @param string $pa_period_seqno 考核期间代码(流水号)
	 */
	public function getSuccessorSetting($pa_period_seqno)
	{
		$sql = <<<eof
			select evaluation_inheritor_id
			  from hr_evaluation_inheritor
			 where evaluation_period_id = :pa_period_seqno
			   and seg_segment_no = :company_id
			   and psn_id = :emp_seq_no
eof;
		return $this->_dBConnection->GetOne($sql,array('pa_period_seqno'=>$pa_period_seqno,
                                                       'company_id'=>$this->_companyId,
                                                       'emp_seq_no'=>$this->_empSeqNo));		
	}// end getSucccessionSetting()
	/**
	 * Get 已添加的接班人资料
	 *
	 * @param  number $pa_period_seqno 考核期间代码
	 * @return number
	 */
	public function getSuccessorPlanList($pa_period_seqno)
	{
		$sql = <<<eof
			select count(1) as cnt
			  from hr_inheritance_people
			  where evaluation_period_id = :pa_period_seqno
			    and seg_segment_no = :company_id
			    and psn_id = :emp_seq_no
eof;
		//$this->_dBConnection->debug = true;
		return $this->_dBConnection->GetOne($sql,array('pa_period_seqno'=>$pa_period_seqno,
                                                       'company_id'=>$this->_companyId,
                                                       'emp_seq_no'=>$this->_empSeqNo));		
	}// end getSuccessorPlanList()
	public function selectSuccessor($pa_successor_key) {
		$sql = <<<eof
		select evaluation_period_desc as pa_desc,
		       evaluation_begin_date  as pa_begin_date,
		       evaluation_end_date    as pa_end_date,
		       order_seq              as order_seq,
		       personnel_id           as s_emp_seqno,
		       id_no_sz               as s_emp_id,
		       name_sz                as s_emp_name,
		       title_name             as s_title_name,
		       dept_name              as s_dept_name,
		       psn_grade              as s_level,
		       job_represent          as job_represent,
		       possibilities          as possibilities,
		       approve_time           as approve_time,
		       excellence             as excellence,
		       defect                 as defect,
		       foster_priority        as foster_priority,
		       remark                 as remark
		  from hr_inheritance_people_v
		 where inheritance_people_id = :successor_key
eof;
		//$this->_dBConnection->debug =true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetRow($sql,array('successor_key'=>$pa_successor_key));
	}// end selectSuccessor()

	private function _checkSuccessorUnique($pa_period_seqno,$s_empseqno)
	{
		$sql = <<<eof
			select 1
			  from hr_inheritance_people
			 where seg_segment_no = :company_id
			   and psn_id         = :emp_seqno
			   and evaluation_period_id = :pa_period_seqno
			   and personnel_id   = :s_emp_seqno 
eof;
		return $this->_dBConnection->GetOne($sql,array('company_id'=>$this->_companyId,
											    'emp_seqno'=>$this->_empSeqNo,
											    'pa_period_seqno'=>$pa_period_seqno,
											    's_emp_seqno'=>$s_empseqno));
	}// end _checkSuccessorUnique()
	
	private function _checkSeqUnique($pa_period_seqno,$seqno)
	{
		$sql = <<<eof
			select 1
			  from hr_inheritance_people
			 where seg_segment_no = :company_id
			   and psn_id         = :emp_seqno
			   and evaluation_period_id = :pa_period_seqno
			   and order_seq      = :seqno 
eof;
		return $this->_dBConnection->GetOne($sql,array('company_id'=>$this->_companyId,
											    'emp_seqno'=>$this->_empSeqNo,
											    'pa_period_seqno'=>$pa_period_seqno,
											    'seqno'=>$seqno));
	}// end _checkSeqUnique()
	
	
	/**
	 * Add new Successor
	 *
	 * @param number $pa_period_seqno 考核期间代码
	 * @param number $order_seq       接任序位
	 * @param number $successor_emp_seqno 接班人员工编号(psn_id)
	 * @param string $approve_time		可接任时间
	 * @param string $job_represent     工作表现
	 * @param string $possabilitiy      发展潜力
	 * @param string $excellent         优点
	 * @param string $defect			缺点
	 * @param string $foster_priority   培育重点
	 * @param string $remark			备注
	 * @param string $create_by			建档人
	 * @param string $create_app		建档程式
	 * @author Dennis	2008-11-19 22:32 
	 * @access public
	 */
	public function insertSuccessor($pa_period_seqno,
									$order_seq,
									$successor_emp_seqno,
									$approve_time,
									$job_represent,
									$possabilitiy,
									$excellent,
									$defect,
									$foster_priority,
									$remark,
									$create_by,
									$create_app)
	{
		$sql = <<<eof
			insert into hr_inheritance_people
			  (inheritance_people_id,
			   seg_segment_no,
			   evaluation_period_id,
			   psn_id,
			   order_seq,
			   personnel_id,
			   job_represent,
			   possibilities,
			   approve_time,
			   excellence,
			   defect,
			   foster_priority,
			   remark,
			   create_date,
			   create_by,
			   create_program)
			values
			  (hr_inheritance_people_s.nextval,
			   :v_seg_segment_no,
			   :v_evaluation_period_id,
			   :v_psn_id,
			   :v_order_seq,
			   :v_personnel_id,
			   :v_job_represent,
			   :v_possibilities,
			   :v_approve_time,
			   :v_excellence,
			   :v_defect,
			   :v_foster_priority,
			   :v_remark,
			   sysdate,
			   :v_create_by,
			   :v_create_program)
eof;
		//$this->_dBConnection->debug = true;
		// 检查 接班人/序位 是否重复
		$is_seq_ok = intval($this->_checkSeqUnique($pa_period_seqno,$order_seq));
		$is_suc_ok = intval($this->_checkSuccessorUnique($pa_period_seqno,$successor_emp_seqno));
		if(1 == $is_seq_ok)
		{
			return 'seqno_error';
		}//end if
		
		if(1 == $is_suc_ok)
		{
			return 'empno_error';
		}// end if
		
		$r = $this->_dBConnection->Execute($sql,array('v_seg_segment_no'=>$this->_companyId,
													  'v_evaluation_period_id'=>$pa_period_seqno,
													  'v_psn_id'=>$this->_empSeqNo,
													  'v_order_seq'=>$order_seq,
													  'v_personnel_id'=>$successor_emp_seqno,
													  'v_job_represent'=>$job_represent,
													  'v_possibilities'=>$possabilitiy,
													  'v_approve_time'=>$approve_time,
													  'v_excellence'=>$excellent,
												 	  'v_defect'=>$defect,
													  'v_foster_priority'=>$foster_priority,
													  'v_remark'=>$remark,
													  'v_create_by'=>$create_by,
													  'v_create_program'=>$create_app));
		//$this->_dBConnection->CommitTrans(true);   // do commit
		//$this->_dBConnection->CompleteTrans();
		if (is_object($r))
		{
			return 1;
		}else{
			return  $this->_dBConnection->ErrorMsg();
		}// end of
	}// end insertSuccessor()
	
	public function updateSuccessor($successor_key,
									$order_seq,
									$successor_emp_seqno,
									$approve_time,
									$job_represent,
									$possabilitiy,
									$excellent,
									$defect,
									$foster_priority,
									$remark,
									$update_by,
									$update_app)
	{
		$sql = <<<eof
		update hr_inheritance_people
		   set order_seq       = :v_order_seq,
		       personnel_id    = :v_personnel_id,
		       job_represent   = :v_job_represent,
		       possibilities   = :v_possibilities,
		       approve_time    = :v_approve_time,
		       excellence      = :v_excellence,
		       defect          = :v_defect,
		       foster_priority = :v_foster_priority,
		       remark          = :v_remark,
		       update_date     = sysdate,
		       update_by       = :v_update_by,
		       update_program  = :v_update_program
		 where inheritance_people_id = :v_inheritance_people_id
eof;
		//$this->_dBConnection->debug = true;
		$r = $this->_dBConnection->Execute($sql,array('v_order_seq'=>$order_seq,
													 'v_personnel_id'=>$successor_emp_seqno,
													 'v_job_represent'=>$job_represent,
													 'v_possibilities'=>$possabilitiy,
													 'v_approve_time'=>$approve_time,
													 'v_excellence'=>$excellent,
													 'v_defect'=>$defect,
													 'v_foster_priority'=>$foster_priority,
													 'v_remark'=>$remark,
													 'v_update_by'=>$update_by,
													 'v_update_program'=>$update_app,
													 'v_inheritance_people_id'=>$successor_key));
		$this->_dBConnection->CommitTrans(true);   // do commit
		$this->_dBConnection->CompleteTrans();
		if($r)
		{
			return $this->_dBConnection->Affected_Rows();
		}else{
			return $this->_dBConnection->ErrorMsg();
		}// end if
	}// end updateSuccessor()
	
	public function deleteSuccessor($pa_successor_key)
	{
		$sql = <<<eof
			delete from hr_inheritance_people where inheritance_people_id = :pa_successor_key
eof;
		$r = $this->_dBConnection->Execute($sql,array('pa_successor_key'=>$pa_successor_key));
		$this->_dBConnection->CommitTrans(true);   // do commit
		$this->_dBConnection->CompleteTrans();
		if($r)
		{
			return $this->_dBConnection->Affected_Rows();
		}else{
			return $this->_dBConnection->ErrorMsg();
		}// end if
	}// end deleteSuccessor()
	
	/**
	 * Get 可接任时间清单
	 *
	 * @param string $lang
	 * @return array
	 */
	public function getApproveTimeList($lang)
	{
		return getMultiLangList($lang,self::APPROVE_TIME_LIST);
	}// end getApproveTimeList()
	
	/**
	 * Get 发展潜力清单
	 *
	 * @param string $lang
	 * @return array
	 */
	public function getPossabilitiesList($lang)
	{
		return getMultiLangList($lang,self::POSSIBILITIES_LIST);
	}// end getApproveTimeList()
	
	/**
	 * Get 工作表现清单
	 *
	 * @param string $lang
	 * @return array
	 */
	public function getJobRepresentList($lang)
	{
		return getMultiLangList($lang,self::JOB_REPRESENT_LIST);
	}// end getApproveTimeList()
	
	public function getSuccessorList($pa_period_seqno)
	{
		$sql = <<<eof
		select inheritance_people_id,
		       seg_segment_no,
		       evaluation_period_id,
		       evaluation_period_no,
		       evaluation_period_desc as pa_desc,
		       order_seq as order_seq,
		       personnel_id,
		       id_no_sz as s_emp_id,
		       name_sz as s_emp_name,
		       title_no,
		       title_name as s_emp_title,
		       dept_no as s_dept_id,
		       b.dept_name as s_dept_name,
		       f_year_sz(personnel_id, sysdate, seg_segment_no) as years,
		       a.emp_grade,
			   a.emp_degree,
		       b.form_status
		  from hr_inheritance_people_v a, ehr_pa_form_v b
		 where a.seg_segment_no = b.company_id
		   and a.evaluation_period_id = b.pa_period_seqno
		   and a.psn_id = b.pa_emp_seqno
		   and seg_segment_no = :company_id
		   and psn_id = :emp_seq_no
		   and evaluation_period_id = :pa_period_seqno
		 order by order_seq
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('pa_period_seqno'=>$pa_period_seqno,
                                                         'company_id'=>$this->_companyId,
                                                         'emp_seq_no'=>$this->_empSeqNo));
	}// end getSuccessorList()
	
	/**
	 * Get PA Workflow
	 *
	 * @param number $pa_period_seqno
	 * @param number $pa_emp_seqno
	 * @return array
	 * @author Dennis 2008-11-22 16:49
	 */
	public function getPAWorkflow($pa_period_seqno,$pa_emp_seqno)
	{
		$sql = <<<eof
		select personnel_id        as pa_emp_seqno,
		       personnel_no        as pa_emp_id,
		       personnel_name      as pa_emp_name,
		       manager_first_id    as mgr1_emp_seqno,
		       manager_first_name  as mgr1_emp_name,
		       manager_second_id   as mgr2_emp_seqno,
		       manager_second_name as mgr2_emp_name,
		       manager_third_id    as mgr3_emp_seqno,
		       manager_third_name  as mgr3_emp_name,
		       comp_eva_group      as pa_group_seqno
		  from hr_evaluation_subordinate
		 where evaluation_period_id = :pa_period_seqno
		   and seg_segment_no = :company_id
		   and personnel_id = :pa_emp_seqno
eof;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		return $this->_dBConnection->GetRow($sql,array('pa_period_seqno'=>$pa_period_seqno,
                                                       'company_id'=>$this->_companyId,
                                                       'pa_emp_seqno'=>$pa_emp_seqno));
	}//end getPAWorkflow()
	
	/**
	 * Get 系统日期在考核期间起讫日期之中的考核期间
	 *
	 * @return number
	 */
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
	
	/**
	 * 取得所有有效的（未过期间的考核期间）
	 * Add by Dennis 2010-06-28
	 * @param no
	 * @return string
	 * @author Dennis
	 */
	private function _getPeriodsInProcess()
	{
		$sql = <<<eof
		select pa_period_seqno
		  from ehr_pa_period_v
		 where company_id = :company_id
		   and to_char(sysdate, 'YYYYMMDD') >= to_char(pa_begin_date, 'YYYYMMDD')
		   and to_char(sysdate, 'YYYYMMDD') <= to_char(pa_end_date,   'YYYYMMDD')
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		$periods = $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId));
		$ps      = '';
		for ($i=0; $i<count($periods);$i++)
		{
			$ps .= $periods[$i]['PA_PERIOD_SEQNO'].',';
		}
		if (count($ps)>0)return substr($ps,0,-1);
		return $ps;
	}
	
	private function _getWhoAmIByPeriod($pa_period_seqno=null)
	{
		$least_period_seqno = is_null($pa_period_seqno) ? 
						      $this->getLeastPAPeriod() :
						      $pa_period_seqno;
		$sql = <<<eof
			select personnel_id    	   as pa_emp_seqno,
			       personnel_no        as pa_emp_id,
			       personnel_name      as pa_emp_name,
			       manager_first_id    as mgr1_emp_seqno,
			       manager_first_name  as mgr1_emp_name,
			       manager_second_id   as mgr2_emp_seqno,
			       manager_second_name as mgr2_emp_name,
			       manager_third_id    as mgr3_emp_seqno,
			       manager_third_name  as mgr3_emp_name,
			       comp_eva_group      as pa_group_seqno
			  from hr_evaluation_subordinate
			 where evaluation_period_id = :pa_period_seqno
			   and seg_segment_no = :company_id
			   and (manager_first_id = :emp_seqno1 or
			   		manager_second_id = :emp_seqno2 or
			   		manager_third_id = :emp_seqno3)
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $this->_dBConnection->GetArray($sql,array('pa_period_seqno'=>$least_period_seqno,
                                                         	 'company_id'=>$this->_companyId,
                                                         	 'emp_seqno1'=>$this->_empSeqNo,
                                                         	 'emp_seqno2'=>$this->_empSeqNo,
                                                        	 'emp_seqno3'=>$this->_empSeqNo));
		$mgrs = '';
		for ($i=0; $i<count($result); $i++)
		{
			if ($this->_empSeqNo == $result[$i]['MGR1_EMP_SEQNO'])
			{
				$mgrs[] = 'mgr1';
			}
			if ($this->_empSeqNo == $result[$i]['MGR2_EMP_SEQNO'])
			{
				$mgrs[] = 'mgr2';
			}
			if ($this->_empSeqNo == $result[$i]['MGR3_EMP_SEQNO'])
			{
				$mgrs[] = 'mgr3';
			}// end if
		}// end loop
		//pr(array_unique($mgrs));
		return is_array($mgrs) ? array_unique($mgrs) : '';
	}// end getWhoAmIByPeriod()
	
	/**
	 * 我的考核单
	 *  1. 有生成考核单
	 *  2. 考核主管有设定
	 *  3. 不考核人员里没有我
	 *  4. 在员工考核起讫时间内
	 *  5. 如果是面谈确认时间一起讫必须在初评(mg1_begin/end_date)期间内
	 *  6. 面谈确认时间小于初评开始时间, 也就是说,大家都很积极,还没到时间大家都填好考核单
	 * @param  no
	 * @return array
	 * @author Dennis 2008-12-03
	 */
	public function getMyPAForm()
	{
		$sql = <<<eof
			select pa_period_seqno,
			       pa_period_id,
			       pa_period_desc,
			       pa_year,
			       pa_form_seqno,
			       form_status,
			       pa_begin_date,
			       pa_end_date,
			       emp_begin_date,
			       emp_end_date,
			       mgr1_begin_date,
			       mgr1_end_date,
			       mgr2_begin_date,
			       mgr2_end_date,
			       mgr3_begin_date,
			       mgr3_end_date
			  from ehr_pa_form_v
			 where company_id = :company_id
			   and pa_emp_seqno = :emp_seqno
			   and (form_status is null or form_status < 2)
			   and to_char(sysdate, 'YYYYMMDD') >= to_char(emp_begin_date, 'YYYYMMDD')
			   and to_char(sysdate, 'YYYYMMDD') <= to_char(emp_end_date, 'YYYYMMDD')
			    or (pa_emp_seqno = :emp_seqno1 and form_status = 4 and
			        ( to_char(sysdate, 'YYYYMMDD') >= to_char(mgr1_begin_date, 'YYYYMMDD') and
			          to_char(sysdate, 'YYYYMMDD') <= to_char(mgr1_end_date, 'YYYYMMDD')  or
			          to_char(sysdate, 'YYYYMMDD') <= to_char(mgr1_begin_date, 'YYYYMMDD')
			         )
			       )
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		// user cache get modify by dennis 2011-08-02 for performance		
		return $this->_dBConnection->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
											  array('company_id'=>$this->_companyId,
											  	    'emp_seqno'=>$this->_empSeqNo,
											  	    'emp_seqno1'=>$this->_empSeqNo));
	}// end getMyPAForm()
	
	/**
	 *  Get 某个考核期间下被我(mgr1/mgr2/mgr3)考核的员工清单
	 *
	 * @param number $pa_period_seqno
	 * @return array
	 * @author Dennis 2009-04-09
	 * @lastupdate: 2010-06-28 by Dennis 多个考核期间同时有效时
	 */
	public function getWaitForPAForms($pa_period_seqno = null,$is_get_gm = false)
	{
		$pa_period_seqno = is_null($pa_period_seqno) ? $this->_getPeriodsInProcess() : $pa_period_seqno;
		$args = func_get_args();
		$where = '';
		if (!empty($pa_period_seqno))
		{
			if (stripos($pa_period_seqno,','))
			{
				$where .= ' and pa_period_seqno in ('.$pa_period_seqno.') ';
			}else{
				$where .= ' and pa_period_seqno = '.$pa_period_seqno;
			}
		}
		else{$where .= ' and 1=2 ';}//Added from this row by hunk at 20151202 for muti-datas no apprars
		
		if (count($args)>1 && isset($args[1]) && $args[1] === true)
		{
			$where .= ' and (pa_end_date < sysdate or form_status >=10) ';
		}
		
		$sql = <<<eof
				select pa_period_seqno,
		               group_seqno,
		               group_desc,
		               pa_period_id,
		               pa_period_desc,
		               std_master_seqno,
		               std_master_desc,
		               count(*) as headcount
		          from ehr_pa_form_v
		         where company_id = :company_id
		           $where
		           and (mgr1_emp_seqno = :emp_seqno1 or 
			            mgr2_emp_seqno = :emp_seqno2 or
			            mgr3_emp_seqno = :emp_seqno3)
		         group by pa_period_seqno,
		                  group_seqno,
		                  group_desc,
		                  pa_period_id,
		                  pa_period_desc,
		                  std_master_seqno,
		                  std_master_desc
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		// modify by dennis 2011-08-02 for performance
		return $this->_dBConnection->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->_companyId,
					 'emp_seqno1'=>$this->_empSeqNo,
					 'emp_seqno2'=>$this->_empSeqNo,
					 'emp_seqno3'=>$this->_empSeqNo));
	}// end getWaitForPAForms()
	
	
	protected function _getPAFormCntByGroup($pa_period_seqno)
	{
		$sql = <<<eof
			select group_seqno,
			       group_desc,
			       pa_period_seqno,
			       pa_period_id,
			       pa_period_desc,
			       count(*) as headcount
			  from ehr_pa_form_v
			 where company_id = :company_id
			   and pa_period_seqno = :pa_period_seqno
			   and (mgr1_emp_seqno = :emp_seqno1 or 
			        mgr2_emp_seqno = :emp_seqno2 or
			        mgr3_emp_seqno = :emp_seqno3)
			 group by group_seqno,
			          group_desc,
			          pa_period_seqno,
			          pa_period_id,
			          pa_period_desc
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'emp_seqno1'=>$this->_empSeqNo,
														 'emp_seqno2'=>$this->_empSeqNo,
														 'emp_seqno3'=>$this->_empSeqNo));
		
	}// end _getPAFormCntByGroup()
	
	/**
	 * 根据考核群组 select 其下的评核标准
	 *
	 * @param number $pa_period_seqno 考期期间代号
	 * @param string $pa_group_no     群级代码
	 * @return array
	 * @author Dennis 2009-03-16
	 */
	protected function _getPAStdByGroup($pa_period_seqno,$pa_group_no)
	{
		$sql = <<<eof
		select a.pa_level_master_seqno pa_std_seqno,
		       b.evaluation_level_master_desc as pa_std_desc,
		       count(a.pa_emp_seqno) as headcount
		  from ehr_pa_form_v a, hr_evaluation_levels_master b
		 where a.company_id = b.seg_segment_no
		   and a.pa_level_master_seqno = b.evaluation_level_master_id
		   and a.company_id =  :company_id
		   and a.pa_period_seqno =  :pa_period_seqno
		   and a.group_seqno =  :group_no
		 group by a.pa_level_master_seqno, b.evaluation_level_master_desc
eof;
		//$this->_dBConnection->deubg = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'group_no'=>$pa_group_no));
		
	}// end _getPAStdByGroup()
	
	/**
	 * 根据群组,和评分标准取得人数统计
	 *
	 * @param number $pa_period_seqno 考核期间
	 * @param string $group_no        群组代码
	 * @param number $pa_std_seqno    评分标准主档代码
	 * @param string $whoami          按角色统计 emp_myself,mgr1_初评主管,mgr2_复评主管,mgr3_核定主管
	 * @return array
	 * @author Dennis 2009-03-16
	 */
	protected function _getHeadcountByGroupAndStd($pa_period_seqno,$group_no,$pa_std_seqno,$whoami='emp')
	{
		$sql = <<<eof
			select count(emp_seqno) as headcount
			  from ehr_pa_emp_list_v
			 where company_id = :company_id
			   and pa_period_seqno = :pa_period_seqno
			   and group_seqno = :group_no
			   and level_std_seqno = :pa_std_seqno
			   and %s_seqno = :emp_seqno
eof;
		$sql = sprintf($sql,$whoami);
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'pa_std_seqno'=>$pa_std_seqno,
														 'group_no'=>$group_no,
														 'emp_seqno'=>$this->_empSeqNo));
	}// end _getHeadcountByGroupAndStd()	
	
	/**
	 * 挑我可签核的表单中的所有的评核标准名称
	 *
	 * @param number $pa_period_seqno
	 * @return array
	 */
	public function getPASTDList($pa_period_seqno = null)
	{
		$pa_period_seqno = is_null($pa_period_seqno) ? $this->getLeastPAPeriod() : $pa_period_seqno;
		$sql = <<<eof
			select b.evaluation_level_master_id as std_seqno,
                   b.evaluation_level_master_desc as std_desc,
                   count(*) as headcount
              from ehr_pa_form_v a, hr_evaluation_levels_master b
             where a.company_id = b.seg_segment_no
               and a.pa_level_master_seqno = b.evaluation_level_master_id
               and company_id = :company_id
			   and pa_period_seqno = :pa_period_seqno
			   and (mgr1_emp_seqno = :emp_seqno1 or 
			        mgr2_emp_seqno = :emp_seqno2 or
			        mgr3_emp_seqno = :emp_seqno3)
             group by b.evaluation_level_master_id, 
                      b.evaluation_level_master_desc
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'emp_seqno1'=>$this->_empSeqNo,
														 'emp_seqno2'=>$this->_empSeqNo,
														 'emp_seqno3'=>$this->_empSeqNo));
		
	}// end getPASTDList()
	
	/**
	 * Get 某群组下的所有员工清单
	 *
	 * @param number $pa_period_seqno
	 * @param string $lang_code
	 * @param string $group_seqno
	 * @return array
	 * @author Dennis
	 */
	/*
	public function getPAEmpList1($pa_period_seqno,$lang_code,$group_seqno=null)
	{
		$group_where = is_null($group_seqno) ? '' : "and a.group_seqno = '".$group_seqno."' ";
		$sql = <<<eof
		select *
		  from (select group_seqno,
		               group_desc,
		               emp_seqno,
		               emp_id,
		               emp_name,
		               dept_id,
		               dept_name,
		               pa_form_seqno,
		               pa_period_seqno,
		               form_status,
		               mgr1_begin_date,
		               mgr1_end_date,
		               mgr2_begin_date,
		               mgr2_end_date,
		               mgr3_begin_date,
		               mgr3_end_date,
		               c.value as form_status_desc,
		               pa_score,
		               pa_rank,
		               emp_score,
		               emp_rank,
		               mgr1_score,
		               mgr1_rank,
		               mgr2_score,
		               mgr2_rank,
		               pa_remark,
		               pk_performance.f_get_whoami(company_id,
		                                           pa_period_seqno,
		                                           pa_form_seqno,
		                                           :emp_seqno) as whoami
		          from ehr_pa_emp_list_v           a,
		               app_muti_lang               c
		         where nvl(a.form_status, 0) = c.seq(+)
		           and c.program_no(+) = 'ESNB008'
		           and c.type_code(+) = 'LL'
		           and c.name(+) = 'PA_FORM_STATUS'
		           and c.lang_code(+) = :lang_code
		           and company_id = :company_id
		           and pa_period_seqno = :pa_period_seqno
		           $group_where
		           order by mgr2_score desc )
		 where whoami != '0'
eof;
		//$sql = sprintf($sql,$this->getMyFormStatus($pa_period_seqno));
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'emp_seqno'=>$this->_empSeqNo,
														 'lang_code'=>$lang_code));
	}// end getPAEmpList()
	*/
	
	/**
	 * 根据考核期间代码和评分标准挑人员清单
	 *
	 * @param number $pa_period_seqno
	 * @param number $pa_std_master_seqno
	 * @return array
	 * @author Dennis 2009-02-20
	 */
	/*
	public function getPAEmpByStd($pa_period_seqno,$lang_code,$pa_std_master_seqno)
	{
	    $std_where = is_null($pa_std_master_seqno) ? '' : "and a.level_std_seqno = $pa_std_master_seqno ";
		$sql = <<<eof
		select *
		  from (select group_seqno,
		               group_desc,
		               emp_seqno,
		               emp_id,
		               emp_name,
		               dept_id,
		               dept_name,
		               pa_form_seqno,
		               pa_period_seqno,
		               form_status,
		               mgr1_begin_date,
		               mgr1_end_date,
		               mgr2_begin_date,
		               mgr2_end_date,
		               mgr3_begin_date,
		               mgr3_end_date,
		               c.value as form_status_desc,
		               pa_score,
		               pa_rank,
		               emp_score,
		               emp_rank,
		               mgr1_score,
		               mgr1_rank,
		               mgr2_score,
		               mgr2_rank,
		               pa_remark,
		               pk_performance.f_get_whoami(company_id,
		                                           pa_period_seqno,
		                                           pa_form_seqno,
		                                           :emp_seqno) as whoami
		          from ehr_pa_emp_list_v           a,
		               app_muti_lang               c
		         where nvl(a.form_status, 0) = c.seq(+)
		           and c.program_no(+) = 'ESNB008'
		           and c.type_code(+) = 'LL'
		           and c.name(+) = 'PA_FORM_STATUS'
		           and c.lang_code(+) = :lang_code
		           and company_id = :company_id
		           and pa_period_seqno = :pa_period_seqno
		           $std_where
		           order by mgr2_score desc)
		 where whoami != '0'
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'lang_code'=>$lang_code,
														 'emp_seqno'=>$this->_empSeqNo));
	}// end getPAEmpByStd()
	*/
	/**
	 * 根據考核期間,群組代碼,等第代碼挑選員工清單
	 *
	 * @param string $pa_period_seqno
	 * @param string $lang_code
	 * @param string $pa_group_no
	 * @param string $pa_level_seqno
	 * @param string $who
	 * @return array
	 * @author Dennis 2009-03-11
	 */
	/*
	public function getPAEmpByGroupAndLevel($pa_period_seqno,
											$lang_code,
											$pa_group_no,
											$pa_level_seqno,
											$who)
	{
		$sql = <<<eof
		select *
		  from (select group_seqno,
		               group_desc,
		               emp_seqno,
		               emp_id,
		               emp_name,
		               dept_id,
		               dept_name,
		               pa_form_seqno,
		               pa_period_seqno,
		               form_status,
		               mgr1_begin_date,
		               mgr1_end_date,
		               mgr2_begin_date,
		               mgr2_end_date,
		               mgr3_begin_date,
		               mgr3_end_date,
		               c.value as form_status_desc,
		               pa_score,
		               pa_rank,
		               emp_score,
		               emp_rank,
		               mgr1_score,
		               mgr1_rank,
		               mgr2_score,
		               mgr2_rank,
		               pa_remark,
		               pk_performance.f_get_whoami(company_id,
		                                           pa_period_seqno,
		                                           pa_form_seqno,
		                                           :emp_seqno) as whoami
		          from ehr_pa_emp_list_v           a,
		               app_muti_lang               c
		         where nvl(a.form_status, 0) = c.seq(+)
		           and c.program_no(+) = 'ESNB008'
		           and c.type_code(+) = 'LL'
		           and c.name(+) = 'PA_FORM_STATUS'
		           and c.lang_code(+) = :lang_code
		           and a.company_id = :company_id
		           and a.pa_period_seqno = :pa_period_seqno
		           and a.group_seqno = :group_no
		           and a.%s_level_seqno = :level_seqno
		           order by mgr2_score desc )
		 where whoami != '0'
eof;
		$sql = sprintf($sql,$who);
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'lang_code'=>$lang_code,
														 'emp_seqno'=>$this->_empSeqNo,
														 'group_no'=>$pa_group_no,
														 'level_seqno'=>$pa_level_seqno));
	}// end getPAEmpByGroupAndLevel()
	*/
	/**
	 * 取考核标准的分数
	 *
	 * @param number $std_master_seqno  考核标准主档的代码
	 * @return array
	 * @author Dennis 2009-05-07
	 */
	public function getPALevelIDByScore($std_master_seqno,$score)
	{
		$sql = <<<eof
		select level_id, 
		       level_desc, 
		       min_value,
		       max_value
		  from ehr_pa_std_v
		 where company_id         = :company_id
		   and level_master_seqno = :std_master_seqno
		   and :score between min_value and max_value
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs = $this->_dBConnection->GetRow($sql,array('company_id'=>$this->_companyId,
													  'std_master_seqno'=>$std_master_seqno,'score'=>$score));
		return $rs['LEVEL_ID'];
		/*
		$c = count($rs)-1;
		for ($i= $c; $i>=0; $i--)
		{
			if ($score >= $rs[$i]['MIN_VALUE'] &&
				$score <= $rs[$i]['MAX_VALUE']) return $rs[$i]['LEVEL_ID'];
		}*/
	}
	/**
	 * 根據條件查詢相關員工列表
	 *
	 * @param string $pa_period_seqno  考核單號
	 * @param string $lang_code        語系 (抓群組多語用到)
	 * @param string $count_by_who	   按哪一階來統計 (emp/mgr1/mgr2/mgr3)
	 * @param string $group_no         群組代碼
	 * @param string $pa_std_seqno     考核標準代碼
	 * @param string $level_seqno      等第代碼
	 * @param number $pageindex		   页索引,default 1
	 * @param number $pagesize		   每页显示记录笔数 default 30
	 * @return array
	 * @author Dennis 2009-03-11 last update by dennis 20091123
	 */
	public function getPAEmpList($pa_period_seqno,
								 $lang_code,
								 $count_by_who = null,
								 $group_no     = null,
								 $pa_std_seqno = null,
								 $level_seqno  = null,
								 $pageindex    = 1,
								 $pagesize     = 30)
	{
		
		$where = '';
		$where .= is_null($group_no) ? '' : 
				  ' and a.group_seqno = \''.$group_no.'\'';
		
		$where .= is_null($pa_std_seqno) ? '' : 
		          ' and a.level_std_seqno = \''.$pa_std_seqno.'\'';
		
		$where .= is_null($level_seqno)  ? '' : 
		          (is_null($count_by_who) ? '' : 
		           ' and a.'.$count_by_who.'_level_seqno = \''.$level_seqno.'\'');
		
		// 当前人的角色		          		
		$where .=  is_null($count_by_who) ? '' : sprintf(' and  %s_emp_seqno =\'%s\'',$count_by_who,$this->_empSeqNo);
		//echo $where .'<hr/>';
		$orderby = '';
		$orderby .= is_null($count_by_who) ? '' : ' order by a.'.$count_by_who.'_score desc ';
		$whoami_column  = is_null($count_by_who) ? 
		                  sprintf('pk_performance.f_get_whoami(company_id,pa_period_seqno,pa_form_seqno,%s) as whoami',$this->_empSeqNo) :
		                  sprintf('\'%s\' as whoami',$count_by_who);
		//echo $where.'<hr/>';
		//echo $orderby.'<hr/>';
		$sql = <<<eof
			select * from (
					select group_seqno,
			               group_desc,
			               emp_seqno,
			               emp_id,
			               emp_name,
			               dept_id,
			               dept_name,
			               pa_form_seqno,
			               pa_period_seqno,
			               form_status,
			               mgr1_begin_date,
			               mgr1_end_date,
			               mgr2_begin_date,
			               mgr2_end_date,
			               mgr3_begin_date,
			               mgr3_end_date,
			               c.value as form_status_desc,
			               gm_score,
			               gm_rank,
			               pa_score,
			               pa_rank,
			               emp_score,
			               emp_rank,
			               mgr1_score,
			               mgr1_rank,
			               mgr2_score,
			               mgr2_rank,
			               pa_remark,
			               a.mgr1_emp_seqno,
			               a.mgr2_emp_seqno,
			               a.mgr3_emp_seqno,
			               a.level_std_seqno as std_master_seqno,
			               $whoami_column
			          from ehr_pa_emp_list_v           a,
			               app_muti_lang               c
			         where nvl(a.form_status, 0) = c.seq(+)
			           and c.program_no(+)       = 'ESNB008'
			           and c.type_code(+)        = 'LL'
			           and c.name(+)             = 'PA_FORM_STATUS'
			           and c.lang_code(+)        = '%s'
			           and a.company_id          = '%s'
			           and a.pa_period_seqno     = '%s'
			           $where)
			where whoami != '0'
eof;
		$sql = sprintf($sql,$lang_code,$this->_companyId,$pa_period_seqno);
		
		//$this->_dBConnection->debug = true;
		// add by dennis for improve performance 20091120
		/* remary by dennis 20091204 因用 temp table 也没有解决速度问题
		 
		$tmp_table_name = 'ehr_pa_emplist_tmp';
		if (1 != $this->_checkTmpTabExists($tmp_table_name))
		{
			$create_stmt = <<<eof
				create global temporary table $tmp_table_name as 
				$sql
				and 1=2
eof;
			// create temp table
			$this->_dBConnection->Execute($create_stmt);
		}
		// insert data to temp table
		$insert_stmt = <<<eof
			insert into $tmp_table_name 
			$sql
eof;
		$this->_dBConnection->Execute($insert_stmt);
		*/
		$count_sql  = "select count(*) from ($sql)";
		//$count_sql  = "select count(*) from $tmp_table_name";
		
		//$total_rows = $this->_dBConnection->GetOne($count_sql,$params);
		$total_rows = $this->_dBConnection->GetOne($count_sql);
		$pageindex  = $pageindex >0 ? $pageindex : 1;
		$this->_setPagerToolbar($total_rows,$pagesize);
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		$rsLimit = $this->_dBConnection->SelectLimit("select * from ($sql) $orderby",$pagesize,$pagesize * ($pageindex-1));
		$rs = $rsLimit->GetArray();
		return $rs;
		//return $this->_dBConnection->GetArray($sql,$params);
	}// end getPAEmpList()
	/**
	 * Help Function
	 * Check Temporary table is exists
	 *
	 * @param string $tabname
	 * @return int
	 * @author Dennis 20091123
	 */
	private function _checkTmpTabExists($tabname)
	{
		$sql = <<<eof
			select 1 from user_tables where table_name  = :table_name
eof;
		return $this->_dBConnection->GetOne($sql,array('table_name'=>strtoupper($tabname)));
	}
	/**
	 * 员工/初评/复评主管填写考核项目分数及备注
	 *
	 * @param number $pa_form_seqno
	 * @param string $whoami
	 * @param array  $pa_item_scores   每项目成绩组成的 array,以项目key为下标
	 * @param array  $pa_item_remark   每项目说明
	 * @return mixed
	 * @author Dennis
	 */
	public function updatePAItem($pa_form_seqno,
								 $whoami,
								 array $pa_item_scores,
								 array $pa_item_remark)
	{
		$score_column = 'rank1';
		switch($whoami)
		{
			case 'mgr1':
				$score_column = 'rank2';
				break;
			case 'mgr2':
				$score_column = 'rank3';
				break;
			case 'mgr3':
				$score_column = 'rank4';
				break;
			default:break;
		}// end switch()
		
		$sql = <<<eof
		update hr_eva_period_type2_tw
		   set %s             = :v_score,
		       %s_selfcomm    = :v_remark,
		       update_by      = :v_update_by,
		       update_date    = sysdate,
		       update_program = :v_update_program
		 where appraisal_id   = :v_appraisal_id
		   and eva_period_type2_tw_id = :v_pa_seqno
		   and seg_segment_no = :v_company_id
eof;
		$sql = sprintf($sql,$score_column,$whoami);
		$stmt = $this->_dBConnection->Prepare($sql);
		$vals = array_values($pa_item_scores);
		$keys = array_keys($pa_item_scores);
		//pr($vals);
		//pr($keys);
		//pr($pa_item_remark);
		//$this->_dBConnection->debug = true;
		for ($i=0; $i<count($vals);$i++)
		{
			$r = $this->_dBConnection->Execute($stmt,array('v_score'=>$vals[$i],
														   'v_remark'=>$pa_item_remark[$keys[$i]],
														   'v_update_by'=>$this->_empSeqNo,
														   'v_update_program'=>self::APP_NAME,
														   'v_appraisal_id'=>$pa_form_seqno,
														   'v_pa_seqno'=>$keys[$i],
														   'v_company_id'=>$this->_companyId));
			$r = $this->_dBConnection->Affected_Rows();
			if (1 != $r)
			{
				return $this->_dBConnection->ErrorMsg();
			}// end if
		}// end for loop
		return $r;
	}// end updatePAItem()
	
	/**
	 * 员工/主管填写现状调查与发展项目
	 *
	 * @param number $pa_form_seqno
	 * @param string $whoami
	 * @param array $item_key_vals
	 * @return mixed
	 * @author Dennis 2008-11-26 17:59
	 */
	public function updateQAItem($pa_form_seqno,
								 $whoami,
								 array $item_key_vals,
								 $itemtype = 'improve')
	{
		$view_name       = $itemtype == 'improve' ? 'hr_eva_period_type5_tw' : 'hr_eva_period_type4_tw';
		$key_column_name = $itemtype == 'improve' ? 'eva_period_type5_tw_id' : 'eva_period_type4_tw_id';
		$mgr_answer_column = 'emp';
		//echo $whoami.'<hr/>';
		switch($whoami)
		{
			case 'mgr1':
				$mgr_answer_column = 'mgr';
				break;
			case 'mgr2':
				$mgr_answer_column = 'mgr2';
				break;
			default:break;
		}// end switch()
		
		$sql = <<<eof
			update %s
			   set animadversion_%s  =  :emp_val,
			       update_by         =  :update_by,
			       update_date       =  sysdate,
			       update_program    =  :update_app
			 where appraisal_id = :pa_form_seqno
			   and %s = :key_item_seqno
			   and seg_segment_no = :company_id
eof;
		$sql = sprintf($sql,$view_name,$mgr_answer_column,$key_column_name);
		//echo $sql.'<hr/>';
		//$this->_dBConnection->debug = true;
		$stmt = $this->_dBConnection->Prepare($sql);
		$vals = array_values($item_key_vals);
		$keys = array_keys($item_key_vals);

		for ($i=0; $i<count($keys);$i++)
		{
			$this->_dBConnection->Execute($stmt,array('emp_val'=>$vals[$i],
													  'update_by'=>$this->_empSeqNo,
													  'update_app'=>self::APP_NAME,
													  'pa_form_seqno'=>$pa_form_seqno,
													  'key_item_seqno'=>$keys[$i],
													  'company_id'=>$this->_companyId));
			$r = $this->_dBConnection->Affected_Rows();
			//echo 'affective rows ->'.$r.'<hr>';
			if (1 != $r)
			{
				return $this->_dBConnection->ErrorMsg();													  
			}// end if
		}// end for loop
		return $r;
	}// end updatePASelfImprove()
	
	/**
	 * 更新考核單中的上半年目標及达成说明
	 * 
	 * @param number $pa_form_seqno
	 * @param string $emp_goal
	 * @param string $emp_achieve_goal
	 * @param string $file_name
	 * @param string $file_path
	 * @param string $form_status
	 * @return boolean, if true update successfully else return error msg
	 * @author Dennis last update 2010-01-13
	 */
	public function updateEmpGoal($pa_form_seqno,
								  $emp_goal,
								  $emp_achieve_goal = '',
								  $file_name,
								  $file_path,
								  $form_status)
	{
		$update_file = !is_null($file_name) ?  
		               sprintf(' pa_att_filename = \'%s\',  pa_att_filepath = \'%s\', ',$file_name,$file_path) : '';
		$sql = <<<eof
		update hr_appraisals_base
		   set emp_achievement = :v_emp_achievement,
		   	   pre_goal		   = :v_emp_achieve_goal,
		       form_status     = :v_form_status,
		       $update_file	
		       emp_submit_date = sysdate,
		       updat_by        = :v_updat_by,
		       updat_date      = sysdate,
		       updat_program   = :v_updat_program
		 where appraisal_id = :v_pa_form_seqno
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->BeginTrans();
		$r = $this->_dBConnection->Execute($sql,array('v_emp_achievement'=>$emp_goal,
													  'v_emp_achieve_goal'=>$emp_achieve_goal,
													  'v_form_status'=>$form_status,
													  'v_updat_by'=>$this->_empSeqNo,
													  'v_updat_program'=>self::APP_NAME,
													  'v_pa_form_seqno'=>$pa_form_seqno));
    	$this->_dBConnection->CommitTrans($r); 
		$this->_dBConnection->CompleteTrans();
		$r = $this->_dBConnection->Affected_Rows();
		if (1 != $r)
		{
			return $this->_dBConnection->ErrorMsg();													  
		}// end if
		return $r;
	}// end updateEmpGoal()
	
	/**
	 * 更新考核单状态
	 * 
	 * @param nubmer $pa_form_seqno
	 * @param string $form_status
	 * @return boolean
	 * @author Dennis
	 */
	public function updatePAFormStatus($pa_form_seqno,$form_status)
	{
		$update_submit_date = '';
		switch($form_status)
		{
			case 2:
				$update_submit_date = ' emp_submit_date = sysdate,';
				break;
			case 5:
				$update_submit_date = ' emp_confirm_date = sysdate,';
				break;
			case 4:
				$update_submit_date = ' mgr1_submit_date = sysdate,';
				break;
			case 7:
				$update_submit_date = ' mgr1_confirm_date = sysdate,';
				break;
			case 9:
				$update_submit_date = ' mgr2_submit_date = sysdate,';
				break;
			case 10:
				$update_submit_date = ' mgr3_submit_date = sysdate,';
				break;
			default:break;				
		}// end switch
		$sql = <<<eof
		update hr_appraisals_base
		   set form_status     = :v_form_status,
		       updat_by        = :v_updat_by,
		       updat_date      = sysdate,
		       $update_submit_date
		       updat_program   = :v_updat_program
		 where appraisal_id = :v_pa_form_seqno
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->Execute($sql,array('v_form_status'=>$form_status,
												 'v_updat_by'=>$this->_empSeqNo,
												 'v_updat_program'=>self::APP_NAME,
												 'v_pa_form_seqno'=>$pa_form_seqno));
 		$this->_dBConnection->CommitTrans(true);   // do commit
		$this->_dBConnection->CompleteTrans();
		$r = $this->_dBConnection->Affected_Rows();
		if (1 != $r)
		{
			return $this->_dBConnection->ErrorMsg();													  
		}// end if
		return $r;
	}// end updatePAFormStatus()
	
	public function updateLastRank($pa_form_seqno,$rank,$comments,$form_status)
	{
		$update_submit_date = $form_status == 10 ? ' mgr3_submit_date = sysdate,' : '';
		$sql = <<<eof
		update hr_appraisals_base
		   set appraisal_rank          = pk_crypt_sz.encryptN(:v_rank),
		       appraisal_result_remark = :v_remark,
		       form_status             = :v_form_status,
		       updat_by                = :v_updat_by,
		       $update_submit_date
		       updat_date              = sysdate,
		       updat_program           = :v_updat_program
		 where appraisal_id = :v_pa_form_seqno
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->Execute($sql,array('v_form_status'=>$form_status,
												 'v_rank'=>$rank,
												 'v_remark'=>$comments,
												 'v_updat_by'=>$this->_empSeqNo,
												 'v_updat_program'=>self::APP_NAME,
												 'v_pa_form_seqno'=>$pa_form_seqno));
 		$this->_dBConnection->CommitTrans(true);   // do commit
		$this->_dBConnection->CompleteTrans();
		$r = $this->_dBConnection->Affected_Rows();
		if (1 != $r)
		{
			return $this->_dBConnection->ErrorMsg();													  
		}// end if
		return $r;		
	}// end updateLastRank()
	
	/**
	 * Update 核等成績
	 * 預設把最後 MGR3 的等第 Seq no 更新到 GM 核定欄位
	 * 
	 * @param number $pa_form_seqno
	 * @param number $score
	 * @param string $comments
	 * @param string $form_status
	 * @return boolean
	 * @author Dennis 2009-03-19
	 */
	public function updateLastScore($pa_form_seqno,$score,$comments,$form_status)
	{
		$update_submit_date = $form_status == $this->_formStatus['mgr3']['submitform'] ? ' mgr3_submit_date = sysdate,' : '';
		$sql = <<<eof
			update hr_appraisals_base
			   set appraisal_result        = pk_crypt_sz.encryptN(:v_score),
			       gm_confirm              = pk_crypt_sz.encryptN(:v_score1),
			       appraisal_result_remark = :v_remark,
			       form_status             = :v_form_status,
			       updat_by                = :v_updat_by,
			       $update_submit_date
			       updat_date              = sysdate,
			       updat_program           = :v_updat_program
			 where appraisal_id = :v_pa_form_seqno
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->StartTrans();
		$this->_dBConnection->Prepare($sql); // for 批量核等
		$this->_dBConnection->Execute($sql,array('v_form_status'=>$form_status,
												 'v_score'=>$score,
												 'v_score1'=>$score,
												 'v_remark'=>$comments,
												 'v_updat_by'=>$this->_empSeqNo,
												 'v_updat_program'=>self::APP_NAME,
												 'v_pa_form_seqno'=>$pa_form_seqno));
 		$this->_dBConnection->CommitTrans(true);   // do commit
		$this->_dBConnection->CompleteTrans();
		$r = $this->_dBConnection->Affected_Rows();
		if (1 != $r)
		{
			return $this->_dBConnection->ErrorMsg();													  
		}
		return $r;		
	}// end updateLastScore()	
	
	public function deleteFileName($pa_form_seqno)
	{
		$sql = <<<eof
		update hr_appraisals_base
		   set pa_att_filename = null,
		   	   pa_att_filepath = null,
		       updat_by        = :v_updat_by,
		       updat_date      = sysdate,
		       updat_program   = :v_updat_program
		 where appraisal_id    = :v_pa_form_seqno
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->Execute($sql,array('v_updat_by'=>$this->_empSeqNo,
												 'v_updat_program'=>self::APP_NAME,
												 'v_pa_form_seqno'=>$pa_form_seqno));
 		$this->_dBConnection->CommitTrans(true);   // do commit
		$this->_dBConnection->CompleteTrans();
		$r = $this->_dBConnection->Affected_Rows();
		if (1 != $r)
		{
			return $this->_dBConnection->ErrorMsg();													  
		}// end if
		return $r;
	}// end updateEmpGoal()
	
	/**
	 * 保存时设定考核单的状态
	 * 1_未提交，暫存（填寫一半）2_自評提交3_初評主管未提交
	 * 4_初評主管提交且回送員工複簽 5_員工已複簽並提交
	 * 6_初評主管填寫面談備註暫存7_初評主管填寫面談備註並提交
	 * 8_複評主管填寫並暫存9_複評主管提交 10_核定提交 11_HR關帳
	 * @param string $whoami
	 * @param string $action
	 * @return string
	 * @author Dennis
	 */
	function getFormStatus($whoami,$action)
	{
		if (!empty($whoami) && !empty($action))
		{
			return $this->_formStatus[$whoami][$action];
		}// end if
		trigger_error('Parameter value can not be null',E_USER_ERROR);
		return null;
	}// end getFunctionStatus()
	
	/**
	 * 根据分数得到等第
	 *
	 * @param number $score    成绩
	 * @param array $rank_std  评分标准
	 * @author Dennis
	 * @return string
	 */
	public function getRank($score,array $rank_std)
	{
		$c = count($rank_std);
		for ($i=$c-1; $i>=0;$i--)
		{
			if ($score >= $rank_std[$i]['SCORE1'] && $score<= $rank_std[$i]['SCORE2'])
			return $rank_std[$i]['EVALUATION_LEVEL_NO'];
		}// end loop
		return '';
	}// end getRank()
	
	/**
	 * Get Headcount by Group & Level
	 *
	 * @param number $pa_period_seqno 考核期间代码
	 * @param string $groupid		     考核群组代码
	 * @return array
	 * @author Dennis 2008-12-18
	 */
	public function getHeadcountByLevelAndGroup($pa_period_seqno,
												$group_no,
												$person_type = 'emp')
	{
		$sql = <<<eof
				select e.group_seqno,
				       e.group_desc,
				       e.level_master_seqno as level_master_id,
				       e.level_master_desc,
				       e.level_seqno,
				       e.level_id,
				       e.level_percentage,
				       count(e.emp_seqno) as headcount 
				  from (select d.group_seqno,
				               d.group_desc,
				               c.level_master_seqno,
				               c.level_master_desc,
				               c.level_seqno,
				               c.level_id,
				               c.level_percentage,
				               d.emp_seqno
				          from (select a.evaluation_level_master_id   as level_master_seqno,
				                       a.evaluation_level_master_desc as level_master_desc,
				                       b.evaluation_level_detail_id   as level_seqno,
				                       b.evaluation_level_no          as level_id,
				                       b.evaluation_level_desc        as level_desc,
				                       b.eva_percent                  as level_percentage,
				                       b.seq_no                       as order_seqno,
				                       b.seg_segment_no               as company_id
				                  from hr_evaluation_levels_master a,
				                       hr_evaluation_levels_detail b
				                 where b.seg_segment_no = a.seg_segment_no
				                   and b.evaluation_level_master_id =
				                       a.evaluation_level_master_id) c,
				               ehr_pa_emp_list_v d
				         where c.company_id = d.company_id(+)
				           and c.level_seqno = d.%s_level_seqno(+)
				           and c.company_id = :company_id
				           and d.group_seqno(+) = :group_no
				           and d.pa_period_seqno(+) = :pa_period_seqno) e
				 group by e.level_master_seqno,
				          e.level_seqno,
				          e.level_id,
				          e.level_master_desc,
				          e.group_seqno,
				          e.group_desc,
				          e.level_percentage
				 order by e.level_master_seqno
eof;
		$sql = sprintf($sql,$person_type);
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->Prepare($sql);
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 /*'pa_std_seqno'=>$pa_std_seqno,*/
														 'group_no'=>$group_no));
		
	}// end getHeadcountByLevelAndGroup()
	
	/**
	 * Get Headcount by Group
	 *
	 * @param number $pa_period_seqno 考核期间代码
	 * @param string $groupid		     考核群组代码
	 * @return array
	 * @author Dennis 2008-12-18
	 */
	public function getHeadcountByGroup($pa_period_seqno,$groupid)
	{
		$sql= <<<eof
			select pa_level_master_seqno,count(*) as headcount
			  from ehr_pa_form_v
			 where company_id = :company_id
			   and pa_period_seqno = :pa_period_seqno
			   and group_seqno = :group_id
			  group by pa_level_master_seqno
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
													   'pa_period_seqno'=>$pa_period_seqno,
													   'group_id'=>$groupid));
	}// end getHeadcountByLevelAndGroup()
	
	public function getMyFormStatus($pa_period_seqno)
	{
		$whoamis = $this->_getWhoAmIByPeriod($pa_period_seqno);
		$formstatus = '';
		if (is_array($whoamis))
		{
			foreach ($whoamis as $who)
			{
				switch ($who)
				{
					case 'mgr1':
						$formstatus.='2,3,5,6,';
						break;
					case 'mgr2':
						$formstatus.='7,8,';
						break;
					case 'mgr3':
						$formstatus.='9,';
						break;
					default:break;			
				}// end switch
			}// end foreach
			if ($formstatus) return substr($formstatus,0,-1);
		}// end if
		return $formstatus;
	}// end getMyFormStatus()
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $whoami
	 * @param unknown_type $begin_date
	 * @param unknown_type $end_date
	 * @param unknown_type $form_status
	 * @return unknown
	 */
	public function getWhoCanApprove($whoami,$begin_date,$end_date,$form_status)
	{
		$bdate = explode('-',$begin_date);
		//pr($bdate);
		$edate = explode('-',$end_date);
		//pr($edate);
		$today = explode('-',date('Y-m-d'));
		//pr($today);
		$bdataint = mktime(0,0,0,$bdate[1],$bdate[2],$bdate[0]);
		$edataint = mktime(0,0,0,$edate[1],$edate[2],$edate[0]);
		$todayint = mktime(0,0,0,$today[1],$today[2],$today[0]);
		//echo $bdataint.'<- Begin <br/>'.$edataint.'<- End <br/>'.$todayint;
		switch (strtolower($whoami)) {
			case 'mgr1':
				if (($todayint>=$bdataint && 
				     $todayint<=$edataint && 
				     $form_status != 4 && $form_status != 7 && $form_status<10) ||
				     in_array($form_status,array(2,3,5,6))) 
				     {
				     	return true;
				     }// end if
				break;
			case 'mgr2':
				if (($todayint>=$bdataint && $todayint<=$edataint && $form_status<10) ||
				     in_array($form_status,array(7,8))) 
				     {
				     	return true;
				     }// end if
				break;
			case 'mgr3':
				if (($todayint>=$bdataint && $todayint<=$edataint && $form_status<10) ||
				     $form_status == 9) 
				     {
				     	return true;
				     }// end if
				break;
			default:break;
		}// end switch
		return false;
	}// end getWhoCanApprove()
	
	/**
	 * 当前 user 可否填写考核单
	 * Logic Desc. 
	 *	1) mgr1,mgr2 提交后(form_status in mgr1->[4,7,9,10] mgr2->[9,10])不可修改成绩
	 * 	2) mgr3 提交后,在考核期间内都可以修改成绩
	 *	3) mgr1,mgr2 form_status 符合且未过其考核期间就可以评核
	 *  4) mgr1,mgr2,mgr3 只要时间到 不管 form_status 为何都可以评核
	 *
	 * @param string $whoami
	 * @param string $begin_date
	 * @param string $end_date
	 * @param string $form_status
	 * @return boolean
	 * @author Dennis 2009-04-10
	 */
	public function isCanPA($whoami,$begin_date,$end_date,$form_status)
	{
		// modify by dennis 20091222 for improve performance 
		// default cache 3600 seconds = 1 hour
		$today = $this->_dBConnection->CacheGetOne(self::DATA_CACHE_SECONDS,'select to_char(sysdate,\'yyyy-mm-dd\') from dual');
		$today = date("Y-m-d",strtotime($today));
		$begin_date = date("Y-m-d",strtotime($begin_date));
		$end_date   = date("Y-m-d",strtotime($end_date));
		$is_current = ($today >= $begin_date && $today <= $end_date);
		$is_future  = ($today < $begin_date);
		/*
		$_formStatus = array('emp' =>array('tempsubmit'=>1,
										   'submitform'=>2,
										   'submitinterview'=>5),
							 'mgr1'=>array('tempsubmit'=>3,
										   'submitform'=>4,
										   'tempsubmit_interview_comment'=>6,
										   'submitform_interview_comment'=>7),
							 'mgr2'=>array('tempsubmit'=>8,
										   'submitform'=>9,
										   'submitform_interview_comment'=>9 ), //一二階主管是同一人時
							 'mgr3'=>array('submitform'=>10,
							 			   'submitform_interview_comment'=>10)); //一二三階主管是同一人時
		*/
		$mgr1_allow_status = array($this->_formStatus['emp']['submitform'],
								   $this->_formStatus['mgr1']['tempsubmit'],
								   $this->_formStatus['emp']['submitinterview'],
								   $this->_formStatus['mgr1']['tempsubmit_interview_comment']);
								   
		$mgr1_deny_status = array($this->_formStatus['mgr1']['submitform'],
								  $this->_formStatus['mgr1']['submitform_interview_comment'],
								  $this->_formStatus['mgr2']['submitform'],
								  $this->_formStatus['mgr3']['submitform']);
								  
		$mgr2_allow_status = array($this->_formStatus['mgr1']['submitform_interview_comment'],
								   $this->_formStatus['mgr2']['tempsubmit']);
								   
		$mgr2_deny_status = array($this->_formStatus['mgr2']['submitform'],
								  $this->_formStatus['mgr3']['submitform']);
										  
		$mgr3_allow_status = array($this->_formStatus['mgr2']['submitform'],
								   $this->_formStatus['mgr3']['submitform']);

		$mgr_ontime_status = array($this->_formStatus['emp']['tempsubmit'],
								   $this->_formStatus['emp']['submitform'],
								   $this->_formStatus['mgr1']['tempsubmit_interview_comment']);								   
		//自評已提交/等待員工面談确認
		// 已经到"我"考核时间 或 时间在将来								  
		$in_time = 	($is_current || $is_future);
		
		/**
		 * 状态是  null (员工或前n阶没有人填写过考核单)
		 */
		if (is_null($form_status) && $is_current) return true;
		switch (strtolower($whoami)) {
			case 'mgr1':
				if (in_array($form_status,$mgr1_allow_status) && $in_time) return true;
				if (!in_array($form_status,$mgr1_deny_status) && $is_current) return true;
				if (in_array($form_status,$mgr_ontime_status) && $is_current) return true;
				break;
			case 'mgr2':
				if (in_array($form_status,$mgr2_allow_status) && $in_time) return true;
				if (!in_array($form_status,$mgr2_deny_status) && $is_current) return true;
				if (in_array($form_status,$mgr_ontime_status) && $is_current) return true;
				break;
			case 'mgr3':
				if (in_array($form_status,$mgr3_allow_status) && $in_time) return true;
				if ($is_current) return true;
				break;
			default:break;
		}// end switch
		return false;
	}// end isCanPA()
	
	/**
	 * 根據評核標準 Get 評核標準細項
	 *
	 * @param number $std_level_seqno
	 * @return array
	 * @author Dennis 2009-03-25
	 * 
	 */
	private  function _getEvaStdList($std_level_master_seqno)
	{
		$sql = <<<eof
			select level_master_seqno,
			       level_master_desc,
			       level_seqno,
			       level_id,
			       level_desc,
			       level_percentage,
			       order_seqno
			  from ehr_pa_std_v
			 where company_id          = :company_id
	           and level_master_seqno  = :std_level_master_seqno
	        order by order_seqno
eof;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->_dBConnection->Prepare($sql);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
													     'std_level_master_seqno'=>$std_level_master_seqno));
	}// end getEvaStdList()
	
	private function _getPercentByGMLevel($pa_period_seqno,$std_master_seqno,$group_id)
	{
		/**
		 * p_company_id       in varchar2,
                                p_period_seqno     in number,
                                p_std_master_seqno in number,
                                p_level_seqno      in number,
                                p_group_id         in varchar2 default '',
                                p_mgr_emp_seqno    in varchar2
		 */
		$sql = <<<eof
		select level_master_seqno as std_master_seqno,
		       level_master_desc  as std_mater_desc,
		       level_seqno        as level_seqno,
		       level_id           as level_id,
		       level_desc         as level_desc,
		       level_percentage   as def_percent,
		       order_seqno        as order_seqno,
		       pk_performance.get_percent_by_level(company_id,
		                                           :pa_period_seqno,
		                                           level_master_seqno,
		                                           level_seqno,
		                                           :group_id,
		                                           :mgr_emp_seqno) as fact_percent
		  from ehr_pa_std_v
		 where company_id         = :company_id
		   and level_master_seqno = :std_master_seqno
		 order by order_seqno
eof;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
												  		 'pa_period_seqno'=>$pa_period_seqno,
												  		 'std_master_seqno'=>$std_master_seqno,
												  		 'group_id'=>$group_id,
														 'mgr_emp_seqno'=>$this->_empSeqNo));
	}
	
	/**
	 * 根据 Group 统计已经有考核成绩的人数
	 *
	 * @param number $pa_period_seqno
	 * @param strig $group_no 群组代码
	 * @param string $by_who 统计自评(emp),初评(mgr1), 复评(mgr2), 核等 (mgr3)
	 * @return array
	 * @author Dennis 2009-03-14
	 */
	public function getHeadcountByGroupFact($pa_period_seqno,$group_no,$by_who = 'emp')
	{
		$sql = <<<eof
			select group_seqno,
			       group_desc,
			       %s_level_seqno,
			       level_std_seqno,
			       count(emp_seqno) as headcount
			  from ehr_pa_emp_list_v
			 where company_id = :company_id
			   and group_seqno = :group_no
			   /*and %s_level_seqno is not null*/
			   and pa_period_seqno = :pa_period_seqno
			   and pk_performance.f_get_whoami(company_id,
	                                           pa_period_seqno,
	                                           pa_form_seqno,
	                                           :user_emp_seqno) != '0'
			 group by group_seqno, 
			          group_desc,
			          level_std_seqno, 
			          %s_level_seqno
eof;
		$sql = sprintf($sql,$by_who,$by_who,$by_who);
		$this->_dBConnection->Prepare($sql);
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs = $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
												  		'pa_period_seqno'=>$pa_period_seqno,
												  		'group_no'=>$group_no,
														'user_emp_seqno'=>$this->_empSeqNo));
		//pr($rs);
		$pa_std_list = $this->_getEvaStdList($rs[0]['LEVEL_STD_SEQNO']);
		$c  = count($pa_std_list);
		$c1 = count($rs);
		if ($c>0)
		{
			for ($i=0; $i<$c; $i++)
			{
				if ($c1 >0)
				{
					for ($j=0; $j<$c1; $j++)
					{
						if ($pa_std_list[$i]['LEVEL_MASTER_SEQNO'] == $rs[$j]['LEVEL_STD_SEQNO'] &&
						    $pa_std_list[$i]['LEVEL_SEQNO'] == $rs[$j][strtoupper($by_who).'_LEVEL_SEQNO'])
						{
							$pa_std_list[$i]['HEADCOUNT'] = $rs[$j]['HEADCOUNT'];
						}else{
							$pa_std_list[$i]['HEADCOUNT'] = 0;
						}// end if
					}
				}else{
					$pa_std_list[$i]['HEADCOUNT'] = 0;
				}
			}
		}
		return $pa_std_list;
	}// end getHeadcountByGroupFact()
	
	/**
	 * 我的年度目标
	 * @return string
	 */
	public function getCurrentYearGoal()
	{
		
		$sql = <<<eof
			select emp_goal
			  from ehr_pa_form_v
			 where company_id = :company_id
			   and pa_emp_seqno = :emp_seqno
			   and pa_form_seqno = (select max(pa_form_seqno)
			                          from ehr_pa_form_v
			                         where company_id = :company_id1
			                           and pa_emp_seqno = :emp_seqno1)
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetOne($sql,array('company_id'=>$this->_companyId,
												  	   'emp_seqno'=>$this->_empSeqNo,
												  	   'company_id1'=>$this->_companyId,
												  	   'emp_seqno1'=>$this->_empSeqNo));
	}// end getMyPAPeriodList()
	
	/**
	 * 當一二階主管是同一主管時,在第一階面談提交時, Update 第二階的成績同第一階一樣
	 *
	 * @param number $pa_form_seqno 考核單號
	 * @return boolean
	 * @author Dennis 2009-03-19
	 */
	public function autoUpdatePAItemScore($pa_form_seqno)
	{
		$sql = <<<eof
		update hr_eva_period_type2_tw
		   set rank3          = rank2,
		       update_by      = :v_update_by,
		       update_date    = sysdate,
		       update_program = :v_update_program
		 where appraisal_id   = :v_appraisal_id
		   and eva_period_type2_tw_id = eva_period_type2_tw_id
		   and seg_segment_no = :v_company_id
eof;
		$this->_dBConnection->Execute($sql,array('v_update_by'=>$this->_empSeqNo,
												 'v_update_program'=>self::APP_NAME,
												 'v_appraisal_id'=>$pa_form_seqno,
												 'v_company_id'=>$this->_companyId));
		$r = $this->_dBConnection->Affected_Rows();
		if ($r<1)
		{
			return $this->_dBConnection->ErrorMsg();
		}// end if
		return $r;
	}//updatePAItemScore()
	
	/**
	 * 當前使用者是考核流程中的一二三階或是二三階的時候自動更新核等成績.
	 *
	 * @param number $pa_form_seqno
	 * @param string $whoami   default 'mgr1'
	 * @return boolean
	 * @author Dennis 2009-03-19
	 */
	public function autoUpdateLastScore($pa_form_seqno,$whoami = 'mgr1')
	{
		$score_item = 'rank1';
		switch ($whoami)
		{
			case 'mgr1':
				$score_item = 'rank2';
				break;
			case 'mgr2':
				$score_item = 'rank3';
			default:break;
		}
		$sql = <<<eof
			select sum(%s) as score
			  from hr_eva_period_type2_tw
			 where appraisal_id = :pa_form_seqno
			   and seg_segment_no = :v_company_id
eof;
		//$this->_dBConnection->debug = true;
		$sql = sprintf($sql,$score_item);
		$score = $this->_dBConnection->GetOne($sql,array('pa_form_seqno'=>$pa_form_seqno,
												         'v_company_id'=>$this->_companyId));
		return $this->updateLastScore($pa_form_seqno,$score,'',$this->_formStatus['mgr3']['submitform']);
	}// end autoUpdateLastScore()
	
	
	/**
	 * 挑我考核過的相關員工統計
	 *
	 * @param unknown_type $pa_period_seqno
	 * @return unknown
	 */
	/*
	public function getGroupListByMgrxxx($pa_period_seqno)
	{
		$sql = <<<eof
			select a.pa_period_seqno as period_seqno,
			       a.pa_period_desc as period_desc,
			       a.group_seqno as group_id,
			       a.group_desc as group_desc,
			       b.evaluation_level_master_id as std_seqno,
			       b.evaluation_level_master_desc as std_desc,
			       count(a.pa_emp_seqno) as headcount
			  from ehr_pa_form_v a, hr_evaluation_levels_master b
			 where a.company_id = b.seg_segment_no
			   and a.pa_level_master_seqno = b.evaluation_level_master_id
			   and a.company_id = :company_id
			   and (a.mgr1_emp_seqno = :mgr1_emp_seqno or
			        a.mgr2_emp_seqno = :mgr2_emp_seqno or
			        a.mgr3_emp_seqno = :mgr3_emp_seqno)
			   and a.pa_period_seqno = :pa_period_seqno
			   and (a.pa_end_date< sysdate or a.form_status >=10)
			 group by a.pa_period_seqno,
			          a.group_seqno,
			          a.group_desc,
			          a.pa_period_desc,
			          b.evaluation_level_master_id,
			          b.evaluation_level_master_desc
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'mgr1_emp_seqno'=>$this->_empSeqNo,
														 'mgr2_emp_seqno'=>$this->_empSeqNo,
														 'mgr3_emp_seqno'=>$this->_empSeqNo));
	}
	*/
	/**
	 * 根據 GM 核定 Level 統計人數及比例
	 *
	 * @param number $pa_period_seqno
	 * @param string $group_id
	 * @param number $std_master_seqno
	 * @return 2-d array
	 * @author Dennis 2009-03-26
	 */
	/*
	public function getHeadcountByGMLevel($pa_period_seqno,
									  	  $group_id,
									  	  $std_master_seqno = '')
	{
		$where = empty($std_master_seqno) ? '' : ' and a.level_std_seqno = '.$std_master_seqno;
		$sql = <<<eof
		select a.group_seqno,
		       b.evaluation_level_master_id   as std_seqno,
			   b.evaluation_level_master_desc as std_desc,
		       a.gm_rank,
		       a.gm_rank_seqno,		      
		       count(a.emp_seqno) as headcount		                                           
		  from ehr_pa_emp_list_v a,
		       hr_evaluation_levels_master b
		 where a.company_id      = b.seg_segment_no
		   and a.level_std_seqno = b.evaluation_level_master_id
		   and company_id        = :company_id
		   and pa_period_seqno   = :pa_period_seqno
		   and group_seqno       = :group_id
		   and (a.pa_end_date < sysdate or a.form_status >=10)
		   and (mgr1_emp_seqno = :mgr1_emp_seqno or
			    mgr2_emp_seqno = :mgr2_emp_seqno or
			    mgr3_emp_seqno = :mgr3_emp_seqno)
		   $where
		 group by a.group_seqno,
		          b.evaluation_level_master_id,
		          b.evaluation_level_master_desc,
		          a.gm_rank,
		          a.gm_rank_seqno
eof;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->_dBConnection->debug = true;
		$rs =  $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'group_id'=>$group_id,
														 'mgr1_emp_seqno'=>$this->_empSeqNo,
														 'mgr2_emp_seqno'=>$this->_empSeqNo,
														 'mgr3_emp_seqno'=>$this->_empSeqNo));
		//pr($rs);
		$rc = count($rs);
		$std_master_seqnos = array();
		for ($i=0; $i<$rc; $i++)
		{
			$std_master_seqnos[$i] = $rs[$i]['STD_SEQNO'];
		}
		// get unique level standard master seq no
		$std_master_seqnos = array_values(array_unique($std_master_seqnos));
		// store level standard by master seq no
		$pa_std_list = array();
		if (empty($std_master_seqno))
		{
			//pr($std_master_seqnos);
			for ($i=0; $i<count($std_master_seqnos); $i++)
			{
				$pa_std_list[$i] = $this->_getEvaStdList($std_master_seqnos[$i]);
			}// end for loop
		}else{
			$pa_std_list[0]= $this->_getEvaStdList($std_master_seqno);
		}// end if
		//pr($pa_std_list);
		// 重組 recordset 為需要的格式
		$x = 0;
		$result = array();
		for ($k=0; $k<count($pa_std_list); $k++)
		{
			for ($m=0; $m<count($pa_std_list[$k]);$m++)
			{
				for ($j=0; $j<count($rs); $j++)
				{
					if ($rs[$j]['STD_SEQNO'] == $pa_std_list[$k][$m]['LEVEL_MASTER_SEQNO'] &&
					    $rs[$j]['GM_RANK_SEQNO'] == $pa_std_list[$k][$m]['LEVEL_SEQNO'])
					{
						$result[$x]['GROUP_SEQNO'] = $rs[$j]['GROUP_SEQNO'];
						$result[$x]['STD_SEQNO'] = $rs[$j]['STD_SEQNO'];
						$result[$x]['STD_DESC']  = $rs[$j]['STD_DESC'];
						$result[$x]['GM_RANK_SEQNO'] = $rs[$j]['GM_RANK_SEQNO'];
						$result[$x]['GM_RANK']   = $rs[$j]['GM_RANK'];
						$result[$x]['HEADCOUNT'] = $rs[$j]['HEADCOUNT'];
						$result[$x]['DEF_PERC']  = $pa_std_list[$k][$m]['LEVEL_PERCENTAGE'];
						$result[$x]['FACT_PERC'] = $rs[$j]['FACT_PERC'];
						$result[$x]['DIFF_PERC'] = $rs[$j]['FACT_PERC']-$pa_std_list[$k][$m]['LEVEL_PERCENTAGE'];
					}else{
						$result[$x]['GROUP_SEQNO'] = $rs[$j]['GROUP_SEQNO'];
						$result[$x]['STD_SEQNO'] = $pa_std_list[$k][$m]['LEVEL_MASTER_SEQNO'];
						$result[$x]['STD_DESC']  = $pa_std_list[$k][$m]['LEVEL_MASTER_DESC'];
						$result[$x]['GM_RANK_SEQNO'] =$pa_std_list[$k][$m]['LEVEL_SEQNO'];
						$result[$x]['GM_RANK']   = $pa_std_list[$k][$m]['LEVEL_ID'];
						$result[$x]['HEADCOUNT'] = 0;
						$result[$x]['FACT_PERC'] = 0;
						$result[$x]['DEF_PERC']  = $pa_std_list[$k][$m]['LEVEL_PERCENTAGE'];
						$result[$x]['DIFF_PERC'] = 0 - $pa_std_list[$k][$m]['LEVEL_PERCENTAGE'];
					}//end if
				}// end for loop
				$x++;
			}// end for loop
		}// end for loop
		return $result;
	}// end getGMLevelByGroup()
	*/
	/**
	 * 根據 GM 核定 Level 統計人數及比例
	 *
	 * @param number $pa_period_seqno
	 * @param string $group_id
	 * @param number $std_master_seqno
	 * @return 2-d array
	 * @author Dennis 2009-03-26
	 */
	public function getHeadcountByGMLevel($pa_period_seqno,
									  	    $group_id,
									  	    $std_master_seqno = '')
	{
		$where = empty($std_master_seqno) ? '' : ' and a.level_std_seqno = '.$std_master_seqno;
		$sql = <<<eof
		select a.group_seqno                  as group_id,
		       b.evaluation_level_master_id   as std_master_seqno,
			   b.evaluation_level_master_desc as std_master_desc,
		       a.gm_rank                      as gm_level_id,
		       a.gm_rank_seqno                as gm_level_seqno,		      
		       count(a.emp_seqno)             as headcount		                                           
		  from ehr_pa_emp_list_v           a,
		       hr_evaluation_levels_master b
		 where a.company_id      = b.seg_segment_no
		   and a.level_std_seqno = b.evaluation_level_master_id
		   and company_id        = :company_id
		   and pa_period_seqno   = :pa_period_seqno
		   and group_seqno       = :group_id
		   and (a.pa_end_date < sysdate or a.form_status >=10)
		   and (mgr1_emp_seqno = :mgr1_emp_seqno or
			    mgr2_emp_seqno = :mgr2_emp_seqno or
			    mgr3_emp_seqno = :mgr3_emp_seqno)
		   $where
		 group by a.group_seqno,
		          b.evaluation_level_master_id,
		          b.evaluation_level_master_desc,
		          a.gm_rank,
		          a.gm_rank_seqno
eof;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		$rs =  $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'group_id'=>$group_id,
														 'mgr1_emp_seqno'=>$this->_empSeqNo,
														 'mgr2_emp_seqno'=>$this->_empSeqNo,
														 'mgr3_emp_seqno'=>$this->_empSeqNo));
		//pr($rs);
		$rc = count($rs);
		$percent_by_level = $this->_getPercentByGMLevel($pa_period_seqno,
														$std_master_seqno,
														$group_id);
		for ($i=0; $i<count($percent_by_level);$i++)
		{
			$percent_by_level[$i]['HEADCOUNT']    = 0;
			$percent_by_level[$i]['DIFF_PERCENT'] = 0 - $percent_by_level[$i]['DEF_PERCENT'];
			for($j=0; $j<$rc; $j++)
			{
				if ($rs[$j]['STD_MASTER_SEQNO'] == $percent_by_level[$i]['STD_MASTER_SEQNO'] &&
				    $rs[$j]['GM_LEVEL_SEQNO']   == $percent_by_level[$i]['LEVEL_SEQNO'])
			    {
			    	$percent_by_level[$i]['HEADCOUNT']    = $rs[$j]['HEADCOUNT'];
			    	$percent_by_level[$i]['DIFF_PERCENT'] = $percent_by_level[$i]['FACT_PERCENT'] - $percent_by_level[$i]['DEF_PERCENT'];
			    	break; 
			    }
			}
			$percent_by_level[$i]['GROUP_SEQNO'] = $group_id;
		}
		//pr($percent_by_level);
		return $percent_by_level;
		// 重組 recordset 為需要的格式
	}// end getGMLevelByGroup()
	
	/**
	 * Get 被"我"评核过的每个等第下的员工清单
	 *
	 * @param number $pa_period_seqno
	 * @param string $pa_group_id
	 * @param number $pa_std_master_seqno
	 * @param number $pa_level_seqno
	 */
	public function getPAScoreDetail($pa_period_seqno,
									 $pa_group_id,
									 $pa_std_master_seqno = null,
									 $pa_level_seqno      = null)
	{
		$where  = is_null($pa_std_master_seqno) ? ''  :  sprintf(' and level_std_seqno = %s ',$pa_std_master_seqno);
		$where .= is_null($pa_level_seqno)      ? ''  :  sprintf(' and gm_rank_seqno = %s ',$pa_level_seqno);
		$sql = <<<eof
			select group_desc,
			       emp_id,
			       emp_name,
			       dept_id,
			       dept_name,
			       emp_score,
			       emp_rank,
			       mgr1_score,
			       mgr1_rank,
			       mgr2_score,
			       mgr2_rank,
			       mgr3_score,
			       mgr3_rank,
			       mgr1_score,
			       nvl(gm_score,0) as gm_score,
			       pk_personnel_msg.f_title_msg(company_id,
			                                    pk_history_data.f_get_value(company_id,
			                                                                emp_seqno,
			                                                                pa_end_date,
			                                                                '5'),
			                                    '02') as title_desc
			  from ehr_pa_emp_list_v
			 where pa_period_seqno = :pa_period_seqno
			   and company_id      = :company_id
			   and group_seqno     = :group_id
			   $where
			   and (mgr1_emp_seqno = :mgr1_emp_seqno or
			        mgr2_emp_seqno = :mgr2_emp_seqno or
			        mgr3_emp_seqno = :mgr3_emp_seqno)
			   and (pa_end_date < sysdate or form_status>=10)
			order by gm_score
eof;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		return  $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'group_id'=>$pa_group_id,
														 'mgr1_emp_seqno'=>$this->_empSeqNo,
														 'mgr2_emp_seqno'=>$this->_empSeqNo,
														 'mgr3_emp_seqno'=>$this->_empSeqNo));
		

	}// end getPAScoreDetail()
	
	public function getHeadCountByGroupSTD($pa_period_seqno,
										   $group_id,
										   $std_master_seqno,
										   $std_master_desc,
										   $bywho = 'emp'){
		$sql = <<<eof
			select '%s' as std_master_desc,
				   pa_period_seqno,
			       group_seqno,
			       level_std_seqno as std_master_seqno,
			       %s_level_seqno,
			       count(emp_seqno) as headcount
			  from ehr_pa_emp_list_v
			 where company_id      = :company_id
			   and pa_period_seqno = :pa_period_seqno
			   and level_std_seqno = :std_master_seqno
			   and group_seqno     = :group_id
			   and (mgr1_emp_seqno = :mgr1_emp_seqno or 
			        mgr2_emp_seqno = :mgr2_emp_seqno or
			        mgr3_emp_seqno = :mgr3_emp_seqno )
			 group by pa_period_seqno, 
			          group_seqno, 
			          level_std_seqno,
			          %s_level_seqno
eof;
		$sql = sprintf($sql,$std_master_desc,$bywho,$bywho);
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->Prepare($sql);
		$rs =   $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														  'pa_period_seqno'=>$pa_period_seqno,
														  'std_master_seqno'=>$std_master_seqno,
														  'group_id'=>$group_id,
														  'mgr1_emp_seqno'=>$this->_empSeqNo,
														  'mgr2_emp_seqno'=>$this->_empSeqNo,
														  'mgr3_emp_seqno'=>$this->_empSeqNo));
		//pr($rs);
		/**
		 * Array
		(
		    [0] => Array
		        (
		            [STD_MASTER_DESC] => 主管适用
		            [PA_PERIOD_SEQNO] => 1
		            [GROUP_SEQNO] => B
		            [STD_MASTER_SEQNO] => 1
		            [EMP_LEVEL_SEQNO] =>  1
		            [HEADCOUNT] => 1
		        )
			[1] => Array
		        (
		            [STD_MASTER_DESC] => 主管适用
		            [PA_PERIOD_SEQNO] => 1
		            [GROUP_SEQNO] => B
		            [STD_MASTER_SEQNO] => 1
		            [EMP_LEVEL_SEQNO] => 2
		            [HEADCOUNT] => 1
		        )
		
		)
		Array
		(
		    [0] => Array
		        (
		            [LEVEL_MASTER_SEQNO] => 1
		            [LEVEL_MASTER_DESC] => 主管适用
		            [LEVEL_SEQNO] => 2
		            [LEVEL_ID] => A
		            [LEVEL_DESC] => A
		            [LEVEL_PERCENTAGE] => 10
		            [ORDER_SEQNO] => 1
		        )
		)

		 * 
		 */
		$result = array();
		
		if (count($rs)>0)
		{
			$pa_std_list = $this->_getEvaStdList($std_master_seqno);
			$percent_summary = $this->getFactPercentByStd($pa_std_list,$pa_period_seqno,$std_master_seqno,$bywho);
			//pr($percent_summary);
			for ($i=0; $i<count($pa_std_list); $i++)
			{
				for ($j=0; $j<count($rs); $j++)
				{
					if($pa_std_list[$i]['LEVEL_MASTER_SEQNO'] == $rs[$j]['STD_MASTER_SEQNO'] &&
					   $pa_std_list[$i]['LEVEL_SEQNO']        == $rs[$j][strtoupper($bywho).'_LEVEL_SEQNO'])
					{
						$result[$i]['PA_PERIOD_SEQNO']   = $rs[$j]['PA_PERIOD_SEQNO'];
						$result[$i]['GROUP_SEQNO']       = $rs[$j]['GROUP_SEQNO'];
						$result[$i]['STD_MASTER_SEQNO']  = $pa_std_list[$i]['LEVEL_MASTER_SEQNO'];
						$result[$i]['LEVEL_SEQNO']       = $pa_std_list[$i]['LEVEL_SEQNO'];
						$result[$i]['LEVEL_ID']          = $pa_std_list[$i]['LEVEL_ID'];
						$result[$i]['LEVEL_DESC'] 	     = $pa_std_list[$i]['LEVEL_DESC'];
						$result[$i]['DEF_PERC']          = $pa_std_list[$i]['LEVEL_PERCENTAGE'];
						$result[$i]['HEADCOUNT'] 		 = $rs[$j]['HEADCOUNT'];
						break;
					}else{
						$result[$i]['PA_PERIOD_SEQNO']   = $rs[$j]['PA_PERIOD_SEQNO'];
						$result[$i]['GROUP_SEQNO']       = $rs[$j]['GROUP_SEQNO'];
						$result[$i]['STD_MASTER_SEQNO']  = $pa_std_list[$i]['LEVEL_MASTER_SEQNO'];
						$result[$i]['LEVEL_SEQNO']       = $pa_std_list[$i]['LEVEL_SEQNO'];
						$result[$i]['LEVEL_ID']          = $pa_std_list[$i]['LEVEL_ID'];
						$result[$i]['LEVEL_DESC'] 	     = $pa_std_list[$i]['LEVEL_DESC'];
						$result[$i]['DEF_PERC']          = $pa_std_list[$i]['LEVEL_PERCENTAGE'];
						$result[$i]['HEADCOUNT'] 		 = 0;
					}// end if
				}// end for loop
				$result[$i]['STD_MASTER_DESC'] = $std_master_desc; 
				for($k=0; $k<count($percent_summary); $k++)
				{
					if ($percent_summary[$k]['STD_MASTER_SEQNO'] == $pa_std_list[$i]['LEVEL_MASTER_SEQNO']&&
					    $percent_summary[$k]['LEVEL_SEQNO'] == $pa_std_list[$i]['LEVEL_SEQNO'])
					    {
					    	$result[$i]['FACT_PERCENT'] = $percent_summary[$k]['FACT_PERCENT'];
					    	$result[$i]['DIFF_PERCENT'] = $percent_summary[$k]['DIFF_PERCENT'];
					    	break;
					    }
				}
			}// end for loop
		}// end if
		//pr($result);
		return $result;
	}// end getHeadCountByGroupSTD()
	
	/**
	 * 挑每个评核标准(master, 主管/非主管)下的人数
	 * 跟使用者权限无关
	 * @param number $pa_period_seqno  考核单号
	 * @param number $std_master_seqno 评核标准主档 seqno
	 * @return array
	 * @author Dennis 2009-03-30
	 */
	private function _getHeadcountBySTDDef($pa_period_seqno,$std_master_seqno)
	{
		$sql = <<<eof
			select level_std_seqno  as std_master_seqno, 
			       count(emp_seqno) as headcount
			  from ehr_pa_emp_list_v
			 where company_id      = :company_id
			   and pa_period_seqno = :pa_period_seqno
			   and level_std_seqno = :std_master_seqno
			 group by level_std_seqno
eof;
 		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		return $this->_dBConnection->GetRow($sql,array('company_id'=>$this->_companyId,
													   'pa_period_seqno'=>$pa_period_seqno,
													   'std_master_seqno'=>$std_master_seqno));
	}// end getHeadcountBySTD()
	
	private function _getHeadcountBySTDFac($pa_period_seqno,$std_master_seqno,$bywho)
	{
		$sql = <<<eof
			select level_std_seqno  as std_master_senqo,
				   %s_level_seqno   as level_seqno, 
			       count(emp_seqno) as headcount
			  from ehr_pa_emp_list_v
			 where company_id      = :company_id
			   and pa_period_seqno = :pa_period_seqno
			   and level_std_seqno = :std_master_seqno
			 group by level_std_seqno,%s_level_seqno
eof;
		$sql = sprintf($sql,$bywho,$bywho);
 		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
													    'pa_period_seqno'=>$pa_period_seqno,
													    'std_master_seqno'=>$std_master_seqno));
	}// end getHeadcountBySTDFac()
	
	/**
	 * 根据评分标准,取得每个等第现有人数所点的百分比
	 *
	 * @param number $pa_period_seqno
	 * @param number $std_master_seqno
	 * @param string $bywho
	 * @return array
	 * @author Dennis 2009-03-30
	 */
	public function getFactPercentByStd($pa_std_list,$pa_period_seqno,$std_master_seqno,$bywho)
	{
		//$pa_std_list = $this->_getEvaStdList($std_master_seqno);
		//pr($pa_std_list);
		$fac_headcount_by_std = $this->_getHeadcountBySTDFac($pa_period_seqno,$std_master_seqno,$bywho);
		//pr($fac_headcount_by_std);
		$def_total_headcount  = $this->_getHeadcountBySTDDef($pa_period_seqno,$std_master_seqno);
		//pr($def_total_headcount);
		for ($i=0; $i<count($pa_std_list); $i++)
		{
			$pa_std_list[$i]['PA_PERIOD_SEQNO'] = $pa_period_seqno;
			$pa_std_list[$i]['STD_MASTER_SEQNO'] = $std_master_seqno;
			for ($j=0; $j<count($fac_headcount_by_std); $j++)
			{
				if ($pa_std_list[$i]['LEVEL_SEQNO'] == $fac_headcount_by_std[$j]['LEVEL_SEQNO'])
				{
					$pa_std_list[$i]['FACT_PERCENT'] = round($fac_headcount_by_std[$j]['HEADCOUNT']/$def_total_headcount['HEADCOUNT'],2)*100;
					break;
				}else{
					$pa_std_list[$i]['FACT_PERCENT'] = 0;
				}// end if
			}// end loop 
			$pa_std_list[$i]['DIFF_PERCENT'] = $pa_std_list[$i]['FACT_PERCENT'] - $pa_std_list[$i]['LEVEL_PERCENTAGE'];
		}// end loop
		return $pa_std_list;
	}// end getFactPercentByStd()
	
	/**
	 * Get 我在指定考核期间/群组/评核标准中的最大角色
	 *
	 * @param  number $pa_period_seqno  考核期间代码
	 * @param  string $group_id         群组代码
	 * @param  number $pa_std_seqno     评核标准代码
	 * @return string 
	 * @author Dennis 2009-04-23
	 */
	public function getMaxRole($pa_period_seqno,$group_id,$pa_std_seqno)
	{
		$sql = <<<eof
		select mgr1_emp_seqno, 
		       mgr2_emp_seqno,
		       mgr3_emp_seqno
		  from ehr_pa_form_v
		 where company_id = :company_id
		   and pa_period_seqno = :pa_period_seqno
		   and group_seqno = :group_id
		   and std_master_seqno = :pa_std_seqno
		   and (mgr1_emp_seqno = :emp_seqno1 or
		        mgr2_emp_seqno = :emp_seqno2 or
		        mgr3_emp_seqno = :emp_seqno3)
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs =  $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
													     'pa_period_seqno'=>$pa_period_seqno,
													     'group_id'=>$group_id,
													     'pa_std_seqno'=>$pa_std_seqno,
													     'emp_seqno1'=>$this->_empSeqNo,
													     'emp_seqno2'=>$this->_empSeqNo,
													     'emp_seqno3'=>$this->_empSeqNo));
		// init array
		$mgr1_array = $mgr1_array = $mgr1_array = array();
		for($i=0; $i<count($rs); $i++)
		{
			$mgr1_array[] = $rs[$i]['MGR1_EMP_SEQNO'];
			$mgr2_array[] = $rs[$i]['MGR2_EMP_SEQNO'];
			$mgr3_array[] = $rs[$i]['MGR3_EMP_SEQNO'];
		}
		if (in_array($this->_empSeqNo,$mgr3_array)) return 'mgr3';
		if (in_array($this->_empSeqNo,$mgr2_array)) return 'mgr2';
		if (in_array($this->_empSeqNo,$mgr1_array)) return 'mgr1';
	}// getMaxRole()
	
	/**
	 * 根据考核期间流水号/群组代码/评分标准主档代码统计相关人数
	 *
	 * @param number $pa_period_seqno   考核期间流水号
	 * @param string $group_id          群组代码
	 * @param number $std_master_seqno  评分标准主档代码
	 * @param string $max_role_id       最大角色代码 (mgr3/mgr2/mgr1)
	 * @param string $pre_role_id       前一角色代码(mgr2/mgr1/emp)
	 * @return array 
	 * @author Dennis 2009-04-27
	 */
	public function getHeadcountByRole($pa_period_seqno,
	                                   $group_id,
	                                   $std_master_seqno,
	                                   $max_role_id,
	                                   $pre_role_id)
	{
		$sql = <<<eof
			select b.evaluation_level_detail_id as level_seqno,
			       b.evaluation_level_no        as level_id,
			       count(a.pa_emp_seqno)        as had_score_headcount
			  from ehr_pa_form_v a, 
			       hr_evaluation_levels_detail b
			 where a.company_id(+)            = b.seg_segment_no
			   and a.pa_level_master_seqno(+) = b.evaluation_level_master_id
			   and a.%s_level_seqno(+)        = b.evaluation_level_detail_id
			   and a.company_id(+)            = :company_id
			   and a.group_seqno(+)           = :group_id
			   and a.pa_period_seqno(+)       = :pa_period_seqno
			   and b.evaluation_level_master_id = :std_master_seqno
			   and a.%s_level_seqno(+)        > 0
			   and a.%s_emp_seqno(+)          = :emp_seqno
			 group by b.evaluation_level_detail_id, b.evaluation_level_no
			 order by b.evaluation_level_detail_id
eof;
		$sql = sprintf($sql,$pre_role_id,$pre_role_id,$max_role_id);
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'group_id'=>$group_id,
														 'std_master_seqno'=>$std_master_seqno,
														 'emp_seqno'=>$this->_empSeqNo));
	}// end getHeadcoundByRole()
	
	/**
	 * 根据考核主管设定中(初/复/核)主管是其本人的人数(不管有没有分数)×评分标准比例
	 * 
	 *
	 * @param number $pa_period_seqno
	 * @param string $group_id
	 * @param number $std_master_seqno
	 * @param string $roleid
	 * @return array
	 * @author Dennis 20090427
	 */
	public function getDefaultHeadcountByPercent($pa_period_seqno,
			                                     $group_id,
			                                     $std_master_seqno,
			                                     $roleid)
	{
		$sql  = <<<eof
		select b.evaluation_level_detail_id                          as level_seqno,
		       b.evaluation_level_no                                 as level_id,
		       b.eva_percent									     as rule_percent,
		       round(count(a.pa_emp_seqno) * b.eva_percent / 100, 2) as rule_headcount,
		       count(a.pa_emp_seqno)								 as headcount
		  from ehr_pa_form_v a, 
		       hr_evaluation_levels_detail b
		 where a.company_id(+) = b.seg_segment_no
		   and a.pa_level_master_seqno(+) = b.evaluation_level_master_id
		   and a.company_id(+) = :company_id
		   and a.group_seqno(+) = :group_id
		   and a.pa_period_seqno(+) = :pa_period_seqno
		   and b.evaluation_level_master_id = :std_master_seqno
		   and a.%s_emp_seqno(+) = :emp_seqno
		 group by b.evaluation_level_detail_id,
		          b.evaluation_level_no,
		          b.eva_percent
		 order by b.evaluation_level_detail_id
eof;
		$sql = sprintf($sql,$roleid,$roleid,$roleid);
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConnection->debug = true;
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'pa_period_seqno'=>$pa_period_seqno,
														 'group_id'=>$group_id,
														 'std_master_seqno'=>$std_master_seqno,
														 'emp_seqno'=>$this->_empSeqNo));
	}
	
	/**
	 * help function 
	 * @param $totalrows number  资料总笔数
	 * @param $pagesize  number  每页显示的资料笔数
	 * @return void
	 * @author Dennis add 20091119
	 */
	protected function _setPagerToolbar($totalrows,$pagesize)
	{
		if($totalrows > $pagesize){
			include_once 'GridView/Data_Paging.class.php';
			$pager = new Data_Paging(array('total_rows'=>$totalrows,
										   'page_size'=>$pagesize));
	        $pager->openAjaxMode('gotopage');
	        $this->_pagerToolbar = $pager->outputToolbar(2);
		}// end if
	}// end _setPagerToolbar()
	
	/**
	 * 取得分页 toolbar
	 *
	 * @return string
	 */
	public function getPagerToolbar()
	{
		return $this->_pagerToolbar;
	}
	
	/**
	 * 取上一期的工作目标设定
	 * @param number $pa_form_seqno
	 * @param number $emp_seqno number employee seq no (add by dennis 20110707)
	 * @return string
	 * @author Dennis 2010-01-13
	 * @lastupdate by dennis 2011-07-07
	 */
	public function getPrePAGoal($pa_form_seqno,$emp_seqno=null)
	{
		$empseqno = is_null($emp_seqno) ? $this->_empSeqNo : $emp_seqno;
		$sql = <<<eof
			select emp_achievement as pre_emp_goal
			  from hr_appraisals_base
			 where appraisal_id = (select max(appraisal_id)
			                         from hr_appraisals_base
			                        where seg_segment_no = :company_id
			                          and psn_id = :emp_seqno
			                          and appraisal_id < :pa_form_seqno)
			   and psn_id         = :emp_seqno1
			   and seg_segment_no = :company_id1
eof;
		//$this->_dBConnection->debug =1;
		return $this->_dBConnection->CacheGetOne(self::DATA_CACHE_SECONDS,$sql,array('company_id'    =>$this->_companyId,
																  'emp_seqno'     =>$empseqno,
																  'pa_form_seqno' =>$pa_form_seqno,
																  'company_id1'   =>$this->_companyId,
																  'emp_seqno1'    =>$empseqno));
	}
	
	
		/**
		 * Get Employee Profile
		 * add by Dennis 2014/01/23
		 * @param number $pa_form_seqno
		 * @return array
		 */
		public function getEmpInfo($pa_form_seqno)
		{
		    $sql = <<<eof
        	    select b.id_no_sz as pa_emp_id,
                       b.name_sz as pa_emp_name,
                       a.job_date as join_date,
                       a.jobplan_date as job_date,
                       c.evaluation_period_no as pa_period_id,
                       c.evaluation_period_desc as pa_period_desc,
                       c.eva_year as pa_year,
			           nvl(a.mgr_appraisal_remark,a.manager_commends) as mgr_comment,
                       pk_personnel_msg.f_title_msg(b.seg_segment_no, b.title, '01') as title_id,
                       pk_personnel_msg.f_title_msg(b.seg_segment_no, b.title, '02') as title_desc,
                       pk_department_message.f_dept_msg(b.seg_segment_no,
                                                        b.seg_segment_no_department,
                                                        sysdate,
                                                        '01') as dept_id,
                       pk_department_message.f_dept_msg(b.seg_segment_no,
                                                        b.seg_segment_no_department,
                                                        sysdate,
                                                        '02') as dept_name
                  from hr_appraisals_base a, hr_personnel_base b, hr_evaluation_periods c
                 where a.seg_segment_no = b.seg_segment_no
                   and a.psn_id = b.id
                   and a.seg_segment_no = c.seg_segment_no
                   and a.evaluation_period_id = c.evaluation_period_id
                   and appraisal_id = :pa_form_seqno
                   and a.seg_segment_no = :company_id
eof;
		    //$this->_dBConnection->debug = true;
		    $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		    return $this->_dBConnection->GetRow($sql,array('pa_form_seqno'=>$pa_form_seqno,
		            'company_id'=>$this->_companyId));
		}
	
		/**
		 * 
		 * @param number $pa_form_seqno
		 */
		public function getGoalMasterList($pa_form_seqno,$is_tmp = '_if')
		{
			$sql = <<<eof
    			select a.appraisal_id     as pa_form_seqno,
    			       a.master_id        as master_goal_seqno,
    			       a.psn_id           as emp_seqno,
    			       a.seq              as seq,
    			       a.goal_type	      as goal_type_id,
    			       b.eva_type_desc    as goal_type_desc,
    			       a.work_goal        as work_goal,
    			       a.percent_goal     as goal_weight,
    			       a.is_approved      as is_approved
    			  from hr_eva_goal_master{$is_tmp} a, 
    			       hr_eva_goal_type            b,
    			       hr_appraisals_base          c
    			 where a.seg_segment_no = b.seg_segment_no
    			   and a.goal_type 		= b.eva_goal_type_id
    			   and a.appraisal_id   = c.appraisal_id
    			   and a.is_active 		= 'Y'
    			   and a.appraisal_id 	= :pa_form_seqno
    			   and a.seg_segment_no = :company_id
    			 order by seq
eof;
			//$this->_dBConnection->debug = 1;
			return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
					'pa_form_seqno'=>$pa_form_seqno));
		}
		
		/**
		 * 
		 * @param number $pa_form_seqno
		 */
		public function getGoalDetailList($pa_form_seqno,$is_tmp = '_if')
		{
			$sql = <<<eof
    			select appraisal_id as pa_form_seqno,
    			       master_id    as master_goal_seqno,
    			       detail_id	as detail_goal_seqno,
    			       psn_id       as emp_seqno,
    			       seq          as seq,
    			       work_goal    as work_goal,
    			       percent_goal as goal_weight,
    			       mgr_psn_id   as work_owner,
    			       to_char(complete_date, 'YYYY/MM/DD') as archive_date,
    			       replace(remark,'&lt;br&gt;','<br>')       as remark,
    			       is_approved  as is_approved
    			  from hr_eva_goal_detail{$is_tmp}
    			 where is_active = 'Y'
    			   and appraisal_id = :pa_form_seqno
    			   and seg_segment_no = :company_id
    			 order by seq
eof;
			//$this->_dBConnection->debug = 1;
			return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
					'pa_form_seqno'=>$pa_form_seqno));
		}
		/**
		 * Get Year Goal Type List
		 * @param no
		 * @return array
		 * @author Dennis 2013/09/12
		 */
		public function getGoalTypeList()
		{
			$sql = <<<eof
			select eva_goal_type_id, eva_type_desc
			  from hr_eva_goal_type
			 where seg_segment_no = :company_id
			   and is_active = 'Y'
			 order by eva_seq
eof;
			$this->_dBConnection->SetFetchMode(ADODB_FETCH_NUM);
			return $this->_dBConnection->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
					array('company_id'=>$this->_companyId));
		}
}// end class AresPA
