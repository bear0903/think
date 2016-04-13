<?php
/**
 *	Integration Authentication with Microsoft Windows Active Directory 
 *  Create By: Dennis
 *  Create Date: 2009-04-02 13:10
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAuthDBAdpater.php $
 *  $Id: AresAuthDBAdpater.php 3584 2013-11-08 02:09:47Z dennis $
 *  $LastChangedDate: 2013-11-08 10:09:47 +0800 (周五, 08 十一月 2013) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3584 $  
 \****************************************************************************/
class AresAuthDBAdapter implements AresIAuthAdapter {
	
	private $_dBConn;
	private $_username;
	private $_password;
	private $_companyid;
	
	/**
	 * Constructor of class AresAuthDBAdpater
	 *
	 * @param ADODB_oracle $db_conn
	 * @param string $company_id
	 * @param string $username
	 * @param string $password
	 */
	public function __construct(ADODB_oracle $db_conn,$company_id,$username,$password)
	{
		$this->_dBConn = $db_conn;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->_companyid = $company_id;
		$this->_username = $username;
		$this->_password = $password;
	}// end class constructor
		
	public function authenticate()
	{
		$code  = AresAuthResult::FAILURE;
		$messages[0] = '';
		$messages[1] = '';
		if (!$this->_username) {
            $code = AresAuthResult::FAILURE_IDENTITY_NOT_FOUND;
            $messages[0] = 'A username is required';
            return new AresAuthResult($code, '', $messages);
        }
		if (!$this->_password) {
            $code = AresAuthResult::FAILURE_IDENTITY_NOT_FOUND;
            $messages[0] = 'Password is required';
            return new AresAuthResult($code, '', $messages);
        }
        if(!$this->_isValidUser())
        {
        	$code = AresAuthResult::FAILURE_USER_NOT_FOUND;
            $messages[0] = 'User dose not exists.';
            return new AresAuthResult($code, '', $messages);
        }
		if(!$this->_checkPassword())
        {
        	$code = AresAuthResult::FAILURE_PASSWD_ERROR;
            $messages[0] = 'User dose not exists.';
            return new AresAuthResult($code, '', $messages);
        }
        $code = AresAuthResult::SUCCESS;
		return new AresAuthResult($code,$this->_username,$messages);
	}// end authenticate()
	
	
	/**
	 * check user exists;
	 *
	 * @return boolean
	 */
	private function _isValidUser()
	{
		$sql = <<<eof
			select 1
			  from app_users
			 where seg_segment_no = :company_id
			   and username_no_sz = :user_name
eof;
		//$this->_decrypt();
		return $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyid,
												 'username'=>$this->_username));
	}
	
	/**
	 * Validation user password
	 *
	 * @return boolean
	 */
	private function _checkPassword()
	{
		$sql = <<<eof
			select 1
			  from app_users
			 where seg_segment_no = :company_id
			   and username_no_sz = :user_name
eof;
		// $this->_decrypt(); // remark by dennis 2013/11/08
		return $this->_dBConn->GetOne($sql,array('company_id'=>$this->_companyid,
												 'username'=>$this->_username,
												 'password'=>$this->_password));
	}
	
	/**
	 * Execute DB decrypt function before password validation
	 * @return void
	 */
	/*
	private function _decrypt()
	{
		$this->_dBConn->Execute('begin dodecrypt(); end;');
	}// end _decrypt()
	*/
	
}// end class AresAuthDBAdapter
