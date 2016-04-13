<?php
/*
 *	Create by Terry 2011-7-18
 *	Description:
 *	This class is used to operate the overtime of employee 
 */
class AresOvertime{
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
	 * @param void
	 *Description:
	 *Return the reason of overtime
	 *Return Array 
	 */
	function GetOvertimeReason(){
		$sql="select overtime_reason_id reason_id, overtime_reason_desc reason
				from hr_overtime_reason
			  where seg_segment_no=:company_id
		";
		//$this->conn->debug=1;
		return $this->conn->GetArray($sql,array(
			'company_id'=>$this->company_id
		));
	}
	/**
	 * @param void
	 * Description:
	 * Return the type of overtime
	 * Return Array 
	 */
	function GetOvertimeType(){
		return array(
			'A'=>'计费',
			'B'=>'补休',
			'C'=>'其他'
		);
	}
	/**
	 *	@param $begin_time 
	 *	@param $end_time
	 *	@param $overtime_type
	 *	@param $overtime_dept
	 *	Description:
	 *	根据开始时间、结束时间、加班原因的ID来统计加班的信息
	 *	Return Array
	 */
	function GetOvertimeInfo($begin_time,$end_time,$overtime_type,$overtime_dept){
		$sql=<<<eof
			select decode(b.reason,'A','计费','B','补休','C','其他') overtime_type,
				   /*b.reason overtime_type_code, 
			 	   count(*) as overtime_times,*/
				   sum(b.hourt) as overtime_hours,
				   c.segment_name overtime_segment_name
			from hr_personnel_base a, hr_overtime b, gl_segment c	
			where a.seg_segment_no = b.psn_seg_segment_no and
				  a.id = b.psn_id and
				  a.seg_segment_no = c.seg_segment_no and
				  a.seg_segment_no_department = c.segment_no and
				 /* b.psn_id    = $this->emp_seq_no and*/
				  b.is_active = 'Y' and 
				  a.seg_segment_no = :company_id and
			 	  b.cday between to_date(:begin_time,'yyyy-mm-dd') and to_date(:end_time,'yyyy-mm-dd')
			 	  %s
			group by c.segment_name,b.reason
eof;
		$where = '';
		//按加班类型查询
		if($overtime_type){
			$where .= "and b.reason = '$overtime_type'";
		}
		//按部门查询
		if($overtime_dept){
			$where .= "and c.segment_no_sz = '$overtime_dept'";
		}else{ //  如果没有输入部门就为权限内的所有部门，而不是所有的部门 add by dennis 2013/10/10 
			$where .= "and PK_USER_PRIV.F_dept_priv(c.segment_no)='Y'";
		}
		//$this->conn->debug=1;
		$sql = sprintf($sql,$where);
		$_statistics = $this->conn->GetArray($sql,array(
			"begin_time" => $begin_time,
			"end_time"   => $end_time,
		    "company_id" => $this->company_id
		));
		//pr($_statistics);
		return $_statistics;
		/*
		//按加班类型来组合数组
		$statistics = $this->RecombineStatisticsByType($_statistics);
		//如果按部门查询,就按部门来组合数组
		if($overtime_dept && empty($overtime_type)){
			$statistics = $this->RecombineStatisticsByDept($_statistics);
		}
		return $statistics;
		*/
	}
	/**
	 * @param $_statistics 统计加班信息的数组
	 * Description:
	 * 用于将查询结果的数据按照加班类型来组合成二维数组
	 * Return $arr
	*/
	function RecombineStatisticsByType($_statistics){
		$tmp=array();
		if(is_array($_statistics)){
			foreach($_statistics as $v){
				switch (strtoupper($v['OVERTIME_TYPE_CODE'])){
					case 'A':
						$tmp['A'][] = $v;
						break;
					case 'B':
						$tmp['B'][]	= $v;
						break;
					case 'C':
						$tmp['C'][] = $v;
						break;
					default:
						break;
				}//end switch
			}//end foreach
		}//end if
		return $tmp;
	}
	
	/**
	 *	@param $_statistics 统计加班信息的数组
	 *	Description:
	 *		按加班部门来组合数组
	 *	Return Array
	 */
	function RecombineStatisticsByDept($_statistics){
		$tmp = array();
		if(is_array($_statistics)){
			//获取segment_no_sz
			@ $segment_no_sz = $_statistics[0]['SEGMENT_NO_SZ'];
			foreach ($_statistics as $v){
				$tmp[$segment_no_sz][] = $v;
			}
		}
		//pr($tmp);
		return $tmp;
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
	 *	@param $arr Array
	 *	Description:
	 *	将部门数组重新组合,用与smarty html_options
	 *	Return Array 
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