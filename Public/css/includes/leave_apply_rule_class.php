<?php
/*
 * 请假规则
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/leave_apply_rule_class.php $
 *  $Id: leave_apply_rule_class.php 3770 2014-06-09 06:52:22Z dennis $
 *  $Rev: 3770 $ 
 *  $Date: 2014-06-09 14:52:22 +0800 (周一, 09 六月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-06-09 14:52:22 +0800 (周一, 09 六月 2014) $
 ****************************************************************************/
//require_once('GridView/Data_Paging.class.php');
include_once 'AresAction.class.php';
class ApplyRule extends AresAction 
{
	public $program_name = "leave_apply_rule";
	public $code = "leave_apply_rule";
	public function actionList()
	{
		$this->actionEdit();
	}
	public function actionEdit()
	{
	    $sql = "select * from ehr_md_content where code=:code and seg_segment_no = :company_id";
	    //echo $sql;exit;
	    $arr = $this->db->GetRow($sql,array('code'=>$this->code,
	            'company_id'=>$_SESSION['user']['company_id']));  //pr($arr);exit;
		$this->tpl->assign("row", $arr);
	}
	public function actionSave()
	{

		$sql = "delete from ehr_md_content  where code=:code and seg_segment_no = :company_id";
		$this->db->Execute($sql,array('code'=>$this->code,
	            'company_id'=>$_SESSION['user']['company_id']));
		$sql = "insert into ehr_md_content (
						CODE,              
						TEXT,
		                seg_segment_no
				) values (
		          :code,
		          :text,
		          :companyid
				)";
		//echo $sql;exit;
		$ok = $this->db->Execute($sql,array('code'=>$this->code,'text'=>$_POST['TEXT'],
		        'companyid'=>$_SESSION['user']['company_id']));
		//if(!ok) print LogError($db->ErrorMsg());
		//$sql=insert into 
		if (!$ok){
			exit($this->db->ErrorMsg());
		}else{
			//return $this->actionList();
			$this->tpl->assign('show_save_msg','Y');
			$this->actionEdit();
		}
		
	}	
	
}
