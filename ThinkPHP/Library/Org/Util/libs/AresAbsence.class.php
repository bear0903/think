<?php
/*
 *Create by Terry 2011-7-17
 *Description:
 *This class is used to operate the absence of employee 
 */
class AresAbsence{
	private $conn=null;
	private $company_id=null;
	private $emp_seq_no=null;
	/**
	 *@param $company_id
	 *@param $emp_seq_no
	 */
	function __construct($company_id,$emp_seq_no){
		global $g_db_sql;
		$this->conn=$g_db_sql;
		$this->company_id=$company_id;
		$this->emp_seq_no=$emp_seq_no;
	}
	/**
	 *@param null
	 *return array
	 *return absence type 
	 */
	public function GetAbsenceType(){
		$sql="select absence_type_id type_id,absence_name name,absence_ename ename from hr_absence_type where is_active='Y'";
		$_absence_type = $this->conn->GetArray($sql);
		return $this->CombineAbsenceType($_absence_type);
	}
	
	/**
	 *@param $_absence_type
	 *Description:
	 *将请假类型数组重新组合,用于 smarty html_options
	 *Return Array
	 */
	public function CombineAbsenceType($_absence_type){
		$tmp = array();
		if(is_array($_absence_type)){
			foreach($_absence_type as $val){
				$tmp[$val['TYPE_ID']] = $val['NAME'];
			}
		}
		return $tmp;
	}
	/**
	 * 根据假别、请假开始和结束时间来统计请假信息
	 * 
	 */
	public function GetAbsenceInfo($begin_time,$end_time,$leave_type='',$leave_dept=''){
		$sql=<<<eof
			select /*count(*) as leave_times,*/
			       sum(a.days) as leave_days,
			       sum(a.hour) as leave_hours,
			       c.segment_name as leave_segment_name,
			       d.absence_name as leave_name
			  from hr_absence a, hr_personnel_base b, gl_segment c,hr_absence_type d
			 where a.psn_id = b.id and a.psn_seg_segment_no = b.seg_segment_no and
			       b.seg_segment_no = c.seg_segment_no and
			       b.seg_segment_no_department = c.segment_no and
			       a.reason = d.absence_type_id and
			       b.seg_segment_no = :company_id and
			       a.is_active = 'Y'
			       /*a.psn_id = $this->emp_seq_no and*/
			       a.cday between to_date(:begin_time,'yyyy-mm-dd') and to_date(:end_time,'yyyy-mm-dd')
			       %s
			 group by c.segment_name, d.absence_name		
eof;
		$where = '';
		//按请假类型查询
		if($leave_type){
			$where .= "and a.reason = '$leave_type'";
		}
		//按请假部门查询
		if($leave_dept){
			$where .= "and c.segment_no_sz = '$leave_dept'";
		}else{ //  如果没有输入部门就为权限内的所有部门，而不是所有的部门 add by dennis 2013/10/10 
			$where .= "and PK_USER_PRIV.F_dept_priv(c.segment_no)='Y'";
		}
		//$this->conn->debug=1;
		$sql = sprintf($sql,$where);
		$_statistics = $this->conn->GetArray($sql,array(
			"begin_time"=>$begin_time,
			"end_time"=>$end_time,
		    "company_id"=>$this->company_id			
		));
		//pr($_statistics);
		return $_statistics;
	}
	/**
	 * @param $user_seq_no
	 * Description:
	 * 根据登陆用户选择可以查询的部门
	 * Return Array
	 */
	function GetDept($user_seq_no){
		$companyid = $this->company_id;
        $emp_seqno = $this->emp_seq_no;
		$stmt = "begin pk_erp.p_set_segment_no(:company_id); pk_erp.p_set_username(:user_seq_no); end;";
 
		$this->conn->Execute($stmt, array(
									"company_id"  => $companyid,
									"user_seq_no" => $user_seq_no));
		$sql_string = <<<_GetDepName_
			select segment_no,
                   segment_no_sz,
                   seg_segment_no,
                   segment_name
				from gl_segment
			where seg_segment_no = :company_id
			    	and sysdate between begindate and enddate
                   and PK_USER_PRIV.F_dept_priv(segment_no)='Y'
				  order by segment_no_sz
_GetDepName_;
			//$this->conn->SetFetchMode(ADODB_FETCH_DEFAULT);
            $dept=$this->conn->GetArray($sql_string,array('company_id'=>$companyid));
            return $this->CombineDept($dept);
	}
	/**
	 *@param $arr Array
	 *Description:
	 *将部门数组重新组合,用与smarty html_options
	 *Return Array 
	 */
	function CombineDept($dept){
		$tmp=array();
		if(is_array($dept)){
			foreach($dept as $val){
				$tmp[$val['SEGMENT_NO_SZ']] = $val['SEGMENT_NAME'];
			}
		}
		return $tmp;
	}
/**
	 *	@param $year
	 *	@param $month
	 *	@param $company_id
	 *	@param $emp_seq_no
	 *	Description:
	 *		根据年份,月份,公司的Id,员工的id来获取记薪期间的开始和结束日期
	 *	Return array('salary_begin_dat'=>,'salary_end_date'=>)
	 */
	function GetSalaryPeriod($year,$month,$company_id,$emp_seq_no){
		//$this->conn->debug = 1;
		$sql = "select pk_history_data.f_get_value('$company_id',$emp_seq_no,sysdate,'K') from dual";
		$period_master_id = $this->conn->GetOne($sql);
		$period_detail_no = $year."/".str_pad($month, 2, 0, STR_PAD_LEFT);
		$sql = "select salary_begin_date,salary_end_date from hr_period_detail where period_master_id = :period_master_id and period_detail_no = :period_detail_no";
		return $this->conn->getRow($sql,array(
			"period_master_id" => $period_master_id,
			"period_detail_no" => $period_detail_no
		));
	}
}