<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *    Print & Export public functions Dennis 2011-06-15
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/gv_print_exp.php $
 *  $Id: gv_print_exp.php 3152 2011-08-01 03:01:19Z dennis $$Rev: 3152 $   
 *  $LastChangedDate: 2008-11-21 09:26:45 +0800 (星期五, 21 十一月 2008) 
 *  $Author: dennis $ 
 ****************************************************************************/

	/**
	 * Copy from pubic_template_DB.php
	 * @param string $programno
	 * @param string $langcode
	 * @return array
	 */
	function getGVColumnConfig($programno,$langcode)
	{
		global $g_db_sql;
		$sql = "select a.table_name,
	                   a.column_name,
	                   (select prompt_text
					      from hcp_muti_lang_pk
					     where muti_lang_pk   =  a.muti_lang_pk
					       and uiculture_code = :langcode
					       and rownum         = 1) as prompt_text,
                       a.data_type/*,
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
                       a.column_seq*/
                  from ehr_program_setup_column a
                 where a.program_no = :programno
                   and a.display    = '1'
                 order by a.column_seq asc";
		//echo $sql;
		//$g_db_sql->debug = 1;
		return $g_db_sql->GetArray($sql,array('programno'=>$programno,
											  'langcode'=>$langcode));	
	}// end getGVColumnConfig()
	
	function registerUser($db, $company_id, $user_seq_no) {
		$plsql_stmt = "begin dodecrypt();pk_erp.p_set_date(sysdate);pk_erp.p_set_segment_no('%s');pk_erp.p_set_username(%s);end;";
		$db->Execute(sprintf($plsql_stmt,$company_id,$user_seq_no));
	} // end function registerUser()
	
	
	function getData($sql)
	{
		global $g_db_sql;
		registerUser($g_db_sql,$_SESSION['user']['company_id'],$_SESSION['user']['user_seq_no']);
		return $g_db_sql->GetArray($sql);
	}
	
	function getReportName($programno,$langcode)
	{
		global $g_db_sql;
		$sql = <<<eof
		select b.value as rpt_desc
		  from app_file a, app_muti_lang b
		 where a.filename = b.name
		   and b.program_no = 'HCP'
		   and b.lang_code = :langcode
		   and a.filetype = 'FORM'
		   and a.filename = :programno
eof;
		return $g_db_sql->GetOne($sql,array('programno'=>$programno,'langcode'=>$langcode));
	}