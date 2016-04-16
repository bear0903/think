<?php
/*
 *  菜单设定
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/umd_menu_create_DB.php $
 *  $Id: umd_menu_create_DB.php 3552 2013-09-28 07:38:38Z dennis $
 *  $Rev: 3552 $ 
 *  $Date: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *********************************************************/

class Umd extends AresAction 
{
	public $program_name = "";
	public $sql;

	private $_username;
	
	public function __construct()
	{
		parent::__construct();
		$this->_username = $_SESSION['user']['user_seq_no'];
	}
	
	public function actionNew(){
		$nullArr[]=array('ID' => '', 'TEXT' => "請選擇");
		$esnArr=$this->getMenuTree('ESN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true);
		$mdnArr=$this->getMenuTree('MDN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true);
		$menuSelected = empty($_POST['FUNCTION_ID'])?'':$_POST['FUNCTION_ID'];
		$html='';
		$html.=$this->getDropDownListHtml($esnArr,$menuSelected);
		$html.=$this->getDropDownListHtml($mdnArr,$menuSelected);
		$this->tpl->assign ( "menuCascade", $html);
		
		// 多语列表
		$sql=" select  language_code   id,
		               language_name   text
		 		from  ehr_multilang_list ";
		$rs=$this->db->GetArray($sql);
		$this->tpl->assign("lang", $rs);
		$langSelected = empty($_POST['LANG_CODE'])?'':$_POST['LANG_CODE'];
		$this->tpl->assign("langCascade", $this->getDropDownListHtml($rs,$menuSelected));
		
		//菜单类别
		$childtypeArr[]=array('ID'=>'FORM','TEXT'=>'FORM');
		$childtypeArr[]=array('ID'=>'MENU','TEXT'=>'MENU');
		$typeSelected = empty($_POST['CHILD_TYPE'])?'':$_POST['CHILD_TYPE'];
		$this->tpl->assign("childtype", $this->getDropDownListHtml($childtypeArr,$typeSelected));
		
		//菜单子分類
		$subtypeArr[]=array('ID'=>'NORMAL'  ,'TEXT'=>'NORMAL');
		$subtypeArr[]=array('ID'=>'WORKFLOW','TEXT'=>'WORKFLOW');
		$subtypeArr[]=array('ID'=>'QUERY'   ,'TEXT'=>'QUERY');
		$subtypeSelected = empty($_POST['SUB_CHILD_TYPE'])?'':$_POST['SUB_CHILD_TYPE'];
		$this->tpl->assign("subchildtype", $this->getDropDownListHtml($subtypeArr,$subtypeSelected));
	}
	
	public function actionSave(){		
		$sql = "insert into app_functions
				  (seg_segment_no,
				   function_id,
				   child_id,
				   child_type,
				   p_prior,
				   username,
				   create_by,
				   create_date,
				   create_program,
				   update_by,
				   update_date,
				   update_program,
				   function_no_sz,
				   child_no_sz,
				   child_name)
				values
				  (
	                 '".$_SESSION['user']['company_id']."'
	                 ,'".$_POST['FUNCTION_ID']."'
	                 ,'".$_POST['CHILD_ID']."'
	                 ,'".$_POST['CHILD_TYPE']."'
	                ,'".$_POST['P_PRIOR']."'
	                ,'".$this->_username."'
	                ,'".$this->_username."'
	                ,sysdate
	                ,'ehr_setup'
	                ,NULL,NULL,NULL
	                ,'".$_POST['FUNCTION_ID']."'
	                ,'".$_POST['CHILD_ID']."'
	                ,'".$_POST['CHILD_ID']."'
	               )";
		//echo $sql;
		$ok = $this->db->Execute($sql);
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			if($err_no=='1')  showMsg('錯誤:菜單代碼重複','error');			
			showMsg('新增失敗.<br>'.$this->db->ErrorMsg(),'error');
		}else{
			 //showMsg('Update successfull.','success' );
		}
		
		// userfunction  begin
		$perm_p_code = $_POST['FUNCTION_ID'].'_ROLE';
		if($_POST['CHILD_TYPE']=='MENU'){
			$perm_code=$_POST['CHILD_ID'].'_ROLE';
			$perm_type="ROLE";
		}else{
			$perm_code=$_POST['CHILD_ID'];
			$perm_type="CALL FORM";
		}
		$sql = "delete from APP_USERFUNCTION
						where  USERROLE='".$perm_p_code."' 
						and ROLEFUNCTION_TYPE='".$perm_type."'
						and  ROLEFUNCTION='".$perm_code."'
				       ";
		//echo $sql;
	    $ok=@$this->db->execute($sql);
	    
		$sql="insert into APP_USERFUNCTION (
						  USERROLE
						  ,ROLEFUNCTION 
						  ,ROLEFUNCTION_TYPE
						  ,USERROLE_ROLE 
						  ,USERROLE_DESC 
						  ,CREATE_PROGRAM
						  ,UPDATE_PROGRAM 
						  ,USER_RESTRICT_OVERRIDE 
						  ,CREATE_BY 
						  ,CREATE_DATE
						) values(
						  '".$perm_p_code."'
						  , '".$perm_code."'
						  ,'".$perm_type."'
						  ,'ROLE'
						  ,'".$perm_p_code."'
						  ,'ESS_UMD'
						  ,'ESS_UMD'
						  ,'N'
						  ,'".$this->_username."'
						  ,sysdate
						)
					";
		//echo $sql;exit;
		$ok=@$this->db->execute($sql);
	    if (!$ok){
			$err_no=$this->db->ErrorNo();
			if($err_no=='1')  showMsg('新增資料重複','error');
			
			showMsg('新增資料失敗<br/>'.$this->db->ErrorMsg(),'error');
		}else{
			 //showMsg('Update successfull.','success' );
		}
		
		$sql="
		              DELETE APP_FILE
		               WHERE FILENAME = '".$_POST['CHILD_ID']."'
		                 AND FILETYPE = '".$_POST['CHILD_TYPE']."'
	                ";
       	$ok=@$this->db->execute($sql);
       	if($_POST['CHILD_TYPE']=='FORM'){
	        $sql="
	             INSERT INTO APP_FILE (
	                FILENAME,FILETYPE,FILEDESC,Report_Approve10,CREATE_BY,update_by,CREATE_DATE,CREATE_PROGRAM
	              ) VALUES (
	                 '".$_POST['CHILD_ID']."'
	                ,'".$_POST['CHILD_TYPE']."'
	                ,'".$_POST['CHILD_ID']."'
	                ,'".$_POST['SUB_CHILD_TYPE']."'
	                ,'".$this->_username."','".$this->_username."',SYSDATE
	                ,'ESS_UMD'
	              )
	              "; 
       	}else{
       		$sql="
	             INSERT INTO APP_FILE (
	                FILENAME,FILETYPE,FILEDESC,CREATE_BY,update_by,CREATE_DATE,CREATE_PROGRAM
	              ) VALUES (
	                 '".$_POST['CHILD_ID']."'
	                ,'".$_POST['CHILD_TYPE']."'
	                ,'".$_POST['CHILD_ID']."'
	                ,'".$this->_username."','".$this->_username."',SYSDATE
	                ,'ESS_UMD'
	              )
	              "; 
       	}
        //echo $sql;
        $ok=@$this->db->execute($sql);
        
        $sql="
                 DELETE FROM APP_MUTI_LANG
                 where name='".$_POST['CHILD_ID']."' 
                 and program_no='HCP'
                 and type_code='MT'
                ";
        $ok=@$this->db->execute($sql);
        
        ///菜单名称
        foreach($_POST['MENU_LANG'] as $key=>$lang){
        	$MENU_LANG='MENU_NAME_'.$lang;
        	$menu_desc=empty($_POST[$MENU_LANG])?$_POST['CHILD_ID']:$_POST[$MENU_LANG];
	        $sql="
	                  INSERT INTO APP_MUTI_LANG(
	                        PROGRAM_NO,
	                        LANG_CODE,
	                        TYPE_CODE,
	                        SEQ,
	                        NAME,
	                        VALUE,
	                        UPDATE_BY,
	                        UPDATE_DATE   )
	                  VALUES(
	                        'HCP',
	                        '".$lang."',
	                        'MT',
	                        NULL,
	                        '".$_POST['CHILD_ID']."',
	                        '".$menu_desc."',
	                        '".$this->_username."',
	                        SYSDATE
	                        )
	                  ";
	        //echo sql;
	        $ok=@$this->db->execute($sql);
	        if($_POST['CHILD_TYPE']=='MENU'){
	        	$sql="
	                  INSERT INTO APP_MUTI_LANG(
	                        PROGRAM_NO,
	                        LANG_CODE,
	                        TYPE_CODE,
	                        SEQ,
	                        NAME,
	                        VALUE,
	                        UPDATE_BY,
	                        UPDATE_DATE   )
	                  VALUES(
	                        'HCP',
	                        '".$lang."',
	                        'MT',
	                        NULL,
	                        '".$_POST['CHILD_ID']."_ROLE',
	                        '".$menu_desc."',
	                        '".$this->_username."',
	                        SYSDATE
	                        )
	                  ";
		        //echo sql;
		        $ok=@$this->db->execute($sql);
	        }
        }
        
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			if($err_no=='1')  showMsg('提示: 菜单注册重复。','error');
			
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			$url= "?scriptname=umd_menu_list&do=MenuList&FUNCTION_ID=".$_POST['FUNCTION_ID'];
			showMsg('<a href="'.$url.'" >儲存成功</a>.','success' );
			//header($url);
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
	public function actionMenuList(){
		$function_id='';
		if(!empty($_GET['FUNCTION_ID'])) $function_id=$_GET['FUNCTION_ID'];
		if(!empty($_POST['FUNCTION_ID'])) $function_id=$_POST['FUNCTION_ID'];
		$_POST['FUNCTION_ID'] = $function_id;
		$this->actionNew();

		$sql="
               SELECT A.CHILD_ID,
		              A.CHILD_TYPE,
		              (select AF.REPORT_APPROVE10 
		               from APP_FILE AF
		               where AF.FILENAME=A.CHILD_ID 
		                     AND rownum=1
		               ) SUB_CHILD_TYPE,
		              A.FUNCTION_ID,
		              A.P_PRIOR,
		              B.VALUE  CHILD_NAME
			     FROM APP_FUNCTIONS A, APP_MUTI_LANG B
				WHERE A.CHILD_ID = B.NAME
				      AND A.SEG_SEGMENT_NO = '".$_SESSION['user']['company_id']."'
				      AND A.FUNCTION_ID = '".$function_id."'
				      AND B.PROGRAM_NO = 'HCP'
				      AND B.LANG_CODE = '".$_SESSION['user']['language']."'
				      AND B.TYPE_CODE = 'MT'
				order by A.P_PRIOR
			";
		
		$rs=$this->db->GetArray($sql);
		//pr($rs);
		$this->tpl->assign('list',$rs);
	}
	public function actionEdit()
	{
		$function_id = $_GET['FUNCTION_ID'];
		$child_id = $_GET['CHILD_ID'];
		$sql="select REPORT_APPROVE10 
		        from APP_FILE 
		       where FILENAME='".$child_id."'";
		$_POST['SUB_CHILD_TYPE']=$this->db->GetOne($sql);//'NORMAL,WORKFLOW,QUERY';
		$sql="
               SELECT A.CHILD_ID,
		              A.CHILD_TYPE,
		              A.FUNCTION_ID,
		              A.P_PRIOR,
		              B.VALUE  CHILD_NAME
			     FROM APP_FUNCTIONS A, APP_MUTI_LANG B
				WHERE A.CHILD_ID = B.NAME
				      AND A.SEG_SEGMENT_NO = '".$_SESSION['user']['company_id']."'
				      AND A.FUNCTION_ID = '".$function_id."'
				      AND A.CHILD_ID = '".$child_id."'
				      AND B.PROGRAM_NO = 'HCP'
				      AND B.LANG_CODE = '".$_SESSION['user']['language']."'
				      AND B.TYPE_CODE = 'MT'
			";
		$arr = $this->db->GetRow($sql);  //echo $sql;exit;
		$this->tpl->assign ( "row", $arr);
		
		
		$_POST['FUNCTION_ID']=$function_id;
		$_POST['CHILD_TYPE']=$arr['CHILD_TYPE'];
		$this->actionNew();
		
		
		$sql="
               SELECT B.VALUE  VALUE,B.LANG_CODE  ID ,lang.language_name  TEXT 
			     FROM APP_MUTI_LANG B
			          ,EHR_MULTILANG_LIST  lang
				WHERE B.LANG_CODE = lang.language_code
			      AND B.PROGRAM_NO = 'HCP'
			      AND B.TYPE_CODE = 'MT'
			      AND B.NAME = '".$child_id."'
			";
		$arr = $this->db->GetArray($sql);  //pr($arr);exit;
		$this->tpl->assign ( "lang", $arr);
		$this->tpl->assign ( "action", 'Update');
	}
	public function actionDelete()
	{
		$function_id=empty($_POST['FUNCTION_ID'])?$_GET['FUNCTION_ID']:$_POST['FUNCTION_ID'];
		$child_id=empty($_POST['CHILD_ID'])?$_GET['CHILD_ID']:$_POST['CHILD_ID'];
		$child_type=empty($_POST['CHILD_TYPE'])?$_GET['CHILD_TYPE']:$_POST['CHILD_TYPE'];
		
		$sql = "delete from  APP_FUNCTIONS
	               where 
	                 SEG_SEGMENT_NO ='".$_SESSION['user']['company_id']."'  and
	                 FUNCTION_ID    ='".$function_id."'   and
	                 CHILD_ID       ='".$child_id."'
	               ";
		//echo $sql;
		$ok = @$this->db->Execute($sql);
		
		$perm_p_code = $function_id.'_';
		if($child_type=='MENU'){
			$perm_code=$child_id.'_';
			$perm_type="ROLE";
		}else{
			$perm_code=$child_id;
			$perm_type="CALL FORM";
		}
		$sql = "delete from APP_USERFUNCTION
						where  USERROLE='".$perm_p_code."' 
						and ROLEFUNCTION_TYPE='".$perm_type."'
						and  ROLEFUNCTION='".$perm_code."'
				       ";
		$ok = @$this->db->Execute($sql);
	
		
		//如果别处有用到,则子菜单保留
		$sql="select count(*) used_count from APP_FUNCTIONS
			  where 
	                 SEG_SEGMENT_NO ='".$_SESSION['user']['company_id']."'  and
	                 CHILD_ID       ='".$child_id."' 
			";
		$used_count=$this->db->GetOne($sql);
		if($used_count==0){
			
			$sql="
			              DELETE APP_FILE
			               WHERE FILENAME = '".$child_id."'
			                 AND FILETYPE = '".$child_type."'
		                ";
			$ok = @$this->db->Execute($sql);
			
			$sql="
	                 DELETE FROM APP_MUTI_LANG
	                 where name='".$child_id."' 
	                 and program_no='HCP'
	                 and type_code='MT'
	                ";
			$ok = $this->db->Execute($sql);
		}
		if(!$ok){
			exit($this->db->ErrorMsg());
		}else{
			$_POST['FUNCTION_ID']=$function_id;
			return $this->actionMenuList();
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

	public function getMenuTree($parent_id = '0', $spacing = '', $exclude = '', $menu_tree_array = '', $include_itself = false) {
	
	    if (!is_array($menu_tree_array)) $menu_tree_array = array();
	    if ( (sizeof($menu_tree_array) < 1) && ($exclude != '0') ) $menu_tree_array[] = array('ID' => '', 'TEXT' => "Select One");
	
	    if ($include_itself) {
	      $menu_title = $this->db->GetOne("select value  from  app_muti_lang 
							      				where name='" . $parent_id . "'
								                  and program_no='HCP'
								                  and lang_code='".$_SESSION['user']['language']."' 
								                  and type_code='MT'"
								                );			                
	      $menu_tree_array[] = array('ID' => $parent_id, 'TEXT' => $menu_title);

	    }
	    $sql=" select T.FUNCTION_ID,T.CHILD_ID,T.CHILD_TYPE
	             from APP_FUNCTIONS  T
			    where T.function_id = '".$parent_id."'
				  AND T.seg_segment_no='".$_SESSION['user']['company_id']."'
				  AND T.CHILD_TYPE='MENU'
			  ORDER BY  T.P_PRIOR";
	    $rs = $this->db->GetArray($sql);
	    //echo $sql.'<br>';
	    //pr($rs);exit;
	    foreach($rs as $key=>$value) {	      
	    	$tree_title = $this->db->GetOne("select value  from  app_muti_lang 
							      				where name='" . $value['CHILD_ID'] . "'
								                  and program_no='HCP'
								                  and lang_code='".$_SESSION['user']['language']."' 
								                  and type_code='MT'"
								                );
			if ($exclude != $value['CHILD_ID']) $menu_tree_array[] = array('ID' => $value['CHILD_ID'], 'TEXT' => $spacing . $tree_title);
			if($value['CHILD_TYPE']=='MENU') $menu_tree_array = $this->getMenuTree($value['CHILD_ID'], $spacing . '&nbsp;&nbsp;&nbsp;&nbsp;', $exclude, $menu_tree_array);
	    }
	
	    return $menu_tree_array;
	}
	public function getDropDownListHtml($list,$select_value){
		$html='';
		foreach ($list as $key=>$row){
			$selected=($row['ID']==$select_value)?'selected':'';
		    $html.="<option value='".$row['ID']."'  ".$selected.">".$row['TEXT']."</option>\r\n";
		}
		return $html;
	}
	
}
if(empty($_GET['do']))  $_GET['do']='New';
/*  controller */
$umd = new Umd();
$umd->run();

