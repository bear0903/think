<?php
/**
 * KPI Alert Send Mail 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAlertMail.php $
 *  $Id: AresAlertMail.php 2076 2009-08-12 09:14:26Z dennis $
 *  $Rev: 2076 $ 
 *  $Date: 2009-08-12 17:14:26 +0800 (周三, 12 八月 2009) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2009-08-12 17:14:26 +0800 (周三, 12 八月 2009) $
 \****************************************************************************/
	session_cache_expire(1);
	session_start();
	define('DOCROOT', '..');
	define('ADODB_DIR','./adodb');
	require_once '../conf/db_config.inc.php'; // database configuration file
	require_once './AresDB.inc.php'; // AdoDB
	require_once('./phpMailer/class.phpmailer.php');
	require_once('./AlertGraph.php');
	//print_r($g_db_sql);exit;
	/*
	$sql = "select count(*) RCT from ehr_alert_setting 
					where user_seqno='".$_SESSION['user']['user_seq_no']."'";
	$arr = $g_db_sql->GetRow($sql);  
	print_r($arr);
	*/
	class timer {   
		private $_startTime = 0;   
		private $_stopTime = 0;   
		private $_timeSpent = 0;   

		function start(){   
			$this->_startTime = microtime();   
		}// end start()
		function stop(){   
			$this->_stopTime   = microtime();   
		}// end stop()
		function spent() {   
			if ($this->_timeSpent) {   
				return $this->_timeSpent;   
			} else {   
				$StartMicro	= substr($this->_startTime,0,10);   
				$StartSecond= substr($this->_startTime,11,10);   
				$StopMicro  = substr($this->_stopTime,0,10);   
				$StopSecond = substr($this->_stopTime,11,10);   
				$start		= doubleval($StartMicro) + $StartSecond;   
				$stop		= doubleval($StopMicro) + $StopSecond;   
				$this->_timeSpent = $stop - $start;   
				return substr($this->_timeSpent,0,8);   
			}// end if
		}// end spent();
	} //end class timer;

	class Alert{
		private $db;
		private $alertRow;
		private $alertAction;
		public function __construct(){
			global $g_db_sql;
			$this->db = $g_db_sql;
		}
		public function getAlert($key){
			$sql = "select * from ehr_alert_setting where alert_seqno='".$key."'";
			$arr = $this->db->GetRow($sql);  //print_r($arr);exit;
			$this->alertRow = $arr;
			
			//get condition
			$sql = "select * from ehr_alert_condition_list where ALERT_SEQNO='".$key."' order by SHOW_NO";
			$rs=$this->db->GetArray($sql);
			$this->alertAction = $rs;
		}
		public function patrol(){
			$sql = "select * from ehr_alert_setting where user_seqno='48176'";
			$rs=$this->db->GetArray($sql);
			for($i=0;$i<count($rs);$i++){
				//print_r($rs[$i]);
			    $alert_seqno=$rs[$i]['ALERT_SEQNO'];
			    $this->sendMail($alert_seqno);
			}
			/*
			print '<pre>';
			print_r($receiver);
			print '</pre>';
			*/
		}
		
		public function sendMail($key){
			$timer = new timer; 
			$timer->start();
			$this->getAlert($key);
			//print '<pre>';print_r($this->alertAction);print '</pre>';
			for($i=0;$i<count($this->alertAction);$i++){
				if(empty($this->alertAction[$i]['MAIL_TO']))  continue;
				//echo $this->alertAction[$i]['MAIL_TO'];
				$mail = new PHPMailer();
				$mail->Priority = $this->alertAction[$i]['MAIL_PRIORITY'];		// 紧急度 1 - High 3 - Normal(default) 5 - Low
				//echo $this->alertAction[$i]['MAIL_PRIORITY'];exit;
				$mail->CharSet= 'utf-8';		// 设置邮件内容字符集
				$mail->SetLanguage('zh');		// 设置语言,出错时显示的错误信息.
				$mail->IsSMTP();				// 设置使用 SMTP
				$mail->Host = '192.168.0.14';   // 指定的 SMTP 服务器地址
				$mail->SMTPAuth = true;         // 设置为安全验证方式
				$mail->Username = 'developer';  // SMTP 发邮件人的用户名
				$mail->Password = 'developer';  // SMTP 密码
				$mail->From = 'test@areschina.com';
				$mail->FromName = 'HCP Alert System';
				//$mail->AddReplyTo('dlan@areschin.com', 'Information');   //回复地址
				
				// TO
				$arr=$this->getReceiverByParseString($this->alertAction[$i]['MAIL_TO']);
				$receiver = $this->getReceiver($arr);
				$k=0;
				for($j=0;$j<count($receiver);$j++){
					if(empty($receiver[$j]['mail'])) continue;
					$mail->AddAddress($receiver[$j]['mail'], $receiver[$j]['name']);//收件人地址
					$k++;
				}
				if($k==0) continue;
				// CC
				$arr=$this->getReceiverByParseString($this->alertAction[$i]['MAIL_CC']);
				$receiver = $this->getReceiver($arr);
				for($j=0;$j<count($receiver);$j++){
					if(empty($receiver[$j]['mail'])) continue;
					$mail->AddCC($receiver[$j]['mail'], $receiver[$j]['name']); // 抄送
				}
				// BCC
				$arr=$this->getReceiverByParseString($this->alertAction[$i]['MAIL_BCC']);
				$receiver = $this->getReceiver($arr);
				for($j=0;$j<count($receiver);$j++){
					if(empty($receiver[$j]['mail'])) continue;
					$mail->AddBCC($receiver[$j]['mail'], $receiver[$j]['name']);// 密送
				}		
				$mail->Subject = $this->alertRow['ALERT_DESC'];	    // 标题
				
				$mail->AltBody = 'Please enable your mail application HTML support functional.';
				$mail->WordWrap = 50;			// set word wrap to 50 characters
				$mail->IsHTML(true);                  // 设置邮件格式为 HTML      logo.gif
				//$mail->AddEmbeddedImage('../img/logo.gif', 'logo', 'logo.gif','base64', 'image/gif');
				$mail->AddEmbeddedImage('./kpi.png', 'logo', 'bargra.png','base64', 'image/png');
				$html = '';
				$html .= '<p>Dears : </p>';
				$html .= '<img src="cid:logo" alt="logo"/>';
				$html .= $this->alertAction[$i]['KPI_ACTION'];
				$html .= '<p>本郵件由系統自動發送,請不要回復此郵件.';
				$mail->Body    = $html;
				$mail->AltBody = 'Please enable your mail application HTML support functional.';
				/*
				$message = '<br/>尊敬的 Dennis,<br/>';
				$message .= '&nbsp;&nbsp;&nbsp;&nbsp;您的 eHR 登錄密碼重置為: <b>'.md5(time()).'</b><br/>';
				$message .= '&nbsp;&nbsp;&nbsp;&nbsp;密碼被重置的時間為:'.date('Y-m-d H:m:s').'<br/>';
				$message .= '&nbsp;&nbsp;&nbsp;&nbsp;密碼是隨機設置的,系統管理員也不知道您的密碼,請登錄後及時修改您的密碼.<a href="http://www.areschina.com">點這裡</a> 本郵件由系統自動發送,請不要回復此郵件.';
				$message .= '<hr size="1"/>';
				$message .= 'eHR for HCP&trade;<br><div align="right"> Copyright &copy;'.date('Y').' ARES China Inc.</div>';
				$mail->AddEmbeddedImage('D:/eHR4/eHR3/img/logo.gif', 'logo', 'logo.gif','base64', 'image/gif');
				//$mail->AddEmbeddedImage('D:\code.jpg', 'logo', 'logo.jpg','base64', 'image/jpeg');
				if (is_readable('D:\test.txt')) unlink('D:\test.txt');
				//exec('del D:\code.jpg');
				$mail->WordWrap = 50;			// set word wrap to 50 characters
				$mail->IsHTML(true);                  // 设置邮件格式为 HTML                              // 
				$mail->AddAttachment('D:/eHR4/eHR3/img/hcplogin_wellcome.swf');         // add attachments
				$mail->AddAttachment('D:/eHR4/eHR3/img/hcplogin_ess.gif', 'ess.gif');    // optional name
				$mail->Body    = '<img src="cid:logo" alt="logo"/>'.$message;
				$mail->AltBody = 'Please enable your mail application HTML support functional.';
				*/
				if(!$mail->Send())
				{
				  echo 'failure. <p>';
				  echo 'Mailer Error: ' . $mail->ErrorInfo;
				  // write log file
				  exit;
				}
				echo "sucessfully!\r\n";
	
				$timer->stop();
				echo 'use '.$timer->spent().' sec';
				// write log file
			}
		}
		
		public function getReceiverByParseString($key){
			//print '<pre>';print_r($key);print '</pre>';
			$arr1=explode('];',$key);
			//print '<pre>';print_r($arr1);print '</pre>';
			
			$receiver = array();
			for($i=0;$i<count($arr1);$i++){
			   if(empty($arr1[$i])) continue;
			   $arr2 = explode('[',$arr1[$i]);
			   $arr3 = explode(':',$arr2[1]);
			   //print '<pre>';print_r($receiver);print '</pre>';

		   	   $arr3 = explode(':',$arr2[1]);
			   $receiver[$i]['name']=$arr2[0];
			   $receiver[$i]['type']=$arr3[0];
			   $receiver[$i]['no']=$arr3[1];
			}
			//print '<pre>';print_r($receiver);print '</pre>';
			return $receiver;
		}
		
		public function getReceiver($list){
			$receiver = array();
			$k=0;
			//print '<pre>';print_r($list);print '</pre>';
			for($i=0;$i<count($list);$i++){
				if($list[$i]['type']=='user'){
					$receiver[$k]['mail'] = $this->getMailAddressByUserNo($list[$i]['no']);
					$receiver[$k]['no'] = $list[$i]['no'];
					$receiver[$k]['name'] = $list[$i]['name'];
					$receiver[$k]['type'] = 'user';
					$k++;
				}else{
					$sql = "  SELECT HPB.EMAIL         USER_EMAIL,
							       AUB.USERNAME       USER_SEQNO,
							       HPB.NAME_SZ        USER_NAME
							  FROM HR_PERSONNEL_BASE HPB,
							       APP_USERS_BASE AUB,
							       Ehr_Mail_Group_Detail EMGM
							 WHERE HPB.ID = AUB.PSN_ID
							   and AUB.USERNAME = EMGM.Receiver_Emp_Seqno
							   and hpb.email is not null
							   and EMGM.GROUP_ID = '".$list[$i]['no']."'";
					//echo $sql;
					$rs=$this->db->GetArray($sql);
					for($j=0;$j<count($rs);$j++){
						$receiver[$k]['no']=$rs[$j]['USER_SEQNO'];
						$receiver[$k]['name']=$rs[$j]['USER_NAME'];
						$receiver[$k]['mail']=$rs[$j]['USER_EMAIL'];
						$receiver[$k]['type'] = 'group';
						$k++;
					}
				}
			}
			//print '<pre>';print_r($receiver);print '</pre>';
			return $receiver;
		}
		public function getMailAddressByUserNo($userNo){
			$sql = "SELECT HPB.EMAIL         USER_EMAIL,
					       AUB.USERNAME       USER_SEQNO,
					       HPB.NAME_SZ        USER_NAME
					  FROM HR_PERSONNEL_BASE HPB,
					       APP_USERS_BASE AUB
					 WHERE HPB.ID = AUB.PSN_ID
					   AND AUB.USERNAME  = '".$userNo."'";
			$arr = $this->db->GetRow($sql);  //print '<pre>';print_r($arr);print '</pre>';//exit;
			return $arr['USER_EMAIL'];
		}
	}

	$alert = new Alert();
	$alert->patrol();
	