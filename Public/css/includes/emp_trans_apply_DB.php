<?php
/*************************************************************
 * Created By Gracie Zhao
 * Description:
 *   员工异动申请
 * ************************************************************/


if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if
// back 保留表单资料
session_cache_limiter('private');
require_once 'AresTrans.class.php';
$Trans = new AresTrans ( $_SESSION ['user'] ['company_id'],
						   $_SESSION ['user'] ['emp_seq_no']);

if (isset ( $_POST ['trans_date'] ) && 
	! empty ( $_POST ['trans_date'] )) {
	global $result;							   
		//$_trans_date = explode ( '-', $_POST ['trans_date'] );					   
        //$trans_date = date ( '%Y-%m-%d', $_trans_date );						   
		$tmp_save = isset($_POST['submit']) ? 'Y' : 'N';
		$result = $Trans->SaveTransApply($_SESSION ['user'] ['user_seq_no'],
											  $_SESSION ['user'] ['dept_seqno'],
											  $_POST ['trans_date'],
											  $_POST['trans_type'],
											  $_POST ['new_department'],
											  $_POST ['new_title_id'],
											  $_POST ['new_jobcategory'],
											  $_POST ['new_period_id'],
											  $_POST ['new_costallocation'],
											  $_POST ['new_reason'],
											  $_POST ['new_nb_newleader'],
											  $_POST ['new_contract'],
											  $_POST ['new_overtime_type_id'],
											  $_POST ['new_absence_type_id'],
											  $_POST ['new_yeartype_id'],
											  $_POST ['new_job_id'],
											  $_POST ['new_tax_id'],
											  $_POST['remark'],
											  $tmp_save);
		//pr($result);
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
			  
	
	$g_parser->ParseSelect ('new_department_list',$Trans->GetNewdept(),'');	
	$g_parser->ParseSelect ('new_nb_list',$Trans->GetNewnb(),'');	
	$g_parser->ParseSelect ('new_contract_list',$Trans->GetNewcontract(),'');	
	$g_parser->ParseSelect ('new_otype_list',$Trans->GetNewotype(),'');	
	$g_parser->ParseSelect ('new_title_list',$Trans->GetNewtitle(),'');	
	$g_parser->ParseSelect ('new_absence_list',$Trans->GetNewabsence(),'');	
	$g_parser->ParseSelect ('new_jobcategory_list',$Trans->GetNewjobcategory(),'');	
	$g_parser->ParseSelect ('new_year_list',$Trans->GetNewyear(),'');	
	$g_parser->ParseSelect ('new_period_list',$Trans->GetNewperiod(),'');
	$g_parser->ParseSelect ('new_job_list',$Trans->GetNewjd(),'');
	$g_parser->ParseSelect ('new_costallocation_list',$Trans->GetNewcost(),'');
	$g_parser->ParseSelect ('new_tax_list',$Trans->GetNewtax(),'');

	$g_parser->ParseSelect ('transtype_list',$Trans->GetTranstype(),'');	
	//$g_parser->ParseSelect ('reason_list',$Trans->GetNewreason(),'');	

	function getArray_reason($rs)
	{
		//pr($rs);
		$jsarray = '';
		for($i = 0; $i < count ( $rs ); $i ++) {
			$jsarray .='v_newreason_array['.$i.'] = new Array();
			            v_newreason_array['.$i.']["TRANSTYPE_MASTER_ID"] = ["' . $rs [$i] ["TRANSTYPE_MASTER_ID"] . '"];
						v_newreason_array['.$i.']["TRANSTYPE_DETAIL_ID"] = ["' . $rs [$i] ["TRANSTYPE_DETAIL_ID"] . '"];';
		}// end for loop
		return $jsarray;
	}// end getArray_contract()
	$new_reason = $Trans->GetNewreason(ADODB_FETCH_ASSOC);
	$g_tpl->assign ('js_array', getArray_reason($new_reason));
	$g_parser->ParseSelect ('new_reason',$Trans->GetNewreason(),'');	
?>


