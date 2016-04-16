<?php
/*
 * 训练计划
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/train_requir_online_DB.php $
 *  $Id: train_requir_online_DB.php 3524 2013-09-03 03:39:59Z dennis $
 *  $Rev: 3524 $ 
 *  $Date: 2013-09-03 11:39:59 +0800 (周二, 03 九月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-09-03 11:39:59 +0800 (周二, 03 九月 2013) $
 *********************************************************/
require_once('../libs/library/GridView/Data_Paging.class.php');
include_once 'AresAction.class.php';
class Train extends AresAction 
{
	public $program_name = "train_requir_online_DB.php";
	public $sql;
	
	public function __construct()
	{
		parent::__construct(); //pr($_SESSION);exit;
		$this->sql = "
				SELECT S.ID SUBJECT_NO,
				       S.SUBJECT SUBJECT_NAME,
				       L.ID REQUIRE_TEACHER_NO,
				       L.NAME TEACHER_NAME,
				       M.MASTER_REASON_NO MASTER_REASON_NO,
				       M.MASTER_REASON_DESC MASTER_REASON_DESC,
				       D.DEATIL_REASON_NO DETAIL_REASON_NO,
				       D.DEATIL_REASON_DESC DETAIL_REASON_DESC,
				       T.*
				  FROM HR_EMP_REQUIRE   T,
				       HR_SUBJECT       S,
				       HR_LECTURE       L,
				       HR_MASTER_REASON M,
				       HR_DEATIL_REASON D
				 WHERE T.SEG_SEGMENT_NO = '".$_SESSION['user']['company_id']."'
				   AND S.SEG_SEGMENT_NO(+) = T.SEG_SEGMENT_NO
				   AND S.SUBJECT_ID(+) = T.REQUIRE_SUBJECT
				   AND L.SEG_SEGMENT_NO(+) = T.SEG_SEGMENT_NO
				   AND L.LECTURE_ID(+) = T.REQUIRE_TEACHER
				   AND M.SEG_SEGMENT_NO(+) = T.SEG_SEGMENT_NO
				   AND M.MASTER_REASON_ID(+) = T.MASTER_REASON_ID
				   AND D.SEG_SEGMENT_NO(+) = T.SEG_SEGMENT_NO
				   AND D.DEATIL_REASON_ID(+) = T.DEATIL_REASON_ID
				   AND T.PSN_ID= '".$_SESSION['user']['emp_seq_no']."'
				   ";
	}
	public function actionList()
	{
		///if(!isset($_POST['EMP_ID'])) $_POST['EMP_ID'] = '--请输入查找条件--';
		$condi_str = "";
		//$this->getSearchFromSave();
		$sql =  $this->sql . $condi_str ." order by T.REQUIRE_TIME desc";
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
 		$this->tpl->assign ( "post", $_POST);
 		//$this->tpl->assign ( "appdesc", $_GET['appdesc']);
		//$rs=$this->db->GetArray($sql);
		//echo $sql;exit;
		//pr($rs);exit;
		
		if($total_rows > 0) $this->tpl->assign ( "show", "List");
        //$this->tpl->assign ( "list", $rs);
        //if($total_rows >= 50)  $this->tpl->assign ( "search_condition_alert", '<font color="red">提示：显示50笔，符合条件数据可能超过50笔，请输入更精确的查找条件。</font>');
        //if($total_rows == 0)  $this->tpl->assign ( "search_condition_alert", '<font color="red">查询结果：没有符合条件的数据。</font>');
        if($total_rows == 0)  $this->actionNew();
	}
	public function actionNew(){
		$this->tpl->assign ( "show", "New");
		//$this->tpl->assign ( "appdesc", $_GET['appdesc']);
		$this->tpl->assign ( "row", array('REQUIRE_TIME'=>date("Y-m-d"),'REQUIRE_YYMM'=>date("Ym")));
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
		if(empty($_POST['REQUIRE_INSERT'])) $_POST['REQUIRE_INSERT']="N";
		if(empty($_POST['EMP_REQUIRE_ID'])){ 
			$sql = "
					insert into HR_EMP_REQUIRE (
							EMP_REQUIRE_ID,              
							SEG_SEGMENT_NO,               
							PSN_ID,   
							REQUIRE_TIME,    
							REQUIRE_TYPE,
							REQUIRE_SUBJECT,  
							REQUIRE_YYMM,
							REQUIRE_HOURS,      
							REQUIRE_TEACHER,          
							REQUIRE_LEVEL,         
							REMARK ,
							MASTER_REASON_ID,        
							DEATIL_REASON_ID ,
							REQUIRE_INSERT ,     
							REQUIRE_EXPLAIN,
							CREATE_BY,                    
							CREATE_DATE, 
							UPDATE_BY,       
							UPDATE_DATE,               
							CREATE_PROGRAM,               
							UPDATE_PROGRAM  
					) values (
							hr_emp_require_S.nextval,              
							'".$_SESSION['user']['company_id']."',               
							'".$_SESSION['user']['emp_seq_no']."',   
							to_date('".$_POST['REQUIRE_TIME']."','YYYY-MM-DD'),    
							'".$_POST['REQUIRE_TYPE']."',
							'".$_POST['REQUIRE_SUBJECT']."',
							'".$_POST['REQUIRE_YYMM']."',
							'".$_POST['REQUIRE_HOURS']."',      
							'".$_POST['REQUIRE_TEACHER']."',          
							'".$_POST['REQUIRE_LEVEL']."',         
							'".$_POST['REMARK']."',
							'".$_POST['MASTER_REASON_ID']."',          
							'".$_POST['DEATIL_REASON_ID']."',    
							'".$_POST['REQUIRE_INSERT']."',        
							'".$_POST['REQUIRE_EXPLAIN']."',
							'".$_SESSION['user']['emp_name']."',
							sysdate,
							'".$_SESSION['user']['emp_name']."',
							sysdate,
							'".$this->program_name."',
							'".$this->program_name."'	   
					)        
			";
		}else{
			$sql=" update HR_EMP_REQUIRE set          
						SEG_SEGMENT_NO ='".$_SESSION['user']['company_id']."',            
						PSN_ID ='".$_SESSION['user']['emp_seq_no']."', 
						REQUIRE_TIME ='".$_POST['REQUIRE_TIME']."', 
						REQUIRE_TYPE ='".$_POST['REQUIRE_TYPE']."',
						REQUIRE_SUBJECT ='".$_POST['REQUIRE_SUBJECT']."',
						REQUIRE_YYMM ='".$_POST['REQUIRE_YYMM']."',
						REQUIRE_HOURS ='".$_POST['REQUIRE_HOURS']."',     
						REQUIRE_TEACHER ='".$_POST['REQUIRE_TEACHER']."',         
						REQUIRE_LEVEL ='".$_POST['REQUIRE_LEVEL']."',        
						REMARK ='".$_POST['REMARK']."',
						MASTER_REASON_ID ='".$_POST['MASTER_REASON_ID']."',       
						DEATIL_REASON_ID ='".$_POST['DEATIL_REASON_ID']."',
						REQUIRE_INSERT ='".$_POST['REQUIRE_INSERT']."',   
						REQUIRE_EXPLAIN ='".$_POST['REQUIRE_EXPLAIN']."',
						UPDATE_BY ='".$_SESSION['user']['emp_name']."',
						UPDATE_DATE =sysdate,
						UPDATE_PROGRAM ='".$this->program_name."'
					where EMP_REQUIRE_ID = '".$_POST['EMP_REQUIRE_ID']."'
				";
		}
	   
		//echo $sql;exit;
		$ok = $this->db->Execute($sql);
		//if(!ok) print LogError($db->ErrorMsg());
		//$sql=insert into 
		if (!$ok){
			exit($this->db->ErrorMsg());
		}else{
			return $this->actionList();
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
	
}

/*  controller */
$train = new Train();
$train->run();
//$train->actionListSearch();
$train->tpl->assign('popListLectureType',$train->getPopListLectureType());
?>