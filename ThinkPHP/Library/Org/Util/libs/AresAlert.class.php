<?php
/*
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAlert.class.php $
 *  $Id: AresAlert.class.php 1871 2009-06-29 08:48:02Z boll $
 *  $Rev: 1871 $ 
 *  $Date: 2009-06-29 16:48:02 +0800 (周一, 29 六月 2009) $
 *  $Author: boll $   
 *  $LastChangedDate: 2009-06-29 16:48:02 +0800 (周一, 29 六月 2009) $
 \****************************************************************************/
require_once 'AresAction.class.php';
class alert extends AresAction 
{
	public function actionList()
	{
		$sql = "select count(*) RCT from ehr_alert_setting where user_seqno='".$_SESSION['user']['user_seq_no']."'";
		$arr = $this->db->GetRow($sql); 
		
		$total_rows=$arr['RCT'];
		$pageIndex = empty($_GET['pageIndex'])?1:$_GET['pageIndex'];
		$page_size = 12;
		if($total_rows==0){
			header("location: redirect.php?scriptname=alert_edit&do=New");
			exit;
		}else if($total_rows > $page_size){
			require_once('../libs/library/GridView/Data_Paging.class.php');
			$page=new Data_Paging(array('total_rows'=>$total_rows,'page_size'=>$page_size));
	        $page->openAjaxMode('load');
			$pageDownUp = $page->outputToolbar(2);
			$this->tpl->assign ( "pageDownUp", $pageDownUp);
		}

		$sql = "select * from ehr_alert_setting where user_seqno='".$_SESSION['user']['user_seq_no']."' order by alert_seqno desc";
		
 		$rs=$this->db->SelectLimit($sql,$page_size,$page_size*($pageIndex-1));

        $this->tpl->assign ( "listAlert", $rs->getArray());      
	}
	public function actionNew(){}
	public function actionDelete()
	{
		$key=empty($_POST['key'])?$_GET['key']:$_POST['key'];
		$sql = "delete from ehr_alert_setting where alert_seqno='".$key."'";
		
		$ok = $this->db->Execute($sql);
	    if (!$ok){
			print(LogError($this->db->ErrorMsg()));exit;
		}else{
			$this->actionList();
		}
	}
	public function actionEdit()
	{
		$key=empty($_POST['key'])?$_GET['key']:$_POST['key'];
		$sql = "select * from ehr_alert_setting where alert_seqno='".$key."'";
		$arr = $this->db->GetRow($sql); 
		$this->tpl->assign ( "row", $arr);
		
		//get condition
		$sql = "select * from ehr_alert_condition_list where ALERT_SEQNO='".$key."' order by SHOW_NO";
		$rs=$this->db->GetArray($sql);
        $this->tpl->assign ( "list", $rs); 
	}
	public function actionSave()
	{
		if(empty($_POST['alert_seqno'])){
			$sql = "select ehr_alert_setting_s.nextval alert_seqno from dual";
			$arr = $this->db->GetRow($sql);  //print_r($arr);
			if(empty($arr['ALERT_SEQNO']))  showMsg('请与系统管理员联系,sequnce ehr_alert_setting_s 没创建');
			$_POST['alert_seqno']=$arr['ALERT_SEQNO'];
		}else{
			$sql = "delete from ehr_alert_setting where alert_seqno='".$_POST['alert_seqno']."'";
			@$this->db->Execute($sql);
			$sql = "delete from ehr_alert_condition_list where alert_seqno='".$_POST['alert_seqno']."'";
			@$this->db->Execute($sql);
		}
		$_POST['company_seqno']=$_SESSION['user']['company_id'];
		$_POST['user_seqno']=$_SESSION['user']['user_seq_no'];
		if(empty($_POST['period_begin'])) $_POST['period_begin']='';
		if(empty($_POST['period_end'])) $_POST['period_end']='';
		//print "<pre>";print_r($_POST);print "</pre>";
		$sql="insert into ehr_alert_setting(
					   company_seqno ,
					   user_seqno    ,
					   alert_seqno   ,
					   alert_desc      ,
					   kpi_catalog_id  ,
					   kpi_dim_id      ,
					   period_type_id  ,
					   period_begin     ,
					   period_end       ,
					   kpi_important   
			   ) values (
			   		   '".$_POST['company_seqno']."',
					   '".$_POST['user_seqno']."',
					   '".$_POST['alert_seqno']."',
					   '".$_POST['alert_desc']."',
					   '".$_POST['kpi_catalog_id']."',
					   '".$_POST['kpi_dim_id']."',
					   '".$_POST['period_type_id']."',
					   '".$_POST['period_begin']."',
					   '".$_POST['period_end']."',
					   '".$_POST['kpi_important']."'
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
		   
			$ok = $this->db->Execute($sql);
			if (!$ok){
				return $this->db->ErrorMsg();
			}else{
				$this->actionList();
			}
			
		}
	}
}
?>