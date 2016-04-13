<?php
/**
 *  我的成长首页
 *  Create By Dennis 2009-01-04
 *  $Id: my_career_DB.php 3363 2012-10-16 06:53:10Z dennis $
 *  $LastChangedDate: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3363 $  
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if
// 本月开课清单
$sql = <<<eof
	select a.subject_class_id,
		   a.subject_class_no,
	       a.subject_class_name,
	       a.subject_hour,
	       b.class_date,
	       to_char(b.hour_begin,'hh24:mi') as hour_begin,
	       to_char(b.hour_end,'hh24:mi') as hour_end
	  from hr_subject_class a, hr_class_date b
	 where a.seg_segment_no = b.seg_segment_no
	   and a.subject_class_id = b.subject_class_id
	   and pk_education.f_class_status(a.seg_segment_no, a.subject_class_id) < '04'
	   and to_char(sysdate, 'yyyymm') = to_char(b.class_date, 'yyyymm')
	   and a.seg_segment_no = :company_id
eof;
//$g_db_sql->debug=true;
$g_tpl->assign('course_list',$g_db_sql->GetArray($sql,
												 array('company_id'=>$_SESSION['user']['company_id'])));
												 
// 必修未上课清单
$sql1 = <<<eof
	select subject_id, subject_no, subject_name
	  from hr_subject_group_v
	 where is_learn != 2
	   and id = :emp_seqno
	   and seg_segment_no = :company_id
eof;
//$g_db_sql->debug=true;
$g_tpl->assign('un_learned_list',$g_db_sql->GetArray($sql1,
										 			 array('company_id'=>$_SESSION['user']['company_id'],
										 				   'emp_seqno'=>$_SESSION['user']['emp_seq_no'])));

?>