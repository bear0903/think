<?php

$no_list_where = TRUE;
include_once 'salary_query.php';

$year_insure = $Salary->getLeastTwoYearInsureList($y);
// for security reason, encypt the salary query parameters
$encrypt_key = md5($_SESSION['user']['emp_seq_no'].session_id());

$cnt = count($year_insure);
for($i=0; $i<$cnt; $i++)
{
    $year_insure[$i]['MASTER_ID']   = encrypt($year_insure[$i]['MASTER_ID'],$encrypt_key);
    $year_insure[$i]['DETAIL_ID']   = encrypt($year_insure[$i]['DETAIL_ID'],$encrypt_key);
}

function getYearInsList($year,$data){
    $year_sal_list = array();
    //echo $year.'<br/>';
    
    //array_reverse($data);
    foreach($data as $val)
    {
        if (substr($val['YEAR_MON'],0,4) == $year){
            $year_sal_list[] = $val;
        }
    }
    return $year_sal_list;
}
$year_insure_list = getYearInsList($y, $year_insure);
//pr($year_salary_list);
$pyear_insure_list = getYearInsList((int)$y-1, $year_insure);

//pr($pyear_salary_list);
/**
 * 根据 period detail id 从数据取  period master id
 * @param int $detailid
 * @param array $rows
 * @return int|string
 */
/*
function getPeriodMasterId($detailid,$rows)
{
    foreach ($rows as $v){
        if ($v['DETAIL_ID'] == $detailid){
            return $v['MASTER_ID'];
        }
    }
    return '';
}
*/
$g_parser->ParseTable('year_ins_list',$year_insure_list);
$g_parser->ParseTable('pyear_ins_list',$pyear_insure_list);

/*
$period_master_id = isset($_GET['master_id']) ? decrypt($_GET['master_id'],$encrypt_key) : 
                    decrypt(getPeriodMasterId(encrypt($m,$encrypt_key), $year_insure),$encrypt_key);
*/
$period_detail_id = isset($_GET['detail_id']) ? decrypt($_GET['detail_id'],$encrypt_key) : $m;

$g_tpl->assign('year',$y);
$g_tpl->assign('detail_id',encrypt($period_detail_id,$encrypt_key));
$g_parser->ParseTable ('salary_ss_list',$Salary->GetInsureSalaryList($period_detail_id));
