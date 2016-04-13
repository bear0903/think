<?php
/*
 * 成批申請，成批签核　，异步处理
 * 多语注册名为：MDN0000
 *  create by boll 2009-9-7
 *  last Changed by Dennis 2012-01-05
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresConcurrentRequest.class.php $
 *  $Id: AresConcurrentRequest.class.php 3804 2014-08-05 09:43:48Z dennis $
 *  $Rev: 3804 $ 
 *  $Date: 2014-08-05 17:43:48 +0800 (周二, 05 八月 2014) $
 *  $LastChanged: Dennis $   
 *  $LastChangedDate: 2014-08-05 17:43:48 +0800 (周二, 05 八月 2014) $
 \****************************************************************************/

class AresConcurrentRequest {
	private $_dbConn;	
		
	public function __construct(){
		global $g_db_sql;
		$this->_dbConn = $g_db_sql;
	}
	
	
	/**
	 * Insert Batch Request Master Table
	 * @param number $req_no
	 * @param string $batch_type
	 */
	private function _addBatchReq($req_no,$batch_type = 'overtime_approve')
	{
		$sql = <<<eof
			insert into ehr_concurrent_request
			  (request_no, data_from, request_emp_no, submit_date, status)
			values
			  (:req_no,
			   :batch_type,
			   :emp_id,
			   sysdate,
			   'N')
eof;
		$ok = $this->_dbConn->Execute($sql,array('req_no'=>$req_no,
		                                         'batch_type'=>$batch_type,
												 'emp_id'=>$_SESSION['user']['emp_id']));
		if (!$ok){
			print $this->_dbConn->ErrorMsg();
			exit;
		}
	}
	
	/**
	 * Get req sequence number
	 */
	private function _getReqSeqNo()
	{
		return $this->_dbConn->GetOne('select ehr_concurrent_request_s.nextval request_no from dual');
	}
	
	/**
	 * Batch Approve Overtime Apply
	 * @param array $list
	 */
	public function saveRequestOvertimeApprove($list){
	    //pr($list);
		$request_no = $this->_getReqSeqNo();
		$this->_addBatchReq($request_no);
        // insert detail
		$n=count($list['workflow_seqno']);
		for ($i=0; $i<$n; $i++)
        {
            $_workflow_info['company_id']          = $list['company_id'][$i];
            $_workflow_info['workflow_seqno']      = $list['workflow_seqno'][$i];
            $_workflow_info['apply_type']          = $list['apply_type'][$i];
            $_workflow_info['approver_emp_seqno']  = $list['approver_emp_seqno'][$i];
            $_workflow_info['approver_user_seqno'] = $list['userseqno']; // 因为有从邮件签核,所以有把 userseqno放到画面上
            $_workflow_info['approve_seqno']       = $list['approve_seqno'][$i];
            $_workflow_info['is_approved']         = $list['approve_action'.$_POST['approve_seqno'][$i]];
			$_workflow_info['reject_reason']	   = $_workflow_info['is_approved'] == 'N' ?  
                                                     (is_array($list['reject_reason']) &&
                                                      isset($list['reject_reason'][$i]) &&
                                                      !empty($list['reject_reason'][$i]) ?
                                                        $list['reject_reason'][$i]:
                                                        (isset($list['all_reject_reason'])? $list['all_reject_reason'] : null)): 
                                                        null;                                                                                             
            if (!empty($_workflow_info['is_approved']))
            {
            	// change to replace method for improve the performance by dennis 2013/09/24
            	$sql= <<<eof
	            	select count(1) cnt
					  from ehr_concurrent_request_detail
					 where approver_emp_seqno = :appr_emp_seqno
					   and workflow_seqno = :wf_seqno
					   and po_success is null
eof;
            	$v_count = $this->_dbConn->GetOne($sql,array('appr_emp_seqno'=>$_workflow_info['approver_emp_seqno'],
            												 'wf_seqno'=>$_workflow_info['workflow_seqno']));
            	if($v_count>0) continue;  ///有重复提交的不处理
            	
            	$sql = <<<eof
            		insert into ehr_concurrent_request_detail
					  (request_no,
					   approve_seqno,
					   company_id,
					   workflow_seqno,
					   apply_type,
					   approver_emp_seqno,
					   approver_user_seqno,
					   is_approved,
					   reject_reason)
					values
					  (:req_no,
					   :approve_seqno,
					   :company_id,
					   :wf_seqno,
					   :apply_type,
					   :appr_emp_seqno,
					   :appr_user_seqno,
					   :is_approve,
					   :rejcet_reason)
eof;
				$ok = $this->_dbConn->Execute($sql,array('req_no'=>$request_no,
	            									'approve_seqno'=>$_workflow_info['approve_seqno'],
	            									'company_id'=>$_workflow_info['company_id'],
	            									'wf_seqno'=>$_workflow_info['workflow_seqno'],
	            									'apply_type'=>$_workflow_info['apply_type'],
	            									'appr_emp_seqno'=>$_workflow_info['approver_emp_seqno'],
	            									'appr_user_seqno'=>$_workflow_info['approver_user_seqno'],
	            									'is_approve'=>$_workflow_info['is_approved'],
	            									'rejcet_reason'=>$_workflow_info['reject_reason']));
				if (!$ok){
					print $this->_dbConn->ErrorMsg();
					exit;
				}
				/* 查出来 Bug， 签核之后还在本人这边 ？？？ by Dennis 2013/11/03 @home
            	//$this->_dbConn->debug = 1;
            	$r = $this->_dbConn->Replace('ehr_concurrent_request_detail',
            			 array('request_no'=>$request_no,
							   'approve_seqno'=>$_workflow_info['approve_seqno'],
							   'company_id'=>$_workflow_info['company_id'],
							   'workflow_seqno'=>$_workflow_info['workflow_seqno'],
							   'apply_type'=>$_workflow_info['apply_type'],
							   'approver_emp_seqno'=>$_workflow_info['approver_emp_seqno'],
            			 	   'approver_user_seqno'=>$_workflow_info['approver_user_seqno'],
            				   'is_approved'=>$_workflow_info['is_approved'],
            				   'reject_reason'=>$_workflow_info['reject_reason']),
            			array('approver_emp_seqno','workflow_seqno','po_success'),
            			true
				);
            
            	if ($r == 0){
            		echo $this->_dbConn->ErrorMsg();
            		exit;
            	}*/
            }
        }
        // last modify by denni 2012-01-05 change v_job to v_job_no binary_integer
        $sql="declare v_job_no binary_integer; begin dbms_job.submit(v_job_no,'begin pkg_concurrent_request.p_overtime_approve_batch(pi_batch_no=>".$request_no."); end;',sysdate,null,false);commit;end;";
		$ok = $this->_dbConn->Execute($sql);
		if (!$ok){
			print $this->_dbConn->ErrorMsg();
			exit;
		}
		showMsg('您的簽核，已成功提交到後台處理.<br>您可以点<a href="../ess/redirect.php?scriptname=ESNH100&requestno='.$request_no.'">這裡</a>查看簽核結果。<br>批次號:'.$request_no,'success' );
	}
	
	/**
	 * Batch Approve Overtime Apply
	 * @param array $list
	 */
	public function leaveBatchApprove($list)
	{
	    //pr($list);
	    $request_no = $this->_getReqSeqNo();
	    $this->_addBatchReq($request_no,'leave_approve');
	    // insert detail
	    $n = count($list['workflow_seqno']);
	    for ($i=0; $i<$n; $i++)
	    {
	        $_workflow_info['company_id']          = $list['company_id'][$i];
	        $_workflow_info['workflow_seqno']      = $list['workflow_seqno'][$i];
	        $_workflow_info['apply_type']          = $list['apply_type'][$i];
	        $_workflow_info['approver_emp_seqno']  = $list['approver_emp_seqno'][$i];
	        $_workflow_info['approver_user_seqno'] = $list['userseqno']; // 因为有从邮件签核,所以有把 userseqno放到画面上
	        $_workflow_info['approve_seqno']       = $list['approve_seqno'][$i];
	        $_workflow_info['is_approved']         = $list['approve_action'.$_POST['approve_seqno'][$i]];
			$_workflow_info['reject_reason']	   = $_workflow_info['is_approved'] == 'N' ?
				                                    (is_array($list['reject_reason']) &&
                                				     isset($list['reject_reason'][$i]) &&
                                				     !empty($list['reject_reason'][$i]) ?
				                                     $list['reject_reason'][$i]:
				                                     (isset($list['all_reject_reason'])? 
				                                      $list['all_reject_reason'] : null)):null;
	        if (!empty($_workflow_info['is_approved']))
	        {
    	        // change to replace method for improve the performance by dennis 2013/09/24
    	        $sql= <<<eof
        	        select count(1) cnt
        	         from ehr_concurrent_request_detail
        	        where approver_emp_seqno = :appr_emp_seqno
        	          and workflow_seqno = :wf_seqno
        	          and po_success is null
eof;
                $v_count = $this->_dbConn->GetOne($sql,array('appr_emp_seqno'=>$_workflow_info['approver_emp_seqno'],
            												 'wf_seqno'=>$_workflow_info['workflow_seqno']));
            	if($v_count>0) continue;  ///有重复提交的不处理
       
            	$sql = <<<eof
            		insert into ehr_concurrent_request_detail
					  (request_no,
					   approve_seqno,
					   company_id,
					   workflow_seqno,
					   apply_type,
					   approver_emp_seqno,
					   approver_user_seqno,
					   is_approved,
					   reject_reason)
				    values(:req_no,
					   :approve_seqno,
					   :company_id,
					   :wf_seqno,
					   :apply_type,
					   :appr_emp_seqno,
					   :appr_user_seqno,
					   :is_approve,
					   :rejcet_reason)
eof;
				$ok = $this->_dbConn->Execute($sql,array('req_no'=>$request_no,
	            									'approve_seqno'=>$_workflow_info['approve_seqno'],
	            									'company_id'=>$_workflow_info['company_id'],
	            									'wf_seqno'=>$_workflow_info['workflow_seqno'],
	            									'apply_type'=>$_workflow_info['apply_type'],
	            									'appr_emp_seqno'=>$_workflow_info['approver_emp_seqno'],
	            									'appr_user_seqno'=>$_workflow_info['approver_user_seqno'],
	            									'is_approve'=>$_workflow_info['is_approved'],
	            									'rejcet_reason'=>$_workflow_info['reject_reason']));
				if (!$ok){
					print $this->_dbConn->ErrorMsg();
					exit;
				}
				/* 查出来 Bug， 签核之后还在本人这边 ？？？ by Dennis 2013/11/03 @home
            	//$this->_dbConn->debug = 1;
            	$r = $this->_dbConn->Replace('ehr_concurrent_request_detail',
            			 array('request_no'=>$request_no,
							   'approve_seqno'=>$_workflow_info['approve_seqno'],
							   'company_id'=>$_workflow_info['company_id'],
							   'workflow_seqno'=>$_workflow_info['workflow_seqno'],
							   'apply_type'=>$_workflow_info['apply_type'],
							   'approver_emp_seqno'=>$_workflow_info['approver_emp_seqno'],
            			 	   'approver_user_seqno'=>$_workflow_info['approver_user_seqno'],
            				   'is_approved'=>$_workflow_info['is_approved'],
            				   'reject_reason'=>$_workflow_info['reject_reason']),
            			array('approver_emp_seqno','workflow_seqno','po_success'),
            			true
				);
	
            	if ($r == 0){
            		echo $this->_dbConn->ErrorMsg();
            		exit;
            	}*/
            }
        }
        // last modify by dennis 2012-01-05 change v_job to v_job_no binary_integer
        $sql="declare v_job_no binary_integer; begin dbms_job.submit(v_job_no,'begin pkg_concurrent_request.p_leave_approve_batch(pi_batch_no=>".$request_no."); end;',sysdate,null,false);commit;end;";
		$ok = $this->_dbConn->Execute($sql);
		if (!$ok){
			print $this->_dbConn->ErrorMsg();
			exit;
		}
		showMsg('您的簽核，已成功提交到後台處理.<br>您可以点<a href="../ess/redirect.php?scriptname=ESNH101&requestno='.$request_no.'">這裡</a>查看簽核結果。<br>批次號:'.$request_no,'success' );
	}
	
	/**
	 * 保存批量加班申请的并发请求
	 * @param string $userseqno
	 * @param date   $ot_begin_time
	 * @param date   $ot_end_time
	 * @param number $ot_hours
	 * @param string $ot_reason
	 * @param string $ot_fee_type
	 * @param string $ot_type
	 * @param string $remark
	 * @param string $tmp_save
	 * @param array  $emplist
	 * @param array  $deptlist
	 * @param string $company_id
	 */
	public function saveRequestOvertimeApply(
								$userseqno,
							    $ot_begin_time, 
							    $ot_end_time, 
							    $ot_hours, 
							    $ot_reason, 
							    $ot_fee_type, 
							    $ot_type, 
							    $remark, 
							    $tmp_save,
							    array $emplist,
							    array $deptlist,
							    $company_id){
		$request_no = $this->_getReqSeqNo();
		$this->_addBatchReq($request_no,'overtime_apply');
        // insert detail
		$n=count($emplist);
		for ($i=0; $i<$n; $i++)
		{
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
				  (:req_no,
				   :user_seqno,
				   :dept_id,
				   to_date(:begin_time,'yyyy-mm-dd hh24:mi'),
				   to_date(:end_time,'yyyy-mm-dd hh24:mi'),
				   :ot_hours,
				   :ot_reason,
				   :ot_fee_type,
				   :ot_type,
				   :remark,
				   :emp_seqno,
				   :companyid)
eof;
            $ok = $this->_dbConn->Execute($sql,array('req_no'=>$request_no,
											   'user_seqno'=>$userseqno,
            								   'dept_id'=>$deptlist[$i],
											   'begin_time'=>$ot_begin_time,
											   'end_time'=>$ot_end_time,
							            	   'ot_hours'=>$ot_hours,
											   'ot_reason'=>$ot_reason,
											   'ot_fee_type'=>$ot_fee_type,
							            	   'ot_type'=>$ot_type,
											   'remark'=>$remark,
											   'emp_seqno'=>$emplist[$i],
											   'companyid'=>$company_id));
			if (!$ok){
				print $this->_dbConn->ErrorMsg();
				exit;
			}
		}// end for loop

        $sql="declare v_job_no binary_integer; begin dbms_job.submit(v_job_no,'begin pkg_concurrent_request.p_overtime_apply_batch(pi_batch_no=>".$request_no."); end;',sysdate,null,false); commit; end;";
		//$this->_dbConn->debug = 1;
        $ok = $this->_dbConn->Execute($sql);
		if (!$ok){
			print $this->_dbConn->ErrorMsg();
			exit;
		}
		showMsg('您的加班申請已成功提交後台處理.<br>您可以在<a href="../ess/redirect.php?scriptname=ESNE023&requestno='.$request_no.'">這裡</a>查看申請處理結果.<br>批次號為:'.$request_no,'success' );
	}
	
	public function leaveBatchApply($userseqno,
                        	        $abs_begin_time,
                        	        $abs_end_time,
                        	        $abs_hours,
                        	        $abs_reason,
                        	        $remark,
                        	        $tmp_save,
                        	        array $emplist,
                        	        array $deptlist,
                        	        $company_id){
	    $request_no = $this->_getReqSeqNo();
	    $this->_addBatchReq($request_no,'leave_apply');
	    // insert detail
	    //$this->_dbConn->debug = 1;
	    $n=count($emplist);
	    for ($i=0; $i<$n; $i++)
	    {
	        $sql = <<<eof
	        insert into ehr_concurrent_leave_apply
                  (request_no,
                   user_seqno,
                   dep_seqno,
                   begin_time,
                   end_time,
                   abs_hours,
                   abs_type_id,
                   remark,
                   emp_seqno,
                   company_id)
                values
                  (:request_no,
                   :user_seqno,
                   :dep_seqno,
                   to_date(:begin_time,'yyyy-mm-dd hh24:mi'),
                   to_date(:end_time,'yyyy-mm-dd hh24:mi'),
                   :abs_hours,
                   :abs_type_id,
                   :remark,
                   :emp_seqno,
                   :company_id)
eof;
	        $ok = $this->_dbConn->Execute($sql,array('request_no'=>$request_no,
	                'user_seqno'=>$userseqno,
	                'dep_seqno'=>$deptlist[$i],
	                'begin_time'=>$abs_begin_time,
	                'end_time'=>$abs_end_time,
	                'abs_hours'=>$abs_hours,
	                'abs_type_id'=>$abs_reason,
	                'remark'=>$remark,
	                'emp_seqno'=>$emplist[$i],
	                'company_id'=>$company_id));
	        if (!$ok){
	            print $this->_dbConn->ErrorMsg();
	            exit;
	        }
	    }// end for loop
	
	    $sql="declare v_job_no binary_integer; begin dbms_job.submit(v_job_no,'begin pkg_concurrent_request.p_leave_apply_batch(pi_batch_no=>".$request_no."); end;',sysdate,null,false); commit; end;";
	    
	    $ok = $this->_dbConn->Execute($sql);
	    if (!$ok){
	        print $this->_dbConn->ErrorMsg();
	        exit;
	    }
	    showMsg('您的加班申請已成功提交後台處理.<br>您可以在<a href="../ess/redirect.php?scriptname=ESNE023&requestno='.$request_no.'">這裡</a>查看申請處理結果.<br>批次號為:'.$request_no,'success' );
	}
	
	/**
	 * Get Batch Processing Result
	 * 
	 * @param string $where
	 */
	public function getBatchProcessResult($where,$batch_type='overtime_apply'){
	    $where .= " and data_from = '".$batch_type."'";
	    $sql = <<<eof
	       select * from (
	           select a.* from ehr_batch_run_result_v a $where
	           order by request_no desc
	       ) where rownum < 101
eof;
	    //$this->_dbConn->debug = 1;
	    return $this->_dbConn->GetArray($sql);
	}
	
	
	/**
	 * Get Batch Request Process result
	 * @param number $requestno
	 * @author Dennis 2014/06/23
	 */
	public function getOTBatchRunDetailbyBatchSeq($requestno){
	    $sql = <<<eof
            select b.ot_msg as ot_msg,
                   b.request_no as request_no,
	               b.ot_issuccess,
                   decode(b.ot_issuccess,'Y','成功','N','失败','运行中') as is_success,
                   c.segment_no_sz as dept_id,
                   c.segment_name as dept_name,
                   a.id_no_sz as emp_id,
                   a.name_sz as emp_name,
                   decode(b.fee_type, 'A', '计费', 'B', '补休', 'C', '其它') as ot_fee_type,
                   decode(b.overtime_type, 'N', '平时', 'S', '例假日', 'H', '国假') as ot_type,
                   to_char(b.begin_time, 'yyyy-mm-dd hh24:mi') as begin_time,
                   to_char(b.end_time, 'yyyy-mm-dd hh24:mi') as end_time,
                   b.ot_hours as ot_hours
              from hr_personnel_base a, gl_segment c, ehr_concurrent_overtimeapply b
             where a.seg_segment_no = b.company_id
               and to_char(a.id) = to_char(b.emp_seqno)
               and b.company_id = c.seg_segment_no
               and to_char(b.dep_seqno) = to_char(c.segment_no)
	           and b.request_no = :requestno
	    order by b.ot_issuccess
eof;
	    //$this->_dbConn->debug = 1;
	    return $this->_dbConn->GetArray($sql,array('requestno'=>$requestno));
	}
	
	public function getLeaveBatchRunDetailbyBatchSeq($requestno){
	    $sql = <<<eof
            select b.abs_msg as abs_msg,
                   b.request_no as request_no,
	               b.abs_issuccess,
                   decode(b.abs_issuccess,'Y','成功','N','失败','运行中') as is_success,
                   c.segment_no_sz as dept_id,
                   c.segment_name as dept_name,
                   a.id_no_sz as emp_id,
                   a.name_sz as emp_name,
	               d.absence_name as leave_name,
                   to_char(b.begin_time, 'yyyy-mm-dd hh24:mi') as begin_time,
                   to_char(b.end_time, 'yyyy-mm-dd hh24:mi') as end_time,
	               b.remark as leave_reason
	          from hr_personnel_base a, gl_segment c, ehr_concurrent_leave_apply b,hr_absence_type d
            where a.seg_segment_no = b.company_id
               and to_char(a.id) = to_char(b.emp_seqno)
               and b.company_id = c.seg_segment_no
               and to_char(b.dep_seqno) = to_char(c.segment_no)
               and b.company_id = d.seg_segment_no
               and b.abs_type_id = d.absence_code
	           and b.request_no = :requestno
			order by b.abs_issuccess
eof;
	    //$this->_dbConn->debug = 1;
	    return $this->_dbConn->GetArray($sql,array('requestno'=>$requestno));
	}
	
	public function getLeaveBatchApproveDetailBySeq($requestno,$mgr_empseqno)
	{
		$sql = <<<eof
			select c.request_no,
				   c.po_errmsg,
				   c.po_success,
				   c.is_approved,
				   c.reject_reason,
				   b.segment_name as dept_name,
				   a.id_no_sz as emp_id,
				   a.name_sz as emp_name,
				   e.absence_name as leave_name,
				   to_char(d.begintime, 'yyyy/mm/dd hh24:mi') as begin_time,
				   to_char(d.endtime, 'yyyy/mm/dd hh24:mi') as end_time,
				   (select value
					  from app_muti_lang x
					 where program_no = 'ESNA013'
					   and x.name = 'WORK_FLOW_STAUS'
					   and x.lang_code = 'ZHT'
					   and x.seq = d.status) as status_name
			  from hr_personnel_base             a,
				   gl_segment                    b,
				   ehr_concurrent_request_detail c,
				   hr_absence_flow_sz            d,
				   hr_absence_type               e
			 where a.seg_segment_no = b.seg_segment_no
			   and a.seg_segment_no_department = b.segment_no
			   and a.seg_segment_no = d.seg_segment_no
			   and a.id = d.psn_id
			   and c.company_id = d.seg_segment_no
			   and c.workflow_seqno = d.absence_flow_sz_id
			   and d.seg_segment_no = e.seg_segment_no
			   and d.reason = e.absence_type_id
			   and c.request_no = :request_no
			   and c.approver_emp_seqno = :approver_emp_seqno
eof;
		return $this->_dbConn->GetArray($sql,array('request_no'=>$requestno,'approver_emp_seqno'=>$mgr_empseqno));
	}
	
}
