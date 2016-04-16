<?php
/*
 * modified by TerryWang
 * 2011-6-29
 * Improve Performance by Dennis 2014/05/14
 * 
 */
require_once 'AresExcel.class.php';

class ImpOTApply extends AresAction {
    /**
     * error message store
     * @var array
     */
    private $_errorMsgs;
    
	public function actionList(){
		if($_FILES && !empty($_FILES)){
		    
			$data=$this->getExcelData();
			
			$total_rows = count($data);
			
			// 前台校验数据
			$data = $this->_frontCheckData($data);
			
			$front_succ_data = $data['succ_data'];
			$front_fail_data = $data['fail_data'];
			
			// 后台校验结果
			$backend_fail_data = array();
			$backend_succ_data = array();
			
			if(count($front_succ_data)>0){
			    
			    // 后台保存数据，得到成功，失败（后台）rows
				$result = $this->save2db($front_succ_data);
				$backend_fail_data = $result['fail_data'];
				$backend_succ_data = $result['succ_data'];
				
				// submit run once job 
				$once_job_str = <<<eof
    				declare
    				    v_job varchar2(32);
    				begin
    				    dbms_job.submit(v_job,
    				                    'begin dodecrypt();pkg_concurrent_request.p_overtime_apply_batch(pi_batch_no=>{$result['request_no']}); end;',
                        				sysdate,
                        				null,
                        				false);
    				    commit;
    				end;
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
				$this->tpl->assign('imp_result_msg','您可以在<a href="../ess/redirect.php?scriptname=ESNE023&requestno='.$result['request_no'].'">這裡</a>查看申請處理結果.批次號為:'.$result['request_no']);
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
	
	/**
	 * Export Error Data to Excel File
	 * @param unknown $sid
	 */
	public function exportExcel($sid){
		$export_data=$this->getFailData($sid);
		//设置导出的Excel的相关信息
		$excel_info=array(
			//设置标题
			'title'=>array(
				'A'=>'员工编号',
				'B'=>'计费/补休(A/B/C)',
				'C'=>'平时/假日/法定假日(N/S/H)',
				'D'=>'加班开始时间(yyyy-mm-dd hh24:mi)',
				'E'=>'加班结束时间(yyyy-mm-dd hh24:mi)',
				'F'=>'加班时数',
				'G'=>'加班原因代号',
				'H'=>'备注',
				'I'=>'错误原因'
			),
			//设置单元格的格式,默认是TEXT格式
			'format'=>array(
				'D'=>'date',
				'E'=>'date',
				'F'=>'number',
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
		$url="<a href=".DOCROOT."/docs/emp_overtime_apply_import_v1.xls>点击下载</a>(*建议单个文件不超过400条记录)";
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
			$sql="insert into EHR_UPLOAD_DATA(sid,col1,col2,col3,col4,col5,col6,col7,col8,col9)values(
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
	//获取excel_faildata表中的fail_data
	public function getFailData($sid){
		$sql="select col1,col2,col3,col4,col5,col6,col7,col8,col9 from EHR_UPLOAD_DATA where sid=:sid";
		$fail_data=$this->db->GetArray($sql,array('sid'=>$sid));
		return $this->_array($fail_data);
	}
	//将关联数组转换成数值数组
	//$arr 二维数组
	public function _array($arr){
		$a=array();
		foreach($arr as $key =>$row){
			//foreach($row as $two){
				//$a[$key][]=$two;
			//}
			$a[$key]=array_values($row);
		}
		return $a;
	}
	//删除excel_faildata表中的fail_data
	public function delFailData($sid){
		//$sql="delete from excel_faildata where id={$id}";
		$sql="delete from ehr_upload_data where sid={$sid}";
		$ok=$this->db->Execute($sql);
	}
	public function getExcelData(){
		//H为最大的栏位编号
		$excel=new AresExcel(null,'H');
		/* 已改为 Auto Detect
		//设置为时间的栏目编号和对应的时间格式
		$excel->cols=array(
			4=>'Y-m-d H:i',
			5=>'Y-m-d H:i',
			//7=>'Y-m-d',
		);*/
		return $excel->readExcel();
	}
	
	/*
	 * 将正确的数据插入到数据库中
	 * 将插入失败的数据压入到$fail_data数组中并返回
	 */
	public function save2db($ot_apply_list)
	{
	    set_time_limit(0);
	    $ins_succ_rows = array();
	    $ins_fail_rows = array();
	    //pr($ot_apply_list);
	    //exit;
	    //一些版本的 Microsoft Internet Explorer 只有当接受到的256个字节
	    //以后才开始显示该页面，所以必须发送一些额外的空格来让这些浏览器显示页面内容。
	    //echo str_repeat("  ",256);
	    /* remark by dennis 2014/05/14
	     $sql="select EHR_CONCURRENT_REQUEST_S.nextval REQUEST_NO from dual";
	    $request_no=$this->db->GetOne($sql);
	    $sql="insert into EHR_CONCURRENT_REQUEST (
	            request_no,
	            data_from,
	            request_emp_no,
	            submit_date,
	            status
	    ) values (
	            :request_no,
	            'overtime_apply',
	            :emp_id,
	            sysdate,
	            'N'
	    )";
	    $ok = $this->db->Execute($sql,array('request_no'=>$request_no,'emp_id'=>$_SESSION['user']['emp_id']));
	    */
	
	    $request_no = '';
	    //$this->db->debug = 1;
	    $sql = <<<eof
		  begin
              insert into ehr_concurrent_request
                (request_no, data_from, request_emp_no, submit_date, status)
              values
                (ehr_concurrent_request_s.nextval,
                 'overtime_apply',
                 :emp_id,
                 sysdate,
                 'N')
              returning request_no into :request_no;
            end;
eof;
	    $stmt = $this->db->PrepareSP($sql);
	    $this->db->InParameter($stmt,$this->_userEmpId,'emp_id',100);
	    $this->db->OutParameter($stmt,$request_no,'request_no');
	    $this->db->Execute($stmt);
	
	    $sql = <<<eof
    		insert into ehr_concurrent_overtimeapply
              (request_no,
               user_seqno,
               dep_seqno,
               begin_time,
               end_time,
               ot_hours,
               overtime_reason_id,
               fee_type,
               overtime_type,
               remark,
               emp_seqno,
               company_id)
            values
              (:request_no,
               :user_seqno,
               :dep_seqno,
               to_date(:begin_time, 'yyyy-mm-dd hh24:mi:ss'),
               to_date(:end_time, 'yyyy-mm-dd hh24:mi:ss'),
               :ot_hours,
               :overtime_reason_id,
               :fee_type,
               :overtime_type,
               :remark,
               :emp_seqno,
               :company_id)
eof;
	    foreach($ot_apply_list as $k=>$row){
	        
	        $ok = $this->db->Execute($sql,array(
	                'request_no'=>$request_no,
	                'user_seqno'=>$this->_userSeqno,
	                'dep_seqno'=>$row['dept_id'],
	                'begin_time'=>$row[3],
	                'end_time'=>$row[4],
	                'ot_hours'=>$row[5],
	                'overtime_reason_id'=>$row[6],
	                'fee_type'=>$row[1],
	                'overtime_type'=>$row[2],
	                'remark'=>$row[7],
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
	                $ins_err_msg = '加班资料重复';
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
	public function getLabel($name,$program_no='ESNE007'){
	   
	    if (is_array($this->_errorMsgs) && isset($this->_errorMsgs[$name])){
	        return $this->_errorMsgs[$name];
	    }else{
    	    $sql = <<<eof
    	       select name,value from app_muti_lang 
    	        where program_no = 'ESNE007'
    	         and lang_code = :lang
    	         and type_code = 'IT'
    	         and substr(name,0,6) ='ERROR_'
eof;
    	    //$this->db->debug = 1;
    	    $msgs = $this->db->CacheGetArray(0,$sql,array('lang'=>$_SESSION['user']['language']));
    	    
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
	private function _getOTReasonList()
	{
		$sql = <<<eof
		  SELECT h.overtime_reason_no as ot_reason_no
            FROM hr_overtime_reason h
           WHERE h.seg_segment_no = :company_id
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
	    // clousure function get the emp ids array
	    $emp_ids = $this->_getArrFromArrByKey($rs, $key);
	    //pr($emp_ids);
	    $idx = array_search($val, $emp_ids);
	    if (FALSE !== $idx) return $rs[$idx];
	    return false;
	}
	
	/**
	 * Insert 到 DB 之前前台简单检查数据是否符合
	 * @param array $rs
	 * @return unknown|multitype:multitype:unknown string  multitype:Ambigous <>
	 */
	protected function _frontCheckData($rs=array())
	{
		$succ_data=array();
		$fail_data=array();
		$n= count($rs);
		// 把员工代码组成 array
		$emp_ids = $this->_getArrFromArrByKey($rs, 0);
		// 把员工代码 array 转成以逗号分隔的 string
		$ids_str = implode(",",$emp_ids);
		//echo $ids_str;
		$emp_list = self::_getEmpList($ids_str, $this->_companyId, $this->_userSeqno);
		//pr($emp_list);
		$ot_reason_list = $this->_getOTReasonList();
		//pr($ot_reason_list);
		for($i=0;$i<$n;$i++){
			$rs[$i]['ERROR_CODE']='';
			if(empty($rs[$i][0])){
				$rs[$i]['ERROR_CODE']='ERROR_100';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'工号错误，或者该员工已离职!';
				$fail_data[]=$rs[$i];
				continue;
			}else{
				//员工编号校验 EMP_SEQNO, 提升性能，放在 Loop 之前一次把所有的人事资料挑出来
				/*
				$sql="select id,seg_segment_no_department dept_id,
				        pk_user_priv.f_user_priv(:user_seqno,seg_segment_no,id) has_permission 
				        from hr_personnel_base 
				        where id_no_sz=:id_no_sz and seg_segment_no=:company_id";
				$emp=$this->db->GetRow($sql,array(
					'id_no_sz'=>$rs[$i][0],
					'company_id'=>$companyid,
				    'user_seqno'=>$userseqno
				));
				*/
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
			if(empty($rs[$i][3])){
				$rs[$i]['ERROR_CODE']='ERROR_110';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'加班开始时间(yyyymmdd hh24:mi) 未填!';
				$fail_data[]=$rs[$i];
				continue;
			}
			
			if(empty($rs[$i][4])){
				$rs[$i]['ERROR_CODE']='ERROR_120';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'加班结束时间(yyyymmdd hh24:mi) 未填!';
				$fail_data[]=$rs[$i];
				continue;
			}
			if(empty($rs[$i][5])){
				$rs[$i]['ERROR_CODE']='ERROR_150';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'加班时数未填!';
				$fail_data[]=$rs[$i];
				continue;
			}else if($rs[$i][5]==0){
				$rs[$i]['ERROR_CODE']='ERROR_152';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'加班时数不能为零!';
				$fail_data[]=$rs[$i];
				continue;
			}else{
				try{
					if(floatval($rs[$i][5])==0){
						$rs[$i]['ERROR_CODE']='ERROR_151';
						$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'加班时数，不是数值!';
						$fail_data[]=$rs[$i];
						continue;
					}
				}catch(Exception $e){
					// print $e->getMessage();
					$rs[$i]['ERROR_CODE']='ERROR_151';
					$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'加班时数，不是数值!';
					$fail_data[]=$rs[$i];
					continue;
				}
			}

			if(empty($rs[$i][1])){
				$rs[$i]['ERROR_CODE']='ERROR_160';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'计费/补休/其它(A/B/C) 未填!<BR>';
				$fail_data[]=$rs[$i];
				continue;
			}else{
				if($rs[$i][1]!='A' && $rs[$i][1] != 'B' && $rs[$i][1]!= 'C'){
					// print $e->getMessage();
					$rs[$i]['ERROR_CODE']='ERROR_161';
					$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'计费／补休 不正确(A 计费;B 补休;C 其他;)!<BR>';
					$fail_data[]=$rs[$i];
					continue;
				}
			}
			
			if(empty($rs[$i][2])){
				$rs[$i]['ERROR_CODE']='ERROR_170';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'平时/假日/法定假日(N/S/H) 未填!<BR>';
				$fail_data[]=$rs[$i];
				continue;
			}else{
				if($rs[$i][2]!='H' && $rs[$i][2] != 'N' && $rs[$i][2]!= 'S'){
					// print $e->getMessage();
					$rs[$i]['ERROR_CODE']='ERROR_171';
					$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'平时/假日/法定假日(N/S/H) 不正确!<BR>';
					$fail_data[]=$rs[$i];
					continue;
				}
			}
			//加班原因检验
			/* Follow HCP 不用检查是否有填写,填写的话就去检查是否正确 by dennis 2013/10/31
			if(empty($rs[$i][6])){
				$rs[$i]['ERROR_CODE']='ERROR_180';
				$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'加班原因代号 未填!<BR>';
				$fail_data[]=$rs[$i];
				continue;
			}else*/
			if (!empty($rs[$i][6])){
			    /* 放在 Loop 外面，提升 Performance
				$sql="select hor.overtime_reason_id from hr_overtime_reason hor where 
					hor.seg_segment_no=:seg_segment_no and hor.overtime_reason_no=:overtime_reason_no";
				$reason_id=$this->db->GetOne($sql,array(
					'seg_segment_no'=>$_SESSION['user']['company_id'],
					'overtime_reason_no'=>$rs[$i][6]
				));*/
			    
				if(false === $this->_checkValExistsByKey('OT_REASON_NO', $rs[$i][6], $ot_reason_list)){
					$rs[$i]['ERROR_CODE']='ERROR_181';
					$rs[$i]['ERROR_TEXT']=$this->getLabel($rs[$i]['ERROR_CODE']);//'加班原因代号有误,请检查!<BR>';
					$fail_data[]=$rs[$i];
					continue;
				}
			}
			// add by dennis 2013/10/18
			// check data from hr_concurrent_overtimeapply, had same time or cross period ot row
			// only check success data -- add by dennis 2013/11/20
			/* 去消检查，放在后台去， 这里检查代价太大了，Loop 里面的 DB IO 应该杜绝 by Dennis 2014/05/14 
			$sql = <<<eof
				select count(*)
				  from ehr_concurrent_overtimeapply a, hr_personnel_base b
				 where a.company_id = b.seg_segment_no
				   and a.emp_seqno = b.id
				   and b.id_no_sz = :id_no_sz
				   and ((to_char(a.begin_time, 'yyyy-mm-dd hh24:mi') = :begin_time and
					   to_char(a.end_time, 'yyyy-mm-dd hh24:mi') = :end_time) or
					   (to_date(:begin_time, 'yyyy-mm-dd hh24:mi') between
					   a.begin_time and a.end_time) or
					   (to_date(:end_time, 'yyyy-mm-dd hh24:mi') between
					   a.begin_time and a.end_time))
			       and a.ot_issuccess = 'Y'
eof;
			$cnt = $this->db->GetOne($sql,array('id_no_sz'=>$rs[$i][0],'begin_time'=>$rs[$i][3],'end_time'=>$rs[$i][4]));
			if ($cnt>0){
				$rs[$i]['ERROR_CODE']='ERROR_182';
				$rs[$i]['ERROR_TEXT']= '已导入过此记录或是加班时间和之前记录交叉';//$this->getLabel($rs[$i]['ERROR_CODE']);//'加班原因代号有误,请检查!<BR>';
				$fail_data[]=$rs[$i];
				continue;
			}*/
			// end added
			
			$succ_data[]=$rs[$i];
		}
		return array('succ_data'=>$succ_data,'fail_data'=>$fail_data);
	}
}

/*  controller */
if(empty($_GET['do']))  $_GET['do']='List';
$imp = new ImpOTApply();
$imp->run();