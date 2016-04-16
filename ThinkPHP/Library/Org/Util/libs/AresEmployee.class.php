<?php
 /**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) Lan Jiangtao 
 *	 
 *	Description:
 *     Employee class
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresEmployee.class.php $
 *  $Id: AresEmployee.class.php 3860 2014-11-10 08:40:36Z dennis $
 *  $Rev: 3860 $ 
 *  $Date: 2014-11-10 16:40:36 +0800 (周一, 10 十一月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-11-10 16:40:36 +0800 (周一, 10 十一月 2014) $
 \****************************************************************************/
    class AresEmployee
    {
        private $companyID; // company id
        private $empSeqNO;  // employee sequence no in table ehr_employee_v
        private $DBConn;    // database connection handler
        /*
         * Data Cache Life Time, add by Dennis 2014/04/14
         */
        const DATA_CACHE_SECONDS = 3600;
        
        /**
        *   Constructor of class AresEmployee
        *   @param $companyid string, the employee's company id
        *   @param $empseqno string, the employee's id in view hr_personnel
        *   @return void.
        */
        function __construct($companyid,$emp_seqno)
        {
            global $g_db_sql;
            $this->companyID = $companyid;
            $this->empSeqNO  = $emp_seqno;
            $this->DBConn 	 = $g_db_sql;
        }// end class constructor
        
        /**
        *   Get employee basic information
        *   @param no
        *   @return array (array("key"=>"value"))
        */
        function GetBaiscInfo()
        {
            $sql = <<<eof
                select company_id,
                       dept_seq_no,
                       dept_id,
                       dept_name,
                       emp_seq_no,
                       emp_id,
                       emp_name,
                       name_en,
                       decode(sex,'M','男','F','女') as sex,
                       sex as sex_code,
                       birthday,
                       nationality_id,
                       nationality,
                       nations,
                       birthplace,
                       title_id,
                       title_name,
                       emp_type_id,
                       emp_type_name,
                       title_grade,
                       salary_level,
                       is_foreigner,
                       cellphone_no,
                       home_tel,
                       extension,
                       permanent_address,
                       address_contactor,
                       postcode,
                       mail_address,
                       mail_postcode,
                       mail_contactor,
                       emergency_contractor,
                       emergency_tel,
                       e_mail,
                       hiredate,
                       id_no,
                       is_served,
                       blood_type,
                       is_marriage,
                       edu_level,
                       join_date,
                       leave_date,
                       year_sal_startdate,
                       work_years,
                       overtime_fee_id,
                       overtime_fee_name,
                       absence_fee_id,
                       absence_fee_name,
                       year_vacation_id,
                       year_vacation_name,
                       salary_type,
                       brush_card_type,
                       remark,
                       emp_status_id,
                       emp_staus,
                       bank_account,
                       bank_id,
                       bank_account1,
                       room_no,
                       factory_zone_id,
                       factory_zone,
                       passport,
                       probation_endate,
                       residence_permit_no,
                       privilege_level,
                       insurance_no,
                       constellation,
                       icq,
                       history_data_id,
                       is_trainee,
                       job_id,
                       job_name,
                       nonleave_pay_id,
                       sal_period_id,
                       sal_period_name,
                       contract_type_id,
                       contract_type,
                       to_formal_date,
                       salary_ratio,
                       tax_area,
                       tax_type_id,
                       tax_type_name,
                       pay_welfare,
                       jobid,
                       id_type,
                       permanent_address_contactor,
                       current_contactor,
                       emp_archives_loc,
                       is_insured,
                       is_reseve_fund,
                       introductor_emp_id,
                       introductor_emp_name,
                       work_address,
                       phone_no
                  from ehr_employee_v 
                where company_id = :companyid
                  and emp_seq_no = :emp_seqno
eof;
            //$this->DBConn->debug = true;
            $stmt = 'begin pk_erp.P_SET_SEGMENT_NO(:companyid); end;';
            $this->DBConn->Execute($stmt,array('companyid'=>$this->companyID));
            $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
            return $this->DBConn->CacheGetRow(self::DATA_CACHE_SECONDS,$sql,array('companyid'=>$this->companyID,
													'emp_seqno'=>$this->empSeqNO));
        }// end GetBaiscInfo()
        
        function GetSysParamVal($parmatype,$paramname)
	    {
	        $sqlstr = <<<eof
	            select parameter_value,
	                   value1,
	                   value2
	              from pb_parameters
	             where parameter_type = :v_parameter_type
	               and parameter_id   = :v_parameter_name
	               and seg_segment_no = :company_id
eof;
	        return $this->DBConn->GetRow($sqlstr,array('v_parameter_type'=>$parmatype,
	                                                   'v_parameter_name'=>$paramname,
	                                                   'company_id'=>$this->companyID));
	    }// end function GetSysParamVal();
        
        public function getEmpContactInfo()
        {
        	$r = $this->getContactInfoFromTmp();
        	if (is_array($r)&& count($r)>0)
        	{
        		return $r;
        	}else{
        		return $this->GetBaiscInfo();
        	}// end if
        }// end getEmpContactInfo()
        
        /**
         * 从暂存表里取资料
         * @param no params
         * @return array
         * @author Dennis 2009-01-20
         */
        function getContactInfoFromTmp()
        {
        	$sql = <<<eof
        		select address_tel            as home_tel,
				       mobiletel              as cellphone_no,
				       tel_part               as extension,
				       address                as permanent_address,
				       address_man            as address_contactor,
				       addresszipcode         as postcode,
				       mailaddress            as mail_address,
				       mailaddress_man        as mail_contactor,
				       mailaddresszipcode     as mail_postcode,
				       emergencycontactor     as emergency_contractor,
				       emergencycontactor_tel as emergency_tel
				  from ehr_pim_tmp
      	         where company_id = :company_id 
      	           and emp_seqno = :emp_seqno
eof;
			//$this->DBConn->debug = true;
			$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
			return $this->DBConn->GetRow($sql,array('company_id'=>$this->companyID,
													'emp_seqno'=>$this->empSeqNO));
        }// end getContactInfoFromTmp()
        
        public function getEmpEditDataList()
        {
        	$sql = <<<eof
        	select a.company_id                as company_id,
        	       a.emp_seqno                 as emp_seqno,
        		   b.seg_segment_no_department as dept_no,
			       c.segment_no_sz             as dept_id,
			       c.segment_name              as dept_name,
			       b.id_no_sz				   as emp_id,
			       b.name_sz                   as emp_name,
			       a.address_tel               as home_tel,
			       a.mobiletel                 as cellphone_no,
			       a.tel_part                  as extension,
			       a.address                   as permanent_address,
			       a.address_man               as address_contactor,
			       a.addresszipcode            as postcode,
			       a.mailaddress               as mail_address,
			       a.mailaddress_man           as mail_contactor,
			       a.mailaddresszipcode        as mail_postcode,
			       a.emergencycontactor        as emergency_contractor,
			       a.emergencycontactor_tel    as emergency_tel
			  from ehr_pim_tmp a, hr_personnel_base b, gl_segment c
			 where a.company_id = b.seg_segment_no
			   and a.emp_seqno = b.id
			   and b.seg_segment_no_department = c.segment_no
			   and a.company_id = :company_id
			   and a.data_status = 0
        	   and pk_user_priv.f_user_priv(:username,a.company_id,a.emp_seqno) = 'Y'
			   order by a.company_id, a.emp_seqno
eof;
        	//$this->DBConn->debug  = 1;
        	$params_array = array('company_id'=>$this->companyID,'username'=>$_SESSION['user']['user_seq_no']);
        	$this->DBConn->Execute("begin pk_erp.P_SET_SEGMENT_NO(:company_id); pk_erp.p_set_username(:username);dodecrypt();end;",$params_array);
        	$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        	return $this->DBConn->GetArray($sql,$params_array);
        }// end getEmpEditDataList()
        
		function GetBankName (){
			$sql_string = <<<eof
				select distinct parameter_value	as bank_name
				  from hr_personnel_base a, PB_PARAMETERS b
				 where a.seg_segment_no = b.seg_segment_no
				   and parameter_type = 'HR_BANK'
				   and a.seg_segment_no = :companyid
				   and a.id = :emp_seqno
				   and a.bankid = b.parameter_id
eof;
			return $this->DBConn->GetOne($sql_string,array("companyid"=>$this->companyID,
														   "emp_seqno"=>$this->empSeqNO));
		}
		
		function GetShiftGroupName (){
			$sql_string = <<<eof
				select workgroup_name as workgroup_name
				  from ehr_calendar_v
				 where company_id = :companyid
				   and emp_seq_no = :emp_seqno
				   and to_char(my_day, 'YYYYMMDD') = to_char(sysdate, 'YYYYMMDD')
eof;
			//echo $this->empSeqNO;
			return $this->DBConn->GetOne($sql_string,array("companyid"=>$this->companyID,
														   "emp_seqno"=>$this->empSeqNO));
		}
        /**
        *   Get Employee photo dir from HCP system setting
        */
        function GetEmpPhotoDir()
        {
            $sql_string = <<<eof
                select parameter_value
                  from pb_parameters
                 where parameter_id = 'PHOTO_URL'
                   and seg_segment_no = :companyid
                   and parameter_type = 'ATTACH'
eof;
            return $this->DBConn->GetOne($sql_string,array("companyid"=> $this->companyID));
        }
        /**
        *   Get employee education background list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetEducationBG()
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
                select schoolname,
                       edu_type_name,
                       major,
                       begindate,
                       enddate,
                       graduatetype
                  from ehr_emp_educationbg_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
			     order by begindate desc
eof;
            return $this->DBConn->GetArray($sql_string);
        }
		/**
        *   Get employee login in list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetLoginList($page_size,$pageIndex)
        {
        	/*
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
			$sql = "
                select username
                  from app_users 
                 where psn_id='".$this->empSeqNO."'";
			$arr = $this->DBConn->GetRow($sql);  //echo $sqlcount;
			//print_r($this->empSeqNO);exit;
			$user_seq_no=$arr['USERNAME'];

            //$user_seq_no = $this->DBConn->GetOne($sql);
			*/
        	/*
				$sql_string ="
                select a.app_use_id,
                       a.app_use_user_no,
                       a.app_use_company_no,
                       to_char(a.APP_USE_DATETIME_BEGIN,'YYYY-MM-DD hh24:mi:ss') as APP_USE_DATETIME_BEGIN,
                       to_char(a.APP_USE_DATETIME_END,'YYYY-MM-DD hh24:mi:ss') as APP_USE_DATETIME_END,
					   a.ip_address,
					   decode(a.source,'ESS','ESS','MGR','MGR','HCP') as source,
					   a.reverse3 as machine_name,
					   a.reverse4 as login_name
                  from app_system_use_historys a 
				   where a.app_use_company_id = '".$this->companyID."'
					 and a.app_use_user_id = '".$this->empSeqNO."'
				   order by a.APP_USE_DATETIME_BEGIN desc";
			*/
        	$sql_string=$this->getLoginListSql;
			// print $sql_string;
			//$this->DBConn->debug = true;
			//return $this->DBConn->GetArray($sql_string);
			$rsLimit=$this->DBConn->SelectLimit($sql_string,$page_size,$page_size*($pageIndex-1));
			return $rs=$rsLimit->getArray();
 		
        }

        /**
        *   Get employee work exprience list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetWorkExperience()
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
                select work_company,
                       job_title,
                       salary,
                       job_type,
                       in_date,
                       out_date,
                       work_years,
                       leave_reason
                  from ehr_emp_workexp_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
				 order by in_date desc
eof;
			//print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }

        /**
        *   Get employee family member list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetFamilyMember()
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
                select name_cn,
                       id_no,
                       rel_ship,
                       sex,
                       foreigner,
                       birthday,
                       occupation,
                       telephone
                  from ehr_emp_family_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
eof;
            return $this->DBConn->GetArray($sql_string);
        }

        /**
        *   Get employee license list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetLicense()
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
                select license_no,
                       license_name,
                       license_dept,
                       confer_date,
                       expired_date,
                       remark
                  from ehr_emp_license_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
eof;
			//print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }
		/**
        *   Get SearchConditon list
        *   @param $search_type PERSONTYPE,CONSTELLATION,MARRIAGE,EDUCATION
		*	add by jack 2006-9-4
        */
		function GetSearchCondition($search_type)
		{
			$companyid = $this->companyID;
			$sql_string = <<<eof
				select codeid, 
					   codeid || '   ' || codevalue as codevalue
				  from hr_codedetail
				 where hcd_seg_segment_no = '$companyid'
				   and hcd_codetype = '$search_type' 
			      order by codeid asc
eof;
		    return $this->DBConn->GetArray($sql_string);
		}
		/**
        *   Get SearchResult list
		*	add by jack 2006-9-4
        */
		function GetSearchResult($wherecond,$user_seq_no,$cond_1,$cond_2)
		{
			$companyid = $this->companyID;
			$plsql_stmt = "begin pk_erp.p_set_date(sysdate);pk_erp.p_set_segment_no(:company_id);pk_erp.p_set_username(:user_seq_no);end;";

			$this->DBConn->Execute($plsql_stmt, array(
									"company_id"  => $companyid,
									"user_seq_no" => $user_seq_no));
			
			$sql_string = <<<eof
				select a.emp_id,
				       emp_name,
					   dept_id,
					   a.emp_seq_no,
					   dept_name,
					   title_name
				  from ehr_employee_v a,
				(select rownum as rno,company_id,emp_id from ehr_employee_v where company_id = '$companyid'
				   $wherecond
					and pk_user_priv.f_user_priv(emp_seq_no) = 'Y') b
				 where a.company_id = '$companyid'
				   and a.company_id = b.company_id
				   and a.emp_id = b.emp_id
				   and b.rno between $cond_1 and $cond_2
			     order by emp_id asc
eof;
			//print $sql_string;
			return $this->DBConn->GetArray($sql_string);
		}

        /**
        *   Get employee skill list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetSkill()
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
                select skill_name,
                       skill_level,
                       remark
                  from ehr_emp_skill_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
eof;
            return $this->DBConn->GetArray($sql_string);
        }

        /**
        *   Get employee competenece list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetCompetence()
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
                select competence_id,
                       competence,
                       competence_level,
                       remark
                  from ehr_emp_competence_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
eof;
			//print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }
        /**
         * Enter description here...
         *
         * @param unknown_type $wherecond
         * @param unknown_type $cond_1
         * @param unknown_type $cond_2
         * @return unknown
         */
        function GetWorkAttendance($wherecond,$cond_1,$cond_2)
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
			$sql_string = <<<eof
                select a.work_form_no,
                       to_char(intime, 'yyyy-mm-dd hh24:mi:ss') as intime,
                       to_char(outtime, 'yyyy-mm-dd hh24:mi:ss') as outtime,
                       to_char(inactual, 'yyyy-mm-dd hh24:mi:ss') as inactual,
                       to_char(outactual, 'yyyy-mm-dd hh24:mi:ss') as outactual,
                       workname,
                       workhours,
                       carding_hours,
                       b.hours,
                       carding_hours - b.hours as hours2,
                       decode(a.holiday, 'S', '例假日', 'H', '国定假日', b.absence_name) as abnormity
                  from ehr_workattendance_v a, ehr_absencecarding_v b
                 where a.company_id = '$companyid'
                   and a.company_id = b.company_id(+)
                   and a.cday = b.my_day(+)
                   and a.emp_seq_no = b.emp_seq_no(+)
                       $wherecond
                   and a.emp_seq_no = '$emp_seqno'
                 order by intime asc
eof;
			//print $sql_string;
			return $this->DBConn->GetArray($sql_string);
        }// end 
        
        function GetYearList () {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
             select to_char(intime, 'yyyy') as year1,
					to_char(intime, 'yyyy') as year2
               from ehr_workattendance_v
              where company_id = '$companyid'
                and emp_seq_no = '$emp_seqno'
              order by intime desc
eof;
			//print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }

		function GetMonthList () {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
             select to_char(intime, 'mm') as month1,
					to_char(intime, 'mm') as month2
               from ehr_workattendance_v
              where company_id = '$companyid'
                and emp_seq_no = '$emp_seqno'
              order by to_char(intime, 'mm') asc
eof;
			//print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }

		function GetMonthList_year ($_where) {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
             select to_char(intime, 'mm') as month1,
					to_char(intime, 'mm') as month2
               from ehr_workattendance_v
              where company_id = '$companyid'
                and emp_seq_no = '$emp_seqno'
				and to_char(intime, 'yyyy') = '$_where'
              order by to_char(intime, 'mm') asc
eof;
			//print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }
		/**
        *   Get year-month rewards list
        *   @add by jack 2007-5-31  
        *   @return array, a 2-dimensional array of records
        */
		function GetYearMonthList () {
			$companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
			$sql_string = <<<eof
			select to_char(intime, 'yyyy') as year1,
				   to_char(intime, 'mm') as month1
			  from ehr_workattendance_v
			 where company_id = '$companyid'
			   and emp_seq_no = '$emp_seqno'
			 group by to_char(intime, 'yyyy'),to_char(intime, 'mm')
			 order by to_char(intime, 'yyyy') desc
eof;
			return $this->DBConn->GetArray($sql_string);
		}

        /**
        *   Get employee rewards list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetReward()
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<_RewardList_
                select occur_date,
                       doc_no,
                       rewards_id,
                       rewards_name,
                       rewards_type,
                       socre,
                       amount,
                       remark
                  from ehr_emp_rewards_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
_RewardList_;
            return $this->DBConn->GetArray($sql_string);
        }

        /**
        *   Get employee position trans detail list
        *   @param no parameter, reference class property companyID, empSeqNO
        *   @return array, a 2-dimensional array of records
        */
        function GetTransDetail()
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<eof
                select issue_date,
					   issue_docno,
					   effectiv_date,
					   trans_id,
					   trans_name,
					   trans_reason_id,
					   trans_reason,
					   dept_id,
					   dept_name,
					   title_id,
					   title_name,
					   salary_type_id,
					   salary_type,
					   contract_type,
					   contract_desc,
					   jd_id,
					   jd_desc,
					   overtime_type_id,
					   overtime_name,
					   absence_id,
					   absence_name,
					   year_holiday_id,
					   year_holiday_name,
					   salary_period_id,
					   salary_period_desc,
					   tax_id,
					   tax_name
				  from ehr_emp_transdetail_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
eof;
			//print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }// end function GetTransDetail()

		/**
		*	@desc 根据登入系统使用者的权限和所下条件,查出符合的人员
		*	@param $user_seq_no string, 登入系统的使用者代码流水号
        *   @param $where string, 查询条件
        *   @param $dblink string, Database link, BIS 透过此link 查资料
		*	@return array, 2-d records array, 
        *   @author: Dennis.Lan(dlan@areschina.com) 2006-04-07 14:50:33 
        *   @lastupdate:2006-08-30 14:10:37  by Dennis.Lan 
		*/
		function GetEmployeeList($user_seq_no,$where,$numrows=-1,$offset=-1,$getcount=null,$dblink = null)
		{
			//$this->DBConn->debug = 1;
			$companyid = $this->companyID;
			// register current login user info for get permissioned employee infomation
			$plsql_stmt = "begin pk_erp.p_set_date{$dblink}(sysdate);pk_erp.p_set_segment_no{$dblink}(:company_id);pk_erp.p_set_username{$dblink}(:user_seq_no);end;";
            $privileges = is_null($dblink) ? "and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'" : '';
            // second manager cannot see the first manager's profile
            // added by Dennis 2013-05-07
            require_once 'AresUser.class.php';
            $User = new AresUser($this->companyID,$user_seq_no);
            $where1 = $User->isSecondMgr($_SESSION['user']['emp_seq_no']) ?
			            ' and nvl(b.leader_emp_id, 0) != emp_seq_no '    :
			            '';
            // end added
			$this->DBConn->Execute($plsql_stmt, array(
									"company_id"  => $companyid,
									"user_seq_no" => $user_seq_no));
			$sql = <<<sql
				select company_id,
					   emp_seq_no,
					   emp_id,
					   emp_name,
                       sex as sex_code,
					   decode(sex,'M','男','F','女') as sex,
					   dept_seq_no,
					   dept_id,
					   dept_name,
					   title_name,
					   factory_zone,
					   e_mail,
					   cellphone_no
				  from ehr_employee_v$dblink, gl_segment b
				 where company_id = '$companyid'
				   and company_id = b.seg_segment_no
				   and dept_seq_no = b.segment_no
                       $privileges
				   and leave_date is null
				       $where
				       $where1
				order by emp_id desc
sql;
			//$this->DBConn->debug = true;
			if (!is_null($getcount))
			{
				$coutsql = 'select count(1) from ('.$sql.')'; 
				return $this->DBConn->GetOne($coutsql);
			}// end if
			$rs = $this->DBConn->SelectLimit($sql,$numrows,$offset);
            //pr($rs->GetArray());
            return $rs->GetArray();
			//return $this->DBConn->GetArray($sql_string);
		}// end function GetEmployeeList()
		
		/**
		 * Get 权限内的员工清单
		 *
		 * @param string $userseqno
		 * @param string $querywhere
		 * @return array
		 * @author Dennis 2008-09-27
		 */
		function GetEmpList($userseqno,$basedate,$querywhere)
		{
            //$this->DBConn->debug=true;
			// register current login user info for get permissioned employee infomation
			$stmt = 'begin pk_erp.p_set_date(to_date(:basedate,\'YYYY-MM-DD\'));pk_erp.p_set_segment_no(:company_id);pk_erp.p_set_username(:user_seq_no);end;';
            $this->DBConn->Execute($stmt, array('basedate'=>(!empty($basedate) ? $basedate:'sysdate'),
            									'company_id'  => $this->companyID,
            									'user_seq_no' => $userseqno));
            $sql = <<<eof
				select a.emp_seq_no,
                       a.emp_id,
                       a.emp_name,
                       a.sex,
                       a.dept_seq_no,
                       a.dept_id,
                       a.dept_name,
                       a.title_name,
                       a.factory_zone,
                       overtime_fee_id,
				       overtimetype_code,
				       overtime_fee_name
                  from ehr_employee_v a
                 where a.company_id = :company_id
                   and pk_user_priv.f_user_priv(a.emp_seq_no) = 'Y'
                   and a.join_date <= pk_erp.f_get_date
                   and pk_history_data.f_get_value(a.company_id,a.emp_seq_no, pk_erp.f_get_date, 'E') = 'JS1'
                   %s                    
				   order by emp_id
eof;
			$sql = sprintf($sql,$querywhere);
		    //$this->DBConn->debug = true;
			return $this->DBConn->GetArray($sql,array('company_id'=> $this->companyID));
		}// end function GetEmpList()
		
		/**
		 * Get所有员工清单 (间接人员)
		 *
		 * @param string $wherecond
		 * @param array  $where_array
		 * @return array
		 * @author Dennis 2009-03-17
		 */
		public function getFullEmpList($wherecond,array $where_array)
		{
			$sql = <<<eof
				select emp_id,
				       emp_name,
				       dept_id,
				       dept_name,
				       emp_seq_no,
				       sex,
				       name_en,
				       title_id,
				       title_name,
				       title_grade,
				       emp_type_id,
				       emp_type_name,
				       join_date,
				       overtime_fee_id,
				       overtimetype_code,
				       overtime_fee_name
				  from ehr_employee_v
				 where company_id = :company_id
				  and  (emp_status_id = 'JS1' or emp_status_id is null)
				  and emp_type_id = 'JO02'
				 $wherecond
eof;
			$where_array['company_id'] = $this->companyID;
			$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
			//$this->DBConn->debug = true;
			return $this->DBConn->GetArray($sql,$where_array);
		}// end getFullEmpList()

        /**
		*	Update employee private information
		*	@param $post_array the global variable Post
		*	@return boolean, if successfully return 1, else return false;
		*/
        function UpdateEmpInfo($post_array, $tablename = 'ehr_pim_tmp')
        {
            //pr($post_array);pr($_POST);exit;
        	$pk = ($tablename == 'ehr_pim_tmp' ? array('company_id','emp_seqno') : array('seg_segment_no','id'));
            //$companyid = $this->companyID;
            /*
            // remarked by dennis 2008-06-03
            $_record["address_tel"] = $post_array["address_tel"];
            $_record["mobiletel"] = $post_array["mobiletel"];
            $_record["email"] = $post_array["email"];
            $_record["tel_part"] = $post_array["tel_part"];
            $_record["address"] = $post_array["address"];
            $_record["address_man"] = $post_array["address_man"];
            $_record["addresszipcode"] = $post_array["addresszipcode"];
            $_record["mailaddress"] = $post_array["mailaddress"];
            $_record["mailaddress_man"] = $post_array["mailaddress_man"];
            $_record["mailaddresszipcode"] = $post_array["mailaddresszipcode"];
            $_record["emergencycontactor"] = $post_array["emergencycontactor"];
            $_record["emergencycontactor_tel"] = $post_array["emergencycontactor_tel"];
			*/
        	//pr($pk);
            $_record = array();
            foreach($post_array as $key=>$val)
            {
                $_record[$key] = $val;
            }
            if ($tablename == 'ehr_pim_tmp')
            {
	            $_record['company_id'] = $this->companyID;
	            $_record['emp_seqno']  = $this->empSeqNO;
	            $_record['data_status']= '0';
            }
            $_record['update_program'] = 'ESS';
            $_record['update_by']      = $this->empSeqNO;
            
            //$this->DBConn->debug = true;
            //$this->DBConn->StartTrans();
            //PR($_record);EXIT;
            // for HQ add by dennis 2010-10-08
            $stmt = 'begin pk_erp.p_set_segment_no(:companyid); end;';
            $this->DBConn->Execute($stmt,array('companyid'=>$this->companyID));
            $this->DBConn->Replace($tablename,$_record, $pk,true);
			//$this->DBConn->CommitTrans();
            return $this->DBConn->Affected_Rows();
        }// end function UpdateEmpInfo();
        
        /**
         * Get 员工修改暂存档中人事资料档中正式的资料
         * 
         * @return array
         */
        public function getOldData()
        {
        	$sql = <<<eof
        	select a.company_id                as company_id,
        	       a.emp_seqno                 as emp_seqno,
        		   b.seg_segment_no_department as dept_no,
			       c.segment_no_sz             as dept_id,
			       c.segment_name              as dept_name,
			       b.id_no_sz				   as emp_id,
			       b.name_sz                   as emp_name,
			       b.address_tel               as home_tel,
			       b.mobiletel                 as cellphone_no,
			       b.tel_part                  as extension,
			       b.address                   as permanent_address,
			       b.address_man               as address_contactor,
			       b.addresszipcode            as postcode,
			       b.mailaddress               as mail_address,
			       b.mailaddress_man           as mail_contactor,
			       b.mailaddresszipcode        as mail_postcode,
			       b.emergencycontactor        as emergency_contractor,
			       b.emergencycontactor_tel    as emergency_tel
			  from ehr_pim_tmp a, hr_personnel_base b, gl_segment c
			 where a.company_id = b.seg_segment_no
			   and a.emp_seqno = b.id
			   and b.seg_segment_no_department = c.segment_no
			   and a.company_id = :company_id
			   and a.data_status = 0
        	   and pk_user_priv.f_user_priv(:username,a.company_id,a.emp_seqno) = 'Y'
			   order by a.company_id, a.emp_seqno
eof;
        	$params_array = array('company_id'=>$this->companyID,'username'=>$_SESSION['user']['user_seq_no']);
        	$this->DBConn->Execute("begin pk_erp.P_SET_SEGMENT_NO(:company_id); pk_erp.p_set_username(:username);dodecrypt();end;",$params_array);
        	$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        	return $this->DBConn->GetArray($sql,$params_array);
        	/*
			$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
			return $this->DBConn->GetArray($sql,array('company_id'=>$this->companyID));
			*/
        }// end getOldData()
        
        /**
        *   Get workgroup list
        *   @param no parameter
        *   @return 2-d array
        *   @author: dennis 2006-04-01 15:00:52 
        *
        */
        function GetWorkGroupList()
        {
            $sql = <<<eof
                select shift_group_id as workgroup_seqno,
                       shift_group_code || ' - ' || shift_group_name as workgroup_name
                  from hr_shift_group
                 where is_active = 'Y'
eof;
            return $this->DBConn->GetArray($sql,array('company_id'=>$this->companyID));
        }// end GetWorkGroupList()
        
        /**
         * 所有職等
         *
         * @return array
         * @author dennis 2009-03-12
         */
        function getGradeList()
        {
        	$sql = <<<eof
        	select grade as grade_seqno, 
			       grade || ' ' || grade_name as grade_desc
			  from hr_grades
			 where seg_segment_no = :company_id
			 order by grade
eof;
			$this->DBConn->SetFetchMode(ADODB_FETCH_NUM);
			return $this->DBConn->GetArray($sql,array('company_id'=>$this->companyID));
        }
        
   		function getFactoryList()
        {
        	$sql = <<<eof
        	select zone_setup_id as factory_seqno,
			       zone_no || ' ' || zone_name as factory_desc
			  from hr_zone_setup
			 where seg_segment_no = :company_id
eof;
			$this->DBConn->SetFetchMode(ADODB_FETCH_NUM);
			return $this->DBConn->GetArray($sql,array('company_id'=>$this->companyID));
        }
        
        /**
         * 即时发邮件会有 Performance Issue (timeout)(Primax 发现 8 笔都会 timeout)
         * 所以改成邮件 insert 到 app_mail_log 中从后台来发
         * by Dennis 2013/10/23
         * @param string $mailto
         * @param string $subject
         * @param string $mail_body
         * @author Dennis
         */
        public function insMail2DB($mailto,$subject,$mail_body)
        {
            $sql = <<<eof
            insert into app_mail_log
                (seq,
                 submit_from,
                 mail_from,
                 mail_to,
                 mail_head,
                 mail_body,
                 create_by)
              values
                (app_mail_log_s.nextval,
                 'ESS',
                 '"ESS" <noreply@areschina.com>',
                 :mailto,
                 :subject,
                 :msg_body,
                 'ESS')
eof;
            return $this->DBConn->Execute($sql,array('mailto'=>$mailto,
                    'subject'=>$subject,'msg_body'=>$mail_body));
            
        }
        /**
         * Get Employee name,email addreess
         * @param int $emp_seqno
         */
        public function getEmpNameMail($emp_seqno)
        {
            $sql = <<<eof
                select name_sz as emp_name,email
                  from hr_personnel_base
                 where seg_segment_no = :company_id
                   and id = :psn_id
eof;
            return $this->DBConn->CacheGetRow(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->companyID,'psn_id'=>$emp_seqno));
        }
        
        /**
         * Delete temp data 
         * @param string $companyid
         * @param int $empseqno
         * @return mixed
         */
        public function delPIMTmpData($companyid,$empseqno)
        {
            $sql = 'delete from ehr_pim_tmp where company_id = :company_id and emp_seqno = :emp_seqno';
            //$this->DBConn->debug = 1;
            return $this->DBConn->Execute($sql,array('company_id'=>$companyid,'emp_seqno'=>$empseqno));
        }

    }// end class AresEmployee