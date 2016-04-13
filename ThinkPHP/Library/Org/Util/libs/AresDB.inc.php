<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) ARES International Inc.
 *
 * Description:
 *     ADODB connection setting
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresDB.inc.php $
 *  $Id: AresDB.inc.php 3584 2013-11-08 02:09:47Z dennis $
 *  $Rev: 3584 $ 
 *  $Date: 2013-11-08 10:09:47 +0800 (周五, 08 十一月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-11-08 10:09:47 +0800 (周五, 08 十一月 2013) $
 \****************************************************************************/
// set db filed to lower case 0_lowercase 1_upper case 2_native
//define ( 'ADODB_ASSOC_CASE', 0 );

include_once (ADODB_DIR . '/adodb.inc.php');

// Set fetch mode to association
define('ADODB_FETCH_MODE',ADODB_FETCH_ASSOC);

// set query cache tmp dir
$ADODB_CACHE_DIR = $GLOBALS['config']['tmp_dir'];
// connect to oracle database
$g_db_sql = AdoNewConnection($GLOBALS['config']['database']['adapter']);

// add by dennis 2008-04-30
//$g_db_sql->charSet = $GLOBALS['config']['database']['charset'];

$g_db_sql->Connect($GLOBALS['config']['database']['dbname'],
				   $GLOBALS['config']['database']['username'],
				   $GLOBALS['config']['database']['password']) or 
die ('<p align=center><font color="red"><b>Connect to Oracle Database Error <br/>' . $g_db_sql->ErrorMsg () . '</b></font></p>' );
					 
// for security reason, unset database connection information 
// after connection handler gen
// add by dennis 2009-05-15
unset($GLOBALS['config']['database']);
// do a cache
$g_db_sql->cacheSecs = 3600; // cache 1 hours     
// execute decrypt data before query data
$g_db_sql->Execute('begin dodecrypt(); end;');
