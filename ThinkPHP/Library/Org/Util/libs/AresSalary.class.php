<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) Lan Jiangtao
 *
 *	Description:
 *     ehr Salary Module
 *    
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresSalary.class.php $
 *  $Id: AresSalary.class.php 3825 2014-08-15 08:07:45Z dennis $
 *  $Rev: 3825 $ 
 *  $Date: 2014-08-15 16:07:45 +0800 (周五, 15 八月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-08-15 16:07:45 +0800 (周五, 15 八月 2014) $
 \****************************************************************************/
class AresSalary {
	private $_companyId;
	private $_empSeqNo;
	private $_dBConn;
	
	const DATA_CACHE_SECONDS = 0;
	/**
	 *   Counstructor of class AresSalary
	 *   init property companyid and emplyee seq no (psn_id)
	 *   @param $companyid string, the employee's company id
	 *   @param $user_seqno string, the login user's sequence no in app_users
	 *   @return void.
	 */
	function AresSalary($companyid, $emp_seqno = "") {
		global $g_db_sql;
		$this->_companyId = $companyid;
		$this->_empSeqNo  = $emp_seqno;
		$this->_dBConn    = &$g_db_sql;
		//$this->_dBConn->debug = true;
	} // end class constructor

	/**
	 * 取得薪资年份
	 *
	 * @return array 2-d array
	 * @author Dennis 2008-01-29 (rewrite)
	 */
	function GetYearList() {
		$sql = 'select distinct
					   substrb (period_detail_id,0,4) as year1,
    				   substrb (period_detail_id,0,4) as year2
    			  from ehr_salary_v
    			 where company_id = :company_id
    			   and emp_seq_no = :emp_seqno
                 order by 1';
		//$this->_dBConn-> debug = true;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql, array('company_id'=>$this->_companyId,
													 'emp_seqno' =>$this->_empSeqNo));
	} // end GetYearList()
	/**
	 * 取得计薪期间月份
	 *
	 * @return unknown
	 */
	function GetMonthList() {
		$sql = <<<eof
        select distinct 
               period_detail_seqno as month1,
        	   period_detail_id    as month2
         from  ehr_salary_v
        where  company_id = :company_id
          and  emp_seq_no = :emp_seqno
eof;
		//$this->_dBConn-> debug = true;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dBConn->CacheGetArray (self::DATA_CACHE_SECONDS,$sql, array('company_id'=>$this->_companyId,
													 'emp_seqno' =>$this->_empSeqNo));
	}// end GetMonthList()
	
	/**
	 * 取得最后的薪资年月
	 * @param  no
	 * @return array
	 * @author Dennis 2008-01-29
	 */
	function GetMaxYM() {
		$sql = 'select substr(period_detail_id, 0, 4) as year_desc,
				       period_detail_seqno            as period_seqno
				  from ehr_salary_v
				 where company_id = :company_id
				   and emp_seq_no = :emp_seqno
				   and period_detail_id =
				       (select nvl(max(period_detail_id),0)
				          from ehr_salary_v
				         where company_id = :company_id1
				           and emp_seq_no = :emp_seqno1)';
		//$this->_dBConn->debug = true;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dBConn->CacheGetRow(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->_companyId,
												 'emp_seqno'=>$this->_empSeqNo,
												 'company_id1'=>$this->_companyId,
												 'emp_seqno1'=>$this->_empSeqNo));
	} // end GetMaxYM
	
	function GetPromotionYearList() {
		$sql = <<<eof
        select distinct 
               to_char(validdate, 'yyyy') as year1,
        	   to_char(validdate, 'yyyy') as year2
          from hr_promotion_sz_v
         where psn_seg_segment_no = :company_id
           and psn_id             = :emp_seqno
		 order by  to_char(validdate, 'yyyy') desc
eof;
		//print $sql_string;
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->_companyId,
												   'emp_seqno'=>$this->_empSeqNo));
	}// end GetPromotionYearList()
	
	function GetPromotionMonthList() {
		$sql = <<<eof
        select distinct 
               to_char(validdate, 'mm') as month1,
        	   to_char(validdate, 'mm') as month2
          from hr_promotion_sz_v
         where psn_id             = :emp_seqno
           and psn_seg_segment_no = :cmpany_id
	     order by to_char(validdate, 'mm') desc
eof;
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('emp_seqno'=>$this->_empSeqNo,
												   'company_id'=>$this->_companyId));
	}// end GetPromotionMonthList()
	
	/**
	 * get salary month list by year
	 *
	 * @param string $year
	 * @return arrray
	 * @author Dennis
	 */
	function getMonthByYear($year) {
		$sql = "select distinct
					   period_detail_seqno as month1,
				       period_detail_id    as month2
				  from ehr_salary_v
				 where company_id = :company_id
				   and emp_seq_no = :emp_seqno
				   and substrb(period_detail_id, 0, 4) = :year
				order by period_detail_id";
		//$this->_dBConn->debug = 1;
		$this->_dBConn->setFetchMode (ADODB_FETCH_NUM );
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql, array('company_id'=>$this->_companyId,
													'emp_seqno' =>$this->_empSeqNo,
													'year'=>$year));
	} // end getMonthByYear()
	
	/**
	 * Get Salary Items Summary List
	 *
	 * @param string $salary_period
	 * @return array
	 * @author Dennis
	 * @last update 2008/02/27 16:36:10 PM by Dennis for FeiRui 福利金
	 */
	function GetEmployeeSalaryList($salary_period) {
		$sql=<<<eof
        	select a.seg_segment_no,
			       a.psn_id as emp_seq_no,
			       a.periodsalary_result_id,
			       a.period_detail_id1,
			       a.period_detail_id,
			       a.pay_type,
			       a.pay_type_code,
			       a.pay_type_desc,
			       a.remark_sz,
			       a.fix_amount,
			       a.temp_add_amount,
			       a.overtime_amount,
			       a.overtime_amount1,
			       a.bonu_amount,
			       a.temp_add_amount - a.temp_dec_amount as temp_amount,
			       a.absence_amount,
			       a.insure_emp_amount,
			       a.insure_com_amount,
			       a.tax_amount,
			       a.salary_tax,
			       a.after_tax_amount,
			       a.before_taxamount,
				   a.welfare_amount,
			       a.emp_total_amount
			  from hr_periodsalary_result_v_v a,
			       hr_pay_type				  b
			 where a.seg_segment_no = b.seg_segment_no
			   and a.pay_type       = b.pay_type_id
			   and b.is_month       = 'Y'
			   and a.seg_segment_no = :company_id
			   and a.psn_id         = :emp_seqno
			   and a.period_detail_id = :period_detail_seqno
eof;
		//$this->_dBConn->debug = true;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->_companyId,
												   'emp_seqno' =>$this->_empSeqNo,
												   'period_detail_seqno'=>$salary_period));
	} // end function
	

	/**
	 * 取得薪资明细
	 *  @param number $salary_key_id
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 *  @lastupdate:
	 *   for security reason add psn_id parameter
	 */
	function GetSalaryDetailList($salary_key_id,$psn_id) {
		$sql =<<<eof
			select periodsalary_result_id,
				   seg_segment_no,
				   period_detail_id1,
				   period_detail_id,
				   period_master_no,
				   psn_no,
				   psn_name,
				   indate,
				   psn_dept_no,
				   psn_dept_name,
				   psn_grade,
				   fix_amount,
				   temp_add_amount,
				   overtime_amount,
				   overtime_amount1,
				   bonu_amount,
				   temp_dec_amount,
				   absence_amount,
				   insure_emp_amount,
				   insure_com_amount,
				   tax_amount,
				   salary_tax,
				   before_taxamount,
				   emp_total_amount
			  from hr_periodsalary_result_v_v
			 where periodsalary_result_id = :salary_result_seqno
			   and psn_id = :psn_id
eof;
		//$this->_dBConn->debug = 1;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->CacheGetRow(self::DATA_CACHE_SECONDS,$sql,array('salary_result_seqno'=>$salary_key_id,'psn_id'=>$psn_id));
	} // end function
	

	/**
	 * 取得固定薪资明细
	 *  @param number $period_detail_id
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 */
	function GetFixSalaryList($period_detail_id) {
		$sql =<<<eof
			select a.sal_item_id,
				   a.sal_item_name,
				   b.amount,
				   b.begin_date,
				   b.remark,
				   b.end_date
			  from ehr_salary_fixed_temp_v a, 
			       hr_salary_base_test     b
			 where a.company_id = :company_id
			   and a.detail_id  = :detail_seqno
			   and a.emp_seq_no = :emp_seqno
			   and a.amount != '0'
			   and a.emp_seq_no = b.psn_id
			   and a.sal_item_id = b.mapid
			   and b.period_detail_id = a.detail_id
			 order by a.sal_item_id, b.begin_date
eof;
		//$this->_dBConn->debug = true;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql, array('company_id'=>$this->_companyId,
													'detail_seqno'=>$period_detail_id,
													'emp_seqno'=>$this->_empSeqNo));
	} // end function
	/**
	 * 取得临时薪资明细
	 *  @param number $period_detail_id
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 */
	function GetTemporarySalaryList($period_detail_id) {
		$sql = "select sal_item_id, 
		               sal_item_name, 
					   amount,
					   decode(plustype,'PLUS','','MINUS','-') as plus_or_minus,    -- add by wilson 2011/07/06
					   plustype,                                                   -- add by wilson 2011/07/06
					   '' as reason -- add by dennis 2012-08-07
				  from ehr_salary_fixed_temp_v
				 where company_id = '%s'
				   and detail_id = '%s'
				   and emp_seq_no = '%s'
		           and amount != 0
				   and sal_item_type = '03'";
		//$this->_dBConn->debug = true;
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,sprintf($sql, $this->_companyId, $period_detail_id, $this->_empSeqNo));
	} // end function GetTemporarySalaryList()
	
	/**
	 *  取得加班薪资明细
	 *  @param number $period_detail_id
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 */
	
	function GetOvertimeSalaryList($period_detail_id) {
		$sql = "select overtime_type, hours, amount
				  from ehr_salary_overtime_v
				 where company_id = '%s'
				   and period_detail_id = '%s'
				   and emp_seq_no = '%s'";
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,sprintf($sql, $this->_companyId, $period_detail_id, $this->_empSeqNo));
	} // end GetOvertimeSalaryList()
	

	/**
	 *  取得请假扣款明细
	 *  @param number $period_detail_id
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 */
	function GetAbsenceSalaryList($period_detail_id) {
		$sql = "select a.absense_id,
					   a.absence_name,
					   a.absence_unit,
					   a.hours,
					   a.days,
					   a.amount,
					   a.cday
				  from ehr_salary_absence_v a
				 where a.company_id = '%s'
				   and a.period_detail_id = '%s'
				   and a.emp_seq_no = '%s'";
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,sprintf($sql, $this->_companyId, $period_detail_id, $this->_empSeqNo));
	} // end GetAbsenceSalaryList()
	

	/**
	 *  取得社保款明细
	 *  @param number $period_detail_seqno
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 */
	function GetInsureSalaryList($period_detail_seqno) {
		$sql = <<<eof
		select detail_no   as salary_ym,
		       detail_desc as salary_ym_desc,
		       master_no   as salary_std_id,
		       master_desc as salary_std_desc,
		       insure_id,
		       insure_name_cn,
		       emp_base,
		       company_base,
		       emp_pay,
		       company_pay,
		       insure_unit_id,
		       insure_unit_name
		  from ehr_salary_insure_v
		 where company_id       = :company_id
		   and period_detail_id = :period_detail_seqno
		   and emp_seq_no       = :emp_seqno
eof;
		//$this->_dBConn->debug =true;
        $this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql, array('company_id'=>$this->_companyId,
													'emp_seqno' =>$this->_empSeqNo,
													'period_detail_seqno'=> $period_detail_seqno));
	} // end GetInsureSalaryList()
	
	

	/**
	 *  取得奖金明细
	 *  @param number $period_detail_id
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 */
	
	function GetBonusSalaryList($period_detail_id) {
		$sql = "select bonus_id, name_cn, older_amount
				  from ehr_salary_bonus_v
				 where company_id = '%s'
				   and period_detail_id = '%s'
				   and emp_seq_no = '%s'";
        $this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,sprintf($sql, $this->_companyId, $period_detail_id, $this->_empSeqNo));
	} // end GetBonusSalaryList()
	
	/**
	 *   Get Salary Promotion list
	 *   @author : jack 2006-12-16 14:16:40
	 */
	function GetSalaryChangeDetailList($wherecond) {
		$sql= <<<eof
        select emp_seq_no,
		       mapid,
		       mapname,
		       amount
        from ehr_salary_promotion_detail_v
        where emp_seq_no = :emp_seqno
          and company_id = :company_id
          and validdate  = :wherecond
	order by validdate desc
eof;
		return $this->_dBConn->GetArray( $sql,array('company_id'=>$this->_companyId,
													'emp_seqno'=>$this->_empSeqNo,
													'wherecond'=>$wherecond));
	}// end GetSalaryChangeDetailList
	
	function GetSalaryTotal($wherecond) {		
		$sql= <<<eof
        select sum(amount) as total
         from  ehr_salary_promotion_detail_v
        where  emp_seq_no = :emp_seqno
          and  company_id = :company_id
          and  validdate  = :wherecond
eof;
		return $this->_dBConn->GetOne( $sql,array('company_id'=>$this->_companyId,
												  'emp_seqno'=>$this->_empSeqNo,
												  'wherecond'=>$wherecond));
	}
	
	/**
	 *  取得劳健团保明细
	 *  @param number $period_detail_id
	 *  @return array 2-d array
	 *  @author Dennis 1/25/2008
	 */
	function GetInsureTW($period_detail_id, $lang, $salary_result_id) {		
		$sql = "select f_get_muti_lang('%s',
									   'HRCF001',
									   'HR_INSURE_FEE_TW.INSURE_TYPE',
									   'LL',insure_type) as insure_type,
					   emp_pay,
					   company_pay,
					   remark
				  from ehr_insure_tw_v
				 where company_id = '%s'
				   and emp_seq_no = '%s'
				   and period_detail_id = '%s'
				   and salary_result_id = '%s'";
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,sprintf($sql, $lang, $this->_companyId, $this->_empSeqNo, $period_detail_id, $salary_result_id));
	} // end GetInsureTW()
	

	/**
	 *  取得公司提拔明细
	 *  @param number $period_detail_id
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 */
	function GetComTiBo($lang, $period_detail_id) {
		$sql = "select company_id,
		               emp_seq_no, 
					   amount, 
					   f_get_muti_lang('%s','HRCF001','HR_SALARY_SZ7.TYPE_ID','LL',pay_type) as pay_type
				  from ehr_salary_company_tibo_v
				 where company_id = '%s'
				   and emp_seq_no = '%s'
				   and period_detail_id = '%s'";
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,sprintf($sql, $lang, $this->_companyId, $this->_empSeqNo, $period_detail_id));
	} // end GetComTiBo()
	

	/**
	 *  取得特殊奖金明细
	 *  @param number $period_detail_id
	 *  @return array 2-d array
	 *  @author Dennis 1/23/2008
	 */
	function GetSpecBonus($period_detail_id) {
		$sql = "select bonus_name,
					   init_amount,
					   tax_amount,
					   fact_amount,
					   remark
				  from ehr_salary_spec_bonus_v
				 where company_id = '%s'
				   and emp_seq_no = '%s'
				   and period_detail_id = '%s'";
		return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,sprintf($sql, $this->_companyId, $this->_empSeqNo, $period_detail_id));
	} // end GetComTiBo()
	
	private function _getPayType() {
		$sql = "select pay_type_id,
					   pay_type_desc
				  from hr_pay_type
				 where seg_segment_no = :company_id
				   and is_active = 'Y'
				   and is_month = 'N'
				 order by pay_type_code";
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->GetArray($sql, array('company_id'=>$this->_companyId));
	} // end _getPayType()
	
	/**
	 * Get 奖金
	 *
	 * @param string $year
	 * @param string $month
	 * @return array
	 * @author Dennis
	 */
	public function GetBonus($year, $month) {
		// default query current month bonus
		$year    = empty($year)? date('Y'): $year;		
		$paytype = $this->_getPayType ();
		$sql     = '';
		$cnt     = count($paytype );
		
		for($i = 0; $i < $cnt; $i ++) {
			$sql .= "select hpd.yyyy             as year_desc,
							hpd.mm				 as month_desc,
							hbs.bonus_name       as bonus_name,
						    hsz.before_amount    as init_amount,
						    hsz.after_tax_amount as tax_amount,
						    hsz.older_amount     as act_amount,
						    '%s'                 as pay_type,
						    '%s'                 as pay_type_id
					  from hr_salary_sz     hsz, 
						   hr_bonus_setup   hbs, 
						   hr_period_detail hpd
					 where hbs.seg_segment_no = hsz.seg_segment_no
					   and hsz.seg_segment_no = hpd.seg_segment_no
					   and hsz.period_detail_id = hpd.period_detail_id
					   and hsz.seg_segment_no = '%s'
					   and hbs.bonus_id = hsz.type_id
					   and hsz.type = '3'
					   and hsz.is_salary_over = 'Y'
					   and hsz.psn_id = '%s'
					   and hpd.yyyy = '%s'
					   and hpd.period_detail_id = '%s'
					   and hsz.pay_type = '%s' " . ($i < $cnt - 1 ? ' union ' : '');
			
			$sql = sprintf($sql, $paytype [$i]['PAY_TYPE_DESC'], $paytype [$i]['PAY_TYPE_ID'], $this->_companyId, $this->_empSeqNo, $year, $month, $paytype [$i]['PAY_TYPE_ID'] );
		} // end for loop
		$cnt1 = 0;
		$result [0]=array();
		if(!empty($sql)){
			$result [0] = $this->_dBConn->GetArray ($sql);
			$cnt1 = count($result[0]);
		}// end if
		// add by dennis 2011-07-29	
		if (count($result[0])>0)
		{
			$result[1]['init_amount_total'] = 0;
			$result[1]['tax_amount_total']  = 0;
			$result[1]['act_amount_total']  = 0;
			// summary bonus item
			for($i = 0; $i < $cnt1; $i ++) {
				$result[1]['init_amount_total'] += $result[0][$i]['INIT_AMOUNT'];
				$result[1]['tax_amount_total']  += $result[0][$i]['TAX_AMOUNT'];
				$result[1]['act_amount_total']  += $result[0][$i]['ACT_AMOUNT'];
			} // end for loop
		}
		return $result;
	} // end GetBonus()

	/**
	 * Get 员工薪资异动历史
	 * @param no
	 * @return array, 2-D array
	 * @author Dennis 2008-06-24
	 */
	function getSalaryPromotionMaster() {
		$sql = 'select psn_seg_segment_no,
				       psn_id,
				       updatetype_no,
				       updatetype_name,
				       sly_grade,
				       new_grade,
				       sly_degree,
				       new_degree,
				       validdate,
				       next_validdate,
				       salary_proportion,
				       remark
				  from hr_promotion_v 
				  where psn_seg_segment_no = :company_id
				    and psn_id             = :emp_seqno';
		//$this->_dBConn->debug  =true;		
		return $this->_dBConn->GetArray($sql, array('company_id'=>$this->_companyId,
													'emp_seqno'=> $this->_empSeqNo) );
	} // end getSalaryPromotionMaster()

	/**
	 * 调薪历史记录
	 *
	 * @param string $valid_date
	 * @param string $previous
	 * @return array, 2-d array
	 * @author Dennis 2008-06-24
	 */
	function getSalaryPromotionDetail($valid_date) {
		$sql = "select a.lineno, b.mapid_no_sz, b.mapname, a.amount
				  from hr_promotiondetail a, hr_allowancemap b
				 where a.mapid = b.mapid(+)
				   and a.psn_seg_segment_no = b.seg_segment_no(+)
				   and a.psn_seg_segment_no = '%s'
				   and a.psn_id = '%s'
				   and a.validdate = to_date('%s','yyyy-mm-dd')
				   and a.amount != 0
				  order by a.lineno";
		//$this->_dBConn->debug = true;
		return $this->_dBConn->CacheGetArray(sprintf($sql, $this->_companyId, $this->_empSeqNo, $valid_date));
	} // end getSalaryPromotionDetail()
	
	/**
	 * 取得税额
	 *
	 * @param number $salary_period_seqno
	 * @return array
	 * @author Dennis 20090609
	 */
	public function getTax($salary_period_seqno)
	{
		$sql=<<<eof
        	select period_detail_id1 as salary_ym,
        		   pay_type_code,
        		   pay_type_desc,
			       tax_amount 
			  from hr_periodsalary_result_v_v
			 where seg_segment_no   = :company_id
			   and psn_id           = :emp_seqno
			   and period_detail_id = :period_detail_seqno
eof;
		//$this->_dBConn->debug = true;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
												   'emp_seqno' =>$this->_empSeqNo,
												   'period_detail_seqno'=>$salary_period_seqno));
	}// end getTax()
	
	/**
	 * check data privileges
	 * @param int $psn_id
	 * @return string  Y_allowed, N_not allowed
	 * @author Dennis 2012-12-06
	 */
	public function checkSalaryPermission($psn_id)
	{
		$this->_dBConn->Execute("begin pk_erp.P_SET_SEGMENT_NO('".$this->_companyId."');pk_erp.p_set_username('".$_SESSION['user']['user_seq_no']."');end;");
		return $this->_dBConn->GetOne("select pk_user_priv.f_user_priv($psn_id) from dual");
	} 
	
	//＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
	// Salary Slip  Rewirte by Dennis 2014/04/09
	//＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
	/**
	 * Get Last 2 year salary list by month
	 * @param No
	 * @return array
	 * @author Dennis 2014/04/10
	 */
	public function getLeastTwoYearSalaryList($year)
	{
	    $sql = <<<eof
	        select x.periodsalary_result_id    as sal_result_id, 
	               x.period_detail_id1         as year_mon, 
	               x.emp_total_amount          as amount,
	               x.period_master_id          as master_id,
	               x.period_detail_id          as detail_id
              from hr_periodsalary_result_v_v x, hr_remit d
             where x.seg_segment_no = :company_id
               and x.psn_id         = :psn_id
               and x.seg_segment_no = d.seg_segment_no
               and x.period_master_id = d.period_master_id
               and x.period_detail_id = d.period_detail_id
               and trunc(sysdate) >= d.remitdate
               and substr(period_detail_id1, 0, 4) >= $year-1
             order by x.period_detail_id1
eof;
	    //$this->_dBConn->debug = 1;
	    $this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->_companyId,
	            'psn_id'=>$this->_empSeqNo));
	}
	
	/**
	 * 取得税后减项清单
	 * @param int $perid_master_id
	 * @param int $period_detail_id
	 * @return array
	 * @author Dennis 2014/04/10
	 */
	public function getSalaryMinusItemAfterTax($perid_master_id,$period_detail_id)
	{
	    $sql = <<<eof
	        select b.mapname as sal_item_desc, -a.amount amount
              from hr_salary_sz a, hr_allowancemap b
             where a.seg_segment_no = b.seg_segment_no
               and a.type_id = b.mapid_no_sz
               and a.seg_segment_no = :company_id
               and a.psn_id = :psn_id
	           and a.period_maser_id = :period_master_id
	           and a.period_detail_id = :period_detail_id
               and nvl(b.fix, 'N') != 'Y'
               and nvl(b.plustype, 'N') = 'MINUS'
               and nvl(b.taxflag, 'N') = 'N'
eof;
        return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
                array('company_id'=>$this->_companyId,
                        'period_master_id'=>$perid_master_id,
                        'period_detail_id'=>$period_detail_id,
                        'psn_id'=>$this->_empSeqNo));
	}
	
	/**
	 * 取得指定月份的薪资概况
	 * @param int $perid_master_id
	 * @param int $period_detail_id
	 * @author Dennis 2014/04/10
	 */
	public function getMonSalSummary($period_master_id,$period_detail_id)
	{
	    $sql = <<<eof
	    select x.period_detail_id1 as mon,
               x.before_taxamount as salary_amount,
               (x.tax_amount - x.salary_tax)*-1 spec_bonus_tax,
               x.salary_tax*-1 as psn_tax,
               x.emp_total_amount as fact_amount
          from hr_periodsalary_result_v_v x
         where x.seg_segment_no = :company_id
           and x.psn_id = :psn_id
           and x.period_master_id = :period_master_id
           and x.period_detail_id = :period_detail_id
eof;
	    //$this->_dBConn->debug = 1;
	    return $this->_dBConn->CacheGetRow(self::DATA_CACHE_SECONDS,$sql,
                array('company_id'=>$this->_companyId,
                        'period_master_id'=>$period_master_id,
                        'period_detail_id'=>$period_detail_id,
                        'psn_id'=>$this->_empSeqNo));
	}
	
	/**
	 * get fixed salary
	 * ps: sql from hcp salary slip report ()
	 * @param int $period_master_id
	 * @param int $period_detail_id
	 * @return array
	 */
	public function getFixSalaryListNew($period_master_id,$period_detail_id)
	{
	    $sql = <<<eof
        select pk_salary_setup.f_mapid_msg(seg_segment_no, type_id, '02') item_desc,
               amount
          from hr_salary_sz_fix
         where amount <> 0
           and seg_segment_no = :company_id
           and psn_id = :psn_id
           and period_master_id = :period_master_id
	       and period_detail_id = :period_detail_id
	       order by amount desc
eof;
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
	            array('company_id'=>$this->_companyId,
	                    'period_master_id'=>$period_master_id,
	                    'period_detail_id'=>$period_detail_id,
	                    'psn_id'=>$this->_empSeqNo));
	}
	/**
	 * 临时薪资
	 * @param int $period_master_id
	 * @param int $period_detail_id
	 * @return array
	 */
	public function getTmpSalaryListNew($period_master_id,$period_detail_id)
	{
	    $sql = <<<eof
            select sal_item_name as sal_item_desc,is_tax_item,
                   decode(plustype, 'PLUS', 1, 'MINUS', -1) * amount as amount
              from ehr_salary_fixed_temp_v
             where company_id = :company_id
               and master_id = :period_master_id
               and detail_id = :period_detail_id
               and emp_seq_no = :psn_id
               and amount != 0
               and sal_item_type = '03'
eof;
        //$this->_dBConn->debug = 1;
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
	            array('company_id'=>$this->_companyId,
	                    'period_master_id'=>$period_master_id,
	                    'period_detail_id'=>$period_detail_id,
	                    'psn_id'=>$this->_empSeqNo));
	}
	/**
	 * 加班费清单
	 * @param int $period_master_id
	 * @param int $period_detail_id
	 * @return array
	 */
	public function getOvertimeSalaryListNew($period_master_id,$period_detail_id)
	{
	    $sql = <<<eof
    	   select overtime_type as ot_type_desc,sum(hours) as hours,sum(amount) as amount
             from ehr_salary_overtime_v
            where company_id = :company_id
              and emp_seq_no = :psn_id
              and period_master_id = :period_master_id
    	      and period_detail_id = :period_detail_id
	        group by overtime_type
eof;
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
	            array('company_id'=>$this->_companyId,
	                    'period_master_id'=>$period_master_id,
	                    'period_detail_id'=>$period_detail_id,
	                    'psn_id'=>$this->_empSeqNo));
	}
	/**
	 * 请假扣款清单
	 * @param int $period_master_id
	 * @param int $period_detail_id
	 * @return array
	 */
	public function getAbsenceSalaryListNew($period_master_id,$period_detail_id)
	{
	    $sql = <<<eof
    	    select absence_name, sum(-1*amount) as amount
              from ehr_salary_absence_v
              where company_id = :company_id
                and emp_seq_no = :psn_id
                and period_master_id = :period_master_id
        	    and period_detail_id = :period_detail_id
	           group by absence_name
eof;
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
	            array('company_id'=>$this->_companyId,
	                    'period_master_id'=>$period_master_id,
	                    'period_detail_id'=>$period_detail_id,
	                    'psn_id'=>$this->_empSeqNo));
	}
	/**
	 * 奖金资料
	 * @param int $period_master_id
	 * @param int $period_detail_id
	 * 年终奖初始金额本来是 a.before_amount 因为 HCP 结薪时转档转成了交完社保之后的
	 * 金额，所以这里改成是 实发金额（older_amount）＋年终奖金税(after_tax_amount)＋缴社保金额(after_psn_amount)
	 * @return array
	 */
	public function getBonusSalaryListNew($period_master_id,$period_detail_id)
	{
	    $sql = <<<eof
	    select b.bonus_name       as bonus_desc,
               /*a.before_amount    as amount,*/
	           a.older_amount + a.after_tax_amount + a.after_psn_amount as amount,
	           -1*a.after_psn_amount as emp_bonus_insure_amount,
	           a.after_com_amount as comp_bonus_insure_amount,
               -1*a.after_tax_amount as bonus_tax
          from hr_salary_sz a, hr_bonus_setup b
         where a.seg_segment_no = b.seg_segment_no
           and a.type_id = b.bonus_id
           and a.type = '3'
           and a.is_salary_over = 'Y'
           and a.before_amount <> 0
           and a.seg_segment_no = :company_id
           and a.psn_id = :psn_id
           and a.period_master_id = :period_master_id
           and a.period_detail_id = :period_detail_id
eof;
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
	            array('company_id'=>$this->_companyId,
	                    'period_master_id'=>$period_master_id,
	                    'period_detail_id'=>$period_detail_id,
	                    'psn_id'=>$this->_empSeqNo));
	}
	
	/**
	 * 社保个人缴费
	 * @param int $period_master_id
	 * @param int $period_detail_id
	 * @return array
	 */
	public function getInsureSalaryListNew($period_master_id,$period_detail_id)
	{
	    $sql = <<<eof
	    select insure_name_cn ss_item_desc, company_pay, emp_pay*-1 as emp_pay
          from ehr_salary_insure_v
         where company_id = :company_id
           and emp_seq_no = :psn_id
           and period_master_id = :period_master_id
           and period_detail_id = :period_detail_id
           and emp_pay > 0
eof;
	    //$this->_dBConn->debug = 1;
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
	            array('company_id'=>$this->_companyId,
	                    'period_master_id'=>$period_master_id,
	                    'period_detail_id'=>$period_detail_id,
	                    'psn_id'=>$this->_empSeqNo));
	}
	/**
	 * 税后减项清单
	 * @param int $period_master_id
	 * @param int $period_detail_id
	 * @return array
	 */
	public function getDeductAfterTaxList($period_master_id,$period_detail_id)
	{
	    $sql = <<<eof
	       select b.mapname as sal_item_desc, -1*a.amount as amount
              from hr_salary_sz a, hr_allowancemap b
             where a.seg_segment_no = b.seg_segment_no
               and a.type_id = b.mapid_no_sz
               and nvl(fix, 'N') != 'Y'
               and nvl(plustype, 'N') = 'MINUS'
               and nvl(taxflag, 'N') = 'N'
               and a.seg_segment_no = :company_id
               and a.psn_id = :psn_id
               and a.period_master_id = :period_master_id
               and a.period_detail_id = :period_detail_id
eof;
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
	            array('company_id'=>$this->_companyId,
	                    'period_master_id'=>$period_master_id,
	                    'period_detail_id'=>$period_detail_id,
	                    'psn_id'=>$this->_empSeqNo));
	}
	
	/**
	 * 最近两年社保资料汇总
	 * @param number $year 年份
	 * @return array
	 * @author Dennis 2014/08/15
	 */
	function getLeastTwoYearInsureList($year){
	    $sql = <<<eof
		select company_id,
               emp_seq_no,
               period_master_id as master_id,
               period_detail_id as detail_id,
               detail_no as year_mon,
               sum(nvl(emp_pay, 0))     as emp_total,
               sum(nvl(company_pay, 0)) as com_total
          from ehr_salary_insure_v
         where company_id = :company_id
           and emp_seq_no = :emp_seqno
           and substr(detail_no, 0, 4) >= :year
         group by company_id,
                  emp_seq_no,
                  period_master_id,
                  period_detail_id,
                  detail_no
         order by period_detail_id
        	    
eof;
	    //$this->_dBConn->debug =true;
	    $this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
	    return $this->_dBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql, array('company_id'=>$this->_companyId,
	            'emp_seqno' =>$this->_empSeqNo,'year'=>$year-1));
	}
	
}// end class AresSalary
