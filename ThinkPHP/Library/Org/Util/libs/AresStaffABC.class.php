<?php

class StaffABC
{
    private $_db;
    private $_companyId;
    const   APPNAME = 'StaffABC';
    
    function __construct($companyid)
    {
        global $g_db_sql;
        $this->_db = $g_db_sql;
        $this->_companyId= $companyid;
    }// end constructor 
    
    function getType()
    {
        $sql = <<<eof
            select staffabc_master_id as type_seqno,
                   staffabc_master_no || '-' || staffabc_master_desc as abc_type_desc
              from hr_staffabc_master
             where is_active = 'Y'
               and seg_segment_no = :company_id
eof;
        $this->_db->SetFetchMode(ADODB_FETCH_NUM);
        //$this->_db->debug = true;
        return $this->_db->GetArray($sql,array('company_id'=>$this->_companyId));
    }// end getType()
    
    function getSubType($master_seqno)
    {
        $sql = <<<eof
            select staffabc_detail_id as sub_type_seqno,
                   staffabc_detail_no || '-' || staffabc_detail_desc as sub_type_desc
              from hr_staffabc_detail
             where staffabc_master_id = :master_seqno
               and seg_segment_no = :company_id
eof;
        $this->_db->SetFetchMode(ADODB_FETCH_NUM);
        //$this->_db->debug = true;
        return json_encode ($this->_db->GetArray($sql,array('company_id'=>$this->_companyId,
        								       				'master_seqno'=>$master_seqno)));
    }// end getSubType()
    
    function insert($staffabc_master_id,
                    $staffabc_detail_id,
                    $emp_seqno,
                    $point,
                    $comments,
                    $create_date,
                    $create_by)
    {
        $sql = <<<eof
       		insert into hr_employee_staffabc
              (employee_staffabc_id,
               staffabc_detail_id,
               staffabc_master_id,
               seg_segment_no,
               psn_id,
               score,
               comment_sz,
               is_active,
               create_by,
               create_date,
               create_program)
            values
              (hr_staffabc_s.nextval,
               :v_staffabc_detail_id,
               :v_staffabc_master_id,
               :v_seg_segment_no,
               :v_psn_id,
               :v_score,
               :v_comment_sz,
               'Y',
               :v_create_by,
               :v_create_date,
               :v_create_program)
eof;
        //$this->_db->debug = true;
        $r = $this->_db->Execute($sql,array('v_staffabc_master_id'=>$staffabc_master_id,
            							   'v_staffabc_detail_id'=>$staffabc_detail_id,
            							   'v_seg_segment_no'=>$this->_companyId,
            							   'v_psn_id'=>$emp_seqno,
            							   'v_comment_sz'=>$comments,
            							   'v_create_by'=>$create_by,
            							   'v_score'=>$point,
            							   'v_create_date'=>$create_date,
            							   'v_create_program'=>self::APPNAME));
        if(!$r)
        {
            return $this->_db->ErrorMsg();  
        }else{
            return 1;
        }// end if;
    }// end insert()
    
    function update()
    {
        
    }// end update()
    
    function delete()
    {
        
    }// end delete()
    
    function listRec($emp_seqno,$staffabc_master_id = null)
    {
        $all_cond = is_null($staffabc_master_id) ? '' : "and c.staffabc_master_id = $staffabc_master_id";
        $sql = <<<eof
        select c.create_date,
               a.staffabc_master_desc,
               b.staffabc_detail_desc,
               c.score,
               c.comment_sz
          from hr_staffabc_master a, hr_staffabc_detail b, hr_employee_staffabc c
         where a.seg_segment_no = b.seg_segment_no
           and a.staffabc_master_id = b.staffabc_master_id
           and b.seg_segment_no = c.seg_segment_no
           and b.staffabc_master_id = c.staffabc_master_id
           and b.staffabc_detail_id = c.staffabc_detail_id
           and c.seg_segment_no = :company_id
           and c.psn_id = :emp_seqno
           %s
           and c.create_date between sysdate-365 and sysdate
         order by c.create_date
eof;
        $sql = sprintf($sql,$all_cond);
        return $this->_db->GetArray($sql,array('company_id'=>$this->_companyId,
        									   'emp_seqno'=>$emp_seqno,
        									   'master_id'=>$staffabc_master_id));
    }// end listRec()
    
    function sttsByYM($emp_seqno)
    {
        $sql = <<<eof
        select to_char(c.create_date, 'yyyymm') as yymm,
        	   a.staffabc_master_id,
               a.staffabc_master_desc,
               sum(c.score) as score
          from hr_staffabc_master a, 
               hr_staffabc_detail b, 
               hr_employee_staffabc c
         where a.seg_segment_no = b.seg_segment_no
           and a.staffabc_master_id = b.staffabc_master_id
           and b.seg_segment_no = c.seg_segment_no
           and b.staffabc_master_id = c.staffabc_master_id
           and b.staffabc_detail_id = c.staffabc_detail_id
           and c.seg_segment_no = :company_id
           and c.psn_id = :emp_seqno
           and c.create_date between sysdate-365 and sysdate
         group by to_char(c.create_date, 'yyyymm'),a.staffabc_master_id, a.staffabc_master_desc
eof;
        //$this->_db->debug = true;
        return $this->_db->GetArray($sql,array('company_id'=>$this->_companyId,
        									   'emp_seqno'=>$emp_seqno));
    }// end sttsByYM()
    
    function sttsByABC($emp_seqno)
    {
        $sql = <<<eof
        select a.staffabc_master_id,
        	   a.staffabc_master_desc, 
        	   sum(c.score) as score
          from hr_staffabc_master a, 
               hr_staffabc_detail b, 
               hr_employee_staffabc c
         where a.seg_segment_no = b.seg_segment_no
           and a.staffabc_master_id = b.staffabc_master_id
           and b.seg_segment_no = c.seg_segment_no
           and b.staffabc_master_id = c.staffabc_master_id
           and b.staffabc_detail_id = c.staffabc_detail_id
           and c.seg_segment_no = :company_id
           and c.psn_id = :emp_seqno
           and c.create_date between sysdate-365 and sysdate
 		group by a.staffabc_master_id, a.staffabc_master_desc
eof;
     return $this->_db->GetArray($sql,array('company_id'=>$this->_companyId,
        									'emp_seqno'=>$emp_seqno));
        
    }// end sttsByABC()
}