<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     薪资条查询,所有资料在一个页面上显示
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/salary_slip_DB.php $
 *  $Id: salary_slip_new_DB.php 3414 2012-12-06 05:33:07Z dennis $
 *  $Rev: 3414 $ 
 *  $Date: 2012-12-06 13:33:07 +0800 (Thu, 06 Dec 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-12-06 13:33:07 +0800 (Thu, 06 Dec 2012) $
 *********************************************************/
$no_list_where = TRUE; // add by Dennis 2014/08/14
include_once 'salary_query.php';

$year_salary = $Salary->getLeastTwoYearSalaryList($y);
// for security reason, encypt the salary query parameters
$encrypt_key = md5($_SESSION['user']['emp_seq_no'].session_id());
$cnt = count($year_salary);

for($i=0; $i<$cnt; $i++)
{
    $year_salary[$i]['SAL_RESULT_ID'] = encrypt($year_salary[$i]['SAL_RESULT_ID'],$encrypt_key);
    $year_salary[$i]['MASTER_ID'] = encrypt($year_salary[$i]['MASTER_ID'],$encrypt_key);
    $year_salary[$i]['DETAIL_ID'] = encrypt($year_salary[$i]['DETAIL_ID'],$encrypt_key);
}

function getYearSalList($year,$data){
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
$year_salary_list = getYearSalList($y, $year_salary);
//pr($year_salary_list);
$pyear_salary_list = getYearSalList((int)$y-1, $year_salary);

//pr($pyear_salary_list);

$g_parser->ParseTable('year_sal_list',$year_salary_list);
$g_parser->ParseTable('pyear_sal_list',$pyear_salary_list);

/**
 * 根据 period detail id 从数据取  period master id
 * @param int $detailid
 * @param array $rows
 * @return int|string
 */
function getPeriodMasterId($detailid,$rows)
{
    foreach ($rows as $v){
        if ($v['DETAIL_ID'] == $detailid){
            return $v['MASTER_ID'];
        }
    }
    return '';
}
/**
 * 金额栏位小计
 * @param array $rows
 * @param int $idx_id
 * @return number
 */
function getSubtotal($rows,$idx_id)
{
    $r = 0;
    if (!is_array($rows) || count($rows)==0) return $r;
    foreach ($rows as $v){
        $r += $v[$idx_id];
    }
    return $r;
}

function tmpSum($arr){
    $result['tmp_tax_subtotal'] = 0;
    $result['tmp_notax_subtotal'] = 0;
    foreach ($arr as $v){
        if($v['IS_TAX_ITEM'] == 'Y'){
            $result['tmp_tax_subtotal'] += $v['AMOUNT'];
        }else{
            $result['tmp_notax_subtotal'] += $v['AMOUNT'];
        }
    }
    return $result;
}

$period_master_id = isset($_GET['master_id']) ? decrypt($_GET['master_id'],$encrypt_key) : decrypt(getPeriodMasterId(encrypt($m,$encrypt_key), $year_salary),$encrypt_key);
$period_detail_id = isset($_GET['detail_id']) ? decrypt($_GET['detail_id'],$encrypt_key) : $m;

$g_tpl->assign('year',$y);
$g_tpl->assign('detail_id',encrypt($period_detail_id,$encrypt_key));

// 薪资概况
$mon_sal_summary = $Salary->getMonSalSummary($period_master_id, $period_detail_id);
$g_parser->ParseOneRow($mon_sal_summary);

// 固定薪资项目
$fixed_salary_list = $Salary->getFixSalaryListNew($period_master_id, $period_detail_id);
$g_parser->ParseTable('fix_sal_list',$fixed_salary_list);
$fix_subtotal = getSubtotal($fixed_salary_list, 'AMOUNT');
$g_tpl->assign('fix_subtotal',$fix_subtotal);

// 临时薪资项目
$tmp_salary_list = $Salary->getTmpSalaryListNew($period_master_id, $period_detail_id);
$g_parser->ParseTable('tmp_sal_list',$tmp_salary_list);
$tmp_tax_subtotal = 0;
if(is_array($tmp_salary_list) && count($tmp_salary_list)>0){
    $tmp_subtotal = tmpSum($tmp_salary_list);
    $tmp_tax_subtotal = $tmp_subtotal['tmp_tax_subtotal'];
    $g_tpl->assign('tmp_tax_subtotal',$tmp_tax_subtotal);
    $g_tpl->assign('tmp_notax_subtotal',$tmp_subtotal['tmp_notax_subtotal']);
}
// 加班费
$ot_salary_list = $Salary->getOvertimeSalaryListNew($period_master_id, $period_detail_id);
$g_parser->ParseTable('ot_sal_list',$ot_salary_list);
$ot_subtotal = getSubtotal($ot_salary_list, 'AMOUNT');
$g_tpl->assign('ot_subtotal',$ot_subtotal);
// 请假扣款
$abs_salary_list = $Salary->getAbsenceSalaryListNew($period_master_id, $period_detail_id);
$g_parser->ParseTable('abs_sal_list',$abs_salary_list);
$abs_subtotal = getSubtotal($abs_salary_list, 'AMOUNT');
$g_tpl->assign('abs_subtotal',$abs_subtotal);

// 奖金
$bonus_salary_list = $Salary->getBonusSalaryListNew($period_master_id, $period_detail_id);
$g_parser->ParseTable('bonus_sal_list',$bonus_salary_list);
$bonus_subtotal = getSubtotal($bonus_salary_list, 'AMOUNT');
$g_tpl->assign('bonus_subtotal',$bonus_subtotal);

// 社保
$ss_sal_list = $Salary->getInsureSalaryListNew($period_master_id, $period_detail_id);
$g_parser->ParseTable('ss_sal_list',$ss_sal_list);
$g_tpl->assign('psnpay_subtotal',getSubtotal($ss_sal_list, 'EMP_PAY')+getSubtotal($bonus_salary_list,'EMP_BONUS_INSURE_AMOUNT'));
// remark by dennis for chipmore 要求合在一起
//$g_tpl->assign('bonus_insure_subtotal',getSubtotal($bonus_salary_list,'EMP_BONUS_INSURE_AMOUNT'));


$yingfa_amount = $fix_subtotal+$tmp_tax_subtotal+$ot_subtotal+$abs_subtotal+$bonus_subtotal;
$g_tpl->assign('yingfa_amount',$yingfa_amount);
// 税后减项
/* 跟临时薪资中的减项重复
$dec_salary_list = $Salary->getDeductAfterTaxList($period_master_id, $period_detail_id);
$g_parser->ParseTable('dec_sal_list',$dec_salary_list);
$g_tpl->assign('after_dec_subtotal',getSubtotal($dec_salary_list, 'AMOUNT'));
*/

