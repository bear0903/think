<?php
 /**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) ARES CHINA Inc.
 *
 *	Description:
 *     ARES Workflow Class
 *       1. approve leave/overtime apply form
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresWorkflow.class.php $
 *  $Id: AresWorkflow.class.php 3841 2014-09-17 08:18:33Z dennis $
 *  $Rev: 3841 $
 *  $Date: 2014-09-17 16:18:33 +0800 (周三, 17 九月 2014) $
 *  $Author: dennis $
 *  $LastChangedDate: 2014-09-17 16:18:33 +0800 (周三, 17 九月 2014) $
 ****************************************************************************/
    class AresWorkflow
    {
    	protected $_dbConn;
    	protected $_tpl;
    	
    	const DATA_CACHE_SECONDS   = 3600; // 1 HOUR

        /**
        * Counstructor of class AresWorkflow
        */
        function __construct()
        {
            global $g_db_sql;
            global $g_tpl;
            $this->_dbConn = &$g_db_sql;
            $this->_tpl    = &$g_tpl;
        }// end __construct()

        /**
        *   Check parameter workflow type
        *   @param $flowtype string, must be 'absence' / 'overtime' / 'cancel_absence'
        *   @return void, if not pass, raise trigger error.
        *   @author: dennis 2006-04-17 20:30:07
        *   @last update: 2006-04-17 20:30:13 by dennis
        *   @update log:
        *       1. add new flow type 'cancel_absence' by dennis 2006-05-09 19:06:15
        *   Copy from AresAttend.class.php
        *  last update by dennis 2009118
        */
        private function _checkFlowType($flowtype)
        {
            $flow_type_array = array('absence','overtime','cancel_absence','trans','nocard','resign','user_defined');
            if (!in_array(strtolower($flowtype),$flow_type_array))
            {
                trigger_error('Programming Error: Unknow Workflow Type, Must be <b>absence</b>,<b>overtime</b>,<b>trans</b>,<b>nocard</b>,<b>resign</b>,<b>cancel_absence</b> or <b>user_defined</b>. <br/>Current Workflow Type is '.$flowtype,E_USER_ERROR);
            }
        }// end function _checkFlowType();

        /**
        *   Workflow Approvement process
        *
        * procedure p_absence_approve(
				pi_seg_segment_no        varchar2, -- 公司ID
                pi_absence_approve_sz_id number, -- 主管签核ID
                pi_approve_flag          varchar2, -- Y:核准/ N驳回
                pi_reject_reason         varchar2, -- 如果是驳回,则这里传入驳回原因
                po_errmsg                out varchar2, -- 返回错误信息
                po_success               out varchar2, -- 操作是否成功 Y/N
                pi_approve_psn_id        varchar2 default null -- 签核主管员工ID
            )
            procedure p_overtime_approve (
				pi_seg_segment_no         varchar2, -- 公司ID
                pi_overtime_approve_sz_id number, -- 主管签核ID
                pi_approve_flag           varchar2, -- Y:核准/ N驳回
                pi_reject_reason          varchar2, -- 如果是驳回,则这里传入驳回原因
                po_errmsg                 out varchar2, -- 返回错误信息
                po_success                out varchar2, -- 操作是否成功 Y/N
                pi_approve_psn_id         varchar2 default null -- 签核主管员工ID
            )
		      传入公司ID，主管签核ID，核准标志Y:核准/N:驳回, 驳回原因
		      返回错误信息，如果返回的po_success为'N' ，则操作不成功
			p_approve(pi_seg_segment_no        varchar2, -- 公司ID
		   pi_approve_sz_id         number, -- 主管签核ID
		   pi_approve_flag          varchar2, -- Y:核准/ N驳回
		   pi_reject_reason         varchar2, -- 如果是驳回,则这里传入驳回原因
		   po_errmsg                out varchar2, -- 返回错误信息
		   po_success               out varchar2, -- 操作是否成功 Y/N
		   pi_table_name            varchar2,--flow table 名字
		   pi_id_name               varchar2,--table id 名字
		   pi_type                  varchar2, --申请类别
		   pi_approve_psn_id        varchar2 default null -- 签核主管员工ID
		   )
		 *
         *
         * @param string $urlcode
         * @param string $reject_resaon
         * @return array
         * @author Dennis  last modify by dennis 20091117
         */
        function Approve($urlcode,$reject_resaon = null)
        {
        	//$this->_dbConn->debug = true;
            $_workflow = array();
            $_workflow = is_array($urlcode) ? $urlcode : $this->ParseWorkflowSecretCode($urlcode); // 解码
            //pr($_workflow); exit;
            $_approve_result = array('msg'=>'',
                                     'is_success'=>'',
                                     'apply_type'=>$_workflow['apply_type'],
                                     'workflow_seqno'=>$_workflow['workflow_seqno'],
                                     'company_id'=>$_workflow['company_id']);

            // 因为要和批量签核共用,所以这里 re-write by Dennis 2006-04-05 16:41:05
            $reject_resaon = is_array($urlcode) ? $urlcode['reject_reason'] : $reject_resaon;

            $_package_name = $_workflow['apply_type'] == 'cancel_absence' ?
                             'wf.pk_cancel_absence_wf.'                    :
                             'wf.pkg_work_flow.';

            $_procedure_name = "p_{$_workflow['apply_type']}_approve";
             // pl/sql 参数的长度最长只能为 30 所以这里做一下替换
            // cance_absence -> c_absence
            $_applytype = str_replace('cancel','c',$_workflow['apply_type']);

            // add by dennis 20091118 for user defined workflow
            $user_define_wf_params = '';
            // user defined workflow 多两个参数
            if (isset($_workflow['wf_flow_table']) && !empty($_workflow['wf_flow_table']))
            {
            	$_procedure_name 		= 'p_approve';
            	$user_define_wf_params 	= 'pi_type=>:in_flow_type,pi_table_name=>:in_wf_table,pi_id_name=>:in_flow_key,';
            	$_applytype 			= '';
            }else{
            	$_applytype = '_'.$_applytype;
            }// end if
            // end add

            $stmt1 = "begin begin pk_erp.p_set_segment_no(:in_company_id1); end; begin pk_erp.p_set_username(:in_user_seqno); end; begin %s%s(pi_seg_segment_no => :in_companyid,pi%s_approve_sz_id => :in_approve_seqno,pi_approve_flag => :in_is_approved,pi_reject_reason=> :in_reject_reason,pi_approve_psn_id=>:in_approver_emp_seqno,%s po_errmsg=>:out_msg,po_success=>:out_issuccess);end;end;";

            $stmt1 = sprintf($stmt1,$_package_name,$_procedure_name,$_applytype,$user_define_wf_params);
            $stmt = $this->_dbConn->PrepareSP($stmt1);//echo $stmt;
            $this->_dbConn->InParameter($stmt,$_workflow['company_id'],'in_company_id1',10);

            $this->_dbConn->InParameter($stmt,$_workflow['approver_user_seqno'],'in_user_seqno',10);
            $this->_dbConn->InParameter($stmt,$_workflow['company_id'],'in_companyid',10);
            $this->_dbConn->InParameter($stmt,$_workflow['approve_seqno'],'in_approve_seqno',10);
            $this->_dbConn->InParameter($stmt,$_workflow['is_approved'],'in_is_approved',10);
            $this->_dbConn->InParameter($stmt,$reject_resaon,'in_reject_reason',1000);
            $this->_dbConn->InParameter($stmt,$_workflow['approver_emp_seqno'],'in_approver_emp_seqno',10);

             // add by dennis 20091118 for user defined workflow
            if (isset($_workflow['wf_flow_table']) && !empty($_workflow['wf_flow_table']))
            {
            	$this->_dbConn->InParameter($stmt,$_workflow['apply_type'],'in_flow_type',100);
            	$this->_dbConn->InParameter($stmt,$_workflow['wf_flow_table'],'in_wf_table',100);
            	$this->_dbConn->InParameter($stmt,$_workflow['tab_key_name'],'in_flow_key',100);
            }// end if
            // end add

            // out parameters
            $this->_dbConn->OutParameter($stmt,$_approve_result['msg'],'out_msg',2000);
            $this->_dbConn->OutParameter($stmt,$_approve_result['is_success'],'out_issuccess',2);
            //$this->_dbConn->debug = true;
            $this->_dbConn->StartTrans();    // begin transaction
            $this->_dbConn->Execute($stmt);
            $this->_dbConn->CompleteTrans(); // end transaction
            //pr($_approve_result);
            //add by boll 经理代理人处理
            // modify by dennis 2012-03-14 
            //只有请假且签核成功才去处理代理人
            if($_workflow['apply_type']       == 'absence' &&
               $_approve_result['is_success'] == 'Y'       &&       	
               !isset($_workflow['wf_flow_table'])){
            		$_approve_result['msg'] .= $this->_setManagerAgent($_workflow);
            }
            return $_approve_result;
        }// end function Approve

        /*
         * //add by boll 经理代理人处理
         *   wf.p_wf_assistant(p_segment_no => :p_segment_no,
                    p_m_id => :p_m_id,        请假人（经理）
                    p_a_id => :p_a_id,　　　　代理人
                    p_a_id1 => :p_a_id1,      代理人
                    p_agent_item => :p_agent_item, 代理项目
                    p_agent_item1 => :p_agent_item1, 代理项目
                    p_begindate => :p_begindate,　　开始时间
                    p_enddate => :p_enddate,　　　　结束时间
                    p_assign_type => :p_assign_type,　　代理类型
                    po_errmsg => :po_errmsg);　　　　　　　
         */ //updated by Gracie at 20090615
        private function _setManagerAgent($_workflow)
        {
        	if(empty($_workflow['workflow_seqno'])) return '';

        	$sql = <<<eof
        		select begin_time,
				       end_time,
				       hours,
				       days,
				       reason,
				       flow_status,
				       emp_seq_no
				  from ehr_leave_in_flow_v
				 where company_id       = :company_id
				   and leave_flow_seqno = :flow_seqno
eof;
        	$row = $this->_dbConn->GetRow($sql,array('company_id'=>$_workflow['company_id'],
        											 'flow_seqno'=>$_workflow['workflow_seqno']));
        	if(empty($row['REASON'])) return '';
        	/* remark by dennis 2010-11-08 暂时先把是否是主管的判断拿掉, 预期按 Mantis 上 #451 要求修改
        	$arr= explode('is_manager="Y"',$row['REASON']);
        	if(count($arr)==1) return '';
        	*/
        	$arr= explode('assign_type="',$row['REASON']);
        	if(count($arr)==1) return '';
        	$arr= explode('"',$arr[1]);
        	$assign_typ=$arr[0];   //echo $assign_typ;

        	$arr= explode('emp_seq_no="',$row['REASON']);
        	if(count($arr)==1) return '';
        	$arr= explode('"',$arr[1]);
        	$emp_seq_no=$arr[0];  //echo $emp_seq_no;

        	$arr= explode('agent_id="',$row['REASON']);
        	if(count($arr)==1) return '';
        	$arr= explode('"',$arr[1]);
        	$assign_seq_no=$arr[0];  //echo $assign_seq_no;

        	//added by Gracie at 20090615
        	$arr= explode('agent_id1="',$row['REASON']);
        	if(count($arr)==1) return '';
        	$arr= explode('"',$arr[1]);
        	$assign_seq_no1=$arr[0];  //echo $assign_seq_no1;

        	$arr= explode('agent_item="',$row['REASON']);
        	if(count($arr)==1) return '';
        	$arr= explode('"',$arr[1]);
        	$agent_item=$arr[0];  //echo $agent_item1;

        	$arr= explode('agent_item1="',$row['REASON']);
        	if(count($arr)==1) return '';
        	$arr= explode('"',$arr[1]);
        	$agent_item1=$arr[0];  //echo $agent_item1;

        	//added end
        	$out_errmsg="";
        	//$stmt = "begin wf.p_wf_assistant(:p_segment_no,:p_m_id,:p_a_id,TO_DATE(:p_begindate,'YYYY-MM-DD HH24:MI:SS'),TO_DATE(:p_enddate,'YYYY-MM-DD HH24:MI:SS'),:p_assign_type,:po_errmsg); end;";
        	$stmt = "begin wf.p_wf_assistant(:p_segment_no,:p_m_id,:p_a_id,:p_a_id1,:p_agent_item,:p_agent_item1,TO_DATE(:p_begindate, 'YYYY-MM-DD HH24:MI:SS'),TO_DATE(:p_enddate, 'YYYY-MM-DD HH24:MI:SS'),:p_assign_type,:p_flow_status,:po_errmsg); end;";//updated by Gracie at 20090615
        	$stmt = $this->_dbConn->PrepareSP($stmt);
        	$this->_dbConn->InParameter($stmt,$_workflow['company_id'],'p_segment_no',10);
        	$this->_dbConn->InParameter($stmt,$emp_seq_no,'p_m_id',10);
        	$this->_dbConn->InParameter($stmt,$assign_seq_no,'p_a_id',10);
        	$this->_dbConn->InParameter($stmt,$assign_seq_no1,'p_a_id1',10);//added by Gracie at 20090615
        	$this->_dbConn->InParameter($stmt,$agent_item,'p_agent_item',10);//added by Gracie at 20090615
        	$this->_dbConn->InParameter($stmt,$agent_item1,'p_agent_item1',10);//added by Gracie at 20090615
        	$this->_dbConn->InParameter($stmt,$row['BEGIN_TIME'],'p_begindate',20);
        	$this->_dbConn->InParameter($stmt,$row['END_TIME'],'p_enddate',20);
        	$this->_dbConn->InParameter($stmt,$assign_typ,'p_assign_type',10);
        	$this->_dbConn->InParameter($stmt,$row['FLOW_STATUS'],'p_flow_status',10);
        	$this->_dbConn->OutParameter($stmt,$out_errmsg,'po_errmsg',2000);

        	//$this->_dbConn->debug = true;
            $this->_dbConn->StartTrans();    // begin transaction
            $this->_dbConn->Execute($stmt);
            $this->_dbConn->CompleteTrans(); // end transaction

        	return $out_errmsg;
        }

        /**
        *   Private Function decrypt code
        *   @param no parameters
        *   @return no return, decrypte key in database
        *   @author: Dennis 2006-04-03 11:18:03
        *   @last update: 2006-04-03 11:18:15 by Dennis
        */
        /* remark by dennis 2013/11/08, 统一放在 AresDB.inc.php
        private function _decrypt()
        {
            // refer to AresDB.inc.php constant DECRYPT_KEY
            $this->_dbConn->Execute('begin dodecrypt(); end;');
        }// end _decrypt()
        */
        /**
        *   Parse serect code (url long code) form mail approve
        *   @param $serect_code string, the encrypt information about workflow
        *   @param $is_batch boolean, batch approve
        *   @param $view_only boolean, view flowchart only
        *   @return array, plain code about workflow information
        *   @author: Dennis 2006-04-03 11:18:03
        *   @last update: 2006-04-28 13:53:33  by Dennis
        */
        public function ParseWorkflowSecretCode($secret_code,$is_batch=false,$view_only = false)
        {
            //$this->_decrypt(); // call private function _decrypte before parse
            $_apply_type = array('A'=>'absence',
            					 'O'=>'overtime',
            					 'C'=>'cancel_absence',
            					 'T'=>'trans',
            					 'N'=>'nocard',
            					 'R'=>'resign',
            					 'U'=>'user_defined');

            $_workflow_info = array('plain_code'=>'',       /* 解析出来的 明码 AY|AN|OY|ON */
                                    'apply_type'=>'',       /* 请假|加班 (absence|overtime) */
                                    'approve_time'=>'',     /* 解析出来的 时间 */
                                    'approver_emp_seqno'=>'',  /* 解析出来的 签核人员工流水号 */
                                    'approve_seqno'=>'',    /* 解析出来的 签核流水号码 */
                                    'workflow_seqno'=>'',   /* 解析出来的 workflow 流水号 */
                                    'company_id'=>'',       /* 解析出来的 公司代码 */
                                    'is_approved'=>'',      /* 解析出来的 是否核准码 Y,N */
                                    'approver_user_seqno'=>''/* 解析出来的 Login User 流水号 */);
            // Get plain code from db
            $sql_str = 'select wf.pks_crypt_sz.decryptc(:serect_code) as plain_code from dual';
            $_plain_code =  $this->_dbConn->GetOne($sql_str,array('serect_code'=>$secret_code));
            //print "<font color=red>$_plain_code</font>";
            // separate explain code
            if (!empty($_plain_code))
            {
                if (!$is_batch && !$view_only){
                    list($_workflow_info['plain_code'],
                         $_workflow_info['approve_time'],
                         $_workflow_info['approver_emp_seqno'],
                         $_workflow_info['approve_seqno'],
                         $_workflow_info['workflow_seqno'],
                         $_workflow_info['company_id'],
                         $_workflow_info['approver_user_seqno']) = explode(';',$_plain_code);
                    $_code = strtoupper($_workflow_info['plain_code']);
                    $_workflow_info['is_approved']= $_code{1}; // Y/N
                }else if($is_batch && !$view_only){
                    list($_workflow_info['plain_code'],
                         $_workflow_info['approve_time'],
                         $_workflow_info['approver_emp_seqno'],
                         $_workflow_info['company_id'],
                         $_workflow_info['approver_user_seqno']) = explode(';',$_plain_code);
                }else if(!$is_batch && $view_only){ // add by dennis 2006-04-28 13:57:07
                	//pr(explode(';',$_plain_code));
                    list($_workflow_info['plain_code'],
                         $_workflow_info['approve_time'],
                         $_workflow_info['approver_emp_seqno'],
                         $_workflow_info['workflow_seqno'],
                         $_workflow_info['company_id'],
                         $_workflow_info['approver_user_seqno']) = explode(';',$_plain_code);
                }// end if
                $_code = strtoupper($_workflow_info['plain_code']);
                $_workflow_info['apply_type'] = $_apply_type[$_code{0}];// absence/overtime
                //pr($_workflow_info);
                return $_workflow_info;
            }else{
                // 私自窜改 approve.php?key=xxx 中的编码,bacthapprove.php?key=xxx
                showMsg('Fatal Error: URL Key Error. Try to Attack Workflow System Failure.','error');
            }// end if
            return null;
        }// end _parseWorkflowSecretCode()
        
        /**
         * Get Approve result list
         * @param string $company_id company id 
         * @param string $workflow_seqno workflow apply sequence number
         * @param string $apply_type workflow type
         * @param string $menu_code the user defined flow menu code
         * @return array the apply information array
         * @author Dennis 2006-04-04 14:24:52
         */
        function GetApproveResultList($company_id,
        							  $workflow_seqno,
        							  $apply_type = 'overtime',
        							  $menu_code = '')
        {
            $this->_checkFlowType($apply_type);
            switch($apply_type)
            {
                case 'absence':
                    $sql_str = <<<eof
                        select b.id_no_sz as emp_id,
                               b.name_sz as emp_name,
                               a.absence_id||'-'||a.absence_name as absence_name,
                               a.begin_time,
                               a.end_time,
                               a.hours,
                               a.days,
                               a.reason,
                               a.flow_status,
                               a.status_name
                          from ehr_leave_in_flow_v a, hr_personnel_base b
                         where a.company_id = b.seg_segment_no
                           and a.emp_seq_no = b.id
                           and b.seg_segment_no = :company_id
                           and a.leave_flow_seqno = :workflow_seqno
eof;
                break;
                case 'overtime':
                    $sql_str = <<<eof
                        select b.id_no_sz as emp_id,
                               b.name_sz as emp_name,
                               a.begin_time,
                               a.end_time,
                               a.overtime_fee_name,
                               a.overtime_type_name,
                               a.hours,
                               a.flow_status,
                               a.status_name
                          from ehr_overtime_in_flow_v a,  hr_personnel_base b
                         where a.company_id = b.seg_segment_no
                           and a.emp_seq_no = b.id
                           and a.company_id = :company_id
                           and a.overtime_flow_seqno = :workflow_seqno
eof;
                break;
                case 'cancel_absence':
                    $sql_str = <<<eof
                        select b.id_no_sz as emp_id,
                               b.name_sz as emp_name,
                               a.absence_id||'-'||a.absence_name as absence_name,
                               a.begin_time,
                               a.end_time,
                               a.hours,
                               a.days,
                               a.reason,
                               a.flow_status,
                               a.status_name
                          from ehr_cancel_leave_in_flow_v a, hr_personnel_base b
                         where a.company_id = b.seg_segment_no
                       	   and a.emp_seq_no = b.id
                           and b.company_id = :company_id
                           and a.leave_flow_seqno = :workflow_seqno
eof;
                break;
                case 'trans':
                    $sql_str = <<<eof
	                      select a.trans_flow_sz_id as trans_flow_seqno,
				                 a.seg_segment_no,
				                 a.psn_id,
				                 b.emp_id,
				                 b.emp_name,
				                 b.dept_id,
				                 b.dept_name,
				                 a.validdate as TRANS_DATE,
				                 pk_personnel_msg.f_transtype_master_no(a.seg_segment_no,a.issuetype) trans_type,
	                             pk_personnel_msg.f_transtype_master_desc(a.seg_segment_no,a.issuetype) trans_name,
	                             pk_department_message.f_dept_msg(a.seg_segment_no,a.newdepartment,a.validdate,'01') segment_no_sz,
	                             pk_department_message.f_dept_msg(a.seg_segment_no,a.newdepartment,a.validdate,'02') new_dept_name,
				                 'trans' as apply_type,
				                 a.issuetype,
				                 a.status,
				                 a.create_date,
				                 decode(a.status,'00','暂存','01','提交','02','流程中','03','核准','04','驳回','05','作废','06','异常') as status_name
				            from hr_trans_flow_sz a,ehr_employee_v b
				           where a.seg_segment_no=b.company_id
				             and a.psn_id=b.emp_seq_no
				             and a.seg_segment_no = :company_id
                             and a.trans_flow_sz_id = :workflow_seqno
eof;
                break;
                case 'nocard':
                    $sql_str = <<<eof
	                      select a.nocard_flow_sz_id as nocard_flow_seqno,
							       a.seg_segment_no,
							       a.psn_id,
							       b.emp_id,
							       b.emp_name,
							       b.dept_id,
							       b.dept_name,
							       a.recordtime as nocard_date,
							       f_nocard_msg(a.seg_segment_no,a.shifttype,'01') as shifttype_name,
							       f_nocard_msg(a.seg_segment_no,a.nocarding_id,'02') as nocarding_name,
							       'nocard' as apply_type,
							       a.status,
							       a.create_date,
							       decode(a.status,'00','暂存','01','提交','02','流程中','03','核准','04','驳回','05','作废','06','异常') as status_name
							  from hr_nocard_flow_sz a,ehr_employee_v b
							 where a.seg_segment_no=b.company_id
							   and a.psn_id=b.emp_seq_no
							   and a.seg_segment_no = :company_id
							   and a.nocard_flow_sz_id = :workflow_seqno

eof;
                break;
				case 'resign':
                    $sql_str = <<<eof
	                      select a.resign_flow_sz_id as resign_flow_seqno,
						       a.seg_segment_no,
						       a.psn_id,
						       b.emp_id,
						       b.emp_name,
						       b.dept_id,
						       b.dept_name,
						       a.apply_date,
						       a.out_type,
						       decode(a.out_type,'1','离职','2','留停','离职') as out_type_name,
						       a.out_date,
						       a.reason,
						       f_codename('RESIGNREASON',a.reason,a.seg_segment_no) as reason_name,
						       'resign' as apply_type,
						       a.status,
						       a.create_date,
						       decode(a.status,'00','暂存','01','提交','02','流程中','03','核准','04','驳回','05','作废','06','异常') as status_name
						  from hr_resign_flow_sz a, ehr_employee_v b
						 where a.seg_segment_no = b.company_id
						   and a.psn_id = b.emp_seq_no
						   and a.seg_segment_no = :company_id
						   and a.resign_flow_sz_id = :workflow_seqno
eof;
                break;
				case 'user_defined':
					$sql_str = <<<eof
						select * from udwf_{$menu_code}_approve_v
						 where  company_id = :company_id
						   and  workflow_seqno = :workflow_seqno
eof;
					break;
                default: break;
            }
            return $this->_dbConn->GetRow($sql_str,
                                          array('company_id'=>$company_id,
                                                'workflow_seqno'=>$workflow_seqno));
        }//end function GetApproveResultList()
        /**
        *   Get Approve result list
        *   @param $company_id string, approver company id
        *   @param $approver_emp_seqno number, the approver emp seq no(psn id)
        *   @param $apply_type string, absence or overtime
        *   @return array, the apply information array
        *   @author: Dennis 2006-04-04 14:24:52
        *   @last update: 2006-04-05 13:14:34  by dennis
        *   @log
        *       1. add column apply_type by dennis 2006-05-16 15:44:18
        *       2. fixed bug 代理人不能进入签核查询不出来的资料问题 2006-05-19 15:42:14 by dennis
        *       3. fixed view flowchart 代理人未显示错误之 issue 2013-02-20 16:21
        *       4. add approved date to flowchart page 2013-02-20 16:21
        *   @notice: 为什么要用 and agency_emp_seqno = '$approver_emp_seqno' 作条件,
        *   而不是 approver_emp_seqno = '$approver_emp_seqno'-> 因为 xx_approve_v 里
        *   的我代理了哪些主管,如果是没有代理人的话, agnecy_emp_seqno 就等于 approver_emp_seqno
        *   2013-01-08 Modify By Dennis
        *   	1. 添加主管可以查詢由“我”簽核的申請單 (優特的需求，納入標準)
        *   2014-09-16 modify by Dennis
        *       1.Tuning Performance, 优化 SQL 查询
        */
        function GetWaitforApproveList($company_id,
        							   $approver_emp_seqno,
        							   $apply_type,
        							   $do_count = false,
        							   $qwhere = '')
        {
			$tab_or_v = ($qwhere == " and can_approve = 'Y'" || $qwhere == '') ? 'tmp' : 'v';  // if get wait approve, from _tmp table, else select data from view , add by Dennis 2014/07/11
        	$qwhere = $qwhere == '' ? " and can_approve = 'Y' " : $qwhere;
        	//echo $qwhere;        	
        	$this->_checkFlowType($apply_type);
        	$emp_name = isset($GLOBALS['config']['staff']['show_ename']) &&  $GLOBALS['config']['staff']['show_ename'] == 'Y' ?
        				'emp_name ||'.'\' \''. '||emp_name_en' :
        				'emp_name';
            //$this->_dbConn->debug  = true;
        	/*and can_approve = 'Y' -- remark by dennis 2013-01-08*/
            switch($apply_type)
            {
                case 'absence':
                    $orderby = $do_count ? '' : ' order by begin_time desc ,dept_id asc ,emp_id asc ';
                    $sqlstr = <<<eof
                        select company_id,
                               approve_seqno,
                               agency_emp_seqno as approver_emp_seqno,
                               workflow_seqno,
                               can_approve,
                               dept_id,
                               dept_name,
                               emp_id,
                               $emp_name,
                               absence_name,
                               can_rest,
                               already_rest,
                               begin_time,
                               end_time,
                               leave_reason,
                               days,
                               hours,
                               flow_status,
                               status_name,
                               '$apply_type' as apply_type,
                               agency_info
                          from ehr_leave_approve_v ela
                         where company_id = :company_id
                           and agency_emp_seqno = :approver_emp_seqno
                           $qwhere
                           and  not exists
                          (select 1 from  ehr_concurrent_request_detail ecrd
                           where ecrd.workflow_seqno=ela.workflow_seqno
                             and ecrd.approver_emp_seqno = :approver_emp_seqno
                             and ecrd.po_success is null)
                           $orderby
eof;
                break;
                case 'overtime':
                    /*and can_approve = 'Y' -- remark by dennis 2013-01-08*/
                    $orderby = $do_count ? '' : ' order by overtime_date desc ,dept_id asc ,emp_id asc ';
                    $sqlstr = <<<eof
                        select company_id,
                               approve_seqno,
                               agency_emp_seqno as approver_emp_seqno,
                               workflow_seqno,
                               dept_id,
                               dept_name1 as dept_name,
                               emp_id,
                               $emp_name,
                               can_approve,
                               overtime_date,
                               begin_time,
                               end_time,
                               hours,
                               reason,
                               overtime_type,
                               overtime_fee,
                               flow_status,
                               status_name,
                               '$apply_type' as apply_type,
                               agency_info
                          from ehr_overtime_approve_v eoa
                         where company_id = :company_id
                           and agency_emp_seqno = :approver_emp_seqno
                           $qwhere
                           and  not exists
                          (select 1 from  ehr_concurrent_request_detail ecrd
                           where ecrd.workflow_seqno = eoa.workflow_seqno
                             and ecrd.approver_emp_seqno =  :approver_emp_seqno
                             and ecrd.po_success is null)
                           $orderby
eof;
                break;
                case 'cancel_absence':
                    $orderby = $do_count ? '' : ' order by begin_time desc ,dept_id asc ,emp_id asc ';
                    $sqlstr = <<<eof
                        select company_id,
                               approve_seqno,
                               agency_emp_seqno as approver_emp_seqno,
                               workflow_seqno,
                               can_approve,
                               dept_id,
                               dept_name,
                               emp_id,
                               $emp_name,
                               absence_name,
                               begin_time,
                               end_time,
                               leave_reason,
                               days,
                               hours,
                               flow_status,
                               status_name,
                               '$apply_type' as apply_type,
                               agency_info
                          from ehr_cancel_leave_approve_v
                         where company_id = :company_id
                           and agency_emp_seqno = :approver_emp_seqno
                           /*and can_approve = 'Y' -- remark by dennis 2013-01-08*/
                            $qwhere
                            $orderby
eof;
                break;
               
                case 'trans':
                    $orderby = $do_count ? '' : ' order by trans_date desc,dept_id asc,emp_id asc ';
                	$sqlstr = <<<eof
                	    select company_id,
						       approve_seqno,
						       agency_emp_seqno as approver_emp_seqno,
						       workflow_seqno,
						       dept_id,
						       emp_id,
						       $emp_name,
						       can_approve,
						       dept_name,
						       trans_date,
						       trans_type,
						       trans_type_no,
						       trans_type_name,
						       new_dept_id,
						       new_dept_no,
						       new_dept_name,
						       new_title,
						       new_jobcategory,
						       new_period,
						       new_costallocation,
						       new_contract,
						       new_nb_newleader,
						       new_transfer_reson,
						       new_overtime_type,
						       new_absence_type,
						       new_year_type,
						       new_job,
						       new_tax,
						       status_name,
						       '$apply_type' as apply_type,
						       agency_info
						  from ehr_trans_approve_v
						 where company_id = :company_id
						   and agency_emp_seqno = :approver_emp_seqno
						   /*and can_approve = 'Y' -- remark by dennis 2013-01-08*/
						   $qwhere
						   $orderby
eof;
                break;
                case 'nocard':
                	/*
                	$sqlstr = <<<eof
                	   select company_id,agency_emp_seqno,
						       approve_seqno,
						       agency_emp_seqno as approver_emp_seqno,
						       workflow_seqno,
						       dept_id,
						       emp_id,
						       $emp_name,
						       can_approve,
						       dept_name1 as dept_name,
						       to_char(recordtime,'yyyy-mm-dd hh24:mi') as nocard_date,
						       to_char(recordtime2,'yyyy-mm-dd hh24:mi') as nocard_date2,
						       to_char(recordtime3,'yyyy-mm-dd hh24:mi') as nocard_date3,
						       to_char(recordtime4,'yyyy-mm-dd hh24:mi') as nocard_date4,
						       to_char(recordtime5,'yyyy-mm-dd hh24:mi') as nocard_date5,
						       to_char(recordtime6,'yyyy-mm-dd hh24:mi') as nocard_date6,
						       shifttype,
						       nocarding_id,
						       shifttype_name,
						       shifttype_name2,
						       shifttype_name3,
						       shifttype_name4,
						       shifttype_name5,
						       shifttype_name6,
                               nocarding_name,
                               nocarding_name2,
                               nocarding_name3,
                               nocarding_name4,
                               nocarding_name5,
                               nocarding_name6,
						       status_name,
						       '$apply_type' as apply_type,
						       agency_info
						  from ehr_nocard_approve_v
						 where company_id = :company_id
						   and agency_emp_seqno = :approver_emp_seqno
						   /*and can_approve = 'Y' -- remark by dennis 2013-01-08
						   $qwhere
						 order by recordtime asc,dept_id asc,emp_id asc
eof;*/
                    $orderby = $do_count ? '' : ' order by recordtime desc,dept_id asc,emp_id asc ';
				    $sqlstr = <<<eof
                	   select company_id,agency_emp_seqno,
						       approve_seqno,
						       agency_emp_seqno as approver_emp_seqno,
						       workflow_seqno,
						       dept_id,
						       emp_id,
						       $emp_name,
						       can_approve,
						       dept_name1 as dept_name,
						       decode(t.shifttype_name,
						              '',
						              '',
						              t.shifttype_name || ' :: ' ||
						              to_char(t.recordtime, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
						              t.nocarding_name||'<hr/>') ||
						       decode(t.shifttype_name2,
						              '',
						              '',
						              t.shifttype_name2 || ' :: ' ||
						              to_char(t.recordtime2, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
						              t.nocarding_name2||'<hr/>') ||
						       decode(t.shifttype_name3,
						              '',
						              '',
						              t.shifttype_name3 || ' :: ' ||
						              to_char(t.recordtime3, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
						              t.nocarding_name3||'<hr/>') ||
						       decode(t.shifttype_name3,
						              '',
						              '',
						              t.shifttype_name4 || ' :: ' ||
						              to_char(t.recordtime4, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
						              t.nocarding_name4||'<hr/>') ||
						       decode(t.shifttype_name5,
						              '',
						              '',
						              t.shifttype_name5 || ' :: ' ||
						              to_char(t.recordtime5, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
						              t.nocarding_name5||'<hr/>') ||
						       decode(t.shifttype_name6,
						              '',
						              '',
						              t.shifttype_name6 || ' :: ' ||
						              to_char(t.recordtime6, 'yyyy/mm/dd hh24:mi') || ' :: ' ||
						              t.nocarding_name6||'<hr/>') as nocard_desc,
						       flow_status,
						       status_name,
						       '$apply_type' as apply_type,
						       emp_remark,
						       agency_info
						  from ehr_nocard_approve_v t
						 where company_id = :company_id
						   and agency_emp_seqno = :approver_emp_seqno
						   /*and can_approve = 'Y' -- remark by dennis 2013-01-08*/
						   $qwhere
						   $orderby
eof;

                break;
				case 'resign':
				    $orderby = $do_count ? '' : ' order by out_date desc,dept_id asc,emp_id asc ';
                	$sqlstr = <<<eof
                	   select company_id,
						       approve_seqno,
						       agency_emp_seqno as approver_emp_seqno,
						       workflow_seqno,
						       dept_id,
						       emp_id,
						       $emp_name,
						       can_approve,
						       dept_name1 as dept_name,
						       apply_date,
						       out_type,
						       out_type_name,
						       out_date,
						       reason,
						       reason_name,
						       flow_status,
						       status_name,
						       emp_remark,
						       mgr_comment,
						       '$apply_type' as apply_type,
						       agency_info
						  from ehr_resign_approve_v
						 where company_id = :company_id
						   and agency_emp_seqno = :approver_emp_seqno
						   /*and can_approve = 'Y' -- remark by dennis 2013-01-08*/
						   $qwhere
						   $orderby
eof;
                break;
                default: break;
            }// end switch
            $params = array('company_id'        =>$company_id,
                            'approver_emp_seqno'=>$approver_emp_seqno);
            if($do_count) return $this->_dbConn->GetOne('select count(*) from ('.$sqlstr.')',$params);
            $this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);

            //分页列表
            /*
            include_once('GridView/Data_Paging.class.php');
			$total_rows = $this->_dbConn->GetOne('select count(*) from ('.$sqlstr.')',$params);
			$page_index = isset($_GET['pageIndex']) && (int)$_GET['pageIndex']>0 ? $_GET['pageIndex']:1;
			$page_size = 200;
			if($total_rows > $page_size){
				$page=new Data_Paging(array('total_rows'=>$total_rows,'page_size'=>$page_size));
		        $page->openAjaxMode('gotoPage');
				$pageDownUp = $page->outputToolbar(2);
				$this->_tpl->assign("pageDownUp", $pageDownUp);
			}else{
				$page_index = 1;
			}
			$rsLimit= $this->_dbConn->SelectLimit($sqlstr,$page_size,$page_size*($page_index-1),$params);
		    $rs=$rsLimit->getArray();
			*/
            $rs=$this->_dbConn->GetArray($sqlstr,$params);
			return $rs;
        }// end function GetWaitforApproveList();

        /**
         * Get 等待签核的清单中的部门清单 (for 查询)
         *
         * @param string $company_id
         * @param string $approver_emp_seqno
         * @param string $apply_type
         * @return array
         * @author Dennis 20090630
         * @lastupdate
         *   1.去掉 can_approve = 'Y', 因为前台加上了已签核的查询。
         */
        public function getDeptList($company_id,
        							$approver_emp_seqno,
        							$apply_type = 'leave')
        {
        	$sql = <<<eof
        		select dept_id,dept_name
        		  from ehr_%s_approve_tmp
        		 where company_id       = :company_id
                   and agency_emp_seqno = :approver_emp_seqno
        	     group by dept_id,dept_name
eof;
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			//$this->_dbConn->debug = true;
			return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECONDS,
			        sprintf($sql,$apply_type),array('company_id'=>$company_id,
													   'approver_emp_seqno'=>$approver_emp_seqno));
        }// end getDeptList()
        
        /**
         * Get leavename list approved by the approve_empseqno
         * @param string $company_id
         * @param string $approver_emp_seqno
         * @return array
         */
        public function getLeaveNameList($company_id,
        							     $approver_emp_seqno)
        {
        	$sql = <<<eof
        		select absence_name,absence_name
        		  from ehr_leave_approve_tmp
        		 where company_id       = :company_id
                   and agency_emp_seqno = :approver_emp_seqno
        	    group by absence_name
eof;
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$company_id,
													   'approver_emp_seqno'=>$approver_emp_seqno));
        }// end getLeaveNameList()
        public function getCancelLeaveNameList($company_id,
        							     $approver_emp_seqno)
        {
        	$sql = <<<eof
        		select absence_name,absence_name
        		  from ehr_cancel_leave_approve_v
        		 where company_id       = :company_id
                   and agency_emp_seqno = :approver_emp_seqno
        	     group by absence_name
eof;
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$company_id,
													   'approver_emp_seqno'=>$approver_emp_seqno));
        }// end getLeaveNameList()
        
        /**
         * “我“签核过的加班类型 ->? 应该抓固定的三个值会比较快
         * @param string $company_id
         * @param string $approver_emp_seqno
         */
        public function getOvertimeTypeList($company_id,
        							        $approver_emp_seqno)
        {
        	$sql = <<<eof
        		select overtime_type,overtime_type
        		  from ehr_overtime_approve_tmp
        		 where company_id       = :company_id
                   and agency_emp_seqno = :approver_emp_seqno
        	   group by overtime_type
eof;
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$company_id,
													   'approver_emp_seqno'=>$approver_emp_seqno));


        }// end getOvertimeTypeList()

        public function getTransTypeList($company_id,
        							     $approver_emp_seqno)
        {
        	$sql = <<<eof
        		select trans_type_no,trans_type_no||trans_type_name trans_type_name
        		  from ehr_trans_approve_v
        		 where company_id       = :company_id
                   and agency_emp_seqno = :approver_emp_seqno
        	   group by trans_type_no,trans_type_name 
eof;
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$company_id,
													   'approver_emp_seqno'=>$approver_emp_seqno));


        }// end getTransTypeList()

        public function getNocardTypeList($company_id,
        							     $approver_emp_seqno)
        {
        	$sql = <<<eof
        		select nocarding_id, codeid || ' ' || reason nocard_reason
			     from hr_nocarding
			    where is_active = 'Y'
			      and seg_segment_no = :company_id
			    order by nocarding_id
eof;
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			return $this->_dbConn->GetArray($sql,array('company_id'=>$company_id,
													   'approver_emp_seqno'=>$approver_emp_seqno));


        }// end getTransTypeList()

		public function getResignTypeList($company_id,
        							     $approver_emp_seqno)
        {
        	$sql = <<<eof
        		select out_type, out_type_name
				  from ehr_resign_approve_v
				 where company_id = :company_id
				   and agency_emp_seqno = :approver_emp_seqno
        	    group by out_type, out_type_name

eof;
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			return $this->_dbConn->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$company_id,
													   'approver_emp_seqno'=>$approver_emp_seqno));


        }// end getTransTypeList()

        /**
         * 取得流程状态
         *
         * @param string $lang_code
         * @return array
         * @author Dennis
         */
        public function getWFStatusList($lang_code)
        {
        	$sql = <<<eof
        		select seq, value
				  from app_muti_lang
				 where program_no = 'ESNA013'
				   and type_code = 'LL'
				   and lang_code = :lang_code
eof;
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			$r = $this->_dbConn->GetArray($sql,array('lang_code'=>$lang_code));
			$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
			return $r;
        }
        
        /**
         * For Improve ESS Home Page Performance, Rewirte the Count logic
         * @param string $flowtype
         * @param string $companyid
         * @param string $empseqno
         * @return number
         * @author Dennis 2014/01/09
         * 
         */
        /*
        public function getWaitApproveCnt($flowtype,$companyid,$empseqno)
        {
            switch ($flowtype)
            {
            	case 'overtime':
            	    $sql = <<<eof
                	    select count(1)
                          from hr_overtime_approve_sz a,
                               hr_overtime_flow_sz b,
                               table(cast(wf.f_get_workflow_agency(a.seg_segment_no,
                                                                   'Y',
                                                                   a.psn_id,
                                                                   a.create_date) as
                                          wf.tab_agency_info)) c
                         where a.seg_segment_no = b.seg_segment_no
                           and a.overtime_flow_sz_id = b.overtime_flow_sz_id
                           and a.signlevel_id = c.signlevel_id(+)
                           and a.psn_id = c.mgr_psn_id(+)
                           and nvl(c.agency_psn_id, a.psn_id) = :emp_seqno
                           and a.seg_segment_no = :company_id
                           and a.can_approve = 'Y'
                           and not exists (select 1
            	                  from ehr_concurrent_request_detail e
                                 where e.workflow_seqno = a.overtime_flow_sz_id
                                   and e.approver_emp_seqno = a.psn_id
                                   and e.po_success is null)
eof;
            break;
            	case 'absence':
            	    $sql = <<<eof
                	    select count(1)
                          from hr_absence_approve_sz a,
                               hr_absence_flow_sz b,
                               table(cast(wf.f_get_workflow_agency(a.seg_segment_no,
                                                                   'Y',
                                                                   a.psn_id,
                                                                   a.create_date) as
                                          wf.tab_agency_info)) c
                         where a.seg_segment_no = b.seg_segment_no
                           and a.absence_flow_sz_id = b.absence_flow_sz_id
                           and a.signlevel_id = c.signlevel_id(+)
                           and a.psn_id = c.mgr_psn_id(+)
                           and nvl(c.agency_psn_id, a.psn_id) = :emp_seqno
                           and a.seg_segment_no = :company_id
                           and a.can_approve = 'Y'
                                	    
eof;
            break;
            	case 'cancel_absence':
            	    $sql = <<<eof
                	    select count(1)
                          from hr_cancel_absence_approve_sz a,
                               hr_cancel_absence_flow_sz b,
                               table(cast(wf.f_get_workflow_agency(a.seg_segment_no,
                                                                   'Y',
                                                                   a.psn_id,
                                                                   a.create_date) as
                                          wf.tab_agency_info)) c
                         where a.seg_segment_no = b.seg_segment_no
                           and a.cancel_absence_flow_sz_id = b.cancel_absence_flow_sz_id
                           and a.signlevel_id = c.signlevel_id(+)
                           and a.psn_id = c.mgr_psn_id(+)
                           and nvl(c.agency_psn_id, a.psn_id) = :emp_seqno
                           and a.seg_segment_no = :company_id
                           and a.can_approve = 'Y'
eof;
            break;
            	case 'nocard':
            	    $sql = <<<eof
                	    select count(1)
                          from hr_nocard_approve_sz a,
                               hr_nocard_flow_sz b,
                               table(cast(wf.f_get_workflow_agency(a.seg_segment_no,
                                                                   'Y',
                                                                   a.psn_id,
                                                                   a.create_date) as
                                          wf.tab_agency_info)) c
                         where a.seg_segment_no = b.seg_segment_no
                           and a.nocard_flow_sz_id = b.nocard_flow_sz_id
                           and a.signlevel_id = c.signlevel_id(+)
                           and a.psn_id = c.mgr_psn_id(+)
                           and nvl(c.agency_psn_id, a.psn_id) = :emp_seqno
                           and a.seg_segment_no = :company_id
                           and a.can_approve = 'Y'
eof;
            	    break;
            	    case 'trans':
            	        $sql = <<<eof
                    	    select count(1)
                              from hr_trans_approve_sz a,
                                   hr_trans_flow_sz b,
                                   table(cast(wf.f_get_workflow_agency(a.seg_segment_no,
                                                                       'Y',
                                                                       a.psn_id,
                                                                       a.create_date) as
                                              wf.tab_agency_info)) c
                             where a.seg_segment_no = b.seg_segment_no
                               and a.trans_flow_sz_id = b.trans_flow_sz_id
                               and a.signlevel_id = c.signlevel_id(+)
                               and a.psn_id = c.mgr_psn_id(+)
                               and nvl(c.agency_psn_id, a.psn_id) = :emp_seqno
                               and a.seg_segment_no = :company_id
                               and a.can_approve = 'Y'  
eof;
            	        break;
            	        case 'resign':
            	            $sql = <<<eof
                    	    select count(1)
                              from hr_resign_approve_sz a,
                                   hr_resign_flow_sz b,
                                   table(cast(wf.f_get_workflow_agency(a.seg_segment_no,
                                                                       'Y',
                                                                       a.psn_id,
                                                                       a.create_date) as
                                              wf.tab_agency_info)) c
                             where a.seg_segment_no = b.seg_segment_no
                               and a.resign_flow_sz_id = b.resign_flow_sz_id
                               and a.signlevel_id = c.signlevel_id(+)
                               and a.psn_id = c.mgr_psn_id(+)
                               and nvl(c.agency_psn_id, a.psn_id) = :emp_seqno
                               and a.seg_segment_no = :company_id
                               and a.can_approve = 'Y'     
eof;
            	            break;
            	default: break;
            }
            return $this->_dbConn->GetOne($sql,array('company_id'=>$companyid,'emp_seqno'=>$empseqno));
        }*/
    }// end class AresWorkflow;