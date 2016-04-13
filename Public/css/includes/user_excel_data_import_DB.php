<?php
/*
 *  excel导入设置
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/user_excel_data_import_DB.php $
 *  $Id: user_excel_data_import_DB.php 2476 2009-12-22 06:45:38Z boll $
 *  $Rev: 2476 $ 
 *  $Date: 2009-12-22 14:45:38 +0800 (周二, 22 十二月 2009) $
 *  $Author: boll $   
 *  $LastChangedDate: 2009-12-22 14:45:38 +0800 (周二, 22 十二月 2009) $
 *********************************************************/

class Umd extends AresAction 
{
	public $program_name = "train_enter_for_DB.php";
	public $sql;
	
	public function __construct()
	{
		parent::__construct(); //pr($_SESSION);exit;
		$this->sql = "
					select 
						 HSC.SUBJECT_CLASS_NO
					     ,HSC.SUBJECT_CLASS_NAME
					     ,HSC.SUBJECT_DATE_BEGIN
					     ,HSC.SUBJECT_DATE_END
					     ,TO_CHAR(HSC.SUBJECT_BEGIN,'HH:MI') SUBJECT_BEGIN
					     ,TO_CHAR(HSC.SUBJECT_END,'HH:MI') SUBJECT_END
					     ,HS.ID         SUBJECT_NO
					     ,HS.SUBJECT    SUBJECT_NAME
				         --,hcs.*
				  from HR_CLASS_STUDENT hcs,
				         hr_subject_class     hsc,
				         hr_subject             hs
				 where hcs.subject_class_id=hsc.subject_class_id
				   and  hs.subject_id = hsc.subject_id 
				   and hcs.seg_segment_no = '".$_SESSION['user']['company_id']."'
				   and hcs.student_id ='".$_SESSION['user']['emp_seq_no']."'
				   and hsc.seg_segment_no = '".$_SESSION['user']['company_id']."'
				   and hs.seg_segment_no = '".$_SESSION['user']['company_id']."'
				   ";
	}
	public function actionList()
	{
		$sql = "select 
				     SETUP_ID,
				     IMPORT_DESC,
				     ACTION_TYPE,
				     IMPORT_SQL_FORMAT,
				     IS_ACTIVE
			     from EHR_UPLOAD_SETUP
			     ";
		$rs=$this->db->getArray($sql);
		if(count($rs)==0) return $this->actionNew();
		$this->tpl->assign("list", $rs);
		//pr($rs);exit;
		$this->tpl->assign ( "show", 'List');
	}
	public function actionNew(){
		$this->tpl->assign ( "show", 'New');
		
		$optionHtml=gf_getDropDownListHtml($this->getActionTypeList(),'');
		$this->tpl->assign ( "actinTypeList", $optionHtml);
	}
	public function actionEdit(){
		$sql = "select 
				     SETUP_ID,
				     IMPORT_DESC,
				     IMPORT_SQL_FORMAT,
				     ACTION_TYPE,
				     IS_ACTIVE
			     from EHR_UPLOAD_SETUP
			     where SETUP_ID='".$_GET['id']."'
			   ";
		$rs=$this->db->getRow($sql);
		$this->tpl->assign("row", $rs);
		$this->tpl->assign ( "show", 'New');
		
		$optionHtml=gf_getDropDownListHtml($this->getActionTypeList(),$rs['ACTION_TYPE']);
		$this->tpl->assign ( "actinTypeList", $optionHtml);
	}
	public function actionSave(){		
		//pr($_POST);exit;
		if(!empty($_POST['SETUP_ID'])){
			$sql="delete from EHR_UPLOAD_SETUP where  SETUP_ID='".$_POST['SETUP_ID']."'";
			@$this->db->Execute($sql);
			$sql="delete from EHR_UPLOAD_DATA where  SETUP_ID='".$_POST['SETUP_ID']."'";
			@$this->db->Execute($sql);
		}
		$sql="
				insert into  EHR_UPLOAD_SETUP (
					 SETUP_ID,
				     IMPORT_DESC,
				     IMPORT_SQL_FORMAT,
				     ACTION_TYPE,
				     IS_ACTIVE
				) values (
					EHR_UPLOAD_SETUP_SEQ.nextval,
					'".$_POST['IMPORT_DESC']."',
					'".$_POST['IMPORT_SQL_FORMAT']."',
					'".$_POST['ACTION_TYPE']."',
					'".(empty($_POST['IS_ACTIVE'])?'N':'Y')."'
				)
				";
	
		//echo $sql;exit;
		$ok = $this->db->Execute($sql);
		if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			showMsg('Update successfull.','success' );
		}
		
	}
	public function actionDelete(){		
		$sql="delete from EHR_UPLOAD_SETUP where  SETUP_ID='".$_GET['id']."'";
		@$this->db->Execute($sql);
		$sql="delete from EHR_UPLOAD_DATA where  SETUP_ID='".$_GET['id']."'";
		@$this->db->Execute($sql);

		//echo $sql;exit;
		$ok = $this->db->Execute($sql);
		if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			//showMsg('Delete successfull.','success' );
			return $this->actionList();
		}
		
	}
	public function getActionTypeList(){
		$arr[]=array('ID'=>'program','TEXT'=>'Program');
		$arr[]=array('ID'=>'dbprocedure','TEXT'=>'DB Procedure');
		$arr[]=array('ID'=>'sql','TEXT'=>'SQL');
		return $arr;
	}


	
}
if(empty($_GET['do']))  $_GET['do']='List';
/*  controller */
$umd = new Umd();
$umd->run();

?>