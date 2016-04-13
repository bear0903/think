<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) Ares Internatinal Inc.
 *
 *	Description:
 *     User Login Auth
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/login.php $
 *  $Id: login.php 3596 2013-11-28 02:50:37Z dennis $
 *  $Rev: 3596 $ 
 *  $Date: 2013-11-28 10:50:37 +0800 (周四, 28 十一月 2013) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2013-11-28 10:50:37 +0800 (周四, 28 十一月 2013) $
 ****************************************************************************/
define ( 'DOCROOT', '../..' );
include_once (DOCROOT.'/conf/config.inc.php');
$cookieDomain = dirname ( dirname ( $_SERVER ['PHP_SELF'] ) );
// define the manager desk home url for redirect when login failure
$home_url = $GLOBALS['config']['curr_home'] . '/index.php';
// add by dennis 2010-06-07
$authtype = isset($_GET['authtype']) && !empty($_GET['authtype']) ?
            $_GET['authtype'] : 'default';

$companyid = '';
$username  = '';
$passwd    = '';
// detect login type
switch ($authtype) {
	// 正常從 ESS Login　頁面登錄
	case 'default':
		if (isset($_POST['companyno']) && 
			isset($_POST['username'])  && 
			isset($_POST['password']))
		{
			$username  = htmlentities ($_POST['username'], ENT_QUOTES, 'UTF-8' );
			$companyid = $_POST['companyno'];
			$passwd    = $_POST['password'];	
		}
	break;
	// 集成 windows AD 驗證, OS 驗證密碼，這裏只做 user 對應
	// Auto fetch User Login Informatin from HCP Database
	case 'sspi':
		if (isset($_GET['remote_user'])    &&
			!empty($_GET['remote_user'])   &&
			isset($_GET['sessid'])         &&
			!empty($_GET['sessid']))
		{
			require_once 'AresAuth.php';
			require_once 'AresAuthSSPIAdapter.php';
			$sspi_auth = new AresAuthSSPIAdapter($g_db_sql,$_GET['remote_user']);
			$auth = AresAuth::getInstance();
			$result = $auth->authenticate($sspi_auth);
			if($result->isValid())
			{
				$username  = $auth->getIdentity();
				$companyid = $sspi_auth->getCompanyId();
				$passwd    = $sspi_auth->getPasswd();
			}
		}
	// 從 EIP Url Link 驗證
	// modify by dennis 2013/11/21 从 eip 连过来的数据我们要求用 base64 方式来加密
	case 'eip':
		if (isset($_GET['companyno']) && 
			isset($_GET['username'])  && 
			isset($_GET['password']))
		{
			$username  = htmlentities(base64_decode($_GET['username']),ENT_QUOTES,'UTF-8');
			$companyid = base64_decode($_GET['companyno']);
			$passwd    = base64_decode($_GET['password']);
		}
	break;
	default:break;
}

if (!empty($companyid) && !empty($username) && !empty($passwd))
{
	$langcode  = isset($_POST['lang']) && !empty($_POST['lang']) ?
		         $_POST['lang']:
		         $GLOBALS['config']['default_lang'];
	// User login authentication class
	require_once 'AresUser.class.php';
        echo $companyid ;
        echo $username;  
     require_once 'KL_AresUser.class.php';    
    $KLUser = new KL_AresUser($companyid,$username);
	$username= $KLUser->KL_check_user($username);
	echo $companyid ;
    echo $username; 
	//exit;
	$User = new AresUser($companyid,$username);
	$home_url .= '?lang=' .$langcode. '&companyno=' .$companyid;
	$home_url .= '&loginerror=';
	// Step 1: User name exists users list
	if ($User->IsUserExits ()) {
		/*
         *   Step 2 Check current login user is an employee
         */
		/*
		if ($User->IsEmployee ()) {
			/* Step 3
             * Check User Password
             *  1. must be a employee
             *  2. employee must be onjob status = JS1
             *  3. Password is correct
             */
			if ($User->isPasswordValid($passwd)) {
				/**
				 *   Step 4 Check Permission
				 *   $module_name  config.inc.php
				 *   login successfully write cookie 
				 */
				//if ($User->CheckPermission ( strtoupper ( $GLOBALS['module_name']) )) { // remark by dennis 20090422
				$mss_perm = $User->CheckPermission ('MDN');
				if ($User->CheckPermission ('ESN') or '1' == $mss_perm ) {
					setCookie ('companyid',$companyid, time () + 3600 * 24 * 365, $cookieDomain );
					setCookie ('language', $langcode, time () + 3600 * 24 * 365, $cookieDomain );
					setCookie ('username', $username, time () + 3600 * 24 * 365, $cookieDomain );
					$_SESSION ['user']['language'] = $langcode;
					// get user profile
					$result = $User->GetUserInfo ();
					$_SESSION ['user']['company_id']  = $companyid; // user company id
					$_SESSION ['user']['user_seq_no'] = $result ['USER_SEQ_NO']; // user seq no in table app_users
					$_SESSION ['user']['emp_seq_no']  = $result ['USER_EMP_SEQ_NO']; // user match emp seq no in table hr_personnel
					$_SESSION ['user']['emp_id']      = $result ['USER_EMP_ID']; // user match emp id
					$_SESSION ['user']['emp_name']    = $result ['USER_EMP_NAME']; // user match emp name
					$_SESSION ['user']['user_name']   = $username; // user login id
					$_SESSION ['user']['sex']         = $result ['SEX']; // user sex
					$_SESSION ['user']['dept_seqno']  = $result ['DEPT_SEQNO']; // user dept seq no
					$_SESSION ['user']['dept_id']     = $result ['DEPT_ID']; // user dept id
					$_SESSION ['user']['dept_name']   = $result ['DEPT_NAME']; // user dept name
					//$_SESSION ['user']['password']  = $passwd; // user login password; for security reason, don't store the password
					$_SESSION ['user']['title_id']    = $result ['TITLE_ID']; // user login title_id 
					$_SESSION ['user']['title_name']  = $result ['TITLE_NAME']; // title name
					$_SESSION ['user']['title_level'] = $result ['TITLE_LEVEL']; // title level
					$_SESSION ['user']['join_date']   = $result ['JOIN_DATE']; // in date
					// add by dennis 2008-06-19 
					$_SESSION ['user']['is_manager1'] = $User->IsManager($result ['USER_EMP_SEQ_NO']); // 记录当前员工是不是 Manager
					// Modify by Dennis 2009-03-24
					$_SESSION ['user']['is_manager']  = $mss_perm;
					unset($result);
					// add by Dennis 2012-03-05 for check first login
					$_SESSION['user']['not_first_login'] = $User->isFirstLogin($passwd);
					// add by jack 2006-8-22	
					$User->AddLoginList($_SESSION ['user']['user_seq_no'],'eHR');
					if(strtoupper($User->getDefaultHome()) == 'MD' ){
						header ('Location:'.$GLOBALS['config']['mgr_home'].'/redirect.php');
					}else {
						header ('Location:'.$GLOBALS['config']['ess_home'].'/redirect.php');
					}// end if
					exit();
				} else {
					header('Location: '.$home_url.urlencode('未授权.'));
					exit();
				}// end if
			} else {
				header('Location: '.$home_url.urlencode('Password error.'));
				exit();
			}// end if
			/*
		} else {
			header('Location: '.$home_url.urlencode('Login User Must be an Employee.'));
			exit();
		}// end if
		*/
	} else {
		header('Location: '.$home_url.urlencode('The user name does not exist.'));
		exit();
	}// end if
}else{
	header('Location: '.$home_url.'?loginerror='.urlencode('Attack error.'));
	exit();
}