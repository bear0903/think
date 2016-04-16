<?php
/**
 *  Create by Boll Yuan  2009-03-26 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_import_DB.php $
 *  $Id: emp_import_DB.php 3490 2013-03-27 08:38:45Z dennis $
 *  $Rev: 3490 $ 
 *  $Date: 2013-03-27 16:38:45 +0800 (周三, 27 三月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-03-27 16:38:45 +0800 (周三, 27 三月 2013) $
 ****************************************************************************/

include_once 'AresAction.class.php';
class ImpEmp extends AresAction {
	public $program_name = "emp_import";
	public function actionList(){
		//$this->db->debug = 1;
		if (!empty($_GET['id'])){
		$sql = <<<eof
			select c.segment_no        as dept_seq_no,
			       c.segment_no_sz     as dept_id,
			       c.segment_name      as dept_name,
			       b.id                as emp_seq_no,
			       b.id_no_sz          as emp_id,
			       b.name_sz           as emp_name,
			       d.overtimetype_code as overtimetype_code,
			       d.overtimetype_desc as overtime_fee_name
			  from ehr_upload_data   a,
			       hr_personnel_base b,
			       gl_segment        c,
			       hr_overtimetype   d
			 where a.company_id = b.seg_segment_no
			   and a.col1 = b.id_no_sz
			   and b.seg_segment_no = c.seg_segment_no
			   and b.seg_segment_no_department = c.segment_no
			   and b.seg_segment_no = d.seg_segment_no
			   and b.overtime_type_id = d.hr_overtimetype_id						
			   and a.company_id= :company_id
		       and a.emp_id= :emp_id
		       and a.setup_id= :setup_id
eof;
		
			$rs=$this->db->GetArray($sql,array('company_id'=>$this->_companyId,
						'emp_id'=>$this->_userEmpId,
						'setup_id'=>$_GET['id']));
			$this->tpl->assign("jsonArr", json_encode($rs));
			
			// delete data after fetch -- add by Dennis 2013-03-27
			$this->db->Execute('delete from ehr_upload_data where company_id = :company_id and emp_id = :emp_id  and setup_id = :setup_id',array('company_id'=>$this->_companyId,
						'emp_id'=>$this->_userEmpId,
						'setup_id'=>$_GET['id']));
		}
	}
}

/*  controller */
if(empty($_GET['do']))  $_GET['do']='List';
$empimp = new ImpEmp();
$empimp->run();

