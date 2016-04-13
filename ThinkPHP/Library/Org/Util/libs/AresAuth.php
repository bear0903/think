<?php
/**
 *	Integration Authentication with Microsoft Windows Active Directory 
 *  Create By: Dennis
 *  Create Date: 2009-04-02 13:10
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAuth.php $
 *  $Id: AresAuth.php 1831 2009-06-22 02:31:39Z dennis $
 *  $LastChangedDate: 2009-06-22 10:31:39 +0800 (周一, 22 六月 2009) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 1831 $  
 \****************************************************************************/
class AresAuth{

	/**
	 * Instance of AuthAdapter
	 *
	 * @var AresIAuthAdapter
	 */
	protected static $_instance = null;
	
	/**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
	protected function __construct(){}

	/**
	* Singletong pattern implementation makes "clone" unavailable
	* @reutrn void
	*/
	protected function __clone(){}
	
	 /**
     * Returns an instance of AresAuth
     *
     * Singleton pattern implementation
     *
     * @return AresAuth Provides a fluent interface
     */
	public static function getInstance()
	{
		if (null == self::$_instance)
		{
			self::$_instance = new self();
		}// end if
		return self::$_instance;
	}// end getInstance()

	 /**
     * Authenticates against the supplied adapter
     *
     * @param  AresIAuthAdapter $adapter
     * @return AresAuthResult
     */
    public function authenticate(AresIAuthAdapter $adapter)
    {
        $result = $adapter->authenticate();
        if ($result->isValid())
        {
        	$_SESSION['sspi']['user'] = $result->getIdentity();
        }
        return $result;
    }// end authenticate()

	public function hasIdentity()
	{
		return !$_SESSION['sspi']['user'];
	}
	/**
	 * Get Identity
	 *
	 * @return string
	 */
	public function getIdentity()
	{
		if (!isset($_SESSION['sspi']['user']) || empty($_SESSION['sspi']['user']))
		{
			return null;
		}
		return $_SESSION['sspi']['user'];
	}
	
	/**
	 * Clear Identity
	 * @return void
	 */
	public function clearIdentity()
    {
    	unset($_SESSION['sspi']['user']);
    }
}// end AresAuth
