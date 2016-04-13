<?php
/**
 * 
 * @author Dennis
 *
 */

class AresSys {
	protected $_dbConn;
	
	public function __construct()
	{
		global $g_db_sql;
		$this->_dbConn = $g_db_sql;
	}
	
	/**
	 * 挑某一类型的程式清单
	 *  (move code from config.inc.php)
	 * @param string $app_type only support
	 * 'NORMAL'_正常撰写的程式,
	 * 'QUERY'_End-User 自定义产生的查询程式
	 * 'WORKFLOW'_ End-User 自定义产生的带 workflow 的程式
	 * @return array
	 * @author Dennis
	 */
	public function getAppsListByType($app_type)
	{
		switch(strtolower($app_type))
		{
			case 'workflow':
				$sql = <<<eof
	        select filename
	          from app_file
	         where report_approve10 = :app_type
	           and filetype         = 'FORM'
eof;
				break;
			case 'query':
				$sql = <<<eof
			select program_no as filename
			  from ehr_program_setup_master
eof;
				break;
			default:break;
		}
		
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$applist = $this->_dbConn->CacheGetArray(3600,$sql,array('app_type'=>strtoupper($app_type)));
		$a = array();
		if (is_array($applist) && count($applist)>0)
		{
			foreach($applist as $v)
			{
				$a[] = $v['FILENAME'];
			}
		}// end if;
		return $a;
	}
	/**
	 * (move code from config.inc.php)
	 * Check WF Schema Installed
	 * @param no
	 * @author Dennis 2012-03-05
	 */
	public function isWorkflowInstalled()
	{
		$sql = 'select 1 from dba_users where username = :username';
		//$g_db_sql->debug = true;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$r = $this->_dbConn->CacheGetOne(3600,$sql,array('username'=>'WF'));
		if ($r == 1) return true;
		return false;
	}
}