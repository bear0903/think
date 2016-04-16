<?php
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
} // end if
// Get Company List
$companyid = isset ( $_POST ['companyno'] ) ? 
			 $_POST ['companyno']           : 
			 (isset ( $_GET ['companyno'] ) ? 
			  $_GET ['companyno'] : 
			  (isset ( $_COOKIE ['companyid'] ) ? 
			  	$_COOKIE ['companyid'] : ''));
			  	
$sql = <<<eof
        select dept_seq_no as company_no,
               dept_name   as company_name
          from ehr_department_v
         where dept_type = 'COMPANY'
eof;
$g_parser->ParseSelect('company_list', $sql, 's_company_id', $companyid );


$sql = "select name, value
		  from app_muti_lang
		 where program_no = 'ESN0000'
		   and type_code = 'LI'
		   and lang_code = '".$_GET['lang']."'";
$g_parser->ParseSelect ('question_list', $sql, 'question_id', '' );

