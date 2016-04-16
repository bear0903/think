<?php
/****************************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     login page
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/hr_check_data_DB.php $
 *  $Id: hr_check_data_DB.php 3861 2014-11-17 05:19:41Z dennis $
 *  $LastChangedDate: 2014-11-17 13:19:41 +0800 (周一, 17 十一月 2014) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3861 $  
 ****************************************************************************/
if (! defined ('DOCROOT' )) {
	die ( 'Attack Error.');
}// end if

/**
 * 
 * Send Mail to Employee
 * @param string $emp_seqno
 * @param string $approve
 * @param string $rows
 * @author Dennis 2011-01-26
 * 
 */
/*
function sendMail($emp_seqno,$approve,$rows = 1)
{
    global $config;
    require_once 'AresEmployee.class.php';
    
	$emp = new AresEmployee ($_SESSION ['user'] ['company_id'], 
						  	 $emp_seqno );
	$_user_info   = $emp->GetBaiscInfo();
	
	$emp1 = new AresEmployee ($_SESSION ['user'] ['company_id'], 
						  	  $_SESSION['user']['emp_seq_no']);
	$current_user = $emp1->GetBaiscInfo();
	
	$approve_lang['en'] = 'Approval';
	$approve_lang['cn'] = '核准';
	$reject_lang['en']	= 'Reject';
	$reject_lang['cn']	= '駁回';
	$result_lang 		= ${$approve};	
	
	$subject = 'Update Personal Information/修改個人資料';
	$message = 'Dear ' . $_user_info ['EMP_NAME'].':<br/>';
	$message .= ' Your submission has been '.$result_lang['en'] .'/';
	$message .= ' 您的資料已經被'.$result_lang['cn'].'<br/>';
	$message .= ' Approver/審核人:' . $current_user['EMP_NAME'] . '<br/>';
	$message .= '<hr size="1"/>';
	$message .= 'eHR for HCP&trade; &nbsp;' . date('Y-m-d H:m:s');
	// clear dirty data before assign value
	$config['smtp_server'] = '';
	$config['smtp_user']   = '';
	$config['smtp_pass']   = '';
	$config['mail_from']   = '';
	// Get SMTP Sever Information from HCP System Setting
	// Add by dennis 2006-11-29 16:47:10  by Dennis.Lan 
	$smtpinfo = $emp->GetSysParamVal('SMTP','IP ADDRESS');
	$config['smtp_server'] = $smtpinfo ['PARAMETER_VALUE'];
	$config['smtp_user']   = $smtpinfo ['VALUE1'];
	$config['smtp_pass']   = $smtpinfo ['VALUE2'];
	$config['mail_from']   = 'no-reply'.substr($_user_info['E_MAIL'],strpos($_user_info['E_MAIL'],'@'));
	if(!empty($_user_info['E_MAIL']) && !empty($config['smtp_server'])){
		require_once ($config['mailer'] . '/class.phpmailer.php');
		
		$mail = new PHPMailer();
		$mail->IsSMTP (); // set mailer to use SMTP
		$mail->Host = $config['smtp_server'];	// specify main and backup server
		$mail->SMTPAuth = false;                // turn on SMTP authentication
		$mail->Username = $config['smtp_user']; // SMTP username
		$mail->Password = $config['smtp_pass']; // SMTP password
		$mail->From     = $config['mail_from']; // Mail From
		$mail->FromName = 'eHR System';
		$mail->AddAddress ($_user_info['E_MAIL']); // name is optional
		$mail->AddReplyTo($config['mail_from'],'no-reply');
		$mail->WordWrap = 150; // set word wrap to 50 characters
		$mail->IsHTML(true); // set email format to HTML
		$mail->CharSet  = "UTF-8";
		$mail->Subject  = "=?utf-8?B?" . base64_encode($subject) . "?="; // add charset by dennis 2012-12-27
		$mail->Body = $message;
		$mail->AltBody = 'Please enable your mail application HTML support functional.';
		if (!$mail->Send()) {
			showMsg ('郵件通知修改人失敗. <br/>錯誤信息: '. $mail->ErrorInfo, 'error' );
		}else{
			// 只審核一筆資料時顯示如下信息
			if ($rows == 1){
				showMsg('資料'.$result_lang['cn'].'成功,且郵件通知修改人.','success');
			}
		}
	}
}*/

/**
 * 
 * @param AresEmployee $emp
 * @param unknown $empseqno
 * @param unknown $approve
 * @param unknown $approve_comment
 */
function insert2MailLog(AresEmployee $emp,$empseqno,$approve,$approve_comment)
{
    $to_emp = $emp->getEmpNameMail($empseqno);
    $curr_user =  $emp->getEmpNameMail($_SESSION['user']['emp_seq_no']);
    
    $approve_lang['en'] = 'Approval';
    $approve_lang['cn'] = '核准';
    $reject_lang['en']	= 'Rejected';
    $reject_lang['cn']	= '駁回';
    $result_lang 		= ${$approve};
    
    $mailto  = '"'.$to_emp['EMP_NAME'].'" <'. $to_emp['EMAIL'].'>';
    
    $subject = 'Update Personal Information/個人資料修改';
    $message = 'Dear ' . $to_emp['EMP_NAME'].':<br>';
    $message .= ' Your submission has been '.$result_lang['en'] .'/';
    $message .= ' 您修改的個人資料已經被'.$result_lang['cn'].'<br>';
    $message .= $approve == 'reject_lang' ? '駁回原因/Reject reason：'.$approve_comment.'<br>' : '';
    $message .= ' Approver/審核人:' . $curr_user['EMP_NAME'] . '<br>';
    $message .= '<hr size="1">';
    $message .= 'eHR for HCP ' . date('Y-m-d H:m:s');
    
    $emp->insMail2DB($mailto, $subject, $message);
    
}

require_once 'AresEmployee.class.php';
$Employee = new AresEmployee ($_SESSION ['user'] ['company_id'], 
							  $_SESSION ['user'] ['emp_seq_no'] );

if (isset($_POST['emp_seqno']) && count($_POST['emp_seqno'])>0)
{
	$countApprove = 0;
	$countReject  = 0;
	$rows = count($_POST['emp_seqno']);
	for ($i=0; $i<$rows; $i++)
	{		
		$approve_action = $_POST['approve_action'.$_POST['emp_seqno'][$i]]; //pr($_POST);
		$approve_remark = $_POST['approve_remark'.$_POST['emp_seqno'][$i]];
		$data[$i]['seg_segment_no'] 		    = $_SESSION ['user'] ['company_id'];
		$data[$i]['id']             		    = $_POST['emp_seqno'][$i];
		if($approve_action == 'Y'){  //核准
			$data[$i]['mailaddress']    		= $_POST['mailaddress'][$i];
			$data[$i]['address_tel']    		= $_POST['address_tel'][$i];
			$data[$i]['address']        		= $_POST['address'][$i];
			$data[$i]['address_man']    		= $_POST['address_man'][$i];
			$data[$i]['mailaddresszipcode']		= $_POST['mailaddresszipcode'][$i];
			$data[$i]['mailaddress_man']    	= $_POST['mailaddress_man'][$i];
			$data[$i]['addresszipcode']     	= $_POST['addresszipcode'][$i];
			$data[$i]['emergencycontactor']		= $_POST['emergencycontactor'][$i];
			$data[$i]['emergencycontactor_tel'] = $_POST['emergencycontactor_tel'][$i];
			$data[$i]['mobiletel']				= $_POST['mobiletel'][$i];
			$data[$i]['tel_part']  				= $_POST['tel_part'][$i];
			
			$r = $Employee->UpdateEmpInfo($data[$i],'hr_personnel_base');
			if (1 == $r)
			{
				// 把暂存区的资料删除
				//$sql = 'delete from ehr_pim_tmp where company_id = :company_id and emp_seqno = :emp_seqno';
				//$g_db_sql->Execute($sql,array('company_id'=>$data[$i]['seg_segment_no'],'emp_seqno'=>$data[$i]['id']));
				// rewrite by Dennis 2014/04/14
				$Employee->delPIMTmpData($data[$i]['seg_segment_no'],$data[$i]['id']);
			}
			//sendMail($_POST['emp_seqno'][$i],'approve_lang',$rows);
			insert2MailLog($Employee, $_POST['emp_seqno'][$i], 'approve_lang', $approve_remark);
			$countApprove++;
		}else if($approve_action=='N'){  //駁回
			//$sql = 'delete from ehr_pim_tmp where company_id = :company_id and emp_seqno = :emp_seqno';
			//$g_db_sql->Execute($sql,array('company_id'=>$_SESSION ['user'] ['company_id'],'emp_seqno'=>$_POST['emp_seqno'][$i]));
			//sendMail($_POST['emp_seqno'][$i],'reject_lang',$rows);
			$Employee->delPIMTmpData($data[$i]['seg_segment_no'],$data[$i]['id']);
			insert2MailLog($Employee, $_POST['emp_seqno'][$i], 'reject_lang', $approve_remark);
			$countReject++;
		}
	}// end if
	
	showMsg('審核:'.$countApprove.'筆<br/>駁回:'.$countReject.'筆,結果已郵件通知修改人員.','success',
			'?scriptname=hr_check_data');
}else{
	// 把员工修改的资料和人事资料档中的资料做 比对，把不同的栏位背景色变成黄色
	$default_data = $Employee->getOldData();
	$emp_edit_data = $Employee->getEmpEditDataList();
	$diff_columns = array();
	//pr($emp_edit_data);
	for ($i=0; $i<count($emp_edit_data); $i++)
	{
		for ($j=0; $j<count($default_data); $j++)
		{
			if ($default_data[$i]['COMPANY_ID'] == $emp_edit_data[$j]['COMPANY_ID'] &&
			    $default_data[$i]['EMP_SEQNO'] == $emp_edit_data[$j]['EMP_SEQNO'])
		    {
		    	foreach ($emp_edit_data[$i] as $okey=>$ovalue)
		    	{
		    		if ($ovalue != $default_data[$j][$okey])
		    		{
		    			$diff_columns[$j][$okey]['class'] =  'notice';
		    		}// end if
		    		$diff_columns[$j][$okey]['val'] = $emp_edit_data[$j][$okey];
		    	}// end foreach
		    	break;
		    }// end if
		}// end loop
	}// end loop
	//pr($diff_columns);
	$g_parser->ParseTable('emp_list',$diff_columns);
}
