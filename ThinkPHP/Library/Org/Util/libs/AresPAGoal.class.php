<?php
/**
 * Goal Setting Module
 *
 *  员工可以填单的条件
 *    1. 考核期内
 *    2. 未关帐  (reverse5 != 'GN' && resverse5 = 'GIF')  ps: GIF 是生成考核单时 reverse5 栏位的值
 *    3. 主管驳回 (reverse1 = 'GR') 或是核准 (reverse5 = 'GY')
 *  Create By: Dennis
 *  Create Date: 2013-09-10 16:10
 *
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresPA.class.php $
 *  $Id: AresPA.class.php 3524 2013-09-03 03:39:59Z dennis $
 *  $LastChangedDate: 2013-09-03 11:39:59 +0800 (Tue, 03 Sep 2013) $
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3524 $
 \****************************************************************************/

	include 'AresPA.class.php';
	
	class AresPAGoal extends AresPA{
		/**
		 * 考核单状态
		 * @var string
		 */
		const GOAL_FORM_STATUS_TMP 		= 'GIF';
		const GOAL_FORM_STATUS_CONFIRM 	= 'GY';
		const GOAL_FORM_STATUS_LOCKED 	= 'GN';
		const GOAL_FORM_STATUS_SUBMIT   = 'GS';
		const GOAL_FORM_STATUS_REJECT   = 'GR'; // Add by Dennis 2014/01/24
		const GOAL_FORM_STATUS_TMPSAVE  = 'GT'; // Add by Dennis 2014/02/17 增加暂存功能
		
		const CREATE_PROGRAM 			= 'ESS_PA_GOAL';
		
		public function __construct($companyid, $empseqno)
		{
			parent::__construct($companyid, $empseqno);
		}
		
		/**
		 * Check module installed
		 */
		public function isGoalPAInstalled()
		{
			$sql = 'select 1 from user_tables where table_name = upper(:table_name)';
			//$this->_dBConnection->debug = true;
			return $this->_dBConnection->GetOne($sql,array('table_name'=>'HR_EVA_GOAL_MASTER'));
		}
		/**
		 * Get Year Goal Type List
		 * @param no
		 * @return array
		 * @author Dennis 2013/09/12
		 */
		public function getGoalTypeList()
		{
			$sql = <<<eof
			select eva_goal_type_id, eva_type_desc
			  from hr_eva_goal_type
			 where seg_segment_no = :company_id
			   and is_active = 'Y'
			 order by eva_seq
eof;
			$this->_dBConnection->SetFetchMode(ADODB_FETCH_NUM);
			return $this->_dBConnection->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
					array('company_id'=>$this->_companyId));
		}
		
		/**
		 * 考核期间内待设定的目标考核单 (by Employee)
		 * @param no
		 * @return array
		 * @author Dennis 2013/09/29
		 */
		public function getWaitSettingGoalForms()
		{
			$sql = <<<eof
    			select b.evaluation_period_no   as pa_period_id,
    			       b.evaluation_period_desc as pa_period_desc,
    			       b.evaluation_begin_date  as pa_begin_date,
    			       b.evaluation_end_date    as pa_end_date,
    			       a.appraisal_id           as pa_form_seqno,
    				   a.yyyy					as pa_year
    			  from hr_appraisals_base a, hr_evaluation_periods b
    			 where a.seg_segment_no 	  = b.seg_segment_no
    			   and a.evaluation_period_id = b.evaluation_period_id
    			   and a.reverse1 is null
			       and a.reverse5             = :form_status
    			   and a.seg_segment_no 	  = :company_id
    			   and a.psn_id 			  = :emp_seqno
    			   and b.evaluation_end_date  >=  trunc(sysdate)
eof;
			//$this->_dBConnection->debug = 1;
			return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
					'emp_seqno'=>$this->_empSeqNo,'form_status'=>self::GOAL_FORM_STATUS_TMP));
		}
		
		/**
		 * Get wait comment goal setting forms by manager psn id
		 * 只要暂存档有资料就表示有待审核
		 * 初始化考核单和主管暂存 reverse5 的值都为 'GIF' self::GOAL_FORM_STATUS_TMP
		 * a.revserse1 不为空时表示被主管驳回了
		 * @return array
		 * @author Dennis
		 */
		public function getWaitPAGoalForm()
		{
			$sql = <<<eof
				select  b.evaluation_period_no   as pa_period_id,
    	                b.evaluation_period_desc as pa_period_desc,
    	                b.evaluation_begin_date  as pa_begin_date,
    	                b.evaluation_end_date    as pa_end_date,
    	                a.appraisal_id           as pa_form_seqno,
    	                a.yyyy                   as pa_year,
    	                c.id_no_sz               as emp_id,
    					c.id					 as emp_seqno,
    	                c.name_sz                as emp_name,
    	                a.manager_commends       as mgr_comments,
                        to_char(nvl(a.emp_submit_date,a.create_date),'yyyy-mm-dd hh24:mi') as submit_date
				  from hr_appraisals_base    a,
				       hr_evaluation_periods b,
				       hr_personnel_base     c
				 where a.seg_segment_no 		= b.seg_segment_no
				   and a.evaluation_period_id 	= b.evaluation_period_id
				   and a.seg_segment_no 		= c.seg_segment_no
				   and a.psn_id 				= c.id
				   and a.seg_segment_no 		= :company_id
				   and a.manager_id 			= :emp_seqno
			       and a.reverse1               = :form_flow_status
eof;
			//$this->_dBConnection->debug = 1;
			return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
					'emp_seqno'=>$this->_empSeqNo,'form_flow_status'=>self::GOAL_FORM_STATUS_SUBMIT));
			
		}
		
		/**
		 * 
		 * Insert goal master, 只insert 新加的值，已审核过的资料不动
		 * @param array $master_row
		 * @param string $is_tmp
		 * @return mixed
		 */
		private function _insGoalMasterTmp($master_row)
		{
		    $masterid_stmt = $master_row['master_id'] == '' ? ' hr_eva_goal_master_if_s.nextval ' : $master_row['master_id'];
		    unset($master_row['master_id']);
			$master_seqno = 0;
			$sql = <<<eof
				insert into hr_eva_goal_master_if
				  (appraisal_id,
				   master_id,
				   psn_id,
				   seg_segment_no,
				   seq,
				   goal_type,
				   work_goal,
				   percent_goal,
				   status,
				   is_active,
				   create_by,
				   create_date,
				   create_program,
		           is_approved)
				values
				  (:appraisal_id,
				   {$masterid_stmt},
				   :psn_id,
				   :seg_segment_no,
				   :seq,
				   :goal_type,
				   :work_goal,
				   :percent_goal,
				   '1',
				   'Y',
				   :create_by,
				   sysdate,
				   :create_program,:is_approved) returning master_id into :master_seq
eof;
			$sql_stmt = $this->_dBConnection->PrepareSP($sql);
			$this->_dBConnection->OutParameter($sql_stmt,$master_seqno,'master_seq');
			$this->_dBConnection->Execute($sql_stmt,$master_row);
    		
    		return $master_seqno;
		}
		
		/**
		 * 
		 * @param array $master_row
		 * @param string $is_tmp
		 */
		/*
		private function _updGoalMasterTmp($master_row,$is_tmp = 'N')
		{
			$sql = <<<eof
				update hr_eva_goal_master_if
				   set seq            = :seq,
				       goal_type      = :goal_type,
				       work_goal      = :work_goal,
				       percent_goal   = :percent_goal,
				       status         = :status,
				       update_by      = :update_by,
				       update_date    = :update_date,
				       update_program = :update_program
				 where appraisal_id   = :appraisal_id
				   and master_id 	  = :master_id,
				   and psn_id 		  = :psn_id
				   and seg_segment_no = :seg_segment_no
eof;
			return $this->_dBConnection->Execute($sql,$master_row);
		}*/
		/**
		 * 
		 * @param array $detail_row
		 */
		private function _insGoalDetailTmp($detail_row)
		{
			$sql = <<<eof
				insert into hr_eva_goal_detail_if
				  (appraisal_id,
				   master_id,
				   detail_id,
				   psn_id,
				   seg_segment_no,
				   seq,
				   work_goal,
				   percent_goal,
				   complete_date,
				   mgr_psn_id,
				   is_active,
				   create_by,
				   create_date,
				   create_program,
			       remark,is_approved)
				values
				  (:appraisal_id,
				   :master_id,
				   hr_eva_goal_detail_if_s.nextval,
				   :psn_id,
				   :seg_segment_no,
				   :seq,
				   :work_goal,
				   :percent_goal,
				   to_date(:complete_date,'yyyy/mm/dd'),
				   :mgr_psn_id,
				   'Y',
				   :create_by,
				   sysdate,
				   :create_program,
			       :remark,:is_approved)
eof;
			return $this->_dBConnection->Execute($sql,$detail_row);
		}
		
		/**
		 * 
		 * 修改保存时，修改主档里的考核流程中的状态（reverse1,reverse5 是记录的考核单的状态）， GS_员工修改后提交
		 */
		private function _updatePAFormStatus($pa_form_seqno)
		{
		    $sql = <<<eof
		      update hr_appraisals_base
		         set reverse1 = 'GS',
		             emp_submit_date = sysdate
		        where appraisal_id = :pa_form_seqno 
eof;
		    return $this->_dBConnection->Execute($sql,array('pa_form_seqno'=>$pa_form_seqno));
		}
		
		/**
		 * Save Goal Settings (员工初次填写、修改、主管修改均调此功能)
		 * 主管修改时其实可以直接 insert 到正式档，这里借用先 insert 暂存的档方式，就一套逻辑
		 * 如果有 Performance issue 再改进
		 * @param array $master_row
		 * @param array $detail_row
		 * @return boolean
		 * @author Dennis 2014/01/28
		 */
		public function saveGoalSetting($master_row,$detail_row, $is_mgr_modify = 'N')
		{
			//$this->_dBConnection->debug 	= 1;
			$pub_vars['psn_id'] 			= $this->_empSeqNo;
			$pub_vars['seg_segment_no'] 	= $this->_companyId;
			$pub_vars['create_by'] 		    = $_SESSION['user']['user_seq_no'];
			$pub_vars['create_program'] 	= self::CREATE_PROGRAM;
			
			$master_row_cnt = count($master_row);
			$this->_dBConnection->BeginTrans();
			
			// before insert data, delete the old data
			$appraisal_id = $master_row[0]['appraisal_id'];
			
			$this->deleteGoalPAData($appraisal_id);
			
			// 修改考核单流程状态
			if ($is_mgr_modify == 'N'){
			     $this->_updatePAFormStatus($appraisal_id);
			}
			// 先删除，后 insert
			for ($i=0; $i<$master_row_cnt;$i++){
				$masterrow = array_merge($master_row[$i],$pub_vars);
				// insert to master table
				$master_row_seqno = $this->_insGoalMasterTmp($masterrow);
				// insert to detail table
				if ($master_row_seqno >0 ){
				    
					$detai_row_cnt = count($detail_row[$i]);
					//#todo 有时间的时候改成 
					// insert all 
					// into tablename(col1,col2,..) values(v1,v2,..), 
					// into tablename(col1,col2,..) values(v1,v2,..)
					// select * from dual 的方式提升性能
					for($j = 0; $j<$detai_row_cnt; $j++){
						// re-get the column value from array
						$detailrow['appraisal_id'] 	= $detail_row[$i][$j]['appraisal_id'];
						$detailrow['master_id'] 	= $master_row_seqno;
						$detailrow['seq'] 			= $detail_row[$i][$j]['seq'];
						$detailrow['work_goal'] 	= $detail_row[$i][$j]['work_goal'];
						$detailrow['percent_goal'] 	= $detail_row[$i][$j]['percent_goal'];
						$detailrow['complete_date'] = $detail_row[$i][$j]['complete_date'];
						$detailrow['mgr_psn_id'] 	= $detail_row[$i][$j]['mgr_psn_id'];
						$detailrow['remark'] 	    = $detail_row[$i][$j]['remark'];
						$detailrow['is_approved'] 	= $detail_row[$i][$j]['is_approved'];
						
						// merge public vars
						$detailrow = array_merge($detailrow,$pub_vars);
						
						// insert detail temp table
						$r = $this->_insGoalDetailTmp($detailrow,$detailrow);
						if (!$r){
							$this->_dBConnection->RollbackTrans();
							return false;
						}
					}
				}else{
					$this->_dBConnection->RollbackTrans();
					return false;
				}
			}
			$this->_dBConnection->CommitTrans();
			return true;
		}
		
		/**
		 * Delete old data before insert new record or update 
		 * @param number $pa_form_seqno
		 * @return boolean
		 */
		public function deleteGoalPAData($pa_form_seqno)
		{
		    //$this->_dBConnection->debug = 1;
		    //$this->_dBConnection->BeginTrans();
		    $this->_deleteGoalMaster($pa_form_seqno);
		    $this->_deleteGoalDetail($pa_form_seqno);
		    //$this->_dBConnection->CommitTrans();
		    return true;
		}
		
		/**
		 * 
		 * @param number $pa_form_seqno
		 */
		public function getGoalMasterList($pa_form_seqno,$is_tmp = '_if')
		{
			$sql = <<<eof
    			select a.appraisal_id     as pa_form_seqno,
    			       a.master_id        as master_goal_seqno,
    			       a.psn_id           as emp_seqno,
    			       a.seq              as seq,
    			       a.goal_type	      as goal_type_id,
    			       b.eva_type_desc    as goal_type_desc,
    			       a.work_goal        as work_goal,
    			       a.percent_goal     as goal_weight,
    			       a.is_approved      as is_approved
    			  from hr_eva_goal_master{$is_tmp} a, 
    			       hr_eva_goal_type            b,
    			       hr_appraisals_base          c
    			 where a.seg_segment_no = b.seg_segment_no
    			   and a.goal_type 		= b.eva_goal_type_id
    			   and a.appraisal_id   = c.appraisal_id
    			   and a.is_active 		= 'Y'
    			   and a.appraisal_id 	= :pa_form_seqno
    			   and a.seg_segment_no = :company_id
    			 order by seq
eof;
			//$this->_dBConnection->debug = 1;
			return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
					'pa_form_seqno'=>$pa_form_seqno));
		}
		
		/**
		 * 
		 * @param number $pa_form_seqno
		 */
		public function getGoalDetailList($pa_form_seqno,$is_tmp = '_if')
		{
			$sql = <<<eof
    			select appraisal_id as pa_form_seqno,
    			       master_id    as master_goal_seqno,
    			       detail_id	as detail_goal_seqno,
    			       psn_id       as emp_seqno,
    			       seq          as seq,
    			       work_goal    as work_goal,
    			       percent_goal as goal_weight,
    			       mgr_psn_id   as work_owner,
    			       to_char(complete_date, 'YYYY/MM/DD') as archive_date,
    			       replace(remark,'&lt;br&gt;','<br>')       as remark,
    			       is_approved  as is_approved
    			  from hr_eva_goal_detail{$is_tmp}
    			 where is_active = 'Y'
    			   and appraisal_id = :pa_form_seqno
    			   and seg_segment_no = :company_id
    			 order by seq
eof;
			//$this->_dBConnection->debug = 1;
			return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
					'pa_form_seqno'=>$pa_form_seqno));
		}
		
		/**
		 * 更新考核单上的主管备注, 顺便把之前驳回的状态及驳回的备注改为 Null
		 * 
		 * 用 hr_appraisals_base 中的 reverse2 来记录考核单的内容是否被主管修改过
		 * 
		 * @param number $pa_form_seqno
		 * @param number $mgr_comments
		 * @return boolean
		 * @author Dennis
		 */
		private function _updatePAForm($pa_form_seqno,$mgr_comments,$is_tmp_save,$is_mgr_modify)
		{
			$form_status = $is_tmp_save == 1 ? self::GOAL_FORM_STATUS_TMP : self::GOAL_FORM_STATUS_CONFIRM;
			//$this->_dBConnection->debug  = 1;
			$sql = <<<eof
			update hr_appraisals_base
			   set manager_commends = :mgr_comment,
				   reverse5         = :form_status,
			       reverse1         = null,
			       reverse2         = :is_mgr_modify,
			       mgr_appraisal_remark=null,
			       mgr1_submit_date = null,
				   updat_by			= :updateby,
			       updat_date       = sysdate,
			   	   updat_program    = :program_name
			 where appraisal_id 	= :pa_form_seqno
			   and seg_segment_no	= :company_id
			   and manager_id 		= :mgr_psn_id
eof;
			return $this->_dBConnection->Execute($sql,array('mgr_comment'=>$mgr_comments,
					'pa_form_seqno'=>$pa_form_seqno,'company_id'=>$this->_companyId,
					'mgr_psn_id'=>$this->_empSeqNo,'updateby'=>$_SESSION['user']['user_seq_no'],
					'program_name'=>self::CREATE_PROGRAM,'form_status'=>$form_status,
			        'is_mgr_modify'=>$is_mgr_modify
			));
		}
		
		/**
		 * Trans data from tmp to normal, if exists, do update others insert
		 * 
		 * @param number $pa_form_seqno
		 */
		private function _trans2GoalMaster($pa_form_seqno)
		{
			$this->_deleteGoalMaster($pa_form_seqno,'');
			$sql = <<<eof
				insert into hr_eva_goal_master
                  (appraisal_id,
                   master_id,
                   psn_id,
                   seg_segment_no,
                   seq,
                   goal_type,
                   work_goal,
                   percent_goal,
                   status,
                   is_active,
                   create_by,
                   create_date,
                   create_program,
                   update_by,
                   update_date,
                   update_program,
			       is_approved)
                  select appraisal_id,
                         master_id,
                         psn_id,
                         seg_segment_no,
                         seq,
                         goal_type,
                         work_goal,
                         percent_goal,
                         status,
                         is_active,
                         create_by,
                         create_date,
                         create_program,
                         update_by,
                         update_date,
                         update_program,
			             'Y'
				    from hr_eva_goal_master_if
				   where seg_segment_no = :company_id
				     and appraisal_id = :pa_form_seqno
eof;
			return $this->_dBConnection->Execute($sql,array('company_id'=>$this->_companyId,
					'pa_form_seqno'=>$pa_form_seqno));
			/*
			$sql = <<<eof
				merge into hr_eva_goal_master ha
				using (select appraisal_id,
				              master_id,
				              psn_id,
				              seg_segment_no,
				              seq,
				              goal_type,
				              work_goal,
				              percent_goal,
				              status,
				              is_active,
				              create_by,
				              create_date,
				              create_program,
				              update_by,
				              update_date,
				              update_program
				         from hr_eva_goal_master_if
				        where appraisal_id = :pa_form_seqno
				          and seg_segment_no = :company_id) hb
				on (ha.appraisal_id = hb.appraisal_id and ha.master_id = hb.master_id)
				when matched then
				  update
				     set ha.seq          = hb.seq,
				         ha.goal_type    = hb.goal_type,
				         ha.work_goal    = hb.work_goal,
				         ha.percent_goal = hb.percent_goal
				   where ha.appraisal_id = hb.appraisal_id
				     and ha.master_id = hb.master_id
				     and ha.seg_segment_no = hb.seg_segment_no
				when not matched then
				  insert
				    (appraisal_id,
				     master_id,
				     psn_id,
				     seg_segment_no,
				     seq,
				     goal_type,
				     work_goal,
				     percent_goal,
				     status,
				     is_active,
				     create_by,
				     create_date,
				     create_program,
				     update_by,
				     update_date,
				     update_program)
				  values
				    (hb.appraisal_id,
				     hb.master_id,
				     hb.psn_id,
				     hb.seg_segment_no,
				     hb.seq,
				     hb.goal_type,
				     hb.work_goal,
				     hb.percent_goal,
				     hb.status,
				     hb.is_active,
				     hb.create_by,
				     hb.create_date,
				     hb.create_program,
				     hb.update_by,
				     hb.update_date,
				     hb.update_program)
eof;
			return $this->_dBConnection->Execute($sql,array('pa_form_seqno'=>$pa_form_seqno,
					'company_id'=>$this->_companyId));
					*/
		}
		
		/**
		 * Trans data from tmp to normal, if exists, do update others insert
		 *
		 * @param number $pa_form_seqno
		 */
		private function _trans2GoalDetail($pa_form_seqno)
		{
			$this->_deleteGoalDetail($pa_form_seqno,'');
			$sql = <<<eof
				insert into hr_eva_goal_detail(appraisal_id,
				              master_id,
				              detail_id,
				              psn_id,
				              seg_segment_no,
				              seq,
				              work_goal,
				              percent_goal,
				              complete_date,
				              mgr_psn_id,
				              is_active,
				              create_by,
				              create_date,
				              create_program,
				              update_by,
				              update_date,
				              update_program,
			                  is_approved,
			                  remark)
				       select appraisal_id,
				              master_id,
				              detail_id,
				              psn_id,
				              seg_segment_no,
				              seq,
				              work_goal,
				              percent_goal,
				              complete_date,
				              mgr_psn_id,
				              is_active,
				              create_by,
				              create_date,
				              create_program,
				              update_by,
				              update_date,
				              update_program,
			                  'Y',
			                  decode(remark,null,null,remark ||' @'||to_char(sysdate,'yyyy/mm/dd hh24:mi:ss')||'<br>')
				    from hr_eva_goal_detail_if
				   where seg_segment_no = :company_id
				     and appraisal_id = :pa_form_seqno
eof;
			return $this->_dBConnection->Execute($sql,array('company_id'=>$this->_companyId,
					'pa_form_seqno'=>$pa_form_seqno));
			/* 
			$sql = <<<eof
			merge into hr_eva_goal_detail ha
				using (select appraisal_id,
				              master_id,
				              detail_id,
				              psn_id,
				              seg_segment_no,
				              seq,
				              work_goal,
				              percent_goal,
				              complete_date,
				              mgr_psn_id,
				              is_active,
				              create_by,
				              create_date,
				              create_program,
				              update_by,
				              update_date,
				              update_program
				         from hr_eva_goal_detail_if a
				        where appraisal_id = :pa_form_seqno
				          and seg_segment_no = :company_id) hb
				on (ha.appraisal_id = hb.appraisal_id and 
					ha.master_id = hb.master_id       and 
					ha.detail_id = hb.detail_id)
				when matched then
				  update
				     set ha.seq           = hb.seq,
				         ha.work_goal     = hb.work_goal,
				         ha.percent_goal  = hb.percent_goal,
				         ha.complete_date = hb.complete_date,
				         ha.mgr_psn_id    = hb.mgr_psn_id
				   where ha.appraisal_id = hb.appraisal_id
				     and ha.master_id = hb.master_id
				     and ha.seg_segment_no = hb.seg_segment_no
				     and ha.detail_id = hb.detail_id
				when not matched then
				  insert
				    (appraisal_id,
				     master_id,
				     detail_id,
				     psn_id,
				     seg_segment_no,
				     seq,
				     work_goal,
				     percent_goal,
				     complete_date,
				     mgr_psn_id,
				     is_active,
				     create_by,
				     create_date,
				     create_program,
				     update_by,
				     update_date,
				     update_program)
				  values
				    (hb.appraisal_id,
				     hb.master_id,
				     hb.detail_id,
				     hb.psn_id,
				     hb.seg_segment_no,
				     hb.seq,
				     hb.work_goal,
				     hb.percent_goal,
				     hb.complete_date,
				     hb.mgr_psn_id,
				     hb.is_active,
				     hb.create_by,
				     hb.create_date,
				     hb.create_program,
				     hb.update_by,
				     hb.update_date,
				     hb.update_program)
eof;
			return $this->_dBConnection->Execute($sql,array('pa_form_seqno'=>$pa_form_seqno,
					'company_id'=>$this->_companyId));*/
		}
		
		private function _deleteGoalMaster($pa_form_seqno,$is_tmp = '_if')
		{
			$sql = <<<eof
				delete from hr_eva_goal_master{$is_tmp} 
				 where appraisal_id = :pa_form_seqno 
				   and seg_segment_no = :company_id
eof;
			return $this->_dBConnection->Execute($sql,array('pa_form_seqno'=>$pa_form_seqno,
					'company_id'=>$this->_companyId));
		}
		
		private function _deleteGoalDetail($pa_form_seqno,$is_tmp = '_if')
		{
			$sql = <<<eof
				delete from hr_eva_goal_detail{$is_tmp} 
				 where appraisal_id = :pa_form_seqno 
				   and seg_segment_no = :company_id
eof;
			return $this->_dBConnection->Execute($sql,array('pa_form_seqno'=>$pa_form_seqno,
					'company_id'=>$this->_companyId));
		}
		
		/**
		 * 保存资料到正式考核单中，删除暂存档的资料
		 * @param number $pa_form_seqno
		 * @param number $mgr_comments
		 * @param number $tmp_save
		 * @return boolean
		 * @author Dennis 2013/09/29
		 */
		public function approveGoalSetting($pa_form_seqno,$mgr_comments,$tmp_save = 0,$is_mgr_modify = 'N')
		{
			//$this->_dBConnection-> = 1;
			$this->_dBConnection->BeginTrans();
			$r = $this->_updatePAForm($pa_form_seqno, $mgr_comments,$tmp_save,$is_mgr_modify);
			if ($r){
				$r = $this->_trans2GoalMaster($pa_form_seqno);
				if ($r){
					$r = $this->_trans2GoalDetail($pa_form_seqno);
					if ($r){
						$r = $this->_deleteGoalMaster($pa_form_seqno);
						if ($r){
							$r = $this->_deleteGoalDetail($pa_form_seqno);
							if($r) {
								$this->_dBConnection->CommitTrans();
								return true;
							}else{ 
								$this->_dBConnection->RollbackTrans();
								return false;
							}
						}else{
							$this->_dBConnection->RollbackTrans();
							return false;
						}
					}else{
						$this->_dBConnection->RollbackTrans();
						return false;
					}
				}else{
					$this->_dBConnection->RollbackTrans();
					return false;
				}
			}else{
				$this->_dBConnection->RollbackTrans();
				return false;
			}
		}
		
		/**
		 * 修改目标设定时
		 * 1.主管已驳回或是核准 (reverse5 = 'GY' or resverse1 = 'GR')
		 * 2.修改后提交的状态不能修改
		 * @param number $pa_form_seqno
		 * @return array
		 */
		public function GetExistsGoalSetting()
		{
			//$this->_dBConnection->debug = 1;
		    $status_locked = self::GOAL_FORM_STATUS_LOCKED;
		    $status_tmp = self::GOAL_FORM_STATUS_TMP;
		    $status_confirm =  self::GOAL_FORM_STATUS_CONFIRM;
		    $status_reject = self::GOAL_FORM_STATUS_REJECT;
		    $status_submit = self::GOAL_FORM_STATUS_SUBMIT;
			$sql = <<<eof
			select b.evaluation_period_no   as pa_period_id,
                   b.evaluation_period_desc as pa_period_desc,
                   b.evaluation_begin_date  as pa_begin_date,
                   b.evaluation_end_date    as pa_end_date,
                   a.appraisal_id           as pa_form_seqno,
                   to_char(a.updat_date,'yyyy/mm/dd hh24:mi:ss') as mgr_approve_date,
                   a.yyyy                   as pa_year,
                   nvl(mgr_appraisal_remark,a.manager_commends) as mgr_comment,
                   a.reverse2               as is_mgr_modify,
                   decode(nvl(a.reverse1,a.reverse5),'{$status_locked}','关账','{$status_submit}','已送出',
                   '{$status_tmp}','暂存','{$status_confirm}','已审核',
                   '{$status_reject}','驳回','未知状态') as form_status
			  from hr_appraisals_base a, hr_evaluation_periods b
			 where a.seg_segment_no         = b.seg_segment_no
			   and a.evaluation_period_id   = b.evaluation_period_id
			   and b.evaluation_end_date    >= trunc(sysdate)
			   and (a.reverse5 				= :status_approved 
                 or a.reverse1              = :status_rejected)
			   and a.seg_segment_no 		= :company_id
			   and a.psn_id 				= :emp_seqno
			   and nvl(a.reverse1,'*')      != :status_flow_submit
eof;
			//$this->_dBConnection->debug = 1;
			return $this->_dBConnection->GetArray($sql,array('company_id'=>$this->_companyId,
					'status_approved'=>self::GOAL_FORM_STATUS_CONFIRM,
			        'status_rejected'=>self::GOAL_FORM_STATUS_REJECT,
			        'status_flow_submit'=>self::GOAL_FORM_STATUS_SUBMIT,
			        'emp_seqno'=>$this->_empSeqNo));
		}
		
		/**
		 * 修改目标时判断挑资料的 table,
		 * 如果暂存档有资料(转入正式档后暂存档就删除了)就以暂存为主
		 * 否则就去正式档
		 * @param unknown $pa_form_seqno
		 */
		public function getDataSourceFlag($pa_form_seqno)
		{
			$sql = <<<eof
				select count(*) 
				  from hr_eva_goal_master_if 
				 where appraisal_id = :pa_form_seqno
			       and seg_segment_no = :company_id
				   and psn_id = :emp_seqno
eof;
			return $this->_dBConnection->GetOne($sql,array('pa_form_seqno'=>$pa_form_seqno,
					'company_id'=>$this->_companyId,'emp_seqno'=>$this->_empSeqNo));
		}
		
		/**
		 * Get Employee Profile
		 * add by Dennis 2014/01/23
		 * @param number $pa_form_seqno
		 * @return array
		 */
		public function getEmpInfo($pa_form_seqno)
		{
		    $sql = <<<eof
        	    select b.id_no_sz as pa_emp_id,
                       b.name_sz as pa_emp_name,
                       a.job_date as join_date,
                       a.jobplan_date as job_date,
                       c.evaluation_period_no as pa_period_id,
                       c.evaluation_period_desc as pa_period_desc,
                       c.eva_year as pa_year,
			           nvl(a.mgr_appraisal_remark,a.manager_commends) as mgr_comment,
                       pk_personnel_msg.f_title_msg(b.seg_segment_no, b.title, '01') as title_id,
                       pk_personnel_msg.f_title_msg(b.seg_segment_no, b.title, '02') as title_desc,
                       pk_department_message.f_dept_msg(b.seg_segment_no,
                                                        b.seg_segment_no_department,
                                                        sysdate,
                                                        '01') as dept_id,
                       pk_department_message.f_dept_msg(b.seg_segment_no,
                                                        b.seg_segment_no_department,
                                                        sysdate,
                                                        '02') as dept_name
                  from hr_appraisals_base a, hr_personnel_base b, hr_evaluation_periods c
                 where a.seg_segment_no = b.seg_segment_no
                   and a.psn_id = b.id
                   and a.seg_segment_no = c.seg_segment_no
                   and a.evaluation_period_id = c.evaluation_period_id
                   and appraisal_id = :pa_form_seqno
                   and a.seg_segment_no = :company_id
eof;
		    //$this->_dBConnection->debug = true;
		    $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		    return $this->_dBConnection->GetRow($sql,array('pa_form_seqno'=>$pa_form_seqno,
		            'company_id'=>$this->_companyId));
		}
		
		/**
		 * 借用考核主档保存
		 * @param number $pa_form_seqno
		 * @param string $reject_reason
		 * @return number
		 */
		public function rejectGoalSetting($pa_form_seqno,$reject_reason)
		{
		    $sql = <<<eof
		     update hr_appraisals_base
               set mgr_appraisal_remark = :reject_reason,
                   reverse1             = :reject_status,
                   mgr1_submit_date     = sysdate
             where appraisal_id = :pa_form_seqno
eof;
		    //$this->_dBConnection->debug = 1;
		    return $this->_dBConnection->Execute($sql,array('reject_reason'=>$reject_reason,
					'pa_form_seqno'=>$pa_form_seqno,'reject_status'=>self::GOAL_FORM_STATUS_REJECT));
		}
		
		/**
		 * 目标考核考核期间清单
		 * 因为无标识考核单是目标考核，所以串到了 hr_eva_goal_master table
		 * @return string
		 */
		public function getPaGoalPeriod()
		{
		    $sql = <<<eof
		    select distinct a.evaluation_period_id, a.evaluation_period_desc
              from hr_evaluation_periods a, hr_appraisals_base b, hr_eva_goal_master c
             where a.seg_segment_no = b.seg_segment_no
               and a.evaluation_period_id = b.evaluation_period_id
               and b.seg_segment_no = c.seg_segment_no
               and b.appraisal_id = c.appraisal_id
		       and a.seg_segment_no = '{$this->_companyId}'
		       and b.psn_id = '{$this->_empSeqNo}'
eof;
		    return $sql;
		    //return $this->_dBConnection->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,array('company_id'=>$this->_companyId));
		}
		
		/**
		 * 取得主管审核后的目标考核资料
		 */
		public function getApprovedGoalPA($year = '',$period_seqno = '')
		{
		    $where = $year == '' ? '' : " and a.eva_year = $year";
		    $where .= $period_seqno == '' ? '' : " and a.evaluation_period_id = $period_seqno";
		    $sql = <<<eof
		    select b.appraisal_id           as pa_form_seqno,
		           a.eva_year               as pa_year,
                   a.evaluation_period_no   as period_id,
                   a.evaluation_period_desc as period_desc,
                   a.evaluation_begin_date  as pa_begin_date,
                   a.evaluation_end_date    as pa_end_date
              from hr_evaluation_periods a, 
		           hr_appraisals_base    b
             where a.seg_segment_no = b.seg_segment_no
               and a.evaluation_period_id = b.evaluation_period_id
               and exists (select 1
				  from hr_eva_goal_master c
				 where b.appraisal_id = c.appraisal_id
				   and b.seg_segment_no = c.seg_segment_no)
               and b.seg_segment_no = :company_id
               and b.psn_id         = :emp_seqno
		       and b.reverse5       = 'GY'
		       $where
eof;
		    //$this->_dBConnection->debug = 1;
		    $this->_dBConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		    return $this->_dBConnection->CacheGetArray(self::DATA_CACHE_SECONDS,$sql,
		            array('company_id'=>$this->_companyId,'emp_seqno'=>$this->_empSeqNo));
		}
	}