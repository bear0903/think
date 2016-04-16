<?php
/*
 * 多语言设置   mappng  APPF326
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/umd_muti_lang_DB.php $
 *  $Id: umd_muti_lang_DB.php 3552 2013-09-28 07:38:38Z dennis $
 *  $Rev: 3552 $ 
 *  $Date: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *********************************************************/

class umdMutiLang extends AresAction 
{
	public $program_name = "train_enter_for_DB.php";
	public $sql;
	
	public function __construct()
	{
		parent::__construct(); //pr($_SESSION);exit;
		/*
		if(!empty($_GET['PROGRAM_NO'])) $program_no=$_GET['PROGRAM_NO'];
		if(!empty($_POST['PROGRAM_NO'])) $program_no=$_POST['PROGRAM_NO'];
		if(!empty($program_no)){
			$sql="
	               SELECT 
			              B.VALUE  CHILD_NAME
				     FROM APP_MUTI_LANG B 
					WHERE B.NAME = '".$program_no."'
					      AND B.PROGRAM_NO = 'HCP'
					      AND B.LANG_CODE = '".$_SESSION['user']['language']."'
					      AND B.TYPE_CODE = 'MT'
				";
			//echo $sql;
			$CHILD_NAME=$this->db->getOne($sql);
			//pr($rs);exit;
			$this->tpl->assign('CHILD_NAME',$CHILD_NAME);
		}
		*/
	}
	public function actionQuery(){
		//print(getMultiLangMsg('ESNS002','ZHS','GROUP_COMMENTS'));exit;
		//pr($this->getMenuTree('MDN','','0','',true));exit;
		//pr($_SESSION);exit;
		//echo 'test';exit;
		$nullArr[]=array('ID' => '', 'TEXT' => "Select One");
		$esnArr=$this->getMenuTree('ESN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true,true);
		$mdnArr=$this->getMenuTree('MDN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true,true);
		
		$menuSelected = empty($_POST['PROGRAM_NO'])?'':$_POST['PROGRAM_NO'];
		$html='';
		//$html=$this->getDropDownListHtml($nullArr,'---');
		$html.=$this->getDropDownListHtml($esnArr,$menuSelected);
		$html.=$this->getDropDownListHtml($mdnArr,$menuSelected);
		
		$this->tpl->assign ( "menuCascade", $html);

		
		// 多语列表
		$sql=" SELECT  lang.language_code   ID,
		               lang.language_name   TEXT
		 		FROM  EHR_MULTILANG_LIST  lang
			  ";
		$rs=$this->db->getArray($sql);
		$this->tpl->assign("lang", $rs);
		$langSelected = empty($_POST['LANG_CODE'])?$_SESSION['user']['language']:$_POST['LANG_CODE'];
		$this->tpl->assign("langCascade", $this->getDropDownListHtml($rs,$langSelected));
		$this->tpl->assign('show','Query');
	}
	public function actionSave(){	
		//delete 
		$sql="
                 DELETE FROM APP_MUTI_LANG
                 where name='".$_POST['NAME']."'
                 and program_no='".$_POST['PROGRAM_NO']."'
                 and (type_code='IT' or type_code='II')
                ";
		$ok = @$this->db->Execute($sql);
		
        ///多语字段
        $PROGRAM_NO=$_POST['PROGRAM_NO'];
        foreach($_POST['MUTI_LANG'] as $key=>$lang){
        	$ARR_KEY='VALUE_'.$lang;
        	$INPUT_VALUE=empty($_POST[$ARR_KEY])?$_POST['NAME']:$_POST[$ARR_KEY];
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
	                        '".$PROGRAM_NO."',
	                        '".$lang."',
	                        'IT',
	                        NULL,
	                        upper('".$_POST['NAME']."'),
	                        '".$INPUT_VALUE."',
	                        'SYSTEM',
	                        SYSDATE
	                        )
	                  ";
	        $ok=@$this->db->execute($sql);
	        
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
	                        '".$PROGRAM_NO."',
	                        '".$lang."',
	                        'II',
	                        NULL,
	                        upper('".$_POST['NAME']."'),
	                        '".$INPUT_VALUE."',
	                        'SYSTEM',
	                        SYSDATE
	                        )
	                  ";
	        $ok=@$this->db->execute($sql);
        }
        
		if (!$ok){
			$err_no=$this->db->ErrorNo();
			
			showMsg('Update failure.<br><br>'.$this->db->ErrorMsg(),'error');
		}else{
			$this->actionList();
		}
		
	}

	public function actionList(){
		//PR($_POST);exit;
		$this->actionQuery();
		$PROGRAM_NO = $_POST['PROGRAM_NO'];
		$LANG_CODE=empty($_POST['LANG_CODE'])?$_SESSION['user']['language']:$_POST['LANG_CODE'];
		$sql="
			  select 
					  aml.PROGRAM_NO,
					  aml.LANG_CODE,
					  aml.TYPE_CODE,
					  aml.SEQ,
					  aml.NAME,
					  aml.VALUE,
					  aml.UPDATE_BY,
					  aml.UPDATE_DATE
			  from 
			  		  APP_MUTI_LANG  aml
			  where   aml.PROGRAM_NO='".$PROGRAM_NO."'
					  and aml.LANG_CODE='".$LANG_CODE."'
					  and aml.TYPE_CODE='IT'
		   order by   aml.NAME
			 ";
		$rs=$this->db->getArray($sql);
		$this->tpl->assign('list',$rs);
		$this->tpl->assign('show','List');
		$this->tpl->assign('LANG_CODE',$LANG_CODE);
	}
	public function actionNew(){
		// 多语列表
		$sql=" SELECT  lang.language_code   ID,
		               lang.language_name   TEXT
		 		FROM  EHR_MULTILANG_LIST  lang
			  ";
		$rs=$this->db->getArray($sql);
		$this->tpl->assign("lang", $rs);
		$this->tpl->assign("PROGRAM_NO", $_GET['PROGRAM_NO']);
		$LANG_CODE=empty($_GET['LANG_CODE'])?$_SESSION['user']['language']:$_GET['LANG_CODE'];
		$this->tpl->assign('LANG_CODE',$LANG_CODE);
		$this->tpl->assign('show','New');
	}
	
	public function actionEdit()
	{
		$PROGRAM_NO = $_GET['PROGRAM_NO'];
		$NAME  = $_GET['NAME'];
		/*
		$sql="
			  select  distinct
					  lang.language_code   ID,
		              lang.language_name   TEXT,
					  aml.VALUE            VALUE
			  from 
			  		  APP_MUTI_LANG       aml,
			  		  EHR_MULTILANG_LIST  lang
			  where   aml.LANG_CODE(+)=lang.language_code
			  		  and aml.PROGRAM_NO='".$PROGRAM_NO."'
					  and aml.NAME='".$NAME."'
					  and aml.TYPE_CODE='IT'
			 ";
		*/
		$sql="select  lang.language_code   ID,
		              lang.language_name   TEXT,
                      (select aml.VALUE 
                         from APP_MUTI_LANG aml
                       where aml.PROGRAM_NO='".$PROGRAM_NO."'
                          and aml.NAME='".$NAME."'
                          and aml.TYPE_CODE='IT'
                          and aml.LANG_CODE=lang.language_code
                          and rownum=1)       VALUE
			  from 
			  		  EHR_MULTILANG_LIST  lang
			  ";
		$rs = $this->db->GetArray($sql);//echo $sql;
		$this->tpl->assign("lang", $rs);
		
		$LANG_CODE=empty($_GET['LANG_CODE'])?$_SESSION['user']['language']:$_GET['LANG_CODE'];
		$this->tpl->assign('LANG_CODE',$LANG_CODE);
		$this->tpl->assign('NAME',$NAME);
		$this->tpl->assign('PROGRAM_NO',$PROGRAM_NO);
		
		$this->tpl->assign('show','New');
		$this->tpl->assign('name_readonly','readonly');
	}
	public function actionDelete()
	{
		//pr($GLOBALS);exit;
		$sql="
                 DELETE FROM APP_MUTI_LANG
                 where name='".$_GET['NAME']."'
                 and program_no='".$_GET['PROGRAM_NO']."'
                 and (type_code='IT' or type_code='II')
                ";
		$ok = $this->db->Execute($sql);
		if(!$ok){
			exit($this->db->ErrorMsg());
		}else{
			$_POST['PROGRAM_NO']=$_GET['PROGRAM_NO'];
			$this->actionList();
		}
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
		  $P_PROGRAM_NO=$parent_id;
		  if($parent_id=='ESN')  $P_PROGRAM_NO='ESN0000';
		  if($parent_id=='MDN')  $P_PROGRAM_NO='MDN0000';
	      $menu_tree_array[] = array('ID' => $P_PROGRAM_NO, 'TEXT' => $menu_title, 'TYPE' => 'MENU');

	    }
	    $sql=" select T.FUNCTION_ID,T.CHILD_ID,T.CHILD_TYPE
	             from APP_FUNCTIONS  T
			    where T.function_id = '".$parent_id."'
				  AND T.seg_segment_no='".$_SESSION['user']['company_id']."'
				  ".(($include_leaf)?'':" AND T.CHILD_TYPE='MENU' ")."
				  AND T.CHILD_ID <> 'MDNA'
			  ORDER BY  T.P_PRIOR";
	    $rs = $this->db->getArray($sql);
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
	    	$tree_title = $this->db->GetOne("select name ||'-'|| value  from  app_muti_lang 
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
	public function getDropDownListHtml($list,$select_value,$optgroup='no'){
		$html='';
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
}
if(empty($_GET['do']))  $_GET['do']='Query';
/*  controller */
$udp = new umdMutiLang();
$udp->run();

?>