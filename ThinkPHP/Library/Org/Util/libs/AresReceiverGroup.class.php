<?php
/* module  
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresReceiverGroup.class.php $
 *  $Id: AresReceiverGroup.class.php 698 2008-11-19 05:51:54Z dennis $
 *  $Rev: 698 $ 
 *  $Date: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 \****************************************************************************/
class ReceiveGroup{
	var $db;
	var $tpl;
	public function __construct(){
		global $g_db_sql,$g_tpl;
		$this->db= &$g_db_sql;
		$this->tpl=&$g_tpl;
	}
	
	public function actionList(){
		$sql = "select * from ehr_mail_group_master 
		 	      where  USER_SEQNO = ".$_SESSION['user']['user_seq_no']."
			   	  ORDER BY GROUP_ID";
		$rs=$this->db->GetArray($sql);
		/*
        print '<pre>';
        print_r($rs);exit;
        print '</pre>';
		*/
		if(count($rs)==0){
			$this->actionAdd();
		}else{
			$this->tpl->assign ( "show", "List");
	        $this->tpl->assign ( "list", $rs);
		}
	}
	
	public function actionAdd(){
		$this->tpl->assign ( "show", "Add");
	}
	
	public function actionEdit(){
		$sql = "select * from ehr_mail_group_master where GROUP_ID='".$_GET['key']."'";
		$arr = $this->db->GetRow($sql);  //print_r($arr);exit;
		$this->tpl->assign ( "row", $arr);
		$this->tpl->assign ( "show", "Edit");
	}
	
	public function actionSave(){
		/*
		$rs=$db->Execute($sql);
		while ($arr = $rs->FetchRow()) {
			 print_r($arr);
		    $user_seqno=$arr['ALERT_SEQNO'];
		}
	    */
		/*
		$rs=$db->GetArray($sql);
		//print_r($rs);exit;
		for($i=0;$i<count($rs);$i++){
			//print_r($rs[$i]);
		    $user_seqno=$rs[$i]['ALERT_SEQNO'];
		}
		*/
		/*
		 $arr = $this->db->GetRow($sql);  //print_r($arr); 
		 */
		if(empty($_POST['GROUP_ID'])){
			$sql = "select ehr_mail_group_master_s.nextval GROUP_ID from dual";
			$arr = $this->db->GetRow($sql);  //print_r($arr);
			$_POST['GROUP_ID']=$arr['GROUP_ID'];
		}else{
			$sql = "delete from ehr_mail_group_master where GROUP_ID='".$_POST['GROUP_ID']."'";
			@$this->db->Execute($sql);
			/*
			$sql = "delete from ehr_mail_group_detail where GROUP_ID='".$_POST['GROUP_ID']."'";
			@$this->db->Execute($sql);
			*/
		}
		$_POST['COMPANY_SEQNO']=$_SESSION['user']['company_id'];
		$_POST['USER_SEQNO']=$_SESSION['user']['user_seq_no'];
		//print "<pre>";print_r($_POST);print "</pre>";
		$sql="insert into ehr_mail_group_master(
					   company_seqno,
					   user_seqno   ,
					   group_id     ,
					   group_desc   ,
					   group_comments
			   ) values (
			   		   '".$_POST['COMPANY_SEQNO']."',
					   '".$_POST['USER_SEQNO']."',
					   '".$_POST['GROUP_ID']."',
					   '".$_POST['GROUP_DESC']."',
					   '".$_POST['GROUP_COMMENTS']."' 
			   )";
	   
		//print_r($sql);exit;
		$ok = $this->db->Execute($sql);
		//if(!ok) print LogError($db->ErrorMsg());
		//$sql=insert into 
		if (!$ok){
			return showMsg($this->db->ErrorMsg());
		}
		/*
		for($i=0;$i<count($_POST['SHOW_NO']);$i++){
			$sql="insert into ehr_alert_condition_list(
						   ALERT_SEQNO ,
						   SHOW_NO,
						   KPI_COLOR,
						   KPI_VALUE,
						   KPI_TYPE ,
						   KPI_OPERATOR,
						   KPI_ACTION ,
						   MAIL_TO  ,
						   MAIL_CC ,
						   MAIL_BCC ,
						   MAIL_PRIORITY
				   ) values (
				   		   '".$_POST['alert_seqno']."',
						   '".$_POST['SHOW_NO'][$i]."',
						   '".$_POST['KPI_COLOR'][$i]."',
						   '".$_POST['KPI_VALUE'][$i]."',
						   '".$_POST['KPI_TYPE'][$i]."',
						   '".$_POST['KPI_OPERATOR'][$i]."',
						   '".$_POST['KPI_ACTION'][$i]."',
						   '".$_POST['MAIL_TO'][$i]."',
						   '".$_POST['MAIL_CC'][$i]."',
						   '".$_POST['MAIL_BCC'][$i]."',
						   '".$_POST['MAIL_PRIORITY'][$i]."' 
				   )";
		   
			//print_r($sql);exit;
			$ok = $this->db->Execute($sql);
			//if(!ok) print LogError($db->ErrorMsg());
			//$sql=insert into 
			if (!$ok){
				return $this->db->ErrorMsg();
			}else{
				//return "ok";
			}
			
		}
		*/
		$this->actionList();
	}
	public function actionDelete(){
		$sql = "delete from ehr_mail_group_master where GROUP_ID='".$_GET['key']."'";
		
		$ok = $this->db->Execute($sql);
	    if (!$ok){
	    	//print_r($ok);exit;
			return  LogError($this->db->ErrorMsg());
		}
		$this->actionList();
	}
	public function run(){
		/*  controller */
		//$receiveGroup = new ReceiveGroup();
		if(!empty($_GET['action'])) $action=$_GET['action'];
		if(!empty($_POST['action'])) $action=$_POST['action'];
		$action = empty($action)?'List':$action;
		//$tt="\$receiveGroup->action".$action."();";print $tt;exit;
		eval("\$this->action".$action."();");
	}
	
}


?>