<?php
/*
 *    mappng  APPF326
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/umd_guide1_DB.php $
 *  $Id: umd_guide1_DB.php 3552 2013-09-28 07:38:38Z dennis $
 *  $Rev: 3552 $ 
 *  $Date: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *********************************************************/

class UmdGuide extends AresAction 
{
	public $program_name = "train_enter_for_DB.php";
	public $sql;

	public function actionQuery(){
		//print(getMultiLangMsg('ESNS002','ZHS','GROUP_COMMENTS'));exit;
		//pr($this->getMenuTree('MDN','','0','',true));exit;
		//pr($_SESSION);exit;
		//echo 'test';exit;
		if(empty($_POST['PROGRAM_NO']) && !empty($_GET['PROGRAM_NO'])) $_POST['PROGRAM_NO']=$_GET['PROGRAM_NO'];
		$nullArr[]=array('ID' => '', 'TEXT' => "Select One");
		$esnArr=$this->getMenuTree('ESN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true,true);
		$mdnArr=$this->getMenuTree('MDN','&nbsp;&nbsp;&nbsp;&nbsp;','0','',true,true);
		$menuSelected = empty($_POST['PROGRAM_NO'])?'':$_POST['PROGRAM_NO'];
		$html='';
		$html.=$this->getDropDownListHtml($esnArr,$menuSelected,'show');
		$html.=$this->getDropDownListHtml($mdnArr,$menuSelected,'show');
		$this->tpl->assign ( "menuCascade", $html);
		//echo $menuSelected;exit;
		
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
		$next_url="?scriptname=umd_guide2&rand=".rand();
		$this->tpl->assign('next_url',$next_url);
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
	    	$sql="select count(1) CNT from EHR_PROGRAM_SETUP_MASTER C where C.PROGRAM_NO='".$value['CHILD_ID']."'";
	    	$def_cnt=$this->db->GetOne($sql);//'NORMAL,WORKFLOW,QUERY';
		    $sql="select REPORT_APPROVE10 
		        from APP_FILE 
		       where FILENAME='".$value['CHILD_ID']."'";
	    	$SUB_CHILD_TYPE=$this->db->GetOne($sql);//'NORMAL,WORKFLOW,QUERY';
		    if($def_cnt==0 && $value['CHILD_TYPE']=='FORM' && $SUB_CHILD_TYPE<>'QUERY') continue;
		
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
$udp = new UmdGuide();
$udp->run();

?>