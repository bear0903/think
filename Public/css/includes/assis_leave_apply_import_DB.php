<?php
/*
 * Assistant Batch Leave Apply  
 * Improve Performance by Dennis 2014/05/14
 * 
 */
require_once 'AresExcel.class.php';

class ImpAbsApply extends AresAction {
    /**
     * error message store
     * @var array
     */
    private $_errorMsgs;
    
	public function actionList()
	{
	    
		if($_FILES && !empty($_FILES)){
		    
			$data = $this->getExcelData();
			
			$total_rows = count($data);
			
			// 前台校验数据
			$data = $this->_frontCheckData($data);
			
			//pr($data);exit;
			$front_succ_data = $data['succ_data'];
			$front_fail_data = $data['fail_data'];
			
			// 后台校验结果
			$backend_fail_data = array();
			$backend_succ_data = array();
			
			if(count($front_succ_data)>0){
			    
			    // 后台保存数据，得到成功，失败（后台）rows
			    //$this->db->debug = 1;
				$result = $this->save2db($front_succ_data);
				$backend_fail_data = $result['fail_data'];
				$backend_succ_data = $result['succ_data'];
				
				// submit run once job
				$once_job_str = <<<eof
    				declare v_job varchar2(32); begin dbms_job.submit(v_job,'begin dodecrypt();pkg_concurrent_request.p_leave_apply_batch(pi_batch_no=>{$result['request_no']}); end;',sysdate,null,false);commit;end;
eof;
				$once_job_stmt = $this->db->PrepareSP($once_job_str);
				$ok = $this->db->Execute($once_job_stmt);
				
				if (!$ok){
				    showMsg($this->db->ErrorMsg(),'error');
				    exit;
				}
				$this->tpl->assign('succ_data',$backend_succ_data);
			}
			
			$fail_rows = array_merge($front_fail_data,$backend_fail_data);
			
			$succ_row_cnt = count($backend_succ_data);
			$fail_row_cnt = count($fail_rows);
			// display the least error rows to screen
			if($fail_row_cnt>0){
				$this->tpl->assign('fail_data',$fail_rows);
				
				//把错误数据写入到数据库中 remark by Dennis 2013/10/20
				//$sid=$this->saveFailData($fail_data);
				//$this->tpl->assign('sid',$sid);
			}
			
			$this->tpl->assign('download_url',$this->getDownloadUrl());
			$this->tpl->assign('total_num',$total_rows);
			$this->tpl->assign('succ_num',$succ_row_cnt);
			$this->tpl->assign('fail_num',$fail_row_cnt);
			
			// add by dennis 2013/10/20
			if ($succ_row_cnt >0)
				$this->tpl->assign('imp_result_msg','您可以在<a href="../ess/redirect.php?scriptname=ESNE024&requestno='.$result['request_no'].'&appdesc='.urlencode('请假汇入结果查询').'">這裡</a>查看申請處理結果.批次號為:'.$result['request_no']);
			$this->tpl->assign('display',true);
		}else{
			$this->tpl->assign('download_url',$this->getDownloadUrl());
			$this->tpl->assign('display',false);
		}
		
		//导出错误数据到Excel
		/* 暂未启用
		if(isset($_POST['excel']) && isset($_POST['sid']) && $_POST['excel']=='export' && $sid=  $_POST['sid']){
			$this->exportExcel($sid);
		}*/
	}
	public function getExcelData(){
	    //H为最大的栏位编号
	    $excel=new AresExcel(null,'E');
	    return $excel->readExcel();
	}

	/**
	 * Save Leave Apply to DB
	 * @param array $leave_apply_list
	 * @return multitype:multitype: string
	 */
	public function save2db($leave_apply_list)
	{
	    set_time_limit(0);
	    $ins_succ_rows = array();
	    $ins_fail_rows = array();
	     
	    $request_no = '';
	    //$this->db->debug = 1;
	    // insert data to master table and returning the sequence number
	    $sql = <<<eof
	    begin insert into ehr_concurrent_request(request_no, data_from, request_emp_no, submit_date, status) values (ehr_concurrent_request_s.nextval,'leave_apply',:emp_id,sysdate,'N') returning request_no into :request_no; end;
eof;
	    $stmt = $this->db->PrepareSP($sql);
	    $this->db->InParameter($stmt,$this->_userEmpId,'emp_id',100);
	    $this->db->OutParameter($stmt,$request_no,'request_no');
	    $this->db->Execute($stmt);
	    // insert data to detail table & validate the 
	    $sql = <<<eof
	       insert into ehr_concurrent_leave_apply
              (request_no,
               user_seqno,
               dep_seqno,
               begin_time,
               end_time,
               abs_type_id,
               remark,
               emp_seqno,
               company_id)
            values
              (:request_no,
               :user_seqno,
               :dep_seqno,
               to_date(:begin_time, 'yyyy-mm-dd hh24:mi:ss'),
               to_date(:end_time, 'yyyy-mm-dd hh24:mi:ss'),
	           :abs_type_id,
               :remark,
               :emp_seqno,
               :company_id)
eof;
	    foreach($leave_apply_list as $k=>$row){
	         
	        $ok = $this->db->Execute($sql,array(
	                'request_no'=>$request_no,
	                'user_seqno'=>$this->_userSeqno,
	                'dep_seqno'=>$row['dept_id'],
	                'begin_time'=>$row[1],
	                'end_time'=>$row[2],
	                'abs_type_id'=>$row[3],
	                'remark'=>$row[4],
	                'emp_seqno'=>$row['emp_seqno'],
	                'company_id'=>$this->_companyId));
	        if($ok){
	            array_push($ins_succ_rows,$row); // store the success rows
	        }else{
	            $row['ERROR_CODE']='ERROR_190:';
	            $dup_err_msg = 'ORA-00001:';
	            $ins_err_msg = '';
	            if (stripos($this->db->ErrorMsg(),$dup_err_msg) === false){
	                $ins_err_msg = $this->db->ErrorMsg();
	            }else{
	                $ins_err_msg = '导入的请假资料有重复';
	            }
	            $row['ERROR_TEXT'] = $ins_err_msg;
	            array_push($ins_fail_rows,$row); // store the insert failure rows
	        }
	    }
	    return array('fail_data'=>$ins_fail_rows,
	            'succ_data'=>$ins_succ_rows,
	            'request_no'=>$request_no);
	}
	
	/**
	 * Get Error Message multiple language
	 * @param string $name
	 * @param string $program_no
	 * @return string
	 */
	public function getLabel($name,$program_no='ESNE008'){
	    if (is_array($this->_errorMsgs) && isset($this->_errorMsgs[$name])){
	        return $this->_errorMsgs[$name];
	    }else{
	        $sql = <<<eof
    	       select name,value from app_muti_lang
    	        where program_no = :program_no
    	         and lang_code = :lang
    	         and type_code = 'IT'
    	         and substr(name,0,6) ='ERROR_'
eof;
	        //$this->db->debug = 1;
	        $msgs = $this->db->CacheGetArray(0,$sql,array('lang'=>$_SESSION['user']['language'],
	                'program_no'=>$program_no));
	        	
	        foreach ($msgs as $val){
	            $this->_errorMsgs[$val['NAME']] = $val['VALUE'];
	        }
	        return $this->_errorMsgs[$name];
	    }
	}
	
	/**
	 * 取得导入的员工清单，并检查此助理是否有权限导入此人的资料
	 * @param string $emp_ids   employee id string, contact by comma
	 * @param string $companyid
	 * @param string $userseqno
	 */
	protected function _getEmpList($emp_ids,$companyid,$userseqno)
	{
	    //$this->db->debug = 1;
	    $this->db->BeginTrans(); // 必须加这个，否则 PHP 抓不到资料
	    // 把所有的员工代码 insert 到 temporary table
	    $stmt = "begin dodecrypt(); p_ehr_insert_from_lists(:empids,'ehr_import_tmp',',');end;";
	    $this->db->Execute($stmt,array('empids'=>$emp_ids));
	    // 根据前面 insert 员工代码 join 人事档，取得实际员工清单包括是否有权限等
	    $sql = <<<eof
		      select a.id,a.seg_segment_no_department dept_id,a.id_no_sz,
    		         pk_user_priv.f_user_priv(:user_seqno,a.seg_segment_no,id) has_permission
    		    from hr_personnel_base a, ehr_import_tmp b
    		   where a.id_no_sz = b.id_no_sz
		         and seg_segment_no=:company_id
	             and outdate is null
eof;
	    $rs = $this->db->GetArray($sql,array(
	            'company_id'=>$companyid,
	            'user_seqno'=>$userseqno
	    ));
	    $this->db->CommitTrans(); // commit, clear temporary table data
	    return $rs;
	}
	
	/**
	 * Get All Overtime Reason
	 * for validation the overtime reason id in excel
	 */
	private function _getLeaveTypeList()
	{
	    $sql = <<<eof
		  SELECT absence_code as type_code
            FROM hr_absence_type
           WHERE seg_segment_no = :company_id
eof;
	    return $this->db->CacheGetArray(36000,$sql,array('company_id'=>$this->_companyId));
	}
	
	/**
	 * Get array by specify key from a 2 dim array
	 * @param array $arr
	 * @param string $key
	 * @return array
	 */
	private function _getArrFromArrByKey($arr,$key)
	{
	    $r = array();
	    foreach ($arr as $v){
	        foreach ($v as $k=>$val){
	            if ($k == $key) $r[] = $val;
	        }
	    }
	    return $r;
	    //return array_map(function($myarray) use($key) {return $myarray[$key];}, $arr); // support php 5.3+
	}
	
	/**
	 * 检查指定 $key, 指定值 $val 是否在 Array $rs 中
	 * @param string $key
	 * @param string $val
	 * @param array $rs
	 * @return array|boolean
	 */
	private function _checkValExistsByKey($key,$val,$rs)
	{
	    // clousure function get the ids array
	    $ids = $this->_getArrFromArrByKey($rs, $key);
	    $idx = array_search($val, $ids);
	    if (FALSE !== $idx) return $rs[$idx];
	    return false;
	}
	
	/**
	 * Insert 到 DB 之前前台简单检查数据是否符合
	 * @param array $rs
	 * @return unknown|multitype:multitype:unknown string  multitype:Ambigous <>
	 */
	protected function _frontCheckData(array $rs)
	{
	    $succ_data = array();
	    $fail_data = array();
	   
	    // 把员工代码组成 array
	    $emp_ids = $this->_getArrFromArrByKey($rs, 0);
	    // 把员工代码 array 转成以逗号分隔的 string
	    $ids_str = implode(",",$emp_ids);
	    //echo $ids_str;
	    $emp_list = self::_getEmpList($ids_str, $this->_companyId, $this->_userSeqno);
	    //pr($emp_list);
	    $leave_type_list = $this->_getLeaveTypeList();
	    //pr($leave_type_list);
	    $rowcnt = count($rs);
	    for($i=0;$i<$rowcnt;$i++){
	        // 基本数据检查
	        if(empty($rs[$i][1])){
	            $rs[$i]['ERROR_CODE']='ERROR_110';
	            $rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'开始时间(yyyymmdd hh24:mi) 未填!';
	            $fail_data[]=$rs[$i];
	            continue;
	        }
	        
	        if(empty($rs[$i][2])){
	            $rs[$i]['ERROR_CODE']='ERROR_120';
	            $rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'结束时间(yyyymmdd hh24:mi) 未填!';
	            $fail_data[]=$rs[$i];
	            continue;
	        }
	        
	        if(empty($rs[$i][3])){
	            $rs[$i]['ERROR_CODE']='ERROR_160';
	            $rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'假别代码未填';
	            $fail_data[]=$rs[$i];
	            continue;
	        }else{
	            $is_abs_type_ok = self::_checkValExistsByKey('TYPE_CODE', $rs[$i][3], $leave_type_list);
	            if (false === $is_abs_type_ok){
	                $rs[$i]['ERROR_CODE']='ERROR_161';
	                $rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'假别代码未填';
	                $fail_data[] = $rs[$i];
	                continue;
	            }
	        }
	        
	        // check employee is exists
	        if(empty($rs[$i][0])){
	            $rs[$i]['ERROR_CODE']='ERROR_100';
	            $rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'工号错误，或者该员工已离职!';
	            $fail_data[]=$rs[$i];
	            continue;
	        }else{
	            $emp = self::_checkValExistsByKey('ID_NO_SZ',$rs[$i][0], $emp_list);
	            if($emp === false){
	                $rs[$i]['ERROR_CODE']='ERROR_100';
	                $rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'工号错误，或者该员工已离职!';
	                $fail_data[]=$rs[$i];
	                continue;
	            }else{
	                $rs[$i]['emp_seqno']=$emp['ID'];
	                $rs[$i]['dept_id']=$emp['DEPT_ID'];
	            }
	
	            // 校验是否有权限 add by Dennis 2013/12/25
	            if ($emp['HAS_PERMISSION'] == 'N'){
	                $rs[$i]['ERROR_CODE']='ERROR_180';
	                //$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_ODE']);
	                $rs[$i]['ERROR_TEXT']= '无权限输入此员工资料';
	                $fail_data[]=$rs[$i];
	                continue;
	            }
	        }	
	        $succ_data[]=$rs[$i];
	    }
	    //pr(array('succ_data'=>$succ_data,'fail_data'=>$fail_data));
	    return array('succ_data'=>$succ_data,'fail_data'=>$fail_data);
	}
	
	/**
	 * Export Error Data to Excel File (暂未用到)
	 * @param number $sid
	 */
	public function exportExcel($sid){
		$export_data=$this->getFailData($sid);
		//设置导出的Excel的相关信息
		$excel_info=array(
			//设置标题
			'title'=>array(
				'A'=>'员工编号',
				'B'=>'开始时间(yyyy-mm-dd hh24:mi)',
				'C'=>'结束时间(yyyy-mm-dd hh24:mi)',
				'D'=>'时数',
				'E'=>'假别代号',
				'Ｆ'=>'备注'
			),
			//设置单元格的格式,默认是TEXT格式
			'format'=>array(
				'B'=>'date',
				'C'=>'date',
				'D'=>'number',
			)
		);
		//删除这条fail_data
		$this->delFailData($sid);
		if($export_data){
			//AresExcel::exportExcel($export_data,$cols);
			AresExcel::exportExcel($export_data,$excel_info);
		}
	}
	public function getDownloadUrl(){
		$url="<a href=".DOCROOT."/docs/emp_leave_apply_import.xls>点击下载</a>(*建议单个文件不超过400条记录)";
		return $url; 
	}
	public function extraEncode($str){
		return base64_encode(json_encode($str));
	}
	public function extraDecode($str){
		return json_decode(base64_decode($str),true);
	}
	
	/**
	 * Save the import failure data to db (暂未用到此功能)
	 * @param array $fail_data
	 * @return void|unknown
	 */
	public function saveFailData($fail_data){
		if(!$fail_data) return ;
		$sql="select EXCEL_FAILDATA_S.nextval from dual";
		$sid=$this->db->GetOne($sql);
		//将fail_data写入数据库
		//$this->db->debug = 1;
		foreach ($fail_data as $row){
			$sql="insert into ehr_upload_data(sid,col1,col2,col3,col4,col5,col6,col7,col8,col9)values(
			".":sid,:col1,:col2,:col3,:col4,:col5,:col6,:col7,:col8,:col9)";
			$this->db->Execute($sql,array(
				'sid'=>$sid,
				'col1'=>$row[0],
				'col2'=>$row[1],
				'col3'=>$row[2],
				'col4'=>$row[3],
				'col5'=>$row[4],
				'col6'=>$row[5],
				'col7'=>$row[6],
				'col8'=>$row[7],
				'col9'=>$row['ERROR_TEXT'],
			));
		}
		return $sid;
	}
	
	
	public function getFailData($sid){
		$sql="select col1,col2,col3,col4,col5,col6,col7,col8,col9 from ehr_upload_data where sid=:sid";
		return $this->db->GetArray($sql,array('sid'=>$sid));
	}
	
	/**
	 * Delete Failure Rows after export
	 * @param number $sid
	 */
	public function delFailData($sid){
		//$sql="delete from excel_faildata where id={$id}";
		$sql="delete from ehr_upload_data where sid={$sid}";
		$ok=$this->db->Execute($sql);
	}
}

/*  controller */
if(empty($_GET['do']))  $_GET['do']='List';
$imp = new ImpAbsApply();
$imp->run();