<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) ARES China
 *	 
 *	Description:
 *     Desktop Module class, contain company news, employee private notes
 *     Wait for approve, absence and overtime
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresDesktop.class.php $
 *  $Id: AresDesktop.class.php 3677 2014-02-08 06:30:59Z dennis $
 *  $Rev: 3677 $ 
 *  $Date: 2014-02-08 14:30:59 +0800 (周六, 08 二月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-02-08 14:30:59 +0800 (周六, 08 二月 2014) $
 \****************************************************************************/
class AresDesktop {
	
	private $_companyID;
	private $_userSeqNo;
	private $_dBConn;
	
	const DATA_CACHE_SECONDS = 3600; // 1 HOUR
	/**
	 *   Counstructor of class AresDesktop
	 *   init property _companyID and emplyee seq no (psn_id)
	 *   @param $_companyID string, the employee's company id
	 *   @param $user_seqno string, the login user's sequence no in app_users
	 *   @return void.
	 */
	function __construct($companyID, $user_seqno = '') {
		global $g_db_sql;
		$this->_companyID = $companyID;
		$this->_userSeqNo = $user_seqno;
		$this->_dBConn    = $g_db_sql;
	} // end structor of class ARESDesktop
	
	/**
	 *   Get company un-expired news list
	 *   @param no parameters
	 *   @return array, a 2-dimensional array of records
	 *   @author: dennis
	 *   @last update:2006-09-25 16:54:44  by Dennis.Lan
	 */
	function GetCompanyNews() {
		
		$sql_string = <<<sql
                select a.seq_no,
                       a.subject,
                       a.bbs_contents,
                       to_char(a.bbs_create_date, 'yyyy/mm/dd hh24:mi:ss') as create_date,
                       to_char(a.bbs_expired_date, 'yyyy/mm/dd hh24:mi:ss') as expired_date,
                       (case
                         when greatest(ceil(sysdate - a.bbs_create_date), 3) < 4 then
                          '<font color="#FF0000"><i> New </i></font>'
                         else
                          ''
                       end) as is_new,
                       a.bbs_status,
                       b.user_desc as bbs_creator
                  from ehr_new_info_v a, app_users_base b
                 where a.company_id = :v_company_id      
                   and a.bbs_type   = 'BULLETIN'
                   and a.company_id = b.seg_segment_no
                   and a.bbs_creator= b.username
                   and a.bbs_expired_date > trunc(sysdate)
		           and rownum = 1
			     order by a.bbs_create_date desc
sql;
		//$this->_dBConn->debug=true;
		return $this->_dBConn->GetArray($sql_string, array ('v_company_id' => $this->_companyID ) );
	} // end function GetCompanyNews
	
	/**
	 * Get Employee Notices
	 * @return array
	 * @author Dennis
	 * @log
	 *    1. change cache time to 0 for dispaly the news immediately by Dennis 2013/10/30
	 */
	function GetMyNotices() {
			$sql_string = <<<sql
                select a.seq_no,
                       a.subject,
                       a.bbs_contents,
                       to_char(a.bbs_create_date, 'yyyy/mm/dd hh24:mi:ss') as create_date,
                       to_char(a.bbs_expired_date, 'yyyy/mm/dd hh24:mi:ss') as expired_date,
                       (case
                         when greatest(ceil(sysdate - a.bbs_create_date), 3) < 4 then
                          '<font color="#FF0000"><i> New </i></font>'
                         else
                          ''
                       end) as is_new,
                       a.bbs_status,
                       b.user_desc as bbs_creator
                  from ehr_new_info_v a, app_users_base b
                 where a.company_id =:v_company_id
                   and a.bbs_type = 'NOTICE'
                   and a.bbs_owner = :v_user_seqno
                   and a.company_id = b.seg_segment_no
                   and a.bbs_creator = b.username
				   and a.bbs_expired_date > trunc(sysdate)
			       and rownum = 1
                order by a.bbs_create_date desc
sql;
		//print $sql_string;
		return $this->_dBConn->GetArray($sql_string, array('v_company_id' => $this->_companyID, 'v_user_seqno' => $this->_userSeqNo ) );
	} // end function GetMyNotices()
	

	/**
	 *   Get new information(company news or user private notice) detail
	 *   @param $newsid number, the company news or private notice sequence number
	 *   @return array, an array of records
	 *   @author: dennis
	 *   @last update:2006-09-26 10:47:46  by Dennis.Lan 
	 */
	function GetNewsDetail($newsid) {
		$sql_string = <<<sql
                select a.subject,
                       a.bbs_contents,
                       to_char(a.bbs_create_date, 'yyyy/mm/dd hh24:mi:ss') as create_date,
                       to_char(a.bbs_expired_date, 'yyyy/mm/dd hh24:mi:ss') as expired_date,
                       b.user_desc as bbs_creator,
                       a.bbs_status
                  from ehr_new_info_v a, app_users_base b
                 where a.company_id = :v_company_id
                   and a.bbs_creator = b.username
                   and a.seq_no = :v_news_id
sql;
		return $this->_dBConn->CacheGetRow(self::DATA_CACHE_SECONDS,$sql_string, array ('v_company_id' => $this->_companyID, 'v_news_id' => $newsid ) );
	} // end function GetNewsDetail()
} // end class AresDesktop
