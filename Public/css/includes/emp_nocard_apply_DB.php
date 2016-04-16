<?php
/*************************************************************
 * Created By Gracie Zhao
 * Description:
 *   员工忘刷申请
 *   Last update by Dennis for apply multiple periods by one Form
 *   FIXME: 检查 fs_get_flow_setting, 当有多个忘刷原因时，每阶主管会存多次，次数跟原因一一对应
 * ************************************************************/

if (! defined ( 'DOCROOT')) {
	die ( 'Attack Error.');
}// end if
require_once 'AresTrans.class.php';
$Nocard = new AresTrans($_SESSION ['user'] ['company_id'],
						$_SESSION ['user'] ['emp_seq_no']);
						
function getDateStr($date,$hour,$minute)
{
	return $date.' '.$hour.':'.$minute.':00';
}// end getTime();
	
if (isset($_POST ['nocard_date']) && 
	!empty($_POST['nocard_date'])) {
		/**
		  [nocard_date] => 2011-11-23
		  [nocard_reason] => 513
		    [shift_type_01] => on
		    [nocard_reason_01] => 
		    [nocard_01_hour] => 13
		    [nocard_01_minute] => 00
		    [nocard_reason_02] => 
		    [nocard_02_hour] => 
		    [nocard_02_minute] => 
		    [nocard_reason_03] => 
		    [nocard_03_hour] => 
		    [nocard_03_minute] => 
		    [nocard_reason_04] => 
		    [nocard_04_hour] => 
		    [nocard_04_minute] => 
		    [nocard_reason_05] => 
		    [nocard_05_hour] => 
		    [nocard_05_minute] => 
		    [nocard_reason_06] => 
		    [nocard_06_hour] => 
		    [nocard_06_minute] => 
		    [remark] => 
		 */
		$date1='';
		$date2='';
		$date3='';
		$date4='';
		$date5='';
		$date6='';
		$reason1='';
		$reason2='';
		$reason3='';
		$reason4='';
		$reason5='';
		$reason6='';
		$shift_type1='';
		$shift_type2='';
		$shift_type3='';
		$shift_type4='';
		$shift_type5='';
		$shift_type6='';
		$nocard_date = $_POST['nocard_date'];
		if (isset($_POST['shift_type_01']) && $_POST['shift_type_01'] == 'on')
		{
			$date1 = getDateStr($nocard_date,$_POST['nocard_01_hour'],$_POST['nocard_01_minute']);
			$reason1 = empty($_POST['nocard_reason_01']) ? $_POST['nocard_reason'] : $_POST['nocard_reason_01'];
			$shift_type1 = '01';
		}
		if (isset($_POST['shift_type_02']) && $_POST['shift_type_02'] == 'on')
		{
			$date2 = getDateStr($nocard_date,$_POST['nocard_02_hour'],$_POST['nocard_02_minute']);
			$reason2 = empty($_POST['nocard_reason_02']) ? $_POST['nocard_reason'] : $_POST['nocard_reason_02'];
			$shift_type2 = '02';
		}
		if (isset($_POST['shift_type_03']) && $_POST['shift_type_03'] == 'on')
		{
			$date3 = getDateStr($nocard_date,$_POST['nocard_03_hour'],$_POST['nocard_03_minute']);
			$reason3 = empty($_POST['nocard_reason_03']) ? $_POST['nocard_reason'] : $_POST['nocard_reason_03'];
			$shift_type3 = '03';
		}
		if (isset($_POST['shift_type_04']) && $_POST['shift_type_04'] == 'on')
		{
			$date4 = getDateStr($nocard_date,$_POST['nocard_04_hour'],$_POST['nocard_04_minute']);
			$reason4 = empty($_POST['nocard_reason_04']) ? $_POST['nocard_reason'] : $_POST['nocard_reason_04'];
			$shift_type4 = '04';
		}
		if (isset($_POST['shift_type_05']) && $_POST['shift_type_05'] == 'on')
		{
			$date5 = getDateStr($nocard_date,$_POST['nocard_05_hour'],$_POST['nocard_05_minute']);
			$reason5 = empty($_POST['nocard_reason_05']) ? $_POST['nocard_reason'] : $_POST['nocard_reason_05'];
			$shift_type5 = '05';
		}
		if (isset($_POST['shift_type_06']) && $_POST['shift_type_06'] == 'on')
		{
			$date6 = getDateStr($nocard_date,$_POST['nocard_06_hour'],$_POST['nocard_06_minute']);
			$reason6 = empty($_POST['nocard_reason_06']) ? $_POST['nocard_reason'] : $_POST['nocard_reason_06'];
			$shift_type6 = '06';
		}
				 
		$tmp_save = isset($_POST['submit']) ? 'Y' : 'N';
		$result = $Nocard->SaveNocardApply($_SESSION['user']['user_seq_no'],
										   $_SESSION['user']['dept_seqno'],
										   $date1,
										   $date2,
										   $date3,
										   $date4,
										   $date5,
										   $date6,
										   $reason1,
										   $reason2,
										   $reason3,
										   $reason4,
										   $reason5,
										   $reason6,
										   $shift_type1,
										   $shift_type2,
										   $shift_type3,
										   $shift_type4,
										   $shift_type5,
										   $shift_type6,
										   $_POST['remark'],
										   $tmp_save);
		//pr($result);
		if ($result ['is_success'] == 'Y') {
			if (! empty ( $_POST ['submit'])) {
				showMsg($result['msg'], 'success');
			}// end if
			if (! empty ( $_POST ['save'])) {
				showMsg($result ['msg']);
			}// end if
		} else {
			showMsg($result ['msg'],'error');
		}// end if
}// end if
//pr();
$g_parser->ParseSelect('nocard_reason_list',$Nocard->GetNocardReason(),'');	
$g_parser->ParseTable('shiftype_list', $Nocard->GetShifttype(ADODB_FETCH_ASSOC));
