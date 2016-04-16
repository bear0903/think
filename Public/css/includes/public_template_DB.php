<?php
/**
 * 通过 HCP 设定程式共用的模版
 * Create Date 2008-08-08 by Dennis
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/public_template_DB.php $
 *  $Id: public_template_DB.php 3722 2014-04-18 07:12:52Z dennis $
 *  $Rev: 3722 $ 
 *  $Date: 2014-04-18 15:12:52 +0800 (周五, 18 四月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-04-18 15:12:52 +0800 (周五, 18 四月 2014) $
 *********************************************************/
if (! defined('DOCROOT')) die('Attack Error.');
/**
 *  Register login user profile
 *
 * @param AdoDB $db
 * @param string $company_id
 * @param string $user_seq_no
 * @return void
 * @author Dennis
 */
function registerUser($db, $company_id, $user_seq_no) {
	$plsql_stmt = "begin pk_erp.p_set_date(sysdate);pk_erp.p_set_segment_no('%s');pk_erp.p_set_username(%s);end;";
	//echo sprintf($plsql_stmt,$company_id,$user_seq_no);
	$db->Execute( sprintf($plsql_stmt, $company_id, $user_seq_no));
	//echo 'execute susccess....<hr>';
} // end function registerUser()

/**
 * decrypt data
 *
 * @param ADODB $db
 * @return void
 * @author Dennis
 */
/*
function dbDecrypt($db) {
	$db->Execute( "begin dodecrypt(); end;");
	//echo 'execute dbDecrypt success <hr/>';
}*/

/**
 * 取得多笔程式设定档信息
 *
 * @param string $programno  程式代码
 */
function getAppMasterSetting($programno)
{
	global $g_db_sql;
	$sql = "select page_size,
	    		   default_where,
	    		   default_order_by,
	    		   allow_sorting,
	    		   sort_mode,
	    		   allow_selected,
	    		   allow_mouse_event,
	    		   allow_paging,
	    		   allow_querying,
	    		   allow_print,
	    		   allow_exp,
	    		   allow_stts_grp,
	    		   allow_grouping,
	    		   header_paging,
	    		   footer_paging,
	    		   paging_theme,
	    		   application_type,
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
	    		   is_show,		/*default query gridview data */
	    		   show_where,	/* Query Default Where only use once */
	    		   comments,
	    		   result_sql
	    	  from ehr_program_setup_master 
	    	 where program_no ='%s'";
	$g_db_sql->SetFetchMode(ADODB_FETCH_ASSOC);
	return $g_db_sql->GetRow(sprintf($sql,$programno));
}// end getAppMasterSetting()

// auto load needed class
include_once 'Common.php';

/**
 * Register Current Login User Profile
*/
registerUser ($g_db_sql,
			  $_SESSION['user']['company_id'], 
			  $_SESSION['user']['user_seq_no']);


// 执行查询之前执行解密 Procedure
//dbDecrypt($g_db_sql); // remark by dennis 2013/11/08 在 AresDB.inc.php 中已经有执行解密

/**
 * GridView 设定资料
 *
 * @param array $gridview_config
 * @return string
 */
function getViewDBSQL(array $gridview_config)
{
	$sql = $gridview_config['RESULT_SQL'];
	// 把 order by 子句切出来，放到后面加
	if (! empty($gridview_config['DEFAULT_ORDER_BY'])) {
		$sql = substr($sql, 0, strripos($sql, 'order by'));
	} // end if 
	return $sql;
}// end getViewDBSQL()
$master_setting = getAppMasterSetting($GLOBALS['scriptname']);


if (count($master_setting)>0 && is_array($master_setting))
{
	//pr($master_setting);
	/**
	 * 多笔程式设定中查询资料的 SQL 语句
	 */
	$sql = $master_setting['RESULT_SQL'];
	
	// 把 order by 子句切出来，放到后面加
	if (! empty($master_setting['DEFAULT_ORDER_BY'])) {
		$sql = substr($sql, 0, strripos($sql, 'order by'));
	} // end if 
	//echo $sql;
	//echo $gvr['SHOW_WHERE'].'<hr/>';
	//echo $gvr['DEFAULT_ORDER_BY'].'<hr/>';
	
	function getGVColumnConfig($programno,$langcode)
	{
		global $g_db_sql;
		$sql = "select a.table_name,
	                   a.column_name,
	                   (select prompt_text
					      from hcp_muti_lang_pk
					     where muti_lang_pk = a.muti_lang_pk
					       and uiculture_code = '".$langcode."'
					       and rownum=1) as prompt_text,
	                       a.data_type,
	                       a.allow_sorting,
	                       a.width,
	                       a.height,
	                       a.align,
	                       a.class_name,
	                       a.format_str,
	                       a.column_type,
	                       a.bgcolor,
	                       a.font_color,
	                       a.font_name,
	                       a.checked_value,
	                       a.data_source,
	                       a.data_source_type,
	                       a.muti_lang_pk,
	                       a.allow_querying,
	                       a.is_rang_condition,
	                       a.query_column_type,
	                       a.data_source,
	                       a.group_id,
	                       a.column_seq
	                  from ehr_program_setup_column a
	                 where a.program_no = '".$programno."'
	                   and a.display = '1'
	                 order by a.column_seq asc";
		//echo $sql;
		return $g_db_sql->GetArray($sql);	
	}// end getGVColumnConfig()
	
	/**
	 * 取得单笔资料分组的清单
	 *
	 * @param string $program_no 程式代码
	 * @return array
	 * @access public
	 * @author Dennis
	 */
	function getSingFormGroupList($program_no, $lang_code) {
		global $g_db_sql;
		$sql = "select a.group_id, a.group_name, b.prompt_text
				  from ehr_program_setup_group a, ehr_program_column_lang b
				 where a.muti_lang_pk = b.muti_lang_pk
				   and a.program_no = '%s'
				   and b.uiculture_code = '%s'
				   order by sort_seq";
		//echo $sql.$program_no.$lang_code;
		return $g_db_sql->GetArray(sprintf($sql, $program_no, $lang_code));
	} // end getSingFormGroupList()
	
	
	/**
	 * 取得 GridView 的相关设定
	 *
	 * @param array $config
	 * @return void
	 * @access public global
	 * @author Dennis
	 */
	function getGridViewConfig(array $config) {
		//pr($config);exit;
		$gridviewConfig = array();
		if (is_array($config)) {
			foreach($config as $key => $value) {
				if (isset($value)) {
					//printf('key =>%s, value =>%s<br/>',$key,$value);
					switch (strtolower($key)) {
						case 'page_size' :
							$gridviewConfig['pageSize']= $value;
							break;
						case 'allow_sorting' :
							$gridviewConfig['allowSorting']= $value;
							break;
						case 'sort_mode' :
							$gridviewConfig['sortMode']= $value;
							break;
						case 'allow_selected' :
							$gridviewConfig['allowSelected']= $value;
							break;
						case 'allow_mouse_event' :
							$gridviewConfig['handleMouse']= $value;
							break;
						case 'allow_paging' :
							$gridviewConfig['isPaging']= $value;
							break;
						case 'header_paging' :
							$gridviewConfig['headerPaging']= $value;
							break;
						case 'footer_paging' :
							$gridviewConfig['footerPaging']= $value;
							break;
						case 'paging_theme' :
							$gridviewConfig['pagingTheme']= $value;
							break;
						case 'allow_alternating_row' :
							$gridviewConfig['isAlternatingColor']= $value;
							break;
						case 'alternating_row_style' :
							$gridviewConfig['alternatingRowStyle']= $value;
							break;
						case 'alternating_bgcolor' :
							$gridviewConfig['alternatingBgcolor']= $value;
							break;
						case 'alternating_fontcolor' :
							$gridviewConfig['alternatingFontColor']= $value;
							break;
						case 'gridview_style' :
							$gridviewConfig['gridViewStyle']= $value;
							break;
						case 'header_style' :
							$gridviewConfig['headerRowStyle']= $value;
							break;
						case 'selected_row_style' :
							$gridviewConfig['selectedRowStyle']= $value;
							break;
						case 'width' :
							$gridviewConfig['width']= $value;
							break;
						case 'height' :
							$gridviewConfig['height']= $value;
							break;
						case 'ui_style' :
							$gridviewConfig['uiStyle']= $value;
							break;
						case 'allow_exp':
							$gridviewConfig['allowExp']= $value;
							break;
						case 'allow_print':
							$gridviewConfig['allowPrint']= $value;
							break;
						default :
							break;
					} // end switch
				} // end if
			} // end foreach
		}
		//dd($gridviewConfig);
		return $gridviewConfig;
	} // end getGridViewConfig()
	
	/**
	 * 解析 sql 语句的变量 如
	 *  :company_no
	 *  :emp_seqno
	 * @param $sql
	 * @author Dennis add 2010-07-12
	 * @lastupdate dennis 2011-06-15
	 */
	function parseVars($sql,$emp_seqno=null,$company_id=null)
	{
		if(empty($emp_seqno)){
		$sql = str_replace(":emp_seq_no","'".$_SESSION['user']['emp_seq_no']."'",$sql);
		}else{
			$sql = str_replace(":emp_seq_no","'".$_GET['empseqno']."'",$sql);
		}
		if(empty($company_id)){
			$sql = str_replace(":company_no","'".$_SESSION['user']['company_id']."'",$sql);
			$sql = str_replace(":company_id","'".$_SESSION['user']['company_id']."'",$sql);
		}else{
			$sql = str_replace(":company_no","'".$_GET['companyid']."'",$sql);
			$sql = str_replace(":company_id","'".$_GET['companyid']."'",$sql);
		}
		$sql = str_replace(":language","'".$_SESSION['user']['language']."'",$sql);
		$sql = str_replace(":emp_id","'".$_SESSION['user']['emp_id']."'",$sql);
		$sql = str_replace(":user_seq_no","'".$_SESSION['user']['user_seq_no']."'",$sql);
		$sql = str_replace(":title_level","'".$_SESSION['user']['title_level']."'",$sql);
		return parseWhere($sql);
	}
	/**
	 * 
	 * Parse where condition 
	 * @param string $sqlstr
	 * @return string
	 * @author Dennis 2011-05-16
	 * 
	 */
	function parseWhere($sqlstr)
	{
		$patterns = array('/\':companyid\'/',
						  '/\':deptid\'/',
						  '/\':empid\'/');
		$replacements = array("'".$_SESSION['user']['company_id']."'",
							  "'".$_SESSION['user']['dept_id']."'",
							  "'".$_SESSION['user']['emp_id']."'");
		return preg_replace($patterns,$replacements,$sqlstr);
	}
	
	/**
	 * 取得栏位属性配置数组
	 * @example 
	 *  $xmlConfig = array('ID'=>array('width'=>60,
	 *			                    'align'=>'right',
	 *			                    'bgcolor'=>'red',
	 *			                    'height'=>20,
	 *			                    'color'=>'#000',
	 *			                    'title'=>'Sequence Number',
	 *			                    'allow_sorting'=>0,
	 *			                    'format'=>'yyyy-mm-dd'),
	 *		            'NAME'=>array('align'=>'center',
	 *				                 'title'=>'Employee Name',
	 *				  				 'width'=>50))
	 * @param array $r fetchAll result 2-d array
	 * @return array
	 */
	function getGridViewColsConfig($r) {
		$cols_config = array();
		if (is_array($r)) {
			for($i = 0; $i < count($r); $i ++) {
				// 栏位别名 规划: table 别名 + '_'+栏位名称
				// add by dennis 2008-05-22
				$colname = strtoupper($r[$i]['TABLE_NAME']. '_' . $r[$i]['COLUMN_NAME']);
				$cols_config["$colname"]= array();
				foreach($r[$i]as $key => $value) {
					// 有值才做设定, 不要用 empty() 函数, "0" 会被过滤掉
					if (isset($value)) {
						/**
						 * 这里重做一次对应(mapping)的原因：
						 * 如果数据库的的栏位有变动,只修改这里就好了,不会影响到
						 * GridView class
						 */
						switch (strtolower($key)) {
							case 'prompt_text' :
								$cols_config["$colname"]['title']= $value;
								break;
							case 'data_type' :
								$cols_config["$colname"]['data_type']= $value;
								break;
							case 'allow_sorting' :
								//echo  $colname.' -> '.$value;
								$cols_config["$colname"]['allow_sorting']= $value;
								break;
							case 'width' :
								$cols_config["$colname"]['width']= $value;
								break;
							case 'height' :
								$cols_config["$colname"]['height']= $value;
								break;
							case 'align' :
								//print 'align=>'.$value.'<br>';
								$cols_config["$colname"]['align']= $value;
								break;
							case 'class_name' :
								$cols_config["$colname"]['class_name']= $value;
								break;
							case 'format_str' :
								$cols_config["$colname"]['format']= $value;
								break;
							case 'column_type' :
								$cols_config["$colname"]['filed_type']= strtolower($value);
								break;
							case 'bgcolor' :
								$cols_config["$colname"]['bgcolor']= $value;
								break;
							case 'font_color' :
								$cols_config["$colname"]['color']= $value;
								break;
							case 'font_name' :
								$cols_config["$colname"]['font_name']= $value;
								break;
							case 'checked_value' :
								$cols_config["$colname"]['checked_value']= $value;
								break;
							case 'data_source' :
								$cols_config["$colname"]['data_source']= $value;
								break;
							case 'group_id' :
								$cols_config["$colname"]['group_id']= $value;
								break;
							default :
								break;
						} // end switch
					} // end if
				} //end foreach
			} // end loop
		} // end if
		//dd($cols_config);
		return $cols_config;
	} // end getGridViewColsConfig()
	
	
	/**
	 * To Smarty List array
	 *
	 * @param string $string
	 * @return array
	 */
	function toArray($string) {
		$r = explode(';', $string);
		$rs = '';
		for($i = 0; $i < count($r); $i ++) {
			if (strlen($r[$i]) > 0)
				$rs[$i]= explode(':', $r[$i]);
		} // end for loop
		return $rs;
	} // end toArray()
	
	// Add by boll  for  ： 静态值 下拉列表  查询条件
	function toArray2($string) {
		$r = explode(';', $string);
		$rs = '';
		for($i = 0; $i < count($r); $i ++) {
			if (strlen($r[$i]) > 0){
				$rs[$i]= explode(':', $r[$i]);
				if(!empty($rs[$i][1])) $rs[$i][0]=$rs[$i][1];
			}
		} // end for loop
		return $rs;
	} // end toArray()
	
	
	
	/**
	 * Get Applcation Setting
	 *
	 * @param array $columns
	 * @return array
	 */
	function getQueryColumn(array $columns) {
		//pr($columns);
		if (is_array($columns)) {
			$cnt = count($columns);
			for($i = 0; $i < $cnt; $i ++) {
				if ('1' !== $columns[$i]['ALLOW_QUERYING']) {
					unset($columns[$i]);
				} else {
					if ('list' == strtolower($columns[$i]['QUERY_COLUMN_TYPE'])) {
						if ('sql' == strtolower($columns[$i]['DATA_SOURCE_TYPE'])) {
							$columns[$i]['DATA_SOURCE']= queryListData($columns[$i]['DATA_SOURCE']);
						} // end if
						if ('static' == strtolower($columns[$i]['DATA_SOURCE_TYPE'])) {
							//print($columns[$i]['DATA_SOURCE']);
							// restore by Dennis 2014/04/08 use the toArray(), 下拉列表静态值时，k:v 对应 <option value="k">v</option>
							$columns[$i]['DATA_SOURCE']= toArray($columns[$i]['DATA_SOURCE']);
							//$columns[$i]['DATA_SOURCE']= toArray2($columns[$i]['DATA_SOURCE']); //Modify by boll 2009-06-11  for : 静态值 下拉列表  查询条件
						} // end if
					} // end if
				} // end if
			} // end for loop
		} // end if
		//pr($columns);
		return $columns;
	} // end getQueryColumn()
	
	
	/**
	 * 组合 Where Condition
	 *
	 * @param array $post_vars Post 变量
	 * @param array $cols_attr 查询栏位的属性, 比如判断是不是日历栏位, 就是从查询栏位定义中取得的
	 * @return string the where where condition string
	 * @access public
	 * @author Dennis
	 */
	function getWhereCond(array $post_vars, array $cols_attr) {
		$where = '';
		if (is_array($post_vars) && is_array($cols_attr)) {
			$op_cols_start = '_op_';
			//$cc = count($cols_attr);
			if (isset($post_vars['do']) && 'query' == strtolower($post_vars['do'])) {
				foreach($post_vars as $key => $value)
					if ('action' != $key && ! empty($value) && 'submit' != strtolower($key) && $op_cols_start != substr($key, 0, 4)) {
						//echo 'key->'.$key.'<br/>';
						$is_date = false;
						// 当操作符是 like 时
						if (isset($post_vars[$op_cols_start . $key])) {
							$like_flag = strtolower($post_vars[$op_cols_start . $key]) == 'like' ? '%' : '';
						} //end if
						foreach($cols_attr as $value1) {
							// between and 的第二个值
							$btValue1 = '';
							// 日历栏位操作符是 like 时 
							$date_key = '';
							// 日历栏位需要格式化
							//echo 'Post key ->'.$key.'<br/>';
							//echo 'col name ->'.$value1['COLUMN_NAME'].' key->'.$key.'  type->'.$value1['QUERY_COLUMN_TYPE'].'<br/>';
							// modified by Dennis 2008-5-21 HCP设定程式式增加了栏位别名
							if ($value1['TABLE_NAME']. '_' . $value1['COLUMN_NAME']== $key && 'CALENDAR' == $value1['QUERY_COLUMN_TYPE']) {
								$date_format = isset($value1['FORMAT_STR']) ? $value1['FORMAT_STR']: 'YYYY/MM/DD';
								if (empty($like_flag)) {
									$value = sprintf("to_date('%s','%s')", $value, $date_format);
								} else {
									$date_key = sprintf("to_char(%s,'%s')", str_replace('|', '.', $key), $date_format);
									$value .= $like_flag;
								} // end if
								// 日历栏位是区间形式时
								if (isset($post_vars[$key . '_1']) && ! empty($post_vars[$key . '_1'])) {
									$btValue1 = sprintf("to_date('%s','%s')", $post_vars[$key . '_1'], $date_format);
								} // end if
								$is_date = true;
								break;
							} else {
								// 非日历栏位，又是 between and 栏位
								if (isset($post_vars[$key . '_1']) && ! empty($post_vars[$key . '_1'])) {
									$btValue1 = $post_vars[$key . '_1'];
								} // end if
							} // end if
						} // end for loop
						
	
						if (! empty($post_vars[$op_cols_start . $key])) {
							$where .= sprintf(' and %s %s ' . (($is_date && ! empty($like_flag) || ! $is_date) ? "'%s'" : '%s'), ($is_date && ! empty($like_flag) ? str_replace('|', '.', $date_key) : str_replace('|', '.', $key)), $post_vars[$op_cols_start . $key], (stripos($value, '%') > 0 ? $value : $value . $like_flag));
						} elseif (isset($post_vars[$key]) && isset($post_vars[$key . '_1']) && ! empty($post_vars[$key . '_1'])) {
							// 组合是 between and 的条件
							$where .= sprintf(' and %s between ' . ($is_date ? '%s' : "'%s'") . '  and ' . ($is_date ? '%s' : "'%s'"), str_replace('|', '.', $key), $value, $btValue1);
						} // end if
					} // end if
			//$_SESSION[$_GET['scriptname']]['queryform'][$key]= $_POST[$key];
			} // end foreach
			$_SESSION[$_GET['scriptname']]['queryform']= $_POST;
		} // end if	    
		unset($cols_attr, $post_vars);
		//echo 'where is ->> '.$where.'<hr/>';
		return $where;
	} // end getWhereCond()
	/**
	 * Get List Field data
	 *
	 * @param string $sql
	 * @return array
	 */
	function queryListData($sql) {
		global $g_db_sql;
		$g_db_sql->SetFetchMode(ADODB_FETCH_NUM);
		return $g_db_sql->GetArray(parseVars($sql));
	} // end queryData()
	$gvColumnConfig = getGVColumnConfig($GLOBALS['scriptname'],$GLOBALS['config']['default_lang']);
	
	if (is_array($master_setting)) {
		$gridviewConfig = getGridViewConfig($master_setting);
	} else {
		showMsg('Application '.$GLOBALS['scriptname'].' is undefined.','error');
	} // end if
	
	$gridviewConfig = array_merge($gridviewConfig, $_GET);
	
	$query_columns = getQueryColumn ($gvColumnConfig);
	//dd($gridviewConfig);
	$col_where = '';
	//echo $sql.'<br>';
	if (isset($_POST['do']) && 'query' == $_POST['do']) {
		// 去掉最前面的 'and', 拿来做 where 条件 
		$col_where = substr(getWhereCond($_POST, $query_columns), 4);
		if (! empty($col_where)) {
			//echo 'post where condition is ->'.$col_where.'<br>';
			//$col_where = empty($w) ? $col_where : $w.substr($col_where,4);
			$_SESSION[$_GET['scriptname']]['where']= $col_where;
		} else {
			if (isset($_SESSION[$_GET['scriptname']]['where']))
				unset($_SESSION[$_GET['scriptname']]['where']);
		} // end if
	} else {
		// clear session query where condition where new page instance
		if (isset($_SESSION[$_GET['scriptname']]['where']) && 
			(! isset($_GET['pageIndex']) && 
			! isset($_GET['sortKey'])   &&
			! isset($_GET['openW'])))
			unset($_SESSION[$_GET['scriptname']]);
	} // end if
	
	// 没有 where 条件时, sql 就是原来设定的 sql
	// 点其它分页时, 到 session 里拿先前查询的 where 条件
	// modify by Dennis 2008-5-21
	if (isset($_SESSION[$_GET['scriptname']]['where'])) {
		$sql = 'select * from (' . $sql . ') where ' . $_SESSION[$_GET['scriptname']]['where'];
	} else {
		// 有查询条件时
		if (! empty($col_where)) {
			$sql = 'select * from (' . $sql . ') where ' . $col_where;
		} // end if
	} // end if
	// 如果有设 order by 子句，在这里再加上,
	// 前面为了组合 sql 方便, 把组合好的 order 拿掉了.
	if (!empty($master_setting['DEFAULT_ORDER_BY'])) {
		$sql .= ' order by ' . str_replace('.','_',$master_setting['DEFAULT_ORDER_BY']). ' '; //如果有查询条件时，这里要用栏位的别名
	} // end if	
	
	if (count($_POST) > 0) {
		$_SESSION['useDefWhere']= 'N';
	} else if (empty($_GET['sortKey']) && empty($_GET['pageIndex'])) {
		unset($_SESSION['useDefWhere']);
	} // end if
	
	//预设显示资料,重组查询语句
	if ('1' == $master_setting['IS_SHOW']&& 
	    !empty($master_setting['SHOW_WHERE']) && 
	    count($_POST) < 1 && 
	    ! isset($_SESSION[$_GET['scriptname']]['where']) && 
	    empty($_SESSION['useDefWhere'])) {
		$sql = 'select * from (' . $sql . ') where ' . $master_setting['SHOW_WHERE'];
	} // end if 
	
	//程式类型
	$template = '';
	$column_config = getGridViewColsConfig($gvColumnConfig);
	$sql = parseVars($sql,@$_GET['empseqno'],@$_GET['companyid']);
	
	//---- begin 可执行部门含下阶----
    $dept_id_permission="select in_gl_segment.segment_no_sz   dept_id
				            from gl_segment  in_gl_segment
				            where exists (select in_gl_segment.segment_no_sz as dept_id
				                            from app_usercompany_v  in_app_usercompany_v, 
				                                 app_userdepartment   in_app_userdepartment
				                           where in_app_usercompany_v.appusr_seg_segment_no = in_app_userdepartment.appusr_seg_segment_no
				                             and in_app_usercompany_v.appusr_username = in_app_userdepartment.appusr_username
				                             and in_app_userdepartment.appusr_seg_segment_no = in_gl_segment.seg_segment_no
				                             and in_app_userdepartment.department = in_gl_segment.segment_no
				                             and in_gl_segment.begindate < sysdate
				                             and (in_gl_segment.enddate is null or in_gl_segment.enddate > trunc(sysdate))
				                             and in_app_usercompany_v.appusr_seg_segment_no = '".$_SESSION['user']['company_id']."'
				                             and in_app_usercompany_v.personself_yn = 'N'
				                             and in_app_usercompany_v.username_no_sz = '".$_SESSION['user']['emp_id']."'
				                          )
				            connect by prior in_gl_segment.segment_no  = in_gl_segment.parent_segment_no";
    $sql = str_ireplace(":dept_id_permission",$dept_id_permission,$sql);
    //---- end 可执行部门含下阶----
	switch (strtolower($master_setting['APPLICATION_TYPE'])) {
		case '': // add by dennis for void null application type error, default as gridview
		case 'gridview' :
			// 是否预设查询资料出来, 如果是大量资料的 Gridview，预设不查询资料来出
			// 只显示查询条件的画面
			if ('1' == $master_setting['IS_SHOW']||
			    (isset($_POST['do']) && 
			    'query' == $_POST['do']) ||
			    isset($_GET['sortKey'])) {
				//echo $sql.'<hr size="1"/>';	
				//pr($column_config);			
				//exit;
				$gridview = new GridView($g_db_sql,
										 $sql,
										 $gridviewConfig,
										 $column_config);
				$template = $gridview->render();
				$gridview->gridViewStyle = 'tblinegray';
				//$g->pagingTheme = 3;
	    		//$g->headerPaging = false;
	    		//$g->footerPaging = true;
	    		//$g->width = 700;
	    		//$g->isSortable = false;
	    		//$g->height = 200;
	    		//$g->isAlternatingColor = true;
	    		//$g->isSortable = false;
	    		//$g->title = '部门资料';
	    		//$g->pageSize = 4;
	    		//$g->uiStyle = 'olive';
	    		
			    //begin summary_rpt 汇总报表的处理 add by boll 20090728 
			    /* remark by dennis 2011-06-08 no summary report now
				$is_summary_rpt=(substr($_GET['scriptname'],0,4)=='MDNB');
				if($is_summary_rpt){
					include_once 'block_summary_rpt.php';
				} //end summary_rpt
				*/
			} // end if
			//echo $template;exit;
			break;
		case 'singlerow' :
			$datGrpList = '';
			//echo $_GET['scriptname']. $_SESSION['user']['language'];
			if ('1' == $master_setting['ALLOW_GROUPING']) {
				$datGrpList = getSingFormGroupList($_GET['scriptname'], $_SESSION['user']['language']);
				//dd($datGrpList);
			} // end if
			require_once 'Layout/SingleRowForm.php';
			//echo $sql;
			//pr($datGrpList);
			$sform = new Layout_SingleRowForm ($g_db_sql, $sql,$column_config, $datGrpList);
			$template = $sform->render();
			break;
		default :
			trigger_error('Unkow application type ' . $gvr['APPLICATION_TYPE']. ' . <br/> Current System only support <b>GridView</b> and <b>SingleRow</b> Type.');
			break;
	} // end switch
	//dd($GLOBALS);
	if ('1' == $master_setting['ALLOW_QUERYING']) {
		//pr($_SERVER);
		//add by boll 处理由菜单link来的参数丢失问题
		$oldparasting = "";
		foreach ($_GET as $key=>$value){
			if($key=='pageIndex') continue;
			if($key=='sortKey')  continue;
			$oldparasting .= '&'.$key.'='.urlencode($value);
		}
		//echo $oldparasting;
		//pr($query_columns);
		//$query_columns['name']='form1';
		//'name'=>'form_search',  add by boll for gotopage
		$qForm = new Form_Query(array ('action' => $_SERVER['PHP_SELF']. '?'.$oldparasting,
									   'name'=>'form_search'),$query_columns);
		// show gridview here
		/* remark by dennis 2011-06-08 no summary report now
		$is_summary_rpt=(substr($_GET['scriptname'],0,4)=='MDNB');
		if($is_summary_rpt){
			$qFormTableHtml=$qForm->render();
			
			if(empty($qFormTableHtml)){
				$qFormTableHtml=  '<form name="Qform"><table><tbody><tr><td></td><td></td></tr></tbody></table></form>';
			}
			$g_tpl->assign ('qFormtemplate',$qFormTableHtml);
			//$template = $qForm->render(). $template;
		}else{ 
			$template = $qForm->render(). $template;
		} //end summary_rpt
		*/
		$template = $qForm->render().$template;
	} //end if
	$g_tpl->assign('template',$template);
	$g_tpl->assign('scriptname',$_GET['scriptname']);
	// 如果有查询过, Export 时也要带条件
	$g_tpl->assign('sql',$sql);
	unset ($gvColumnConfig,$gridview, $qForm,$column_config,$query_columns);
}else{
	//echo 'error ';
	showMsg('Application '.$GLOBALS['scriptname'].' is undefined.','error');
}// end if

