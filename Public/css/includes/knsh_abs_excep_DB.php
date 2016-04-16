<?php
	/**
	 * for KNSH
	 * Get 近两个月的出勤异常记录
	 * 
	 */
/**
 * Get 60 days attendance exception records
 * @param string $company_id
 * @param string $emp_seqno
 * @author Dennis 2013-04-26
 */
function getAbsencExceptList($company_id,$emp_seqno)
{
	global $g_db_sql;
	$sql = <<<eof
		select cday as att_day,
		       decode(holiday, 'S', '例假日', 'H', '國定假日', holiday) as holiday,
		       absence_name as abs_name,
		       to_char(begindate, 'yyyy/mm/dd hh24:mi:ss') as abs_begin_time,
		       to_char(enddate, 'yyyy/mm/dd hh24:mi:ss') as abs_end_time,
		       to_char(intime, 'yyyy/mm/dd hh24:mi:ss') as shift_begin_time,
		       to_char(outtime, 'yyyy/mm/dd hh24:mi:ss') 　as shift_end_time,
		       to_char(inactual, 'yyyy/mm/dd hh24:mi:ss') as act_intime,
		       to_char(outactual, 'yyyy/mm/dd hh24:mi:ss') as act_outtime,
		       pk_ehr_util.f_get_emp_absence_perday('ZHT',
		                                            psn_seg_segment_no,
		                                            psn_id,
		                                            cday) as abs_recs
		  from pri_attend_issues_new
		 where psn_id = :emp_seqno
		   and psn_seg_segment_no = :company_id
		   and cday >= trunc(sysdate - 60)
		   and cday <= trunc(sysdate)
		 order by cday desc
eof;
	//$g_db_sql->debug = 1;
	return $g_db_sql->CacheGetArray(3600,$sql,array('emp_seqno'=>$emp_seqno,
			'company_id'=>$company_id));
}
$rs = getAbsencExceptList($_SESSION['user']['company_id'],$_SESSION['user']['emp_seq_no']);
$g_parser->ParseTable('abs_list',$rs);	