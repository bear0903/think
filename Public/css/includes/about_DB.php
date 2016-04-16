<?php
/**
 * 关于系统
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/about_DB.php $
 *  $Id: about_DB.php 2688 2010-05-06 01:36:30Z dlan $
 *  $Rev: 2688 $ 
 *  $Date: 2010-05-06 09:36:30 +0800 (周四, 06 五月 2010) $
 *  $Author: dlan $   
 *  $LastChangedDate: 2010-05-06 09:36:30 +0800 (周四, 06 五月 2010) $
 ****************************************************************************/

if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}

// Get Client && DB information
$sql = <<<eof
		select sys_context('userenv', 'db_name') as db_name,
			   sys_context('userenv', 'language')as db_charset
		  from dual
eof;
$g_parser->ParseOneRow ( $sql );
$company_name = $g_db_sql->GetOne ('select dept_name 
									  from ehr_department_v
								     where dept_seq_no =:company_id 
									   and dept_type = \'COMPANY\'', array ('company_id' => $_SESSION ['user'] ['company_id'] ) );
$db_version = $g_db_sql->GetOne ("select banner as db_version from v\$version where rownum <2");

//$build_no = $g_db_sql->GetOne ("select max(patch_name) as build_no from ehr_upgrade_config");
$g_tpl->assign ('DB_VERSION', $db_version );
$g_tpl->assign ('ESS_APP_VERSION', ESS_APP_VERSION);
$g_tpl->assign ('LISENCE_OWNER', $company_name);
$g_tpl->assign ('SERVER_OS', php_uname());

    