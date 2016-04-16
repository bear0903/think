<?php
/*  
 *  计算加班时数
 *  Create by Boll Yuan 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/ajax_overtime_DB.php $
 *  $Id: ajax_overtime_DB.php 3028 2010-12-06 11:01:35Z dennis $
 *  $Rev: 3028 $ 
 *  $Date: 2010-12-06 19:01:35 +0800 (周一, 06 十二月 2010) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2010-12-06 19:01:35 +0800 (周一, 06 十二月 2010) $
 *********************************************************/
class ajaxOverTime extends AresAction {
	public function actionGetOverTime() {
		$r = array();
		$end_time=gf_getEndTime($_POST['overtime_date'],$_POST['begin_time'],$_POST['end_time']);
		$arr=array( 'p_seg_segment_no'  => $_SESSION['user']['company_id'],
					'p_psn_id'  => $_SESSION['user']['emp_seq_no'],
					'p_begintemp'  => $_POST['overtime_date'],
					'p_begintime'  => $_POST['overtime_date'].' '.$_POST['begin_time'],
					'p_endtime'  => $end_time,
					'p_hours'  =>'',
					'p_errmsg'  => ''
					 ) ;
	
		$stmt1 ="begin begin pk_erp.p_set_segment_no('".$_SESSION['user']['company_id']."'); end; begin pk_overtime.p_overtime_hours(p_seg_segment_no => :p_seg_segment_no,p_psn_id => :p_psn_id,p_begintemp => to_date(:p_begintemp,'YYYY-MM-DD'),p_begintime => to_date(:p_begintime,'YYYY-MM-DD HH24:MI:SS'),p_endtime => to_date(:p_endtime,'YYYY-MM-DD HH24:MI:SS'),p_hours => :p_hours,p_errmsg => :p_errmsg); end ;end;";
			
		//$this->db->debug = true;
		$stmt = $this->db->PrepareSP ( $stmt1 );		
		$this->db->InParameter ( $stmt, $arr['p_seg_segment_no'], 'p_seg_segment_no', 32 );
		$this->db->InParameter ( $stmt, $arr['p_psn_id'], 'p_psn_id', 32 );
		$this->db->InParameter ( $stmt, $arr['p_begintemp'], 'p_begintemp', 32 );
		$this->db->InParameter ( $stmt, $arr['p_begintime'], 'p_begintime', 32 );
		$this->db->InParameter ( $stmt, $arr['p_endtime'], 'p_endtime', 32 );
		
		$this->db->OutParameter ( $stmt, $arr['p_hours'], 'p_hours', 32 );
		$this->db->OutParameter ( $stmt, $arr['p_errmsg'], 'p_errmsg', 2000 );
		
		$this->db->StartTrans (); // begin transaction
		$this->db->Execute ( $stmt );
		$this->db->CompleteTrans (); // end transaction
		$r['hours'] = $arr['p_hours'];
		/* add by dennis 2010-12-06 for auto get holidy type*/
		$sql = <<<eof
			select holiday
			  from hr_carding
			 where psn_seg_segment_no = :company_id
			   and psn_id             = :emp_seqno
			   and cday               = to_date(:the_date,'yyyy-mm-dd')
eof;
		$r['day_type'] = $this->db->GetOne($sql,array('company_id'=>$arr['p_seg_segment_no'],
									 'emp_seqno'=>$arr['p_psn_id'],
									 'the_date'=>$_POST['overtime_date']));
		$r['day_type'] = is_null($r['day_type']) ? 'N' : $r['day_type'];
		exit(json_encode($r));
	}
	public function actionCheckLeaveApplyTimeArea(){
		if(empty($_POST['begin_time']) || empty($_POST['end_time'])) exit('no parameter');
		//pr($_SESSION);
		$sql="
			 SELECT COUNT(*) CNT
		       FROM HR_CARDING
		      WHERE PSN_ID = '".$_SESSION['user']['emp_seq_no']."'
		        AND PSN_SEG_SEGMENT_NO = '".$_SESSION['user']['company_id']."'
		        AND (   (  BREAKBEGIN IS NULL
				             AND to_date('".$_POST['begin_time']."','YYYY-MM-DD HH24:MI') BETWEEN INTIME AND OUTTIME )
				      OR (  BREAKBEGIN IS NOT NULL
				             AND (   to_date('".$_POST['begin_time']."','YYYY-MM-DD HH24:MI') BETWEEN DECODE(FREETYPE,'N',INTIME,intime-NVL(FREETIME/60/24,0)) AND BREAKBEGIN
				                  OR to_date('".$_POST['begin_time']."','YYYY-MM-DD HH24:MI') BETWEEN BREAKEND AND DECODE(FREETYPE,'N',OUTTIME,outtime+nvl(freetime2/60/24,0)) )
				         )
	             )
			";
		//echo $sql;
		$rs=$this->db->GetOne($sql);
		if($rs==0) exit('1');
		$sql="
			 SELECT COUNT(*) CNT
		       FROM HR_CARDING
		      WHERE PSN_ID = '".$_SESSION['user']['emp_seq_no']."'
		        AND PSN_SEG_SEGMENT_NO =  '".$_SESSION['user']['company_id']."'
		        AND (   (  BREAKBEGIN IS NULL
				             AND to_date('".$_POST['end_time']."','YYYY-MM-DD HH24:MI') BETWEEN INTIME AND OUTTIME )
				      OR (  BREAKBEGIN IS NOT NULL
				             AND (   to_date('".$_POST['end_time']."','YYYY-MM-DD HH24:MI') BETWEEN INTIME AND BREAKBEGIN
				                  OR to_date('".$_POST['end_time']."','YYYY-MM-DD HH24:MI') BETWEEN BREAKEND AND OUTTIME )
				         )
	             )
			";
		//echo $sql;
		$rs=$this->db->GetOne($sql);
		if($rs==0) exit('2');
		
		exit('ok');
	}
}

if(empty($_GET['do']))  $_GET['do']='GetOverTime';
$ajax = new ajaxOverTime();
$ajax->run();
?>