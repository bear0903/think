<?php
/**********************************************************************\
  * (C)  2008 ARES CHINA All Rights Reserved.  http://www.areschina.com
  *
  *  Desc
  *   离职原因调查
  *  Create By: Dennis  Create Date: 2008-12-19 ����01:28:30
  *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresLeaving.class.php $
  *  $Id: AresLeaving.class.php 1278 2009-03-02 06:48:25Z dennis $
  *  $LastChangedDate: 2009-03-02 14:48:25 +0800 (周一, 02 三月 2009) $
  *  $LastChangedBy: dennis $
  *  $LastChangedRevision: 1278 $  
  * 
 \ **********************************************************************/ 

class AresLeaving
{
	
	private $_companyId;
	private $_empSeqNo;
	private $_dBConnection;
	
	public function __construct($companyid,$empseqno)
	{
		global $g_db_sql;
		$this->_dBConnection = &$g_db_sql;
		$this->_companyId = $companyid;
		$this->_empSeqNo = $empseqno;
		//var_dump($g_db_sql);
	}// end __construct()
	
	public function getReasonCatalog()
	{
		$sql = <<<eof
		select a.ques_id,b.cate_id, b.cate_no, b.cate_desc
		  from hr_leave_ques_header a, 
		  	   hr_leave_ques_cate   b
		 where a.seg_segment_no = b.seg_segment_no
		   and a.ques_id = b.ques_id
		   and sysdate >= a.ques_begin_date
		   and sysdate <= a.ques_end_date
		   and a.seg_segment_no = :company_id
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId));
		
	}// end getReasonCatalog()
	
	public function getItemByCatalog($ques_id)
	{
		$sql = <<<eof
			select b.cate_id,
			       b.items_id,
			       b.items_desc,
			       b.type,
			       b.add_ans,
			       b.eva_score_master_id
			  from hr_leave_ques_cate a, hr_leave_ques_items b
			 where a.seg_segment_no = b.seg_segment_no
			   and a.cate_id = b.cate_id
			   and b.ques_id = :ques_id
			 order by b.seqno
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('ques_id'=>$ques_id));
	}// end getItemByCatalog()
	
	/**
	 *  Add 离职原因调查答案
	 *
	 * @param number $ques_seqno      问卷代码
	 * @param string $leaving_date    预计离职日期
	 * @param string $after_con_addr  离职后联系地址
	 * @param string $after_con_tel   离职后联系电话
	 * @param string $leaving_type    离职种类
	 * @param string $is_other_type   是否其它类型
	 * @param string $other_type_desc 其它类型描述
	 * @param string $emp_sugestion   建议
	 * @param string $form_status     表单状态 0_暂存 1_生效
	 * @param array  $data			     原因及说明
	 * @return void
	 * @author Dennis 2008-12-22 
	 */
	public function insert($ques_seqno,
						   $leaving_date,
						   $after_con_addr,
						   $after_con_tel,
						   $leaving_type,
						   $other_type_desc,
						   $emp_suggestion,
						   $form_status,
						   array $data) {
		$sql = <<<eof
			insert into hr_leaving_reason_master
			  (seqno,
			   seg_segment_no,
			   psn_id,
			   leaving_date,
			   after_con_addr,
			   after_con_tel,
			   leaving_type,
			   other_type_desc,
			   emp_suggestion,
			   create_date,
			   create_by,
			   form_status,
			   questionnaire_seqno)
			values
			  (hr_leaving_reason_s.nextVal,
			   :v_seg_segment_no,
			   :v_psn_id,
			   :v_leaving_date,
			   :v_after_con_addr,
			   :v_after_con_tel,
			   :v_leaving_type,
			   :v_other_type_desc,
			   :v_emp_sug,
			   sysdate,
			   :v_create_by,
			   :v_form_status,
			   :v_ques_seqno)
eof;
		//$this->_dBConnection->debug = true;
		$ok = $this->_dBConnection->Execute($sql,array('v_seg_segment_no'=>$this->_companyId,
													   'v_psn_id'=>$this->_empSeqNo,
													   'v_leaving_date'=>$leaving_date,
													   'v_after_con_addr'=>$after_con_addr,
													   'v_after_con_tel'=>$after_con_tel,
													   'v_leaving_type'=>$leaving_type,
													   'v_other_type_desc'=>$other_type_desc,
													   'v_emp_sug'=>$emp_suggestion,
													   'v_create_by'=>$this->_empSeqNo,
													   'v_ques_seqno'=>$ques_seqno,
													   'v_form_status'=>$form_status));
		if($ok){
			$this->_insertDetail($data);
		}// end if
	}// end insert()
	
	private function _insertDetail(array $data)
	{
		$sql = <<<eof
		insert into hr_leaving_reason_detail
		  (reason_master_seqno,
		   ques_seqno,
		   seg_segment_no,
		   psn_id,
		   cate_seqno,
		   item_seqno,
		   remarks)
		values
		  (hr_leaving_reason_s.currVal,
		   :v_ques_seqno,
		   :v_seg_segment_no,
		   :v_psn_id,
		   :v_cate_seqno,
		   :v_item_seqno,
		   :v_remarks)
eof;
		for ($i=0; $i<count($data); $i++)
		{
			$this->_dBConnection->Execute($sql,array('v_ques_seqno'=>$data[$i]['ques_seqno'], 
													 'v_seg_segment_no'=>$this->_companyId, 
													 'v_psn_id'=>$this->_empSeqNo,
													 'v_cate_seqno'=>$data[$i]['cate_seqno'], 
													 'v_item_seqno'=>$data[$i]['item_seqno'], 
													 'v_remarks'=>$data[$i]['remark']));
		}// end for loop
	}// end insertDetail()
	
	private function _insertDetail1($master_seqno,array $data)
	{
		for ($i=0; $i<count($data); $i++)
		{
			$this->_dBConnection->Replace('hr_leaving_reason_detail',
										  array('REASON_MASTER_SEQNO'=>$master_seqno,
										  		'ques_seqno'=>$data[$i]['ques_seqno'],
										  		'seg_segment_no'=>"'".$this->_companyId."'",
										  		'psn_id'=>$this->_empSeqNo,
										  		'cate_seqno'=>$data[$i]['cate_seqno'],
										  		'item_seqno'=>$data[$i]['item_seqno'],
										  		'remarks'=>"'".$data[$i]['remark']."'"),
										  array('seg_segment_no','psn_id','cate_seqno','item_seqno'));
		}// end for loop
	}// end insertDetail()
	
	public function update($master_seqno,
						   $leaving_date,
						   $after_con_addr,
						   $after_con_tel,
						   $leaving_type,
						   $other_type_desc,
						   $emp_suggestion,
						   $form_status,
						   array $data)
	{
		$sql = <<<eof
		update hr_leaving_reason_master
		   set leaving_date        = :v_leaving_date,
		       after_con_addr      = :v_after_con_addr,
		       after_con_tel       = :v_after_con_tel,
		       leaving_type        = :v_leaving_type,
		       other_type_desc     = :v_other_type_desc,
		       emp_suggestion      = :v_emp_suggestion,
		       create_date         = sysdate,
		       create_by           = sysdate,
		       form_status         = :v_form_status
		 where seqno = :v_seqno
eof;
		//$this->_dBConnection->debug = true;
		$ok = $this->_dBConnection->Execute($sql,array('v_leaving_date'=>$leaving_date,
													   'v_after_con_addr'=>$after_con_addr,
													   'v_after_con_tel'=>$after_con_tel,
													   'v_leaving_type'=>$leaving_type,
													   'v_other_type_desc'=>$other_type_desc,
													   'v_emp_suggestion'=>$emp_suggestion,
													   'v_seqno'=>$master_seqno,
													   'v_form_status'=>$form_status));
		if($ok)
		{
			$this->_insertDetail1($master_seqno,$data);
		}// end if
	}// end update()
	
	public function delete($master_seqno)
	{
		$sql = <<<eof
			delete from hr_leaving_reason_detail where REASON_MASTER_SEQNO = :master_seqno
eof;
		$sqla = <<<eof
			delete from hr_leaving_reason_master where seqno = :master_seqno1
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->Execute($sql,array('master_seqno'=>$master_seqno));
		$this->_dBConnection->Execute($sqla,array('master_seqno1'=>$master_seqno));
	}// end delete()
	
	/**
	 * 取得离职原因主档资料
	 * 一个员工原则上只有一个离职问卷，防止离职人员重新聘用，使用原来的工号.
	 * 这里先挑当前员工最大的离职问卷单号
	 *
	 * @return unknown
	 */
	public function getLeavingMaster() {
		$max_seqno = $this->_dBConnection->GetOne('select max(seqno) from hr_leaving_reason_master where seg_segment_no = :company_id and psn_id = :emp_seqno',array('company_id'=>$this->_companyId,'emp_seqno'=>$this->_empSeqNo));
		if ($max_seqno >0)
		{
			$sql = <<<eof
			select seqno,
			       leaving_date,
			       after_con_addr,
			       after_con_tel,
			       leaving_type,
			       other_type_desc,
			       emp_suggestion,
			       create_date,
			       create_by,
			       form_status,
			       questionnaire_seqno
			  from hr_leaving_reason_master
			 where seg_segment_no = :company_id
			   and psn_id = :emp_seqno
eof;
			$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
			return $this->_dBConnection->GetRow($sql,array('company_id'=>$this->_companyId,'emp_seqno'=>$this->_empSeqNo));
		}// end if
		return false;
	}// end getLeavingMaster();
	
	public function getLeavingDetail($master_seqno)
	{
		$sql = <<<eof
		 select reason_master_seqno, 
		        ques_seqno,
		        cate_seqno, 
		        item_seqno, 
		        remarks
		  from hr_leaving_reason_detail
		 where seg_segment_no = :company_id
		   and psn_id = :emp_seqno
		   and reason_master_seqno = :master_seqno
eof;
		//$this->_dBConnection->debug = true;
		$this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
														 'emp_seqno'=>$this->_empSeqNo,
														 'master_seqno'=>$master_seqno));
	}// end GetLeavingDetail()
	
	/**
	 * 是否已經提交過調查問卷
	 *
	 * @return boolean
	 */
	public function isSubmitted()
	{
		$sql = <<<eof
		select 1
		  from hr_leaving_reason_master
		 where seg_segment_no = :company_id
		   and psn_id = :emp_seqno
		   and form_status = '1'
eof;
		return $this->_dBConnection->GetOne($sql,array('company_id'=>$this->_companyId,
													   'emp_seqno'=>$this->_empSeqNo));
		
	}// end isSubmitted()
	
}// end class AresLeaving()