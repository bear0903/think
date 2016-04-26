<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) ARES International Inc.
 *	Description:
 *     Login User Class
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresUser.class.php $
 *  $Id: AresUser.class.php 3819 2014-08-12 08:42:07Z dennis $
 *  $Rev: 3819 $
 *  $Date: 2014-08-12 16:42:07 +0800 (閸涖劋绨�, 12 閸忣偅婀� 2014) $
 *  $Author: dennis $
 *  $LastChangedDate: 2014-08-12 16:42:07 +0800 (閸涖劋绨�, 12 閸忣偅婀� 2014) $
 \****************************************************************************/

namespace Org\Util\libs;
use Think\Controller;
use Think\Mode;

class AresUser
{
    // private variables
    private $_companyId; // login user company id
    //private $_userSeqNo; // login user seq no in user table(app_users)
    private $_userName;  // login user id
    private $_dBConn;    // database connection handle

    /**
     * constructor of class AresUser
     *
     * @param string $company_id
     * @param string $user_name
     */
    function __construct($company_id,$user_name) {
        global $g_db_sql;
        $this->_dBConn    = $g_db_sql;
        $this->_companyId = strtoupper($company_id);
        $this->_userName  = strtoupper($user_name);
        //$this->_dBConn->debug = true;
    }

    /**
    *   @desc regiser current logon user info after login success.
    *   @param  $user_seq_no string login user's seq no
    *   @return no return value, call store procedure only
    *   @author: Dennis.lan
    *   @last Update:2006-05-23 14:30:19 by Dennis.lan
    *   @log
    *       1. add database link for BIS system
    */
    function RegisterUser($user_seq_no) {
        $plsql_stmt = 'begin pk_erp.p_set_date(sysdate);pk_erp.p_set_segment_no(:company_id);pk_erp.p_set_username(:user_seq_no);end;';
        $this->_dBConn->Execute($plsql_stmt, array ('company_id'  => $this->_companyId,
                                                   'user_seq_no' => $user_seq_no));
    } // end function RegisterUser()

    /**
    *   Check user exists
    *   @retrun mix, if exists return array else return false;
    *   @author dennis
    *   last update 2006-01-10 13:09:36 by dennis
    *   lastupdate by dennis 2011-11-08
    *   Reason: 閸旂娀娲熼懕铚傛眽閸濃�茬瑝閸欑枮ogin 闂勬劕鍩�
    */
    function IsUserExits() {
		//$this->_dBConn->debug = true;
        $sql_string = <<<eof
            select 1 as is_exists
              from app_users_base
             where upper(seg_segment_no) = :company_id
               and upper(username_no_sz) = :user_name
               and pk_history_data.f_get_value(seg_segment_no,psn_id, sysdate, 'E') = 'JS1'
eof;
        //echo $sql_string;exit;
        $is_exists = $this->_dBConn->GetOne($sql_string,
                                     array('company_id'=>$this->_companyId,
                                           'user_name'=>$this->_userName));
        if (intval($is_exists) == 1) {
            return true;
        }// end if
        return false;
    }// end function IsUserExits()
	/**
	*	Get current login user profile
	*	@param $password string, login user passowrd
	*	@return user info array
	*	@author dennis 2006-2-18
	*	@last update 2006-2-18 10:59
	*/
    function GetUserInfo()
	{
    	$mod = M();
        $sql_string = "
            select a.seg_segment_no    as company_id,
                   a.username          as user_seq_no,
                   a.psn_id            as user_emp_seq_no,
                   a.username_no_sz    as user_name,
                   a.user_desc         as user_desc,
                   b.emp_id            as user_emp_id,
                   b.emp_name          as user_emp_name,
				   b.sex               as sex,
			       b.dept_id           as dept_id,
			       b.dept_seq_no       as dept_seqno,
			       b.dept_name         as dept_name,
                   a.email             as email,
                   b.title_id          as title_id,
                   b.title_name        as title_name,
			       b.title_grade       as title_level,
			       b.join_date         as join_date
              from app_users_base a, ehr_employee_v b
             where upper(a.seg_segment_no) = '".$this->_companyId."'
               and upper(a.username_no_sz) = '".$this->_userName."'
			   and a.seg_segment_no = b.company_id(+)
			   and a.psn_id = b.emp_seq_no(+)
";
// 		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
//         return $this->_dBConn->GetRow($sql_string, array ('company_id' => $this->_companyId,
// 											              'user_name'  => $this->_userName));
		$userInfo = $mod->query($sql_string);
		//dump($sql_string);exit();
        return $userInfo;
    }// end function GetUserInfo()

   /**
    * 濡拷閺屻儱缍嬮崜锟� Login User閺勵垯绗夐弰顖欏瘜缁狅拷 (娴犺缍嶆稉锟芥稉顏堝劥闂傘劎娈戞稉鑽ゎ吀,閸楀厖绗夐弰顖涙拱闁劑妫惃鍕瘜缁犫�茬瘍閸欘垯浜�)
    *
    * @param string $user_emp_seq_no
    * @return boolean true_閺勵垶鍎撮梻銊ゅ瘜缁狅拷 false_闂堢偤鍎撮梻銊ゅ瘜缁狅拷
    * @author Dennis 2008-09-17 rewrite
    */
    /*
    function IsManager($user_emp_seq_no)
	{
		// modify by Dennis 2009-03-04, 閸欐牗绉烽弰顖欏瘜閹靛秷鍏榣ogin MSS 閻ㄥ嫰妾洪崚锟�
		return true;
		//$this->_dBConn->debug = true;
		// 閸欘亣顩﹂弰顖涚厙娑擃亪鍎撮梻銊ф畱娑撹崵顓哥亸鍗炲讲娴狅拷
        $sql = <<<eof
            select count(1)
              from ehr_department_v a, ehr_employee_v b
             where a.company_id = :company_id
               and a.dept_type = 'DEPARTMENT'
               and a.company_id = b.company_id
               and a.dept_seq_no = b.dept_seq_no
               and (a.mgr1_emp_seq_no = :user_emp_seq_no or
               		a.mgr2_emp_seq_no = :user_emp_seq_no)
eof;
        $is_mgr = $this->_dBConn->GetOne($sql,array (
            'company_id'      => $this->_companyId,
            'user_emp_seq_no' => $user_emp_seq_no
        ));
		if ($is_mgr>0) return true;
		return false;
    }// end function IsManager*/
    public function IsManager($user_emp_seqno)
    {
    	$sql = <<<eof
    		select count(1)
			  from gl_segment
			 where seg_segment_no = :company_id
			   and segment_type = 'DEPARTMENT'
			   and (fa_parent_department = :user_emp_seqno1 or
			       leader_emp_id = :user_emp_seqno2)
eof;
		$r = $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId,
										 	   'user_emp_seqno1'=>$user_emp_seqno,
										 	   'user_emp_seqno2'=>$user_emp_seqno));
		if(intval($r)<>0) return true;
    	return false;
    }// end IsManager()
    
    /**
     * is current login user is the second manager
     * @param string $user_emp_seqno
     * @return boolean
     * @author Dennis 
     */
    public function isSecondMgr($user_emp_seqno){
    	$sql = <<<eof
    		select 1
			  from gl_segment
			 where seg_segment_no = :company_id
			   and segment_type = 'DEPARTMENT'
			   and fa_parent_department = :user_emp_seqno1
    		   and segment_no = :dept_seqno
eof;
    	$r = $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId,
    			'user_emp_seqno1'=>$user_emp_seqno,
    			'dept_seqno'=>$_SESSION['user']['dept_seqno']));
    	if(intval($r)<>0) return true;
    }

    /**
    *   Change current logon user password
    *   @param string $_companyId string, current user's company id
    *   @param string $userid string ,current login user name
    *   @param string $passwd currency login user's password
    *   @return boolean if password change successfully return true, else return false;
    *   @Note new version HCP system password not case sensitive , auto to upper case
    */
    function ChangePassword($old_password, $new_password) {
        $sql = <<<eof
            update app_users
               set username_password = :new_password
             where upper(seg_segment_no) = :company_id
               and upper(username_no_sz) = :user_name
               and upper(username_password) = :old_password
eof;
		//$this->_dBConn->debug = true;
        //print $sql;
        $this->_dBConn->Execute('begin pk_erp.p_set_segment_no(:company_id); end;', array (
            'company_id'  => $this->_companyId));
        $this->_dBConn->Execute($sql, array (
                                    'new_password' => $new_password,
                                    'company_id' => $this->_companyId,
                                    'user_name'    => $this->_userName,
                                    'old_password' => strtoupper($old_password)));
        //print $this->_dBConn->ErrorMsg();
        return $this->_dBConn->Affected_Rows();
    }//end function ChangePassword

	/**
    *   Add Employee Login Note
    *   @param string $_companyId string, current user's company id
    *   @param string $userid string ,current login user name
    *   @return boolean if password change successfully return true, else return false;
    */
    function AddLoginList($user_seq_no,$login_type) {
		$sql = <<<eof
            select segment_short_name
              from gl_segment
             where segment_type = 'COMPANY'
               and segment_no = :company_id
eof;
        $companyname = $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId));
		//get_real_ip()瀵版鍩岀�广垺鍩涢惇鐔割劀ip閸︽澘娼� libs/functions.php闁插矂娼扮�规矮绠�
		$addr = get_real_ip();
		// add by dennis 20090713
		$hostname = gethostbyaddr($addr);
		//$time = date('Y-m-d H:i:s');
		$sql = <<<eof
			insert into app_system_use_historys
				   (app_use_id,
					app_use_user_id,
					app_use_user_no,
					app_use_company_id,
					app_use_company_no,
					app_use_datetime_begin,
					create_by,
					create_date,
					ip_address,
					source,
					reverse3)
				 values
				   (app_system_use_historys_s.nextval,
				    :user_seqno,
				    :emp_name,
				    :company_id,
				    :company_name,
					sysdate,
					:user_seqno1,
					sysdate,
					:ip_addr,
					:login_type,
					:hostname)
eof;
		//$this->_dBConn->debug = 1;
        $this->_dBConn->Execute($sql,array('user_seqno'=>$user_seq_no,
        								   'emp_name'=>$this->_userName,
        								   'company_id'=>$this->_companyId,
        								   'company_name'=>$companyname,
        								   'user_seqno1'=>$user_seq_no,
        								   'ip_addr'=>$addr,
        								   'login_type'=>$login_type,
        								   'hostname'=>$hostname));
    }//end function ChangePassword

	/**
    *   @desc  閸掋倖鏌囬弰顖欑瑝閺勵垰鎲冲銉礉娴ｈ法鏁ら懓鍛�傞惃鍕▏閻€劏锟藉懎褰叉禒銉ょ瑝鐎电懓绨查崨妯轰紣閿涘奔绲鹃弰顖欑瑝鐎电懓绨查崨妯轰紣鐏忓彉绗夐崣顖欎簰閻ц缍� ESS
    *   @param no parameter
    *   @return boolean, if is an employee return true, else return false
    *   @author: Dennis.Lan  2006-11-20 15:46:14
    *   @last update: 2006-11-20 15:46:32  by Dennis.Lan
    */
    function IsEmployee()
    {
        $sql = <<<eof
            select 1 as is_success
              from hr_personnel_base a, app_users b
             where a.seg_segment_no = :company_id
               and upper(b.username_no_sz) = :user_name
               and a.seg_segment_no = b.seg_segment_no
               and a.id = b.psn_id
               and pk_history_data.f_get_value(:company_id,b.psn_id, sysdate, 'E') = 'JS1'
eof;
        $_isemp = $this->_dBConn->GetOne($sql, array (
                                        'company_id' => $this->_companyId,
                                        'user_name'  => $this->_userName));
        if (intval($_isemp) == 1)
        {
            return true;
        }
        return false;
    }// end function IsEmployee()
    /**
    * Check User Password
    *  1. must be a employee
    *  2. employee must be onjob status = JS1
    *  3. Password is correct
    *   @param $password mix, the user's login password.
    *   @return array if sucess else return boolean value 'false';
    *   @author: Dennis.lan
    *   @last Update:2006-02-18 14:46:40  by Dennis.lan
    */
	function ValidatePassword($password) {
        $sql = <<<eof
            select 1 as is_success
              from hr_personnel_base a, app_users b
             where a.seg_segment_no        = :company_id
               and upper(b.username_no_sz) = upper(:user_name)
               and a.seg_segment_no        = b.seg_segment_no
               and a.id                    = b.psn_id
               and pk_history_data.f_get_value(:company_id,b.psn_id, sysdate, 'E') = 'JS1'
               and upper(b.username_password) = upper(:password)
eof;
        // print $sql;
        //$this->_dBConn->debug = 1;
        $_is_success = $this->_dBConn->GetOne($sql, array ('company_id' => $this->_companyId,
									            		   'user_name'  => $this->_userName,
									            		   'password'   => $password));
        if (intval($_is_success) == 1) {
            return true;
        }// end if
        return false;
    } // end function ValidatePassword

    public function isPasswordValid($password)
    {
    	/*$this->_dBConn->Execute('begin p_set_appinfo(:DecryptKey); end;',
    							array('DecryptKey' => DECRYPT_KEY));*/
		$sql = <<<eof
            select 1 as is_success
              from app_users
             where seg_segment_no = :company_id
               and upper(username_no_sz) = :user_name
               and upper(username_password) = :password
eof;
        // print $sql;
        $_is_success = $this->_dBConn->GetOne($sql, array ('company_id' => $this->_companyId,
									            		  'user_name'  => $this->_userName,
									            		  'password'   => strtoupper($password)));
        if (intval($_is_success) == 1) {
            return true;
        }// end if
        return false;
    }

	/**
	*	Get My Favorite Application List
	*	@param $language string, language code, ZHS|ZHT|US
	*	@return array,favorite application list 2-d array recordsets
	*/
	function GetHotList($language = 'ZHS')
	{
		$sql = <<<eof
			select a.setting_value as app_id,
			       b.value         as app_name
			  from ehr_user_setting a, app_muti_lang b
			 where a.company_id = :company_id
			   and a.user_id = :user_id
			   and a.lang_code = :lang_code
			   and upper(a.setting_type) = 'HOTLIST'
			   and b.program_no = 'HCP'
			   and a.lang_code = b.lang_code
			   and a.setting_value = b.name
			 order by a.order_num
eof;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
												  'user_id'=>$this->_userName,
												  'lang_code'=>$language));
	}// end function GetHotList()
	/**
	*	Get system granted application list
	*	@param $user_seqno string , the seq no (user name) in app_users_base
	*	@param $sys_name string, current system name 'mgr' or 'ess'
	*	@param $lang string, the current user login user selected lanauge set
	*	@return array, 2-d records,my application list
	*
	*/
	function GetMenu($user_seqno,$sys_name,$lang)
	{
		//$this->_dBConn->debug = true;
		$stmt = 'begin pk_erp.p_set_segment_no(:company_id); pk_menu.p_set_language(:language);pk_erp.p_set_username(:user_seq_no);end;';
		$this->_dBConn=Execute($stmt,array('company_id'=>$this->_companyId,
										    'language'=>$lang,
										    'user_seq_no'=>$user_seqno));
		// follow statement for improve performance
		/* remark by dennis 2011-08-02 閺堫亞鏁ら崚棰佷簰娑撳娈� temporary table
		$this->_dBConn->Execute('delete from ess_userfunction_sz');
		$this->_dBConn->Execute('insert into ess_userfunction_sz
								  select rolefunction
								    from app_userfunction
								   where rolefunction_type != \'ROOT\'
								   start with userrole = :user_seq_no
								  connect by userrole = prior rolefunction',
								array('user_seq_no'=>$user_seqno));
								*/
		// Get app menu tree structure data
		$_view_name = $sys_name.'_function_menu_v'; // get view name start by mgr_ or ess_
		$sql = <<<eof
			select program_no   as nodeid,
				   program_name as nodetext,
				   parent_id    as p_nodeid,
				   program_type as nodetype
			  from $_view_name
			 where parent_id <> 'ROOT'
eof;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->GetArray($sql);
	}// end GetMenu

	/**
	 * 閸欐牕绶辩紒鍕矏閺嶏拷
	 *
	 * @param string $user_seqno
	 * @return array
	 */
	function GetOrgTree($user_seqno) {
		$stmt = 'begin pk_erp.p_set_segment_no(:company_id);pk_department.p_set_base_date(sysdate);pk_erp.p_set_username(:user_seq_no);end;';
	  	$this->_dBConn->Execute($stmt, array ('company_id' => $this->_companyId,
	  										  'user_seq_no'=> $user_seqno));
	  	$sql = <<<eof
		select t1.seg_segment_no as company_id,
		       t1.segment_no as node_seq_no,
		       t1.segment_no as dept_seq_no,
		       trim(t1.segment_no_sz) as dept_id,
		       t1.segment_name as dept_name,
		       trim(t1.segment_no_sz) as node_id,
		       t1.segment_name as node_text,
		       'F' as emp_sex,
		       '0' as emp_type,
		       nvl(t1.parent_segment_no, 'ROOT') as parent_node_id,
		       '0' as is_emp
		  from hr_dept_hierarchy_trees_v2 t1
		 where seg_segment_no = :company_id
		   and dept_mask is not null
		   and t1.parent_segment_no is null
		   and sysdate between t1.begindate and t1.enddate + 1 /*and to_number(level_code) <= to_number('10') */
		   and effective_date =
		       (select max(t2.effective_date)
		          from hr_dept_hierarchy_trees_v2 t2
		         where sysdate between t2.begindate and t2.enddate + 1 /*and to_number(t2.level_code) <= to_number('10') */
		           and t2.segment_no = t1.segment_no)
		 Order By dept_mask

eof;
	  	$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
	  	return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId));
	 }// end GetOrgTree()

	 /**
	  * Get 閺夊啴妾洪崘鍛村劥闂傘劍鍨ㄩ柈銊╂，娑撳鎲冲銉︾閸楋拷
	  *
	  * @param number $user_emp_seqno
	  * @param number $dept_seqno
	  * @return array
	  * @author
	  */
	 function GetMyStaff($user_emp_seqno,$user_seqno,$dept_seqno = null)
	 {
	 	//$this->_dBConn->debug = true;
	 	if($dept_seqno=='rootnode'){
	 		// modify by dennis 20090427
	 		$sql = <<<eof
		 		select department_yn, personself_yn
				  from app_usercompany_v
				 where appusr_seg_segment_no = :company_id
				   and username_no_sz        = :user_name
eof;
			//$this->_dBConn->debug = true;
			$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
			$userPermissionArr = $this->_dBConn->GetRow($sql,array('company_id'=>$this->_companyId,
																   'user_name'=>$this->_userName));
			if($userPermissionArr['DEPARTMENT_YN']=='N' &&
			   $userPermissionArr['PERSONSELF_YN']=='N'){
				$do='rootDeptAllBelow';
			}else{
				$do='rootDeptPermission';
			}
	 	}else{
	 		$arr = explode('_',$dept_seqno);
	 		$do  = $arr[0];
	 		$dept_seqno = $arr[1];
	 		if(isset($arr[2]) && $arr[2]=='N') $do = 'emp';
	 	}
	 	// regiter user information
	 	$this->RegisterUser($user_seqno);
	 	$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		switch ($do){
			case 'rootDeptAllBelow':
				// 鐠╁洦鏋″▎濠囨鐟奉厼鐣炬稉顓熺煈閺堝艒鐎规岸鍎撮梺锟藉▎濠囨閿涘矁瀚㈠銈勬眽閺勵垶鍎撮梺锟芥稉鑽ゎ吀,闂嬫劘艒妞ゎ垳銇氶張顒勫劥闂侊拷
				$sql = <<<eof
				select segment_no    as dept_seqno,
				       segment_no_sz as dept_id,
				       segment_name || '(' || segment_no_sz || ')' as dept_name,
				       'Y'           as is_below
				  from gl_segment
				 where seg_segment_no = :company_id
				   and begindate <= trunc(sysdate)
				   and (enddate is null or enddate >= trunc(sysdate))
				   and (leader_emp_id        = :emp_seqno or
				        fa_parent_department = :emp_seqno1)
				 order by segment_no_sz
eof;
				return $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
														   'emp_seqno'=>$user_emp_seqno,
														   'emp_seqno1'=>$user_emp_seqno));
				break;
			case 'rootDeptPermission':
				// 闂堢偛鍎忛懛顏囬煩濞嗗﹪妾�, 閺堝宸垮▎濠囧劥闂侊拷濞嗗﹪妾�
				$sql = <<<eof
				select gs.segment_no    as dept_seqno,
				       gs.segment_no_sz as dept_id,
				       gs.segment_name || '(' || gs.segment_no_sz || ')' as dept_name,
				       b.is_below       as is_below
				  from app_usercompany_v  a,
				       app_userdepartment b,
				       gl_segment         gs
				 where a.appusr_seg_segment_no = b.appusr_seg_segment_no
				   and a.appusr_username       = b.appusr_username
				   and b.appusr_seg_segment_no = gs.seg_segment_no
				   and b.department            = gs.segment_no
				   and gs.begindate < sysdate
				   and (gs.enddate is null or gs.enddate > trunc(sysdate))
				   and a.appusr_seg_segment_no = :companyid
				   and a.personself_yn         = 'N'
				   and a.username_no_sz        = :username
				 order by gs.segment_no_sz
eof;
				return $this->_dBConn->GetArray($sql,array('companyid'=>$this->_companyId,
				                                           'username'=>$this->_userName));

				break;
			case 'dept':
				 // 閺嶈鎽� Root Node 娑撳鈥欑粈铏规畱闁劑鏉搁崢璇插絿娑撳娈犻柈銊╂澑(濞嗗﹪妾洪崗褏娈�)
				$sql = <<<eof
					select segment_no    as dept_seqno,
					       segment_no_sz as dept_id,
					       segment_name || '(' || segment_no_sz || ')' as dept_name
					  from gl_segment
					 where seg_segment_no    = :company_id
					   and parent_segment_no = :p_dept_seqno
					   and begindate < sysdate
					   and (enddate is null or enddate > trunc(sysdate))
					   and pk_user_priv.f_dept_priv(segment_no) = 'Y'
					 order by segment_no_sz
eof;
				 $arrDept= $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
				 											   'p_dept_seqno'=>$dept_seqno));
			case 'emp':
				///鐠囧鍎撮梻銊ょ瑓閻ㄥ嫪姹夐崨锟�
				/* Add ename for 娴兼澹� by dennis 2010-12-06*/
				/* 閺嶈宓� define.ini 娑擃厾娈� config 鐠佹儳鐣鹃弶銉ュ枀鐎规碍妲搁崥锔芥▔缁�锟� ename */
				$emp_name_col = isset($GLOBALS['config']['staff']['show_ename']) &&
								$GLOBALS['config']['staff']['show_ename'] == 'Y' ?
								"name_sz||' '||ename" : 'name_sz';

				// modify by dennis 20101215 缁楊兛绨╂稉鑽ゎ吀閻ц缍嶉弮璺哄箵闂勩倗顑囨稉锟芥稉鑽ゎ吀鐠у嫭鏋�
				$where = $this->isSecondMgr($_SESSION['user']['emp_seq_no']) ? 
						 ' and nvl(b.leader_emp_id, 0) != a.id '              : 
						 '';
				$sql = <<<eof
					select a.id_no_sz       as emp_id,
					       trim($emp_name_col)  as emp_name,
					       a.id             as emp_seqno,
					       a.sex            as sex,
					       a.seg_segment_no as company_id
					  from hr_personnel_base a, gl_segment b
					 where a.seg_segment_no = b.seg_segment_no
					   and a.seg_segment_no_department = b.segment_no
					   and a.seg_segment_no = :company_id
					   and a.seg_segment_no_department    = :dept_seqno
					   and pk_user_priv.f_user_priv(a.id) = 'Y'
					   and (a.outdate is null or a.outdate >= trunc(sysdate))
					   and a.indate <= trunc(sysdate)
					   $where
					 order by id_no_sz
eof;

				$arrEmp= $this->_dBConn->GetArray($sql,array('company_id'=>$this->_companyId,
															 'dept_seqno'=>$dept_seqno));
				if(!empty($arrDept)) return  array_merge($arrEmp,$arrDept);
				return  $arrEmp;
				break;
		}
	 }// end GetMyStaff()

	 /**
	  * Enter description here...
	  *
	  * @param unknown_type $alertAnswerString
	  * @return unknown
	  * @last update by dennis 2010-02-02
	  *  note: uppper(user_name)
	  */
	 function ValidatePwdAlert($alertAnswerString){
	 	$sql = 'select 1
				  from ehr_md_sys_setting
				 where company_no = :company_id
				   and upper(user_name)  = :user_name
				   and pwd_answer = :pwd_answer';
	 	//echo $sql;
	 	//$this->_dBConn->debug = true;
		return $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId,
											    'user_name'=>$this->_userName,
											    'pwd_answer'=>$alertAnswerString));

	 }

	/**
	*	Get lost password, re-gernater an new password, and send a e-mail to
	*	@param $email string , user e-mail address
    *   @return boolean value
    *   @author: dennis 2006-03-08 16:54:19
    *   @last update: 2009-06-03 18:18:28  by dennis
    * @ 娣囶喗鏁兼稉鍝勫涧閸︺劋濞囬悽銊拷鍛�傛稉顓㈢崣鐠囦線鍋栨禒锟�, 娴ｈ法鏁ゅ鐑樸�傞弮鎯邦洣閸旂娀鍋栨禒锟�
	*
	*/
	/*
	function ValidateEmail($email)
	{
		$sql = <<<eof
            select 1
              from app_users_base
             where seg_segment_no = :company_id
               and lower(email) = :email
               and upper(username_no_sz) = :user_name
eof;
		//$this->_dBConn->debug = 1;
		return $this->_dBConn->GetOne($sql,
									 array('company_id' =>$this->_companyId,
									       'user_name'  => $this->_userName,
										   'email'      =>strtolower($email))
			                        );
	}// end function ValidateEmail()*/
	/**
	 * 閸ョ姳璐熼悳鏉挎躬閹碉拷閺堝娈戦柇顔绘闁姤妲告禒锟� HR_PERSONNEL娑擃厽娼甸敍灞惧娴犮儴绻栨稉顏呭閸ョ偛鐦戦惍浣规閻ㄥ嫰鍋栨禒鍫曠崣鐠囦椒绡冮弨瑙勫灇娑擄拷閼凤拷
	 * @author Dennis 2013/11/04
	 * @return integer
	 */
	public function ValidateEmail($email)
	{
	    $sql = <<<eof
	       select 1
              from app_users_base a, hr_personnel_base b
             where a.seg_segment_no = b.seg_segment_no
               and a.psn_id = b.id
               and a.seg_segment_no = :company_id
               and lower(b.email) = :email
               and upper(a.username_no_sz) = :user_name
eof;
	    return $this->_dBConn->GetOne($sql,
	            array('company_id' =>$this->_companyId,
	                    'user_name'  => $this->_userName,
	                    'email'      =>strtolower($email))
	    );
	}
	/**
	*	Reset Password for lost password
	*	@param no parameter, reference to class property
	*	@return integer, if password reset successfully
	*	@author: dennis 2006-03-03 16:25:30
	*	@last update: 2006-04-14 12:16:37 by dennis
	*/
	function ResetPassword($randpassword)
	{
		$sql = <<<eof
            update app_users
               set username_password = :new_password
             where upper(seg_segment_no) = :company_id
               and upper(username_no_sz) = :user_name
eof;
        $this->_dBConn->Execute('begin pk_erp.p_set_segment_no(:company_id); end;', array (
            'company_id'  => $this->_companyId));
        $this->_dBConn->Execute($sql, array (
                                    'new_password' => $randpassword,
                                    'company_id'   => $this->_companyId,
                                    'user_name'    => $this->_userName));

        return $this->_dBConn->Affected_Rows();
	}// end function ResetPassword()

	/**
	*	Update employee logout time
	*	@param $login_type, ESS MGR BIS HCP
	*	@param no parameter, reference to class property
	*	@return integer, if user click logout button
	*	@author: jack 2006-8-22
	*/
	function UpdateLogouttime($login_type)
	{
		$company_id = $this->_companyId;
		$user_name  = $this->_userName;
		$addr = $_SERVER['REMOTE_ADDR'];
		$sql = <<<eof
            update app_system_use_historys
               set app_use_datetime_end = sysdate
			 where app_use_id in (select max(app_use_id)
                         			from app_system_use_historys
             where upper(app_use_company_id) = '$company_id'
               and upper(app_use_user_no) = '$user_name'
			   and source = '$login_type')
			   and ip_address= '$addr'
eof;
        $this->_dBConn->Execute($sql);

        return $this->_dBConn->Affected_Rows();
	}// end function UpdateLogouttime()
    /**
    *   Get 閺夊啴妾洪崘鍛畱閸樺倸灏柈銊╂，濞撳懎宕�
    *   @param $user_seqno number, login user's seqno
    *   @param $privi_type string, privilege type
    *   @return 2-d array
    *   @author: dennis 2006-05-18 18:04:47
    *   @last update:
    *       1. change department order by
    *       2. 闁劑妫弶鍐婢舵碍瀵弽蹇庣秴 dept_mask,dept_level 2006-12-06 15:16:15  by Dennis.Lan
    */
    function GetUserDataPrivileges($user_seqno,$privi_type, $where=null)
    {
    	//$this->_dBConn->debug = true;
        // add by dennis 2008-03-25
        $this->RegisterUser($user_seqno);
        $sqlstr = <<<eof
            select seq_no,
                   factory_zone_privilege,
                   title_privilege,
                   dept_privilege,
                   /*grade_privilege, 閼卞瞼鐡戦幒鍫熸綀閸樼粯甯�娴滐拷, modify by dennis 2008-03-25*/
                   only_self_privilege,
                   salary_privilege
              from ehr_user_global_privi_v
             where company_id = :company_id
               and user_seq_no= :user_seqno
eof;
        //$this->_dBConn->debug = true;
        $this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        $_r =  $this->_dBConn->GetRow($sqlstr,array('company_id'=>$this->_companyId,
                                                    'user_seqno'=>$user_seqno));
        ///pr($_r);
        $sql = '';
        if (isset($_r['ONLY_SELF_PRIVILEGE']) && $_r['ONLY_SELF_PRIVILEGE'] == 'N')
        {
            switch(strtolower($privi_type))
            {
                case 'department':
                    if  ($_r['DEPT_PRIVILEGE'] == 'Y')
                    {
                        $sql = <<<eof
                            select dept_seqno,
                                   dept_id || ' ' || dept_name as dept_name,
                                   dept_id    as deptid,
                                   dept_name  as deptname
                              from ehr_user_dept_privi_v
                             where company_id = '$this->_companyId'
                               and user_seqno = '$user_seqno'
                               $where
                             order by dept_id
eof;
                    }else{
                        $sql = <<<eof
                            select dept_seq_no,
                                   dept_id || ' ' || dept_name as dept_name,
                                   dept_id    as deptid,
                                   dept_name  as deptname
                              from ehr_department_v
                             where company_id = '$this->_companyId'
                               and dept_type = 'DEPARTMENT'
                               $where
                             order by dept_id
eof;
                    }
                    //echo $sql;
                    break;
                case 'factory_area':
                   if  ($_r['FACTORY_ZONE_PRIVILEGE'] == 'Y')
                   {
                        $sql = <<<eof
                            select zone_seqno, zone_id || ' ' || zone_name as zone_name
                              from ehr_user_zone_privi_v
                             where company_id = '$this->_companyId'
                               and user_seqno = '$user_seqno'
                               order by zone_id
eof;
                    }else{
                        $sql = <<<eof
                            select zone_setup_id,
								   zone_no || ' ' || zone_name as zone_name
                              from hr_zone_setup
                             where seg_segment_no = '$this->_companyId'
                               and is_active = 'Y'
eof;
                    }
                    break;
                case 'title':
                    if  ($_r['TITLE_PRIVILEGE'] == 'Y')
                    {
                        $sql = <<<eof
                            select title_seq_no, title_id ||' '|| title_desc as title_name
                              from ehr_user_title_privi_v
                             where company_id = '$this->_companyId'
                               and user_seqno = '$user_seqno'
                               order by title_id
eof;
                    }else{
                        $sql = <<<eof
                            select title as title_seq_no, title_no_sz ||' '|| titlename as title_name
                              from hr_title
                             where seg_segment_no = '$this->_companyId'
                             order by title_no_sz
eof;
                    }
                    break;
                /*
                 * HCP閼卞瞼鐡戦幒鍫熸綀閹锋寧甯�娴滐拷
                 * Modify by Dennis 2008-03-25
                case 'grade':
                    if  ($_r['GRADE_PRIVILEGE'] == 'Y')
                    {
                        $sql = <<<eof
                            select grade_seq_no, grade_id ||' '|| grade_desc as title_name
                              from ehr_user_grade_privi_v
                             where company_id = '$__companyId'
                               and user_seqno = '$user_seqno'
                               order by grade_id
eof;
                    }else{
                        $sql = <<<eof
                            select grade_id as greade_seq_no, grade_id ||' '|| grade as grade_name
                              from hr_grades
                             where seg_segment_no = '$__companyId'
                             order by grade_id
eof;
                    }
                    break;
                */
                default:
                    break;
            }
        }
        //print $sql.'<hr/><hr/>';
        $this->_dBConn->SetFetchMode(ADODB_FETCH_NUM);
		$r = !empty($sql) ? $this->_dBConn->GetArray($sql) : '';
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        return $r;
    }// end  function GetUserDataPrivileges()
    /**
    *   Get 缁崵绮洪崣鍌涙殶閸婏拷

    *   @param $parmatype  string, parameter type
    *   @param $$paramname string, parameter name
    *   @return 2-d array
    *   @author: dennis 2006-04-13 17:51:20
    */
    function GetSysParamVal($parmatype,$paramname)
    {
        $sqlstr = <<<eof
            select parameter_value,
                   value1,
                   value2
              from pb_parameters
             where parameter_type = :v_parameter_type
               and parameter_id   = :v_parameter_name
               and seg_segment_no = :company_id
eof;
        return $this->_dBConn->GetRow($sqlstr,array('v_parameter_type'=>$parmatype,
                                                   'v_parameter_name'=>$paramname,
                                                   'company_id'=>$this->_companyId));
    }// end function GetSysParamVal();

	/**
    *   @desc 濡拷閺屻儲妲搁崥锔芥箒閹哄牊娼堝Ο锛勭矋
    *   @param $module string, 濡紕绮嶉崥宥囆�, ESS/MGR/BIS
    *   @return boolean, if permission allowed return 1 else return null
    *   @author: Dennis.Lan  2006-11-19 00:47:10
    *   @last update: 2006-11-19 00:47:40  by Dennis.Lan
    */
    function CheckPermission($module)
    {
        $sqlstr = <<<eof
            select 1 as isallowed
              from app_userfunction a, app_users b
             where a.userrole = b.username
               and a.rolefunction like '{$module}%'
               and b.username_no_sz = :username
eof;
	   //$this->_dBConn->debug = true;
	   //pr($this->_dBConn->GetOne($sqlstr,array('username'=>$this->_userName)));
       return $this->_dBConn->GetOne($sqlstr,array('username'=>$this->_userName));
    }// end function

    /**
     * get Manager Desk Main Menu Items
     *
     * @param array $menuitem 2-d array
     * @return array
     * @author Dennis 2008-07-23
     */
    function getMainMenu(array $menuitem,$modulename)
    {
    	$main_menu = array();
    	$c = count($menuitem);
    	//pr($menuitem);
    	$j = 0;
    	if (is_array($menuitem) && $c>0)
    	{
    		for ($i=0; $i<$c; $i++)
    		{
    			if (strtoupper($menuitem[$i]['NODETYPE']) == 'MENU' &&
    				strtoupper($menuitem[$i]['P_NODEID']) == $modulename ){
    					$main_menu[$j]['href'] = $menuitem[$i]['NODEID'];
    					$main_menu[$j]['label'] = $menuitem[$i]['NODETEXT'];
    					$j++;
    				}// end if
    		}// end for loop
    	}// end if
    	return $main_menu;
    }// end getMDMainMenu()

    /**
     * 閸欙拷 Manager 鐠佹儳鐣鹃惃鍕暕鐠佹崘绻� ESS or MSS
     *
     * @return string
     * @author Dennis 2009-02-10
     *
     */
    public function getDefaultHome()
    {
        $sql = <<<eof
            select default_home
              from ehr_md_sys_setting
             where company_no = :company_id
               and user_name = :user_name
eof;
        return $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId,
        										'user_name'=>$this->_userName));
    }// end getDefaultHome()
    
    
    /**
     * detect user password changed
     * 缁楊兛绔村▎锛勬瑜版洖绻�妞ゆ槒顩︽穱顔芥暭鐎靛棛鐖滈幍宥呭讲娴犮儳娅ヨぐ锟�
     * @return int
     * @author Dennis 2012-03-02
     */
    private function _isDefaultPassword($pwd)
    {
    	return strtoupper($pwd) === strtoupper($this->_getDefaultPwd());
    }
    
    /**
     * Get Default Password
     * @return string
     * @author Dennis 2012-03-02
     */
    private function _getDefaultPwd()
    {
    	$sql = <<<eof
    		select substr(pk_crypt_sz.decryptC(b.id_card), -6) as def_pwd
			  from app_users a, hr_personnel_base b
			 where a.seg_segment_no = b.seg_segment_no
			   and a.psn_id = b.id
			   and a.username_no_sz = :user_name
			   and a.seg_segment_no = :company_id
eof;
    	$this->_dBConn->debug = 1;
    	return $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId,
    											 'user_name'=>$this->_userName));
    }
    
    /**
     * Get User Login History Count
     * @author Dennis 2012-03-02
     */
    private function _getLoginCnt()
    {
    	$sql = <<<eof
    		select count(1) as login_cnt
			  from app_system_use_historys
			 where app_use_company_id = :company_id
			   and app_use_user_id =
			       (select username from app_users where username_no_sz = :user_name)
			   and source = 'eHR'
eof;
    	//$this->_dBConn->debug =1;
    	return $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId,
    											 'user_name'=>$this->_userName));
    }
    
    /**
     * the user is first login system
     * @param $pwd string 閻ц缍嶇�靛棛鐖�
     * @return boolean
     * @author Dennis 2012-03-02
     */
    public function isFirstLogin($pwd)
    {
    	return 'N';
    	$r = $this->CheckPasswordStrength($pwd);
    	var_export($r);
    	if (!$this->CheckPasswordStrength($pwd) || 
    		$this->_isDefaultPassword($pwd) 	|| 
    		$this->_getLoginCnt()==0)
    	{
    		return 'Y';
    	}
    	return 'N';
    }
    
    /**
     * Check Password strength
     * @param string $pwd
     * @return boolean <string>
     * @author Dennis 2012-07-25
     */
    public function CheckPasswordStrength($pwd)
    {
    	// 闂�鍨
    	if (strlen($pwd) < 6)
    	{
    		return false;
    	}
    	// 韫囧懘銆忕憰浣规箒閺佹澘鐡�
    	if (!preg_match("/[0-9]/", $pwd))
    	{
    		return false;
    	}
    	// 韫囧懘銆忕憰浣规箒鐎涙鐦�
    	if (!preg_match("/[a-z]/", $pwd) && preg_match("/[A-Z]/", $pwd))
    	{
    		return false;
    	}
    	// 韫囧懘銆忕憰浣规箒閻楄鐣╃�涙顑�
    	if (!preg_match("/.[!,@,#,$,%,^,&,*,?,_,~,-,鎷�,(,)]/", $pwd))
    	{
    		return false;
    	}
    	return true;
    }
    /**
     * 妤犲矁鐦夐惂璇茬秿鐢劕褰块弰顖氭儊閺堝娅ヨぐ鏇燁劃閸忣剙寰冮惃鍕綀闂勶拷
     * @param string $username_no_sz
     * 
     */
    public function isHasCompanyPermission($username_no_sz)
    {
    	$sql = <<<eof
    		select 1
			  from app_users a, app_usercompany b
			 where a.username_no_sz = upper(:username_sz)      
			   and b.appusr_username = a.username      
			   and b.appusr_seg_segment_no = :company_id
    	
eof;
    	//$this->_dBConn->debug =1;
    	return $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyId,
    			'username_sz'=>$username_no_sz));
    }
}// end class ARESUser()
