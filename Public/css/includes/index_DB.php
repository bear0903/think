<?php
/****************************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     login page
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/index_DB.php $
 *  $Id: index_DB.php 2983 2010-11-01 10:08:15Z dennis $
 *  $LastChangedDate: 2010-11-01 18:08:15 +0800 (周一, 01 十一月 2010) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 2983 $  
 ****************************************************************************/

if (! defined ('DOCROOT' )) {
	die ( 'Attack Error.');
}// end if
$language = isset ( $_POST ['lang'] ) ? 
			$_POST ['lang'] :
			(isset ( $_GET ['lang'] ) ? $_GET ['lang'] : $GLOBALS['config']['default_lang']);

// Get System supported language list
$g_parser->ParseSelect ('language_list', 'select language_code,language_name from ehr_multilang_list', 's_lang_code', $language );

// Get Company List
$companyid = isset ( $_POST ['companyno'] ) ? 
			 $_POST ['companyno']           : 
			 (isset ( $_GET ['companyno'] ) ? 
			  $_GET ['companyno'] : 
			  (isset ( $_COOKIE ['companyid'] ) ? 
			  	$_COOKIE ['companyid'] : ''));
/* Add Order by Company ID  by Dennis 2010.11.01*/			  	
$sql = <<<eof
        select dept_seq_no as company_no,
               dept_name   as company_name
          from ehr_department_v
         where dept_type = 'COMPANY'
         order by dept_id
eof;
$g_parser->ParseSelect ( 'company_list', $sql, 's_company_id', $companyid );
// change cust company logo if exists
$g_tpl->assign('company_logo',getLogoUrl());

