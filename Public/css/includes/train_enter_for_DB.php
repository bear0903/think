<?php
/*
 * 教育训练   在线报名   mappng  hrhf310
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/train_enter_for_DB.php $
 *  $Id: train_enter_for_DB.php 3524 2013-09-03 03:39:59Z dennis $
 *  $Rev: 3524 $ 
 *  $Date: 2013-09-03 11:39:59 +0800 (周二, 03 九月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-09-03 11:39:59 +0800 (周二, 03 九月 2013) $
 *********************************************************/
require_once('../libs/library/GridView/Data_Paging.class.php');
include_once 'AresAction.class.php';
class Train extends AresAction 
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
		///if(!isset($_POST['EMP_ID'])) $_POST['EMP_ID'] = '--请输入查找条件--';
		$condi_str = "";
		//$this->getSearchFromSave();
		$sql =  $this->sql . $condi_str ." order by hcs.apply_date desc";
		$sqlcount = "select count(*) RCT from (".$sql.")";//echo $sqlcount;exit;
		$arr = $this->db->GetRow($sqlcount);  //echo $sqlcount;
		//pr($arr);exit;
		$total_rows=$arr['RCT'];
		$pageIndex = empty($_GET['pageIndex'])?1:$_GET['pageIndex'];
		$page_size = 5;
		if($total_rows > $page_size){
			$page=new Data_Paging(array('total_rows'=>$total_rows,'page_size'=>$page_size));
	        $page->openAjaxMode('gotopage');
			$pageDownUp = $page->outputToolbar(2);
			$this->tpl->assign ( "pageDownUp", $pageDownUp);
		}else{
			$pageIndex = 1;
		}
		//echo $sql;
		$rsLimit=$this->db->SelectLimit($sql,$page_size,$page_size*($pageIndex-1));
		$rs=$rsLimit->getArray();
 		$this->tpl->assign ( "list", $rs);

		$this->tpl->assign ( "show", "New");
		if($total_rows > 0) $this->tpl->assign ( "show", "List");
        //$this->tpl->assign ( "list", $rs);
        //if($total_rows >= 50)  $this->tpl->assign ( "search_condition_alert", '<font color="red">提示：显示50笔，符合条件数据可能超过50笔，请输入更精确的查找条件。</font>');
        //if($total_rows == 0)  $this->tpl->assign ( "search_condition_alert", '<font color="red">查询结果：没有符合条件的数据。</font>');
        if($total_rows == 0)  $this->actionNew();
	}
	public function actionNew(){
		//pr($_SESSION);exit;
		//echo 'test';exit;
		$row=array('EMP_ID'=>$_SESSION['user']['emp_id'],
					'EMP_NAME'=>$_SESSION['user']['emp_name']
				  );
		$this->tpl->assign ( "show", "New");
		//$this->tpl->assign ( "appdesc", $_GET['appdesc']);
		$this->tpl->assign ( "row",$row);
	}
	public function actionEdit()
	{
		$this->actionNew();
		$sql = $this->sql ." and EMP_REQUIRE_ID='".$_GET['key']."'";
		$arr = $this->db->GetRow($sql);  //pr($arr);exit;
		$this->tpl->assign ( "row", $arr);
	}
	public function actionDelete()
	{
		$sql = "delete from HR_EMP_REQUIRE where EMP_REQUIRE_ID='".$_GET['key']."'";
		
		$ok = $this->db->Execute($sql);
		if(!$ok){
			exit($this->db->ErrorMsg());
		}else{
			return $this->actionList();
		}
	}
	public function actionSave()
	{
		//pr($_SESSION['user']);
		//pr($_POST);exit;
		$sql = "
					insert into HR_CLASS_STUDENT (
						  CLASS_STUDENT_ID,
						  SEG_SEGMENT_NO,
						  STUDENT_ID,
						  IS_APPROVE,
						  SUBJECT_CLASS_ID,
						  
						  CREATE_BY,
						  CREATE_DATE,
						  UPDATE_BY,
						  UPDATE_DATE ,
						  CREATE_PROGRAM,
						  
						  UPDATE_PROGRAM,
						  IS_REFUCE ,
						  REFUSE_REASON,
						  IS_CANCEL,
						  CANCEL_REASON,
						  
						  APPLY_DATE,
						  APPROVE_DATE,
						  REFUSE_DATE ,
						  CANCEL_DATE ,
						  LEAVE_HOUR       ,
						  
						  ABSENCE_HOUR     ,
						  CLASS_LEVEL,
						  CLASS_SCORE,
						  IS_PASS,
						  SCORE_REMARK,
						  
						  IS_ABSENCE
					) values (
							hr_class_student_s.nextval,            
							'".$_SESSION['user']['company_id']."',               
							'".$_SESSION['user']['emp_seq_no']."',
							'N',   
							'".$_POST['SUBJECT_CLASS_ID']."',
							
							'train_enter_for',
							sysdate,    
							'train_enter_for',
							sysdate,    
							'HRHF310',
							
							'HRHF310',
							'N',   
							null,
							'N',   
							null,
							
							sysdate,    
							null,
							null,
							null,
							null,
							
							null,
							null,
							null,
							'N',
							null,
							
							'N' 
					)        
		";

	   
		//echo $sql;exit;
		$ok = $this->db->Execute($sql);
		//if(!ok) print LogError($db->ErrorMsg());
		//$sql=insert into 
		/*
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			if($err_no=='1') exit('提示: 不能重复报名。');
			exit($this->db->ErrorMsg());
		}else{
			 exit('恭喜,报名成功!');
		}*/
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			if($err_no=='1')  showMsg('提示: 不能重复报名。','error');
			
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			 showMsg('Update successfull.','success' );
		}
		
	}	
	public function getPopListLectureType()
	{
		$sql = "SELECT CODEVALUE  TEXT
						,CODEID   VALUE
				FROM HR_CODEDETAIL
				 WHERE HCD_SEG_SEGMENT_NO = '".$_SESSION['user']['company_id']."'
				   AND HCD_CODETYPE = 'CLASSTYPE'
				 ORDER BY LINE_NO";
		$rs=$this->db->GetArray($sql);
		return $rs;
	}
	public function actionGetSubject() {
		//pr($_GET);exit;
		/*
		 * into :HR_CLASS_STUDENT.subject_hour
			       ,:HR_CLASS_STUDENT.SUBJECT_DATE_BEGIN
			       ,:HR_CLASS_STUDENT.SUBJECT_DATE_END
			       ,:HR_CLASS_STUDENT.SUBJECT_BEGIN
			       ,:HR_CLASS_STUDENT.SUBJECT_END
			       ,ln_agency
			       ,ln_group
			       ,:HR_CLASS_STUDENT.SUGGEST_PERSON
			       ,:HR_CLASS_STUDENT.MIN_PERSON
			       ,:HR_CLASS_STUDENT.MAX_PERSON
			       ,:HR_CLASS_STUDENT.CLASSTYPE
			       ,:HR_CLASS_STUDENT.EVALUATION_TYPE
			       ,:HR_CLASS_STUDENT.CLASS_DESC
			       ,:HR_CLASS_STUDENT.CLASS_BOARD,
			       ln_subject,
			       :HR_CLASS_STUDENT.is_begin_apply,
			       :HR_CLASS_STUDENT.apply_begin_date,
			       :HR_CLASS_STUDENT.apply_end_date
		 */
		$sql="
				 select HSC.SUBJECT_CLASS_NO
		               ,HSC.SUBJECT_CLASS_NAME
		               ,HSC.SUBJECT_DATE_BEGIN
		               ,HSC.SUBJECT_DATE_END
		               ,TO_CHAR(HSC.SUBJECT_BEGIN,'HH:MI') SUBJECT_BEGIN
		               ,TO_CHAR(HSC.SUBJECT_END,'HH:MI') SUBJECT_END
		               ,HS.ID         SUBJECT_NO
		               ,HS.SUBJECT    SUBJECT_NAME
		                 --,hcs.*
		          from hr_subject_class     hsc,
		               hr_subject             hs
				 where hsc.subject_id=hs.subject_id
				   and hsc.seg_segment_no = '".$_SESSION['user']['company_id']."'
				   and hs.seg_segment_no = '".$_SESSION['user']['company_id']."'
		           and hsc.subject_class_id='".$_POST['SUBJECT_CLASS_ID']."'
			";
		//echo $sql;exit;
		$rs=$this->db->getArray($sql);
		echo json_encode($rs);exit;
	}
	
}
if(empty($_GET['do']))  $_GET['do']='New';
/*  controller */
$train = new Train();
$train->run();

?>