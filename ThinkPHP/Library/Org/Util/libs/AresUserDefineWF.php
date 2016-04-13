<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) ARES International Inc.
 *	Description:
 *     使用者自定义 workflow
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresUserDefineWF.php $
 *  $Id: AresUserDefineWF.php 3841 2014-09-17 08:18:33Z dennis $
 *  $Rev: 3841 $ 
 *  $Date: 2014-09-17 16:18:33 +0800 (周三, 17 九月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-09-17 16:18:33 +0800 (周三, 17 九月 2014) $
 \****************************************************************************/

class AresUserDefineWF{
	
	private $_companyId;
	private $_userSeqNo;
	private $_dbConn;
	private $_langCode;
	private $_errorMsg;
	
	private static $_tablePrefix 			= 'udwf_'; 
	private static $_multi_lang_key 		= 'HR_FLOW_APPLY_SUBTYPE_V.SUB_TYPE';
	private static $_multi_lang_program_no	= 'HRPB301';
	private static $_myself 				= 'myself';
	private static $_assistant 				= 'assistant';
	private static $_admin 					= 'admin';
	
	
	/**
	 * Error Message Code Define
	 * @var string
	 */
	const WF_ERR_NO_COL_DEFINED			= 'W01';
	const WF_ERR_COL_CHANGED 			= 'W02';
	const WF_ERR_TAB_CREATE_FAILURE		= 'W03';
	const WF_ERR_VIEW_CREATE_FAILURE	= 'W04';
	const WF_ERR_SEQ_CREATE_FAILURE		= 'W05';
	const WF_ERR_COL_NAME_UNDEFINE		= 'W06';
	const WF_ERR_COL_LEN_UNDEFINE		= 'W07';
	const WF_ERR_DATA_SOURCE_T_UNDEFINE	= 'W08';
	const WF_ERR_DATA_SOURCE_UNDEFINE	= 'W09';
	const WF_ERR_STATIC_DATA_FORMAT		= 'W10';
	const WF_ERR_DYNAMIC_SQL_VAR		= 'W11';
	const WF_ERR_COL_SEQ_UNDEFINE		= 'W12';
	
	/**
	 *  File Upload Error Message Code
	 * @var string
	 */
	const WF_ERR_FILE_DEL_FAILURE		= 'F01';
	const WF_ERR_UPLOAD_FAILURE 		= 'F02';
	const WF_ERR_UPLOAD_DUPLICATE 		= 'F03';
	const WF_ERR_NOT_ALLOWED_TYPE		= 'F04';
	const WF_ERR_FILE_TOO_BIG 			= 'F05';
	const WF_ERR_FILE_UPLOD_PART		= 'F06';
	const WF_ERR_ALLOWED_TYPE			= 'F07';
	const WF_ERR_SUBMIT_FAILURE			= 'F08';
	
	/**
	 * 申请单提交相关 Message
	 * @var string
	 */
	const WF_MSG_MUST_BE_INPUT			= 'M01';
	const WF_MSG_MUST_BE_NUMBER			= 'M02';
	const WF_MSG_MUST_BE_INT			= 'M03';
	const WF_MSG_SUBMIT_SUCCESS			= 'M04';
	const WF_MSG_SUBMIT_FAILURE			= 'M05';
	const WF_MSG_SAVE_SUCCESS			= 'M06';
	const WF_MSG_SAVE_FAILURE			= 'M07';
	const WF_MSG_DEL_SUCCESS			= 'M08';
	const WF_MSG_DEL_FAILURE			= 'M09';
	
	/**
	 * 申请单设定相关 message
	 * @var string
	 */
	const WF_MSG_APPID_REQUIRED			= 'M10';
	const WF_MSG_MULTI_LANG_REQUIRED	= 'M11';
	const WF_MSG_UPDATE_SUCCESS			= 'M12';
	const WF_MSG_UPDATE_FAILURE			= 'M13';
	const WF_MSG_INSERT_SUCCESS			= 'M14';
	const WF_MSG_INSERT_FAILURE			= 'M15';
	const WF_MSG_DELETE_SUCCESS			= 'M16';
	const WF_MSG_DELETE_FAILURE			= 'M17';
	
	
	/**
	 * Constructor of class AresUserDefineWF
	 *
	 * @param string $company_id
	 * @param string $user_seqno
	 */
	public function __construct($company_id,$user_seqno)
	{
		global $g_db_sql;
		$this->_companyId = $company_id;
		$this->_userSeqNo = $user_seqno;
		$this->_langCode  = $GLOBALS['config']['default_lang'];
		$this->_dbConn    = $g_db_sql;
		//$this->_dbConn->debug =1;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->_errorMsg = $this->_getErrorMsg();
		
	}// end __construct()
	
	/**
	 * 取得主档设定
	 *
	 * @param string $menu_code 菜单代码, default ''
	 * @return array
	 * @author Dennis
	 */
	public function getMasterDefine($menu_code = '')
	{
		//$this->_dbConn->debug = 1;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$where = empty($menu_code) ? '' : ' and a.menu_code = \''.$menu_code.'\'';
		$w = array('company_id'=>$this->_companyId,
				   'lang_code'=>$this->_langCode);
		$sql = <<<eof
			select a.menu_code,
			       b.value as menu_desc,
			       a.layout_cols,
			       a.tmp_save_allowed,
			       a.apply_rules_desc,
			       a.flow_type_desc,
			       c.choice_type as flow_type_code,
			       c.hsl_seq as flow_type_seqno
			  from ehr_wf_define_master a, 
			       app_muti_lang        b, 
			       hr_signlevel         c
			 where a.flow_type_seqno = c.hsl_seq
			   and a.menu_code       = b.name
			   and b.program_no      = 'HCP'
			   and b.lang_code       = :lang_code
			   and b.type_code       = 'MT'
			   and a.company_id      = :company_id
			 $where
eof;
		if (empty($menu_code))
		{
			return $this->_dbConn->GetArray($sql,$w);	
		}// end if
		return $this->_dbConn->GetRow($sql,$w);
	}// end getWFDefineList()
	
	/**
	 * 新增自定义 workflow
	 *
	 * @param string 	$menu_code
	 * @param string	$flow_type_code
	 * @param number 	$layout_cols
	 * @param boolean 	$tmp_save
	 * @param string 	$rule_desc
	 * @return boolean
	 * @author Dennis
	 * @last update 2010-01-29 by dennis
	 */
	public function insertMasterDefine($menu_code,
									   $flow_type_code,
									   $approve_type_multi_lang,
									   $layout_cols = 1,
									   $tmp_save = false,
									   $rule_desc ='')
	{
		//$this->_dbConn->debug = 1;
		$this->_dbConn->BeginTrans();
		$this->_insertWorkflowType($flow_type_code);
		if ($this->_dbConn->Affected_Rows()== 1)
		{
			$sql = <<<eof
			insert into ehr_wf_define_master
					  (menu_code,
					   layout_cols,
					   tmp_save_allowed,
					   apply_rules_desc,
					   create_date,
					   create_app,
					   create_by,
					   company_id,
					   flow_type_seqno)
					values
					  (:menu_code,
					   :layout_cols,
					   :tmp_save_allowed,
					   :apply_rules_desc,
					   sysdate,
					   :create_app,
					   :create_by,
					   :company_id,
					   hr_signlevel_hsl_seq.currval)
eof;
			$this->_dbConn->Execute($sql,array('menu_code'=>strtoupper($menu_code),
											   'layout_cols'=>$layout_cols,
											   'tmp_save_allowed'=>$tmp_save,
											   'apply_rules_desc'=>$rule_desc,
											   'create_app'=>'ESNW001',
											   'create_by'=>$this->_userSeqNo,
											   'company_id'=>$this->_companyId));
			if ($this->_dbConn->Affected_Rows() == 1 )
			{
				foreach ($approve_type_multi_lang as $langcode=>$langtxt)
				{
					$this->_insertMultiLang($menu_code,$langcode,$flow_type_code,$langtxt);
				}
				if ($this->_dbConn->Affected_Rows() >= 1)
				{
					$this->_dbConn->CommitTrans();
					return true;
				}else{
					$error_msg = $this->_dbConn->ErrorMsg();
					$this->_dbConn->RollbackTrans();
					return $error_msg;
				}// end if
			}else{
				$error_msg = $this->_dbConn->ErrorMsg();
				$this->_dbConn->RollbackTrans();
				return $error_msg;
			}
		}else{
			$error_msg = $this->_dbConn->ErrorMsg();
			$this->_dbConn->RollbackTrans();
			return $error_msg;
		}
	}// end insertWFDefine()
	
	/**
	 * update workflow define master file
	 *
	 * @param string $menu_code
	 * @param int	 $flow_type_seqno
	 * @param string $flow_type_code
	 * @param number $layout_cols
	 * @param boolean $tmp_save
	 * @param string $rule_desc
	 * @return boolean
	 * @author Dennis 20090925 
	 * last update 2010-01-29 by dennis
	 */
	public function updateMasterDefine($menu_code,
									   $flow_type_seqno,
									   $flow_type_code,
									   $approve_type_multi_lang,
									   $layout_cols,
									   $tmp_save,
									   $rule_desc)
	{
		//$this->_dbConn->debug = 1;
		$this->_dbConn->BeginTrans();
		$r = $this->_updateWorkflowType($flow_type_seqno,$flow_type_code);
		//var_dump($r);
		if($r){
			$sql = <<<eof
				update ehr_wf_define_master
				   set layout_cols      = :layout_cols,
				       tmp_save_allowed = :tmp_save_allowed,
				       apply_rules_desc = :apply_rules_desc,       
				       update_date      = sysdate,
				       update_by        = :update_by,
				       update_app       = 'ESNW001'
				 where menu_code  = :menu_code
				   and company_id = :company_id
eof;
			$this->_dbConn->Execute($sql,array('menu_code'=>$menu_code,
											 'layout_cols'=>$layout_cols,
											 'tmp_save_allowed'=>$tmp_save,
											 'apply_rules_desc'=>$rule_desc,
											 'update_by'=>$this->_userSeqNo,
											 'company_id'=>$this->_companyId));
			if ($this->_dbConn->Affected_Rows() == 1)
			{
				foreach ($approve_type_multi_lang as $langcode=>$langtxt)
				{
					$this->_insertMultiLang($menu_code,$langcode,$flow_type_code,$langtxt);
				}
				if ($this->_dbConn->Affected_Rows() >= 1)
				{
					$this->_dbConn->CommitTrans();
					return true;
				}else{
					$error_msg = $this->_dbConn->ErrorMsg();
					$this->_dbConn->RollbackTrans();
					return $error_msg;
				}
			}else{
				$error_msg = $this->_dbConn->ErrorMsg();
				$this->_dbConn->RollbackTrans();
				return $error_msg;
			}
		}else{
			$error_msg = $this->_dbConn->ErrorMsg();
			$this->_dbConn->RollbackTrans();
			return $error_msg;
		}
	}// end updateWFDefine()
	
	/**
	 *  update approve level type
	 *
	 * @param int $flow_type_seqno
	 * @param string $flow_type_code
	 * @return boolean
	 * @author Dennis 20090925
	 */
	private function _updateWorkflowType($flow_type_seqno,$flow_type_code)
	{
		$sql = <<<eof
			update hr_signlevel 
			   set choice_type = :flow_type_code
			 where hsl_seq     = :flow_type_seqno
eof;
		return $this->_dbConn->Execute($sql,array('flow_type_seqno'=>$flow_type_seqno,
												  'flow_type_code'=>$flow_type_code));
	}
	
	/**
	 * Delete user defined workflow master/detail and workflow approve level type
	 *
	 * @param string $menu_code
	 * @param int    $flow_type_seqno
	 * @param string $workflow_type_code
	 * @return boolean
	 * @author Dennis 20091102
	 */
	public function deleteDefine($menu_code,
								$flow_type_seqno,
								$workflow_type_code)
	{
		//$this->_dbConn->debug =1;
		$this->_dbConn->BeginTrans();
		$r = $this->_deleteWorkflowType($flow_type_seqno);
		if($r == 1)
		{
			$r = $this->_deleteDetailDefine($menu_code);
			// >=0 是因为有可能表单资料没有建
			if ($r)
			{
				// 删除设定主档
				$this->_deleteMasterDefine($menu_code);
				if ($this->_dbConn->Affected_Rows() == 1)
				{
					// delete 签核类型多语
					$r = $this->_deleteApproveTypeMultiLang($workflow_type_code);
					if ($r>=0)
					{
						if($this->_deleteApproveData($workflow_type_code)>=0)
						{
							if ($this->dropSchema($menu_code))
							{
								$this->_dbConn->CommitTrans();
								return true;
							}else{
								$error_msg = $this->_dbConn->ErrorMsg();
								$this->_dbConn->RollbackTrans();
								return $error_msg;
							}
						}else{
							$error_msg = $this->_dbConn->ErrorMsg();
							$this->_dbConn->RollbackTrans();
							return $error_msg;
						}
					}else{
						$error_msg = $this->_dbConn->ErrorMsg();
						$this->_dbConn->RollbackTrans();
						return $error_msg;
					}
				}else{
					$error_msg = $this->_dbConn->ErrorMsg();
					$this->_dbConn->RollbackTrans();
					return $error_msg;
				}
			}else{
				$error_msg = $this->_dbConn->ErrorMsg();
				$this->_dbConn->RollbackTrans();
				return $error_msg;
			}
		}else{
			$error_msg = $this->_dbConn->ErrorMsg();
			$this->_dbConn->RollbackTrans();
			return $error_msg;
		}
	}// end deleteDefine()
	
	/**
	 * Help Function
	 *  delete workflow type when apply form delete
	 * @param string $flow_type_seqno
	 * @return boolean
	 */
	private function _deleteWorkflowType($flow_type_seqno)
	{
		$sql = <<<eof
			delete from hr_signlevel where hsl_seq = :flow_type_seqno
eof;
		$this->_dbConn->Execute($sql,array('flow_type_seqno'=>$flow_type_seqno));
		return $this->_dbConn->Affected_Rows();
	}
	
	/**
	 * HCP workflow 签核层阶的设定
	 * 自定义workflow 
	 *
	 * @param string $type_code
	 * @param string $type_desc
	 */
	private function _insertWorkflowType($type_code)
	{
		$sql = <<<eof
			insert into hr_signlevel(hsl_seq,choice_type,reason,l_date,u_date,flow_seq,seg_segment_no)
			values(hr_signlevel_hsl_seq.nextval,:type_code,:reason,0,0,0,:company_id)
eof;
		return $this->_dbConn->Execute($sql,array('type_code'=>$type_code,
												  'reason'=>$type_code,
												  'company_id'=>$this->_companyId));
	}
	
	/**
	 * Delete user defined workflow master
	 *
	 * @param string $menu_code
	 * @return boolean
	 * @author Dennis 20090925
	 */
	private function _deleteMasterDefine($menu_code)
	{
		$sql = <<<eof
			delete from ehr_wf_define_master
			 where menu_code  = :menu_code
			   and company_id = :company_id
eof;
		//$this->_dbConn->debug = true;
		return $this->_dbConn->Execute($sql,array('menu_code'=>$menu_code,
												  'company_id'=>$this->_companyId));
	}// end _deleteWFDefineMaster()
	
	/**
	 * Delete user defined workflow detail
	 *
	 * @param string $menu_code
	 * @param string $col_name	default null
	 * @return boolean
	 * @author Dennis 20090925
	 */
	private function _deleteDetailDefine($menu_code,$col_name ='')
	{
		$this->_dbConn->BeginTrans();
		$where = empty($col_name) ? '' : ' and col_name = \''.$col_name.'\'';
		$sql = <<<eof
			delete from ehr_wf_define_detail
			 where menu_code = :menu_code
			   $where
eof;
		$this->_dbConn->Execute($sql,array('menu_code'=>$menu_code));
		// 有可能未建资料
		if($this->_dbConn->Affected_Rows()>=0)
		{
			if (!empty($col_name)) 
			{
				$table_name = $this->_getName($menu_code,'flow_table');
				
				if ('1' == $this->_isTableExists($table_name) && 
					'1' == $this->_isColExists($table_name,$col_name))
				{
					$r = $this->_dropColumn($table_name,$col_name);
					
					if ($r !== false)
					{
						// re-create view
						if (false !== $this->_createView($this->_getCreateViewStmt($menu_code)))
						{
							// 删除栏位时,重建 approve view
							$flow_type =  $this->_getApplyType($menu_code);
							$detail_col_define = $this->getDetailDefine($menu_code);
							$approve_view_stm  = $this->_getApproveViewStmt($menu_code,
							                                                $flow_type,
							                                                $this->_getApplyFormCols($detail_col_define));
							
							if (false !== $this->_createView($approve_view_stm))
							{
								// add by dennis 20091116
								//echo $this->_getWFStatusViewStmt($menu_code);
								if (false !== $this->_createView($this->_getWFStatusViewStmt($menu_code,$flow_type)))
								{
									$this->_dbConn->CommitTrans();
									return 1;
								}else {
									$error_msg = $this->_dbConn->ErrorMsg();
									$this->_dbConn->RollbackTrans();
									return $error_msg;
								}
							}else{
								$error_msg = $this->_dbConn->ErrorMsg();
								$this->_dbConn->RollbackTrans();
								return $error_msg;
							}
						}else{
							$error_msg = $this->_dbConn->ErrorMsg();
							$this->_dbConn->RollbackTrans();
							return $error_msg;
						}
					}else{
						$error_msg = $this->_dbConn->ErrorMsg();
						$this->_dbConn->RollbackTrans();
						return $error_msg;
					}//end if
				}else{
					$this->_dbConn->CommitTrans();
					return 1;
				}
			}else{
				$this->_dbConn->CommitTrans();
				return 1;
			}
		}else{
			$error_msg = $this->_dbConn->ErrorMsg();
			$this->_dbConn->RollbackTrans();
			return $error_msg;
		}
	}// end _deleteWFDefineDetail()

	/**
	 * 取得 workflow 自定义表单细项
	 *
	 * @param string $menu_code
	 * @return array
	 * @author Dennis
	 */
	public function getDetailDefine($menu_code)
	{
		$sql = <<<eof
		select menu_code,
		       col_name,
		       col_data_type,
		       col_data_length,
		       col_label,
		       col_type,
		       is_required,
		       is_unique_col,
		       is_active,
		       validate_rule,
		       min_val,
		       max_val,
		       date_format,
		       checked_val,
		       select_data_type,
		       data_source,
		       layout_order
		  from ehr_wf_define_detail
		 where menu_code  = :menu_code
		 order by layout_order
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetArray($sql,array('menu_code'=>$menu_code));	
	}
	
	/**
	 * 新增申请表单栏位
	 *
	 * @param string $menu_code
	 * @param string $col_name
	 * @param string $col_label
	 * @param string $col_type
	 * @param number $layout_order
	 * @param number $is_required
	 * @param string $validate_rule
	 * @param number $min_val
	 * @param number $max_val
	 * @param string $date_format
	 * @param string $checked_val
	 * @param string $select_data_type
	 * @param string $data_source
	 * @return boolean
	 * @author Dennis 20090924
	 */
	public function insertDetailDefine($menu_code,
									   $col_name,
									   $col_data_type,
									   $col_data_length,
									   $col_label,
									   $col_type,
									   $layout_order,									   
									   $is_required = 0,
									   $validate_rule ='',
									   $min_val ='',
									   $max_val ='',
									   $date_format ='',
									   $checked_val ='',
									   $select_data_type ='',
									   $data_source ='',
									   $is_unique = 0)
	{
		//$this->_dbConn->debug = 1;
		$sql = <<<eof
			insert into ehr_wf_define_detail
			  (menu_code,
			   col_name,
			   col_data_type,
			   col_data_length,
			   col_type,
			   is_required,
			   validate_rule,
			   min_val,
			   max_val,
			   date_format,
			   checked_val,
			   select_data_type,
			   data_source,
			   layout_order,
			   create_date,
			   create_by,
			   create_app,
			   col_label,
			   company_id)
			values
			  (:menu_code,
			   :col_name,
			   :col_data_type,
			   :col_data_length,
			   :col_type,
			   :is_required,
			   :validate_rule,
			   :min_val,
			   :max_val,
			   :date_format,
			   :checked_val,
			   :select_data_type,
			   :data_source,
			   :layout_order,
			   sysdate,
			   :create_by,
			   'ess',
			   :col_label,
			   :company_id)
eof;
		$this->_dbConn->BeginTrans();
		$ok = $this->_dbConn->Execute($sql,array('menu_code'=>$menu_code,
											     'col_name'=>$col_name,
												 'col_data_type'=>$col_data_type,
												 'col_data_length'=>$col_data_length,
												 'col_label'=>$col_label,
												 'col_type'=>$col_type,
												 'is_required'=>$is_required,
												 'validate_rule'=>$validate_rule,
												 'min_val'=>$min_val,
												 'max_val'=>$max_val,
												 'date_format'=>$date_format,
												 'checked_val'=>$checked_val,
												 'select_data_type'=>$select_data_type,
												 'data_source'=>$data_source,
												 'layout_order'=>$layout_order,
												 'create_by'=>$this->_userSeqNo,
												 'company_id'=>$this->_companyId));
		$error_msg = $this->_dbConn->ErrorMsg();
		if(1 == $this->_dbConn->Affected_Rows())
		{
			$table_name = $this->_getName($menu_code,'flow_table');
			
			if ('1' == $this->_isTableExists($table_name) &&
			    '1' != $this->_isColExists($table_name,$col_name))
			{
				$col_define = array('IS_REQUIRED'=>$is_required,
									'COL_DATA_TYPE'=>$col_data_type,
									'IS_UNIQUE_COL'=>$is_unique,
									'COL_DATA_LENGTH'=>$col_data_length,
									'COL_NAME'=>$col_name,
									'COL_LABEL'=>$col_label);
				$col_define_strs = $this->_getColDefineStr($col_define);
				if (false !== $this->_addColumn($table_name,substr($col_define_strs[0],0,-1)))
				{
					// add column comments
					if (false !== $this->_addColComment($menu_code,$col_define_strs[1]))
					{
						//$this->_dbConn->debug = 1;
						// re-create view
						if (false !== $this->_createView($this->_getCreateViewStmt($menu_code)))
						{
							// 添加栏位时,重建 approve view
							$detail_col_define = $this->getDetailDefine($menu_code);
							$flow_type = $this->_getApplyType($menu_code);
							$approve_view_stm  = $this->_getApproveViewStmt($menu_code,
																			$flow_type,
																			$this->_getApplyFormCols($detail_col_define));
							if (false !== $this->_createView($approve_view_stm))
							{
								//echo $this->_getWFStatusViewStmt($menu_code);
								
								if (false !== $this->_createView($this->_getWFStatusViewStmt($menu_code,$flow_type)))
								{
									$this->_dbConn->CommitTrans();
									return 1;
								}else{
									$error_msg = $this->_dbConn->ErrorMsg();
									$this->_dbConn->RollbackTrans();
									return $error_msg;
								}
							}else{
								$error_msg = $this->_dbConn->ErrorMsg();
								$this->_dbConn->RollbackTrans();
								return $error_msg;
							}
						}else{
							$error_msg = $this->_dbConn->ErrorMsg();
							$this->_dbConn->RollbackTrans();
							return $error_msg;
						}
					}else{
						$error_msg = $this->_dbConn->ErrorMsg();
						$this->_dbConn->RollbackTrans();
						return $error_msg;
					}
				}else{
					$error_msg = $this->_dbConn->ErrorMsg();
					$this->_dbConn->RollbackTrans();
					return $error_msg;
				}
			}else{
				// insert first column not gen schema
				$this->_dbConn->CommitTrans();
				return 1;
			}
		}else{
			//$error_msg = $this->_dbConn->ErrorMsg();
			$this->_dbConn->RollbackTrans();
			return $error_msg;
		}// end if;
	}// end insertDetailDefine()
	
	/**
	 * 修改栏位定义
	 *
	 * @param string $menu_code
	 * @param string $col_name
	 * @param string $col_label
	 * @param string $col_type
	 * @param number $layout_order
	 * @param number $is_required
	 * @param number $min_val
	 * @param number $max_val
	 * @param string $date_format
	 * @param string $checked_val
	 * @param string $select_data_type
	 * @param string $data_source
	 * @return boolean
	 * @author Dennis 20090927
	 */
	public function updateDetailDefine($menu_code,
									   $col_name,
									   $col_data_type,
									   $col_data_length,
									   $col_label,
									   $col_type,
									   $layout_order,									   
									   $is_required = 0,
									   $validate_rule ='',
									   $min_val ='',
									   $max_val ='',
									   $date_format ='',
									   $checked_val ='',
									   $select_data_type ='',
									   $data_source ='',
									   $is_unique = 0)
	{
		$sql = <<<eof
			update ehr_wf_define_detail
			   set col_type         = :col_type,
			   	   col_data_type    = :col_data_type,
			   	   col_data_length  = :col_data_length,
			       col_label        = :col_label,
			       is_required      = :is_required,
			       validate_rule    = :validate_rule,
			       min_val          = :min_val,
			       max_val          = :max_val,
			       date_format      = :date_format,
			       checked_val      = :checked_val,
			       select_data_type = :select_data_type,
			       data_source      = :data_source,
			       layout_order     = :layout_order,
			       update_date      = sysdate,
			       update_app       = 'ess',
			       update_by        = :update_by
			 where menu_code = :menu_code
			   and col_name  = :col_name
eof;
		$this->_dbConn->BeginTrans();
		$ok = $this->_dbConn->Execute($sql,array('menu_code'=>$menu_code,
										   'col_name'=>$col_name,
										   'col_data_type'=>$col_data_type,
										   'col_data_length'=>$col_data_length,
										   'col_label'=>$col_label,
										   'col_type'=>$col_type,
										   'is_required'=>$is_required,
										   'validate_rule'=>$validate_rule,
										   'min_val'=>$min_val,
										   'max_val'=>$max_val,
										   'date_format'=>$date_format,
										   'checked_val'=>$checked_val,
										   'select_data_type'=>$select_data_type,
										   'data_source'=>$data_source,
										   'layout_order'=>$layout_order,
										   'update_by'=>$this->_userSeqNo));
		if($ok)
		{
			$col_define = array('IS_REQUIRED'=>$is_required,
								'COL_DATA_TYPE'=>$col_data_type,
								'IS_UNIQUE_COL'=>$is_unique,
								'COL_DATA_LENGTH'=>$col_data_length,
								'COL_NAME'=>$col_name,
								'COL_LABEL'=>$col_label);
			$col_define_str = $this->_getColDefineStr($col_define);
			
			$r = $this->_modifyColumn($menu_code,substr($col_define_str[0],0,-1));
			
			if (false !== $r)
			{
				$this->_addColComment($menu_code,$col_define_str[1]);
				$this->_dbConn->CommitTrans();
				return 1;
			}else{
				$error_msg = $this->_dbConn->ErrorMsg();
				$this->_dbConn->RollbackTrans();
				return $error_msg;
			}
		}else{
			$error_msg = $this->_dbConn->ErrorMsg();
			$this->_dbConn->RollbackTrans();
			return $error_msg;
		}// end if;
	}// end updateDetailDefine()
	
	/**
	 * 删除栏位设定资料
	 *
	 * @param sring $menu_code
	 * @param string $col_name
	 * @return boolean
	 * @author Dennis
	 */
	public function deleteDetailDefine($menu_code,$col_name)
	{
		return $this->_deleteDetailDefine($menu_code,$col_name);
	}
	
	/**
	 * Insert/Update Workflow Define Detail
	 *
	 * @param string $menu_code
	 * @param string $col_name
	 * @param string $col_type
	 * @param number $layout_order
	 * @param number $is_required
	 * @param string $validate_rule
	 * @param number $min_val
	 * @param number $max_val
	 * @param string $date_format
	 * @param string $checked_val
	 * @param string $select_data_type
	 * @param string $data_source
	 * @return mixed
	 * @author Dennis
	 */
	private function _replaceDetailDefine($menu_code,
										  $col_name,
										  $col_type,
										  $layout_order,
										  $is_required,
										  $validate_rule,
										  $min_val,
										  $max_val,
										  $date_format,
										  $checked_val,
										  $select_data_type,
										  $data_source)
	{
		//$this->_dbConn->debug = 1;
		$this->_dbConn->Replace('ehr_wf_define_detail',array('menu_code'=>$menu_code,
															 'col_name'=>$col_name,
															 'col_type'=>$col_type,
															 'is_required'=>$is_required,
															 'validate_rule'=>$validate_rule,
															 'min_val'=>$min_val,
															 'max_val'=>$max_val,
															 'date_format'=>$date_format,
															 'checked_val'=>$checked_val,
															 'select_data_type'=>$select_data_type,
															 'data_source'=>$data_source,
															 'layout_order'=>$layout_order,
															 'company_id'=>$this->_companyId,
															 'create_by'=>$this->_userSeqNo),
								array('menu_code','col_name'),true);
		return $this->_dbConn->Affected_Rows();
	}// end _replaceDetailDefine()
	
	/**
	 * 
	 */
	private function _isFileColExists($form_cols)
	{
		$c = count($form_cols);
		for ($i=0; $i<$c; $i++)
		{
			if ($form_cols[$i]['COL_TYPE'] == 'file') return true;
		}
		return false;
	}
	
	
	/**
	 * 根据设定组成自定义的 workflow 申请单 和查询 where condition
	 *
	 * @param string $menu_code
	 * @param string $scriptname
	 * @param string $is_qry_form_form
	 * @return string
	 * @author Dennis 20091117
	 */
	public function renderForm($menu_code,$scriptname,$is_qry_form = false,$menu_desc = '')
	{
		$_formMaster = $this->getMasterDefine($menu_code);
		$_formDetail = $this->getDetailDefine($menu_code);
		//pr($_formDetail);
		$c = count($_formDetail);
		$html = '';
		if ($c>0)
		{
			$js_code = '';
			$upload_file_att = $this->_isFileColExists($_formDetail) ? ' enctype="multipart/form-data" ' : '';
			$html .= '<form name="qry_form" method="post" '.$upload_file_att.' action="?scriptname='.$scriptname.'">';
			$html .= '<input type="hidden" name="doaction" id="doaction" value="submit"/>';
			$html .= $this->_getTableBeginTag();
			$submit_button   = '<input type="submit" name="submit_form"  value="Submit" class="button-submit"/>';
			$tmp_save_button = '<input type="submit" name="tmp_save"     value="Save" class="button-submit" onclick="$(\'#doaction\').val(\'tmpsave\');"/>';
			
			$submit_qry_btn = '<input type="submit" name="submit_form"  value="Submit Query" onclick="$(\'#doaction\').val(\'search\');" class="button-submit"/>';
			$reset_btn      = '<input type="reset"  name="reset_form"   value="Reset" class="button-submit"/>';
			$hidden_item    = '<input type="hidden" name="menu_code" value="'.$menu_code.'"/>';
			$hidden_item   .= '<input type="hidden" name="menu_desc" value="'.$menu_desc.'"/>';
			$hidden_item   .= '<input type="hidden" name="flow_type" value="'.$_formMaster['FLOW_TYPE_CODE'].'"/>';
			
			for($i=0; $i<$c; $i++)
			{
				if(!$is_qry_form)
				{
					$element = $this->_getFormElement($_formDetail[$i]['COL_NAME'],
													  $_formDetail[$i]['COL_TYPE'],
													  $_formDetail[$i]['VALIDATE_RULE'],
													  $_formDetail[$i]['IS_REQUIRED'],
													  $_formDetail[$i]['MAX_VAL'],
													  $_formDetail[$i]['MIN_VAL'],
													  $_formDetail[$i]['CHECKED_VAL'],
													  $_formDetail[$i]['DATE_FORMAT'],
													  $_formDetail[$i]['SELECT_DATA_TYPE'],
													  $_formDetail[$i]['DATA_SOURCE']);
				}else{
					//echo $_formDetail[$i]['IS_REQUIRED'].'<hr/>';
					if ($_formDetail[$i]['IS_REQUIRED'] == '1')
					{
						$element = $this->_getFormElement($_formDetail[$i]['COL_NAME'],
														  $_formDetail[$i]['COL_TYPE'],
														  $_formDetail[$i]['VALIDATE_RULE'],
														  $_formDetail[$i]['IS_REQUIRED'],
														  $_formDetail[$i]['MAX_VAL'],
														  $_formDetail[$i]['MIN_VAL'],
														  $_formDetail[$i]['CHECKED_VAL'],
														  $_formDetail[$i]['DATE_FORMAT'],
														  $_formDetail[$i]['SELECT_DATA_TYPE'],
														  $_formDetail[$i]['DATA_SOURCE']);
					}else{
						continue;
					}
				}
				$required_mark =  $_formDetail[$i]['IS_REQUIRED'] == '1' ? ' * ' : '';
				if ($_formMaster['LAYOUT_COLS'] == 1)
				{
					$html .= '<tr><td class="column-label">'.$_formDetail[$i]['COL_LABEL'].$required_mark.'</td>';
					/*
					$html .= '<td><input type="'.$_formDetail[$i]['COL_TYPE'].
							 '" id="'.$_formDetail[$i]['COL_NAME'].'" '.
							 '" name="'.$_formDetail[$i]['COL_NAME'].'" class="text-input"/></td></tr>';
					*/
					$html .= '<td>'.$element['html'].'</td></tr>';
				}
				elseif ($_formMaster['LAYOUT_COLS'] == 2)
				{
					$html .= '<tr><td class="column-label">'.$_formDetail[$i]['COL_LABEL'].$required_mark.'</td>';
					/*
					$html .= '<td><input type="'.$_formDetail[$i]['COL_TYPE'].
							 '" id="'.$_formDetail[$i]['COL_NAME'].'" '.
							 '" name="'.$_formDetail[$i]['COL_NAME'].'" class="text-input"/></td></tr>';
					*/
					$html .= '<td>'.$element['html'].'</td></tr>';
				}// end if
				$js_code .= $element['js'];
			}// end for loop
			if ($_formMaster['TMP_SAVE_ALLOWED'] == 1 && !$is_qry_form)
			{
				$submit_button .= $tmp_save_button;
			}
			
			$sbtn = $is_qry_form ? $submit_qry_btn.$reset_btn.$hidden_item : $submit_button;
			if ($_formMaster['LAYOUT_COLS'] == 1)
			{
				$html .= '<tr><td></td><td>'.$sbtn.'</td></tr>';
			}
			elseif ($_formMaster['LAYOUT_COLS'] == 2)
			{
				$html .= '<tr><td></td><td colspan="3">'.$sbtn.'</td></tr>';
			}
			$html .= $this->_getTableEndTag();
			$html .= $this->_getFromEndTag();
			
			// add form validation rules
			if (!empty($js_code) && !$is_qry_form)
			{
				//echo $js_code;
				$html .= $this->_getValidateJS();
				$html .= $this->_getJSBeginTag();
				$html .= $this->_getJqueryVBegin();
				$html .= substr($js_code,0,-1); // 取掉最后一个逗号
				$html .= $this->_getJqueryVEnd();
				$html .= $this->_getJqueryVMsg();
				$html .= $this->_getJSEndTag();
			}
		}else{
			$html  = '<div class="error">'.$this->_errorMsg[self::WF_ERR_NO_COL_DEFINED].'</div>';
		}// end if
		return $html;
	}// end renderForm()

	/**
	 * Help Function
	 *
	 * @return string
	 */
	private function _getJqueryVBegin()
	{
		return '$("form").validate({
		  				rules: {';
	}
	
	/**
	 * Help Function
	 *
	 * @return string
	 */
	private function _getJqueryVEnd()
	{
		return ' }
		});';
	}
/**
	 * Help Function
	 *
	 * @param string $name
	 * @return string
	 * @author Dennis 20090918 
	 */
	private function _getInputText($name)
	{
		return sprintf('<input type="text" name="%s" id="%s" class="input-text"/>',$name,$name);
	}
	
	/**
	 * Help Function
	 *
	 * @param string $name
	 * @param string $date_format
	 * @return string
	 * @author Dennis 20090918
	 */
	private function _getInputDate($name,$date_format)
	{
		include_once 'Form/Input_Calendar.class.php';
		$js_path = DOCROOT.'/libs/library/JsCalendar/';
		//echo $js_path;
		
		$cal = new Input_Calendar(array('name'=>$name,
										'id'=>$name,
										'jsPath'=>$js_path,
										'value'=>'',
										'ifFormat'=>$date_format,
										'lang'=>$this->_langCode));
		return $cal->render();
	}
	/**
	 * Help Function
	 *
	 * @param string $name
	 * @return string
	 * @author Dennis 20090918
	 */
	private function _getTextarea($name)
	{
		return sprintf('<textarea name="%s" id="%s" class="text-input"></textarea>',$name,$name);
	}
	
	/**
	 * Help Function
	 *
	 * @param string $name
	 * @param string $data_type
	 * @param string $data_source
	 * @return string
	 * @author Dennis20090918
	 */
	private function _getSelect($name,$data_type,$data_source)
	{
		$list_items = '';
		$html = sprintf('<select name="%s" id="%s"><option value=""> -- Select -- </option>',$name,$name);
		if (strtolower($data_type) == 's')
		{
			$list_items = $this->_str2Array($data_source);
		}
		if (strtolower($data_type) == 'd')
		{
			$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
			// for parse user passed parameters
			$params = null;
			if (stripos($data_source,':company_id') >0 )
			{
				$params['company_id'] = $this->_companyId;
			}
			if (stripos($data_source,':dept_id') >0 )
			{
				$params['dept_id'] = $_SESSION['user']['dept_id'];
			}
			if (stripos($data_source,':emp_id') >0 )
			{
				$params['emp_id'] = $_SESSION['user']['emp_id'];
			}
			//$this->_dbConn->debug = 1;
			if (is_null($params))
			{
				$list_items = $this->_dbConn->GetArray($data_source);
			}else{
				$list_items = $this->_dbConn->GetArray($data_source,$params);
			}
		}
		$c = count($list_items);
		for ($i=0; $i<$c; $i++)
		{
			$html .= sprintf('<option value="%s">%s</option>',$list_items[$i][0],$list_items[$i][1]);
		}
		$html .= '</select>';
		return $html;
	}// end _getSelect()
	
	/**
	 * Help Function
	 *
	 * @param string $name
	 * @param string $checked_val
	 * @return string
	 * @author Dennis
	 */
	private function _getCheckbox($name,$checked_val)
	{
		return sprintf('<input type="checkbox" name="%s" id="%s" value="%s">',$name,$name,$checked_val);
	}
	
	/**
	 * Help Function
	 * @param string $name column name
	 * @return string html code
	 * @author dennis
	 * 
	 */
	private function _getInputFile($name)
	{
		return sprintf('<input type="file" name="%s" id="%s" size="12" class="text-input"/><br/><span class="notice">Upload max size: %s,allowed type: .jpg,.jpeg,.gif,.png,.bmp,.pdf,.doc,.xsl,.ppt</span>',$name,$name,$this->_getMaxUploadSize());
	}
	
	private function _getMaxUploadSize()
	{
		return ini_get('upload_max_filesize');
	}
	
	/**
	 * Help Function
	 *
	 * @return string
	 */
	private function _getJqueryVMsg()
	{
		return '$.extend(
					$.validator.messages, {
						required: "'.$this->_errorMsg[self::WF_MSG_MUST_BE_INPUT].'",
						number:   "'.$this->_errorMsg[self::WF_MSG_MUST_BE_NUMBER].'",
						digits:   "'.$this->_errorMsg[self::WF_MSG_MUST_BE_INT].'"
				});';
	}// end _getJqueryVMsg()
	
	/**
	 * Help Function
	 *  Generate Form Element
	 * @param string $name
	 * @param string $type
	 * @param string $validate_rule
	 * @param number $max_val
	 * @param number $min_val
	 * @param string $checked_val
	 * @param string $select_data_type
	 * @param string $data_source
	 * @return string
	 * @author Dennis 20090917
	 * 
	 */
	private function _getFormElement($name,$type,
									 $validate_rule='',
									 $is_required='',
									 $min_val='',
									 $max_val='',
									 $checked_val='',
									 $date_format= '',
									 $select_data_type='',
									 $data_source='')
	{
		$form = '';
		switch(strtolower($type))
		{
			case 'text':
				$form['html'] = $this->_getInputText($name);
				break;
			case 'date':
				$form['html'] = $this->_getInputDate($name,$date_format);
				break;
			case 'textarea':
				$form['html'] = $this->_getTextarea($name);
				break;
			case 'select':
				$form['html'] = $this->_getSelect($name,$select_data_type,$data_source);
				break;
			case 'checkbox':
				$form['html'] = $this->_getCheckbox($name,$checked_val);
				break;
			case 'file':
				$form['html'] = $this->_getInputFile($name);
			default:break;	
		}// end switch
		$form['js'] = '';
		$jscode = '';
		
		if(!empty($validate_rule))
		{
			//echo $validate_rule.'<br/>';
			switch ($validate_rule)
			{
				case 'digital':
					if ($is_required)
					{
						$jscode = $name.':{required:true,digits:true},';
					}else{
						$jscode = $name.':{digits:true},';
					}// end if
					break;
				case 'number':
					if ($is_required)
					{
						$jscode = $name.':{required:true,number:true},';
					}else{
						$jscode = $name.':{number:true},';
					}
					break;
				case 'date':
					if ($is_required)
					{
						$jscode = $name.':{required:true,date:true},';
					}else{
						$jscode = $name.':{date:true},';
					}
					break;
				case 'range':
					if ($is_required)
					{
						$jscode = $name.':{required:true,range:['.$min_val.','.$max_val.']},';
					}else{
						$jscode = $name.':{range:['.$min_val.','.$max_val.']},';
					}
					break;
				case 'mail':
					if ($is_required)
					{
						$jscode = $name.':{required:true,email:true},';
					}else{
						$jscode = $name.':{email:true},';
					}
					break;
				default:break;				
			}// end switch
			
		}else{
			if ('1' == $is_required)
			{
				$jscode = $name.':{required:true},';
			}
		}
		$form['js'] .= $jscode;
		return $form;
	}// end _getFormElement()
	
	/**
	 * Help Function
	 * string to array
	 *
	 * @param string $str
	 * @return Array
	 * @author Dennis 20090918
	 */
	private function _str2Array($str)
	{
		// firstly split by ';';
		$arr = explode(';',$str);
		for ($i=0; $i<count($arr); $i++)
		{
			$arr[$i] = explode(':',$arr[$i]);
		}
		return $arr;
	}// end _str2Array()
	
	/**
	 * Help Function
	 *  Get Validation Js 
	 */
	private function _getValidateJS()
	{
		return '<script src="'.$GLOBALS['config']['js_dir'].'/jquery.validate.min.js" language = "JavaScript" type = "text/javascript" charset = "utf-8"></script>';
	}
	
	/**
	 * Help Function
	 *
	 * @return string
	 */
	private function _getJSBeginTag()
	{
		return '<script language="JavaScript">';
	}
	
	/**
	 * Help Function
	 *
	 * @return string
	 */
	private function _getJSEndTag()
	{
		return '</script>';
	}
	
	/**
	 * Help Function
	 *
	 * @return string
	 */
	private function _getFromEndTag()
	{
		return '</form>';
	}
	
	/**
	 * Help Function
	 *
	 * @return string
	 */
	private function _getTableBeginTag()
	{
		return '<table class="bordertable">';
	}
	
	/**
	 * Help Function
	 *
	 * @return string
	 */
	private function _getTableEndTag()
	{
		return '</table>';
	}
		
	/**
	 * Help Function
	 *
	 * @param string $title
	 * @return string
	 * @author Dennis
	 */
	private function _getBoxHeader($title)
	{
		return '<div class="sidebox resources">
					<div class="x-box-tl">
						<div class="x-box-tr">
							<div class="x-box-tc"></div>
						</div>
					</div>
					<!-- Header Box End -->
					<div class="x-box-ml">
					<div class="x-box-mr">
					<div class="x-box-mc">
					<!-- Grid Title -->
					<h3 style="margin:1px;padding:1px;">'.$title.'</h3>
					<hr noshade="noshade" size="1" style="color: rgb(128, 128, 128);" />';
	}// end _getBoxHeader()
	
	/**
	 * Get Box Footer Html Code
	 *
	 * @return string
	 */
	private  function _getBoxFooter()
	{
		return '</div></div></div><div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div></div>';
	}// end _getBoxFooter();
	
	//////////////////////////////////////////////////////////////////////////
	//	Dynamic create schema
	//////////////////////////////////////////////////////////////////////////
	
	/**
	 * Create Schema
	 *
	 * @param string $menu_code
	 * @return boolean
	 * @author Dennis 20090929
	 */
	public function createSchema($menu_code)
	{
		//$this->_dbConn->debug = 1;
		$flow_detail_define = $this->getDetailDefine($menu_code);
		if (count($flow_detail_define)>0)
		{
			$flow_master_define = $this->getMasterDefine($menu_code);
			// workflow in process table
			$wf_flow_table_name    = $this->_getName($menu_code,'flow_table');
			$flow_sequence_name    = $this->_getName($menu_code,'flow_seq');		
			
			// create workflow sequence
			if (false !== $this->_createSequence($flow_sequence_name))
			{
				$cols_define = $this->_getColsDefineStmt($flow_detail_define);
				
				$flow_tab_stmt = $this->_getCreateFlowTabStmt($wf_flow_table_name,
															  $flow_master_define['FLOW_TYPE_DESC'],
															  $cols_define[0], 
															  $cols_define[1]);
				// create workflow in processing table
				if (false !== $this->_createTable($wf_flow_table_name,$flow_tab_stmt))
				{
					// create fact data view  for user query
					$fact_view_stmt = $this->_getCreateViewStmt($menu_code);
					if (false !== $this->_createView($fact_view_stmt))
					{
						$flow_type = $flow_master_define['FLOW_TYPE_CODE'];
						$cols_str = $this->_getApplyFormCols($flow_detail_define);
						$approve_view_stmt = $this->_getApproveViewStmt($menu_code,
																		$flow_type,
																		$cols_str);
						if (false !== $this->_createView($approve_view_stmt))
						{
							if (false !== $this->_createView($this->_getWFStatusViewStmt($menu_code,$flow_type)))
							{
								return 1;
							}else{
								return $this->_dbConn->ErrorMsg();
							}
						}else{
							return $this->_dbConn->ErrorMsg();
						}
					}else{
						return $this->_dbConn->ErrorMsg();
					}
				}else{
					return $this->_dbConn->ErrorMsg();
				}
			}else{
				return $this->_dbConn->ErrorMsg();
			}
		}else{
			//return '尚未定义任何栏位，不能生成申请单.';
			return $this->_errorMsg[self::WF_ERR_NO_COL_DEFINED];
		}
	}// end createScheam()
	
	/**
	 * Drop Workflow relate db object
	 *
	 * @param string $menu_code
	 * @return boolean
	 * @author Dennis 20091104
	 */
	public function dropSchema($menu_code)
	{
		if (false !== $this->_dropTable($this->_getName($menu_code,'flow_table')))
		{
			if (false !== $this->_dropSequence($this->_getName($menu_code,'flow_seq')))
			{
				if (false !== $this->_dropView($this->_getName($menu_code,'fact_view')))
				{
					if (false !== $this->_dropView($this->_getName($menu_code,'approve_view')))
					{
						if (false !== $this->_dropView($this->_getName($menu_code,'flow_view')))
						{
							return true;
						}else{
							return $this->_dbConn->ErrorMsg();
						}
					}else{
						return $this->_dbConn->ErrorMsg();
					}
				}else{
					return $this->_dbConn->ErrorMsg();
				}
			}else{
				return $this->_dbConn->ErrorMsg();
			}
		}
		return false;
	}// end dropSchema()
	
	/**
	 * Help Function for Drop Scheam
	 * 删除表单时，同时删除 hr_approve_sz 中的资料
	 * add by dennis 20091207
	 * @param $flow_type 申请单类型
	 * @return boolean
	 * @author Dennis 2009
	 */
	private function _deleteApproveData($flow_type)
	{
		$sql = <<<eof
			delete from hr_approve_sz 
			      where seg_segment_no = :company_id 
			        and flow_type      = :flow_type
eof;
		$this->_dbConn->Execute($sql,array('company_id'=>$this->_companyId,
										   'flow_type'=>$flow_type));
		return $this->_dbConn->Affected_Rows();
	}
	
	/**
	 * Help Function
	 * Get table/view/sequence name
	 * @param string $menu_code
	 * @param sring $type
	 * @return string
	 */
	private function _getName($menu_code,$type)
	{
		$prefix = self::$_tablePrefix.$menu_code;
		$obj_name = '';
		switch(strtolower($type))
		{
			case 'fact_view':
				$obj_name = $prefix.'_v';
			break;
			case 'flow_table':
				$obj_name = $prefix.'_flow_sz';
				break;
			case 'flow_seq':
				$obj_name = $prefix.'_flow_s';
				break;
			case 'approve_view':
				return $prefix.'_approve_v';
				break;
			case 'flow_view':
				return $prefix.'_flow_status_v';
				break;
			default:break;
		}
		return strtoupper($obj_name);
	}
	
	/**
	 * help function
	 * table 是否存在
	 *
	 * @param string $tablename
	 * @return boolean
	 * @author Dennis 20091027
	 */
	private function _isTableExists($tablename)
	{
		$sql = 'select 1 from user_tables where table_name = :table_name';
		return $this->_dbConn->GetOne($sql,array('table_name'=>strtoupper($tablename)));
	}
	
	/**
	 * Help Function
	 *
	 * @param string $table_name
	 * @param string $col_name
	 * @return number
	 * @author Dennis 20091103
	 */
	private function _isColExists($table_name,$col_name)
	{
		 $sql = <<<eof
		 select 1
		  from user_tab_cols
		 where table_name = :table_name
		   and column_name = :col_name
eof;
		return $this->_dbConn->GetOne($sql,array('table_name'=>strtoupper($table_name),
												 'col_name'=>strtoupper($col_name)));
	}
	
	/**
	 * Help function
	 *  检查表里是否有资料
	 *
	 * @param string $tablename
	 * @return boolean
	 * @author Dennis 20091027
	 */
	private function _isDataExists($tablename)
	{
		$sql = 'select count(1) from :table_name ';
		$r = $this->_dbConn->GetOne($sql,array('table_name'=>strtoupper($tablename)));
		if($r>0) return true;
		return false;
	}
	
	/**
	 * Help Function
	 *  检查 Sequence 是否已经存在
	 * @param string $sequence_name
	 * @return boolean
	 * @author Dennis 20091027
	 */
	private function _isSequenceExists($sequence_name)
	{
		$sql = 'select * from user_sequences where sequence_name = :sequence_name';
		return $this->_dbConn->GetOne($sql,array('sequence_name'=>strtoupper($sequence_name)));
	}
	
	/**
	 * Help Function
	 *
	 * @param array $cols_define
	 * @return array
	 * @author Dennis 20090930
	 * 
	 */
	private function _getColsDefineStmt(array $cols_define)
	{
		//pr($cols_define);
		$cols_define_str  = '';
		$col_comments_str = '';
		$c = count($cols_define);
		for ($i=0;$i<$c; $i++)
		{
			if ($cols_define[$i]['IS_ACTIVE'] == '1')
			{
				$col_def_str      = $this->_getColDefineStr($cols_define[$i]);
				$cols_define_str .= $col_def_str[0];
				$col_comments_str.= $col_def_str[1];
			}
		}
		$r[0] = $cols_define_str;
		$r[1] = $col_comments_str;
		return $r;
	}// end _getColsDefineStmt()
	
	/**
	 * Help Function
	 *  取得申请单上栏位的名称
	 * @param array $cols_define
	 * @return string (col_name1,col_name2...)
	 * @author Dennis 20091106
	 */
	
	private function _getApplyFormCols($cols_define,$to_char = true)
	{
		$cols = '';
		for ($i=0; $i<count($cols_define); $i++)
		{
			if ($cols_define[$i]['COL_DATA_TYPE'] == 'date' && $to_char)
			{
				$col = '';
				switch ($cols_define[$i]['DATE_FORMAT'])
				{
					case '%Y-%m-%d':
						$col = 'to_char(b.'.$cols_define[$i]['COL_NAME'].',\'yyyy-mm-dd\')';
						break;
					case '%Y-%m-%d %H:%M':
						$col = 'to_char(b.'.$cols_define[$i]['COL_NAME'].',\'yyyy-mm-dd hh24:mi\')';
						break;
					case '%Y-%m-%d %H:%M:%S':
						$col = 'to_char(b.'.$cols_define[$i]['COL_NAME'].',\'yyyy-mm-dd hh24:mi:ss\')';
						break;
					default:
						$col = 'to_char(b.'.$cols_define[$i]['COL_NAME'].',\'yyyy-mm-dd\')';
						break;
				}
				$cols .= $col.' as '.$cols_define[$i]['COL_NAME'].',';
			}else{
				$cols .= 'b.'.$cols_define[$i]['COL_NAME'].',';
			}
		}
		//echo $cols.'<br/>';
		return $cols;
	}
	
	/**
	 * Help Function
	 *  Get apply type
	 * @param string $menu_code
	 * @return string
	 * @author Dennis 20091116
	 */
	private function _getApplyType($menu_code)
	{
		$r = $this->getMasterDefine($menu_code);
		return $r['FLOW_TYPE_CODE'];
	}
	/**
	 * Help Function
	 *  取得某个栏位的定义
	 * @param array $cols_define
	 * @return string
	 * @author Dennis 20091103
	 */
	private function _getColDefineStr($cols_define)
	{
		if (is_array($cols_define))
		{
			$cols_define_str = '';
			$modify_str = '';
			if ($cols_define['IS_REQUIRED'] == '1')
			{
				$modify_str .= ' not null ';
			}else{
				$modify_str .= ' null '; //modify by dennis 20091116
				// 修改时出现
				//$modify_str .= ' ';
			}
			if ($cols_define['IS_UNIQUE_COL'] == '1')
			{
				$modify_str .= ' unique ';
			}
			switch($cols_define['COL_DATA_TYPE'])
			{
				case 'varchar2':
					$cols_define_str.= chr(10).$cols_define['COL_NAME']. ' varchar2('.
									   $cols_define['COL_DATA_LENGTH'].') '.$modify_str.',';
					break;
				case 'number':
					$ls = $cols_define['COL_DATA_LENGTH'] ? ' ('.
						  $cols_define['COL_DATA_LENGTH'].') ' : '';
					$cols_define_str .= chr(10).$cols_define['COL_NAME']. ' number'.
										$ls.$modify_str.',';
					break;
				case 'date':
					$cols_define_str .= chr(10). $cols_define['COL_NAME']. ' date '.$modify_str.',';
					break;
				default:break;
			}
			$r[0] = $cols_define_str;
			$r[1] = chr(10).'comment on column -X$X$-.'.$cols_define['COL_NAME'].'  is \''.$cols_define['COL_LABEL'].'\';';
			return $r;
		}
		return '';
	}// end _getColDefineStr()
	
	/**
	 * Get create workflow table sql
	 *
	 * @param string $table_name
	 * @param string $wf_type_code
	 * @param string $wf_type_desc
	 * @param string $wf_cols_define
	 * @param string $wf_cols_comments
	 * @return string  create table sql statement
	 * @author Dennis 20090930
	 */
	private function _getCreateFlowTabStmt($table_name,
										   $wf_type_desc,
										   $wf_cols_define,
										   $wf_cols_comments)
	{
		$wf_cols_comments = str_replace('-X$X$-',$table_name,$wf_cols_comments);
		//echo $wf_cols_define;
		$create_tab_stmt = <<<eof
		create table $table_name
		(
		  %s_id             number not null primary key,
		  seg_segment_no    varchar2(10),
		  psn_id            varchar2(32),
		  $wf_cols_define
		  reverse1          varchar2(200),
		  reverse2          varchar2(200),
		  reverse3          varchar2(200),
		  reverse4          varchar2(200),
		  reverse5          varchar2(200),
		  status            varchar2(2) not null,
		  refuse_reason     varchar2(200),
		  cancel_admin_id   varchar2(32),
		  submit_date       date,
		  signlevel_id      number,
		  remark            varchar2(1000),
		  create_by         varchar2(32) default user,
		  create_date       date default sysdate,
		  update_by         varchar2(32) default user,
		  update_date       date default sysdate,
		  create_program    varchar2(32),
		  update_program    varchar2(32)
		)
eof;
		$col_comments_str = <<<eof
		comment on table  $table_name  is ' $wf_type_desc - created by eHR automatically %s';
		comment on column $table_name.seg_segment_no  is '公司id';
		comment on column $table_name.psn_id  is '员工id';
		$wf_cols_comments
		comment on column $table_name.reverse1  is '保留字段1';
		comment on column $table_name.reverse2  is '保留字段2 ';
		comment on column $table_name.reverse3  is '保留字段3 ';
		comment on column $table_name.reverse4  is '保留字段4  ';
		comment on column $table_name.reverse5  is '保留字段5 ';
		comment on column $table_name.status  is '申請單狀態 "00":未提交 "01":已提交   "02":流程中 "03":核准 "04":駁回 "05":作廢 "06":異常';
		comment on column $table_name.refuse_reason  is '如果申請單被駁回,則填入駁回原因, 如果到了最後一步核准,出錯,則填入異常原因';
		comment on column $table_name.cancel_admin_id  is '作廢申請單的管理員id';
		comment on column $table_name.submit_date  is '申請提交時間';
		comment on column $table_name.signlevel_id  is '簽核層級設定id(from hr_signlevel.hsl_seq)';
		comment on column $table_name.remark  is '备注';
		comment on column $table_name.create_by  is '建档者';
		comment on column $table_name.create_date  is '建档日期';
		comment on column $table_name.update_by  is '修改者';
		comment on column $table_name.update_date  is '修改日期';
		comment on column $table_name.create_program  is '建档程序';
		comment on column $table_name.update_program  is '修改程序';
eof;
		$r[0] = sprintf($create_tab_stmt,$table_name);
		$r[1] = sprintf($col_comments_str,date('Y-m-d H:i:s'));
		return $r;
	}
	
	/**
	 * Help Function
	 * Create Workflow Approve views
	 *
	 * @param string $menu_code		菜单代码		
	 * @param string $apply_type	申请类型
	 * @param string $cols_str		申请表栏位组合(col_name1,col_name2,...)
	 * @return string
	 * @author Dennis 20091105
	 */
	private function _getApproveViewStmt($menu_code,$apply_type,$cols_str)
	{
		$view_name_prefix = self::$_tablePrefix.$menu_code;
		$stmt = <<<eof
		create or replace view %s_approve_v as
			select a.approve_sz_id        as approve_seqno,
			       b.%s_flow_sz_id        as workflow_seqno,
			       b.seg_segment_no       as company_id,
			       a.psn_id               as approver_emp_seqno,
			       a.can_approve          as can_approve,
			       c.emp_id               as emp_id,
			       c.emp_name             as emp_name,
			       c.dept_id              as dept_id,
			       c.dept_name            as dept_name,
			       $cols_str
			       b.status               as flow_status,
			       a.flow_type,
			       decode(b.status,
			              '00',
			              '暂存',
			              '01',
			              '已提交',
			              '02',
			              '流程中',
			              '03',
			              '核准',
			              '04',
			              '驳回',
			              '05',
			              '作废',
			              '06',
			              '异常')    as status_name,
			       nvl(d.agency_psn_id, a.psn_id) as agency_emp_seqno,
			       decode(a.psn_id,
			              d.agency_psn_id,
			              null,
			              pk_personnel_msg.f_emp_msg(a.seg_segment_no, 
			              							 a.psn_id, '02')) agency_info
			  from hr_approve_sz  a,
			       %s_flow_sz     b,
			       ehr_employee_v c,
			       /*
			       table(cast(wf.f_get_workflow_agency(a.seg_segment_no,
			                                           'Y',
			                                           a.psn_id,
			                                           to_date(b.create_date,
			                                                   'YYYY-MM-DD HH24:MI')) as
			                  wf.tab_agency_info))*/ ehr_wf_agency d
			 where a.flow_sz_id  = b.%s_flow_sz_id
			   and a.seg_segment_no = b.seg_segment_no
			   and a.seg_segment_no = c.company_id
			   and b.seg_segment_no = c.company_id
			   and b.psn_id         = c.emp_seq_no
			   and a.signlevel_id   = d.signlevel_id(+)
			   and a.psn_id         = d.mgr_psn_id(+)
			   and a.flow_type      = '%s'
eof;
		return sprintf($stmt,$view_name_prefix,
							 $view_name_prefix,
							 $view_name_prefix,
							 $view_name_prefix,$apply_type);
	}
	
	/**
	 * Help Function
	 *  取得 create worklfow status view create statement
	 *  for draw flowchart
	 * @param string $menu_code
	 * @param string $flow_type
	 * @return string
	 * @author Dennis 20091116 last update by dennis 20091207
	 */
	private function _getWFStatusViewStmt($menu_code,$flow_type)
	{
		$stmt = <<<eof
		create or replace view udwf_%s_flow_status_v as
		select a.seg_segment_no,
		       a.psn_id,
		       p.id_no_sz,
		       p.name_sz,
		       a.main_level as lev_cnt,
		       a.sub_level  as lev,
		       count(0) over(partition by a.seg_segment_no, a.flow_sz_id, a.sub_level) cnt_per_lev,
		       decode(a.is_approve, 'Y', 'Y', decode(a.is_refuce, 'Y', 'N'))           approve_flag,
		       a.must_approve,
		       a.can_approve,
		       a.approve_sz_id,
		       a.flow_sz_id,
		       b.submit_date,
		       b.status,
		       decode(a.psn_id,
		              a.approve_psn_id,
		              null,
		              pk_personnel_msg.f_emp_msg(a.seg_segment_no,
		                                          a.approve_psn_id,
		                                          '02')) agency_name_sz,
		       decode(a.approve_psn_id,
		              null,
		              decode(a.is_approve || a.is_refuce,
		                     'NN',
		                     wf.f_get_agency_mgr_names(a.seg_segment_no,
		                                            a.psn_id,
		                                            a.signlevel_id,
		                                            decode(b.status,
		                                                   '01',
		                                                   sysdate,
		                                                   '02',
		                                                   sysdate,
		                                                   a.create_date),
		                                            chr(10)))) agency_names
		  from hr_approve_sz       a, 
		       udwf_%s_flow_sz     b, 
		       hr_personnel_base   p
		 where a.psn_id     = p.id 
		   and a.flow_sz_id = b.udwf_%s_flow_sz_id
		   and a.flow_type  = '%s'
		 order by a.seg_segment_no, 
		          a.flow_sz_id, 
		          a.sub_level, 
		          a.must_approve desc
eof;
		return sprintf($stmt,$menu_code,$menu_code,$menu_code,$flow_type);
	}
	
	/**
	 * Help Function
	 *	Dynamic create sequence
	 * @param string $seqname
	 * @param int $start
	 * @param int $increment
	 * @return boolean
	 * @author Dennis 20090929
	 */
	private function _createSequence($seqname,$start = 1,$increment = 1)
	{
		if ($this->_isSequenceExists($seqname))
		{
			$this->_dropSequence($seqname);
		}
		$sql = <<<eof
			create sequence $seqname
				start with $start
				increment by $increment
eof;
		return $this->_dbConn->Execute($sql);
	}
	
	/**
	 * Help Function
	 *
	 * @param string $seqname
	 * @return boolean
	 */
	private function _dropSequence($seqname)
	{
		if ($this->_isSequenceExists($seqname))
		{
			$sql = "drop sequence $seqname";
			return $this->_dbConn->Execute($sql);
		}
		return true;
	}
	
	
	/**
	 * Help Function 
	 * Dynamic Create Table
	 *
	 * @param string $table_name
	 * @param array $create_table_stmt
	 * @return boolean
	 * @author Dennis 20091028
	 */
	private function _createTable($table_name,$create_table_stmt)
	{
		if ('1' == $this->_isTableExists($table_name))
		{
			$this->_dropTable($table_name);
		}
		// create table
		$r = $this->_dbConn->Execute($create_table_stmt[0]);
		if (false !== $r)
		{
			// add comments
			$comments = explode(';',$create_table_stmt[1]);
			foreach ($comments as $comm) {
				$this->_dbConn->Execute($comm);
			}
			return 1;
		}else {
			return 1;
		}
	}// end _createTable()
	
	/**
	 * 修改 table field
	 *
	 * @param string $alter_stmt
	 * @return boolean
	 * @author Dennis 20090929
	 */
	/*
	public function alterTable($alter_stmt)
	{
		return $this->_dbConn->Execute($alter_stmt);
	}*/
	
	/**
	 * Help Function
	 * Drop table column
	 * @param string $table_name
	 * @param string $col_name
	 * @return boolean
	 * @author Dennis 20091103
	 */
	private function _dropColumn($table_name,$col_name)
	{
		//$this->_dbConn->debug = 1;
			
		$stmt = <<<eof
			alter table %s drop column %s
eof;
		return $this->_dbConn->Execute(sprintf($stmt,$table_name,$col_name));
	}
	
	/**
	 * Help Function
	 * Modify column attributes
	 * @param string $menu_code
	 * @param string $col_define_str
	 * @return boolean
	 * @author Dennis 2001103
	 */
	private function _modifyColumn($menu_code,$col_define_str)
	{
		$table_name = $this->_getName($menu_code,'flow_table');
		//$this->_dbConn->debug = 1;
		$stmt = <<<eof
			alter table %s modify %s
eof;
		return $this->_dbConn->Execute(sprintf($stmt,$table_name,$col_define_str));
	}
	
	/**
	 * Help Function
	 *  dynamic add column to table
	 * @param string $table_name
	 * @param string $col_define_str
	 * @return boolean
	 */
	private function _addColumn($table_name,$col_define_str)
	{
		//$this->_dbConn->debug =1;
		$stmt = <<<eof
			alter table %s add %s
eof;
		return $this->_dbConn->Execute(sprintf($stmt,$table_name,$col_define_str));
	}
	
	/**
	 * Help Function
	 *  给栏位加备注
	 * @param string $menu_code
	 * @param string $comment_str
	 * @return boolean
	 */
	private function _addColComment($menu_code,$comment_str)
	{
		//$this->_dbConn->debug =1;
		$tablename = $this->_getName($menu_code,'flow_table');
		$stmt = str_replace('-X$X$-',$tablename,$comment_str);
		return $this->_dbConn->Execute(substr($stmt,0,-1));	
	}
		
	/**
	 * Help Function
	 * Dynamic drop table
	 *
	 * @param string $tablename
	 * @return boolean
	 */
	private function _dropTable($tablename)
	{
		if ($this->_isTableExists($tablename))
		{
			$drop_stmt = 'drop table '.$tablename;
			return $this->_dbConn->Execute($drop_stmt);
		}
		return true;
	}
	
	/**
	 * Help Function
	 *  Create View
	 * @param string $create_stmt
	 * @return boolean
	 */
	private function _createView($create_stmt)
	{
		return $this->_dbConn->Execute($create_stmt);
	}
	
	private function _dropView($viewname)
	{
		if ($this->_isViewExists($viewname))
		{
			$drop_stmt = 'drop view '.$viewname;
			return $this->_dbConn->Execute($drop_stmt);
		}
		return true;
	}
	
	/**
	 * Help Function
	 *  Create fact data view statement
	 * @param string $menu_code
	 * @return string
	 */
	private function _getCreateViewStmt($menu_code)
	{
		// only show non-processing apply form
		$stmt = <<<eof
		create or replace view udwf_%s_v as 
			select * from udwf_%s_flow_sz 
			where status > '02'
eof;
		return sprintf($stmt,$menu_code,$menu_code);
	}
	/**
	 * Help Function
	 * 检查 view 是否存在
	 *
	 * @param string $viewname
	 * @return boolean
	 */
	private function _isViewExists($viewname)
	{
		$sql = <<<eof
			select 1 from user_views where view_name = :viewname
eof;
		return $this->_dbConn->GetOne($sql,array('viewname'=>strtoupper($viewname)));
	}
	
	/**
	 * Help Function 
	 *  Get columns count by table anem
	 * @param string $tablename
	 * @return number
	 * @author Dennis 20091103
	 */
	private function _getColsCountByTableName($tablename)
	{
		$sql = <<<eof
			select count(1) from user_tab_columns where table_name = :tab_name
eof;
		return $this->_dbConn->GetOne($sql,array('tab_name'=>strtoupper($tablename)));
	}
	
	/**
	 * Help Function
	 *  新增/修改签核类型多语
	 * @param string $menu_code  程式代码
	 * @param string $lang_code  语言别代码
	 * @param string $lang_name  多语KEY
	 * @param string $lang_label 多语
	 * @return boolean
	 * @author Dennis 20090102
	 */
	private function _insertMultiLang($menu_code,$lang_code,$lang_name,$lang_label)
	{
		return $this->_replaceLang(array('program_no'=>strtoupper($menu_code),
										 'lang_code'=>$lang_code,
										 'seq'=>$lang_name,
										 'value'=>$lang_label,
										 'update_by'=>$this->_userSeqNo));
	}// end _insertMultiLang()
	
	/**
	 * Help function 
	 * @see _insertMultiLang()
	 */
	private function _updateMultiLang($menu_code,$lang_code,$lang_name,$lang_label)
	{
		$this->_insertMultiLang($menu_code,$lang_code,$lang_name,$lang_label);
	}
	
	/**
	 * Help Function
	 *	insert or update multi-language
	 * @param array $rowdata
	 * @return boolean
	 */
	private function _replaceLang($rowdata)
	{
		//$this->_dbConn->debug  = 1;
		$tableName = 'app_muti_lang';
		$keyCols   = array('program_no','name','lang_code','seq');
		$rowdata = array_merge($rowdata,array('program_no'=>self::$_multi_lang_program_no,
										      'name'=>self::$_multi_lang_key,'type_code'=>'LL'));
		$result = $this->_dbConn->Replace($tableName,
										  $rowdata,
										  $keyCols,
										  true);
		if ($result == '0') return $this->_dbConn->ErrorMsg();
		return true;
	}
	
	/**
	 * Help Function
	 *  删除签核类型多语
	 * @param string $program_no
	 * @return number
	 */
	private function _deleteApproveTypeMultiLang($seq)
	{
		//$this->_dbConn->debug =1;
		$sql = 'delete from app_muti_lang where program_no = :program_no and name=:name and seq=:seq';
		$this->_dbConn->Execute($sql,array('program_no'=>self::$_multi_lang_program_no,
										   'name'=>self::$_multi_lang_key,
										   'seq'=>$seq));
		return $this->_dbConn->Affected_Rows();
	}
	
	/**
	 * 取得有系统有哪些语言
	 *
	 * @return array
	 * @author Dennis 20091102
	 */
	public function getLangSupport()
	{
		$sql = 'select language_code,language_name from ehr_multilang_list';
		return $this->_dbConn->CacheGetArray(86400,$sql);
	}
	
	/**
	 * 取得簽核類型多語
	 *
	 * @param string $approve_type_code
	 * @return array
	 * @author dennis 20091102
	 */
	public function getApproveMultiLang($approve_type_code)
	{
		$sql = <<<eof
			select lang_code,value
			  from app_muti_lang
			 where program_no = :program_no
			   and name = :name
			   and seq = :type_code
eof;
		return $this->_dbConn->GetArray($sql,array('program_no'=>self::$_multi_lang_program_no,
												   'name'=>self::$_multi_lang_key,
												   'type_code'=>$approve_type_code));
		
	}
	
	/**
	 * Check db schema is changed
	 *
	 * @param string $menu_code
	 * @return fixed boolean or error message false_表示无变化, db schema ok
	 * @author Dennis 20091104
	 */
	public function checkDBSchema($menu_code)
	{
		// check columns changed or not defined.
		//$this->_dbConn->debug = 1;
		$r = $this->_isSchemaChanged($menu_code);
		if(false !== $r)
		{
			return $r;	
		}
		// check workflow in process table and sequence
		$workflow_tab = $this->_getName($menu_code,'flow_table');
		$workflow_seq = $this->_getName($menu_code,'flow_seq');
		$fact_view	  = $this->_getName($menu_code,'fact_view');
		$approve_view = $this->_getName($menu_code,'approve_view');
		
		// check table xxx_flow_sz 
		if (!$this->_isTableExists($workflow_tab))
		{
			//return 'workflow table is not created.';
			return $this->_errorMsg[self::WF_ERR_TAB_CREATE_FAILURE];
		}
		
		// check sequence xxx_flow_sz_s
		if (!$this->_isSequenceExists($workflow_seq))
		{
			//return  'workflow sequence is not created.';
			$this->_errorMsg[self::WF_ERR_SEQ_CREATE_FAILURE];
		}
		
		// check view xxx_v
		if (!$this->_isViewExists($fact_view))
		{
			//return  'fact data view is not created.';
			return $this->_errorMsg[self::WF_ERR_VIEW_CREATE_FAILURE];
		}
		
		// check view xxx_approve_v
		if (!$this->_isViewExists($approve_view))
		{
			//return 'workflow approve view is not created.';
			return $this->_errorMsg[self::WF_ERR_VIEW_CREATE_FAILURE];
		}
		return false;
	}
	
	/**
	 * Help Function
	 *  检查申请单栏位是否有变动(加/减)
	 *
	 * @param string $menu_code
	 * @return string
	 * @author Dennis 20091102
	 */
	private function _isSchemaChanged($menu_code)
	{
		//$this->_dbConn->debug = 1;
		$worflow_fixed_cols = 20; // workflow table 固定栏位个数
		$sql = <<<eof
			select count(*)
			  from ehr_wf_define_detail
			 where menu_code  = :menu_code
			   and company_id = :company_id
eof;
		$r1 = $this->_dbConn->GetOne($sql,array('menu_code'=>$menu_code,
												'company_id'=>$this->_companyId));
		if ($r1 == 0)
		{
			//return '尚未定义任何栏位，不能生成申请单.';
			return $this->_errorMsg[self::WF_ERR_NO_COL_DEFINED];
		}
		
		$r2 = $this->_getColsCountByTableName($this->_getName($menu_code,'flow_table'));
		if ($r2 - $worflow_fixed_cols == $r1)
		{
			return false;
		}else{
			//return '栏位有变动，请点击 "生成申请单" 按钮，重新生成申请单';
			return $this->_errorMsg[self::WF_ERR_COL_CHANGED];
		}
	}// end _isSchemaChanged()
	
	/**
	 * 提交或暂存资料
	 *
	 * @param string $menu_code
	 * @param string $emp_seqno
	 * @param array $rowdata
	 * @return mixed, boolean or array
	 * @author Dennis 20091105
	 */
	public function save($menu_code,$emp_seqno,$rowdata,$files=null)
	{
		$x = true;
		$upload_dir = '';
		$n_filename = '';
		//pr($files);
		if(count($files)>0)
		{
			$col = $this->_getFileColName($files);
			if ($files[$col]['error'] != UPLOAD_ERR_NO_FILE)
			{
				$x = false !== $this->_preUploadFile($files);
				//$upload_dir = $this->_getUploadDir();
				$upload_dir = $GLOBALS['config']['upl_dir'];
				$o_filename = $files[$col]['name'];			
				$ext		= substr($o_filename, strrpos ( $o_filename, '.' ) + 1 );
				$n_filename = $upload_dir.'/'.md5(time()).'.'.$ext;
				$rowdata[$col] = '<a href="'.$n_filename.'">'.$o_filename.'</a>';
			}
		}
		if(true == $x)
		{
			$table_name = $this->_getName($menu_code,'flow_table');
			$seq_name   = $this->_getName($menu_code,'flow_seq');
			$cols = 'udwf_'.$menu_code.'_flow_sz_id,seg_segment_no,psn_id,status';
			$flow_status = $rowdata['doaction'] == 'savedata' ? '01' : '00';
			$row_data = array('seg_segment_no'=>$this->_companyId,
							  'psn_id'=>$emp_seqno,'status'=>$flow_status);
			$vals = ':seg_segment_no,:psn_id,:status';
			$fact_cols = $this->getDetailDefine($menu_code);
			$c = count($fact_cols);
			for ($i=0; $i<$c; $i++)
			{
				foreach ($rowdata as $k=>$v) {
					if ($fact_cols[$i]['COL_NAME'] == $k)
					{
						$row_data[$k] = $v;
						$cols .= ','.$k;
						if ($fact_cols[$i]['COL_DATA_TYPE'] == 'date')
						{
							switch ($fact_cols[$i]['DATE_FORMAT'])
							{
								case '%Y-%m-%d':
									$k = 'to_date(:'.$k.',\'yyyy-mm-dd\')';
									break;
								case '%Y-%m-%d %H:%M':
									$k = 'to_date(:'.$k.',\'yyyy-mm-dd hh24:mi\')';
									break;
								case '%Y-%m-%d %H:%M:%S':
									$k = 'to_date(:'.$k.',\'yyyy-mm-dd hh24:mi:ss\')';
									break;
								default:break;
							}
							$vals .= ','.$k;
						}else{
							$vals .= ',:'.$k;
						}
					}
				}
			}
		}
		//$this->_dbConn->debug = 1;
		$ins_stmt = 'insert into '.$table_name.'('.$cols.') values('.$seq_name.'.nextval,'.$vals.')';
		$this->_dbConn->BeginTrans();
		$this->_dbConn->Execute($ins_stmt,$row_data);
		$error_msg = $this->_dbConn->ErrorMsg();
		if( 1 == $this->_dbConn->Affected_Rows())
		{
			$r = 1;
			if ($rowdata['doaction'] == 'submit')
			{
				$flow_seq   = $this->_dbConn->GetOne('select '.$seq_name.'.currval from dual');
				$apply_type = $this->getMasterDefine($menu_code);
				$r          = $this->SubmitForm($emp_seqno,
												$table_name,
												$apply_type['FLOW_TYPE_CODE'],
												$flow_seq);
				$error_msg = $this->_dbConn->ErrorMsg();
			}
			// 暂存或提交成功
			if ($r == 1 || (is_array($r) && $r['is_success'] == 'Y'))
			{
				$u = true;
				// 有附件需要上传
				if (!empty($n_filename))
				{
					$u = $this->_uploadFile($files,$upload_dir,$n_filename);
				}
				if (true == $u)
				{
					$this->_dbConn->CommitTrans();
					return $r;
				}else{
					$this->_dbConn->RollbackTrans();
					return $error_msg;
				}
			}else{
				$this->_dbConn->RollbackTrans();
				return $r;
			}
		}else{
			$this->_dbConn->RollbackTrans();
			return $error_msg;
		}
	}// end save
	
	/**
	 * Submit form to workflow
	 *
	 * @param string $emp_seqno   applicant emp seqno(psn_id)
	 * @param string $table_name  workflow table name
	 * @param string $apply_type  workflow apply type
	 * @param string $flow_seqno  workflow seqno
	 * @return array
	 * @author Dennis 20091105
	 */
	public function SubmitForm($emp_seqno,$table_name,$apply_type,$flow_seqno,$submit_form = 'Y')
	{
		//$this->_dbConn->debug = 1;
		$result = array ('error_msg' => '','is_success' => '');
		$call_pro_stmt = <<<eof
			begin begin pk_erp.p_set_segment_no(:in_company_id); end; wf.pkg_work_flow.p_save_apply(pi_seg_segment_no => :pi_seg_segment_no,pi_psn_id => :pi_psn_id,pi_table_name => :pi_table_name,pi_type => :pi_type,pi_flow_sz_id => :pi_flow_sz_id,po_errmsg => :po_errmsg,po_success => :po_success,pi_submit => :pi_submit);end;
eof;
		$stmt = $this->_dbConn->PrepareSP($call_pro_stmt);
		$this->_dbConn->InParameter($stmt,$this->_companyId, 'in_company_id',    10);
		$this->_dbConn->InParameter($stmt,$this->_companyId, 'pi_seg_segment_no',10);
		$this->_dbConn->InParameter($stmt,$emp_seqno,        'pi_psn_id',        10);
		$this->_dbConn->InParameter($stmt,$table_name,       'pi_table_name',    100);
		$this->_dbConn->InParameter($stmt,$apply_type,       'pi_type',          100);
		$this->_dbConn->InParameter($stmt,$flow_seqno,       'pi_flow_sz_id',    20);
		$this->_dbConn->InParameter($stmt,$submit_form,      'pi_submit',        20);
		
		$this->_dbConn->OutParameter($stmt,$result['error_msg'], 'po_errmsg',  2000);
		$this->_dbConn->OutParameter($stmt,$result['is_success'],'po_success', 20);
				
		$this->_dbConn->Execute ($stmt);
		return $result;
	}
	
	/**
	 * 取得自定义 workflow 类型
	 *
	 * @param string $langcode
	 * @param boolean $is_defined  true_挑已设定好的申请单的类型, false_未设定的
	 * @return array
	 * @author Dennis 20091113 update 20091123
	 */
	public function getWFAppList($langcode,$is_defined = false)
	{
		$c = $is_defined == true ? '' : ' not '; 
		$sql = <<<eof
		select a.filename                       as app_code, 
		       a.filename|| ' - ' || b.value    as app_desc 
		  from app_file a, app_muti_lang b
		 where a.filename = b.name
		   and a.report_approve10 = 'WORKFLOW'
		   and b.lang_code = '%s'
		   and $c exists
		 (select menu_code from ehr_wf_define_master where menu_code = a.filename)
eof;
		//$this->_dbConn->debug = 1;
		$sql = sprintf($sql,$langcode,strtoupper(self::$_tablePrefix));
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->GetArray($sql);
	}
	/**
	 * Get 已经定义的 workflow
	 *
	 * @return unknown
	 */
	public function getUDWFTypeList() {
		$sql = <<<eof
			select b.choice_type, a.flow_type_desc
			  from ehr_wf_define_master a, hr_signlevel b
			 where a.flow_type_seqno = b.hsl_seq
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		$r= $this->_dbConn->GetArray($sql);
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $r;
	}
	
	
	/**
	 * 取得所有自定義  workflow 的待簽,有資料的才會顯示(首頁顯示)
	 *
	 * @param string $approver_emp_seqno 簽核人員工 psn_id
	 * @return array
	 * @author Dennis 20091106
	 * 
	 */
	public function getAllWaitApprove($approver_emp_seqno)
	{
		//$this->_dbConn->debug = 1;
		$apply_type_list = $this->getMasterDefine();
		if (!is_array($apply_type_list)) return array();
		$c = count($apply_type_list);
		//
		// ??? 想辦法改 union 的方式寫會快一些
		//
		//pr($apply_type_list);
		for($i=0; $i<$c; $i++)
		{
			$approve_view_name = $this->_getName($apply_type_list[$i]['MENU_CODE'],'approve_view');
			$sql = <<<eof
				select count(1) 
				  from %s 
				 where company_id          = :company_id
				   and (approver_emp_seqno = :emp_seqno or
				        agency_emp_seqno   = :emp_seqno1)				        
				   and can_approve         = 'Y'
eof;
			$r = $this->_dbConn->GetOne(sprintf($sql,$approve_view_name),array('company_id'=>$this->_companyId,
																			   'emp_seqno'=>$approver_emp_seqno,
																			   'emp_seqno1'=>$approver_emp_seqno));
			if ($r>0)
			{
				$apply_type_list[$i]['CNT'] = $r;
			}else{
				unset($apply_type_list[$i]);
			}
		}
		$wait_approve_list = array();
		$j = 0;
		foreach ($apply_type_list as $val) {
			$wait_approve_list[$j]['MENU_CODE']      = $val['MENU_CODE'];
			$wait_approve_list[$j]['FLOW_TYPE_CODE'] = $val['FLOW_TYPE_CODE'];
			$wait_approve_list[$j]['FLOW_TYPE_DESC'] = $val['FLOW_TYPE_DESC'];
			$wait_approve_list[$j]['CNT']            = $val['CNT'];
			$j++;
		}
		//pr($wait_approve_list);
		return $wait_approve_list;
	}
	
	/**
	 * 取得申请单上的所有栏位
	 *
	 * @param string $menu_code
	 * @return array
	 * @author dennis 20091113
	 */
	public function getColsTitle($menu_code)
	{
		$form_cols = $this->getDetailDefine($menu_code);
		$c = count($form_cols);
		$cols_title = '';
		for ($i=0; $i<$c; $i++)
		{
			$cols_title .= '<th>'.$form_cols[$i]['COL_LABEL'].'</th>';
		}
		$cols['html'] = $cols_title;
		$cols['cnt']  = $c;
		return $cols;
	}
	
	/**
	 * 取得申请单栏位和值 (view flowchart 部分用到)
	 *
	 * @param string $menu_code
	 * @param array $form_data
	 * @return string html code with data
	 * @author Dennis 20091117
	 */
	public function getApplyForm($menu_code,array $form_data)
	{
		//pr($form_data);
		$form_cols = $this->getDetailDefine($menu_code);
		$c = count($form_cols);
		$apply_form = '';
		for ($i=0; $i<$c; $i++)
		{
			foreach ($form_data as $key=>$val)
			{
				//echo $form_cols[$i]['COL_NAME'].'<br/>';
				// remove the number index element
				// add by dennis 20091124
				if (!is_numeric($key) && $key == strtoupper($form_cols[$i]['COL_NAME']))
				{
					$apply_form .= '<tr><td class="column-label">'.$form_cols[$i]['COL_LABEL'];
					$apply_form .= '</td><td>'.$val.'</td></tr>';
				}
			}
		}
		//echo $apply_form;
		return $apply_form;
	}
	
	/**
	 * Get wait for approve list
	 *
	 * @param string $menu_code
	 * @param string $approver_emp_seqno
	 * @param string $flow_type_code
	 * @param string $qwhere
	 * @return array
	 * @author Dennis 20091112
	 */
	public function getWaitApproveList($menu_code,
									   $approver_emp_seqno,
									   $flow_type_code,
									   $qwhere)
	{
		$form_cols    = $this->_getApplyFormCols($this->getDetailDefine($menu_code),false);
		$form_cols    = str_replace('b.','',$form_cols);		
		$approve_view_name = $this->_getName($menu_code,'approve_view');
		$sql = <<<eof
			select $form_cols
				   company_id,
			       approve_seqno,
			       approver_emp_seqno,
			       workflow_seqno,
			       can_approve,
			       status_name,
			       dept_id,
			       dept_name,
			       emp_id,
			       emp_name,
			       '$flow_type_code' as apply_type,
			       agency_info
			  from $approve_view_name
			 where company_id       = :company_id
			   and agency_emp_seqno = :approver_emp_seqno
			   and can_approve      = 'Y'
			   $qwhere
eof;
		//$this->_dbConn->debug = 1;
		// 前台程式特殊处理,必须要用 ADODB_FETCH_BOTH mode
		$this->_dbConn->SetFetchMode(ADODB_FETCH_BOTH);
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId,
											       'approver_emp_seqno'=>$approver_emp_seqno));
	}
	
	/**
	 * Get user defined workflow apply list
	 *
	 * @param string  $menu_code
	 * @param string  $query_where
	 * @param array   $cols
	 * @param string  $who
	 * @param boolean $countrow
	 * @param number  $numrows
	 * @param number  $offset
	 * @return array
	 * @author Dennis 20091117
	 */
	public function getUDWFApply($menu_code,
								 $query_where, 
								 array $cols,
								 $who      = 'myself',
								 $countrow = false, 
								 $numrows  = -1, 
								 $offset   = -1)
	{
		$flow_table = $this->_getName($menu_code,'flow_table');
		$form_cols  = $this->_getApplyFormCols($cols);
		$form_cols  = str_replace('b.','a.',$form_cols); 
		$apply_type = $this->_getApplyType($menu_code);
		//$this->_dbConn->debug = 1;
		$sql = <<<eof
			select $form_cols
				   a.udwf_{$menu_code}_flow_sz_id as apply_flow_seqno,
			       a.seg_segment_no,
			       a.psn_id,
			       b.emp_id,
			       b.emp_name,
			       b.dept_id,
			       b.dept_name,
			       '$apply_type' as apply_type,
			       a.status flow_status,
			       a.create_date,
			       a.refuse_reason as reject_comment,
			       decode(a.status,
			              '00',
			              '暂存',
			              '01',
			              '已提交',
			              '02',
			              '流程中',
			              '03',
			              '核准',
			              '04',
			              '驳回',
			              '05',
			              '作废',
			              '06',
			              '异常') as status_name
			  from $flow_table a, ehr_employee_v b
			 where a.seg_segment_no = b.company_id
			   and a.psn_id         = b.emp_seq_no
			   and a.seg_segment_no = :company_id
			   $query_where
			   %s
			 order by a.create_date desc, b.emp_id
eof;
		//echo $sql;
		$params = array ('company_id' => $this->_companyId );
		$who_where = '';
		// 根据查资料的人员的不同，组合不同的where条件
		switch ($who) {
			case self::$_myself:
				$who_where = 'and a.psn_id = :emp_seq_no';
				$params ['emp_seq_no'] = $_SESSION['user']['emp_seq_no'];
			break;
			case self::$_assistant:
				$who_where = 'and a.create_by = :user_seq_no';
				$params ['user_seq_no'] = $this->_userSeqNo;
				break;
			case self::$_admin:
				break;
			default:break;
		}// end switch
		$sql = sprintf ($sql, $who_where, $query_where );
		//$this->DBConn->debug =true;
		if ($countrow) {
			return $this->_dbConn->GetOne ( 'select count(1) from (' . $sql . ')', $params );
		} // end if
		$this->_dbConn->SetFetchMode(ADODB_FETCH_BOTH);
		$rs = $this->_dbConn->SelectLimit ($sql,$numrows,$offset,$params );
		return $rs->GetArray ();
	} // end getOvertimeApply()
	
	/**
	 * Get 当前 user 签核过的申请单
	 *
	 * @param string $menu_code
	 * @param string $query_where
	 * @param boolean $countrow
	 * @param number $numrows
	 * @param number $offset
	 * @return array
	 * @author Dennis 20091125
	 */
	public function getApprovedByMe($menu_code,
									$query_where,
									array $cols,
									$user_emp_seqno,
									$countrow = false, 
									$numrows  = -1, 
								    $offset   = -1)
	{
		$approve_view = $this->_getName($menu_code,'approve_view');
		$form_cols    = $this->_getApplyFormCols($cols,false);
		$form_cols    = str_replace('b.','a.',$form_cols); 
		//$this->_dbConn->debug = 1;
		$sql = <<<eof
		select $form_cols
		       dept_id,
		       dept_name, 
		       emp_id, 
		       emp_name, 
		       a.status_name,
		       a.flow_type as apply_type,
		       a.workflow_seqno as apply_flow_seqno,
		       a.company_id as seg_segment_no
		  from $approve_view a
		 where a.company_id  = :company_id
		   and a.flow_status > '02'
		   and (a.approver_emp_seqno = :user_emp_seqno or
		        a.agency_emp_seqno   = :user_emp_seqno1)
		   $query_where
eof;
		$params = array ('company_id'     => $this->_companyId,
						 'user_emp_seqno' =>$user_emp_seqno,
						 'user_emp_seqno1'=>$user_emp_seqno);
		//$this->_dbConn->debug =true;
		if ($countrow) {
			return $this->_dbConn->GetOne ( 'select count(1) from (' . $sql . ')', $params );
		} // end if
		$this->_dbConn->SetFetchMode(ADODB_FETCH_BOTH);
		$rs = $this->_dbConn->SelectLimit ($sql,$numrows,$offset,$params );
		return $rs->GetArray ();
	}
	
	/**
	 * 申请人删除暂存或是已提交尚未签核过的申请单
	 *
	 * @param string $menu_code
	 * @param number $flow_seqno
	 * @return boolean
	 * @author Dennis 20091125
	 */
	public function deleteWorkflowApply($menu_code,$flow_seqno)
	{
		$table_name = $this->_getName($menu_code,'flow_table');
		// delete file before delete apply form
		// delete the attach file from server if exists
		$r = true;
		if (1 == $this->_checkAttachFileCol($table_name,$flow_seqno,$menu_code))
		{
			$r = $this->_delFile($flow_seqno,$table_name,$menu_code);
		}
		if ($r == true)
		{
			$sql = <<<eof
			delete from $table_name
			 where udwf_{$menu_code}_flow_sz_id = :flow_seqno
			   and seg_segment_no               = :company_id
eof;
			$this->_dbConn->Execute($sql,array('flow_seqno'=> $flow_seqno,
											   'company_id'=> $this->_companyId));
			$r = $this->_dbConn->Affected_Rows();
			if ($r == 1) return $r;
			return $this->_dbConn->ErrorMsg();
		}else{
			return $this->_errorMsg[self::WF_];
		}
	}// end deleteWorkflowApply()
	
	/**
	 * Help Function
	 * 	Check Attatch File Column Exists
	 * @param string $table_name
	 * @return string
	 * @author Dennis 2010-02-20
	 */
	private function _checkAttachFileCol($table_name)
	{
		$sql = <<<eof
			select 1 
			  from user_tab_cols 
			 where table_name = :table_name 
			   and column_name = 'ATT_FILE_URL'
eof;
		return $this->_dbConn->GetOne($sql,array('table_name'=>strtoupper($table_name)));
	}
	
	/**
	 * procedure p_cancel_udwf_apply(
	 * pi_seg_segment_no     varchar2, -- 公司ID
	   pi_udwf_flow_sz_id    number,   -- 自定義申请单ID
	   pi_table_name         varchar2, --flow table 名字
	   pi_type               varchar2, --申请类别
	   po_errmsg             out varchar2, -- 返回错误信息
	   po_success            out varchar2, -- 操作是否成功 Y/N
	   pi_admin_id           in varchar2 default null, -- 管理员ID
	   pi_reject_reason      varchar2 default null     --作废原因)
	 */
	/**
	 * Amdinistrator cancel workflow
	 *
	 * @param number $flow_seqno
	 * @param string $menu_code
	 * @param string $appy_type
	 * @param string $reason
	 */
	public function cancelWorkflow($flow_seqno,$menu_code,$apply_type,$reason)
	{
		//$this->_dbConn->debug = 1;
		$result = array ('error_msg' => '','is_success' => '');
		$call_pro_stmt = <<<eof
			begin begin pk_erp.p_set_segment_no(:in_company_id); end; wf.pkg_work_flow.p_cancel_udwf_apply(pi_seg_segment_no => :pi_seg_segment_no,pi_udwf_flow_sz_id => :pi_flow_sz_id,pi_table_name => :pi_table_name,pi_type => :pi_type,po_errmsg => :po_errmsg,po_success => :po_success,pi_admin_id => :pi_admin_id,pi_reject_reason => :pi_reject_reason);end;
eof;
		$table_name = $this->_getName($menu_code,'flow_table');
		$stmt = $this->_dbConn->PrepareSP($call_pro_stmt);
		$this->_dbConn->InParameter($stmt,$this->_companyId, 'in_company_id',10);
		$this->_dbConn->InParameter($stmt,$this->_companyId, 'pi_seg_segment_no',10);
		$this->_dbConn->InParameter($stmt,$flow_seqno,       'pi_flow_sz_id',    20);
		$this->_dbConn->InParameter($stmt,$table_name,       'pi_table_name',    50);
		$this->_dbConn->InParameter($stmt,$apply_type,       'pi_type',          100);
		$this->_dbConn->InParameter($stmt,$this->_userSeqNo, 'pi_admin_id',      10);
		$this->_dbConn->InParameter($stmt,$reason,           'pi_reject_reason', 255);
		
		$this->_dbConn->OutParameter($stmt,$result['error_msg'], 'po_errmsg',  2000);
		$this->_dbConn->OutParameter($stmt,$result['is_success'],'po_success', 2);
		
		$this->_dbConn->Execute ($stmt);
		return $result;
	}// end cancelWorkflow()
	
	/**
	 * procedure p_submit_udwf_apply
      Createed by Gracie at 2009/11/26
	      传入公司ID，申请单ID，
	      返回天数/时数和错误信息，如果返回的po_success为'N' ，则申请不成功
	      因为可能是申请保存过后影响天数和时数的参数有变化，所以需要重新计算一遍
	        即使 po_success为'Y' ，这时错误信息可能也有值，那可能是一些提示信息的返回
	      将自定义申请申请插入到自定义申请资料档中
	  (pi_seg_segment_no     varchar2, --公司ID
	   pi_udwf_flow_sz_id    varchar2, --自定义申请申请单ID
	   pi_table_name         varchar2,
	   pi_type               varchar2,
	   po_errmsg             out varchar2, -- 返回错误信息
	   po_success            out varchar2 -- 操作是否成功 Y/N
	   )
	 * 使用者提交暂存的 Workflow 申请
	 *
	 * @return array
	 * @author Dennis 20091126
	 */
	public function submitWorkflow($menu_code,$flow_seqno,$apply_type)
	{
		$result = array ('error_msg' => '','is_success' => '');
		$call_pro_stmt = <<<eof
			begin begin pk_erp.p_set_segment_no(:in_company_id); end; wf.pkg_work_flow.p_submit_udwf_apply(pi_seg_segment_no => :pi_seg_segment_no,pi_udwf_flow_sz_id => :pi_flow_sz_id,pi_table_name => :pi_table_name,pi_type => :pi_type,po_errmsg => :po_errmsg,po_success => :po_success);end;
eof;
		$table_name = $this->_getName($menu_code,'flow_table');
		$stmt = $this->_dbConn->PrepareSP($call_pro_stmt);
		$this->_dbConn->InParameter($stmt,$this->_companyId, 'in_company_id',10);
		$this->_dbConn->InParameter($stmt,$this->_companyId, 'pi_seg_segment_no',10);
		$this->_dbConn->InParameter($stmt,$flow_seqno,       'pi_flow_sz_id',    20);
		$this->_dbConn->InParameter($stmt,$table_name,       'pi_table_name',    50);
		$this->_dbConn->InParameter($stmt,$apply_type,       'pi_type',          100);
		
		$this->_dbConn->OutParameter($stmt,$result['error_msg'], 'po_errmsg',  2000);
		$this->_dbConn->OutParameter($stmt,$result['is_success'],'po_success', 2);
		
		$this->_dbConn->Execute ($stmt);
		return $result;
	}
	
	/**
	 * Help Function
	 *   upload file
	 * @param file handler $file
	 */
	private function _preUploadFile($files)
	{
		$file_u = $this->_getFileColName($files);
		if (count($files)>0)
		{
			$r = false;
			if ($files [$file_u]['error'] != '4') {
				//pr($files);
				//$support_type = array('jpg','jpeg','gif','png','bmp');
				$filename = $files [$file_u]['name'];
				$ext = substr ( $filename, strrpos ( $filename, '.' ) + 1 );
				$filetype = strtolower($files[$file_u]['type'] );
				// size 是以 byte 为单位
				//$size = $files[$file_u] ['size'];
				switch($files[$file_u] ['error'])
				{
					case UPLOAD_ERR_OK: // value = 0
						switch (strtolower ($ext)) {
							case 'jpg' :
								$r = ($filetype == 'image/jpeg' || $filetype == 'image/jpg' || $filetype == 'image/pjpeg');
								break;
							case 'jpeg' :
								$r = ($filetype == 'image/jpeg');
								break;
							case 'gif' :
								$r = ($filetype == 'image/gif');
								break;
							case 'png' :
								$r = ($filetype == 'image/png');
								break;
							case 'bmp' :
								$r = ($filetype == 'image/bmp');
								break;
							case 'pdf' :
								$r = ($filetype == 'application/pdf');
								break;
							case 'doc' :
								$r = ($filetype == 'application/msword');
								break;
							case 'xls' :
								$r = ($filetype == 'application/vnd.ms-excel');
								break;
							default :break;
						} // end switch()
						break;
					case UPLOAD_ERR_INI_SIZE : // value = 1 The uploaded file exceeds the upload_max_filesize directive in php.ini. 
					case UPLOAD_ERR_FORM_SIZE: // value = 2 The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. 
						$max_upload_size = ini_get('upload_max_filesize' );
						$r = false;
						showMsg($this->_errorMsg[self::WF_ERR_FILE_TOO_BIG].':' . $max_upload_size . '<br/>','error' );
						break;
					case UPLOAD_ERR_PARTIAL:// value  = 3
						showMsg($this->_errorMsg[self::WF_ERR_FILE_UPLOD_PART],'error');
						$r = false;
						break;
					/*
					case UPLOAD_ERR_NO_FILE://value = 4
						showMsg('没有检测到有附件上传.','error');
						$r = false;
						break;
					case 5:
					break;*/
				} // end if
				if (false == $r)
				{
					showMsg($this->_errorMsg[self::WF_ERR_NOT_ALLOWED_TYPE].':'.
								$filetype.'<br>'.$this->_errorMsg[self::WF_ERR_ALLOWED_TYPE].':<br/> .jpg,.jpeg,.gif,.png,.bmp,.pdf,.doc,.xsl,.ppt','error');
				}// end if
			}
		}
		return $r;
	}// end _preUploadFile()
	
	/**
	 * 
	 * @param int $error_code
	 */
	/*
	private function _fileUploadErrorMsg($error_code) {
	    switch ($error_code) {
	        case UPLOAD_ERR_INI_SIZE:
	            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
	        case UPLOAD_ERR_FORM_SIZE:
	            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
	        case UPLOAD_ERR_PARTIAL:
	            return 'The uploaded file was only partially uploaded';
	        case UPLOAD_ERR_NO_FILE:
	            return 'No file was uploaded';
	        case UPLOAD_ERR_NO_TMP_DIR:
	            return 'Missing a temporary folder';
	        case UPLOAD_ERR_CANT_WRITE:
	            return 'Failed to write file to disk';
	        case UPLOAD_ERR_EXTENSION:
	            return 'File upload stopped by extension';
	        default:
	            return 'Unknown upload error';
	    }
	} */
	
	private function _uploadFile($files,$upload_dir,$new_filename)
	{
		$file_u = $this->_getFileColName($files);
		
		if (! file_exists ( $new_filename )) {
			//Attempt to move the uploaded file to it's new place
			if (! move_uploaded_file ( $files[$file_u]['tmp_name'], $new_filename )) {
				showMsg($this->_errorMsg[self::WF_ERR_UPLOAD_FAILURE].$this->_errorMsg[self::WF_ERR_SUBMIT_FAILURE],'error' );				
			} // end if
		} else {
			showMsg($this->_errorMsg[self::WF_ERR_UPLOAD_FAILURE]. $new_filename . 
					$this->_errorMsg[self::WF_ERR_UPLOAD_DUPLICATE].$this->_errorMsg[self::WF_ERR_SUBMIT_FAILURE],'error');			
		} // end if
		return true;
	}// end _uploadFile()
	
	private function _getFileColName($files)
	{
		$f = array_keys($files);
		return $f[0];
	}// end _getFileColName()
	
	/**
	 * Get upload dir
	 *
	 * @param string $companyid
	 * @return string
	 */
	private function _getUploadDir() {
		$sql = "select parameter_value
                  from pb_parameters
                 where parameter_id = 'UPLOAD_DIR'
                   and seg_segment_no = '%s'";
		return $this->_dbConn->GetOne(sprintf($sql,$this->_companyId));
	} // end _getUploadDir()
	
	/**
	 * Help Function
	 *  Delete file when apply form deleted
	 * @param number $flow_seqno
	 * @param string $table_name
	 * @param string $menu_code
	 * @return boolean
	 * @author Dennis
	 */
	private function _delFile($flow_seqno,$table_name,$menu_code)
	{
		$sql = <<<eof
			select att_file_url 
			  from $table_name
			 where udwf_{$menu_code}_flow_sz_id = :flow_seqno
			   and seg_segment_no               = :company_id
			   and att_file_url is not null
eof;
		$filename = $this->_dbConn->GetOne($sql,array('flow_seqno'=>$flow_seqno,
										              'company_id'=>$this->_companyId));
		$b = stripos($filename,'"');
		$e = strripos($filename,'"');
		// get file full path 
		$filename = substr($filename,$b+1,$e-$b-1);
		if (is_file($filename))
		{
			//echo $filename.'<br/>';
			return unlink($filename);
		}
		return true;
	}// end _delFile()
	
	private function _getErrorMsg()
	{
		$sql = <<<eof
			select name as err_code,value as err_msg
			  from app_muti_lang
			 where program_no = 'ESNW001'
			   and type_code  = 'MP'
			   and lang_code  = :lang_code
eof;
		//$this->_dbConn->debug = 1;
		$rs = $this->_dbConn->CacheGetArray(86400,$sql,array('lang_code'=>$this->_langCode));
		$cnt = count($rs);
		for($i=0; $i<$cnt; $i++)
		{
			$rs[$rs[$i]['ERR_CODE']] = $rs[$i]['ERR_MSG'];
		}
		return $rs;
eof;
	}
	
	/**
	 * Get Error Message
	 * @param string $error_code
	 * @return string
	 * @author Dennis 2010-02-23
	 * 
	 */
	public function getErrorMsg($error_code)
	{
		return $this->_errorMsg[$error_code];
	}// end getErrorMsg()
	
}
