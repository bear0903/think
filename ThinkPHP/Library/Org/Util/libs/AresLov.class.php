<?php
/* program: LOV 通用类  
 * create by boll 2008-11-05
 * Reference: /mgr/includes/train_student_lov_db.php
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresLov.class.php $
 *  $Id: AresLov.class.php 2276 2009-11-12 07:52:30Z dennis $
 *  $Rev: 2276 $ 
 *  $Date: 2009-11-12 15:52:30 +0800 (周四, 12 十一月 2009) $
 *  $LastChanged: boll $   
 *  $LastChangedDate: 2009-11-12 15:52:30 +0800 (周四, 12 十一月 2009) $
 \****************************************************************************/
include_once 'AresAction.class.php';
class AresLov extends AresAction {
	public $db;
	public $tpl;
	public $ListSql;
	public function __construct(){
		parent::__construct();
		global $MD_TEMPLATE;
		$MD_TEMPLATE = 'lov_template';
	}
	
	public function setSql($sql){
		$this->ListSql = $sql;
	}
	
	public function actionList(){
		$search_condition='';
		if(!empty($_GET['search_condition'])) $search_condition=$_GET['search_condition'];
		
		if(!empty($_POST['search_condition'])){
			$search_condition=$_POST['search_condition'];
			$rs= $this->db->GetArray($this->ListSql,
								array('v_search_condition'=>'%'.$search_condition.'%',
								      'v_company_id'=>$_SESSION['user']['company_id']));
		}else {
			$rs= $this->db->GetArray($this->ListSql,
								array('v_search_condition'=>'%'.$search_condition.'%',
								      'v_company_id'=>$_SESSION['user']['company_id']));
		}// end if
		/* remark by dennis 20091112 共用的 LOV 都没有传 v_dept_id 这个变量 , 需要用到的童鞋,请自已修改一下程式
		if(!empty($_POST['search_condition'])){
			$search_condition=$_POST['search_condition'];
			$rs= $this->db->GetArray($this->ListSql,
								array('v_search_condition'=>'%'.$search_condition.'%',
								      'v_company_id'=>$_SESSION['user']['company_id'],
								      'v_dept_id'=>''));
		}else {
			$rs= $this->db->GetArray($this->ListSql,
								array('v_search_condition'=>'%'.$search_condition.'%',
								      'v_company_id'=>$_SESSION['user']['company_id'],
								      'v_dept_id'=>$_SESSION ['user'] ['dept_seqno']));
		}// end if
		*/
		
        								
		//$this->db->debug  =true;
		//$rs= $this->db->GetArray($this->ListSql,
								//array('v_search_condition'=>'%'.$search_condition.'%',
								      //'v_company_id'=>$_SESSION['user']['company_id'],
								      //'v_dept_id'=>$_SESSION ['user'] ['dept_seqno'])
								//);//updated by Gracie at 20090819 先显出本部门的人


		$page_size=30;
		$this->tpl->assign ('search_condition',$search_condition);
		$this->tpl->assign ('show', 'List');
		$len=count($rs);
		if($len>$page_size) array_splice($rs,$page_size,$len);
        $this->tpl->assign ('list', $rs);
        // modify by dennis 20090623
        $msgs = get_multi_lang($GLOBALS['config']['default_lang'],'MDN0000');
        //pr($msgs);
        //$MSG_TOO_MANY_RESULT = get_app_muti_lang('MDN0000','MSG_TOO_MANY_RESULT',$_SESSION['user']['language'],'IT');
        //$MSG_INPUT_CONDITION = get_app_muti_lang('MDN0000','MSG_INPUT_CONDITION',$_SESSION['user']['language'],'IT');
        //$MSG_SEARCH_NO_DATA = get_app_muti_lang('MDN0000','MSG_SEARCH_NO_DATA',$_SESSION['user']['language'],'IT');
        if($len >= $page_size)  
        	$this->tpl->assign ( "search_condition_alert", '<div class="notice">'.$msgs['001'].$page_size.$msgs['002'].'</div>');
        if($len==0)  
        	$this->tpl->assign ( "search_condition_alert", '<div class="notice">'.$msgs['003'].'</div>');
	}
}

?>