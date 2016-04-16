<?php
/*************************************************************
 * Created By Gracie Zhao
 * Description:
 *   员工离职留停申请
 * ************************************************************/
	if (! defined ( 'DOCROOT' )) {
		die ( 'Attack Error.' );
	}// end if
	// back 保留表单资料
	session_cache_limiter('private');
	require_once 'AresTrans.class.php';
	$Resign = new AresTrans ( $_SESSION ['user'] ['company_id'],
							   $_SESSION ['user'] ['emp_seq_no']);
	function _toDate($date,$hour,$minute)
	{
		return $date.' '.$hour.':'.$minute.':00';
	}// end getTime();
	
	if (isset ( $_POST ['resign_date'] ) && 
		! empty ( $_POST ['resign_date'] )) {
		global $result;							   
		//$_trans_date = explode ( '-', $_POST ['trans_date'] );					   
        //$trans_date = date ( '%Y-%m-%d', $_trans_date );						   
		$tmp_save = isset($_POST['submit']) ? 'Y' : 'N';
		$result = $Resign->SaveResignApply($_SESSION ['user'] ['user_seq_no'],
										  $_SESSION ['user'] ['dept_seqno'],
                                          $_POST['resign_date'],												  
										  $_POST['resign_reason'],
										  $_POST ['out_type'],
										  $_POST['remark'],
										  $tmp_save);
		
		if ($result ['is_success'] == 'Y') {
			if (! empty ( $_POST ['submit'] )) {
				showMsg($result ['msg'], 'success');
			}// end if
			if (! empty ( $_POST ['save'] )) {
				showMsg($result ['msg']);
			}// end if
		} else {
			showMsg($result ['msg'],'error' );
		}// end if
	}// end if		
			  
	
	$g_parser->ParseSelect ('resign_reason_list',$Resign->GetResignReason(),'');	
	//$g_parser->ParseSelect ('out_type_list',$Resign->GetOuttype(),'');	
    $g_parser->ParseSelect ('out_type_list', 
							$Resign->getListMultiLang('ESNA025',
													  'OUT_TYPE',
													  $GLOBALS ['config'] ['default_lang']),
							'');  