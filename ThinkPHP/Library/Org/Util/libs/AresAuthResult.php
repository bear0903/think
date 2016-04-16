<?php
/**
 *	Integration Authentication with Microsoft Windows Active Directory 
 *  Create By: Dennis
 *  Create Date: 2009-04-02 13:10
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAuthResult.php $
 *  $Id: AresAuthResult.php 1831 2009-06-22 02:31:39Z dennis $
 *  $LastChangedDate: 2009-06-22 10:31:39 +0800 (周一, 22 六月 2009) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 1831 $  
 \****************************************************************************/
class AresAuthResult
{
    /**
     * General Failure
     */
    const FAILURE                        =  0;

    /**
     * Failure due to identity not being found.
     */
    const FAILURE_IDENTITY_NOT_FOUND     = -1;

    /**
     * Mail address not found in HR_PERSONNEL_BASE
     */
    const FAILURE_EMAIL_NOT_FOUND        = -2;

    /**
     * Failure due to invalid credential being supplied.
     */
    const FAILURE_CREDENTIAL_INVALID     = -3;

    /**
     * Failure due to domain null
     */
    const FAILURE_DOMAIN_IS_NULL         = -4;
    
     /**
     * Failure due to password error
     */
    const FAILURE_PASSWD_ERROR          = -5;
    
     /**
     * Failure due to user not exists error
     */
    const FAILURE_USER_NOT_FOUND        = -6;
    
    /**
     * Failure due to uncategorized
     */
    const FAILURE_UNCATEGORIZED         = -10;

    /**
     * Authentication success.
     */
    const SUCCESS                        =  1;

    /**
     * Authentication result code
     *
     * @var int
     */
    protected $_code;

    /**
     * The identity used in the authentication attempt
     *
     * @var mixed
     */
    protected $_identity;

    /**
     * An array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this should be an empty array.
     *
     * @var array
     */
    protected $_messages;

    /**
     * Sets the result code, identity, and failure messages
     *
     * @param  int     $code
     * @param  mixed   $identity
     * @param  array   $messages
     * @return void
     */
    public function __construct($code, $identity, array $messages = array())
    {
        $code = (int) $code;

        if ($code < self::FAILURE_UNCATEGORIZED) {
            $code = self::FAILURE;
        } elseif ($code > self::SUCCESS ) {
            $code = 1;
        }// end if

        $this->_code     = $code;
        $this->_identity = $identity;
        $this->_messages = $messages;
    }

    /**
     * Returns whether the result represents a successful authentication attempt
     *
     * @return boolean
     */
    public function isValid()
    {
        return ($this->_code > 0) ? true : false;
    }

    /**
     * getCode() - Get the result code for this authentication attempt
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Returns the identity used in the authentication attempt
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * Returns an array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}