<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) Lan Jiangtao 
 *	 
 *	Description:
 *     get lost password
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/lostpassword.php $
 *  $Id: lostpassword.php 3843 2014-09-17 08:21:15Z dennis $
 *  $Rev: 3843 $ 
 *  $Date: 2014-09-17 16:21:15 +0800 (周三, 17 九月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-09-17 16:21:15 +0800 (周三, 17 九月 2014) $
 ****************************************************************************/
define ( 'DOCROOT', '..' );
require_once (DOCROOT.'/conf/config.inc.php');
if (isset ($_SERVER ['HTTP_REFERER'])                && 
	strpos($_SERVER ['HTTP_REFERER'], 'findpasswd' ) && 
	isset ($_POST ['email'])                         &&
	!empty($_POST ['email'])                         && 
	isset ($_POST ['user_name'])                     &&
	!empty($_POST ['user_name'])                     &&
	isset ($_POST ['companyno'])                     &&
	!empty($_POST ['companyno'])) {
	// for get company id by dennis 2006-03-09 15:29:30 
	$_url_array = parse_url($_SERVER ['HTTP_REFERER']);
	parse_str($_url_array ['query']);
	// end get comany id
	require_once 'AresUser.class.php';
	$User = new AresUser ( $_POST['companyno'], $_POST ['user_name'] );
	// 共用 ESN0000 的多语
	// by Dennis 20090527
	$multi_lang_msg = get_multi_lang($_POST['langcode'],'ESN0000');
	// check user name
	if (intval($User->IsUserExits ()) == 1) {
		// check user email
		if (intval ( $User->ValidateEmail($_POST['email']))== 1) {
			//print_r($_POST);exit;
			/* remark by dennis 2011-07-21 去掉找回密码问题
			if (intval($User->ValidatePwdAlert( $_POST ['alert_answer'] )) != 1) {
				//showMsg( '您輸入的提示问题的答案不正确: <b>' . $_POST ['alert_answer'] . '</b>,請檢查後重新輸入.', 'error' );
				showMsg($multi_lang_msg['001'].': <b>' . $_POST ['alert_answer'] . '</b>,'.$multi_lang_msg['002'], 'error' );
			}
			*/
			$_new_password = substr(md5(microtime ()), 0, 6); // get 6 chars
			if ($User->ResetPassword($_new_password )) {
			    ini_set('magic_quotes_runtime', 0);
				$_user_info = $User->GetUserInfo ();
				$_title = $_user_info ['SEX'] == 'F' ? $multi_lang_msg['003'] : $multi_lang_msg['004'];
				$subject = $multi_lang_msg['005'];
				$message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><br/>' .$multi_lang_msg['006'].$_user_info['USER_EMP_NAME'] . $_title . ',<br/>';
				$message .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$multi_lang_msg['007'].': <b>' . $_new_password . '</b><br/>';
				$message .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$multi_lang_msg['008'].':'. date ( 'Y-m-d H:m:s' ) . '<br/>';
				$message .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$multi_lang_msg['009'] ;
				$message .= '<a href="http://'.$_SERVER['SERVER_ADDR'].dirname($_SERVER['PHP_SELF']);
				$message .= '/index.php">'.$multi_lang_msg['010'].'</a><br/>'.$multi_lang_msg['011'];
				$message .= '<hr size="1"/>';
				$message .= 'eHR for HCP&trade;<br><div align="right"> Copyright &copy;' . date ( 'Y' ) . ' ARES CHINA</div></body></html>';
				/*
				// clear dirty data before assign value
				$config ['smtp_server'] = '';
				$config ['smtp_user'] = '';
				$config ['smtp_pass'] = '';
				$config ['mail_from'] = '';
				
				// Get SMTP Sever Information from HCP System Setting
				// Add by dennis 2006-11-29 16:47:10  by Dennis.Lan 
				$smtpinfo = $User->GetSysParamVal ( 'SMTP', 'IP ADDRESS' );
				$config ['smtp_server'] = $smtpinfo ['PARAMETER_VALUE'];
				$config ['smtp_user']   = $smtpinfo ['VALUE1'];
				$config ['smtp_pass']   = $smtpinfo ['VALUE2'];
				$config ['mail_from']   = 'no-reply'.substr($_POST['email'],strpos($_POST['email'],'@'));
				
				require_once ($config ['mailer'].'/class.phpmailer.php');				
				if ($config ['mail_method'] == 'mail') {
					ini_set('SMTP', $config ['smtp_server']);
				}
				$mail = new PHPMailer();
				$mail->SMTPDebug = 1;
				$mail->CharSet = 'UTF-8'; // set mail charset
				$mail->IsSMTP (); // set mailer to use SMTP
				$mail->Host = $config ['smtp_server']; // specify main and backup server
				//$mail->SMTPAuth = true;                   // turn on SMTP authentication
				$mail->Username = $config ['smtp_user']; // SMTP username
				$mail->Password = $config ['smtp_pass']; // SMTP password
				$mail->From     = $config ['mail_from'];
				$mail->FromName = 'eHR System';
				//$mail->AddAddress('josh@example.net', 'Josh Adams');
				//$mail->AddAddress('ellen@example.com');            // name is optional
				$mail->AddAddress($_POST['email']); // name is optional
				//$mail->AddReplyTo('info@example.com', 'Information');
				//$this->Mail->AddEmbeddedImage('test.png', 'my-attach', 'test.png','base64', 'image/png')
				$mail->AddEmbeddedImage('../img/logo.gif', 'logo', 'logo.gif', 'base64', 'image/gif' );
				
				$mail->WordWrap = 150; // set word wrap to 50 characters
				//$mail->AddAttachment('/var/tmp/file.tar.gz');         // add attachments
				//$mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // optional name
				$mail->IsHTML ( true ); // set email format to HTML
				$mail->Subject = $subject;
				$mail->Body = '<img src="cid:logo" alt="logo"/>' . $message;
				$mail->AltBody = 'Please enable your mail application HTML support functional.';
				*/
				include 'AresEmployee.class.php';
				$emp = new AresEmployee($_POST['companyno'], '');
				$r = $emp->insMail2DB($_POST['email'], $subject, $message);
				//if (!$mail->Send ()) {
				if (!$r){
					showMsg($multi_lang_msg['012'].'<p>Mailer Error: ' . $mail->ErrorInfo, 'error' );
				}
				showMsg($multi_lang_msg['013'].'<b>' . $_POST ['email'] . '</b>','success','index.php' );
			} else {
				showMsg($multi_lang_msg['014'], 'error' );
			}
		} else {
			showMsg($multi_lang_msg['015'].' :<strong>'. $_POST ['email'].'</strong>', 'error');
		}// end if
	} else {
		showMsg ($multi_lang_msg['016'].' :<strong>'. $_POST ['user_name'].'</strong>', 'error' );
	}// end if
} else {
	showMsg($multi_lang_msg['017'], 'error' );
}// end if

