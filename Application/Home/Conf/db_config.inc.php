<?php
/**************************************************************************\
 *	 
 *  (C) 2004 Ares China, Inc. All rights reserved. 
 *
 *	Description:
 *	reconfigure database configuration
 *
 *  $CreateBy: Dennis 2008-11-19
 *  $Id: db_config.inc.php 3815 2014-08-12 03:11:51Z dennis $ 
 *  $Date: 2014-08-12 11:11:51 +0800 (周二, 12 八月 2014) $ 
 *  $Author: dennis $ 
 *  $Revision: 3815 $
 *  $LastChangedDate: 2014-08-12 11:11:51 +0800 (周二, 12 八月 2014) $
 **************************************************************************/

if (! defined ( 'DOCROOT' ))die ( 'Attack Error.');
/**
 * Exit Application
 * @return void
 * @author Dennis 20090604
 */
function exit_app()
{
	// add by dennis 
	die('<html>
			<head>
			<meta http-equiv="Content-Type" content = "text/html; charset=utf-8"/>
			<title>eHR Not Installed</title></head>
			<body>
				<div align="center" style="margin-top:88px; margin-left:auto;">
		 		<font color="red"><p>Error: Database configuration file is not exists.</p>
	     		<p>Please contact your HR administrator.</p></font>
	     		</div>
	     	</body>
	     </html>');
}
// check file
/* define(__FILE__, NULL);
if (file_exists(dirname(__FILE__).'./config.php'))
{
	include 'define.ini.php';
}else{
	exit_app();
} */// end if

// check config variables
/* if (!isset($GLOBALS['config']['database']) || !is_array($GLOBALS['config']['database']))
{
	exit_app();
} */// end if

// check install.lock file exists
// add by dennis 2011-10-27 for solve BIS App Server ESS App Server on same Server
// BIS ESS use diff NLS_LANG issues
putenv("NLS_LANG=American_America.UTF8");

// 用下面的 tns name 就不用在 Application Server 上 配置 tns.ora
// for compability old version
$port = isset($GLOBALS['config']['database']['port']) ? $GLOBALS['config']['database']['port'] : 1521;

$GLOBALS['config']['database']['dbname'] = '(DESCRIPTION =
											    (ADDRESS_LIST =
											      (ADDRESS = (PROTOCOL = TCP)(HOST = '.$GLOBALS['config']['database']['host'].')(PORT = '.$port.'))
											    )
											    (CONNECT_DATA = (SID = '.$GLOBALS['config']['database']['dbname'].')(SERVER = DEDICATED))
											  )';
