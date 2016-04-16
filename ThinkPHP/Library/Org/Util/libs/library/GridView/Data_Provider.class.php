<?php
/**
 * eHR Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.areschina.com/license/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@areschina.com so we can send you a copy immediately.
 *
 * @category   eHR
 * @package    GridView
 * @subpackage Data_Provider
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Data_Provider.class.php 2797 2007-12-25 02:09:30 PM Dennis$
 */

/**
 * 数据库操作相关类
 * @category   eHR
 * @package    GridView
 * @subpackage Data_Provider
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 */

/**
 * Database Provider 继承自 Zend_DB
 * @category   eHR
 * @package    GridView
 * @subpackage Data_Provider
 * @see 	   Zend_Db
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
class Data_Provider extends Zend_Db
{
	private static $_dbAdpater = null;
	/**
	 * 私有建构子,此 class 不能直接实例化
	 * 透过 getDbInstance() 得到实例,单件模式(singleton pattern)
	 */
	private function __construct()
	{
	}// end class constructor __construct
	/**
	 * 得到一个 Database Handler Zend_Db_Adapater_Abstract type
	 * @param string $adapter  数据库类型
	 * @param array  $config   连结数据库的相关参数(username/password/dbname/charset)
	 * @return Zend_Db_Abstract_Adapter
	 * @author Dennis
	 */
	static public function getInstance($adapter,$config)
	{
		if (is_null(self::$_dbAdpater))
			self::$_dbAdpater = parent::factory($adapter,$config);
		return self::$_dbAdpater;
	}// end getInstance()
	
	/**
	 * 防止被克隆
	 * @param no
	 * @return no
	 * @author Dennis
	 */
	protected function __clone()
	{
		trigger_error('This class '.__CLASS__.'can not clonable.',E_USER_ERROR);
	}// end __clone()	
}// end class Data_Provider()

?>