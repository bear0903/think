<?php
/*
 * 普通查询面基类
 * 多语注册名为：MDN0000
 *  create by boll 2008-10-05
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAction.class.php $
 *  $Id: AresAction.class.php 3486 2013-03-22 03:40:47Z dennis $
 *  $Rev: 3486 $ 
 *  $Date: 2013-03-22 11:40:47 +0800 (周五, 22 三月 2013) $
 *  $LastChanged: boll $   
 *  $LastChangedDate: 2013-03-22 11:40:47 +0800 (周五, 22 三月 2013) $
 \****************************************************************************/

class AresAction {
	public $db;
	public $tpl;
	/**
	 * get val form session once
	 * add by dennis 2013-03-20
	 * @var string
	 */
	protected $_companyId,$_userSeqno,$_userEmpId,$_userEmpSeqno;
	
	public function __construct(){
		global $g_db_sql,$g_tpl;
		$this->db = $g_db_sql;
		$this->tpl= $g_tpl;
		$this->_companyId = $_SESSION['user']['company_id'];
		$this->_userEmpId = $_SESSION['user']['emp_id'];
		$this->_userEmpSeqno = $_SESSION['user']['emp_seq_no'];
		$this->_userSeqno = $_SESSION['user']['user_seq_no'];
	}
	/**
	 * 
	 * Dynamic run the action function according action name
	 */	
	public function run(){
		if(!empty($_GET['do'])) $action=$_GET['do'];
		if(!empty($_POST['do'])) $action=$_POST['do'];
		$action = empty($action)? 'List' :$action;
		$func = 'action'.$action;
		if (method_exists($this,$func))
		$this->$func();
	}
}// end AresAction
