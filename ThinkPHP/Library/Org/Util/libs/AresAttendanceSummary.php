<?php
/**
 * 
 *  Create By: Dennis
 *  Create Date: 2009-06-19 17:00
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAttendanceSummary.php $
 *  $Id: AresAttendanceSummary.php 3740 2014-05-05 09:26:36Z dennis $
 *  $LastChangedDate: 2014-05-05 17:26:36 +0800 (周一, 05 五月 2014) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3740 $  
 \****************************************************************************/

class AresAttendanceSummary {	
	private $_companyId;
	private $_empSeqNo;
	private $_dBConn;
	private $_salaryPeriodSeqno;
	
	function __construct($companyid,$empseqno)
	{
		global $g_db_sql;
		$this->_dBConn    = $g_db_sql;
		$this->_companyId = $companyid;
		$this->_empSeqNo  = $empseqno;
		$this->_salaryPeriodSeqno = $this->_getEmpSalaryPeriod();
	}// end class constructor()
	
	private function _getEmpSalaryPeriod()
	{
		$sql = 'select new_period_id
				  from hr_transdetail
				 where psn_id             = :emp_seq_no
				   and psn_seg_segment_no = :company_id
				   and validdate = (select max(validdate)
				                      from hr_transdetail
				                     where psn_id             = :emp_seq_no1
				                       and psn_seg_segment_no = :company_id1
				                       and new_period_id is not null)';
		return $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId,
										  		 'emp_seq_no'=>$this->_empSeqNo,
										  		 'company_id1'=>$this->_companyId,
										  		 'emp_seq_no1'=>$this->_empSeqNo));
	}// end _getEmpSalaryMasterPeriod()
	
	/**
	 * 加班汇总 by 计薪期间
	 *
	 * @return array
	 * @author Dennis 20090619
	 */
	public function getOvertimeSummary()
	{
		$sql = <<<eof
				select b.period_detail_no as ym, 
				       a.ot_fee_type      as ot_fee_type, 
				       sum(a.hours)       as hours
				  from ehr_overtime_v a, hr_period_detail b
				 where a.company_id = b.seg_segment_no
				   and a.my_day >= to_char(b.overtime_begin_date, 'yyyy-mm-dd')
				   and a.my_day <= to_char(b.overtime_end_date, 'yyyy-mm-dd')
				   and b.yyyy   = to_char(sysdate, 'yyyy')
				   and b.period_master_id = :period_master_id
				   and a.company_id = :company_id
				   and a.emp_seq_no = :emp_seq_no
				 group by b.period_detail_no, a.ot_fee_type
				 order by b.period_detail_no desc
eof;
		if ($this->_salaryPeriodSeqno)
		{
			return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
											  		   'emp_seq_no'=>$this->_empSeqNo,
											  		   'period_master_id'=>$this->_salaryPeriodSeqno));
		}else{
			return null;
		}
	}// end getCompensatorySummary()
	
	/**
	 * 请假资料汇总 by 计薪期间
	 *
	 * @return array
	 * @author Dennis 20090619
	 */
	public function getLeaveSummary()
	{
		$sql = <<<eof
			select b.period_detail_no as ym,
			       a.absence_id,
			       a.absence_name,
			       sum(a.hours) as hours,
			       sum(a.work_days) as days
			  from ehr_absence_v a, hr_period_detail b
			 where a.company_id = b.seg_segment_no
			   and to_char(a.my_day, 'yyyy-mm-dd') >=
			       to_char(b.attend_begin_date, 'yyyy-mm-dd')
			   and to_char(a.my_day, 'yyyy-mm-dd') <=
			       to_char(b.attend_end_date, 'yyyy-mm-dd')
			   and b.yyyy = to_char(sysdate, 'yyyy')
			   and b.period_master_id = :period_master_id
			   and a.company_id = :company_id
			   and a.emp_seq_no = :emp_seq_no
			 group by b.period_detail_no, absence_id, absence_name
			 order by b.period_detail_no desc
eof;
		if ($this->_salaryPeriodSeqno)
		{
			return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
											  		   'emp_seq_no'=>$this->_empSeqNo,
											  		   'period_master_id'=>$this->_salaryPeriodSeqno));
		}else{
			return null;
		}
	}// end getLeaveSummary()
	
	/**
	 * Get 补休资料
	 *
	 * @return array()
	 * @author Dennis 20090619
	 */
	public function getCompensatorySummary($base_date = null)
	{
		/*
		$sql = 'select ym,already_hours, left_hours
				  from ehr_mendhours_v
				 where seg_segment_no = :company_id
				   and id             = :emp_seq_no';*/
		$date = is_null($base_date) ? date('Y-m-d') : $base_date;
		$sql = <<<eof
		select '$date' as ym,
		       pk_ehr_mend_hours.f_get_mendhours(a.seg_segment_no,
		                                         a.id,
		                                         to_date(:mydate, 'yyyy-mm-dd'),
		                                         '1') already_hours,
		       pk_ehr_mend_hours.f_get_mendhours(a.seg_segment_no,
		                                         a.id,
		                                         to_date(:mydate1, 'yyyy-mm-dd'),
		                                         '2') left_hours
		  from hr_personnel_base a
		 where a.seg_segment_no = :company_id
		   and a.id = :emp_seq_no
eof;
		//$this->_dBConn->debug = true;
		return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
										  		   'emp_seq_no'=>$this->_empSeqNo,
										  		   'mydate'=>$date,
										  		   'mydate1'=>$date));
	}// end getCompensatorySummary()
	
	/**
	 * Get 年假
	 *
	 * @return array
	 * @author Dennis 20090619
	 * @last update: dennis 2011-01-17 年假加生效过期日期
	 */
	/*
	public function getYearVacationSummary($base_date = null)
	{
		$base_date = is_null($base_date) ? date('Y-m-d') : $base_date;
		$sql = 'select hy.absence_year     as my_year,
				       hy.only_year_days   as year_vacation_days,
				       hy.only_year_days1  as modulate_days,
				       hy.last_leave_days  as extend_days,
				       (nvl(hy.already_days,0)   + nvl(hy.last_already_days,0))  as used_days,
				       (nvl(hy.only_year_days,0) + nvl(hy.only_year_days1,0) + 
				       nvl(hy.last_leave_days,0) - nvl(hy.already_days,0)    - 
				       nvl(hy.last_already_days,0)) as left_days,
				       hy.effective_date,
				       hy.year_disable_date as expired_date
				  from hr_personnel hp, hr_yearabsence hy
				 where hy.seg_segment_no = hp.seg_segment_no
				   and hy.employee_id    = hp.id
				   and hy.seg_segment_no = :company_id
				   and hy.employee_id    = :emp_seq_no
				   and to_date(:base_date,\'yyyy-mm-dd\') between hy.effective_date and hy.year_disable_date';
		//$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->_dBConn->debug = true;
		return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
										  		   'emp_seq_no'=>$this->_empSeqNo,
										  		   'base_date'=>$base_date));
	}*/
	
	/**
	 * 
	 * Get Year Vacation
	 * ref HPBF350 p_gen_date
	 * @param string $base_date
	 * @author Dennis  2011-01-27
	 * @lastupdate Dennis 2011-12-13 只 show 年假，不show 递延年假
	 */
	public function getYearVacationSummary($base_date = null) 
	{
		$base_date = is_null($base_date) ? date('Y-m-d') : $base_date;
		$wh_sql = <<<eof
			select day_work_hours
			  from hr_yearabsence_setup
			 where seg_segment_no = :company_id
eof;
		$wh = (int)$this->_dBConn->GetOne($wh_sql,array('company_id'=>$this->_companyId));
		$wh = $wh == 0 ? 8 : $wh;
		$sql = <<<eof
				 select distinct a.seg_segment_no,
		                a.id,
		                a.parameter_id,
		                a.parameter_value,
		                a.value1,
		                a.value5,
		                a.value7,
		                a.value9,
		                a.extend_adj_days,
		                a.modulate_days,
		                a.extend_days,
		                a.effective_date,
		                a.expired_date,
		                nvl(a.curr_year_days, 0) as year_vacation_days,
		                decode(a.statistic_unit, 'D', a.nb_alreadyday, null) as used_days,
		                decode(a.statistic_unit, 'D', a.nb_alreadyday, null)* $wh as used_hours,
		                decode(a.statistic_unit, 'D', nvl(nb_mayday, 0), null) as left_days,
		                decode(a.statistic_unit, 'D', nvl(nb_mayday, 0), null) * $wh as left_hours,
		                a.statistic_unit
				  from (select hp.seg_segment_no,
				               hp.id,
				               to_char(hat.absence_type_id) as parameter_id,
				               hat.absence_name as parameter_value,
				               hat.measure_unit as value1,
				               hat.calendar_yn as value5,
				               hat.law_day as value7,
				               hat.absence_code as value9,
				               hat.measure_unit,
				               to_char(hat.absence_type_id),
				               ha.mend_leave,
				               hy.last_leave_days1  as extend_adj_days, -- 递延调整天数
				               hy.only_year_days1   as modulate_days, -- 调整天数
				       		   hy.last_leave_days   as extend_days, -- 递延天数
				       		   hy.year_disable_date as expired_date,
				       		   hy.only_year_days    as curr_year_days, -- 年假天数
				               hy.effective_date,
				               to_date(:base_date1, 'yyyy-mm-dd') as enddate,/*
				               pk_attend_status_sz.f_get_psn_day(hp.seg_segment_no,
				                                                 hp.id,
				                                                 to_date(:base_date2,
				                                                         'yyyy-mm-dd'),
				                                                 to_char(hat.absence_type_id)) nb_days,*/
				               pk_attend_status_sz.f_already_rest_day(hp.seg_segment_no,
				                                                      hp.id,
				                                                      to_char(hat.absence_type_id),
				                                                      to_date(:base_date3,
				                                                              'yyyy-mm-dd'),
				                                                      hat.statistic_unit,
				                                                      'D') nb_alreadyday, -- 已休天数
				               pk_attend_status_sz.f_can_rest_day(hp.seg_segment_no,
				                                                  hp.id,
				                                                  to_char(hat.absence_type_id),
				                                                  to_date(:base_date4,
				                                                          'yyyy-mm-dd'),
				                                                  'D') nb_mayday,
				               hat.statistic_unit
				         from  hr_personnel    hp,
				               hr_absence_type hat,
				               hr_attendset    ha,
				               gl_segment      gl,
				               hr_yearabsence  hy
				         where hp.seg_segment_no = :company_id
				           and hp.seg_segment_no = hat.seg_segment_no
				           and hp.seg_segment_no = ha.seg_segment_no
				           and hp.seg_segment_no = gl.seg_segment_no
				           and hp.seg_segment_no_department = gl.segment_no
				           and hat.is_active = 'Y'
				           and hp.id = :emp_seqno
				           and to_char(hat.absence_type_id) = ha.year_leave
				           /*
				           and to_char(hat.absence_type_id) in
				               (ha.last_year_leave, ha.year_leave)*/
				           and hp.id = hy.employee_id
				           and to_date(:base_date5, 'yyyy-mm-dd') >= hy.effective_date
				           and to_date(:base_date6, 'yyyy-mm-dd') <= hy.year_disable_date) a
eof;
		//$this->_dBConn->debug = true;
		return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
												   'emp_seqno' =>$this->_empSeqNo,
												   'base_date1'=>$base_date,
												   /*'base_date2'=>$base_date,*/
												   'base_date3'=>$base_date,
												   'base_date4'=>$base_date,
												   'base_date5'=>$base_date,
												   'base_date6'=>$base_date));	
	}
}// end class AresAttendanceSummary
