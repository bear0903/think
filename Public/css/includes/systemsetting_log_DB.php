<?php
/*
 * 系统日志
 *  $HeadURL: http://192.168.0.126:9001/svn/EHR/trunk/eHR/mgr/includes/systemsetting_log_DB.php $
 *  $Id: systemsetting_log_DB.php 1868 2009-06-29 05:55:04Z boll $
 *  $Rev: 1868 $
 *  $Date: 2009-06-29 13:55:04 +0800 (星期一, 29 六月 2009) $
 *  $Author: boll $
 *  $LastChangedDate: 2009-06-29 13:55:04 +0800 (星期一, 29 六月 2009) $
 * ************************************************************************** */
require_once 'GridView/Data_Paging.class.php';
include_once 'AresAction.class.php';
class SysLog extends AresAction {
	private function _getLogSql() {
		return $_logsql = <<<eof
        select app_use_id,
               app_use_user_no,
               app_use_company_id,
               app_use_company_no,
               to_char(app_use_datetime_begin, 'yyyy-mm-dd hh24:mi:ss') as app_use_datetime_begin,
               to_char(app_use_datetime_end, 'yyyy-mm-dd hh24:mi:ss') as app_use_datetime_end,
               ip_address,
               source,
               reverse3 as machine_name,
               reverse4 as login_name,
               reverse1 as os_username
          from app_system_use_historys a
         where app_use_company_id = '%s'
           and app_use_user_id    = '%s'
         order by app_use_datetime_begin desc
eof;
	}
	public function actionList() {
		$logsql = sprintf ( $this->_getLogSql (), $_SESSION ['user'] ['company_id'], $_SESSION ['user'] ['user_seq_no'] );
		$sql = "select t.* from (" . $logsql . ") t  where t.source <>'HCP'";
		$sqlcount = "select count(*) RCT from (" . $logsql . ")";
		$arr = $this->db->GetRow ( $sqlcount );
		$total_rows = $arr ['RCT'];
		$pageIndex = empty ( $_GET ['pageIndex'] ) ? 1 : $_GET ['pageIndex'];
		$page_size = 15;
		if ($total_rows > $page_size) {
			$page = new Data_Paging ( array ('total_rows' => $total_rows, 'page_size' => $page_size ) );
			$page->openAjaxMode ( 'gotopage' );
			$pageDownUp = $page->outputToolbar ();
			$this->tpl->assign ( "pageDownUp", $pageDownUp );
		}
		$rsLimit = $this->db->SelectLimit ( $sql, $page_size, $page_size * ($pageIndex - 1) );
		$rs = $rsLimit->getArray ();
		$this->tpl->assign ( "list", $rs );
		//$this->tpl->assign("post", $_POST);
		if ($total_rows > 0)
			$this->tpl->assign ( "show", "List" );
		if ($total_rows == 0)
			showMsg ( 'No Data Found .' );
	}
}
/*  controller */
$sysLog = new SysLog ();
$sysLog->run ();
