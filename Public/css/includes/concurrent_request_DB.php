<?php
/*
 * 并发请求，用户放弃
 * Create by Boll 2009-09-25
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/concurrent_request_DB.php $
 *  $Id: concurrent_request_DB.php 3782 2014-07-11 02:31:20Z dennis $
 *  $Rev: 3782 $ 
 *  $Date: 2014-07-11 10:31:20 +0800 (周五, 11 七月 2014) $
 *  $LastUpdated: boll $   
 *  $LastChangedDate: 2014-07-11 10:31:20 +0800 (周五, 11 七月 2014) $
 *********************************************************/

class Concurrent extends AresAction 
{
	public function __construct()
	{
		parent::__construct(); //父类的初始化函数
		//在这里加上你的要初始化的代码
	}

	public function actionCancel(){
		
		$request_no=$_GET['request_no'];

		$sql="update EHR_CONCURRENT_REQUEST_DETAIL set Po_Success='S' 
		      where request_no=".$request_no."
		      and  Po_Success<>'Y' ";
		$ok = $this->db->Execute($sql);
		if (!$ok){
			print $this->db->ErrorMsg();
			exit;
		}
		
		$sql="update EHR_CONCURRENT_REQUEST_DETAIL set Po_Success='S' 
		      where request_no=".$request_no."
		      and  Po_Success<>'Y' ";
		$ok = $this->db->Execute($sql);
		if (!$ok){
			print $this->db->ErrorMsg();
			exit;
		}
		/*
		 * S-手工取消，N-新增，C-完成，R-运行中
		 */

		$sql="update EHR_CONCURRENT_REQUEST set status='S' where request_no=".$request_no;
		$ok = $this->db->Execute($sql);
		if (!$ok){
			print $this->db->ErrorMsg();
			exit;
		}
		
		showMsg('操作成功!','success','?scriptname='.$_GET['fromscript']);
        
	}
}

/*  controller */
$concurrent = new Concurrent();
$concurrent->run();

?>