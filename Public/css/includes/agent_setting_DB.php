<?php
/*
 *  菜单设定
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/agent_setting_DB.php $
 *  $Id: agent_setting_DB.php 3552 2013-09-28 07:38:38Z dennis $
 *  $Rev: 3552 $ 
 *  $Date: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *********************************************************/

class Umd extends AresAction 
{
	public $program_name = "";
	public $sql;
	
	public function __construct()
	{
		parent::__construct(); //pr($_SESSION);exit;
		
	}
	public function actionList()
	{
		
	}
	public function actionNew(){
		
	}
	
	public function _toDate($date,$hour,$minute)
	{
		if(empty($hour)) $hour='00';
		if(empty($minute)) $minute='00';
		return $date.' '.$hour.':'.$minute;
	}
	
	public function actionSave(){        
        $begin_date = $this->_toDate($_POST['begin_date'],$_POST['begin_hour'],$_POST['begin_minute']);
		$end_date   = $this->_toDate($_POST['end_date'],$_POST['end_hour'],$_POST['end_minute']);
		
        $sql="select WORKFLOW_ANGECY_ID from HR_WORKFLOW_ANGECY 
        	    where SEG_SEGMENT_NO='".$_SESSION['user']['company_id']."'
        	      and PSN_ID='".$_SESSION['user']['emp_seq_no']."'";
        
        $WORKFLOW_ANGECY_ID=$this->db->GetOne($sql);
        // 新增被代理人
        if(empty($WORKFLOW_ANGECY_ID)){
        	$sql="select HR_WORKFLOW_ANGECY_s.Nextval WORKFLOW_ANGECY_ID from dual";
	        $WORKFLOW_ANGECY_ID=$this->db->GetOne($sql);
        	$sql="insert into HR_WORKFLOW_ANGECY
				  (
					  WORKFLOW_ANGECY_ID,
					  SEG_SEGMENT_NO     ,
					  PSN_ID             ,
					  REMARK             ,
					  CREATE_BY          ,
					  CREATE_DATE        ,
					  UPDATE_BY          ,
					  UPDATE_DATE        ,
					  CREATE_PROGRAM     ,
					  UPDATE_PROGRAM     
					) values (
					  '".$WORKFLOW_ANGECY_ID."',
					  '".$_SESSION['user']['company_id']."',
					  '".$_SESSION['user']['emp_seq_no']."',
					  null,
					  '".$_SESSION['user']['user_seq_no']."',
					  sysdate,
					  null,
					  null,
					  'ESNS206',
					  null
					)
					";
        	$ok = $this->db->Execute($sql);
	        if (!$ok){
				$err_no=$this->db->ErrorNo();
				showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
			}
        }
        // 新增代理人
        $ALL_ABSENCE=empty($_POST['ALL_ABSENCE'])?'N':'Y';
        $ALL_OVERTIME=empty($_POST['ALL_OVERTIME'])?'N':'Y';
        $ALL_NOCARD=empty($_POST['ALL_NOCARD'])?'N':'Y';
        $ALL_RESIGN=empty($_POST['ALL_RESIGN'])?'N':'Y';
        $ALL_TRANS=empty($_POST['ALL_TRANS'])?'N':'Y';
		$sql = " insert into HR_WORKFLOW_ANGECY_DETAIL
					(
					  WORKFLOW_ANGECY_DETAIL_ID ,
					  SEG_SEGMENT_NO            ,
					  WORKFLOW_ANGECY_ID        ,
					  PSN_ID                    ,
					  ASSIGN_TYPE               ,					  
					  ASSIGN_BEGINTIME          ,
					  ASSIGN_ENDTIME            ,
					  ALL_ABSENCE               ,
					  ALL_OVERTIME              ,
					  REMARK                    ,					  
					  CREATE_BY                 ,
					  CREATE_DATE               ,
					  UPDATE_BY                 ,
					  UPDATE_DATE               ,
					  CREATE_PROGRAM            ,					  
					  UPDATE_PROGRAM            ,
					  ALL_NOCARD                ,
					  ALL_RESIGN                ,
					  ALL_TRANS                 					  
					) values (
					 Hr_Workflow_Angecy_Detail_s.Nextval,
					 '".$_SESSION['user']['company_id']."',
					 '".$WORKFLOW_ANGECY_ID."',
					 '".$_POST['agent_id']."',
					 '".$_POST['assign_type']."',
					 
					 to_date('".$begin_date."','YYYY-MM-DD HH24:MI'),
					 to_date('".$end_date."','YYYY-MM-DD HH24:MI'),
					 '".$ALL_ABSENCE."',
					 '".$ALL_OVERTIME."',
					 null,					 
					 'ehr',
					 sysdate,
					 null,
					 null,
					 null,					 
					 null,
					 '".$ALL_NOCARD."',
					 '".$ALL_RESIGN."',
					 '".$ALL_TRANS."'					 
					)
		";
		$ok = $this->db->Execute($sql);
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			showMsg('保存成功!','success' );
		}
		
	}
	public function actionUpdate(){
		$sql="delete from  APP_FUNCTIONS 
	          where  FUNCTION_ID='".$_POST['FUNCTION_ID']."'
	                 and CHILD_ID='".$_POST['CHILD_ID']."'
	               ";
		//echo $sql;
		$ok = $this->db->Execute($sql);
		$this->actionSave();
	}
	
	public function actionDelete()
	{
		$id=empty($_POST['id'])?$_GET['id']:$_POST['id'];
		$sql = "delete from  HR_WORKFLOW_ANGECY_DETAIL
	               where WORKFLOW_ANGECY_DETAIL_ID ='".$id."' ";
		//echo $sql;
		$ok = @$this->db->Execute($sql);
		
		if(!$ok){
			exit($this->db->ErrorMsg());
		}else{
			showMsg('操作成功!','success' );
		}
	}

}
if(empty($_GET['do']))  $_GET['do']='New';
/*  controller */
$umd = new Umd();
$umd->run();
?>