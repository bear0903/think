<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C)2008 ARES CHINA INC.
 *
 *	Description:
 *     员工请假加班 Workflow 相关程式
 *
 *	last update: 2008-08-07 by dennis
 *
 *	!!! notice get_query_where() function reference to "functions.php"
 *
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAttend.class.php $
 *  $Id: AresAttend.class.php 3858 2014-11-06 01:56:06Z dennis $
 *  $Rev: 3858 $
 *  $Date: 2014-11-06 09:56:06 +0800 (周四, 06 十一月 2014) $
 *  $LastChangedDate: 2014-11-06 09:56:06 +0800 (周四, 06 十一月 2014) $
 \****************************************************************************/
class AresAttend {
    protected $companyID;
    protected $empSeqNo;
    protected $DBConn;

    const MYSELF    = 'myself';
    const ASSISTANT = 'assistant';
    const ADMIN     = 'admin';
    
    const DATA_CACHE_SECONDS = 3600; // data cache seconds (1H)
    /**
     *   Counstructor of class AresAttend
     *   init property companyid and emplyee seq no (psn_id)
     *   @param $companyid string, the employee's company id
     *   @param $emp_seqno string, the employee's sequence no(psn_id) in employee
     */
    function __construct($companyid, $emp_seqno) {
        global $g_db_sql;
        $this->companyID = $companyid;
        $this->empSeqNo  = $emp_seqno;
        $this->DBConn    = &$g_db_sql;
    } // end class constructor


    /**
     * Get 员工已经请的假别清单
     *
     * @param number, $query_all 0_仅查询某一个员工的, 1_查询全部
     * @return 2-d Array
     * @author Dennis 2008-08-07
     *
     */
    function GetLeaveName($query_all = 0) {
        $_wherecond = $query_all == 0 ? ' and emp_seq_no = :emp_seqno ' : '';
        $sql = <<<eof
                select distinct absence_seq_no,
                       absence_id||'-'|| absence_name as absence_name
                  from ehr_absence_v
                 where company_id = :company_id
                   $_wherecond
eof;
        //print $sql;
        return $this->DBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql, 
                array('emp_seqno' => $this->empSeqNo, 'company_id' => $this->companyID));
    } // end function GetLeaveName()


    /**
     * Get 权限内部门清单
     * @param string $user_seqno
     * @return 2-d Array
     * @author Dennis
     */
    function GetDeptName($user_seqno) {
        // register current user
        $stmt = 'begin pk_erp.p_set_segment_no(:company_id); pk_erp.p_set_username(:user_seqno); end;';
        $this->DBConn->Execute($stmt, array ('company_id' => $this->companyID, 'user_seqno' => $user_seqno));
        $sql = <<<eof
            select segment_no, segment_no_sz || ' ' || segment_name
              from gl_segment
             where seg_segment_no = :company_id
               and sysdate between begindate and enddate
               and pk_user_priv.f_dept_priv(segment_no) = 'Y'
             order by segment_no_sz
eof;
        //print $sql;
        return $this->DBConn->GetArray ($sql, array ('company_id' => $this->companyID));
    } // end GetDeptName()

    private function _getYearMendID()
    {
        $sql = <<<eof
            select year_leave,mend_leave
              from hr_attendset
             where seg_segment_no = :company_id
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->DBConn->GetRow($sql,array('company_id'=>$this->companyID));
    }

    /**
     *  取得当前使用者可以请的假别清单
     *  @param $sex string , sex, M_男 F_女 upper case
     *  @return  array
     *	@author : dennis 2006-05-11 14:14:14
     *	@last update: 2008-08-07  by dennis
     */
    function GetLeaveNameList($sex = null,$except_id = null) {
        $where = is_null($sex) ? '' : " and (sex_absence = 'A' or sex_absence ='" . $sex . "')";
        if (!is_null($except_id))
        {
            $r = $this->_getYearMendID();
            if (is_array($r))
            {
                $where .= !empty($r['YEAR_LEAVE']) ? ' and absence_type_id != \''.$r['YEAR_LEAVE'].'\'' : '';
                $where .= !empty($r['MEND_LEAVE']) ? ' and absence_type_id != \''.$r['MEND_LEAVE'].'\'' : '';
            }
        }
        $sql = <<<eof
                select absence_seq_no,
                       absence_id ||' - '||absence_name
                  from ehr_absence_type_v
                 where company_id = :company_id
                   and is_active = 'Y'
                  $where
                  order by absence_id
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->SetFetchMode(ADODB_FETCH_NUM);
        return $this->DBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
                                         array ('company_id' => $this->companyID));
    } // end GetLeaveNameList()
    
    /**
     * 取得可休假别清单，除去可休设定为 0 的假别清单
     * @param string $sex
     * @param string $except_id
     * @author Dennis 2014/04/15 add
     */
    function GetVacationList($sex = null,$except_id = null) {
        $where = is_null($sex) ? '' : " and (reverse1 = 'A' or reverse1 ='" . $sex . "')";
        if (!is_null($except_id))
        {
            $r = $this->_getYearMendID();
            if (is_array($r))
            {
                $where .= !empty($r['YEAR_LEAVE']) ? ' and absence_type_id != \''.$r['YEAR_LEAVE'].'\'' : '';
                $where .= !empty($r['MEND_LEAVE']) ? ' and absence_type_id != \''.$r['MEND_LEAVE'].'\'' : '';
            }
        }
        $sql = <<<eof
            select absence_type_id, absence_code ||'-'|| absence_name
              from hr_absence_type
             where seg_segment_no = :company_id
               and is_active = 'Y'
               $where
               and nvl(pk_attend_status_sz.f_get_psn_day(:company_id1,
    		                                             :psn_id,
    		                                             sysdate,
    		                                             absence_type_id),0) >0
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->SetFetchMode(ADODB_FETCH_NUM);
        return $this->DBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
                array ('company_id' => $this->companyID,
                       'company_id1' => $this->companyID,
                       'psn_id'=>$this->empSeqNo));
    } // end GetLeaveNameList()

    /**
     * 取得上传的目录
     * @param no
     * @return void
     */
    function getUploadDir() {
        require_once 'AresEmployee.class.php';
        return AresEmployee::GetEmpPhotoDir ();
    } // end getUploadDir()

    /**
	 * 取得亲属清单
	 *
	 * @return array
	 * add already_days=0 by dennis 2012-10-26 for filter expired data
	 * last update by dennis  2012-11-06 
	 * Fixed bug: 特殊假起讫时间内，且未休完的假都要列出来
	 * Fixed Bug: 亲属类型未按假别来取  by Dennis 2014/11/06 
	 */
	function GetFamilyType($abs_type_id) {
		$sql = <<<eof
               select funeral_id, family_type || '-'|| family_type_name as family_type
                 from ehr_funeral_v
                where company_id = :company_id
                  and emp_seq_no = :emp_seqno
		          and absence_type_id = :abs_type_id
eof;
		//$this->DBConn->debug = true;
		$this->DBConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->DBConn->GetArray($sql, array ('emp_seqno' => $this->empSeqNo, 
		        'company_id' => $this->companyID,
		        'abs_type_id'=> $abs_type_id
		));
	} // end GetFamilyType();


    /**
     * procedure p_save_absence_apply
     *****************************************************************************
     * 传入公司ID，员工ID，请假类别ID，请假时间起讫，pi_submit : Y/N 保存时是否提交申请
     * 返回天数/时数,请假申请单ID和错误信息，如果返回的po_success为'N' ，则保存不成功
     * 即使 po_success为'Y' ，这时错误信息可能也有值，那可能是一些提示信息的返回
     * 保存至HR_ABSENCE_FLOW_SZ表中
     *****************************************************************************
     *	  (pi_seg_segment_no     varchar2, -- 公司ID
     *	   pi_psn_id             varchar2, -- 员工ID
     *	   pi_absence_type_id    number, --请假类别ID
     *	   pi_date_begin         date, -- 请假开始时间
     *	   pi_date_end           date, -- 请假结束时间
     *	   pi_funeral_id         varchar2,--丧假建档ID
     *	   pi_remark             varchar2, -- 备注
     *	   po_days               out number, -- 返回天数
     *	   po_hours              out number, -- 返回小时数
     *	   po_errmsg             out varchar2, -- 返回错误信息
     *	   po_absence_flow_sz_id in out number, -- 返回请假申请单ID
     *	   po_success            out varchar2, -- 操作是否成功 Y/N
     *	   pi_submit             varchar2 default 'N', -- 是否立即提交申请
     *	   pi___only_submit      varchar2 default 'N' -- 私有参数,仅供单独提交申请程式传入Y值使用)
     * 暂存或提交请假申请
     *
     * @param string, $user_seqno  请假人的使用者流水号(app_users_base.user_id)
     * @param string, $absence_id  假别代码
     * @param string, $begin_time  请假开始时间
     * @param string, $end_time    请假结束时间
     * @param string, $leave_reason请假原因
     * @param string, $submit_type 提交类型,save_只是暂存请假单 submit_提交到流程中签核
     * @param string, $funeral_id  特殊假别代码,default null
     * @param number, $emp_seqno   提交人的员工代码流水号 (hr_personnel_base.id)
     * @return array, procedure 传出参数组成的 Array
     * @author Dennis 2008-09-12 last update
     */
    function SaveLeaveForm($user_seqno,
                           $absence_id,
                           $begin_time,
                           $end_time,
                           $leave_reason,
                           $submit_type,
                           $funeral_id = null,
                           $emp_seqno = null) {
        // add by dennis 2006-04-02 18:43:45  for support batch apply
        //$this->DBConn->debug = 1;
        $emp_seqno = is_null($emp_seqno) ? $this->empSeqNo : $emp_seqno;
        // 保存或提交返回的结果
        $_save_result = array ('days' => '',
                               'hours' => '',
                               'msg' => '',
                               'flow_seqno' => '',
                               'is_success' => '');

        $stmt1 = "begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_save_absence_apply(pi_seg_segment_no=>:in_companyid,pi_psn_id=>:in_empseqno,pi_absence_type_id=>:in_leavetype_seqno,pi_date_begin=>to_date(:in_begintime,'YYYY-MM-DD HH24:MI'),pi_date_end=>to_date(:in_endtime,'YYYY-MM-DD HH24:MI'),pi_funeral_id=>:in_funeral_id,pi_remark=>:in_reason,pi_submit=>:in_submit,po_days=>:out_days,po_hours=>:out_hours,po_errmsg=>:out_msg,po_absence_flow_sz_id=>:out_leave_flowseqno,po_success=>:out_issuccess,pi_assis_username=>:in_assis_username); end;end;";
        $stmt = $this->DBConn->PrepareSP($stmt1);
        $this->DBConn->InParameter($stmt, $this->companyID, 'in_company_id1', 30);
        $this->DBConn->InParameter($stmt, $this->companyID, 'in_companyid', 30);
        $this->DBConn->InParameter($stmt, $user_seqno, 'in_user_seqno', 30);
        $this->DBConn->InParameter($stmt, $emp_seqno, 'in_empseqno', 30);
        $this->DBConn->InParameter($stmt, $absence_id, 'in_leavetype_seqno', 30);
        $this->DBConn->InParameter($stmt, $begin_time, 'in_begintime', 30);
        $this->DBConn->InParameter($stmt, $end_time, 'in_endtime', 30);
        $this->DBConn->InParameter($stmt, $funeral_id, 'in_funeral_id', 30);
        $this->DBConn->InParameter($stmt, $leave_reason, 'in_reason', 4000);
        $this->DBConn->InParameter($stmt, $submit_type, 'in_submit', 2);
        $this->DBConn->InParameter($stmt, $user_seqno, 'in_assis_username', 20);
        $this->DBConn->OutParameter($stmt, $_save_result ['days'], 'out_days', 10);
        $this->DBConn->OutParameter($stmt, $_save_result ['hours'], 'out_hours', 10);
        $this->DBConn->OutParameter($stmt, $_save_result ['msg'], 'out_msg', 2000);
        $this->DBConn->OutParameter($stmt, $_save_result ['flow_seqno'], 'out_leave_flowseqno', 9);
        $this->DBConn->OutParameter($stmt, $_save_result ['is_success'], 'out_issuccess', 2);
        //$this->DBConn->debug = true;
        $this->DBConn->StartTrans (); // begin transaction
        $this->DBConn->Execute($stmt);
        $this->DBConn->CompleteTrans (); // end transaction
        return $_save_result;
    } // end function SaveLeaveForm();

    /**
     *
     * Check Begin/End time is in schedule
     *
     * @param string $emp_seq_no
     * @param sring $company_id
     * @param string $begin_time
     * @param string $end_time
     */
    public function checkLeaveApplyTimeArea($emp_seq_no,$company_id,$begin_time,$end_time)
    {
        if(empty($begin_time) || empty($end_time)) return 'no parameter';
        //$this->DBConn->debug = 1;
        $sql= <<<eof
            select count(*) cnt
              from hr_carding
             where psn_id = :emp_seqno
               and psn_seg_segment_no = :company_id
               and ((breakbegin is null and
                   to_date(:begin_time, 'yyyy-mm-dd hh24:mi') between intime and outtime) or
                   (breakbegin is not null and
                   (to_date(:begin_time1, 'yyyy-mm-dd hh24:mi') between intime and
                   breakbegin or to_date(:begin_time2, 'yyyy-mm-dd hh24:mi') between
                   breakend and outtime)))
eof;
        $rs = $this->DBConn->GetOne($sql,array('emp_seqno'=>$emp_seq_no,
                                               'company_id'=>$company_id,
                                               'begin_time'=>$begin_time,
                                               'begin_time1'=>$begin_time,
                                               'begin_time2'=>$begin_time));
        if($rs==0) return '1';
        $sql = <<<eof
            select count(*) cnt
              from hr_carding
             where psn_id = :emp_seqno
               and psn_seg_segment_no = :company_id
               and ((breakbegin is null and
                   to_date(:end_time, 'yyyy-mm-dd hh24:mi') between intime and
                   outtime) or
                   (breakbegin is not null and
                   (to_date(:end_time1, 'yyyy-mm-dd hh24:mi') between intime and
                   breakbegin or to_date(:end_time2, 'yyyy-mm-dd hh24:mi') between
                   breakend and outtime)))
eof;
        $rs = $this->DBConn->GetOne($sql,array('emp_seqno'=>$emp_seq_no,
                                               'company_id'=>$company_id,
                                               'end_time'=>$end_time,
                                               'end_time2'=>$end_time,
                                               'end_time1'=>$end_time));
        if($rs==0) return '2';
        return 'ok';
    }

    /**
     * 助理批量提交请假单
     *
     * @param string $user_seqno
     * @param string $absence_id
     * @param string $begin_time
     * @param string $end_time
     * @param string $leave_reason
     * @param string $submit_type
     * @param array $emplist
     * @param string $funeral_id default null
     * @return array
     * @author Dennis 2008-10-18
     * @access public
     *
     */
    function batchLeaveApply($user_seqno,
                             $absence_id,
                             $begin_time,
                             $end_time,
                             $leave_reason,
                             $submit_type,
                             array $emplist,
                             $funeral_id = null)
    {
        $result = null;
        for ($i=0; $i<count($emplist);$i++)
        {
            //begin 验证开始/结束时间是在排程时间内,成批申请时
            $check_time_msg = $this->checkLeaveApplyTimeArea($emplist[$i],$this->companyID,$begin_time,$end_time);
            if($check_time_msg =='1'){
                $result[$i] = array('days' => '0',
                                    'hours' => '0',
                                    'msg' => '開始時間未在排程時間內或此人此天未排班 ',
                                    'flow_seqno' => '',
                                    'is_success' => 'N');
                continue;
            }elseif($check_time_msg == '2'){
                $result[$i] = array('days' => '0',
                                    'hours' => '0',
                                    'msg' => '結束時間未在排程時間內或此人此天未排班',
                                    'flow_seqno' => '',
                                    'is_success' => 'N');
                continue;
            }

            $result[$i] = $this->SaveLeaveForm($user_seqno,
                                                $absence_id,
                                                $begin_time,
                                                $end_time,
                                                $leave_reason,
                                                $submit_type,
                                                $funeral_id,
                                                $emplist[$i]);
        }// end for loop;
        return $result;
    }// end batchLeaveApply()

    /**
     *   Batch save overtime form
     *   @param $user_seq_no number, login user seq no
     *   @param $post_array array, 包括批量的员工流水号,员工所在部门的流水号

     *   @return array, 批量输入的结果, 包括每一笔记录的结果
     *   @author: dennis 2006-04-02 10:41:21
     *   @last update: 2006-04-02 10:41:29  by dennis
     */
    function BatchSaveLeaveForm($user_seq_no, $post_array) {
        if (! is_array($post_array ["emplist"])) {
            trigger_error("Please select a employee.");
        }
        $_result = array ();
        for($i = 0; $i < count($post_array ["emplist"]); $i ++) {
            $post_array ["emp_seqno"] [$i] = substr($post_array ["emplist"] [$i], 0, strpos($post_array ["emplist"] [$i], '|'));
            //pr($post_array);exit;
            $_result [$i] = $this->SaveLeaveForm($user_seq_no, $post_array, $post_array ["emp_seqno"] [$i]);
            $_result [$i] = array_change_key_case(array_merge($_result [$i], $this->_getEmployeeInfo($post_array ["emp_seqno"] [$i])), CASE_LOWER);
        }
        return $_result;
    } // end funciton BatchSaveLeaveForm()


    /**
     *   Submit leave apply form to workflow
     *   @param $user_seqno number, the current login user seqno (in app_users)
     *   @param $workflow_seqno number, the leave form workflow seqno
     *   @return array, submit result
     *	@author: Dennis 2006-03-28 16:44:25
     *	@last update: 2006-03-28 15:47:06  by dennis
            procedure p_submit_absence_apply
     *****************************************************************************
            Createed by nyfor at 2005/07/01
               传入公司ID，申请单ID,返回天数/时数和错误信息，如果返回的po_success为'N' ，则申请不成功
               因为可能是申请保存过后影响天数和时数的参数有变化，所以需要重新计算一遍即使 po_success为'Y' ，
               这时错误信息可能也有值，那可能是一些提示信息的返回将请假申请插入到请假资料档中
     *****************************************************************************
            (pi_seg_segment_no     varchar2, --公司ID
            pi_absence_flow_sz_id varchar2, --请假申请单ID
            po_days               out number, --返回天数
            po_hours              out number, -- 返回小时数

            po_errmsg             out varchar2, -- 返回错误信息
            po_success            out varchar2 -- 操作是否成功 Y/N
           )
     */
    function SubmitLeaveForm($user_seqno, $workflow_seqno) {
        $companyid = $this->companyID;
        $_submit_result = array ("days" => "", "hours" => "", "msg" => "", "is_success" => "");
        $stmt1 = "begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_submit_absence_apply(pi_seg_segment_no=>:in_companyid,pi_absence_flow_sz_id=>:in_workflow_seqno,po_days=>:out_days,po_hours=>:out_hours,po_errmsg=>:out_msg,po_success=>:out_issuccess); end;end;";
        $stmt = $this->DBConn->PrepareSP($stmt1);
        $this->DBConn->InParameter($stmt, $companyid, "in_company_id1", 10);
        $this->DBConn->InParameter($stmt, $companyid, "in_companyid", 10);
        $this->DBConn->InParameter($stmt, $user_seqno, "in_user_seqno", 10);
        $this->DBConn->InParameter($stmt, $workflow_seqno, "in_workflow_seqno", 10);
        $this->DBConn->OutParameter($stmt, $_submit_result ["days"], "out_days", 5);
        $this->DBConn->OutParameter($stmt, $_submit_result ["hours"], "out_hours", 6);
        $this->DBConn->OutParameter($stmt, $_submit_result ["msg"], "out_msg", 2000);
        $this->DBConn->OutParameter($stmt, $_submit_result ["is_success"], "out_issuccess", 2);
        //$this->DBConn->debug = true;
        $this->DBConn->StartTrans (); // begin transaction
        $this->DBConn->Execute($stmt);
        $this->DBConn->CompleteTrans (); // end transaction
        return $_submit_result;
    } // end function SubmitLeaveForm();


    /**
     *  提交销假申请
     * procedure p_submit_absence_apply(
                pi_seg_segment_no       varchar2, --公司ID
                pi_c_absence_flow_sz_id varchar2, --请假申请单ID
                po_days                 out number, --返回天数
                po_hours                out number, -- 返回小时数
                po_errmsg               out varchar2, -- 返回错误信息
                po_success              out varchar2 -- 操作是否成功 Y/N
           );
     *	@param $user_seqno number, the current login user seqno (in app_users)
     *  @param $workflow_seqno number, the leave form workflow seqno
     *  @return array, submit result
     *	@author: Dennis 2006-05-09 20:39:48 last update: 2006-05-09 20:39:50 by dennis
     */
    function SubmitCancelLeaveForm($user_seqno, $workflow_seqno) {
        $companyid = $this->companyID;
        //print $workflow_seqno;
        $_submit_result = array ('days' => '', 'hours' => '', 'msg' => '', 'is_success' => '');
        $stmt1 = 'begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pk_cancel_absence_wf.p_submit_absence_apply(pi_seg_segment_no=>:in_companyid,pi_c_absence_flow_sz_id=>:in_workflow_seqno,po_days=>:out_days,po_hours=>:out_hours,po_errmsg=>:out_msg,po_success=>:out_issuccess); end;end;';
        $stmt = $this->DBConn->PrepareSP($stmt1);
        $this->DBConn->InParameter($stmt, $companyid, 'in_company_id1', 10);
        $this->DBConn->InParameter($stmt, $companyid, 'in_companyid', 10);
        $this->DBConn->InParameter($stmt, $user_seqno, 'in_user_seqno', 10);
        $this->DBConn->InParameter($stmt, $workflow_seqno, 'in_workflow_seqno', 10);
        $this->DBConn->OutParameter($stmt, $_submit_result ['days'], 'out_days', 5);
        $this->DBConn->OutParameter($stmt, $_submit_result ['hours'], 'out_hours', 6);
        $this->DBConn->OutParameter($stmt, $_submit_result ['msg'], 'out_msg', 2000);
        $this->DBConn->OutParameter($stmt, $_submit_result ['is_success'], 'out_issuccess', 2);
        //$this->DBConn->debug = true;
        $this->DBConn->StartTrans (); // begin transaction
        $this->DBConn->Execute($stmt);
        $this->DBConn->CompleteTrans (); // end transaction
        return $_submit_result;
    } // end function SubmitLeaveForm()


    /**
     *   Check parameter workflow type
     *   @param $flowtype string, must be "absence" / "overtime" / "cancel_absence"
     *   @return void, if not pass, raise trigger error.
     *   @author: dennis 2006-04-17 20:30:07  last update: 2006-04-17 20:30:13 by dennis
     */
    function CheckFlowType($flowtype) {
        $flow_type_array = array ("absence", "overtime", "cancel_absence");
        if (! in_array(strtolower($flowtype), $flow_type_array)) {
            trigger_error("<font color='red'>Programming Error: Unknow Workflow Type, Must be 'absence' or 'overtime'. Current Workflow Type is " . $flowtype . "</font>", E_USER_ERROR);
        }
    } // end function CheckFlowType();


    /**
     * 管理员作废申请(请假/加班/销假)
     *  procedure p_cancel_overtime_apply
       ( pi_seg_segment_no      varchar2, -- 公司ID
              pi_overtime_flow_sz_id number, -- 请假申请单ID
              pi_reject_reason       varchar2 default null, --作废原因
              po_errmsg              out varchar2, -- 返回错误信息
              po_success             out varchar2, -- 操作是否成功 Y/N
              pi_admin_id            in varchar2 default null -- 管理员ID
        )
     *   @param $workflow_seqno number, workflow sequence number
     *   @param $flowtype string, workflow type, must be "absence" or "overtime"
     *   @param $cancel_comment string, cancel reason
     *   @return array, contain the cancel result
     *   @author: dennis 2006-04-10 20:30:57 last update: 2006-05-11 16:20:14 by dennis
     */
    function CancelWorkflow($workflow_seqno,
                            $flowtype,
                            $user_seqno,
                            $cancel_comment = null) {
        $this->DBConn->debug = true;
		$this->CheckFlowType($flowtype);
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $_submit_result = array ('msg' => '', 'is_success' => '');
        $_procedure_name = array ('cancel_absence' => 'wf.pk_cancel_absence_wf.p_waste_c_absence_apply',
                                  'absence' => 'wf.pkg_work_flow.p_cancel_' . $flowtype . '_apply',
                                  'overtime' => 'wf.pkg_work_flow.p_cancel_' . $flowtype . '_apply');

        $stmt1 = 'begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin %s(pi_seg_segment_no=>:in_companyid,pi_%s_flow_sz_id=>:in_workflow_seqno,pi_reject_reason=>:in_cancel_comment,pi_admin_id=>:in_flowadmin_empseqno,po_errmsg=>:out_msg,po_success=>:out_issuccess); end;end;';
        $func_name = $_procedure_name[$flowtype];
        $stmt1 = sprintf($stmt1,$func_name,$flowtype);
        $stmt = $this->DBConn->PrepareSP($stmt1);
        $this->DBConn->InParameter($stmt, $companyid, 'in_company_id1', 10);
        $this->DBConn->InParameter($stmt, $user_seqno, 'in_user_seqno', 10);
        $this->DBConn->InParameter($stmt, $companyid, 'in_companyid', 10);
        $this->DBConn->InParameter($stmt, $workflow_seqno, 'in_workflow_seqno', 10);
        $this->DBConn->InParameter($stmt, $cancel_comment, 'in_cancel_comment', 2000);
        $this->DBConn->InParameter($stmt, $emp_seqno, 'in_flowadmin_empseqno', 10);
        $this->DBConn->OutParameter($stmt, $_submit_result ['msg'], 'out_msg', 2000);
        $this->DBConn->OutParameter($stmt, $_submit_result ['is_success'], 'out_issuccess', 2);

        $this->DBConn->debug = true;echo $stmt1;exit;
        $this->DBConn->StartTrans (); // begin transaction
        $this->DBConn->Execute($stmt);
        $this->DBConn->CompleteTrans (); // end transaction
        return $_submit_result;
    } // end function CancelWorkflow();
    
    private function _checkBeforeDelete($workflow_senqo,$flowtype)
    {
        $sql = <<<eof
            select count(*)
              from hr_{$flowtype}_flow_sz
             where {$flowtype}_flow_sz_id = $workflow_senqo
               and status < '02'
eof;
        return $this->DBConn->GetOne($sql);
    }

    /**
     *   Delete apply form in workflow
     *   @param $workflow_seqno number, workflow seq no
     *   @param $flowtype string, workflow type, must be "absence" or "overtime" or "cancel_absence"
     *   @return mixed, effected rows or error msg
     *   @author: Dennis 2006-03-31 16:02:46
     *   @last update: 2006-04-17 20:30:44  by dennis
     *   @log
     *       1. add new type "cancle_absence" by dennis 2006-05-09 18:26:32
     *       2. 添加删除之前的判断，是否有签核过 by Dennis 2013/10/28 
     *       3.批量删除的时候也同样调用单笔的删除，因为也需要检查是否有签核过 删除申请单时理应删除 hr_overtime_approve_sz 中的资料，暂时不删除也没有影响
     */
    function DeleteWorkflowApply($workflow_seqno, $flowtype) {
        //$this->DBConn->debug = 1;
        $this->CheckFlowType($flowtype);
        if ($this->_checkBeforeDelete($workflow_seqno, $flowtype) > 0){
            if ($flowtype == 'overtime'){
                // delete the batch import success data when delete the apply form from hr_xxxx_flow_sz table by dennis 2013/12/03
                $sql1 = <<<eof
                delete from ehr_concurrent_overtimeapply co
                where exists (select 1
                       from hr_overtime_flow_sz bb
                      where co.company_id = bb.seg_segment_no
                        and co.emp_seqno = bb.psn_id
                        and co.begin_time = bb.begintime
                        and co.end_time = bb.endtime
                        and bb.overtime_flow_sz_id = $workflow_seqno)
eof;
                $this->DBConn->Execute($sql1);
            }
            
            $sql = 'delete from hr_' . $flowtype . '_flow_sz where ' . $flowtype . "_flow_sz_id = '$workflow_seqno'";
            $this->DBConn->Execute($sql);
            $effectrows = $this->DBConn->Affected_Rows ();
            if ($effectrows > 0) {
                return $effectrows;
            }
            return $this->DBConn->ErrorMsg ();
        }else{
            return '申請單:'.$workflow_seqno.'已經被簽核過，不可刪除。';
        }
    } // end function DeleteWorkflowApply();

    /**
     * Batch Delete Workflow Apply
     * @param array $worklfow_seqnos
     * @param string $flow_type
     * @return number delete rows of apply
     * @author Dennis 2010-06-11
     * @change Log
     *   1. 改为调用单笔的逻辑，删除前也需要检查申请单是否已被签核过 by Dennis 2013/12/03
     *
     */
    function BatchDeleteWorkflowApply(array $worklfow_seqnos,$flow_type)
    {
        //$this->CheckFlowType($flow_type);
        $c = count($worklfow_seqnos);
        $succecc_cnt = 0;
        $errormsg = '';
        for($i=0; $i<$c; $i++)
        {
            $result = $this->DeleteWorkflowApply($worklfow_seqnos[$i],$flow_type);
            
            if ($result == 1){
                $succecc_cnt++;
            }else{
                $errormsg .= $result;
            }
        }
        if ($c == $succecc_cnt) return $c;
        return $errormsg;
        /* remark by dennis 2013/12/03
        $flowseqnos = '';
        for($i=0; $i<$c; $i++)
        {
            $flowseqnos .= $worklfow_seqnos[$i] .',';
        }
        $flowseqnos = substr($flowseqnos,0,-1);
        //$this->DBConn->debug = 1;
        if ($flow_type == 'overtime'){
            // delete the batch import success data when delete the apply form from hr_xxxx_flow_sz table by dennis 2013/12/03
            $sql1 = <<<eof
                delete from ehr_concurrent_overtimeapply co
                where exists (select 1
                    from hr_overtime_flow_sz bb
                    where co.company_id = bb.seg_segment_no
                    and co.emp_seqno = bb.psn_id
                    and co.begin_time = bb.begintime
                    and co.end_time = bb.endtime
                    and bb.overtime_flow_sz_id in ($flowseqnos))
eof;
            $this->DBConn->Execute($sql1);
        }
        $sql = 'delete from hr_' . $flow_type . '_flow_sz where ' . $flow_type . '_flow_sz_id in('.$flowseqnos.')';
        
        $this->DBConn->Execute ($sql);
        $effectrows = $this->DBConn->Affected_Rows();
        if ($effectrows > 0) {
            return $effectrows;
        }
        return $this->DBConn->ErrorMsg ();
        */
    }

    /**
     * 暂存或提交加班申请
     *procedure p_save_overtime_apply (
     *	pi_seg_segment_no      varchar2, -- 公司ID
     *	pi_psn_id              varchar2, -- 员工ID
     *	pi_cost_dept_id        varchar2, -- 成本部门
     *	pi_stype               varchar2, -- N/S/H
     *	pi_reason              varchar2, -- A:计费  B:补休
     *	pi_overtime_reason_id  number, --加班原因ID
     *	pi_date_begin          date, -- 请假开始时间
     *	pi_date_end            date, -- 请假结束时间
     *	pi_remark              varchar2, -- 备注
     *	po_hours               in out number, -- 返回加班小时数
     *	po_errmsg              out varchar2, -- 返回错误信息
     *	po_overtime_flow_sz_id in out number, -- 返回加班申请单ID
     *	po_success             out varchar2, -- 操作是否成功 Y/N
     *	pi_submit              varchar2 default 'N', -- 是否立即提交申请
     *	pi___only_submit       varchar2 default 'N' -- 私有参数,仅供单独提交申请程式传入Y值使用
     *)
     * @param string $userseqno     Login User Seq. No.
     * @param string $deptseqno		申请人的部门代码流水号
     * @param string $ot_begin_time 加班开始时间
     * @param string $ot_end_time   加班结束时间
     * @param number $ot_hours      加班时数
     * @param string $ot_reason     加班原因
     * @param string $ot_fee_type   补偿方式
     * @param string $ot_type       加班类型（平常 /假日/国假）
     * @param string $remark		备注
     * @param string $tmp_save		只是暂存，不提交
     * @param string $empseqno		员工代码流水号(psn_id),如果为空表示是当前 login user
     * @return array, 加班申请单提交结果的相关信息
     * @author Dennis 2008-09-19 rewrite
     */
    function SaveOvertimeApply($userseqno,
                               $cost_deptid,
                               $ot_begin_time,
                               $ot_end_time,
                               $ot_hours,
                               $ot_reason,
                               $ot_fee_type,
                               $ot_type,
                               $remark,
                               $tmp_save,
                               $emp_seqno = null) {
        $emp_seqno = is_null($emp_seqno) ? $this->empSeqNo : $emp_seqno;
        $result = array ('hours' => '',
                         'msg' => '',
                         'flow_seqno' => '',
                         'is_success' => '');
        $stmt1 = 'begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_save_overtime_apply(pi_seg_segment_no => :in_companyid,pi_psn_id => :in_empseqno,pi_cost_dept_id => :in_cost_dept_id,pi_stype => :in_overtime_type,pi_reason => :in_overtime_fee,pi_overtime_reason_id=> :in_overtime_reason,pi_date_begin => to_date(:in_begin_date,\'YYYY-MM-DD HH24:MI\'),pi_date_end => to_date(:in_end_date,\'YYYY-MM-DD HH24:MI\'),pi_remark => :in_remark,pi_submit=>:in_submit,po_hours=>:out_hours,po_errmsg=>:out_msg,po_overtime_flow_sz_id=>:out_overtime_flowseqno,po_success=>:out_issuccess,pi_assis_username=>:in_assis_username);end;end;';
        //$this->DBConn->debug = true;
        $stmt = $this->DBConn->PrepareSP($stmt1);
        $this->DBConn->InParameter($stmt, $this->companyID, 'in_company_id1', 10);
        $this->DBConn->InParameter($stmt, $userseqno, 'in_user_seqno', 10);
        $this->DBConn->InParameter($stmt, $this->companyID, 'in_companyid', 10);
        $this->DBConn->InParameter($stmt, $emp_seqno, 'in_empseqno', 10);
        $this->DBConn->InParameter($stmt, $cost_deptid, 'in_cost_dept_id', 10);
        $this->DBConn->InParameter($stmt, $ot_type, 'in_overtime_type', 10);
        $this->DBConn->InParameter($stmt, $ot_fee_type, 'in_overtime_fee', 10);
        $this->DBConn->InParameter($stmt, $ot_reason, 'in_overtime_reason', 10);
        $this->DBConn->InParameter($stmt, $ot_begin_time, 'in_begin_date', 20);
        $this->DBConn->InParameter($stmt, $ot_end_time, 'in_end_date', 20);
        $this->DBConn->InParameter($stmt, $remark, 'in_remark', 4000);
        $this->DBConn->InParameter($stmt, $tmp_save, 'in_submit', 2);
        $this->DBConn->InParameter($stmt, $userseqno, 'in_assis_username', 20); // add by dennis 2013/07/26 for fixed create_by = "NOTEMP" issue
        // add by dennis 2006-04-24 09:43:37
        if (! is_null($ot_hours) && floatval($ot_hours) > 0) {
            $this->DBConn->InParameter($stmt, $ot_hours, 'out_hours', 6);
        } else {
            $this->DBConn->OutParameter($stmt, $result ['hours'], 'out_hours', 6);
        } // end if
        $this->DBConn->OutParameter($stmt, $result ['msg'], 'out_msg', 2000);
        $this->DBConn->OutParameter($stmt, $result ['flow_seqno'], 'out_overtime_flowseqno', 9);
        $this->DBConn->OutParameter($stmt, $result ['is_success'], 'out_issuccess', 2);
        //$this->DBConn->debug = true;
        $this->DBConn->StartTrans (); // begin transaction
        //print_r($stmt);
        $this->DBConn->Execute($stmt);
        $this->DBConn->CompleteTrans (); // end transaction
        return $result;
    } // end SaveOvertimeApply()


    /**
     *   Private function, get employee id,name
     *   help function of BatchSaveOvertimeForm()
     *   @param $emp_seqno number, employee seq no
     *   @return array
     *   @author: dennis 2006-04-02 10:41:21
     *   @last update: 2006-04-02 10:41:29  by dennis
     */
    private function _getEmployeeInfo($emp_seqno) {
        $sql_string = <<<eof
                select id_no_sz as emp_id,
                       name_sz as emp_name
                  from hr_personnel_base
                 where seg_segment_no = :company_id
                   and id = :emp_seqno
eof;
        return $this->DBConn->GetRow($sql_string,
        array ('company_id' => $this->companyID, 'emp_seqno' => $emp_seqno));
    } // end private function _getEmployeeInfo();


    /**
     *   Batch save overtime form
     *   @param $user_seq_no number, login user seq no
     *   @param $post_array array, 包括批量的员工流水号,员工所在部门的流水号
     *   @return array, 批量输入的结果, 包括每一笔记录的结果
     *   @author: dennis 2006-04-02 10:41:21
     *   @last update: 2006-04-02 10:41:29  by dennis
     */
    function BatchSaveOvertimeForm($user_seq_no, $post_array) {
        /*if (!is_array($post_array["emp_seqno"]))
            {
                trigger_error("Please select a employee.");
            }
            $_result = array();
            for ($i=0; $i<count($post_array["emp_seqno"]); $i++)
            {
                $_result[$i] = $this->SaveOvertimeForm($user_seq_no,$post_array["dept_seq_no"][$i],$post_array,$post_array["emp_seqno"][$i]);
                $_result[$i] =array_change_key_case( array_merge($_result[$i],$this->_getEmployeeInfo($post_array["emp_seqno"][$i])),CASE_LOWER);
            }*/

        if (! is_array($post_array ["emplist"])) {
            trigger_error("Please select a employee.");
        }
        $_result = array ();
        for($i = 0; $i < count($post_array ["emplist"]); $i ++) {
            $post_array ["emp_seqno"] [$i] = substr($post_array ["emplist"] [$i], 0, strpos($post_array ["emplist"] [$i], '|'));
            $post_array ["dept_seq_no"] [$i] = substr($post_array ["emplist"] [$i], strrpos($post_array ["emplist"] [$i], '|') + 1);
            $_result [$i] = $this->SaveOvertimeForm($user_seq_no, $post_array ["dept_seq_no"] [$i], $post_array, $post_array ["emp_seqno"] [$i]);
            $_result [$i] = array_change_key_case(array_merge($_result [$i], $this->_getEmployeeInfo($post_array ["emp_seqno"] [$i])), CASE_LOWER);
        }
        return $_result;
    } // end funciton BatchSaveOvertimeForm()

    /**
     * 批量提交或暂存加班单
     *
     * @param array $overtime_info
     * @param array $emplist
     * @param boolean $issubmit
     * @return boolean
     * @access public
     * @author Dennis
     */
    function batchOvertimeApply($userseqno,
                                $ot_begin_time,
                                $ot_end_time,
                                $ot_hours,
                                $ot_reason,
                                $ot_fee_type,
                                $ot_type,
                                $remark,
                                $tmp_save,
                                array $emplist,
                                array $deptlist)
    {
        for ($i=0; $i<count($emplist); $i++)
        {
            $result[$i] = $this->SaveOvertimeApply($userseqno,
                                                 $deptlist[$i],
                                                 $ot_begin_time,
                                                 $ot_end_time,
                                                 $ot_hours,
                                                 $ot_reason,
                                                 $ot_fee_type,
                                                 $ot_type,
                                                 $remark,
                                                 $tmp_save,
                                                 $emplist[$i]);
        }// end for loop
        return $result;
    }// end batchOvertimeApply()

    /**
     *  Submit overtime apply form to workflow
     *	procedure p_submit_overtime_apply
     *	(
     *		pi_seg_segment_no      varchar2, --公司ID
     *		pi_overtime_flow_sz_id varchar2, --加班申请单ID
     *		po_days                out number, --返回天数
     *		po_hours               out number, -- 返回小时数
     *		po_errmsg              out varchar2, -- 返回错误信息
     *		po_success             out varchar2 -- 操作是否成功 Y/N
     *	);
     * @param $user_seqno number, the current login user seqno (in app_users)
     * @param $workflow_seqno number, the leave form workflow seqno
     * @return array, submit result
     * @author Dennis 2006-03-28 16:44:25  last update: 2006-03-31 16:19:03  by dennis
     */
    function SubmitOvertimeForm($user_seqno, $workflow_seqno) {
        $companyid = $this->companyID;
        $_submit_result = array ('days' => '',
                                 'hours' => '',
                                 'msg' => '',
                                 'is_success' => '');
        $stmt1 = 'begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pkg_work_flow.p_submit_overtime_apply(pi_seg_segment_no=>:in_companyid,pi_overtime_flow_sz_id=>:in_workflow_seqno,po_days=>:out_days,po_hours=>:out_hours,po_errmsg=>:out_msg,po_success=>:out_issuccess); end;end;';
        $stmt = $this->DBConn->PrepareSP($stmt1);
        $this->DBConn->InParameter($stmt, $companyid, 'in_company_id1', 10);
        $this->DBConn->InParameter($stmt, $companyid, 'in_companyid', 10);
        $this->DBConn->InParameter($stmt, $user_seqno, 'in_user_seqno', 10);
        $this->DBConn->InParameter($stmt, $workflow_seqno, 'in_workflow_seqno', 10);
        $this->DBConn->OutParameter($stmt, $_submit_result ['days'], 'out_days', 5);
        $this->DBConn->OutParameter($stmt, $_submit_result ['hours'], 'out_hours', 6);
        $this->DBConn->OutParameter($stmt, $_submit_result ['msg'], 'out_msg', 2000);
        $this->DBConn->OutParameter($stmt, $_submit_result ['is_success'], 'out_issuccess', 2);
        //$this->DBConn->debug = true;
        $this->DBConn->StartTrans (); // begin transaction
        $this->DBConn->Execute($stmt);
        $this->DBConn->CompleteTrans (); // end transaction
        return $_submit_result;
    } // end function SubmitOvertimeForm()

    /**
     * 请假申请查询
     *
     * @param string  $query_where		查询条件
     * @param boolean $who				当前查询资料的是谁,default 是当前登录的使用者
     * @param boolean $get_total_rows	只挑资料总笔数
     * @param number  $numrows			从 offset 起显示多少笔
     * @param number  $offset			从哪一笔资料开始显示
     * @return array
     * @author Dennis 2008-09-19
     */
    function getHR_ABSENCE($query_where,
                           $who = 'myself',
                           $coutrows = false,
                           $numrows = -1,
                           $offset = -1) {
       $sql = <<<eof
            select ha.CDAY CDAY,
                   ha.DAYS DAYS,
                   ha.HOUR HOURS,
                   ha.REMARK REMARK,
                   hat.ABSENCE_CODE ABSENCE_CODE,
                   hat.ABSENCE_NAME ABSENCE_NAME,
                   to_char(ha.ENDTIME, 'hh24:mi') ENDTIME,
                   to_char(ha.begintime, 'hh24:mi') BEGINTIME,
                   ha.absence_id ABSENCE_ID
              from HR_ABSENCE_TYPE hat, HR_ABSENCE ha
             where ha.psn_seg_segment_no = hat.seg_segment_no
               and ha.reason = hat.absence_type_id
               and ha.psn_seg_segment_no = :company_id
               and ha.is_active = 'Y'
               and ha.absence_id not in(select reason
                                          from hr_cancel_absence_flow_sz a
                                         where a.reason=ha.absence_id
                                           and a.status = '03')
               and pk_attend_status_sz.f_attend_confirmed(ha.psn_seg_segment_no,
                                                    ha.psn_id,
                                                    ha.cday,
                                                    'ABSENCE') = 'N'
               %s %s
             order by ha.begintime desc
eof;
		$params = array ('company_id' => $this->companyID);
        $who_where = '';
        // 根据查资料的人员的不同，组合不同的where条件
        switch ($who) {
            case self::MYSELF:
                $who_where = 'and ha.psn_id = :emp_seq_no';
                $params ['emp_seq_no'] = $this->empSeqNo;
            break;
            case self::ASSISTANT:
                $who_where = 'and ha.create_by = :user_seq_no';
                $params ['user_seq_no'] = $_SESSION['user']['user_seq_no'];
                break;
            case self::ADMIN:
                break;
            default:break;
        }// end switch
        $sql = sprintf($sql, $who_where, $query_where);
        //$this->DBConn->debug =true;
        $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($coutrows) {
            ///echo $sql.'---------';
            return $this->DBConn->GetOne('select count(1) from (' . $sql . ')', $params);
        } // end if
        //echo $sql;
        //echo 'offiset ->'.$offset.'<br>';
        //echo 'numrows ->'.$numrows.'<br>';
        //echo $sql;
        $rs = $this->DBConn->SelectLimit($sql, $numrows, $offset, $params);
        //pr($rs->GetArray());
        return $rs->GetArray ();
    } // end getHR_ABSENCE()
    function getCancelLeaveApply($query_where,
                                 $who = 'myself',
                                 $coutrows = false,
                                 $numrows = -1,
                                 $offset = -1) {
        $sql = <<<eof
            select a.leave_flow_seqno as cancel_leave_flow_seqno,
			       a.company_id,
			       a.emp_seq_no,
			       b.id_no_sz as emp_id,
			       b.name_sz as emp_name,
			       c.segment_no_sz as dept_id,
			       c.segment_name as dept_name,
			       a.begin_time,
			       a.end_time,
			       a.hours,
			       a.days,
			       a.absence_seq_no,
			       a.absence_id,
			       a.absence_name,
			       a.my_day,
			       a.reason,
			       a.reject_comment,
			       'cancel_absence' as apply_type,
			       a.flow_status,
			       a.status_name,
			       to_char(a.create_date, 'yyyy/mm/dd hh24:mi:ss') as create_date
			  from ehr_cancel_leave_in_flow_v a, hr_personnel_base b, gl_segment c
			 where a.company_id = :company_id
			   and a.company_id = b.seg_segment_no
			   and a.emp_seq_no = b.id
			   and b.seg_segment_no = c.seg_segment_no
			   and b.seg_segment_no_department = c.segment_no
			   %s %s
			 order by a.begin_time desc, b.id_no_sz asc
eof;
        $params = array ('company_id' => $this->companyID);
        $who_where = '';
        // 根据查资料的人员的不同，组合不同的where条件
        switch ($who) {
            case self::MYSELF:
                $who_where = 'and a.emp_seq_no = :emp_seq_no';
                $params ['emp_seq_no'] = $this->empSeqNo;
            break;
            case self::ASSISTANT:
                $who_where = 'and a.create_by = :user_seq_no';
                $params ['user_seq_no'] = $_SESSION['user']['user_seq_no'];
                break;
            case self::ADMIN:
                break;
            default:break;
        }// end switch
        $sql = sprintf($sql, $who_where, $query_where);
        //$this->DBConn->debug =true;
        $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($coutrows) {
            return $this->DBConn->GetOne('select count(1) from (' . $sql . ')', $params);
        } // end if
        $rs = $this->DBConn->SelectLimit($sql, $numrows, $offset, $params);
        //pr($rs->GetArray());
        return $rs->GetArray ();
    } // end getCancelLeaveApply()
    function getLeaveApply($query_where,
                           $who = 'myself',
                           $coutrows = false,
                           $numrows = -1,
                           $offset = -1) {
        $sql = <<<eof
            select a.leave_flow_seqno,
                   a.company_id,
                   a.emp_seq_no,
                   b.id_no_sz as emp_id,
                   b.name_sz as emp_name,
                   c.segment_no_sz as dept_id,
                   c.segment_name as dept_name,
                   a.begin_time,
                   a.end_time,
                   a.hours,
                   a.days,
                   a.absence_seq_no,
                   a.absence_id,
                   a.absence_name,
                   a.my_day,
                   a.reason,
                   a.reject_comment,
                   'absence' as apply_type,
                   a.flow_status,
                   a.status_name,
                   a.create_date
              from ehr_leave_in_flow_v a, 
        		   hr_personnel_base b,
        		   gl_segment c
             where a.company_id = :company_id
               and a.company_id = b.seg_segment_no
               and a.emp_seq_no = b.id
        	    and b.seg_segment_no = c.seg_segment_no
   				and b.seg_segment_no_department = c.segment_no
               %s %s
             order by c.segment_no_sz,b.id_no_sz,a.begin_time desc
eof;
        $params = array ('company_id' => $this->companyID);
        $who_where = '';
        // 根据查资料的人员的不同，组合不同的where条件
        switch ($who) {
            case self::MYSELF:
                $who_where = 'and a.emp_seq_no = :emp_seq_no';
                $params ['emp_seq_no'] = $this->empSeqNo;
            break;
            case self::ASSISTANT:
                $who_where = 'and a.create_by = :user_seq_no';
                $params ['user_seq_no'] = $_SESSION['user']['user_seq_no'];
                break;
            case self::ADMIN:
                break;
            default:break;
        }// end switch
        $sql = sprintf($sql, $who_where, $query_where);
        //$this->DBConn->debug =true;
        $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($coutrows) {
            ///echo $sql.'---------';
            return $this->DBConn->GetOne('select count(1) from (' . $sql . ')', $params);
        } // end if
        $rs = $this->DBConn->SelectLimit($sql, $numrows, $offset, $params);
        //pr($rs->GetArray());
        return $rs->GetArray ();
    } // end getLeaveApply()

    /**
     * Get leave apply headcount in flow by department
     * @param no
     * @author Dennis 2010-06-03
     */
    function getLeaveApplyCountByDept($whoami = 'assistant')
    {
        $create_by = $whoami=='admin' ? '' : ' and a.create_by   =  \''.$_SESSION['user']['user_seq_no'].'\' ';
        $sql = <<<eof
            select c.segment_no as dept_seq_no,
			       c.segment_no_sz as dept_id,
			       c.segment_name as dept_name,
			       a.flow_status,
			       decode(a.flow_status, '01', '已提交', '02', '流程中', '暂存') as flow_status_desc,
			       count(a.absence_seq_no) as trows
			  from ehr_leave_in_flow_v a, hr_personnel_base b, gl_segment c
			 where a.company_id = b.seg_segment_no
			   and a.emp_seq_no = b.id
			   and b.seg_segment_no = c.seg_segment_no
			   and b.seg_segment_no_department = c.segment_no        
               and a.company_id  = :company_id
               and a.flow_status < '03'
               $create_by
             group by c.segment_no, c.segment_no_sz ,c.segment_name, a.flow_status
            order by c.segment_no_sz
eof;
        //$this->DBConn->debug =1;
        return $this->DBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->companyID));
    }// end getApplyCountByDept()

    /**
     * Get Overtime apply headcount in flow by department
     * @param string $whoami
     * @author Dennis 2010-06-03
     */
    function getOvertimeApplyCountByDept($whoami = 'assistant')
    {
        $create_by = $whoami=='admin' ? '' : ' and a.create_by   =  \''.$_SESSION['user']['user_seq_no'].'\' ';
        $sql = <<<eof
			select c.segment_no as dept_seq_no,
				   c.segment_no_sz as dept_id,
				   c.segment_name as dept_name,
				   a.flow_status,
				   decode(a.flow_status,'01','已提交','02','流程中','暂存') as flow_status_desc,
				   count(a.overtime_flow_seqno) as trows
			 from  ehr_overtime_in_flow_v a,
			       hr_personnel_base   b,
				   gl_segment          c
			 where a.company_id = b.seg_segment_no
			   and a.emp_seq_no = b.id
			   and b.seg_segment_no = c.seg_segment_no
			   and b.seg_segment_no_department = c.segment_no
			   and a.company_id  = :company_id 
			   and a.flow_status < '03'
			   $create_by
			group by c.segment_no,c.segment_no_sz,c.segment_name,a.flow_status
			order by c.segment_no_sz
eof;
        //$this->DBConn-> debug = 1;
        return $this->DBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->companyID));
    }// end getApplyCountByDept()

    /**
     * 加班申请查询
     *
     * @param string  $query_where		查询条件
     * @param boolean $myself			只挑当前login user 的加班申请
     * @param boolean $get_total_rows	只挑资料总笔数
     * @param number  $numrows			从 offset 起显示多少笔
     * @param number  $offset			从哪一笔资料开始显示
     * @return array
     * @author Dennis 2008-09-19
     */
    function getOvertimeApply($query_where,
                              $who = 'myself',
                              $countrow = false,
                              $numrows = -1,
                              $offset = -1) {
        $sql = <<<eof
            select a.overtime_flow_seqno,
                   a.my_day,
                   a.company_id,
                   a.emp_seq_no,
                   b.id_no_sz as emp_id,
			       b.name_sz as emp_name,
			       c.segment_no_sz as dept_id,
			       c.segment_name as dept_name,
                   a.overtime_date,
                   a.hours,
                   a.overtime_fee_name,
                   a.overtime_type_name,
                   a.reason,
                   a.reject_comment,
                   a.begin_time,
                   a.end_time,
                   'overtime' as apply_type,
                   a.flow_status,
                   a.create_date,
                   a.status_name
              from ehr_overtime_in_flow_v a, hr_personnel_base b, gl_segment c
             where a.company_id = b.seg_segment_no
			   and a.emp_seq_no = b.id
			   and b.seg_segment_no = c.seg_segment_no
			   and b.seg_segment_no_department = c.segment_no
               and a.company_id = :company_id
               %s %s
              order by a.my_day desc, c.segment_no_sz, b.id_no_sz
eof;
        $params = array ('company_id' => $this->companyID);
        $who_where = '';
        // 根据查资料的人员的不同，组合不同的where条件
        switch ($who) {
            case self::MYSELF:
                $who_where = 'and a.emp_seq_no = :emp_seq_no';
                $params ['emp_seq_no'] = $this->empSeqNo;
            break;
            case self::ASSISTANT:
                $who_where = 'and a.create_by = :user_seq_no';
                $params ['user_seq_no'] = $_SESSION['user']['user_seq_no'];
                break;
            case self::ADMIN:
                break;
            default:break;
        }// end switch
        $sql = sprintf($sql, $who_where, $query_where);
        //$this->DBConn->debug =true;
        $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($countrow) {
            return $this->DBConn->GetOne('select count(1) from (' . $sql . ')', $params);
        } // end if
        $rs = $this->DBConn->SelectLimit($sql, $numrows, $offset, $params);
        return $rs->GetArray ();
    } // end getOvertimeApply()

    /**
     * 已申请过的假别名称
     * @param string $userseqno current login user seq no
     * @param string $whoami myself_current login user, assistant_department assistant, admin_workflow admin
     * @return array
     * @author Dennis 2008-09-24
     */
    function getWfLeaveName($userseqno, $whoami = 'myself') {
        $sql = <<<eof
            select distinct absence_seq_no,
                   absence_id || '-' || absence_name as absence_name,
                   absence_id
              from ehr_leave_in_flow_v
             where company_id = :company_id
             %s
             order by absence_id
eof;
        $who_where = '';
        $params = array ('company_id' => $this->companyID);
        switch ($whoami) {
            case self::MYSELF :
                $who_where = ' and emp_seq_no = :emp_seq_no ';
                $params ['emp_seq_no'] = $this->empSeqNo;
                break;
            case self::ASSISTANT :
                $who_where = ' and create_by = :user_seq_no ';
                $params ['user_seq_no'] = $userseqno;
                break;
            case self::ADMIN :
                break;
            default :
                break;
        } // end switch
        $sql = sprintf($sql, $who_where);
        //$this->DBConn->debug = true;
        return $this->DBConn->GetArray($sql, $params);
    } // end getWfLeaveNameNew()

    /**
     * Workflow 管理员查询时的部门条件
     * @param no
     * @return array
     * @author Dennis 2008-09-25
     */
    public function getWfDept()
    {
        $sql = <<<eof
            select dept_seq_no,dept_id||' - '|| dept_name as dept_name
            from   ehr_department_v
            where  company_id = :company_id
              and  dept_type = 'DEPARTMENT'
              order by dept_id
eof;
         return $this->DBConn->GetArray($sql, array ('company_id' => $this->companyID));
    }// end getWfDept()
    /**
     *   Get overtime reason list
     *   @param no parameter
     *   @return 2-d array
     */
    function GetOvertimeReason($fetchmode = ADODB_FETCH_NUM) {
        $sql = <<<eof
          select overtime_reason_id,
                 overtime_reason_no ||' '||overtime_reason_desc as overtime_reason,
                 reason
            from hr_overtime_reason
           where is_active = 'Y'
             and seg_segment_no = : company_id
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->Prepare($sql);
        $this->DBConn->SetFetchMode($fetchmode);
        return $this->DBConn->GetArray($sql, array ('company_id' => $this->companyID));
    }// end GetOvertimeReason()

    /**
     *   Get employee leave list
     *   @param $wherecond string , query where condition
     *   @return array, a 2-dimensional array of records
     *	@notice get_query_where() reference to functions.php
     *   @author: dennis
     *   @last update: 2006-05-08 16:29:01 by dennis
     *   @log
     *     1. add "is_confirmed","create_by" 2006-05-08 16:29:39
     *     2. 如果是代销假,要查询出权限内的员工 by dennis 2006-05-11 14:45:16
     *	@modify:2006-7-31 by jack 新增type,默认为员工请假查询,否则为部门查询

     */
    function GetLeaveList($wherecond = "", $cond_1, $cond_2, $create_by = null, $type = null) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        // 如果是代销假,要查询出权限内的员工
        // add by dennis 2006-05-11 14:45:02
        $plsql_stmt = "begin pk_erp.p_set_date(sysdate);pk_erp.p_set_segment_no(:company_id);pk_erp.p_set_username(:user_seq_no);end;";
        $user_seq_no = $_SESSION ["user"] ["user_seq_no"];
        $this->DBConn->Execute($plsql_stmt, array ("company_id" => $companyid, "user_seq_no" => $user_seq_no));
        if (! is_null($create_by)) {
            $_where = " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y' and is_confirmed = 'N'";
        } else {
            if (empty($type)) {
                $_where = " and emp_seq_no = '$emp_seqno'";
            } else {
                $_where = " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'";
            }
        }

        $sql_string = <<<eof
                select abs_form_no,
                       company_id,
                       dept_seq_no,
                       dept_id,
                       dept_name,
                       emp_seq_no,
                       emp_id,
                       emp_name,
                       begin_time,
                       end_time,
                       absence_id,
                       absence_id || '-' || absence_name as absence_name,
                       hours,
                       remark,
                       work_days,
                       is_workday,
                       my_day,
                       is_confirmed,
                       create_by
                  from (select A.*,rownum rn
                          from (select abs_form_no,
                                       company_id,
                                       dept_seq_no,
                                       dept_id,
                                       dept_name,
                                       emp_seq_no,
                                       emp_id,
                                       emp_name,
                                       begin_time,
                                       end_time,
                                       absence_id,
                                       absence_id || '-' || absence_name as absence_name,
                                       hours,
                                       remark,
                                       work_days,
                                       is_workday,
                                       my_day,
                                       is_confirmed,
                                       create_by
                                 from ehr_absence_v
                                where company_id = '$companyid'
                                $_where $wherecond
                                order by my_day desc) A
                        where rownum <=$cond_2)
                    where rn>= $cond_1
                    order by my_day desc
eof;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetLeaveList()


    /**
     *   Get employee salaryleave list
     *   @param $wherecond string , query where condition
     *   @return array, a 2-dimensional array of records
     *   @author: jack
     *   @log
     *     1. add "is_confirmed","create_by" 2006-05-08 16:29:39
     *     2. 如果是代销假,要查询出权限内的员工 by dennis 2006-05-11 14:45:16
     *	@modify:2006-9-29 by jack 新增type,默认为员工请假查询,否则为部门查询

     */
    function GetLeaveSalaryList($wherecond = "", $cond_1, $cond_2, $type = "") {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $user_seq_no = $_SESSION ["user"] ["user_seq_no"];
        $stmt = "begin pk_erp.p_set_segment_no(:company_id); pk_erp.p_set_username(:user_seq_no); end;";

        $this->DBConn->Execute($stmt, array ("company_id" => $companyid, "user_seq_no" => $user_seq_no));
        $_where = empty($type) ? " and emp_seq_no = '$emp_seqno'" : " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'";

        $sql_string = <<<eof
                select year,
                       emp_id,
                       emp_name,
                       absence_seq_no,
                       absence_name,
                       hours,
                       dept_name,
                       begin_time,
                       end_time
                  from (select A.*,rownum rn
                          from (select period_detail_no as year,
                                       emp_id,
                                       emp_name,
                                       absence_seq_no,
                                       absence_id || '-' || absence_name as absence_name,
                                       hours,
                                       dept_name,
                                       begin_time,
                                       end_time
                                 from ehr_absence_salary_v
                                where company_id = '$companyid'
                                  $_where
                                  $wherecond
                                order by begin_time desc) A
                        where rownum <=$cond_2)
                    where rn>= $cond_1
eof;

        //print $sql_string;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetLeaveSalaryList()
    /**
     *   Get employee salary overtime list
     *   @param $wherecond string , query where condition
     *   @return array, a 2-dimensional array of records
     *   @author: jack
     *	@modify:2006-9-29 by jack 新增type,默认为员工请假查询,否则为部门查询

     */
    function GetOvertimeSalaryList($wherecond = "", $cond_1, $cond_2, $type = "") {

        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $user_seq_no = $_SESSION ["user"] ["user_seq_no"];
        $stmt = "begin pk_erp.p_set_segment_no(:company_id); pk_erp.p_set_username(:user_seq_no); end;";

        $this->DBConn->Execute($stmt, array ("company_id" => $companyid, "user_seq_no" => $user_seq_no));
        $_where = empty($type) ? " and emp_seq_no = '$emp_seqno'" : " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'";

        $sql_string = <<<eof
                select my_day,
                       ot_fee_type_id,
                       ot_fee_type,
                       ot_type,
                       hours,
                       emp_id,
                       emp_name,
                       dept_name,
                       begin_time,
                       end_time,
                       remark
                  from (select A.*,rownum rn
                          from (select period_detail_no as my_day,
                                       ot_fee_type_id,
                                       ot_fee_type as ot_fee_type,
                                       ot_type,
                                       hours,
                                       emp_id,
                                       emp_name,
                                       dept_name,
                                       begin_time,
                                       end_time,
                                       reason as remark
                                 from ehr_overtime_salary_v
                                where company_id = '$companyid'
                                  $wherecond
                                  $_where
                                order by begin_time desc) A
                        where rownum <=$cond_2)
                    where rn>= $cond_1
eof;

        //print $sql_string;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetOvertimeSalaryList()
    /**
     *   Get employee leaveyearlist
     *   @return array, a 2-dimensional array of records
     *   @author: Jack 2006-07-07
     */
    function GetLeaveYearList($type = NULL) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $_where = empty($type) ? " and emp_seq_no = '$emp_seqno'" : "";
        $sql_string = <<<eof
                select distinct substrb(to_char(my_day,'YYYY-MM-DD'), 0,4) as year1,
                       substrb(to_char(my_day,'YYYY-MM-DD'), 0,4) as year2
                  from ehr_absence_v
                 where company_id = '$companyid'
                   $_where
                  order by substrb(to_char(my_day, 'YYYY-MM-DD'), 0, 4) desc
eof;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetLeaveList()


    /**
     *   Get employ salary period year
     *   @author: Jack 2006-9-13
     */
    function GetSalaryYearList($type = NULL) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        //得到登陆员工的计薪期间代码  add by Jack
        $period_master_id = $this->DBConn->GetOne("select period_master_id from hr_personnel where id = '" . $emp_seqno . "'");
        $_where = is_null($type) ? " and period_master_id = nvl('" . $period_master_id . "', period_master_id)" : "";

        $sql_string = <<<_LeaveYearList_
                select distinct yyyy as year1,
                       yyyy as year2
                  from hr_period_detail
                 where seg_segment_no = '$companyid'
                 $_where
                 order by yyyy desc
_LeaveYearList_;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetSalaryYearList()


    /**
     *   Get employ salary period year
     *   @author: Jack 2006-9-13
     */
    function GetSalaryMonthList($type = NULL) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        //得到登陆员工的计薪期间代码  add by Jack
        $period_master_id = $this->DBConn->GetOne("select period_master_id from hr_personnel where id = '" . $emp_seqno . "'");
        $_where = is_null($type) ? " and period_master_id = nvl('" . $period_master_id . "', period_master_id)" : "";

        $sql_string = <<<_LeaveYearList_
                select distinct mm as mm1,
                       mm as mm2
                  from hr_period_detail
                 where seg_segment_no = '$companyid'
                 $_where
                  order by mm asc
_LeaveYearList_;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetSalaryMonthList()


    /**
     *   Get employee Overtimeyearlist
     *   @return array, a 2-dimensional array of records
     *   @author: Jack 2006-07-14
     */
    function GetOvertimeYearList($type = NULL) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $_where = empty($type) ? " and emp_seq_no = '$emp_seqno'" : "";
        $sql_string = <<<_OvertimeYearList_
                select distinct substrb(my_day, 0,4) as year1,
                       substrb(my_day, 0,4) as year2
                  from ehr_overtime_v
                 where company_id = '$companyid'
                   $_where
                  order by substrb(my_day,0,4) desc
_OvertimeYearList_;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetOvertimeYearList()
    /**
     *   Get employee leavemonthlist
     *   @return array, a 2-dimensional array of records
     *   @author: Jack 2006-07-07
     */
    function GetLeaveMonthList($type = NULL) {
        $companyid = $this->companyID;
        //$emp_seqno = $this->empSeqNo;
        $emp_seqno = $_SESSION ["user"] ["emp_seq_no"];
        $_where = empty($type) ? " and emp_seq_no = '$emp_seqno'" : "";
        $sql_string = <<<_LeaveYearList_
                select distinct substrb(to_char(my_day,'YYYY-MM-DD'), 6,2) as month1,
                       substrb(to_char(my_day,'YYYY-MM-DD'), 6,2) as month2
                  from ehr_absence_v
                 where company_id = '$companyid'
                   $_where
                  order by substrb(to_char(my_day,'YYYY-MM-DD'), 6,2) asc
_LeaveYearList_;
        //print $sql_string;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetLeaveMonthList()
    /**
     *   Get employee overtimemonthlist
     *   @return array, a 2-dimensional array of records
     *   @update: Jack 2006-7-31 type is not null 为部门月份

     */

    function GetOvertimeMonthList($type = NULL) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $_where = empty($type) ? " and emp_seq_no = '$emp_seqno'" : "";
        $sql_string = <<<_LeaveYearList_
                select distinct substrb(my_day, 6,2) as month1,
                       substrb(my_day, 6,2) as month2
                  from ehr_overtime_v
                 where company_id = '$companyid'
                  $_where
                  order by substrb(my_day,6,2) asc
_LeaveYearList_;
        //print $sql_string;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetOvertimeMonthList()
    /**
     *   Get employee overtime list
     *   @param $wherecond string, query where condition
     *   @return array, a 2-dimensional array of records
     *   @author: Dennis
     *   @last update: 2006-04-18 13:38:01
     */

    function GetOvertimeList($wherecond, $cond_1, $cond_2, $create_by = null, $type = null) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $plsql_stmt = "begin pk_erp.p_set_date(sysdate);pk_erp.p_set_segment_no(:company_id);pk_erp.p_set_username(:user_seq_no);end;";
        $user_seq_no = $_SESSION ["user"] ["user_seq_no"];
        $this->DBConn->Execute($plsql_stmt, array ("company_id" => $companyid, "user_seq_no" => $user_seq_no));
        if (! is_null($create_by)) {
            $_where = " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y' and is_confirmed = 'N'";
        } else {
            if (empty($type)) {
                $_where = " and emp_seq_no = '$emp_seqno'";
            } else {
                $_where = " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'";
            }
        }

        $sql_string = <<<_OvertimeList_
                select distinct emp_id,
                                emp_name,
                                dept_seq_no,
                                dept_id,
                                dept_name,
                                my_day,
                                begin_time,
                                end_time,
                                hours,
                                ot_type_id,
                                ot_type,
                                ot_fee_type_id,
                                ot_fee_type,
                                reason_id,
                                reason
                  from (select A.*, rownum rn
                          from (select distinct emp_id,
                                                emp_name,
                                                dept_seq_no,
                                                dept_id,
                                                dept_name,
                                                my_day,
                                                begin_time,
                                                end_time,
                                                hours,
                                                ot_type_id,
                                                ot_type,
                                                ot_fee_type_id,
                                                ot_fee_type,
                                                reason_id,
                                                reason
                                  from ehr_overtime_v
                                 where company_id = '$companyid'
                                   and emp_seq_no = '$emp_seqno'
                                 $_where $wherecond
                                 order by my_day desc) A
                         where rownum <= $cond_2)
                 where rn >= $cond_1
                 order by my_day desc
_OvertimeList_;

        //print $sql_string;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetOvertimeList()


    /**
     *   Get employee carding record list
     *   @param $where array, query where array (array(array("column_name"=>colunmname,"operator"=>operator,"db_value"=>value)
     *   @return array, a 2-dimensional array of records
     */
    /*unused remark by dennis 2011-12-13
    function GetCardingRecord($whereArray) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $_where_cond = is_array($whereArray) ? get_query_where($whereArray) : "";

        $sql_string = <<<_CardingList_
                select dept_no,
                       my_day,
                       std_in_time,
                       act_in_time,
                       std_out_time,
                       act_out_time,
                       workgroup_id,
                       workgroup_name,
                       continuous_work,
                       rest_begin_time,
                       rest_end_time,
                       workday_hours,
                       exception_id,
                       exception_name,
                       is_free_type,
                       free_time1,
                       free_time2
                  from ehr_carding_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
                   $_where_cond
_CardingList_;
        return $this->DBConn->GetArray($sql_string);
    } // end function GetCardingRecord()
    */


    /**
     *   Get employee calendar
     *   @param $where array, query where array (array(array("column_name"=>colunmname,"operator"=>operator,"db_value"=>value)
     *   @return array, a 2-dimensional array of records
     */
    function GetCalendar($whereArray) {
        $companyid = $this->companyID;
        $emp_seqno = $this->empSeqNo;
        $_where_cond = is_array($whereArray) ? get_query_where($whereArray) : "";

        $sql_string = <<<_CalendarList_
                select my_day,
                       worgroup_seq_no,
                       workgroup_id,
                       workgroup_name,
                       in_time,
                       out_time,
                       holiday,
                       absence_id,
                       absence_name,
                       begin_time,
                       end_time
                  from ehr_calendar_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
                   $_where_cond
_CalendarList_;
        return $this->DBConn->CacheExecute($sql_string);
    } // end function GetCalendar()


    /**
     * 可休假况查询
     * 分开处理 年假，补休，特殊假
     * last update by dennis 2010-05-21
     * @param $emp_sex
     * @param $leave_name_id
     * @param $base_date
     * @return Array
     * @author Dennis
     */
    function GetVacationLeft($emp_sex,$leave_name_id = '',$base_date = null)
    {
        $base_date = is_null($base_date) ? date('Y-m-d') : $base_date;
        $where = empty($leave_name_id) ? '' : " and absence_seq_no = '$leave_name_id' ";
        //echo 'base date ->'.$base_date.'<br/>';
        // add 'stts_unit' by dennis 20091030 (for follow HCP standard)
        $sql = <<<eof
                select absence_seq_no,
                       absence_id,
                       absence_name,
                       decode(unit,'H','小時','D','天數') as unit,
                       stts_unit,
                       is_calendar,
                       legal_days
                  from ehr_absence_type_v
                 where company_id = :company_id
                   and is_active  = 'Y'
                   and (sex_absence = 'A' or sex_absence = :emp_sex)
                    $where
eof;
        //$this->DBConn->debug = true;
        $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        $rs = $this->DBConn->GetArray($sql,array('company_id'=>$this->companyID,'emp_sex'=>$emp_sex));
        //$this->DBConn->Execute('alter session set nls_date_format = 'YYYY-MM-DD'');

        // get 补休假/特殊假/年假 代码
        $sql = <<<eof
              select reverse3   as leave_id0,
                     mend_leave as leave_id1,
                     year_leave
               from  hr_attendset
              where  seg_segment_no = :company_id
eof;
		$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$row = $this->DBConn->GetArray ($sql,array('company_id'=>$this->companyID));
		//pr($row);
		$_cnt = count($rs);

		for($i = 0; $i < $_cnt; $i ++)
		{
			$absenceid = $rs[$i]['ABSENCE_SEQ_NO'];
			$sql = '';
			switch ($absenceid) {
				// 补休假
				case $row [0]['LEAVE_ID1']:
					//$this->DBConn->debug = 1;
					$stmt1 = "begin :out_can_use_hours := pk_mends.f_get_mendhours(p_seg_segment_no => :in_company_id,p_psn_id => :in_emp_seqno,p_date => to_date(:in_my_date,'YYYY-MM-DD'),p_type => 'Y',po_disable_hourt => :out_void_hours,po_exceed_hourt => :out_exceed_hours,po_exceed_hourt_x => :out_exceed_void_hours);end;";
					$stmt = $this->DBConn->PrepareSP($stmt1);
					$this->DBConn->InParameter( $stmt, $this->companyID,              'in_company_id', 10);
					$this->DBConn->InParameter( $stmt, $this->empSeqNo,               'in_emp_seqno', 10);
					$this->DBConn->InParameter( $stmt, $base_date,                    'in_my_date', 10);
					$this->DBConn->OutParameter( $stmt, $rs[$i]['LEFT_HOURS'],        'out_can_use_hours');
					$this->DBConn->OutParameter( $stmt, $rs[$i]['CAN_REST_HOURS'],    'out_void_hours');
					$this->DBConn->OutParameter( $stmt, $rs[$i]['CAN_REST_DAYS'],     'out_exceed_hours');
					$this->DBConn->OutParameter( $stmt, $rs[$i]['ALREADY_REST_DAYS'], 'out_exceed_void_hours');
					$this->DBConn->Execute ($stmt);
				break;
				// 特殊假
				case $row [0]['LEAVE_ID0']:
					$sql = <<<eof
                      	select nvl(t.can_rest_days, 0)     as can_rest_days,
						       0                           as can_rest_hours,
						       nvl(b.already_rest_days, 0) as already_rest_days,
						       0                           as already_rest_hours
						  from hr_personnel a,
						       (select seg_segment_no,
						               psn_id,
						               sum(nvl(funeral_days, 0)) as can_rest_days
						          from hr_funeral
						         where psn_id = :emp_seqno
						           and seg_segment_no = :company_id
						           and to_date(:base_date, 'yyyy-mm-dd')
						               between death_date and  absence_deadline
						         group by seg_segment_no, psn_id) t,
						       (select psn_seg_segment_no as seg_segment_no,
						               psn_id,
						               sum(days) as already_rest_days
						          from hr_absencecarding
						         where psn_id = :emp_seqno
						           and psn_seg_segment_no = :company_id
						           and reason = :absence_id
						           and to_char(cday, 'yyyy') =
						               to_char(to_date(:base_date, 'yyyy-mm-dd'), 'yyyy')
						           and cday <= to_date(:base_date, 'yyyy-mm-dd')
						         group by psn_seg_segment_no, psn_id) b
						 where a.seg_segment_no = t.seg_segment_no(+)
						   and a.seg_segment_no = b.seg_segment_no(+)
						   and a.id = b.psn_id(+)
						   and a.id = t.psn_id(+)
						   and a.id = :emp_seqno
						   and a.seg_segment_no = :company_id
eof;
				break;
				// 年假
				// 请到年假查询中查看
				case $row [0]['YEAR_LEAVE']:
					$sql = <<<eof
						select  :absence_id                                       as absence_id,
							    nvl(only_year_days,0)  +nvl(last_leave_days,0)    as can_rest_days,
							    0                                                 as can_rest_hours,
							    nvl(already_days,0)    + nvl(last_already_days,0) as already_rest_days,
							    0                                                 as can_rest_days,
							    (nvl(only_year_days,0) + nvl(only_year_days1,0) + nvl(last_leave_days,0) -
							     nvl(already_days,0)   - nvl(last_already_days,0)) as left_days
						  from hr_yearabsence
						 where seg_segment_no = :company_id
						   and employee_id    = :emp_seqno
						   and absence_year   = substr(:base_date,0,4)
eof;
				break;
				// 其它假别
				default:
					$sql = <<<eof
						select pk_attend_status_sz.f_sex_can_rest_day(:company_id,
						                                              :emp_seqno,
						                                              :absence_id,
						                                              to_date(:base_date,
						                                                      'yyyy-mm-dd'),
						                                              'D') as can_rest_days,
							   pk_attend_status_sz.f_sex_can_rest_day(:company_id,
						                                              :emp_seqno,
						                                              :absence_id,
						                                              to_date(:base_date,
						                                                      'yyyy-mm-dd'),
						                                              'H') as can_rest_hours,
						       pk_attend_status_sz.f_already_rest_day(:company_id,
						                                              :emp_seqno,
						                                              :absence_id,
						                                              to_date(:base_date,
						                                                      'yyyy-mm-dd'),
						                                              'D',
						                                              'D') as already_rest_days,
						       pk_attend_status_sz.f_already_rest_day(:company_id,
						                                              :emp_seqno,
						                                              :absence_id,
						                                              to_date(:base_date,
						                                                      'yyyy-mm-dd'),
						                                              'H',
						                                              'D') as already_rest_hours
						  from dual

eof;
				break;
			}

			if ($sql !== '')
			{
				//echo $sql.'<br>';
				//$this->DBConn->debug = true;
				$result = $this->DBConn->GetRow($sql, array ('company_id' => $this->companyID,
															 'emp_seqno'  => $this->empSeqNo,
															 'absence_id' => $absenceid,
															 'base_date'  => $base_date));
				if (!isset($result ['CAN_REST_HOURS']) ||
				    $result ['CAN_REST_HOURS'] == '365'||
				    $result ['CAN_REST_HOURS'] < '0') {
					$result ['CAN_REST_HOURS'] = '0';
				}// end if

				if (!isset($result ['CAN_REST_DAYS']) ||
				    $result ['CAN_REST_DAYS'] == '365' ||
				    $result ['CAN_REST_DAYS'] < '0') {
					$result ['CAN_REST_DAYS'] = '0';
				}// end if
			}// end if
		}// end for loop
		//pr($rs);
		return $rs;
	} // end function getVacationLeft()

	/**
	 *   Get year holiday year list
	 *	@param no parameters
	 *   @return sql string for get year list, to match smarty "select" object
	 *	@author: dennis.lan 2006-02-28 13:15:52
	 *	@last update: 2006-02-28 13:16:00
	 */
	function GetYearList() {
		$companyid = $this->companyID;
		$emp_seqno = $this->empSeqNo;
		$sql_string = <<<_YearHolidayList_
				select my_year as year1,
					   my_year as year2
				  from ehr_year_holiday_v
				 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
				 group by my_year
				 order by my_year desc
_YearHolidayList_;
		return $this->DBConn->GetArray($sql_string);
	} // end function GetYearList()


	/**
	 *   Get employee year holidays list and rest/left days
	 *   @param $the_year string, query year, default current year
	 *   @return a 2-dimensional array of employee year holiday records
	 *	@author: Dennis
	 *	@last update: 2006-03-06 14:29:43 by Dennis
	 */
	function GetYearHoliday($the_year = NULL, $cond_1, $cond_2) {
		$_where = empty($the_year) ? "" : "and my_year = $the_year";
		$companyid = $this->companyID;
		$emp_seqno = $this->empSeqNo;
		$sql_string = <<<_YearHolidayList_
				select my_year,
					   in_date,
					   year_holidays,
					   /*year_adjust_days,--HCP version  v.1.0.5.4.1 no this filed*/
					   nvl(already_rest_days,0) as already_rest_days,
					   defered_year_days,
					   defered_adjust_days,
					   defered_expried_date,
					   nvl(already_rest_ddays,0) as already_rest_ddays,
					   active_date,
					   expired_date
					from (select A.*,rownum rn
						    from (select my_year,
										 in_date,
									     year_holidays,
									     already_rest_days,
									     defered_year_days,
									     defered_expried_date,
									     already_rest_ddays,
									     active_date,
									     expired_date
						            from ehr_year_holiday_v
						           where company_id = '$companyid'
									 and emp_seq_no = '$emp_seqno'
									 $_where
								   order by year_holidays desc) A
								  where rownum <=$cond_2)
							where rn >=$cond_1
							order by year_holidays desc
_YearHolidayList_;
		// print $sql_string;
		return $this->DBConn->GetArray($sql_string);

	} // end function GetYearHoliday()


	/**
	 *   Get employee mend holidays list and rest/left days
	 *   @param $wherecond string,query where condition
	 *   @return a 2-dimensional array of employee absence mend holiday records
	 *   @author: dennis 2006-04-19 10:22:31
	 *   @last update: 2006-04-19 10:22:37
	 */
	function GetMendHoliday($wherecond, $cond_1, $cond_2) {
		$companyid = $this->companyID;
		$emp_seqno = $this->empSeqNo;

		$sql_string = <<<_MendHolidayList_
				select abs_mend_seqno,
					   mend_type,
					   my_day,
					   mend_hours,
					   expired_date
				  from (select A.*,rownum rn
					      from (select abs_mend_seqno,
									   decode(mend_type,2,'请假',1,'加班','') as mend_type,
									   my_day,
									   mend_hours,
									   expired_date
								  from ehr_holiday_mend_v
								 where company_id = '$companyid'
								 and emp_seq_no = '$emp_seqno'
								 $wherecond
							     order by my_day desc) A
					     where rownum <= $cond_2)
					  where rn >=$cond_1
					  order by my_day desc
_MendHolidayList_;
		$rs = $this->DBConn->GetArray($sql_string);
		$_cnt = count($rs);
		/*
			:hr_absencemend.nb_left_mend := 		  pk_mends.f_get_mendhours(:hr_absencemend.psn_seg_segment_no,
									  :hr_absencemend.psn_id,
									  :hr_absencemend.cday,'Y',
									  :hr_absencemend.nb_disable_mend,
									  :hr_absencemend.nb_exceed_mend,
									  :hr_absencemend.nb_exceed_mend_x);
			*/
		$stmt1 = "begin :out_can_use_hours := pk_mends.f_get_mendhours(p_seg_segment_no => :in_company_id,p_psn_id => :in_emp_seqno,p_date => to_date(:in_my_date,'YYYY-MM-DD'),p_type => 'Y',po_disable_hourt => :out_void_hours,po_exceed_hourt => :out_exceed_hours,po_exceed_hourt_x => :out_exceed_void_hours);end;";
		$stmt = $this->DBConn->PrepareSP($stmt1);

		/*
			$_can_use_hours = 0; // 可用时数
			$_void_hours  = 0;   // 失效时数
			$_exceed_hours  = 0; // 超休时数
			$_exceed_void_hours = 0;//超休失效时数
			*/
		for($i = 0; $i < $_cnt; $i ++) {
			$this->DBConn->InParameter($stmt, $companyid, "in_company_id", 10);
			$this->DBConn->InParameter($stmt, $emp_seqno, "in_emp_seqno", 10);
			$this->DBConn->InParameter($stmt, $rs [$i] ["MY_DAY"], "in_my_date");
			//$this->DBConn->InParameter($stmt,"Y","in_is_sysdate");
			$this->DBConn->OutParameter($stmt, $rs [$i] ["CAN_USE_HOURS"], "out_can_use_hours");
			$this->DBConn->OutParameter($stmt, $rs [$i] ["VOID_HOURS"], "out_void_hours");
			$this->DBConn->OutParameter($stmt, $rs [$i] ["EXCEED_HOURS"], "out_exceed_hours");
			$this->DBConn->OutParameter($stmt, $rs [$i] ["EXCEED_VOID_HOURS"], "out_exceed_void_hours");
			//$this->DBConn->debug = true;
			$this->DBConn->StartTrans (); // begin transaction
			$this->DBConn->Execute($stmt);
			$this->DBConn->CompleteTrans (); // end transaction
		}
		return $rs;
	} // end function GetMendHoliday()


	/**
	 *   Get employee leave counts
	 *   @ $wherecond string,query where condition
	 *   @return a 2-dimensional array of employee leave counts records
	 *	@author: Jack 2006-07-14
	 *   @update by jack add type = '',employ; 1,department
	 */
	function GetLeaveCount($wherecond, $cond_1, $cond_2, $type = NULL) {
		$companyid = $this->companyID;
		$emp_seqno = $this->empSeqNo;
		$user_seq_no = $_SESSION ["user"] ["user_seq_no"];
		$stmt = "begin pk_erp.p_set_segment_no(:company_id); pk_erp.p_set_username(:user_seq_no); end;";

		$this->DBConn->Execute($stmt, array ("company_id" => $companyid, "user_seq_no" => $user_seq_no));

		$_where = is_null($type) ? " and emp_seq_no = '$emp_seqno'" : " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'";
		$sql_string = <<<_GetLeaveCount_
			   SELECT year, absence_name, absence_seq_no,hours
					  FROM (SELECT A.*, ROWNUM RN
							  FROM (SELECT my_time year, absence_name, absence_seq_no,sum(hours) hours
									  FROM ehr_absence_v
									 WHERE company_id = '$companyid'
									   $_where
									   $wherecond
									 group by my_time, absence_name, absence_seq_no
									 order by my_time desc) A
							 WHERE ROWNUM < = $cond_2)
 WHERE RN > = $cond_1
_GetLeaveCount_;
		//print $sql_string;
		return $this->DBConn->GetArray($sql_string);
	} //end function GetLeaveCount()
	/**
	 *   Get employee salary leave counts
	 *   @ $wherecond string,query where condition
	 *   @return a 2-dimensional array of employee leave counts records
	 *	@author: Jack 2006-9-26
	 *   @update by jack add type = '',employ; 1,department
	 */
	function GetLeaveSalaryCount($wherecond, $cond_1, $cond_2, $type = NULL) {
		$companyid = $this->companyID;
		$emp_seqno = $this->empSeqNo;

		//得到登陆员工的计薪期间代码  add by Jack
		$period_master_id = $this->DBConn->GetOne("select period_master_id from hr_personnel where id = '" . $emp_seqno . "'");
		$_where = is_null($type) ? " and emp_seq_no = '$emp_seqno' and period_master_id = nvl('" . $period_master_id . "', period_master_id)" : " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'";

		$sql_string = <<<_GetSalaryLeaveCount_
			   select year, absence_name, absence_seq_no, hours
					  from (select A.*, rownum rn
							  from (select period_detail_no year, absence_name, absence_seq_no,sum(hours) hours
									  from ehr_absence_salary_v
									 where company_id = '$companyid'
									 $_where
									 $wherecond
									 group by period_detail_no,absence_name,absence_seq_no
									 order by period_detail_no desc) A
							 where rownum <= $cond_2)
					 where rn >= $cond_1
_GetSalaryLeaveCount_;
		//print $sql_string;
		return $this->DBConn->GetArray($sql_string);
	} //end function GetSalaryLeaveCount()
	/**
	 *   Get employee salary overtime counts
	 *   @ $wherecond string,query where condition
	 *   @return a 2-dimensional array of employee leave counts records
	 *	@author: Jack 2006-9-26
	 *   @update by jack add type = '',employ; 1,department
	 */
	function GetOvertimeSalaryCount($wherecond, $cond_1, $cond_2, $type = NULL) {
		$companyid = $this->companyID;
		$emp_seqno = $this->empSeqNo;

		//得到登陆员工的计薪期间代码  add by Jack
		$period_master_id = $this->DBConn->GetOne("select period_master_id from hr_personnel where id = '" . $emp_seqno . "'");
		$_where = is_null($type) ? " and emp_seq_no = '$emp_seqno' and period_master_id = nvl('" . $period_master_id . "', period_master_id)" : " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'";

		$sql_string = <<<_GetSalaryLeaveCount_
			   select year, ot_fee_type, ot_fee_type_id, hours
					  from (select A.*, rownum rn
							  from (select period_detail_no year, ot_fee_type, ot_fee_type_id,sum(hours) hours
									  from ehr_overtime_salary_v
									 where company_id = '$companyid'
									 $_where
									 $wherecond
									 group by period_detail_no,ot_fee_type,ot_fee_type_id
									 order by period_detail_no desc) A
							 where rownum <= $cond_2)
					 where rn >= $cond_1
_GetSalaryLeaveCount_;

		//print $sql_string;
		return $this->DBConn->GetArray($sql_string);
	} //end function GetSalaryLeaveCount()
	/**
	 *   Get employee overtime counts
	 *   @ $wherecond string,query where condition
	 *   @return a 2-dimensional array of employee overtime counts records
	 *   @update 2006-7-31 by jack add type = '',employ; 1,department
	 */
	/*
	function GetOvertimeCount($wherecond, $cond_1 = "", $cond_2 = "", $type = NULL) {
		$companyid = $this->companyID;
		$emp_seqno = $this->empSeqNo;
		$user_seq_no = $_SESSION ["user"] ["user_seq_no"];
		$stmt = "begin pk_erp.p_set_segment_no(:company_id); pk_erp.p_set_username(:user_seq_no); end;";
		$this->DBConn->Execute($stmt, array ("company_id" => $companyid, "user_seq_no" => $user_seq_no));

		$_where = is_null($type) ? " and emp_seq_no = '$emp_seqno'" : " and pk_user_priv.f_user_priv(emp_seq_no) = 'Y'";
		$sql_string = <<<_GetOvertimeCount_
				 SELECT year, ot_fee_type, ot_fee_type_id,hours
					  FROM (SELECT A.*, ROWNUM RN
							  FROM (SELECT my_time year, ot_fee_type, ot_fee_type_id,sum(hours) hours
									  FROM ehr_overtime_v
									 WHERE company_id = '$companyid'
									   $_where
									   $wherecond
									 group by my_time, ot_fee_type, ot_fee_type_id
								     order by my_time desc) A
							 WHERE ROWNUM < = $cond_2)
				 WHERE RN > = $cond_1
_GetOvertimeCount_;
		//print $sql_string;
		return $this->DBConn->GetArray($sql_string);
	} //end function GetOvertimeCount()
	*/
	/**
	 *   Cancel leave of absence
        procedure p_save_cancel_absence
	 *****************************************************************************
            Created by FrankFei at 2006/04/27
            功能：保存销假申请单，进行相关校验
	 *****************************************************************************
       (  pi_seg_segment_no            varchar2, -- 公司ID
            pi_psn_id                    varchar2, -- 员工ID
            pi_absence_id                number, --请假ID
            pi_date_begin                date, -- 销假开始时间
            pi_date_end                  date, -- 销假结束时间
            pi_remark                    varchar2, -- 备注
            po_days                      out number, -- 返回天数
            po_hours                     out number, -- 返回小时数
            po_errmsg                    out varchar2, -- 返回错误信息
            po_cancel_absence_flow_sz_id in out number, -- 返回消假申请单ID
            po_success                   out varchar2, -- 操作是否成功 Y/N
            pi_submit                    varchar2 default 'N', -- 是否立即提交申请
            pi___only_submit             varchar2 default 'N' -- 私有参数,仅供单独提交申请程式传入Y值使用

       ) is
	 *   @param $user_seqno number, login user seq no (in app_users)
	 *   @param $postarray array, vars array via post (in app_users)
	 *   @param $emp_seqno number, employee sequence number (in hr_personnel_base)
	 *   @author: dennis 2006-05-08 15:03:35
	 *   @last update: 2006-05-09 10:22:15 by dennis
	 */
	function CancelLeaveofAbsence1($user_seqno,
			                       $leave_seqno) {
	    $companyid = $this->companyID;
	    $_save_result = array ("days" => "", "hours" => "", "msg" => "", "flow_seqno" => "", "is_success" => "");
	    $_submit='Y';
	    $stmt1 = "begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pk_cancel_absence_wf.p_save_cancel_absence(pi_absence_id=>:in_absence_id,po_days=>:out_days,po_hours=>:out_hours,po_errmsg=>:out_msg,po_cancel_absence_flow_sz_id=>:out_cancel_flowseqno,po_success=>:out_issuccess,pi_submit=>:in_submit); end;end;";
	    $stmt = $this->DBConn->PrepareSP($stmt1);
	    $this->DBConn->InParameter($stmt, $companyid, "in_company_id1", 10);
	    $this->DBConn->InParameter($stmt, $user_seqno, "in_user_seqno", 10);
		$this->DBConn->InParameter($stmt, $leave_seqno, "in_absence_id", 10);
		$this->DBConn->InParameter($stmt, $_submit, "in_submit", 2);
		$this->DBConn->OutParameter($stmt, $_save_result ["days"], "out_days", 5);
		$this->DBConn->OutParameter($stmt, $_save_result ["hours"], "out_hours", 6);
		$this->DBConn->OutParameter($stmt, $_save_result ["msg"], "out_msg", 2000);
		$this->DBConn->OutParameter($stmt, $_save_result ["flow_seqno"], "out_cancel_flowseqno", 9);
		$this->DBConn->OutParameter($stmt, $_save_result ["is_success"], "out_issuccess", 2);
		//$this->DBConn->debug = true;
		$this->DBConn->StartTrans (); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans (); // end transaction
		return $_save_result;
	}// end function CancelLeaveofAbsence1()
	function CancelLeaveofAbsence($user_seqno, $postarray, $emp_seqno = NULL) {

		$companyid = $this->companyID;
		// 如果传了 emp_seq_no 就销所传的 emp
		$_emp_seqno = is_null($emp_seqno) ? $this->empSeqNo : $emp_seqno;
		//print "<br>".$_emp_seqno ;
		$_save_result = array ("days" => "", "hours" => "", "msg" => "", "flow_seqno" => "", "is_success" => "");
		$_submit = (isset($postarray ["submit"]) && ! empty($postarray ["submit"])) ? "Y" : "N";

		$stmt1 = "begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin wf.pk_cancel_absence_wf.p_save_cancel_absence(pi_seg_segment_no=>:in_companyid,pi_psn_id=>:in_empseqno,pi_absence_id=>:in_absence_id,pi_date_begin=>to_date(:in_begintime,'YYYY-MM-DD HH24:MI'),pi_date_end=>to_date(:in_endtime,'YYYY-MM-DD HH24:MI'),pi_remark=>:in_reason,pi_submit=>:in_submit,po_days=>:out_days,po_hours=>:out_hours,po_errmsg=>:out_msg,po_cancel_absence_flow_sz_id=>:out_cancel_flowseqno,po_success=>:out_issuccess); end;end;";

		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $companyid, "in_company_id1", 10);
		$this->DBConn->InParameter($stmt, $companyid, "in_companyid", 10);
		$this->DBConn->InParameter($stmt, $user_seqno, "in_user_seqno", 10);
		$this->DBConn->InParameter($stmt, $_emp_seqno, "in_empseqno", 10);
		$this->DBConn->InParameter($stmt, $postarray ["absence_id"], "in_absence_id", 10);
		$this->DBConn->InParameter($stmt, $postarray ["begin_time"], "in_begintime", 20);
		$this->DBConn->InParameter($stmt, $postarray ["end_time"], "in_endtime", 20);
		$this->DBConn->InParameter($stmt, $postarray ["reason"], "in_reason", 4000);
		$this->DBConn->InParameter($stmt, $_submit, "in_submit", 2);
		$this->DBConn->OutParameter($stmt, $_save_result ["days"], "out_days", 5);
		$this->DBConn->OutParameter($stmt, $_save_result ["hours"], "out_hours", 6);
		$this->DBConn->OutParameter($stmt, $_save_result ["msg"], "out_msg", 2000);
		$this->DBConn->OutParameter($stmt, $_save_result ["flow_seqno"], "out_cancel_flowseqno", 9);
		$this->DBConn->OutParameter($stmt, $_save_result ["is_success"], "out_issuccess", 2);
		//$this->DBConn->debug = true;
		$this->DBConn->StartTrans (); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans (); // end transaction
		return $_save_result;
	} // end function CancelLeaveofAbsence()


	/**
	 * 取得考勤参数设定中的病假代码设定
	 *
	 * @return string
	 * @author Dennis
	 *
	 */
	function getSickLeaveId() {
		$sql = "select sick_leave from hr_attendset where seg_segment_no = '%s'";
		return $this->DBConn->GetOne(sprintf($sql, $this->companyID));
	} // end getSickLeaveId()

	/**
	 * Get upload dir
	 *
	 * @param string $companyid
	 * @return string
	 */
	/* remark by dennis 2011-11-20 设置在 config 里了
	function GetAttachDir() {
		$sql_string = "select parameter_value
                  from pb_parameters
                 where parameter_id = 'UPLOAD_URL'
                   and seg_segment_no = '%s'";
		return $this->DBConn->GetOne(sprintf($sql_string, $this->companyID));
	} // end GetAttachDir()
	*/
	/**
	 * 把病假附件的路径附到说明栏位里,并加上 alink click to open the attach file
	 *
	 * @param string $flowseqno 请假单流水号
	 * @param string $filename  full path of attache file
	 * @return boolean
	 * @author Dennis 2008-09-17
	 */
	function UpdateAttachFileName($flowseqno, $filename) {
		$sql = 'update hr_absence_flow_sz
        	           set remark = remark||\'<br/><a type="popup" href="' . $filename . '">查看附件 </a>\'
					   where seg_segment_no    = :company_id
					     and absence_flow_sz_id=:flow_seqno';
		//$this->DBConn->debug = true;
		return $this->DBConn->Execute($sql, array ('company_id' => $this->companyID, 'flow_seqno' => $flowseqno));
	} // end UpdateAttachFileName()

	/**
	 * 从多语资料中挑 List 的多语
	 *
	 * @param string $programno  程式代码
	 * @param string $labelid    多语 Key
	 * @param string $lang       语言代码
	 * @return array 2d-array of workflow status
	 * @author Dennis 2008-09-11
	 *
	 */
	function getListMultiLang($programno, $labelid, $lang, $where = '') {
		$sql = 'select  seq as option_value, value as lable_text
				  from app_muti_lang
				 where program_no = :program_no
				   and name = :labelid
				   and lang_code  = :lang_code
				   and type_code = \'LL\'';
		//$this->DBConn->debug = true;
		 $this->DBConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->DBConn->GetArray($sql . $where, array ('program_no' => $programno, 'labelid' => $labelid, 'lang_code' => $lang));
	} // end getFlowStatus()

	/**
	 * 加班或请假规则说明
	 *
	 * @param string $rtype 规则类型
	 * @return string
	 * @author Dennis 2009-03-23
	 */
	function getRuleText($rtype = 'overtime_apply_rule')
	{
		$sql = <<<eof
			select text from ehr_md_content where code=:rtype and seg_segment_no = :companyid
eof;
		//$this->DBConn->debug = true;
		return $this->DBConn->GetOne($sql,array('rtype'=>$rtype,'companyid'=>$this->companyID));
	}// end getRuleText()

	public function getMyOTType($date1)
	{
		$sql = <<<eof
			select f_otype_info(:company_id, :emp_seqno, to_date(:date1,'yyyy-mm-dd'), '01') as h_type,
			       f_otype_info(:company_id1, :emp_seqno1, to_date(:date2,'yyyy-mm-dd'), '02') as ot_type
			  from dual
eof;
		$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		//$this->DBConn->debug = true;
		return $this->DBConn->GetRow($sql,array('company_id'=>$this->companyID,
												 'emp_seqno'=>$this->empSeqNo,
												 'date1'=>$date1,
												 'company_id1'=>$this->companyID,
												 'emp_seqno1'=>$this->empSeqNo,
												 'date2'=>$date1));

	}// end getMyOTType()

	function getMyOTHours($thedate,$begintime,$endtime)
	{
		$stmt1 = "begin pk_overtime.p_overtime_hours(p_seg_segment_no => :p_seg_segment_no,p_psn_id => :p_psn_id,p_begintemp => to_date(:p_begintemp,'yyyy-mm-dd'),p_begintime => to_date(:p_begintime,'hh24:mi'),p_endtime => to_date(:p_endtime,'hh24:mi'),p_hours => :p_hours,p_errmsg => :p_errmsg);end;";
		$result = array();
		$stmt = $this->DBConn->PrepareSP($stmt1);
		$this->DBConn->InParameter($stmt, $this->companyID, "p_seg_segment_no", 10);
		$this->DBConn->InParameter($stmt, $this->empSeqNo, "p_psn_id", 10);
		$this->DBConn->InParameter($stmt, $thedate, "p_begintemp", 20);
		$this->DBConn->InParameter($stmt, $begintime, "p_begintime", 20);
		$this->DBConn->InParameter($stmt, $endtime, "p_endtime", 20);
		$this->DBConn->OutParameter($stmt, $result['hours'], "p_hours", 5);
		$this->DBConn->OutParameter($stmt, $result ['error_msg'], "p_errmsg",2000);
		//$this->DBConn->debug = true;
		$this->DBConn->StartTrans (); // begin transaction
		$this->DBConn->Execute($stmt);
		$this->DBConn->CompleteTrans (); // end transaction

		return $result;
	}

	/**
	 * 特殊假别
	 * @param no
	 * @author dennis
	 */
	function getSpecAbsence()
	{
		$sql = <<<eof
			select absence_type_id
			  from hr_family_type_master
			 where seg_segment_no = :company_id
eof;
		$rs = $this->DBConn->GetArray($sql,array('company_id'=>$this->companyID));
		return json_encode($rs);
	}

	/**
	 * 可休假况查询
	 * @param string $absence_id
	 * @return array
	 * @author Dennis 2010-09-02 last update
	 * @changelog
	 *   2014/04/09 1.弃除可休非大于 0 的记录； 2.弃除性别假
	 */
	public function getVacationLeftN($absence_id = null,$base_date = null)
	{
		$where = empty($absence_id) ? '' : ' and b.absence_type_id = '.$absence_id;
		$year_mend_id = $this->_getYearMendID();
		if (is_array($year_mend_id))
		{
			if (!empty($year_mend_id['YEAR_LEAVE']))
			$where .= ' and b.absence_type_id !='. $year_mend_id['YEAR_LEAVE'];
			if (!empty($year_mend_id['MEND_LEAVE']))
			$where .= ' and b.absence_type_id !='. $year_mend_id['MEND_LEAVE'];
		}
		// add by dennis for add base date 2010-10-13
		$date = is_null($base_date) ? date('Y-m-d') : $base_date;
		$sql = <<<eof
			select a.id_no_sz,
			       b.absence_code as absence_id,
			       b.absence_name,
			       b.calendar_yn as is_calendar,
			       nvl(pk_attend_status_sz.f_get_psn_day(a.seg_segment_no,
			                                             a.id,
			                                             to_date(:mydate, 'yyyy-mm-dd'),
			                                             b.absence_type_id),
			           0) can_rest,
			       nvl(pk_attend_status_sz.f_already_rest_day(a.seg_segment_no,
			                                                  a.id,
			                                                  b.absence_type_id,
			                                                  to_date(:mydate1, 'yyyy-mm-dd'),
			                                                  'D'),
			           0) already_rest,
			       nvl(pk_attend_status_sz.f_sex_can_rest_day(a.seg_segment_no,
			                                                  a.id,
			                                                  b.absence_type_id,
			                                                  to_date(:mydate2, 'yyyy-mm-dd'),
			                                                  'D'),
			           0) left,
			       b.statistic_unit as unit
			  from hr_personnel_base a, hr_absence_type b , hr_attendset c
			 where a.seg_segment_no = b.seg_segment_no
			   and a.id             = :emp_seqno
			   and a.seg_segment_no = :company_id
			   and b.is_active      = 'Y'
			   and (b.reverse1 = 'A' or b.reverse1 = :my_gender)
			   and nvl(pk_attend_status_sz.f_get_psn_day(a.seg_segment_no,
			                                             a.id,
			                                             to_date(:mydate, 'yyyy-mm-dd'),
			                                             b.absence_type_id),
			           0) >0
			   and a.seg_segment_no = c.seg_segment_no 
			   and b.absence_type_id != c.late_leave
			   and b.absence_type_id != c.early_leave
			   and b.absence_type_id != c.abs_leave
			   $where
eof;
		//$this->DBConn->debug = true;
		$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->DBConn->GetArray($sql,array('company_id'=>$this->companyID,
		                                          'emp_seqno'=>$this->empSeqNo,
										  		  'mydate'=>$date,
										  		  'mydate1'=>$date,
										  		  'mydate2'=>$date,
		                                          'my_gender'=>$_SESSION['user']['sex']
		));
	}
	
	/**
	 * Get OT pay or rest by emp's ot id
	 * N,S,H as js array index for change pay or rest 
	 * @return array row of every day
	 * @author Dennis 2011-12-09 TW Mantis 6310
	 */
	public function getOTTypeFee()
	{
		$sql = <<<eof
			select a.otn_reason as N,
			       a.ots_reason as S,
			       a.oth_reason as H
			  from hr_overtimetype a, hr_personnel_base b
			 where a.seg_segment_no = b.seg_segment_no
			   and a.hr_overtimetype_id = b.overtime_type_id
			   and b.id = :emp_seqno
			   and b.seg_segment_no = :company_id
eof;
		$this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$r =  $this->DBConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('emp_seqno'=>$this->empSeqNo,
					'company_id'=>$this->companyID));
		$jscode = 'var otfee = new Array();'; 
		if (is_array($r) && count($r)>0){
    		foreach($r[0] as $k=>$v)
    		{
    			$jscode .= ' otfee["'.$k.'"]="'.$v.'";'; 
    		}
		}else{
		    // 台湾公司无此相关设定 add by dennis 2014/09/26
		    $jscode .= 'otfee["N"] = "";';
		    $jscode .= 'otfee["S"] = "";';
		    $jscode .= 'otfee["H"] = "";';
		}
		return $jscode;
	}
	
	/**
	 * Get Leave Name by Id
	 * @param number $absid
	 * @return string
	 * @author Dennnis 2013/09/16 
	 */
	public function getLeaveNameById($absid){
        $sql = <<<eof
        select absence_name
		  from hr_absence_type
		 where seg_segment_no = :companyid
		   and absence_type_id = :absid
eof;
        //$this->DBConn->debug = 1;
        return $this->DBConn->CacheGetOne(self::DATA_CACHE_SECONDS,$sql,array('companyid' => $this->companyID,'absid'=>$absid));
    } // end function getLeaveNameById()
    
    public function getShiftBeginTime($date)
    {
        $sql = <<<eof
            select to_char(intime, 'hh24:mi')
              from hr_carding
             where psn_seg_segment_no = :company_id
               and psn_id = :emp_seqno
               and cday = to_date(:cday, 'yyyy-mm-dd')
eof;
        //$this->DBConn->debug = 1;
        return $this->DBConn->CacheGetOne(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->companyID,
                'emp_seqno'=>$this->empSeqNo,'cday'=>$date));      
    }
    
    public function getShiftEndTime($date)
    {
        $sql = <<<eof
            select to_char(outtime, 'hh24:mi')
              from hr_carding
             where psn_seg_segment_no = :company_id
               and psn_id = :emp_seqno
               and cday = to_date(:cday, 'yyyy-mm-dd')
eof;
        //$this->DBConn->debug = 1;
        return $this->DBConn->CacheGetOne(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->companyID,
                'emp_seqno'=>$this->empSeqNo,'cday'=>$date));  
    }
}// end class AresAttend
