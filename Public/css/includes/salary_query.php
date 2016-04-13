<?php

if (! defined ( 'DOCROOT' ) || 
	! $_SESSION ['salary_view_approve'] ['is_auth']) {
	die ( 'Attack Error.' );
} // end if
require_once 'AresSalary.class.php';
$company_id = $_SESSION['user']['company_id'];
$emp_seq_no = isset($_GET['empseqno']) && !empty($_GET['empseqno']) ? 
              $_GET['empseqno'] : 
              $_SESSION['user']['emp_seq_no'];
              
$Salary = new AresSalary ($company_id,$emp_seq_no);
// ajax call get month list according the year
if (isset ( $_POST ['ajaxcall'] ) && 
    $_POST ['ajaxcall'] == '1'    && 
    isset ( $_POST ['year'] )     && 
    !empty ( $_POST ['year'] )) {
	echo json_encode($Salary->getMonthByYear ($_POST ['year']));
	exit ();
} //end if

// 预设查询最后一次薪资资料
$y = '';
$m = '';
if (! isset ( $_POST ['period_year'] ) && ! isset ( $_POST ['period_month'] )) {
	$ym = $Salary->GetMaxYM ();
	//pr($ym);
	if (is_array ( $ym )) {
		$y = $ym [0];
		$m = $ym [1];
	}
} else {
	$y = $_POST ['period_year'];
	$m = $_POST ['period_month'];
}// end if 

//pr($Salary->GetMonthList());
if (!isset($no_list_where)){ // add by Dennis 2014/08/14
    $g_parser->ParseSelect ('year_list', $Salary->GetYearList(),  's_year', $y );
    $months = !empty($y) ? $Salary->getMonthByYear($y) : $Salary->GetMonthList();
    $g_parser->ParseSelect ('month_list',$months, 's_month',$m );
}
