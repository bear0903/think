<?php
/**
 *  請假申請excel導入
 *  Create by Boll Yuan   2009-04-01
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_leave_apply_import_DB.php $
 *  $Id: emp_leave_apply_import_DB.php 3756 2014-05-12 07:56:35Z dennis $
 *  $Rev: 3756 $ 
 *  $Date: 2014-05-12 15:56:35 +0800 (周一, 12 五月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-05-12 15:56:35 +0800 (周一, 12 五月 2014) $
 ****************************************************************************/
require_once 'AresAction.class.php';
class Imp extends AresAction {
	public $program_name = "emp_import";
	
	public function actionList(){
		if(empty($_GET['id'])){
			header('location: ?scriptname=user_excel_upload&used_program=emp_leave_apply_import');exit;
		}
		$rs=$this->getUploadData($this->_companyId,$this->_userEmpId,$_GET['id']);
		$rs=$this->check_data($rs);
		
		$this->tpl->assign("list", $rs);
		$this->tpl->assign ( "show", 'List');
	}
	public function actionCancel()
	{
	    $linenums = $_POST['successfull_line_no'];
	    $sql = <<<eof
	      delete from ehr_upload_data
         where setup_id = :setup_id
           and company_id = :company_id
	       and emp_id  = :emp_id
           and line_no in $linenums
eof;
	    //$this->db->debug = 1;
	    $r = $this->db->Execute($sql,array('setup_id'=>$_GET['id'],
	            'company_id'=>$this->_companyId,
	            'emp_id'=>$this->_userEmpId
	    ));
	    if ($r){
	        header('location: ?scriptname=emp_leave_apply_import&do=List');
	        exit;
	    }
	}
	/**
	 * save batch apply
	 */
	public function actionSaveApply(){
	   
	    
		include_once 'AresAttend.class.php';
		$Attend = new AresAttend ($this->_companyId,
								  $this->_userEmpSeqno);
		$result=array();
		$rs=$this->getUploadData($this->_companyId,
								 $this->_userEmpId,
								 $_GET['id'],
								 " and A.LINE_NO in ".$_POST['successfull_line_no']);
		//pr($rs);exit;
		$count_successfull=0;
		$n=count($rs);
		for($i=0;$i<$n;$i++){
		    /* 未用到 remark by dennis 2013/12/25
			$absence_id=$this->db->GetOne("select absence_seq_no from  ehr_absence_type_v eatv
										where eatv.absence_id='01'
										      and company_id = '".$this->_companyId."'
										      and is_active = 'Y'");*/
										      
			// 參數初始化
			$row=array( 'user_seqno'=>   $this->_userSeqno,
						'absence_id'=>   substr($rs[$i]['ABSENCE_ID'],0,stripos($rs[$i]['ABSENCE_ID'],'::')),
						'begin_time'=>   $rs[$i]['BEGIN_TIME'],
						'end_time'  =>   $rs[$i]['END_TIME'],
						'leave_reason'=> $rs[$i]['COL5'],
						'submit_type'=>  'Y',
						'funeral_id'=>   null,
						'emp_seqno'=>    $rs[$i]['EMP_SEQ_NO']);			
			//pr(row);exit;
			$result[$i]=$Attend->SaveLeaveForm(
			 					$row['user_seqno'],
			 					$row['absence_id'],
			 					$row['begin_time'],
			 					$row['end_time'],
			 					$row['leave_reason'],
			 					$row['submit_type'],
			 					$row['funeral_id'],
			 					$row['emp_seqno']);
			$result[$i]['LINE_NO']=$rs[$i]['LINE_NO'];
			$result[$i]['DEPT_ID']=$rs[$i]['DEPT_ID'];
			$result[$i]['DEPT_NAME']=$rs[$i]['DEPT_NAME'];
			$result[$i]['COL1']=$rs[$i]['COL1'];
			$result[$i]['EMP_NAME']=$rs[$i]['EMP_NAME'];
			$result[$i]['COL4']=$rs[$i]['COL4'];
			$result[$i]['COL2']=$rs[$i]['COL2'];
			$result[$i]['COL3']=$rs[$i]['COL3'];
			$result[$i]['COL5']=$rs[$i]['COL5'];
			// delete the temp data after submit add by dennis 2013/10/21
			$this->_delUploadData($this->_companyId,$rs[$i]['EMP_ID'],$rs[$i]['SETUP_ID'],$rs[$i]['LINE_NO'],$rs[$i]['COL1'], $rs[$i]['COL2'],$rs[$i]['COL3']);
			if($result[$i]['is_success'] =='Y')  $count_successfull++;
		}
		//pr($result);//exit;
		$this->tpl->assign("list", $result);
		$this->tpl->assign("show", 'result');
		$this->tpl->assign("count_successfull", $count_successfull);
		$this->tpl->assign("count_all", $n);
		$this->tpl->assign("fail_rows", $n - $count_successfull);
	}
	/**
	 * 
	 * @param unknown $name
	 * @param string $program_no
	 * @return unknown
	 */
	public function getLabel($name,$program_no='ESNE008'){
		$sql=<<<eof
		select value
		  from app_muti_lang
		 where program_no = :program_no
		   and lang_code = :lang_code
		   and type_code = 'IT'
		   and name = :name
eof;
		$rs=$this->db->GetOne($sql,array('program_no'=>$program_no,
										'lang_code'=>$GLOBALS['config']['default_lang'],
										'name'=>$name));
		//echo $rs;
		return $rs;
	}
	
	function getImpMsg($program_no = 'ESNE008')
	{
		$sql=<<<eof
		select name,value
		  from app_muti_lang
		 where program_no = :program_no
		   and lang_code = :lang_code
		   and type_code = 'IT'
eof;
		$rs = $this->db->GetOne($sql,array('program_no'=>$program_no,
										   'lang_code'=>$GLOBALS['config']['default_lang']));
		return $rs;
	}
	/**
	 * Delete data after submit 失败的也删除(add by dennis 2013/11/01)
	 * add by Dennis 2013/10/18
	 */
	private function _delUploadData($companyid,$assis_emp_id,$setupid,$lineno,$col1,$col2,$col3)
	{
	    $sql = <<<eof
	       delete from ehr_upload_data
             where company_id = :company_id
               and emp_id = :emp_id
               and setup_id = :setup_id
               and line_no = :line_no
               and col1 = :col1
               and col2 = :col2
               and col3 = :col3
eof;
	    return $this->db->Execute($sql,array('company_id'=>$companyid,'emp_id'=>$assis_emp_id,
	            'setup_id'=>$setupid,'line_no'=>$lineno,'col1'=>$col1,'col2'=>$col2,'col3'=>$col3));
	    
	}
	/**
	 * 检查日期格式是否正确
	 * @param string $date
	 * @param string $format
	 * @return boolean
	 * @since >=php 5.4
	 */
	protected function _validateDate5($date,$format = 'Ymd Hi')
	{
	
	    $d = DateTime::createFromFormat($format, $date);
	    return $d && $d->format($format) == $date;
	}
	
	/**
	 * 检查日期字串是否符合格式 yyyymmdd hh24mi，及日期时间是否符合逻辑
	 * @param string $input_date
	 * @return boolean
	 */
	protected function _validateDate($input_date)
	{
	    //echo $input_date.'<hr/>';
	    $reg_one = "/^(\d{4})(\d{1,2})(\d{1,2}) ([01][0-9]|2[0-3])([0-5][0-9])$/";
	    //檢查格式後用php function做日期檢查
	    if (preg_match($reg_one, $input_date, $matches)){
	        if (checkdate($matches[2], $matches[3], $matches[1])) {
	            return true;
	        }
	    }
	    return false;
	}
	
	/**
	 * 提交请假之前检查数据
	 * @param array $rs
	 * @return Ambigous <string, unknown, unknown>
	 */
	protected function check_data($rs=array()){
		$count_successfull=0;
		$successfull_line_no = "(0";
		$n= count($rs);
		$error_msg = $this->getImpMsg();
		//pr($error_msg);
		for($i=0;$i<$n;$i++){
			$rs[$i]['ERROR_CODE']='';			
			if(empty($rs[$i]['EMP_NAME'])){
				$rs[$i]['ERROR_CODE']='ERROR_100';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'工号错误，或者该员工已离职!';
				
				$this->_delUploadData($this->_companyId,
				        $rs[$i]['EMP_ID'],
				        $rs[$i]['SETUP_ID'],
				        $rs[$i]['LINE_NO'],
				        $rs[$i]['COL1'], 
				        $rs[$i]['COL2'],
				        $rs[$i]['COL3']);
				continue;
			}
			/**
			 * 判断助理是否对此员工有权限输入资料
			 * add by Dennis 2013/12/25
			 */
			if ($rs[$i]['HAS_PERMISSION'] == 'N')
			{
			    $rs[$i]['ERROR_CODE']='ERROR_180';
			    //$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'工号错误，或者该员工已离职!';
			    $rs[$i]['ERROR_TEXT']= '无权限输入此员工资料';
			    $this->_delUploadData($this->_companyId,
			            $rs[$i]['EMP_ID'],
			            $rs[$i]['SETUP_ID'],
			            $rs[$i]['LINE_NO'],
			            $rs[$i]['COL1'], 
			            $rs[$i]['COL2'],
			            $rs[$i]['COL3']);
			    //continue;
			}
			
			if(empty($rs[$i]['COL2'])){
				$rs[$i]['ERROR_CODE']='ERROR_110';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'請假开始时间(yyyymmdd hh24:mi) 未填!';
				$this->_delUploadData($this->_companyId,
				        $rs[$i]['EMP_ID'],
				        $rs[$i]['SETUP_ID'],
				        $rs[$i]['LINE_NO'],
				        $rs[$i]['COL1'], 
				        $rs[$i]['COL2'],
				        $rs[$i]['COL3']);
			}else{
			    if ($this->_validateDate($rs[$i]['COL2'])===false)
			    {
			        $rs[$i]['ERROR_CODE'] = 'ERROR_111';
			        $rs[$i]['ERROR_TEXT'] = '请假开始时间格式(20101101 1300)或时间有误，请检查.';//$this->getLabel($rs[$i]['ERROR_CODE']); //'日期格式不正确!';
			        $this->_delUploadData($this->_companyId,
			                $rs[$i]['EMP_ID'],
			                $rs[$i]['SETUP_ID'],
			                $rs[$i]['LINE_NO'],
			                $rs[$i]['COL1'], 
			                $rs[$i]['COL2'],
			                $rs[$i]['COL3']);
			        continue;
			    }
			}
			
			if(empty($rs[$i]['COL3'])){
				$rs[$i]['ERROR_CODE']='ERROR_120';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'结束时间(yyyymmdd hh24:mi) 未填!';
				$this->_delUploadData($this->_companyId,
				        $rs[$i]['EMP_ID'],
				        $rs[$i]['SETUP_ID'],
				        $rs[$i]['LINE_NO'],
				        $rs[$i]['COL1'], 
				        $rs[$i]['COL2'],
				        $rs[$i]['COL3']);
			
			}else{
			    if ($this->_validateDate($rs[$i]['COL3'])===false)
			    {
			        $rs [$i] ['ERROR_CODE'] = 'ERROR_121';
					$rs [$i] ['ERROR_TEXT'] = '请假结束时间格式(20101101 1300)或时间有误，请检查.';//$this->getLabel($rs[$i]['ERROR_CODE']); 
					$this->_delUploadData($this->_companyId,
					        $rs[$i]['EMP_ID'],
					        $rs[$i]['SETUP_ID'],
					        $rs[$i]['LINE_NO'],
					        $rs[$i]['COL1'], 
					        $rs[$i]['COL2'],
					        $rs[$i]['COL3']);
					continue;
			    }
			}			
			if(empty($rs[$i]['COL4'])){
				$rs[$i]['ERROR_CODE']='ERROR_160';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'假別未填!<BR>';
				$this->_delUploadData($this->_companyId,
				        $rs[$i]['EMP_ID'],
				        $rs[$i]['SETUP_ID'],
				        $rs[$i]['LINE_NO'],
				        $rs[$i]['COL1'], 
				        $rs[$i]['COL2'],
				        $rs[$i]['COL3']);			
			}else{
			    /*
				//select absence_seq_no,  absence_id ||' - '||absence_name
				$sql="select absence_id ||' - '||absence_name  ABSENCE_TYPE
				        from ehr_absence_type_v
				       where company_id = '".$this->_companyId."'
				         and is_active = 'Y'
				         and absence_id='".$rs[$i]['COL4']."'
				      ";
				//echo $sql;
				$tmp_absence_type=$this->db->GetOne($sql);
				*/
				if(empty($rs[$i]['ABSENCE_ID'])){
					// print $e->getMessage();
					$rs[$i]['ERROR_CODE']='ERROR_161';
					$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'假別不正确!<BR>';
					$this->_delUploadData($this->_companyId,
					        $rs[$i]['EMP_ID'],
					        $rs[$i]['SETUP_ID'],
					        $rs[$i]['LINE_NO'],
					        $rs[$i]['COL1'], 
					        $rs[$i]['COL2'],
					        $rs[$i]['COL3']);
					
				}else{
					$rs[$i]['COL4'] = substr($rs[$i]['ABSENCE_ID'],stripos($rs[$i]['ABSENCE_ID'],'::')+2);
				}
			}
			
			//count successfull  line
			if($rs[$i]['ERROR_CODE']==''){
				$count_successfull++;
				$successfull_line_no.=','.$rs[$i]['LINE_NO'];
			}

		}
		$successfull_line_no.=')';
		$this->tpl->assign("successfull_line_no", $successfull_line_no);
		$this->tpl->assign("count_successfull", $count_successfull);
		$this->tpl->assign("count_all", $n);
		return $rs;
	}
	/**
	 * 取得从 Excel 导入的资料
	 * @param string $company_id
	 * @param string $emp_id
	 * @param string $setup_id
	 * @param string $condition_str
	 * @return array
	 */
	public function getUploadData($company_id,$emp_id,$setup_id,$condition_str=""){
		$sql = <<<eof
		select c.segment_no    as dept_seq_no,
               c.segment_no_sz as dept_id,
               c.segment_name  as dept_name,
               b.id            as emp_seq_no,
               b.name_sz       as emp_name,
               a.*,
               a.col2 as begin_time,
               a.col3 as end_time,
               (select absence_seq_no||'::'||absence_id ||' - '||absence_name
                  from ehr_absence_type_v eatv
                 where eatv.absence_id = a.col4
                   and company_id = a.company_id
                   and is_active = 'Y') absence_id,
               pk_user_priv.f_user_priv(:user_seqno,b.seg_segment_no,b.id) has_permission
          from ehr_upload_data a, hr_personnel_base b,gl_segment c
         where a.company_id = b.seg_segment_no(+)
           and a.col1 = b.id_no_sz(+)
           and b.seg_segment_no = c.seg_segment_no(+)
           and b.seg_segment_no_department = c.segment_no(+)
           and a.company_id = :company_id
           and a.emp_id = :emp_id
           and a.setup_id = :setup_id
           $condition_str
           and a.col1 is not null
         order by a.line_no
eof;
		//$this->db->debug = 1;
		$rs=$this->db->GetArray($sql,array('company_id'=>$company_id,
		        'emp_id'=>$emp_id,
		        'user_seqno'=>$this->_userSeqno,
		        'setup_id'=>$setup_id)); //echo $sql;
		return $rs;
	}
}

/*  controller */
if(empty($_GET['do']))  $_GET['do']='List';
$imp = new Imp();
$imp->run();