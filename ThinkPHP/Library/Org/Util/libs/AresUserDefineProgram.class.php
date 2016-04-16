<?php
/**********************************************************
 * 
 * 使用者自定义报表   mapping  ares211
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresUserDefineProgram.class.php $
 *  $Id: AresUserDefineProgram.class.php 3363 2012-10-16 06:53:10Z dennis $
 *  $Rev: 3363 $ 
 *  $Date: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 *********************************************************/

class AresUserDefineProgram extends AresAction 
{
	public $program_name = "";
	public $sql;	
	public function actionNew(){
		$nullArr[]=array('ID' => '', 'TEXT' => "Select One");
		$esnArr=$this->getMenuTree('ESN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true);
		$mdnArr=$this->getMenuTree('MDN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true);
		$menuSelected = empty($_POST['FUNCTION_ID'])?'':$_POST['FUNCTION_ID'];
		$html = '';
		$html.=$this->getDropDownListHtml($esnArr,$menuSelected);
		$html.=$this->getDropDownListHtml($mdnArr,$menuSelected);
		$this->tpl->assign("menuCascade",$html);		
		// 多语列表
		$sql=" SELECT  lang.language_code   ID,
		               lang.language_name   TEXT
		 		FROM  EHR_MULTILANG_LIST  lang
			  ";
		$rs=$this->db->GetArray($sql);
		$this->tpl->assign("lang", $rs);
		$langSelected = empty($_POST['LANG_CODE'])?'':$_POST['LANG_CODE'];
		$this->tpl->assign("langCascade", $this->getDropDownListHtml($rs,$menuSelected));
		
		//菜单类别
		$childtypeArr[]=array('ID'=>'FORM','TEXT'=>'FORM');
		$childtypeArr[]=array('ID'=>'MENU','TEXT'=>'MENU');
		$typeSelected = empty($_POST['CHILD_TYPE'])?'':$_POST['CHILD_TYPE'];
		$this->tpl->assign("childtype", $this->getDropDownListHtml($childtypeArr,$typeSelected));
	}
	
	public function actionSave(){
		$sql = <<<eof
			insert into app_functions
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
			  (:company_id,
			   :function_id,
			   :child_id,
			   :child_type,
			   :p_prior,
			   :usenrame,
			   :create_by,			 
			   SYSDATE,
			  'ehr_setup',
			   NULL,
			   NULL,
			   NULL,
			   :function_id1,
			   :child_id1,
			   :child_id2)		
eof;
		$ok = $this->db->Execute($sql,array('company_id'=>$_SESSION['user']['company_id'],
											'function_id'=>$_POST['FUNCTION_ID'],
											'child_id'=>$_POST['CHILD_ID'],
											'p_prior'=>$_POST['P_PRIOR'],
											'username'=>$_SESSION['user']['username'],
											'create_by'=> $_SESSION['user']['username'],
											'function_id1'=>$_POST['FUNCTION_ID'],
											'child_id1'=>$_POST['CHILD_ID'],
											'child_id2'=>$_POST['CHILD_ID'],));
		if (!$ok){
			$err_no = $this->db->ErrorNo();
			if($err_no=='1')  showMsg('提示: 菜单代码重复。','error');
			showMsg('更新失败.<br><br>'.$this->db->ErrorMsg(),'error');
		}
		// userfunction  begin
		$perm_p_code = $_POST['FUNCTION_ID'].'_';
		if($_POST['CHILD_TYPE']=='MENU'){
			$perm_code=$_POST['CHILD_ID'].'_';
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
						  ,'eHr_UMD'
						  ,'eHr_UMD'
						  ,'N'
						  ,'eHr_UMD'
						  ,sysdate
						)
					";
		//echo $sql;exit;
		$ok=@$this->db->execute($sql);
	    if (!$ok){
			$err_no=$this->db->ErrorNo();
			if($err_no=='1')  showMsg('提示: 授权重复。','error');
			
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			 //showMsg('Update successfull.','success' );
		}
		
		$sql="DELETE APP_FILE
		               WHERE FILENAME = '".$_POST['CHILD_ID']."'
		                 AND FILETYPE = '".$_POST['CHILD_TYPE']."'
	                ";
       	$ok=@$this->db->execute($sql);
        $sql="
             INSERT INTO APP_FILE (
                FILENAME,FILETYPE,FILEDESC,CREATE_BY,CREATE_DATE,CREATE_PROGRAM
              ) VALUES (
                 '".$_POST['CHILD_ID']."'
                ,'".$_POST['CHILD_TYPE']."'
                ,'".$_POST['CHILD_ID']."'
                ,'ARES',SYSDATE
                ,'EHR_UMD'
              )
              "; 
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
	                        'SYSTEM',
	                        SYSDATE
	                        )
	                  ";
	        //echo sql;
	        $ok=@$this->db->execute($sql);
        }
        
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			if($err_no=='1')  showMsg('提示: 菜单注册重复。','error');
			
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			$url= "?scriptname=umd_menu_list&do=MenuList&FUNCTION_ID=".$_POST['FUNCTION_ID'];
			showMsg('<a href="'.$url.'" >保存成功!</a>  刷新网页后,新建菜单生效.','success' );
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
	public function actionProgramList(){
		$function_id='';
		if(!empty($_GET['FUNCTION_ID'])) $function_id=$_GET['FUNCTION_ID'];
		if(!empty($_POST['FUNCTION_ID'])) $function_id=$_POST['FUNCTION_ID'];
		$_POST['FUNCTION_ID'] = $function_id;
		$this->actionNew();

		$sql="
               SELECT A.CHILD_ID,
		              A.CHILD_TYPE,
		              A.FUNCTION_ID,
		              A.P_PRIOR,
		              B.VALUE  CHILD_NAME,
		              C.PROGRAM_NO,
		              C.APPLICATION_TYPE
			     FROM APP_FUNCTIONS A
			     	  ,APP_MUTI_LANG B 
			     	  ,EHR_PROGRAM_SETUP_MASTER C
				WHERE A.CHILD_ID = B.NAME
				      and a.CHILD_ID=C.PROGRAM_NO
				      AND A.SEG_SEGMENT_NO = '".$_SESSION['user']['company_id']."'
				      AND A.FUNCTION_ID = '".$function_id."'
				      AND B.PROGRAM_NO = 'HCP'
				      AND B.LANG_CODE = '".$_SESSION['user']['language']."'
				      AND B.TYPE_CODE = 'MT'
				order by A.P_PRIOR
			";
		$rs=$this->db->GetArray($sql);
		$this->tpl->assign('list',$rs);
	}
	public function actionProgramDelete(){
		$_POST['FUNCTION_ID']=$_GET['FUNCTION_ID'];
		// master
		$sql="delete from EHR_PROGRAM_SETUP_MASTER 
			  where PROGRAM_NO='".$_GET['PROGRAM_NO']."'
			  ";
		$ok = $this->db->Execute($sql);
		if(!$ok){
			exit($this->db->ErrorMsg());
		}
		
		//table
		$sql= "
                 DELETE FROM EHR_PROGRAM_SETUP_TABLE
                 where PROGRAM_NO='".$_GET['PROGRAM_NO']."'
              ";
		$ok = $this->db->Execute($sql);
		if(!$ok){
			exit($this->db->ErrorMsg());
		}
		
		//column
		$sql= "
                 DELETE FROM EHR_PROGRAM_SETUP_COLUMN
                 where PROGRAM_NO='".$_GET['PROGRAM_NO']."'
              ";
		$ok = $this->db->Execute($sql);
		if(!$ok){
			exit($this->db->ErrorMsg());
		}
		
		//column
		$sql= "
                 DELETE FROM EHR_PROGRAM_SETUP_GROUP
                 where PROGRAM_NO='".$_GET['PROGRAM_NO']."'
              ";
		$ok = $this->db->Execute($sql);
		if(!$ok){
			exit($this->db->ErrorMsg());
		}
		
		$this->actionProgramList();
	}
	
	public function actionEditBasic(){
		//基本设置部分
		$sql=<<<eof
			select a.*,
			       (select value
			          from app_muti_lang
			         where name       = a.program_no
			           and program_no = 'HCP'
			           and lang_code  = :lang
			           and type_code  = 'MT') as app_desc
			  from ehr_program_setup_master a
			 where program_no = :program_no
eof;
		$arrMaster = $this->db->GetRow($sql,array('lang'=>$_SESSION['user']['language'],
												  'program_no'=>$_GET['PROGRAM_NO']));
		$arrMaster['PROGRAM_NO']=$_GET['PROGRAM_NO'];
		$this->tpl->assign ("row", $arrMaster);
		/*	 remark by dennis 2011-03-16 没用到	
		$sql="
				select * 
				from  EHR_PROGRAM_SETUP_TABLE epst
				where epst.program_no='".$_GET['PROGRAM_NO']."'
			";
		$arrTable = $this->db->GetRow($sql);  //echo $sql;exit;
		$this->tpl->assign ( "tbrow", $arrTable);
		*/
		
		// column PROGRAM_NO
		$nullArr[]=array('ID' => '', 'TEXT' => "Select One");
		$esnArr=$this->getMenuTree('ESN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true,true);
		$mdnArr=$this->getMenuTree('MDN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true,true);
		$menuSelected = empty($_GET['PROGRAM_NO'])?'':$_GET['PROGRAM_NO'];
		$html='';
		//$html=$this->getDropDownListHtml($nullArr,'---');
		$html.=$this->getDropDownListHtml($esnArr,$menuSelected,'show');
		$html.=$this->getDropDownListHtml($mdnArr,$menuSelected,'show');
		//$html.=$this->getDropDownListHtml($smrptArr,$menuSelected,'show');
		$this->tpl->assign ( "menuCascade", $html);
		
		// COLUMN APPLACATION_TYPE
		/* remark by dennis 2011-03-16 not needed
		$sql="
			SELECT AML.SEQ  ID,AML.VALUE  TEXT
			FROM  APP_MUTI_LANG AML
			WHERE  AML.PROGRAM_NO='ESNS203'
			AND AML.LANG_CODE='".$_SESSION['user']['language']."'
			AND AML.SEQ IS NOT NULL
			AND AML.NAME= 'APPLICATION_TYPE'
			";
		//echo $sql;
		$rs=$this->db->GetArray($sql);//pr($rs);exit;
		$this->tpl->assign("appTypeCascade", $this->getDropDownListHtml($rs,empty($arrMaster['APPLICATION_TYPE'])?'':$arrMaster['APPLICATION_TYPE']));
		
		// COLUMN APPLACATION_TYPE
		$sql="
			SELECT AML.SEQ  ID,AML.VALUE  TEXT
			FROM  APP_MUTI_LANG AML
			WHERE  AML.PROGRAM_NO='ESNS203'
			AND AML.LANG_CODE='".$_SESSION['user']['language']."'
			AND AML.SEQ IS NOT NULL
			AND AML.NAME= 'UI_STYLE'
			";
		$rs=$this->db->GetArray($sql);//pr($rs);exit;
		$this->tpl->assign("UIstyleCascade", $this->getDropDownListHtml($rs,empty($arrMaster['UI_STYLE'])?'':$arrMaster['UI_STYLE']));
		
		// column SORT_MODE dropdownlist
		$sql="
			SELECT AML.SEQ  ID,AML.VALUE  TEXT
			FROM  APP_MUTI_LANG AML
			WHERE  AML.PROGRAM_NO='ESNS203'
			AND AML.LANG_CODE='".$_SESSION['user']['language']."'
			AND AML.SEQ IS NOT NULL
			AND AML.NAME= 'SORT_MODE'
			";
		$rs=$this->db->GetArray($sql);//pr($rs);exit;
		$this->tpl->assign("SORT_MODE_Cascade", $this->getDropDownListHtml($rs,empty($arrMaster['SORT_MODE'])?'':$arrMaster['SORT_MODE']));
		*/
	}
	
	public function actionSaveBasic(){
		//pr($_POST);
		$sql = "delete from EHR_PROGRAM_SETUP_MASTER where PROGRAM_NO='".$_POST['PROGRAM_NO']."'";
		$ok = @$this->db->Execute($sql);
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}
		
		$sql="insert into EHR_PROGRAM_SETUP_MASTER
				(
				  PROGRAM_NO,
				  TARGET_TABLE_DETAIL_ID,
				  PAGE_SIZE,
				  DEFAULT_WHERE,
				  DEFAULT_ORDER_BY,
				  ALLOW_SORTING,
				  SORT_MODE,
				  ALLOW_SELECTED,
				  ALLOW_MOUSE_EVENT,
				  ALLOW_PAGING,
				  HEADER_PAGING,
				  FOOTER_PAGING,
				  PAGING_THEME,
				  ALLOW_ALTERNATING_ROW,
				  ALTERNATING_ROW_STYLE,
				  ALTERNATING_BGCOLOR,
				  ALTERNATING_FONTCOLOR,
				  GRIDVIEW_STYLE,
				  HEADER_STYLE,
				  SELECTED_ROW_STYLE,
				  WIDTH,
				  HEIGHT,
				  UI_STYLE,
				  COMMENTS,
				  RESULT_SQL,
				  ALLOW_QUERYING,
				  APPLICATION_TYPE,
				  ALLOW_GROUPING,
				  IS_SHOW,
				  SHOW_WHERE
				) values (
				  '".$_POST['PROGRAM_NO']."',
				  '1',
				  '".$_POST['PAGE_SIZE']."',
				  '".ereg_replace("'", "''", $_POST['DEFAULT_WHERE'])."',
				  '".$_POST['DEFAULT_ORDER_BY']."',
				  '".$_POST['ALLOW_SORTING']."',
				  '".$_POST['SORT_MODE']."',
				  '".$_POST['ALLOW_SELECTED']."',
				  '".$_POST['ALLOW_MOUSE_EVENT']."',
				  '".$_POST['ALLOW_PAGING']."',
				  '".$_POST['HEADER_PAGING']."',
				  '".$_POST['FOOTER_PAGING']."',
				  '".$_POST['PAGING_THEME']."',
				  '".$_POST['ALLOW_ALTERNATING_ROW']."',
				  '".$_POST['ALTERNATING_ROW_STYLE']."',
				  '".$_POST['ALTERNATING_BGCOLOR']."',
				  '".$_POST['ALTERNATING_FONTCOLOR']."',
				  '".$_POST['GRIDVIEW_STYLE']."',
				  '".$_POST['HEADER_STYLE']."',
				  '".$_POST['SELECTED_ROW_STYLE']."',
				  '".$_POST['WIDTH']."',
				  '".$_POST['HEIGHT']."',
				  '".$_POST['UI_STYLE']."',
				  '".$_POST['COMMENTS']."',
				  '".ereg_replace("'","''",$_POST['RESULT_SQL'])."',
				  '".$_POST['ALLOW_QUERYING']."',
				  '".$_POST['APPLICATION_TYPE']."',
				  '".$_POST['ALLOW_GROUPING']."',
				  '".$_POST['IS_SHOW']."',
				  '".ereg_replace("'","''",$_POST['SHOW_WHERE'])."'
				)
			 ";
		//echo $sql;exit;
		$ok = @$this->db->Execute($sql);
		if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			if(!empty($_POST['next_url'])){
				header('location: '.$_POST['next_url'].'&PROGRAM_NO='.$_POST['PROGRAM_NO']);
			}else{
				echo '<script type="text/javascript">window.close();</script>';
			}
		}
		
	}
	public function actionViewSql(){
		$this->createSql($_GET['PROGRAM_NO']);
		//$this->set_define_query_ini('testsss');
		//PR($_SESSION);EXIT;
		$sql="
				select * 
				from EHR_PROGRAM_SETUP_MASTER epsm
				where epsm.program_no='".$_GET['PROGRAM_NO']."'
			";
		$arrMaster = $this->db->GetRow($sql);  //echo $sql;exit;
		$this->tpl->assign ( "row", $arrMaster);
		
		

	}
	public function createSql($program_no){
		//栏位
		$sql="select TABLE_NAME TABLE_ALLIES_NAME,
	                   COLUMN_NAME,
	                   trim(COLUMN_ACTUAL_VALUE) COLUMN_ACTUAL_VALUE
	              from EHR_PROGRAM_SETUP_COLUMN
	             where PROGRAM_NO = '".$program_no."'
	               and display = '1'
			";
		$cols=$this->db->GetArray($sql); //pr($cols);
		$result_sql=" select ";
		foreach ($cols as $key=>$row){
			if(empty($row['COLUMN_ACTUAL_VALUE'])){
				$result_sql .= $row['TABLE_ALLIES_NAME'].".".$row['COLUMN_NAME'].' '.$row['TABLE_ALLIES_NAME'].'_'.$row['COLUMN_NAME'].' ,'; 
			}else{
				$result_sql .= $row['COLUMN_ACTUAL_VALUE'].' '.$row['TABLE_ALLIES_NAME'].'_'.$row['COLUMN_NAME'].' ,'; 
			}
		}
		$result_sql=substr($result_sql,0,strlen($result_sql)-2);
		
		//表
		$sql="select TABLE_NAME,
	                   TABLE_ALLIES_NAME
	              from EHR_PROGRAM_SETUP_TABLE         
	             where PROGRAM_NO = '".$program_no."'
			";
		$rs=$this->db->GetArray($sql); //pr($rs);
		$result_sql .=" from ";
		foreach ($rs as $key=>$row){
			$result_sql .= $row['TABLE_NAME'].' '.$row['TABLE_ALLIES_NAME'].' ,'; 
		}
		$result_sql=substr($result_sql,0,strlen($result_sql)-2);
		//echo $result_sql;
		
		//where 条件
		$sql="
				select trim(DEFAULT_WHERE)  DEFAULT_WHERE,
				       trim(DEFAULT_ORDER_BY)  DEFAULT_ORDER_BY
				from EHR_PROGRAM_SETUP_MASTER epsm
				where epsm.program_no='".$program_no."'
			";
		$arrMaster = $this->db->GetRow($sql);  //echo $sql;exit;
		
		if(!empty($arrMaster['DEFAULT_WHERE'])){
		 	 $result_sql .= ' where '.$arrMaster['DEFAULT_WHERE'];
		}	
		//order by
		if(!empty($arrMaster['DEFAULT_ORDER_BY'])){
		 	 $result_sql .= '  order by '.$arrMaster['DEFAULT_ORDER_BY'];
		}	
		
		//$result_sql=ereg_replace("'", "''", $result_sql);
		$result_sql=str_ireplace("'", "''", $result_sql);
		//echo $result_sql;
		//echo strlen($result_sql);exit;
		$sql="update EHR_PROGRAM_SETUP_MASTER 
		  	  set RESULT_SQL='".$result_sql."'
		      where PROGRAM_NO='".$program_no."'
		     ";
		$ok=@$this->db->execute($sql);
		if (!$ok){
			showMsg('Update EHR_PROGRAM_SETUP_MASTER.RESULT_SQL faillor.<br><br>'.$this->db->ErrorMsg(),'error');
		}
	}
	public function actionListGroup(){
		//pr($_SESSION);exit;
		$sql="select epsg.*,
		             (
		             select lang.PROMPT_TEXT 
		             from EHR_PROGRAM_COLUMN_LANG lang
		             where 
	                       lang.MUTI_LANG_PK=epsg.Muti_Lang_Pk
	                  and  lang.UICULTURE_CODE='".$_SESSION['user']['language']."'
	                  and  rownum=1
		             ) PROMPT_TEXT
				from EHR_PROGRAM_SETUP_GROUP epsg
				where epsg.program_no='".$_GET['PROGRAM_NO']."'
				order by epsg.sort_seq
			";
		//echo $sql;
		$rs= $this->db->GetArray($sql);
		//pr($rs);exit;

		/*
		 * 
		if(count($rs)==0){
			$_POST['PROGRAM_NO']=$_GET['PROGRAM_NO'];
			return $this->actionNewGroup();
		}
		*/

		$this->tpl->assign('PROGRAM_NO',$_GET['PROGRAM_NO']);
		$this->tpl->assign('list',$rs);
		$this->tpl->assign('show','List');
	}
	public function actionNewGroup(){
		//pr($_POST);
		// 多语列表
		$sql=" SELECT  lang.language_code   ID,
		               lang.language_name   TEXT
		 		FROM  EHR_MULTILANG_LIST  lang
			  ";
		$rs=$this->db->GetArray($sql);
		$this->tpl->assign("lang", $rs);
		
		
		$sql="select MAX(GROUP_ID)  GROUP_ID 
				from EHR_PROGRAM_SETUP_GROUP epsg
				where epsg.program_no='".$_POST['PROGRAM_NO']."'
			 ";
		$group_id = $this->db->GetOne($sql);
		if(empty($group_id)) $group_id = 0;
		$_POST['GROUP_ID'] = $group_id + 1;
		$this->tpl->assign('row',$_POST);
		$this->tpl->assign('show','New');
		$this->tpl->assign('PROGRAM_NO',$_POST['PROGRAM_NO']);
	}
	public function actionEditGroup(){
		//pr($_POST);exit;
		$sql="select * 
				from EHR_PROGRAM_SETUP_GROUP epsg
				where epsg.program_no='".$_POST['PROGRAM_NO']."'
				  and epsg.GROUP_ID='".$_POST['GROUP_ID']."'
				order by epsg.sort_seq
			";
		$row= $this->db->GetRow($sql);
		///pr($row);exit;
		$this->tpl->assign('row',$row);
		$this->tpl->assign('show','Edit');
		$this->tpl->assign('PROGRAM_NO',$_POST['PROGRAM_NO']);
		
		
		// 多语列表
		$sql=" SELECT  lang.language_code   ID,
		               lang.language_name   TEXT,
		               (select L.PROMPT_TEXT
		                from   EHR_PROGRAM_COLUMN_LANG L
		                where  L.UICULTURE_CODE=lang.language_code
		                and    L.MUTI_LANG_PK='".$row['MUTI_LANG_PK']."'
		               )   VALUE
		 		FROM  EHR_MULTILANG_LIST  lang
			  ";

		$rs=$this->db->GetArray($sql);//pr($rs);
		$this->tpl->assign("lang", $rs);
	}
	public function actionSaveGroup(){
		//pr($_POST);exit;
		if(empty($_POST['MUTI_LANG_PK'])) $_POST['MUTI_LANG_PK']=$_POST['GROUP_NAME'];
		///多语pk
		$sql = "delete from EHR_PROGRAM_COLUMN_LANG where MUTI_LANG_PK='".$_POST['MUTI_LANG_PK']."'";
		$ok=@$this->db->execute($sql);
        foreach($_POST['LANG_CODE'] as $key=>$lang){
        	$MUTI_LANG='GROUP_DESC_'.$lang;
        	$muti_desc=empty($_POST[$MUTI_LANG])?$_POST['MUTI_LANG_PK']:$_POST[$MUTI_LANG];
	        $sql="
	                  INSERT INTO EHR_PROGRAM_COLUMN_LANG(
	                        UICULTURE_CODE,
	                        PROMPT_TEXT,
	                        MUTI_LANG_PK
	                  ) VALUES (
	                        '".$lang."',
	                        '".$muti_desc."',
	                        '".$_POST['MUTI_LANG_PK']."'
	                   )
	               ";
	        //echo sql;
	        $ok=@$this->db->execute($sql);
        }
        //delete 
        $sql="delete from EHR_PROGRAM_SETUP_GROUP epsg
			  where epsg.program_no='".$_POST['PROGRAM_NO']."'
				  and epsg.GROUP_ID='".$_POST['GROUP_ID']."'
			 ";
        $ok=@$this->db->execute($sql);
        //insert 
        $sql="
        	insert into EHR_PROGRAM_SETUP_GROUP
			(
			  PROGRAM_NO,
			  GROUP_ID,
			  GROUP_NAME,
			  GROUP_DESC,
			  MUTI_LANG_PK,
			  SORT_SEQ
			) values (
			  '".$_POST['PROGRAM_NO']."',
			  '".$_POST['GROUP_ID']."',
			  '".$_POST['GROUP_NAME']."',
			  '".$_POST['GROUP_NAME']."',
			  '".$_POST['MUTI_LANG_PK']."',
			  '".$_POST['SORT_SEQ']."'
			)
			";
        //echo $sql;exit;
        $ok=@$this->db->execute($sql);
		if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			 //echo '<script type="text/javascript">window.close();</script>';
			 $_GET['PROGRAM_NO']=$_POST['PROGRAM_NO'];
			 $this->actionListGroup();
		}
	}
	public function actionDeleteGroup(){
		//pr($_POST);exit;
		$sql="delete from EHR_PROGRAM_SETUP_GROUP epsg
			  where epsg.program_no='".$_POST['PROGRAM_NO']."'
				  and epsg.GROUP_ID='".$_POST['GROUP_ID']."'
			 ";
		$ok=@$this->db->execute($sql);
		if (!$ok){
			showMsg('Delete failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			 //echo '<script type="text/javascript">window.close();</script>';
			 $_GET['PROGRAM_NO']=$_POST['PROGRAM_NO'];
			 return $this->actionListGroup();
		}
		
	}
	public function actionListTable(){
		$sql="select   
				  PROGRAM_NO,
				  TABLE_NAME,
				  TABLE_ALLIES_NAME,
				  IS_DEFINE,
				  RELATION_SQL,
				  REMARKS
				from
				  EHR_PROGRAM_SETUP_TABLE
				where
				  PROGRAM_NO='".$_GET['PROGRAM_NO']."'
			";
		$rs= $this->db->GetArray($sql);
		$this->tpl->assign('PROGRAM_NO',$_GET['PROGRAM_NO']);
		/*
		if(count($rs)==0){
			$_POST['PROGRAM_NO']=$_GET['PROGRAM_NO'];
			return $this->actionNewTable();
		}
		*/
		//pr($rs);exit;
		$this->tpl->assign('list',$rs);
		$this->tpl->assign('show','List');
	}
	public function actionNewTable(){
		//pr($_POST);

		$this->tpl->assign('row',$_POST);
		$this->tpl->assign('show','New');
	}
	public function actionEditTable(){
		$sql="select   
				  PROGRAM_NO,
				  TABLE_NAME,
				  TABLE_ALLIES_NAME,
				  IS_DEFINE,
				  RELATION_SQL,
				  REMARKS
				from
				  EHR_PROGRAM_SETUP_TABLE
				where
				  PROGRAM_NO='".$_POST['PROGRAM_NO']."'
			  and TABLE_ALLIES_NAME='".$_POST['TABLE_ALLIES_NAME']."'
			";
		$rs= $this->db->GetRow($sql);
		//pr($rs);exit;
		$this->tpl->assign('row',$rs);
		$this->tpl->assign('show','Edit');
	}
	public function actionSaveTable(){
		//delete 
		if(!empty($_POST['OLD_TABLE_ALLIES_NAME'])){
		        $sql="delete
						from
						  EHR_PROGRAM_SETUP_TABLE
						where
						  PROGRAM_NO='".$_POST['PROGRAM_NO']."'
					  and TABLE_ALLIES_NAME='".$_POST['OLD_TABLE_ALLIES_NAME']."'
					";
		        $ok=@$this->db->execute($sql);
		}
        
        //insert 
        $sql="
        	insert into EHR_PROGRAM_SETUP_TABLE
			(
				  PROGRAM_NO,
				  TABLE_NAME,
				  TABLE_ALLIES_NAME,
				  IS_DEFINE,
				  RELATION_SQL,
				  REMARKS
			) values (
			  '".$_POST['PROGRAM_NO']."',
			  '".$_POST['TABLE_NAME']."',
			  '".$_POST['TABLE_ALLIES_NAME']."',
			  '".$_POST['RELATION_SQL']."',
			  '".$_POST['IS_DEFINE']."',
			  '".$_POST['REMARKS']."'
			)
			";
        //echo $sql;exit;
        $ok=@$this->db->execute($sql);
		if (!$ok){
			showMsg('Insert failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			 //echo '<script type="text/javascript">window.close();</script>';
			 $_GET['PROGRAM_NO']=$_POST['PROGRAM_NO'];
			 return $this->actionListTable();
		}
	}
	public function actionDeleteTable(){
		$table_name=empty($_POST['TABLE_NAME'])?'':$_POST['TABLE_NAME'];
		$table_allies_name=empty($_POST['TABLE_ALLIES_NAME'])?'':$_POST['TABLE_ALLIES_NAME'];
		
		$sql="delete from EHR_PROGRAM_SETUP_COLUMN epsc where 
					epsc.PROGRAM_NO='".$_POST['PROGRAM_NO']."'
					and upper(epsc.TABLE_NAME)=upper('".$table_allies_name."')
			";
		$ok=@$this->db->execute($sql); //echo $sql;
		if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}
        $sql="delete
				from
				  EHR_PROGRAM_SETUP_TABLE
				where
				  PROGRAM_NO='".$_POST['PROGRAM_NO']."'
			 ";
        if(!empty($table_allies_name)) $sql .=" and TABLE_ALLIES_NAME='".$table_allies_name."'";
        $ok=@$this->db->execute($sql);
        
        //汇部报表
        $is_summary_rpt=(substr($table_name,0,16)=='HCP_SUMMARY_KPI_');
		if($is_summary_rpt){
			$sql="delete from HCP_MUTI_LANG_PK where muti_lang_pk like '".$table_name."%'";
			$ok=@$this->db->execute($sql);
		}
		if (!$ok){
			showMsg('Delete failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			 //echo '<script type="text/javascript">window.close();</script>';
			 $_GET['PROGRAM_NO']=$_POST['PROGRAM_NO'];
			 return $this->actionListTable();
		}
	}
	public function actionListColumn(){
		if(empty($_GET['PROGRAM_NO']) && !empty($_POST['PROGRAM_NO'])) 
		{
			$_GET['PROGRAM_NO'] = $_POST['PROGRAM_NO'];
		}
		pr($_GET);
		/*
		$sql="select 
					PROGRAM_NO,                                                                                             
					TABLE_NAME,                                                                                   
					COLUMN_NAME,                                                                         
					DATA_TYPE,                                                                            
					ALLOW_SORTING,                                                                                               
					WIDTH,                                                                               
					HEIGHT,                                                                                          
					ALIGN,                                                                                       
					CLASS_NAME,                                                                            
					FORMAT_STR,                                                                                                   
					COLUMN_TYPE,                                                                                    
					BGCOLOR,                                                                                   
					FONT_COLOR,                                                                                             
					FONT_NAME,                                                                                                 
					CHECKED_VALUE,                                                                                          
					DATA_SOURCE,
					MUTI_LANG_PK, 
					(select                    
						  HMLP.PROMPT_TEXT                  
					 from 
						  HCP_MUTI_LANG_PK hmlp
					 where 
						hmlp.muti_lang_pk=epsc.MUTI_LANG_PK
						and hmlp.uiculture_code='".$_SESSION['user']['language']."'
						and rownum=1
					)  COLUMN_DESC,                                                                                               
					COLUMN_SEQ,
					DISPLAY, 
					ALLOW_QUERYING,
					IS_RANG_CONDITION,
					QUERY_COLUMN_TYPE,
					DATA_SOURCE_TYPE,
					GROUP_ID ,                           
					COLUMN_ACTUAL_VALUE
				from 
					EHR_PROGRAM_SETUP_COLUMN epsc
				where 
					epsc.PROGRAM_NO='".$_GET['PROGRAM_NO']."'
				order by epsc.COLUMN_SEQ,epsc.TABLE_NAME,epsc.COLUMN_NAME
			";*/
		$sql = <<<eof
			select program_no,
			       table_name,
			       column_name,
			       data_type,
			       allow_sorting,
			       width,
			       height,
			       align,
			       class_name,
			       format_str,
			       column_type,
			       bgcolor,
			       font_color,
			       font_name,
			       checked_value,
			       data_source,
			       muti_lang_pk,
			       (select hmlp.prompt_text
			          from hcp_muti_lang_pk hmlp
			         where hmlp.muti_lang_pk = epsc.muti_lang_pk
			           and hmlp.uiculture_code = :lang
			           and rownum = 1) column_desc,
			       column_seq,
			       display,
			       allow_querying,
			       is_rang_condition,
			       query_column_type,
			       data_source_type,
			       group_id,
			       column_actual_value
			  from ehr_program_setup_column epsc
			 where epsc.program_no = :program_no
			 order by epsc.column_seq, epsc.table_name, epsc.column_name
		
eof;
		$this->db->debug = true;
		$rs= $this->db->GetArray($sql,array('lang'=>$_SESSION['user']['language'],
											'program_no'=>$_GET['program_no']));
		/*
		if (count($rs)==0)
		{
			//$this->actionExtendColumn();
		}*/
		pr($rs);
		$this->tpl->assign('list',$rs);
		$this->tpl->assign('show','List');
		$this->tpl->assign('PROGRAM_NO',$_GET['PROGRAM_NO']);
	}
	public function actionEditColumn(){
		//pr($_POST);
		$sql="select 
					PROGRAM_NO,                                                                                             
					TABLE_NAME,                                                                                   
					COLUMN_NAME,                                                                         
					DATA_TYPE,                                                                            
					ALLOW_SORTING,                                                                                               
					WIDTH,                                                                               
					HEIGHT,                                                                                          
					ALIGN,                                                                                       
					CLASS_NAME,                                                                            
					FORMAT_STR,                                                                                                   
					COLUMN_TYPE,					                                                                               
					BGCOLOR,                                                                                   
					FONT_COLOR,                                                                                             
					FONT_NAME,                                                                                                 
					CHECKED_VALUE,                                                                                          
					DATA_SOURCE,
					MUTI_LANG_PK,                                                                                              
					COLUMN_SEQ,
					DISPLAY, 
					ALLOW_QUERYING,
					IS_RANG_CONDITION,					
					QUERY_COLUMN_TYPE,
					DATA_SOURCE_TYPE,
					GROUP_ID ,                           
					COLUMN_ACTUAL_VALUE
				from 
					EHR_PROGRAM_SETUP_COLUMN epsc
				where 
					epsc.PROGRAM_NO='".$_POST['PROGRAM_NO']."'
					and epsc.TABLE_NAME='".$_POST['TABLE_NAME']."'
					and epsc.COLUMN_NAME='".$_POST['COLUMN_NAME']."'
			";
		$colsRow= $this->db->GetRow($sql);//echo $sql;//pr($rs);exit;
		$this->tpl->assign('row',$colsRow);
		$this->tpl->assign('show','Edit');
		
		// 多语列表  --  栏位描述式	
		$sql=" SELECT  lang.language_code   ID,
		               lang.language_name   TEXT,
		               (select HMLP.PROMPT_TEXT    
		                from  HCP_MUTI_LANG_PK   hmlp
		                where hmlp.muti_lang_pk='".$colsRow['MUTI_LANG_PK']."'
		                  and hmlp.uiculture_code=lang.language_code
		                  and rownum=1
		               )     VALUE
		 		FROM   EHR_MULTILANG_LIST  lang
			  ";
		$rs=$this->db->GetArray($sql);//echo $sql;//pr($rs);exit;
		$this->tpl->assign("lang", $rs);
		$this->tpl->assign('PROGRAM_NO',$_POST['PROGRAM_NO']);

		// 查询条件类型
		$this->getOptionMutiLangHtml('COLUMN_QUERY_TYPE',$colsRow['QUERY_COLUMN_TYPE'],'column_query_type_option',$_SESSION['user']['language']);
		// 资料来源类型
		$this->getOptionMutiLangHtml('COLUMN_QUERY_DATA_SOURCE',$colsRow['DATA_SOURCE_TYPE'],'column_query_datasource_option',$_SESSION['user']['language']);
	}
	public function actionDeleteColumn(){
		//pr($_POST);//exit;
		$sql="delete from EHR_PROGRAM_SETUP_COLUMN epsc where 
					epsc.PROGRAM_NO='".$_POST['PROGRAM_NO']."'
					and epsc.TABLE_NAME='".$_POST['TABLE_NAME']."'
					and epsc.COLUMN_NAME='".$_POST['COLUMN_NAME']."'
			";
		//echo "<textarea>".$sql."</textarea>";
		$ok=@$this->db->execute($sql);
		if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
		    $_GET['PROGRAM_NO']=$_POST['PROGRAM_NO'];
			$this->actionListColumn();
		}
	}
	public function actionSaveColumn($callFrom=''){
		//pr($_POST);
        $sql="delete from EHR_PROGRAM_SETUP_COLUMN epsc where 
					epsc.PROGRAM_NO='".$_POST['PROGRAM_NO']."'
					and epsc.TABLE_NAME='".$_POST['TABLE_NAME']."'
					and epsc.COLUMN_NAME='".$_POST['COLUMN_NAME']."'
			";
		//echo "<textarea>".$sql."</textarea>";
		@$this->db->execute($sql);
		
		$DISPLAY=empty($_POST['DISPLAY'])?'0':'1';
		$ALLOW_SORTING=empty($_POST['ALLOW_SORTING'])?'0':'1';
		$ALLOW_QUERYING=empty($_POST['ALLOW_QUERYING'])?'0':'1';
		$IS_RANG_CONDITION_LABEL=empty($_POST['IS_RANG_CONDITION_LABEL'])?'0':'1';
		$sql="insert into  EHR_PROGRAM_SETUP_COLUMN ( 
					PROGRAM_NO,                                                                                             
					TABLE_NAME,                                                                                   
					COLUMN_NAME,                                                                         
					DATA_TYPE,                                                                            
					ALLOW_SORTING,                                                                                               
					WIDTH,                                                                               
					HEIGHT,                                                                                          
					ALIGN,                                                                                       
					CLASS_NAME,                                                                            
					FORMAT_STR,                                                                                                   
					COLUMN_TYPE,                                                                                    
					BGCOLOR,                                                                                   
					FONT_COLOR,                                                                                             
					FONT_NAME,                                                                                                 
					CHECKED_VALUE,                                                                                          
					DATA_SOURCE,
					MUTI_LANG_PK,                                                                                              
					COLUMN_SEQ,
					DISPLAY, 
					ALLOW_QUERYING,
					
					IS_RANG_CONDITION,
					QUERY_COLUMN_TYPE,
					DATA_SOURCE_TYPE,
					GROUP_ID ,                           
					COLUMN_ACTUAL_VALUE
				) values (
					'".$_POST['PROGRAM_NO']."',
					'".$_POST['TABLE_NAME']."',                                                                                   
					'".$_POST['COLUMN_NAME']."',                                                                         
					'".$_POST['DATA_TYPE']."',                                                                            
					'".$ALLOW_SORTING."',                                                                                               
					'".$_POST['WIDTH']."',                                                                               
					'".$_POST['HEIGHT']."',                                                                                          
					'".$_POST['ALIGN']."',                                                                                       
					'".$_POST['CLASS_NAME']."',                                                                            
					'".$_POST['FORMAT_STR']."',                                                                                                   
					'".$_POST['COLUMN_TYPE']."',                                                                                    
					'".$_POST['BGCOLOR']."',                                                                                   
					'".$_POST['FONT_COLOR']."',                                                                                             
					'".$_POST['FONT_NAME']."',                                                                                                 
					'".$_POST['CHECKED_VALUE']."',                                                                                          
					'".str_ireplace("'","''",$_POST['DATA_SOURCE'])."',
					'".$_POST['MUTI_LANG_PK']."',                                                                                              
					'".$_POST['COLUMN_SEQ']."',
					'".$DISPLAY."', 
					'".$ALLOW_QUERYING."',
					
					'".$IS_RANG_CONDITION_LABEL."',
					'".$_POST['QUERY_COLUMN_TYPE']."',
					'".$_POST['DATA_SOURCE_TYPE']."',
					'".$_POST['GROUP_ID']."',                           
					'".str_ireplace("'","''",$_POST['COLUMN_ACTUAL_VALUE'])."'
			  )
			";
		  // ereg_replace("'","''",$_POST['COLUMN_ACTUAL_VALUE'])
		//echo "<textarea>".$sql."</textarea>";exit;
		$ok=@$this->db->execute($sql);
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			if($err_no=='1')  showMsg('提示: 主键冲突。','error');
			
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}
		
		///栏位描述多语支持
		$sql="delete from HCP_MUTI_LANG_PK where muti_lang_pk='".$_POST['MUTI_LANG_PK']."'";
		@$this->db->execute($sql);
		foreach($_POST['LANG_CODE'] as $key=>$lang){
        	$multi_desc='COLUMN_DESC_'.$lang;
        	$COLUMN_DESC=empty($_POST[$multi_desc])?$_POST['COLUMN_NAME']:$_POST[$multi_desc];
        	$sql="insert into HCP_MUTI_LANG_PK (
        						muti_lang_pk,
        						uiculture_code,
        						PROMPT_TEXT,
								CREATE_DATE,
								CREATE_BY,
								CREATE_PROGRAM
        					) values (
        					    '".$_POST['MUTI_LANG_PK']."',
        					    '".$lang."',
        					    '".$COLUMN_DESC."',
        					    sysdate,
        					    'ehr',
        					    'umd_program_column'
        					)
        		  ";
	        //echo $sql;
	        $ok=@$this->db->execute($sql);
        }
        
		if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
		    $_GET['PROGRAM_NO']=$_POST['PROGRAM_NO'];
			$this->actionListColumn();
		}
	}
	public function actionExtendColumn(){
		$program_no = $_GET['PROGRAM_NO'];
		$sql = <<<eof
			select program_no,
			       table_name,
			       table_allies_name,
			       is_define,
			       relation_sql,
			       remarks
			  from ehr_program_setup_table
			 where program_no = :program_no
eof;
		$tabs = $this->db->GetArray($sql,array('program_no'=>$program_no));
		$cnt = count($tabs);
		if($cnt == 0 ){
			showMsg('请先设定数据来源再展开栏位.<br><br>'.$this->db->ErrorMsg(),'error');
		}
		
		/**
		 * Get Oracle Table Columns Information Sample
		 select t1.column_name,
		       decode(data_type,
		              'VARCHAR2',
		              '文本',
		              'NUMBER',
		              '数字',
		              'DATE',
		              '日期',
		              'FLOAT',
		              '浮点数',
		              'DOUBLE',
		              data_type) as dtype,
		       data_length,
		       substr(data_type || '(' || data_length || ')', 0, 20) as data_type,
		       decode(nullable, 'N', '非空白', '') as null_status,
		       comments
		  from all_tab_columns t1, all_col_comments t2
		 where t1.table_name = t2.table_name
		   and t1.column_name = t2.column_name
		   and t1.table_name = 'GL_SEGMENT'
		 ORDER BY column_id;
		 */
		
		for($i=0;$i<$cnt;$i++){
			$cols_sql = <<<eof
				select table_name, column_name, data_type, data_length, nullable, column_id
				  from all_tab_cols
				 where owner = 'HCP'
				   and table_name = upper(:tabname)
eof;
			//处理汇总报表
			$is_summary_rpt=(substr($tabs[$i]['TABLE_NAME'],0,16)=='HCP_SUMMARY_KPI_');
			if($is_summary_rpt){
				$sql="select * from :tabname k  where k.flag='1'";
				$colsLabel=$this->db->GetRow($sql);// 取column label
				//如果是汇总报表，只取COL开头的栏位
				$cols_sql .= " and column_name like 'COL%'";
			}
			$cols = $this->db->GetArray($cols_sql,array('tabname'=>$tabs[$i]['TABLE_NAME']));
			
			$sql_cols_cnt = <<<eof
				select column_name, count(1) as cnt
				  from ehr_program_setup_column
				 where upper(program_no) = upper(:program_no)
				   and upper(table_name) = upper(:tab_allies_name)
				 group by column_name
eof;
			$cols_cnt = $this->db->GetArray($sql_cols_cnt,array('program_no'=>$program_no,
																'tab_allies_name'=>$tabs[$i]['TABLE_ALLIES_NAME']));
			$tab_cols = '';
			foreach ($cols_cnt as $val)
			{
				$tab_cols[$val['COLUMN_NAME']] = $val['CNT'];
			}
			foreach ($cols as $key=>$rowCols){
				/*
				$sql="select count(*) col_count
						from 
							EHR_PROGRAM_SETUP_COLUMN epsc
						where 
							upper(epsc.PROGRAM_NO)=upper('".$_GET['PROGRAM_NO']."')
							and upper(epsc.TABLE_NAME)=upper('".$tabs[$i]['TABLE_ALLIES_NAME']."')
							and upper(epsc.COLUMN_NAME)=upper('".$rowCols['COLUMN_NAME']."')
					  ";
				 
				$sql = <<<eof
				select count(*) cnt
				  from ehr_program_setup_column
				 where upper(program_no) = upper(:program_no)
				   and upper(table_name) = upper(:tab_allies_name)
				   and upper(column_name) = upper(:col_name)
eof;
				$countCols= $this->db->GetOne($sql,array('program_no'=>$program_no,
														 'tab_allies_name'=>$tabs[$i]['TABLE_ALLIES_NAME'],
														 'col_name'=>$rowCols['COLUMN_NAME']));
				*/
				//echo $countCols."<br>";
				//pr($tab_cols);
				if(isset($tab_cols[strtoupper($rowCols['COLUMN_NAME'])])) continue;
				$row=array(
				    'PROGRAM_NO'=>$_GET['PROGRAM_NO'],
				    'TABLE_ALLIES_NAME'=>$tabs[$i]['TABLE_ALLIES_NAME'],
				    'TABLE_NAME'=>$rowCols['TABLE_NAME'],
				    'COLUMN_NAME'=>$rowCols['COLUMN_NAME'],
				    'DATA_TYPE'=>$rowCols['DATA_TYPE'],
				    'DISPLAY'=>$is_summary_rpt?'1':'0',
				    'COLUMN_SEQ'=>$key*10,
				    'MUTI_LANG_PK'=>$rowCols['TABLE_NAME'].'.'.$rowCols['COLUMN_NAME'],
					'MUTI_LANG_VALUE'=>empty($colsLabel[$rowCols['COLUMN_NAME']])?$rowCols['COLUMN_NAME']:$colsLabel[$rowCols['COLUMN_NAME']],
				);
				$this->_insertColumn($row);
			}
		}
		$this->actionListColumn();
	}
	protected   function _insertColumn($row){
		$sql="insert into  EHR_PROGRAM_SETUP_COLUMN ( 
					PROGRAM_NO,                                                                                             
					TABLE_NAME,                                                                                   
					COLUMN_NAME,                                                                         
					DATA_TYPE,                                                                            
					ALLOW_SORTING,  
					                                                                                             
					WIDTH,                                                                               
					HEIGHT,                                                                                          
					ALIGN,                                                                                       
					CLASS_NAME,                                                                            
					FORMAT_STR,   
					                                                                                                
					COLUMN_TYPE,                                                                                    
					BGCOLOR,                                                                                   
					FONT_COLOR,                                                                                             
					FONT_NAME,                                                                                                 
					CHECKED_VALUE,    
					                                                                                      
					DATA_SOURCE,
					MUTI_LANG_PK,                                                                                              
					COLUMN_SEQ,
					DISPLAY, 
					ALLOW_QUERYING,
					
					IS_RANG_CONDITION,
					QUERY_COLUMN_TYPE,
					DATA_SOURCE_TYPE,
					GROUP_ID ,                           
					COLUMN_ACTUAL_VALUE
					
				) values (
					upper('".$row['PROGRAM_NO']."'),
					upper('".$row['TABLE_ALLIES_NAME']."'),                                                                                   
					upper('".$row['COLUMN_NAME']."'),                                                                         
					upper('".$row['DATA_TYPE']."'),                                                                            
					'0', 
					                                                                                              
					null,                                                                               
					null,                                                                                          
					null,                                                                                       
					null,                                                                            
					null,     
					                                                                                              
					null,                                                                                    
					null,                                                                                   
					null,                                                                                             
					null,                                                                                                 
					null,   
					                                                                                       
					null,
					upper('".$row['MUTI_LANG_PK']."'),                                                                                              
					'".$row['COLUMN_SEQ']."',
					'".$row['DISPLAY']."', 
					null,
					
					'',
					'TEXT',
					'',
					'',                           
					''
			  )
			";
		//echo $sql;
		$ok=$this->db->exeCute($sql);
		if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}
		
		$arrLang= $this->db->GetArray("select * from EHR_MULTILANG_LIST");
		foreach ($arrLang as $key=>$value){
			$sql=" select count(1) countlang from HCP_MUTI_LANG_PK
			        where upper(muti_lang_pk)=upper('".$row['MUTI_LANG_PK']."')
			          and upper(uiculture_code)=upper('".$value['LANGUAGE_CODE']."')
				";
			$countlangs=$this->db->GetOne($sql);
			//echo $sql."<br>".$countlangs."<br>";
			if($countlangs>0) continue;
			$sql="insert into HCP_MUTI_LANG_PK (
        						muti_lang_pk,
        						uiculture_code,
        						PROMPT_TEXT,
								CREATE_DATE,
								CREATE_BY,
								CREATE_PROGRAM
        					) values (
        					    upper('".$row['MUTI_LANG_PK']."'),
        					    upper('".$value['LANGUAGE_CODE']."'),
        					    upper('".$row['MUTI_LANG_VALUE']."'),
        					    sysdate,
        					    'ehr',
        					    'eHr_umd_guide'
        					)
        		  ";
			//echo $sql;
			$ok=$this->db->exeCute($sql);
			if (!$ok){
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}
		}
	}
	public function actionEdit()
	{
		$function_id = $_GET['FUNCTION_ID'];
		$child_id = $_GET['CHILD_ID'];
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
		//$this->tpl->assign ( "menuCodeReadonly", 'readonly');
		//pr($arr);
	}
	public function actionDelete()
	{
		//pr($GLOBALS);exit;
		$sql="
                 DELETE FROM APP_MUTI_LANG
                 where name='".$child_id."' 
                 and program_no='HCP'
                 and type_code='MT'
                ";
		$ok = $this->db->Execute($sql);
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
	public function getMenuTree($parent_id = '0', $spacing = '', $exclude = '', $menu_tree_array = '', $include_itself = false , $include_leaf = false) {
	
	    if (!is_array($menu_tree_array)) $menu_tree_array = array();
	    if ( (sizeof($menu_tree_array) < 1) && ($exclude != '0') ) $menu_tree_array[] = array('ID' => '', 'TEXT' => "Select One", 'TYPE' => 'LEAF');
	
	    if ($include_itself) {
	      $menu_title = $this->db->GetOne("select value  from  app_muti_lang 
							      				where name='" . $parent_id . "'
								                  and program_no='HCP'
								                  and lang_code='".$_SESSION['user']['language']."' 
								                  and type_code='MT'"
								                );			                
	      $menu_tree_array[] = array('ID' => $parent_id, 'TEXT' => $menu_title, 'TYPE' => 'MENU');

	    }
	    $sql=" select T.FUNCTION_ID,T.CHILD_ID,T.CHILD_TYPE
	             from APP_FUNCTIONS  T
			    where T.function_id = '".$parent_id."'
				  AND T.seg_segment_no='".$_SESSION['user']['company_id']."'
				  ".(($include_leaf)?'':" AND T.CHILD_TYPE='MENU' ")."
				  AND T.CHILD_ID <> 'MDNA'
			  ORDER BY  T.P_PRIOR";
	    $rs = $this->db->GetArray($sql);
	    //echo $sql.'<br>';
	    //pr($rs);exit;
	    foreach($rs as $key=>$value) {
	       /*
		  $tree_title='';//get_name_value('menu_title',TABLE_MENU_DESCRIPTION,'menu_id',$categories['menu_id'],true);
		  $tree_title = empty($tree_title)?$row['menu_title']:$tree_title;
	      if ($exclude != $categories['menu_id']) 
			  $menu_tree_array[] = array('id' => $categories['menu_id'], 
										 'text' => $spacing . $tree_title);
	      $menu_tree_array = gf_get_menu_tree($categories['menu_id'], $spacing . '&nbsp;&nbsp;&nbsp;&nbsp;', $exclude, $menu_tree_array);
           */
	    	//pr($value);
	    	$tree_title = $this->db->GetOne("select value  from  app_muti_lang 
							      				where name='" . $value['CHILD_ID'] . "'
								                  and program_no='HCP'
								                  and lang_code='".$_SESSION['user']['language']."' 
								                  and type_code='MT'"
								                );
			if ($exclude != $value['CHILD_ID']) $menu_tree_array[] = array('ID' => $value['CHILD_ID'], 'TEXT' => $spacing . $tree_title, 'TYPE' =>$value['CHILD_TYPE'] );
			if($value['CHILD_TYPE']=='MENU') $menu_tree_array = $this->getMenuTree($value['CHILD_ID'], $spacing . '&nbsp;&nbsp;&nbsp;&nbsp;', $exclude, $menu_tree_array,false,$include_leaf);
	    }
	
	    return $menu_tree_array;
	}
	public function getDropDownListHtml($list,$select_value,$optgroup='no',$pre_null='N'){
		$html='';
		if($pre_null=='Y') $html="<option value=''></option>";
		foreach ($list as $key=>$row){
			$selected=($row['ID']==$select_value)?'selected':'';
			if($optgroup=='show' && !empty($row['TYPE']) && $row['TYPE']=='MENU'){
				$html.="<optgroup label='".$row['TEXT']."'></optgroup>\r\n";
			}else{
				$html.="<option value='".$row['ID']."'  ".$selected.">".$row['TEXT']."</option>\r\n";
			}
		}
		return $html;
	}
	//取下拉清单
	public function getOptionMutiLangHtml($lang_name,$selected_value,$html_name,$lang_code='ZHS'){
		$sql="select seq as ID, value as TEXT
			  from app_muti_lang
			 where name = '".$lang_name."'
			   and lang_code = '".$lang_code."'
			   and type_code = 'LL'
		   ";
		$rs=$this->db->GetArray($sql);//echo $sql;pr($rs);exit;
		$column_query_type_option=$this->getDropDownListHtml($rs,$selected_value,'no','Y');
		$this->tpl->assign($html_name, $column_query_type_option);
	}

}
