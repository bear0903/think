<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
*  Created By Dennis Lan, Lan Jiangtao
*  Description:
*     宿舍在线查房
*  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/bonus_DB.php $
*  $Id: bonus_DB.php 3144 2011-07-29 07:11:00Z dennis $
*  $Rev: 3144 $
*  $Date: 2011-07-29 15:11:00 +0800 (Fri, 29 Jul 2011) $
*  $Author: dennis $
*  $LastChangedDate: 2011-07-29 15:11:00 +0800 (Fri, 29 Jul 2011) $
****************************************************************************/


class AresDorm {
	
	/**
	 * Database Handler
	 * @var object
	 */
	private $_dbConn;
	
	/**
	 * Username
	 * @var string
	 */
	private $_username;
	
	/**
	 * Company ID
	 * @var string
	 */
	private $_companyId;

	const DB_CACHE_SECOND = 0; // 1 hours
	
	/**
	 * Constructor of class AresDorm
	 * init some vars
	 */
	public function __construct($companyid,$username)
	{
		global $g_db_sql;
		$this->_dbConn 	  = $g_db_sql;
		$this->_companyId = $companyid;
		$this->_username  = $username;
		
		$this->_initParams();
	}
	
	/**
	 * init the database parameter
	 */
	private function _initParams()
	{
		$stmt = 'begin pk_erp.p_set_segment(:companyid);pk_erp.p_set_username(:username);end;';
		$this->_dbConn->Execute($stmt,array('companyid'=>$this->_companyId,'username'=>$this->_username));
	}
	
	/**
	 * Get dorm some parameters by the parameter type
	 * @param string $type
	 * @return array
	 * @author Dennis 2013/08/12
	 */
	private function _getParamByType($type)
	{
		$sql = <<<eof
		   select b.dorm_code_id as param_id,
			 	  b.dorm_name    as param_desc
			 from hr_dorm_type a, hr_dorm_code b
			where a.seg_segment_no = b.seg_segment_no
				 and a.dorm_type_id = b.dorm_type_id
				 and a.dorm_type = :dorm_type
				 and a.seg_segment_no = :company_id
eof;
		//$this->_dbConn->debug = 1;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM); 
		$r =  $this->_dbConn->CacheGetArray(self::DB_CACHE_SECOND,$sql,array('dorm_type'=>$type,'company_id'=>$this->_companyId));
		return $r;
	}
	
	/**
	 * Get Area list
	 * @return multitype:
	 */
	public function getAreaList()
	{
		return $this->_getParamByType('AREA_CODE');
	}
	
	/**
	 * Get Bed Status List
	 * @return multitype:
	 */
	public function getBedStatusList()
	{
		return $this->_getParamByType('BED_STATUS');
	}
	
	/**
	 * Get 栋别清单
	 * @param $areacode  string, the area code of dorm
	 * @return array
	 */
	public function getBuildingGrpByArea($areacode)
	{
		$sql = <<<eof
			select  distinct dong_do as p_id,dong_do as p_desc
			  from  hr_room_info
			 where  area = :areacode 
			   and  seg_segment_no = :companyid
eof;
		//$this->_dbConn->debug = 1;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM); 
		return $this->_dbConn->CacheGetArray(self::DB_CACHE_SECOND,$sql,array('areacode'=>$areacode,'companyid'=>$this->_companyId));
	}

	public function getBuildingByGrp($areacode,$building_grp_no)
	{
		$sql = <<<eof
			select  distinct floor as p_id,floor as p_desc
			  from  hr_room_info
			 where  area = :areacode
			   and  dong_do = :building_grp_no
			   and  seg_segment_no = :companyid
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM); 
		return $this->_dbConn->CacheGetArray(self::DB_CACHE_SECOND,$sql,array('areacode'=>$areacode,
			'building_grp_no'=>$building_grp_no,
			'companyid'=>$this->_companyId));
	}

	
	/**
	 * Get Room Emp Bed List
	 * 
	 * @param string $checkdate
	 * @param number $times 
	 * @param string $areacode
	 * @param string $building_grp_no
	 * @param string $building_no
	 * @param string $room_no
	 * @return array
	 * @author Dennis 2013/08/13
	 * 
	 */
	public function getRoomEmpList($checkdate,$times,$areacode,$building_grp_no,$building_no,$room_no)
	{
		$sql = <<<eof
		select room_by_dept,
		       room_by_shiftid,
		       room_by_shift_desc,
		       emp_seqno,
		       emp_id,
		       emp_name,
		       idcard_no,
		       gender,
		       check_date,
		       area_code,
		       area_desc,
		       building_grp_no,
		       building_no,
		       room_no,
		       bed_no,
		       bed_status,
		       bed_status_desc,
		       fact_emp_id,
		       fact_idcard_no,
		       fact_emp_name,
		       fact_gender,
		       comments,
			   room_bed_master_seq,
			   room_bed_seq
		  from hr_dorm_check_online_v
		 where check_date = to_date(:check_date,'yyyy-mm-dd')
		   and area_code = :area_code
		   and building_grp_no = :building_grp_no
		   and building_no = :building_no
		   and room_no = :room_no
		   and check_times = :times
		   and company_id = :company_id
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetArray($sql,array('check_date'=>$checkdate,
			'area_code'=>$areacode,
			'building_grp_no'=>$building_grp_no,
			'building_no'=>$building_no,
			'room_no'=>$room_no,
			'times'=>$times,
			'company_id'=>$this->_companyId));
	}
	
	/**
	 * 更新查房人信息
	 * @param number $check_room_master_seqno
	 * @param number $emp_seqno
	 * @param string $emp_name
	 */
	
	/**
	 * Update the checker info
	 * @param number $check_room_master_seqno	check room master seqno
	 * @param number $emp_seqno	emp seqno
	 * @param string $emp_id	emp id
	 * @param string $emp_name	emp name
	 * @param string $type		check type for get the update target table only, default 'ROOM'
	 */
	private function _rewriteCheckedBy($check_room_master_seqno,$emp_seqno,$emp_id,$emp_name,$type = 'ROOM')
	{
		$tabname = $type == 'ROOM' ? 'hr_check_room' : 'hr_health_check_detail';
		$pk = $type == 'ROOM' ?  'check_room_id' : 'check_health_detail_id';
		$sql = <<<eof
			update $tabname
		       set check_psn_id = :emp_seqno,
				   check_id_no  = :emp_id,
				   check_name   = :emp_name,
				   update_date	= sysdate,
				   update_by	= :user_seqno,
				   update_prom  = 'ESS_ONLINE_CHECK'
		    where  $pk = :master_seqno
			  and seg_segment_no= :company_id
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->Execute($sql,array('emp_seqno'=>$emp_seqno,
										 'emp_id'=>$emp_id,
										 'emp_name'=>$emp_name,
										 'master_seqno'=>$check_room_master_seqno,
										 'company_id'=>$this->_companyId,
										 'user_seqno'=>$this->_username));
	}
	
	private function _saveCheckResult($room_check_data)
	{
		// yes, it's rmark, not remark
		$room_check_data = array_merge(array('companyid'=>$this->_companyId,'user_name'=>$this->_username),$room_check_data);
		$sql = <<<eof
			update hr_check_room_detail
 			  set  id_no_sz = :fact_emp_id,
 				   name_sz = :fact_emp_name,
 				   sex = :fact_gender,
				   id_card = :fact_idcard,
				   rmark = :comments,
				   bed_status = :bed_status,
				   update_by = :user_name,
				   update_date = sysdate,
				   update_prom = 'ESS_ONLINE_CHECK'
 			 where check_room_detail_id = :room_bed_seq
 			   and check_room_id = :room_bed_master_seq
 			   and seg_segment_no = :companyid
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->Execute($sql,$room_check_data);
	}
	/**
	 * Save check item score 
	 * @param array $room_check_data
	 * @return boolean
	 * @author Dennis 2013/08/19
	 */
	private function _saveItemChkResult($room_check_data)
	{
		// yes, it's rmark, not remark
		$room_check_data = array_merge(array('companyid'=>$this->_companyId,'user_name'=>$this->_username),$room_check_data);
		$sql = <<<eof
			update hr_health_result
 			  set  actual_number = :assess_score,
				   rmark = :comments,
				   update_by = :user_name,
				   update_date = sysdate,
				   update_prom = 'ESS_ONLINE_CHECK'
 			 where health_result_id = :detail_seqno
 			   and health_detail_id = :master_seqno
 			   and seg_segment_no = :companyid
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->Execute($sql,$room_check_data);
	}
	
	/**
	 * 保存查房资料
	 * @param number $check_master_seqno
	 * @param string $emp_seqno
	 * @param string $emp_id
	 * @param string $emp_name
	 * @param array $room_check_data
	 * @return boolean
	 * @author Dennis
	 */
	public function saveRoomCheckResult($check_master_seqno,$emp_seqno,$emp_id,$emp_name,$room_check_data)
	{
		if (is_array($room_check_data) && count($check_master_seqno)>0){
			$this->_rewriteCheckedBy($check_master_seqno,$emp_seqno,$emp_id,$emp_name);
			$r = true;
			$effective_rows = 0;
			foreach ($room_check_data as $row)
			{
				if($r) {
					$r = $this->_saveCheckResult($row);
					$effective_rows++;
				}else{
					return false;
				}
			}
			return $effective_rows;
		}
		return false;
	}
	
	/**
	 * Get room check item by room number
	 * @param string $checkdate
	 * @param number $times
	 * @param string $areacode
	 * @param string $building_grp_no
	 * @param string $building_no
	 * @param string $room_no
	 * @return array
	 * @author Dennis 2013/08/13
	 *
	 */
	public function getCheckItemByRoom($checkdate,$times,$areacode,$building_grp_no,$building_no,$room_no)
	{
		$sql = <<<eof
			select room_by_dept,
				   room_by_shift_desc,
					 area_code,
					 building_grp_no,
					 building_no,
					 check_date,
					 master_seqno,
					 detail_seqno,
					 check_times,
					 room_id,
					 room_no,
					 check_item_id,
					 check_item_desc,
					 item_score,
					 assess_score,
					 comments
		  from hr_dorm_sanitation_check_v
		 where check_date = to_date(:check_date,'yyyy-mm-dd')
		   and area_code = :area_code
		   and building_grp_no = :building_grp_no
		   and building_no = :building_no
		   and room_no = :room_no
		   and check_times = :times
		   and company_id = :company_id
eof;
		//$this->_dbConn->debug = 1;
		return $this->_dbConn->GetArray($sql,array('check_date'=>$checkdate,
			'area_code'=>$areacode,
			'building_grp_no'=>$building_grp_no,
			'building_no'=>$building_no,
			'room_no'=>$room_no,
			'times'=>$times,
			'company_id'=>$this->_companyId));
	}
	
	/**
	 * 保存查Wei生资料
	 * @param number $check_master_seqno
	 * @param string $emp_seqno
	 * @param string $emp_id
	 * @param string $emp_name
	 * @param array $room_check_data
	 * @return boolean
	 * @author Dennis
	 */
	public function SaveSanitationChkReslut($check_master_seqno,$emp_seqno,$emp_id,$emp_name,$room_check_data)
	{
		if (is_array($room_check_data) && count($check_master_seqno)>0){
			$this->_rewriteCheckedBy($check_master_seqno,$emp_seqno,$emp_name,'WS');
			$r = true;
			$effective_rows = 0;
			foreach ($room_check_data as $row)
			{
				if($r) {
					$r = $this->_saveItemChkResult($row);
					$effective_rows++;
				}else{
					return false;
				}
			}
			return $effective_rows;
		}
		return false;
	}
	
}
