<?php
/*-----------------------------------------------------
* Description: 身份证阅读器处理类
* Author: TerryWang
* Date: 2011-9-5
* Version: 1.0
* Last Update By Dennis 2012-10-16
* ----------------------------------------------------*/
class AresCard{

    private $_dbConn = null;
    private $_companyId = null;
	private $_userSeqno = '';

    const DATA_CACHE_SECS = 0;					// 3600 数据 Cache Seconds
    const PARAM_PERSON_TYPE = 'PERSONTYPE';		// 人员分类
    const PARAM_EMP_TYPE = 'HR_COSTALLOCATION'; // 员工分类
    const PARAM_EMP_FROM = 'PARTTIME';			// 入职来源
    const PARAM_CARDING	 = 'HR_CARDING2';		// 刷 卡别
    const PARAM_EMP_CATE = 'JOB';				// 直间接
    const PARAM_NATION_REGION = 'NATIONALITY';	// 国际/地区
    const PARAM_CHK_IDCARD_DUPL = '2';			// 2 检查身份证重复，不允许进系统

    /**
     * Constructor of class AresCard
     *
     * @param DBHandler $_dbConn
     * @param string $_companyId
     */
    public function __construct($_dbConn,$_companyId){
        $this->_dbConn    = $_dbConn;
        $this->_companyId = $_companyId;
		$this->_userSeqno = $_SESSION['user']['user_seq_no'];
    }

    /**
     * 取部門人數最多的10個部門的列表
     * @param no
     * @return array
     * @author Dennis
     * @last update:
     * 1. change segment_name to segment_short_name for display by dennis 2012-12-20
     */
    public function getTop10Dept(){
        $sql = <<<eof
            select d.seqno, d.id, d.name
              from (select rownum as rn, c.*
                      from (select count(b.segment_no) n,
                                   b.segment_no as seqno,
                                   b.segment_no_sz as id,
                                   b.segment_short_name as name
                              from hr_personnel_base a, gl_segment b
                             where a.seg_segment_no = b.seg_segment_no
        					   and a.seg_segment_no_department = b.segment_no
                               and b.seg_segment_no = :company_id
                               and b.begindate <= trunc(sysdate)
                               and b.enddate >= trunc(sysdate)
                             group by b.segment_no, b.segment_no_sz, b.segment_short_name
                             order by n desc) c) d
             where d.rn <= 10
eof;
        //$this->_dbConn->debug = 1;
        return $this->_dbConn->CacheGetAll(self::DATA_CACHE_SECS,$sql,array('company_id'=>$this->_companyId));
    }
    /**
     * Get Dept List by KW
     * @param string $kw dept id or dept name
     * @return array
     * @author Dennis
     */
    public function getDeptList($kw)
    {
        $sql = <<<eof
            select segment_no    as seqno,
                   segment_no_sz as id,
                   segment_short_name  as name
              from gl_segment
             where begindate <= trunc(sysdate)
               and enddate >= trunc(sysdate)
               and seg_segment_no = :company_id
               and (segment_no_sz like :kw1 or segment_name like :kw2)
eof;
        //$this->_dbConn->debug =1;
        return $this->_dbConn->GetAll($sql,array('company_id'=>$this->_companyId,
                'kw1'=>'%'.$kw.'%','kw2'=>'%'.$kw.'%'));
    }
    /**
     * Get top 10 title list
     * @param no
     * @return array
     * @author Dennis
     */
    public function getTop10Title()
    {
        $sql = <<<eof
        select d.seqno, d.id, d.name
		  from (select rownum as rn, c.*
		          from (select count(a.title) n,
		                       b.title as seqno,
		                       b.title_no_sz as id,
		                       b.titlename as name
		                  from hr_personnel_base a, hr_title b
		                 where a.title = b.title
		                   and a.seg_segment_no = b.seg_segment_no
		                   and b.seg_segment_no = :company_id
		                 group by b.title, b.title_no_sz, b.titlename
		                 order by n desc) c) d
		 where rn <= 10
eof;
        return $this->_dbConn->CacheGetAll(self::DATA_CACHE_SECS,$sql,array('company_id'=>$this->_companyId));
    }

    /**
     * Get title list according the kw
     * @param string $kw title id or title desc
     * @return array
     * @author Dennis
     */
    public function getTitleList($kw)
    {
        $sql = <<<eof
            select title as seqno, title_no_sz as id, titlename as name
              from hr_title
             where begindate <= trunc(sysdate)
               and enddate >= trunc(sysdate)
               and seg_segment_no = :company_id
               and (title_no_sz like :kw1 or titlename like :kw2)
eof;
        //$this->_dbConn->debug =1;
        return $this->_dbConn->GetAll($sql,array('company_id'=>$this->_companyId,
                'kw1'=>'%'.$kw.'%','kw2'=>'%'.$kw.'%'));
    }
    /**
     * get 人員分類
     */
    public function getPersonTypeList()
    {
        return $this->_getCodParamByType(self::PARAM_PERSON_TYPE);
    }
    /**
     * Get 員工分類
     */
    public function getEmpTypeList()
    {
        return $this->_getPBParamByType(self::PARAM_EMP_TYPE);
    }
    /**
     * Get 员工入职来源
     */
    public function getEmpFromList()
    {
        return $this->_getCodParamByType(self::PARAM_EMP_FROM);
    }

    /**
    * Get parameter list by type (from hr_codedetail)
    * @param $param_type string parameter type
    * @return array
    * @author Dennis
    */
    private function _getCodParamByType($param_type)
    {
        $sql = <<<eof
        select codeid    as id,
               codevalue as name
          from hr_codedetail
         where hcd_codetype = :param_type
           and hcd_seg_segment_no = :company_id
eof;
        //$this->_dbConn->debug = 1;
        $this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
        return $this->_dbConn->CacheGetAll(self::DATA_CACHE_SECS,$sql,
            array('company_id'=>$this->_companyId,'param_type'=>$param_type));
    }
    /**
     * Get parameter list by type (from pb_parameters)
     * @param $param_type string parameter type
     * @return array
     * @author Dennis
     */
    private function _getPBParamByType($param_type)
    {
        $sql = <<<eof
        select parameter_id as id, parameter_value as name
          from pb_parameters
         where parameter_type = :param_type
           and seg_segment_no = :company_id
eof;
        $this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
        return $this->_dbConn->CacheGetAll(self::DATA_CACHE_SECS,$sql,
                array('company_id'=>$this->_companyId,'param_type'=>$param_type));
    }
    /**
     * Get 给薪类别 
     * 人事档中用的是 breakmonth_setup_no，不是 breakmonth_setup_id
     *   
     */
    public function GetSalaryTypeList(){
        $sql =  <<<eof
            select breakmonth_setup_no, breakmonth_setup_no
              from hr_breakmonth_setup
             where seg_segment_no = :company_id
               and trans_id = -1
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,array(
				'company_id' => $this->_companyId,
		));
	}
	/**
	 * Get pesonal tax list
	 */
	public function getPersonalTaxList()
	{
		$sql = <<<eof
		select tax_id, tax_code || '-' || tax_name
		  from hr_tax_header
		 where seg_segment_no = :company_id
eof;
 		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,array(
				'company_id' => $this->_companyId,
		));
	}
	/**
	 * Salary period list
	 */
	public function getSalaryPeroidList()
	{
		$sql = <<<eof
		select period_master_id, period_master_desc
		  from hr_period_master
		 where seg_segment_no = :company_id
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,array(
				'company_id' => $this->_companyId,
		));
	}
	/**
	 * 刷卡别	 
	 *  01:一卡	, 02:二卡,03:不刷卡
	 * @return array
	 */
	public function getCardingTypeList()
	{
		return $this->_getPBParamByType(self::PARAM_CARDING);
	}
	/**
	 * Get ovetime fee type list
	 * @return array
	 */
	public function getOTFeeTypeList()
	{
		$sql = <<<eof
		select hr_overtimetype_id, overtimetype_desc
		  from hr_overtimetype
		 where seg_segment_no = :company_id
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,array(
				'company_id' => $this->_companyId
		));
	}
	/**
	 * Get 假扣代号
	 */
	public function getAbsFeeTypeList()
	{
		$sql = <<<eof
			select hr_absencetype_id, hr_absencetype_desc
			  from hr_absencetype
			 where seg_segment_no = :company_id
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,array(
				'company_id' => $this->_companyId
		));
	}
	/**
	 * Get annual leave type list (年假代号)
	 */
	public function getYearAbsTypeList()
	{
		$sql = <<<eof
			select hr_yeartype_id, yeartype_desc
  				from hr_yeartype
 			where seg_segment_no = :company_id
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,array(
				'company_id' => $this->_companyId
		));
	}
	/**
	 * get DL/IDL
	 * @return array
	 */
	public function getEmpCateList()
	{
		return $this->_getCodParamByType(self::PARAM_EMP_CATE);
	}
	/**
	 * Get 全勤奖代号
	 * SQL from HRAF006 Recordgroup
	 */
	public function getAbsBonusList()
	{
		$sql = <<<eof
		select a.att_setup_id, a.setup_name
		  from hr_attend_bonus_setups a, hr_allowancemap b
		 where a.allowance_map_id = b.mapid(+)
		   and a.seg_segment_no = b.seg_segment_no(+)
		   and nvl(b.enableflag, 'N') = 'Y'
		   and a.seg_segment_no = :company_id
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,array(
				'company_id' => $this->_companyId
		));
	}
	
	public function getNationRegionList()
	{
		return $this->_getCodParamByType(self::PARAM_NATION_REGION);				
	}
	/**
	* Get Packaged Parameters List
	* @param no
	* @return array
	* @author Dennis
	*/
	public function getParamPkgList()
	{
		$sql = <<<eof
			select employee_history_data_id   as pkg_seqno,
			       employee_history_data_name as pkg_desc
			  from hr_employee_history_datas
			  where seg_segment_no = :company_id
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,array(
				'company_id' => $this->_companyId
		));
	}
	/**
	 * Get packaged parameters by package id
	 * @param number $pkg_id
	 * @return array
	 */
	public function getParamPkgRow($pkg_id)
	{
		$sql = <<<eof
		select overtime_type_id  as ot_fee_type_id,
		       absence_type_id   as abs_fee_type_id,
		       year_leave_id     as year_abs_id,
		       is_grade_degree   as is_grd_degree,
		       jobcategory       as emp_cate_id,
		       period_master_id  as salary_period_id,
		       salary_proportion as salary_type_id,
		       carding           as carding_type,
		       abs_bonus_id      as abs_bonus_id
		  from hr_employee_history_datas
		 where seg_segment_no = :company_id
		   and employee_history_data_id = :pkg_id
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dbConn->CacheGetRow(self::DATA_CACHE_SECS,$sql,
				array('company_id'=>$this->_companyId,'pkg_id'=>$pkg_id));
	}
	
	public function delEmpFromTmp($tmp_seqnos)
	{
		$sql = "delete from hr_personnel_temp where id in($tmp_seqnos)";
		//$this->_dbConn->debug = 1;
		$this->_dbConn->Execute($sql);
		return $this->_dbConn->Affected_Rows();
	}
	/**
	 * Transfor temp data to HCP
	 * @param array $emp_row_data
	 * @return number
	 * @author Dennis
	 */
	public function trans2HCP($emp_row_data)
	{
		$this->_beforeTrans2HCP();
		$TWID = '000';    // 国家地区代码 TW
		$CNID = 'N002';   // 国家地区代码 CN
		$TWCODE = '2';    // 国家地区ID  TW
		$empid_func = $this->_getGenEmpIdFunc();
		$issue_date = date('Y-m-d');
		$issue_no   = date('YmdHis');
		$trans_result = array();
		$sql = <<<eof
			insert into hr_personnel
			  (id,
			   name,
			   name_sz,
			   sex,
			   nation,
			   birth,
			   address,
			   id_no_sz,
			   title,
			   seg_segment_no,
			   seg_segment_no_department,
			   create_by,
			   create_date,
			   indate,
			   id_card_type,
			   id_card,
			   salary_type,
			   period_master_id,
			   overtime_type_id,
			   absence_type_id,
			   tw_tax_id,
			   year_leave_id,
			   jobcategory,
			   abs_bonus_id,
			   trialdate,
			   foreigner,
			   salary_proportion,
			   experiencestartdate,
			   temp_company,
			   carding,
			   contract,
			   costallocation,
			   nationality,
			   employee_history_data_id,
			   nb_leader,
			   origin,
			   constellation)
			  select :emp_seq,
			         :emp_seq1,
			         name,
			         decode(sex, '男', 'M', 'F'),
			         nation,
			         birth,
			         address,
			         {$empid_func},
			         :title_seqno,
			         seg_segment_no,
			         :dept_seqno,
			         create_by,
			         sysdate,
			         to_date(:indate,'yyyy-mm-dd'),
			         '1',
			         :id_card,
			         :salary_type,
			         :period_master_id,
			         :overtime_type_id,
			         :absence_type_id,
			         :tw_tax_id,
			         :year_leave_id,
			         :jobcategory,
			         :abs_bonus_id,
			         add_months(indate, 3),
			         'N',
			         '1',
			         to_date(:experiencestartdate,'yyyy-mm-dd'),
			         :temp_company,
			         :carding,
			         :contract,
			         :costallocation,
				     decode((select tax_area
							  from gl_segment
							 where seg_segment_no = t.seg_segment_no
							   and segment_type = 'COMPANY'), '{$TWCODE}', '{$TWID}', '{$CNID}'),
					:employee_history_data_id,
					(select leader 
						from hr_title 
					  where seg_segment_no = t.seg_segment_no
					    and title = :title_seqno),
					origin,
					constellation
			    from hr_personnel_temp t
			   where id = :tmp_seqno 
eof;
		//$this->_dbConn->debug = 1;
		$tmp_seqno = '';
		$cnt = count($emp_row_data);
		$user_seqno = $this->_userSeqno;
		$idcard_chk_rule = $this->getIDCardCheckWay();
		$result      = 'N';
		$success_cnt = 0;
		$fail_cnt    = 0;
		$success_psnids  = array();
		$fail_tmpids = array();
		$trans_result_msg = array();
		$this->_dbConn->BeginTrans();
		// add data to trans master
		$r0 = $this->_addEmp2TransMaster($issue_date,$issue_no);
		if ($r0 == 1){
			for ($i=0; $i<$cnt; $i++)
			{
				$tmp_seqno = $emp_row_data[$i]['tmp_seqno'];
				$r = 1;
				if ($idcard_chk_rule['IDCARD_CHECK'] == 'Y' && 
					$idcard_chk_rule['IDCARD_CHECK_TYPE'] == self::PARAM_CHK_IDCARD_DUPL)
				{
					if ($this->_isIDCardExists($emp_row_data[$i]['id_card']) == 1)
					{
						$trans_result_msg[$tmp_seqno]['errmsg'] = '身份證號碼重複，不可錄入.';
						//throw new Exception('身份證號碼重複，不可錄入.',$tmp_seqno);
						$r = 0;
					}
				}
				if ($r == 1){
					$seqno = $this->_dbConn->GetOne('select hr_personnel_s.nextval from dual');
					$emp_row_data[$i]['emp_seq'] = $seqno;
					$emp_row_data[$i]['emp_seq1'] = $seqno;									
					$result = 'N';
					$errmsg = '';
					// add employee data to hr_personnel
					$this->_dbConn->Execute($sql,$emp_row_data[$i]);
					$r1 = $this->_dbConn->Affected_Rows();
					if ($r1 == 1){
						$emp_data = $this->_getEmpRowData($seqno);
						$r2 = $this->_addEmpNewCardingRow($emp_data['SEG_SEGMENT_NO'],
								$emp_data['INDATE'],$emp_data['CARDING'],$emp_data['CREATE_BY'],
								$emp_data['ID']);
						if ($r2 == 1){
							$r3 = $this->_addEmp2TransDetail($emp_data,$issue_date,$issue_no);
							if ($r3 == 1){
								$result = 'Y';
								// 回写暂存档转档结果
								$r4 = $this->_afterTrans2HCP($tmp_seqno,$result,$emp_data['ID_NO_SZ'],$user_seqno,$errmsg);
								if ($r4 != 1){
									//throw new Exception('回写临时档错误:'.$this->_dbConn->ErrorMsg(),$tmp_seqno);
									$trans_result_msg[$tmp_seqno]['errmsg'] = '回寫到暫存檔動檔錯誤:'.$this->_dbConn->ErrorMsg();
								}else{
									$this->_dbConn->CommitTrans();
									$success_psnids[$success_cnt] = $seqno;
									$success_cnt++;
								}
							}else{
								//throw new Exception('添加人事异动明细档错误:'.$this->_dbConn->ErrorMsg(),$tmp_seqno);
								$trans_result_msg[$tmp_seqno]['errmsg'] = '添加到人事異動錯誤:'.$this->_dbConn->ErrorMsg();
							}
						}else{
							//throw new Exception('添加卡别异动档错误:'.$this->_dbConn->ErrorMsg(),$tmp_seqno);
							$trans_result_msg[$tmp_seqno]['errmsg'] = '添加到卡別異動檔錯誤:'.$this->_dbConn->ErrorMsg();
						}
					}else{
						//throw new Exception('添加到人事档错误:'.$this->_dbConn->ErrorMsg(),$tmp_seqno);
						$trans_result_msg[$tmp_seqno]['errmsg'] = '添加到人事檔錯誤:'.$this->_dbConn->ErrorMsg();
					}
				}
				if (count($trans_result_msg)>0)
				{
					$this->_dbConn->RollbackTrans();
					$r5 = $this->_afterTrans2HCP($tmp_seqno,$result,
							@$emp_data['ID_NO_SZ'],$user_seqno,$trans_result_msg[$tmp_seqno]['errmsg']);
					$fail_tmpids[$fail_cnt] = $tmp_seqno;
					$fail_cnt++;
				}else{
					$this->_dbConn->CommitTrans();
				}
			}
		}else{
			$this->_dbConn->RollbackTrans();
			return '添加到人事異動主檔錯誤:'.$this->_dbConn->ErrorMsg();
		}
		//pr($trans_result_msg);
		$trans_result['success'] = $success_cnt;
		$trans_result['fail'] = $fail_cnt;
		$trans_result['success_psnids'] = $success_psnids;
		$trans_result['fail_tmpids'] = $fail_tmpids;
		return $trans_result;
	}
	/**
	 * 
	 * @param string $idcard
	 */
	private function _isIDCardExists($idcard)
	{
		$this->_beforeTrans2HCP();
		$sql = <<<eof
			select 1 from hr_personnel 
			where id_card = :id_card 
			  and outdate is  null
		      and seg_segment_no = :company_id 
eof;
		//$this->_dbConn->debug =1;
		return $this->_dbConn->GetOne($sql,array('id_card'=>$idcard,
				'company_id'=>$this->_companyId));
	}
	
	/**
	 * Help function of trans2HCP()
	 * Get Employee Data by emp seqno
	 * @param int $seqno
	 * @return array row of hr_peronnel
	 * @author Dennis
	 */
	private function _getEmpRowData($seqno)
	{
		$sql = <<<eof
		select id,
			   seg_segment_no,
			   name,
			   indate,
			   seg_segment_no_department,
			   title,
			   create_by,
			   carding,
			   overtime_type_id,
			   absence_type_id,
			   year_leave_id,
			   jobcategory,
			   contract,
			   period_master_id,
			   tw_tax_id,
			   id_no_sz,
			   nb_leader as nb_newleader,
			   costallocation as newcostallocation
		  from hr_personnel
		 where seg_segment_no = :company_id
		   and id = :seqno
eof;
		return $this->_dbConn->GetRow($sql,array('company_id'=>$this->_companyId,
				'seqno'=>$seqno));
	}
	
	/**
	 * help function of trans2HCP()
	 * init some data before trans data
	 * @param no
	 * @return void
	 */
	private function _beforeTrans2HCP()
	{
		$this->_dbConn->Execute('begin pk_erp.p_set_segment_no(\''.$this->_companyId.'\');end;');
	}
	
	
	/**
	 * Help function of trans2HCP,轉檔成功後刪除 by Dennis 2012/12/12
	 * Update trans result to temp table after trans
	 * @param string $tmp_seqno  暂存档 seqno 
	 * @param string $result     $result trans result, Y_success or N_error
	 * @param string $emp_id	 转档成功得到的员代码
	 * @param string $trans_by   转档人使用者 seqno
	 * @param string $errmsg     转档错误信息
	 * @return int
	 */
	private function _afterTrans2HCP($tmp_seqno,$result,$emp_id,$trans_by,$errmsg = '')
	{
		$is_err = $result == 'Y' ? 'N' : 'Y';
		$sql_update = <<<eof
			update hr_personnel_temp
			   set is_approve = :result,
				   is_fail    = :err,
				   id_no_sz   = :emp_id,
				   fail_reason = :error_reason,
				   trans2_hcp_date = sysdate,
				   trans2_hcp_by = :trans_by
			 where id = :tmp_seqno
eof;
		$sql_del = 'delete from hr_personnel_temp where id = :tmp_seqno';
		if ($is_err == 'Y'){
			$this->_dbConn->Execute($sql_update,array('tmp_seqno'=>$tmp_seqno,'emp_id'=>$emp_id,
				'result'=>$result,'err'=>$is_err,'trans_by'=>$trans_by,'error_reason'=>$errmsg));
		}else{
			$this->_dbConn->Execute($sql_del,array('tmp_seqno'=>$tmp_seqno));
		}
		return $this->_dbConn->Affected_Rows();
	}
	/**
	 * add new carding change after insert to hr_personnel
	 * 
	 * @param string $seg_segment_no company id
	 * @param string $indate         employee join date
	 * @param string $carding        employee carding type
	 * @param string $create_by      data creator
	 * @param string $psn_id		 employee seq no
	 */
	private function _addEmpNewCardingRow($seg_segment_no,$indate,$carding,$create_by,$psn_id)
	{
		$sql = <<<eof
			insert into hr_change_carding(
				change_carding_id,
   				seg_segment_no,
   				psn_id,
   				change_date_begin,
   				change_date_end,
   				new_carding,
   				is_active,
   				create_by,
   				create_date,
   				create_program
   			)values(
   				hr_change_carding_s.nextval,
   				:seg_segment_no,
   				:id,
   				to_date(:indate,'yyyy-mm-dd'),
   				'',
   				:carding,
   				'Y',
   				:create_by,
   				sysdate,
   				'ESNP002'
   			)
eof;
		$this->_dbConn->Execute($sql,array('seg_segment_no'=>$seg_segment_no,
				'indate'=>$indate,
				'create_by'=>$create_by,
				'carding'=>$carding,
				'id'=>$psn_id));
		return $this->_dbConn->Affected_Rows();
	}

	private function _addEmp2TransMaster($issue_date,$issue_no)
	{
		// 设置create_by , create_programe,这么处理是因为hr_transmaster表上的trigger的原因
		$sql = "begin pk_erp.p_set_username(:create_by);pk_erp.p_set_function_id('ESNP002');end;";
		$this->_dbConn->Execute($sql,array('create_by' => $this->_userSeqno));
		$sql = <<<eof
			insert into hr_transmaster(
				seg_segment_no,
				issueno,
				issuedate,
				remark,
				create_date
			)values(
				:seg_segment_no,
				:issue_no,
				to_date(:issue_date,'yyyy-mm-dd'),
				'員工新進',
				sysdate
			)
eof;
		$this->_dbConn->Execute($sql,array(
			'seg_segment_no' => $this->_companyId,
				'issue_no'=>$issue_no,
				'issue_date'=>$issue_date
		));
		return $this->_dbConn->Affected_Rows();
	}

	private function _addEmp2TransDetail($emp_data,$issue_date,$issue_no)
	{
		// remove unused carding
		unset($emp_data['CARDING']);
		unset($emp_data['ID_NO_SZ']);
		$emp_data['seg_segment_no1'] = $emp_data['SEG_SEGMENT_NO'];
		$emp_data['indate'] = $emp_data['INDATE'];
		$emp_data['issue_date'] = $issue_date;
		$emp_data['issue_no'] = $issue_no;
		$emp_data['title1'] = $emp_data['TITLE'];
		$emp_data['seg_segment_no_department1'] = $emp_data['SEG_SEGMENT_NO_DEPARTMENT'];
		$emp_data['overtime_type_id1'] = $emp_data['OVERTIME_TYPE_ID'];
		$emp_data['absence_type_id1'] = $emp_data['ABSENCE_TYPE_ID'];
		$emp_data['year_leave_id1'] = $emp_data['YEAR_LEAVE_ID'];
		$emp_data['jobcategory1'] = $emp_data['JOBCATEGORY'];
		
		$emp_data = array_change_key_case($emp_data,CASE_LOWER);
		//pr($emp_data);exit;
		$sql = <<<eof
			insert into hr_transdetail
			  (psn_id,
			   psn_seg_segment_no,
			   psn_name,
			   htm_issuedate,
			   htm_issueno,
			   validdate,
			   issuetype,
			   olddepartment,
			   oldtitle,
			   newdepartment,
			   newtitle,
			   create_by,
			   create_date,
			   --update_by,
			   --update_date,
			   new_overtime_type_id,
			   new_absence_type_id,
			   new_year_type_id,
			   old_overtime_type_id,
			   old_absence_type_id,
			   old_year_type_id,
			   new_jobcategory,
			   old_jobcategory,
			   hr_transdetail_seq,
			   new_contract,
			   new_period_id,
			   new_tw_tax_id,
			   nb_newleader,
			   newcostallocation)
			values
			  (:id,
			   :seg_segment_no,
			   :name,
			   to_date(:issue_date, 'yyyy-mm-dd'),
			   :issue_no,
			   to_date(:indate, 'yyyy-mm-dd'),
			   pk_personnel_msg.f_get_transtype(:seg_segment_no1, '4'),
			   :seg_segment_no_department,
			   :title,
			   :seg_segment_no_department1,
			   :title1,
			   :create_by,
			   --sysdate,
			   --'eHR',
			   sysdate,
			   :overtime_type_id,
			   :absence_type_id,
			   :year_leave_id,
			   :overtime_type_id1,
			   :absence_type_id1,
			   :year_leave_id1,
			   :jobcategory1,
			   :jobcategory,
			   hr_transdetail_s.nextval,
			   :contract,
			   :period_master_id,
			   :tw_tax_id,
			   :nb_newleader,
			   :newcostallocation
			   )
eof;
		//$this->_dbConn->debug = 1;
		$this->_dbConn->Execute($sql,$emp_data);
		return $this->_dbConn->Affected_Rows();
	}
	
	/**
	 * help function of _getEmpId()
	 * get employee id identify type
	 * @param no
	 * @return string
	 * @author Dennis
	 */
	private function _getEmpIdRule()
	{
		$sql = <<<eof
			select emp_id_rule from ehr_idcard_param where seg_segment_no = :company_id
eof;
		return $this->_dbConn->CacheGetOne(self::DATA_CACHE_SECS,$sql,array('company_id'=>$this->_companyId));
	}
	
	/**
	 * Help function of trans2HCP
	 * @return string the employee id
	 * @author Dennis
	 * @last update by Dennis 2012-11-13 for Use Cust Gen Emp ID rule
	 */
	protected function _getGenEmpIdFunc()
	{
		return  'xfle_f_get_id_no(seg_segment_no,:contract1,:costallocation1)';
		/*
		$empid_rule = $this->_getEmpIdRule();
		return $empid_rule == 'cust' ? 
		'xfle_get_id_no(seg_segment_no,:contract1,:costallocation1)' :
		'f_generate_empno(seg_segment_no)';
		*/
	}

	/**
	 * Save ID Card Data to tmp table
	 * @param array $row
	 * @return resource
	 * @author Dennis 2012-10-15
	 */
	function save2Temp($row)
	{
		$row_def['company_id']	= $this->_companyId;
		$row_def['create_by']	= $this->_userSeqno;
		$row_def['empno'] 		= '';
		$row_def['hr_title']	= '';
		$row_def['hr_department'] = '';
		$row_def['emphoto']		= '';
		
		$last_row = array_merge($row,$row_def); // last row data

		$sql = <<<eof
			insert into hr_personnel_temp(
				id,
				name,
				sex,
				nation,
				birth,
				address,
				id_card_type,
				id_card,
				id_no_sz,
				title,
				seg_segment_no_department,
				seg_segment_no,
				create_by,
				create_time,
				indate,
				emphoto,
				origin,
				idcard_issue_date,
				idcard_expired_date,
				issue_org_name,
				photo_base64_data,
				constellation
			)values(
				hr_personnel_temp_s.nextval,
				:name,
				:sex,
				:nation,
				to_date(:born,'yyyymmdd'),
				:address,
				1,
				:cardno,
				:empno,
				:hr_title,
				:hr_department,
				:company_id,
				:create_by,
				sysdate,
				:indate,
				:emphoto,
				:origin,
				:activitylfrom,
				:activitylto,
				:police,
				:photo,
				:constellation
			)
eof;
		//$this->_dbConn->debug = 1;
		//pr($last_row);
		return $this->_dbConn->Execute($sql,$last_row);
	}

	/**
	 * 从hr_personnel_temp 表中查询符合条件的员工
	 * @param $where , query condition
	 * 
	 */
	public function GetEmpList($where = ''){
		$sql = <<<eof
		select id,
		       name,
		       sex,
		       nation,
		       birth,
		       address, 
		       indate,
		       id_card,
		       is_approve,
		       fail_reason,
		       is_fail
		  from hr_personnel_temp
		 where seg_segment_no = :company_id
		   and is_approve <> 'Y' 
		   $where
		 order by create_time asc
eof;
		//$this->_dbConn->debug = 1;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		/*
		if($total){
			$sql = "select count(1) from ({$sql})";
			return $this->_dbConn->GetOne($sql,array(
				'company_id' => $this->_companyId,
			));
		}
		$rs= $this->_dbConn->SelectLimit($sql,$numrows,$offset,array(
			'company_id' => $this->_companyId,
		));
		if($rs){
			return $rs->GetArray();
		}*/
		return $this->_dbConn->GetArray($sql,array(
			'company_id' => $this->_companyId,
		));
	}
	
	/**
	 * Check IDCardNo is exists (contain turnover emps)
	 * 检查是在职员工 or Rehire (有离职日期的员工)
	 * @param string $idcardno
	 * @author dennis 2012-09-21
	 * @last update by dennis 2012-12-17
	 *   1. add  'and seg_segment_no = :company_id  and indate >sysdate-100000' for use index improve performance
	 */
	public function isRehireEmp($idcardno,$rehire = true)
	{
		$where = $rehire ? ' and outdate is not null' : //離職
				 ' and outdate is null'; // 在職
		$sql = <<<eof
			select count(1) from hr_personnel
			 where upper(id_card) = upper(:idcardno)
			 $where
eof;
		//$this->_dbConn->Execute('begin dodecrypt();end;');
		return $this->_dbConn->CacheGetOne(self::DATA_CACHE_SECS,$sql,
			   array('idcardno'=>$idcardno,'company_id'=>$this->_companyId));
	}
	/**
	 *
	 * Insert 之前 Get 身份证号检查规则
	 * @param no
	 * @author Dennis 2011-11-14
	 */
	public function getIDCardCheckWay()
	{
		$sql = <<<eof
		select idcard_check_type,
			   idcard_check
		  from gl_segment
		 where segment_type = 'COMPANY'
		   and seg_segment_no = :seg_segment_no
eof;
		//$this->_dbConn->debug =1;
		return $this->_dbConn->CacheGetRow(self::DATA_CACHE_SECS,$sql,
				array('seg_segment_no'=>$this->_companyId));
	}

	/**
	 * check the idcard is in the black list
	 * @param string $idcardno string id card number
	 * @return string
	 * @author Dennis 2012-09-25
	 */
	public function isBlacklist($idcardno)
	{
		$sql = <<<eof
			select nvl(remark_sz,'"未輸入原因"')
			  from hr_personnel_blacklist
			 where id_card = :id_card_no
eof;
		//$this->_dbConn->Execute('begin dodecrypt();end;');
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->CacheGetOne(self::DATA_CACHE_SECS,$sql,
				array('id_card_no'=>$idcardno));
	}

	/**
	 * Check 是不是童工，未成年工
	 * call db function f_get_child_labor
	 * T_童工 W_未成年工 Z_正常
	 * @param string $idcardno  id card number
	 */
	public function isChidLabor($idcardno)
	{
		$birthday = substr($idcardno,6,8);
		$sql = <<<eof
			select f_get_child_labor(to_date(:birthday,'yyyymmdd'),sysdate) from dual
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetOne($sql,array('birthday'=>$birthday));
	}
	/**
	 * Get Age by IDCard No
	 * @param string $idcardno
	 */
	public function getPersonalAge($idcardno)
	{
		$birthday = substr($idcardno,6,8);
		$sql = <<<eof
			select f_get_age(to_date(:birthday,'yyyymmdd'),sysdate) from dual
eof;
		return $this->_dbConn->GetOne($sql,array('birthday'=>$birthday));
	}
	/**
	 * get old employee data
	 * @param string $idcardno
	 */
	public function getEmpOldData($idcardno)
	{
		$sql = <<<eof
		select a.seg_segment_no as company_id,
		       a.id             as emp_seqno,
		       a.id_no_sz       as emp_id,
		       a.name_sz        as emp_name,
		       a.indate         as join_date,
		       a.outdate        as leave_date,
		       round(f_year_sz(a.id,a.outdate,a.seg_segment_no),2) as w_years,
		       g.codevalue      as leave_reason,
		       b.segment_no_sz  as dept_id,
		       b.segment_name   as dept_name,
		       c.id_no_sz       as mgr_emp_id,
		       c.name_sz        as mgr_name,
		       d.segment_no_sz  as p_dept_id,
		       d.segment_name   as p_dept_name,
		       e.id_no_sz       as p_mgr_emp_id,
		       e.name_sz        as p_mgr_name
		  from hr_personnel_base a,
		       gl_segment        b,
		       hr_personnel_base c,
		       gl_segment        d,
		       hr_personnel_base e,
		       hr_personnel_out  f,
		       hr_codedetail     g
		 where pk_history_data.f_get_value(a.seg_segment_no,
		                                   a.id,
		                                   trunc(sysdate),
		                                   '1') = b.segment_no
		   and upper(pk_crypt_sz.decryptC(a.id_card)) = upper(:idcardno)
		   and b.parent_segment_no = d.segment_no
		   and b.seg_segment_no = c.seg_segment_no
		   and b.leader_emp_id = c.id
		   and d.seg_segment_no = e.seg_segment_no
		   and d.leader_emp_id = e.id
		   and a.seg_segment_no = f.seg_segment_no
		   and a.id = f.psn_id
		   and f.seg_segment_no = g.hcd_seg_segment_no
		   and f.reason = g.codeid
		   and g.hcd_codetype = 'RESIGNREASON'
eof;
		//$this->_dbConn->debug = 1;
		//$this->_dbConn->Execute('begin dodecrypt();end;');
		return $this->_dbConn->CacheGetRow(0,$sql,
				array('idcardno'=>$idcardno));
	}

	/**
	 * 二次錄用人員原獎懲記錄
	 * @param string $idcardno
	 *
	 */
	public function getMeritList($idcardno)
	{
		$sql = <<<eof
		select a.cday      as merit_date,
		       b.meritname as merit_desc,
		       a.document  as issue_no,
		       a.remarks   as remarks
		  from hr_merits a, hr_merittype b, hr_personnel_base c
		 where a.psn_seg_segment_no = b.seg_segment_no
		   and b.codeid = a.class
		   and a.psn_seg_segment_no = c.seg_segment_no
		   and a.psn_id = c.id
		   and upper(pk_crypt_sz.decryptC(c.id_card)) = upper(:idcardno)
eof;
		//$this->_dbConn->debug = 1;
		//$this->_dbConn->Execute('begin dodecrypt();end;');
		return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECS,$sql,
				array('idcardno'=>$idcardno));
	}
	/**
	 * Check temp table exists
	 * @param string $idcardno
	 */
	public function isTmpExists($idcardno)
	{
		$sql = <<<eof
			select 'Y'
		      from hr_personnel_temp
			 where id_card = :id_card_no
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetOne($sql,array('id_card_no'=>$idcardno));
	}
	/**
	 * Get Trans error emp list from tmp table
	 * @param string $ids
	 */
	public function getTransTmpErrList($ids)
	{
		$sql = <<<eof
			select id,name, id_card, fail_reason
			  from hr_personnel_temp
			 where id in ($ids)
eof;
		return $this->_dbConn->GetArray($sql);
	}
	
	/**
	 * Get trans success employee list from hr_personnel
	 * @param string $psnids
	 */
	public function getTransSuccessEmpList($psnids)
	{
		$this->_beforeTrans2HCP();
		$sql = <<<eof
			select id_no_sz,name_sz,id_card
			  from hr_personnel
			 where id in ($psnids)
eof;
		return $this->_dbConn->GetArray($sql);
	}
	
	public function getNoFabIDCardEmpList($where)
	{
		$sql = <<<eof
		select d.*
		from (
		select rownum as rn,
			   b.id            as emp_seqno,
		       b.id_no_sz      as emp_id,
		       b.name_sz       as emp_name,
		       c.segment_no_sz as dept_id,
		       c.segment_name  as dept_name,
		       a.subid         as idcard_no,
		       b.indate        as indate
		  from hr_substitute a, 
			   hr_personnel  b, 
			   gl_segment    c
		 where a.psn_seg_segment_no(+) = b.seg_segment_no
		   and a.psn_id(+) = b.id
		   and a.psn_name(+) = b.name
		   and b.seg_segment_no = c.seg_segment_no
		   and b.seg_segment_no_department = c.segment_no
		   $where
		   and b.seg_segment_no = :company_id
		   and b.outdate is null
		   order by b.id_no_sz) d
		where rn < 501
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId));
	}

	/**
	 * 新增员工工卡号
	 * @param string $emp_seqno
	 * @param string $card_no
	 * @param string $indate
	 * @return int
	 */
	public function addFabIDCard($emp_seqno,$card_no,$indate)
	{
		/*
		$sql = <<<eof
		select 1 from hr_substitute 
		where psn_seg_segment_no = :company_id
		  and psn_id = :emp_seqno
		  and is_active = 'Y'
eof;
		$is_exists = $this->_dbConn->GetOne($sql,array('company_id'=>$this->_companyId,'emp_seqno'=>$emp_seqno));
		*/
		$sql = <<<eof
		select b.id_no_sz as emp_id, b.name_sz as emp_name
		  from hr_substitute a, hr_personnel_base b
		 where a.psn_seg_segment_no = b.seg_segment_no
		   and a.psn_id = b.id
		   and a.psn_seg_segment_no = :company_id
		   and a.subid = :card_no
		   and a.is_active = 'Y'
		   and b.outdate is null				
eof;
		$is_cardno_exists = $this->_dbConn->GetRow($sql,array('company_id'=>$this->_companyId,'card_no'=>$card_no));
		if (count($is_cardno_exists) == 0)
		{
			/*
			$sql = <<<eof
				insert into hr_substitute
				  (psn_seg_segment_no,
				   psn_id,
				   psn_name,
				   subid,
				   begintime,
				   endtime,
				   reason,
				   create_by,
				   create_date,
				   create_program,
				   is_active)
				values
				  (:company_id,
				   :emp_seqno,
				   :emp_seqno1,
				   :card_no,
				   :indate,
				   sysdate + 36500,
				   'G01',
				   :create_by,
				   sysdate,
				   'ESNQ001',
				   'Y')
eof;
			//$this->_dbConn->debug = 1;
			$this->_dbConn->Execute($sql,array('company_id'=>$this->_companyId,
				'emp_seqno'=>$emp_seqno,'emp_seqno1'=>$emp_seqno,'card_no'=>$card_no,
				'indate'=>$indate,'create_by'=>$this->_userSeqno));
				*/
			//$this->_dbConn->debug = 1;
			$this->_dbConn->Replace('hr_substitute',
					array('psn_seg_segment_no'=>"'$this->_companyId'",
						  'psn_id'=>$emp_seqno,'psn_name'=>$emp_seqno,
						  'subid'=>"'$card_no'",'begintime'=>"to_date('$indate','yyyy-mm-dd')",
						  'create_by'=>$this->_userSeqno,'endtime'=>'sysdate + 36500',
						  'reason'=>"'G01'",'create_date'=>'sysdate',
						  'create_program'=>"'ESNQ001'",'is_active'=>"'Y'"),
					array('psn_seg_segment_no','psn_id','psn_name','indate'));
			return $this->_dbConn->Affected_Rows();
		}else{
			if (count($is_cardno_exists)>0)
			return '此卡號已經錄入過(現在使用此卡號員工:'.$is_cardno_exists['EMP_ID'].'/'.$is_cardno_exists['EMP_NAME'].'),請換一張.';
		}
	}
	/**
	 * Get Employee list for print the id card
	 * @param string $where
	 */
	public function getFabEmpList($where)
	{
		$sql = <<<eof
		select d.*
		from (
			select rownum as rn,
			       b.id as emp_seqno,
			       b.id_no_sz as emp_id,
			       b.name_sz as emp_name,
			       decode(b.sex, 'M', '男', '女') as gender,
			       c.segment_name as dept_name,
			       f_get_idcard_dept(b.seg_segment_no, b.seg_segment_no_department) as idcard_dept_name,
			       b.indate as indate,
			       e.segment_no_sz as company_no_sz,
			       e.segment_name as company_name
			  from hr_personnel b, gl_segment c, gl_segment e
			 where b.seg_segment_no = c.seg_segment_no
			   and b.seg_segment_no_department = c.segment_no
			       $where
			   and b.seg_segment_no = :company_id
			   and b.outdate is null
			   and b.seg_segment_no = e.seg_segment_no
			   and e.segment_type = 'COMPANY'
			 order by b.id_no_sz
		) d
		where rn < 201
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId));
	}
	
	/**
	 * IDCard Reader Parameters (one row per company)
	 * @param number $dept_level
	 * @param string $empid_rule
	 * @param string $company_name
	 * @param string $logo_url
	 * @param string $barcode_type	default 'code39'
	 * @return int
	 * @author Dennis
	 */
	public function updateIDCardParams($dept_level,
									   $empid_rule,
										$company_name,
										$logo_url = null,
										$barcode_type = 'code39',
										$font_family='標楷體',
										$font_size = '16',
										$font_style='bold'){
		$row_data = array('seg_segment_no'=>$this->_companyId,
					  'idcard_dept_level' =>$dept_level, 
					  'barcode_type'=>$barcode_type,
					  'emp_id_rule'=>$empid_rule,
					  'company_name'=>$company_name,
					  'font_family'=>$font_family,
					  'font_size'=>$font_size,
					  'font_style'=>$font_style,
					  'create_by'=>$this->_userSeqno, 
					  'update_by'=>$this->_userSeqno);
		if (!is_null($logo_url))
		{
			$row_data['logo_url']=$logo_url;
		}
		$ret = $this->_dbConn->Replace('ehr_idcard_param',
				$row_data,
				'seg_segment_no',$autoquote = true);
		return $this->_dbConn->Affected_Rows();
	}
	
	/**
	 * Get IDCard Parameter List
	 */
	public function getIDCardParams()
	{
		$sql = <<<eof
			select seg_segment_no,
			       idcard_dept_level as dept_level,
			       barcode_type,
			       emp_id_rule as empid_rule,
			       company_name,
			       logo_url,
			       font_family,
			       font_size,
			       font_style
			  from ehr_idcard_param
			 where seg_segment_no = :company_id
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetRow($sql,array('company_id'=>$this->_companyId));
	}
	
	public function getCardData($tmp_seqno)
	{
		$sql = <<<eof
			select id,name, sex, nation, to_char(birth,'yyyymmdd') as birth, address, id_card, origin, indate,constellation
			  from hr_personnel_temp
			 where id = $tmp_seqno
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetRow($sql);
	}
	/**
	 * update hr_personnel_temp
	 * 
	 * @param array $row_data
	 */
	public function setCardData($row_data)
	{
		$sql = <<<eof
			update hr_personnel_temp
			   set name    = :name,
			       sex     = :sex,
			       nation  = :nation,
			       birth   = :born,
			       address = :address,
			       id_card = :cardno,
			       origin  = :origin,
			       indate  = :indate,
				   constellation = :constellation
			 where id = :tmpid
eof;
		return $this->_dbConn->Execute($sql,$row_data);
	}
	
	/**
	 * 根据计税区域判断是否是台湾公司
	 */
	public function isTWCompany()
	{
		/**
		 * 台湾税区 = 2
		 */
		$sql = <<<eof
			select 'Y'
			  from gl_segment
			 where segment_type = 'COMPANY'
			   and seg_segment_no = :company_id
			   and tax_area = '2'
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->CacheGetOne(self::DATA_CACHE_SECS,$sql,array('company_id'=>$this->_companyId));
	}
}

