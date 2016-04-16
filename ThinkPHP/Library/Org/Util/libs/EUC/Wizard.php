<?php
/**
 * @package EUC
 * @category Wizard
 * 
 *	User Define Report Wizard
 *
 *  Create By: Dennis
 *  Create Date: 2011-04-11
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/EUC/Wizard.php $
 *  $Id: Wizard.php 3463 2013-02-27 01:43:53Z dennis $
 *  $LastChangedDate: 2013-02-27 09:43:53 +0800 (周三, 27 二月 2013) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3463 $  
 \****************************************************************************/

class EUC_Wizard {
	
	private $_dbConn;
	private $_companyId;
	private $_username;
		
	public function __construct()
	{
		global $g_db_sql;
		$this->_dbConn = $g_db_sql;
		//$this->_dbConn->debug = true;
		$this->_companyId = $_SESSION['user']['company_id'];
		$this->_username  = $_SESSION['user']['user_name'];
	}
	
	/**
	 * 
	 * Check user defined report name unique
	 * default check zh-cn languauge
	 * @param string $rptname
	 * @access public
	 * @return array
	 * @author Dennis 2011-04-12
	 */
	public function checkRptNameUnique($rptname)
	{
		$sql = <<<eof
			select count(*)
			  from app_file a, app_muti_lang b
			 where a.filename   = b.name
			   and b.program_no = 'HCP'
			   and b.lang_code  = 'ZHS'			   
			   and a.filetype   = 'FORM'
			   and b.value      = '$rptname'
eof;
		//$this->_dbConn->debug = 1;
		$r['isexists'] = $this->_dbConn->GetOne($sql);
		return $r;
	}
	
	/**
	 * Get Useable Views from DBMS table
	 * @param string $moduleid  view 所属的模组 id, 如 ESNA, ESNB etc..
	 * @return array
	 * @access public
	 * @author Dennis 2011-04-13
	 */
	public function getDbViewList()
	{
		/*
		$sql = <<<eof
			select a.view_name as tab_name, 
			       b.comments  as tab_desc
			  from user_views        a, 
			       user_tab_comments b
			 where a.view_name = b.table_name
			   and a.view_name like upper('$prefix%')
			   
			-- where upper(module_id) = upper('$moduleid')
eof;
		*/
		$sql = <<<eof
			select table_name as tab_name,
			       table_desc as tab_desc,
			       remark     as tab_remark,
			       upper(module_id) as module_id
			  from ehr_program_view_list
eof;
		//echo $sql;
		return $this->_dbConn->GetArray($sql);
	}
	
	/**
	 * Get ess module list
	 * @param no
	 * @return array
	 */
	public function getModule()
	{
		$sql = <<<eof
			select distinct a.filename as module_id, 
			       c.remark   as module_desc
			  from app_file a, app_muti_lang b, ehr_program_view_list c
			 where a.filetype = 'MENU'
			   and substr(a.filename, 0, 3) = 'ESN'
			   and length(a.filename) = 4
			   and a.filename   = b.name
			   and a.filename   = c.module_id
			   and b.program_no = 'HCP'
			   and b.lang_code  = 'ZHS'
			 order by a.filename
eof;
		return $this->_dbConn->GetArray($sql);
	}
	
	/**
	 * Get user defined report list
	 * @param string $moduleid
	 * @param string $rpt_name
	 * @param string $byowner
	 */
	function getMenu($moduleid,$rpt_name,$byowner = '')
	{
		$where = !empty($moduleid) ? ' and a.function_id = \''.$moduleid.'\' ' : '';
		$where.= !empty($rpt_name) ? ' and b.value like \'%'.$rpt_name.'%\' ' : '';
		$where.= !empty($byowner)  ? ' and upper(c.create_by) = \''.strtoupper($this->_username).'\' ' : '';
		$sql = <<<eof
			 select a.child_id    as rpt_id,
			 		b.value       as rpt_desc,
			        a.function_id as module_id,
			        (select d.value
					  from app_file c, app_muti_lang d
					 where c.filename = d.name
					   and c.filetype = 'MENU'
					   and d.lang_code = 'ZHS'
					   and filename  = a.function_id) as module_desc
			   from app_functions a, 
			        app_muti_lang b, 
			        app_file      c
			  where a.child_id   = b.name			    
			    and a.child_id   = c.filename
			    and b.program_no = 'HCP'
			    and b.lang_code  = 'ZHS'
			    and a.child_type = 'FORM'
			    $where
			    and c.report_approve10 = 'QUERY'
			  order by a.p_prior
eof;
		//echo $sql;
		return $this->_dbConn->GetArray($sql);
	}
	
	/**
	 * 暂未用到
	 * Enter description here ...
	 * @param unknown_type $menucode
	 */
	public function getMenuDesc($menucode)
	{
		$sql = <<<eof
		select b.value as rpt_desc
		  from app_file a, app_muti_lang b
		 where a.filename = b.name
		   and b.program_no = 'HCP'
		   and b.lang_code = 'ZHS'
		   and a.filetype = 'FORM'
		   and a.filename = '$menucode'
eof;
		return $this->_dbConn->GetOne($sql);
	}
	
	/**
	 * 
	 * Get Columns of a view according the $viewname
	 * @param string $viewname
	 * @return array
	 * @access public
	 * @author Dennis 2011-04-12
	 */
	public function getColumnsByView($viewname)
	{
		$sql = <<<eof
			select a.column_name, 
			       nvl(a.comments,a.column_name) as column_desc, 
			       b.data_type,
			       decode(b.data_type,'VARCHAR2','文本',
			              'DATE','日期','NUMBER','数字',
			              b.data_type) as data_type_cn, 
			       b.data_length
			  from user_col_comments a, 
			       user_tab_cols     b
			 where a.table_name  = b.table_name
			   and a.column_name = b.column_name
			   and a.table_name  = upper('$viewname')
	      order by a.table_name,b.column_id
eof;
		return $this->_dbConn->GetArray($sql);
	}
	
	/**
	 * add new report app
	 * 
	 * @param array $master_row
	 * @param array $data_source
	 * @param array $detail_row
	 * @param array $col_group
	 * @param array $col_group_lang
	 * @param array $col_lang
	 * @author Dennis 2012-08-22 (last update)
	 */
	public function addRpt($master_row,
						   $data_source,
						   $detail_row,
						   $col_group,
						   $col_group_lang,
						   $col_lang)
	{
		
		//$this->_dbConn->debug = 1;	
		$this->_dbConn->BeginTrans();
		$r = $this->_saveRptMastSetting($master_row);
		if ($r){
			foreach($data_source as $v)
			{
				$r = $this->_saveRptDataSource($v);
			}
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r){
			// loop insert col setting
			$c = count($detail_row);
			for($i=0; $i<$c; $i++)
			{
				$r = $this->_saveRptColDetail($detail_row[$i]);
			}
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r){
			if (is_array($col_lang))
			{
				foreach ($col_lang as $row) {
					$r = $this->_saveRptColMultiLang($row);
				}
			}
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r){
			if(is_array($col_group))
			{
				foreach ($col_group as $grp) {
					$r = $this->_saveRptColGroup($grp);
				}
			}
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r){
			if (is_array($col_group_lang))
			{
				foreach ($col_group_lang as $grp) {
					$r = $this->_saveRptColGrpLang($grp);
				}
			}
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		$this->_dbConn->CommitTrans($r);
		$this->_dbConn->CompleteTrans(false);
		return true;
	}
	/**
	 * 
	 * Delete user defined report settings
	 * @param string $menucode
	 * @return boolean
	 * @access public
	 * @author Dennis 2011-05-06
	 */
	public function deleteRpt($modulecode,$menucode)
	{
		$rbac = new EUC_RBAC();
		//$this->_dbConn->debug = 1;	
		$this->_dbConn->BeginTrans();
		$r = true;
		if (!is_null($modulecode))
		{
			$r= $rbac->delMenu($modulecode, $menucode);
			if ($r)
			{
				$r = $this->_delRptMastSetting($menucode);
			}else{
				$this->_dbConn->RollbackTrans();
				return false;
			}
		}
		if ($r){
			$r = $this->_delRptColDetail($menucode);
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r){
			$r = $this->_delDataSocure($menucode);
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r){
			$r = $this->_delColGroup($menucode);
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r){
			$r = $this->_delColMultiLang('',$menucode);
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		$this->_dbConn->CommitTrans($r);
		$this->_dbConn->CompleteTrans(false);
		return true;
	}
	/**
	 * Update user defined report settings
	 * @param string $menucode
	 * @param array $master_row
	 * @param array $detail_row
	 * @param array $col_group
	 * @return boolean
	 * @access public
	 * @author Dennis 2011-05-06
	 */
	public function updateRpt($menucode,
							  $data_source,
							  $master_row,
							  $detail_row,
							  $col_group,
							  $col_group_lang,
							  $col_lang)
	{
		$r = $this->delRpt($menucode);
		if ($r) $r = $this->addRpt($master_row,
								   $data_source,
								   $detail_row,
								   $col_group,
								   $col_group_lang,
								   $col_lang);
		return $r;
	}
	
	/**
	 * Get user define report master
	 * 
	 * @param string $menucode
	 * @return array row data
	 */
	public function getRptMaster($menucode,$langcode = 'ZHS')
	{
		$sql = <<<eof
		select a.program_no,
		       b.value as rpt_desc,
		       target_table_detail_id,
		       c.table_name,
		       page_size,
		       default_where,
		       default_order_by,
		       allow_sorting,
		       sort_mode,
		       allow_selected,
		       allow_mouse_event,
		       allow_paging,
		       header_paging,
		       footer_paging,
		       paging_theme,
		       allow_alternating_row,
		       alternating_row_style,
		       alternating_bgcolor,
		       alternating_fontcolor,
		       gridview_style,
		       header_style,
		       selected_row_style,
		       width,
		       height,
		       ui_style,
		       comments,
		       result_sql,
		       allow_querying,
		       application_type,
		       allow_grouping,
		       is_show,
		       show_where,
		       allow_exp,
		       allow_print,
		       allow_stts_grp
		  from ehr_program_setup_master a, 
		       app_muti_lang            b,
		       ehr_program_setup_table  c
		 where a.program_no = b.name
		   and b.program_no = 'HCP'
		   and a.program_no = c.program_no
		   and b.lang_code  = :langcode
		   and a.program_no = :menucode
eof;
		//$this->_dbConn->debug = true;
		return $this->_dbConn->GetRow($sql,array('menucode'=>$menucode,
												 'langcode'=>$langcode));
	}
	/**
	 * Get all columns setting by program no
	 * @param string $menucode
	 * @author Dennis 2011-06-26
	 */
	public function getRptCols($menucode)
	{
		$sql = <<<eof
			select * 
			  from ehr_program_setup_column
			 where program_no = :menucode
			order by column_seq
eof;
		return $this->_dbConn->GetArray($sql,array('menucode'=>$menucode));
	}
	
	public function getRptColGroup($menucode)
	{
		$sql = <<<eof
eof;
		return $this->_dbConn->GetArray($sql,array('menucode'=>$menucode));
	}
	
	public function getRptColLang($menucode)
	{
		$sql = <<<eof
eof;
		return $this->_dbConn->GetArray($sql,array('menucode'=>$menucode));
	}
	
	/**
	 * Parse where condtion replace text to variables
	 * 
	 * @param string $wherestr
	 * @return string
	 * @access private
	 * @author Dennis 2011-05-04
	 */
	private function _parseWhereConst($wherestr)
	{
		$patterns = array('/1.用户公司代码/',
						  '/2.用户部门代码/',
						  '/3.用户员工代码/',
						  '/4.系统日期/');
		
		$replacements = array(':companyid',':deptid',':empid','sysdate');
		
		return preg_replace($patterns,$replacements,$wherestr);
	}
	
	/**
	 * 
	 * Save user defined report master setting
	 * 
	 * @param array $row
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _saveRptMastSetting($row)
	{
		$sql = <<<eof
			insert into ehr_program_setup_master(
				  program_no,
				  target_table_detail_id,
				  page_size,
				  default_where,
				  default_order_by,
				  allow_sorting,
				  sort_mode,
				  allow_paging,
				  header_paging,
				  footer_paging,				  
				  allow_querying,
				  application_type,
				  allow_grouping,
				  allow_exp,
				  allow_print,
				  allow_stts_grp,
				  result_sql
				) values (
				  :program_no,
				  1,
				  :page_size,
				  :default_where,
				  :default_order_by,
				  :allow_sorting,
				  'remote',
				  :allow_paging,
				  :header_paging,
				  :footer_paging,
				  :allow_querying,
				  :layout_type,
				  :allow_grouping,
				  :allow_exp,
				  :allow_print,
				  :allow_stts_grp,
				  :sql_result)
eof;
		//pr($row);
		return $this->_dbConn->Execute($sql,$row);
		$this->deleteRpt(null, $row['program_no']);
		$row['target_table_detail_id'] = 1;
		$row['sort_mode'] = 'remote';
		return $this->_dbConn->Replace('ehr_program_setup_master',$row,'program_no',true);
	}
	/**
	 * 
	 * Delete user deinfed report master setting 
	 * @param string $menucode
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _delRptMastSetting($menucode)
	{
		$sql = <<<eof
			delete from ehr_program_setup_master where program_no = :menu_code
eof;
		return $this->_dbConn->Execute($sql,array('menu_code'=>$menucode));
	}
	
	/**
	 * Save user defined report data source name 
	 * 
	 * @param array $row_data
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _saveRptDataSource($row_data)
	{
		$sql = <<<eof
			insert into ehr_program_setup_table
			  (program_no, table_name, table_allies_name)
			values
			  (:program_no, :table_name, :table_alies)
eof;
		$key_cols = array('program_no','table_name');
		return $this->_dbConn->Replace('ehr_program_setup_table',$row_data,$key_cols,true);
	} 
	/**
	 * 
	 * Delete user defined data source (view name or table name)
	 * 
	 * @param string $menucode
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _delDataSocure($menucode)
	{
		$sql = <<<eof
			delete from ehr_program_setup_table where program_no = :menu_code
eof;
		return $this->_dbConn->Execute($sql,array('menu_code'=>$menucode));
	}
	
	private function _saveRptColGroup($row)
	{
		$sql = <<<eof
			insert into ehr_program_setup_group
			  (program_no,
			   group_id,
			   group_name,
			   group_desc,
			   muti_lang_pk,
			   sort_seq)
			values
			  (:program_no,
			   :group_id,
			   :group_name,
			   :group_desc,
			   :muti_lang_pk,
			   :sort_seq)
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->Execute($sql,$row);
	}
	/**
	 * Delete single row layout group
	 * @param string $menucode
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _delColGroup($menucode)
	{
		$sql = <<<eof
			delete from ehr_program_setup_group where program_no = :menu_code
eof;
		return $this->_dbConn->Execute($sql,array('menu_code'=>$menucode));
	}
	/**
	 * 
	 * Save user defined report columns
	 * @param array $row
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _saveRptColDetail($row)
	{
		$sql = <<<eof
			insert into ehr_program_setup_column
			  (program_no,
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
			   column_seq,
			   display,
			   allow_querying,
			   is_rang_condition,
			   query_column_type,
			   data_source_type,
			   group_id,
			   column_actual_value,
			   tgt_url,
			   groupby_type,
			   date_fmt,
			   num_format,
			   dec_num,
			   unsign_fmt,
			   uf_font_color,
			   uf_bg_color)
			values
			  (:program_no,
			   :table_name,
			   :column_name,
			   :data_type,
			   :allow_sorting,
			   :width,
			   :height,
			   :align,
			   :class_name,
			   :format_str,
			   :column_type,
			   :bgcolor,
			   :font_color,
			   :font_name,
			   :checked_value,
			   :data_source,
			   :muti_lang_pk,
			   :column_seq,
			   :display,
			   :allow_querying,
			   :is_rang_condition,
			   :query_column_type,
			   :data_source_type,
			   :group_id,
			   :column_actual_value,
			   :tgt_url,
			   :groupby_type,
			   :date_fmt,
			   :num_format,
			   :dec_num,
			   :unsign_fmt,
			   :uf_font_color,
			   :uf_bg_color)
eof;
		//pr($row);
		//return $this->_dbConn->Execute($sql,$row);
		// delete all cols before insert update
		$key_cols = array('program_no','table_name','column_name');
		return $this->_dbConn->Replace('ehr_program_setup_column',$row,$key_cols,true);
	}
	/**
	 * 
	 * Delete user defined report columns
	 * @param string $menucode
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _delRptColDetail($menucode)
	{
		$sql = <<<eof
			delete from ehr_program_setup_column where program_no = '$menucode'
eof;
		return $this->_dbConn->Execute($sql);
	}	
	/**
	 * 
	 * Save user defined report column multiple language
	 * @param array $row
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _saveRptColGrpLang($row)
	{
		$sql = <<<eof
			insert into ehr_program_column_lang(
				muti_lang_pk,uiculture_code,prompt_text,program_no
			)values(
				:muti_lang_pk,
				:lang_code,
				:grp_desc,
				:program_no
			)
eof;
		return $this->_dbConn->Execute($sql,$row);
	}
	
	private function _saveRptColMultiLang($row)
	{
		$sql = <<<eof
		insert into hcp_muti_lang_pk
		  (muti_lang_pk,
		   uiculture_code,
		   prompt_text,
		   create_by,
		   create_program,
		   reverse1)
		values
		  (:muti_lang_pk, :lang_code, :col_title, sysdate,:create_by, 'ESS_UDF_RPT',:program_no)
eof;
		//pr($row);
		//return $this->_dbConn->Execute($sql,$row);
		$row['create_program'] = 'ESS_UDF_RPT';
		$key_cols = array('muti_lang_pk','uiculture_code','reverse1');
		return $this->_dbConn->Replace('hcp_muti_lang_pk',$row,$key_cols,true);
	}
	
	/**
	 * Delete column multiple language
	 * 
	 * @param string $colname
	 * @param string $menucode
	 */
	private function _delColMultiLang($colname,$menucode=null)
	{
		$where = is_null($menucode) ? ' muti_lang_pk = \''.$colname.'\'' : 'program_no = \''.$menucode.'\'';
		$sql = <<<eof
			delete from ehr_program_column_lang where $where
eof;
		return $this->_dbConn->Execute($sql);
	}
}// end class EUC_Wizard

/**
 * Role Based Access Control
 * 
 * @package EUC
 * @category RBAC
 * @author Dennis
 *
 */
class EUC_RBAC
{
	private $_dbConn;
	private $_companyId;
	private $_username;
	private $_appName = 'ESS_UDF_RPT';
	
	public function __construct()
	{
		global $g_db_sql;
		$this->_dbConn    = $g_db_sql;
		$this->_companyId = $_SESSION['user']['company_id'];
		$this->_username  = $_SESSION['user']['user_name'];
	}
	
	/**
	 * Add Menu to System
	 * @param string $code
	 * @param string $desc
	 * @param number $seq
	 * @param string $type default 'FORM' maybe 'MENU'
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _addMenu($code,$desc,$type = 'FORM')
	{
		$sql = <<<eof
			 insert into app_file (
                filename,filetype,filedesc,report_approve10,create_by,create_date,create_program
              ) values (
                :menucode,
                :menutype,
                :menudesc,
                'QUERY',
                :username,
                sysdate,
                '$this->_appName')
eof;
		return $this->_dbConn->Execute($sql,array('menucode'=>$code,
												  'menudesc'=>$desc,
												  'menutype'=>$type,
												  'username'=>$this->_username));
	}
		
	/**
	 * Automatically get max menu code according the module code
	 * 
	 * @param string $modulecode module code such as 'ESNA, ESNB...' etc.
	 * @return string
	 * @access private
	 * @author Dennis 2011-05-04
	 */
	private function _getMenuCode($modulecode)
	{
		$modulecode = strtoupper($modulecode);
		$sql = <<<eof
			select to_number(substrb(nvl(max(child_id), '{$modulecode}000'), 5)) + 1 as m
			  from app_functions
			 where function_id = '$modulecode'
			   and child_type  = 'FORM'
eof;
		return $modulecode.sprintf("%03s", $this->_dbConn->GetOne($sql));
	}
	
	/**
	 * 
	 * Automatically get max menu order sequence number
	 * @param string $modulecode
	 */
	private function _getMenuOrderSeq($modulecode,$type = 'FORM')
	{
		$sql = <<<eof
			select nvl(max(p_prior),0)+ 10 as m
			  from app_functions
			 where function_id = '$modulecode'
			   and child_type  = '$type'
eof;
		return $this->_dbConn->GetOne($sql);
	}
	
	/**
	 * 
	 * Add new menu to system
	 * @param string $modulecode
	 * @param string $menucode
	 * @param string $menudesc
	 * @param number $seq
	 * @param string $menutype
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-04
	 */
	private function _addMenu2Module($modulecode,$menucode,$seq,$menutype = 'FORM')
	{
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
			   function_no_sz,
			   child_no_sz,
			   child_name)
			values
			  (:company_id,
			   :function_id,
			   :child_id,
			   :child_type,
			   :p_prior,
			   :username,
			   :create_by,			 
			   sysdate,
			  '$this->_appName',
			   :function_id1,
			   :child_id1,
			   :child_id2)		
eof;
		return $this->_dbConn->Execute($sql,array('company_id' => $this->_companyId,
											     'function_id'=> $modulecode,
											     'child_id'   => $menucode,
											     'child_type' => $menutype,
											     'p_prior'    => $seq,
											     'username'   => $this->_username,
											     'create_by'  => $this->_username,
											     'function_id1'=>$modulecode,
											     'child_id1'  => $menucode,
											     'child_id2'  => $menucode));
	}
	/**
	 * Add menu multiple languange 
	 * @param string $menucode
	 * @param string $menudesc
	 * @param string $lang
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _addMenuMultiLang($menucode,$menudesc,$lang = 'ZHS')
	{
		$sql = <<<eof
			insert into app_muti_lang
				  (program_no,
				   lang_code,
				   type_code,
				   name,
				   value,
				   update_by,
				   update_date)
				values
				  ('HCP','$lang','MT','$menucode','$menudesc','$this->_appName',sysdate)
eof;
		return $this->_dbConn->Execute($sql);
	}
	
	/**
	 * Delete menu code before insert
	 * 
	 * @param string $menucode
	 * @return boolean
	 * @access private
	 * @author Dennis 2011-05-05
	 */
	private function _delMenu($menucode,$menutype = 'FORM')
	{
		$sql = <<<eof
			delete app_file
		     where filename = '$menucode'
		       and filetype = '$menutype'
eof;
		return $this->_dbConn->Execute($sql);
	}
	/**
	 * 
	 * Delete menu from module 
	 * @param string $modulecode
	 * @param string $menucode
	 */
	private function _delModuleMenu($modulecode,$menucode)
	{
		$sql = <<<eof
		delete from app_functions 
	     where function_id = '$modulecode'
	       and child_id    = '$menucode'
eof;
		return $this->_dbConn->Execute($sql);
	}
	/**
	 * Delete menu multiple language
	 * 
	 * @param string $menucode
	 */
	private function _delMenuLang($menucode)
	{
		$sql = <<<eof
		   delete from app_muti_lang
            where name = '$menucode'
              and program_no ='HCP'
              and type_code  ='MT'
eof;
		return $this->_dbConn->Execute($sql);
	}
	
	/**
	 * Delete relate data before insert
	 * 
	 * @param string $modulecode
	 * @param string $menucode
	 * @param string $type
	 * @return boolean
	 * @access public
	 * @author Dennis 2011-05-05
	 */
	public function delMenu($modulecode,$menucode,$type = 'FORM')
	{
		$this->_dbConn->BeginTrans();
		$r = $this->_delModuleMenu($modulecode,$menucode);
		if ($r)
		{
			$r = $this->_delMenu($menucode,$type);
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r)
		{
			$r = $this->_delMenuLang($menucode);
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		$this->_dbConn->CommitTrans($r);
		$this->_dbConn->CompleteTrans(false);
		return true;
	}
	
	/**
	 * 
	 * Add new menu to system
	 * @param string $modulecode
	 * @param string $menucode
	 * @param string $menudesc
	 * @param string $seq
	 * @param string $type
	 * @return boolean
	 * @access public
	 * @author Dennis 2011-05-05
	 */
	public function addMenu($modulecode,$menudesc,$type='FORM')
	{
		$menucode = $this->_getMenuCode($modulecode);
		$seq      = $this->_getMenuOrderSeq($modulecode,$type);
		//$this->_dbConn->debug = 1;	
		$this->_dbConn->BeginTrans();
		$r = $this->_addMenu($menucode, $menudesc,$type);
		if ($r)
		{
			$r = $this->_addMenu2Module($modulecode, $menucode,$seq,$type);
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		if ($r)
		{
			$this->_addMenuMultiLang($menucode, $menudesc);
		}else{
			$this->_dbConn->RollbackTrans();
			return false;
		}
		$this->_dbConn->CommitTrans($r);
		$this->_dbConn->CompleteTrans(false);
		return $menucode;
	}
	
	function genGUID() 
	{
	   //e.g. output: 372472a2-d557-4630-bc7d-bae54c934da1
	   //word*2-, word-, (w)ord-, (w)ord-, word*3
	   $guidstr = "";
	   for ($i=1;$i<=16;$i++) {
	      $b = (int)rand(0,0xff);
	      if ($i == 7) { $b &= 0x0f; $b |= 0x40; } // version 4 (random)
	      if ($i == 9) { $b &= 0x3f; $b |= 0x80; } // variant
	      $guidstr .= sprintf("%02s", base_convert($b,10,16));
	      if ($i == 4 || $i == 6 || $i == 8 || $i == 10) { $guidstr .= '-'; }
	   }
	   return strtoupper($guidstr);
	}

	
}
/* end file */