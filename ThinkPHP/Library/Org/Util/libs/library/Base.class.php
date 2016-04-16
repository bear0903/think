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
 * @package    
 * @subpackage IBase
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id: Base.class.php 2797 2007-12-25 02:09:30 PM Dennis$
 */

/**
 * Interface IBase
 */
require_once 'IBase.interface.php';

/**
 * 实作 Interface IBase
 * 并加入判断 __get __set 的变量是否存在于 class 中
 * 
 * @category   eHR
 * @package    
 * @subpackage Base
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
class Base implements IBase {
	/**
	 * 设定属性的值
	 *
	 * @param string $name   属性名称
	 * @param stting $value  属性值
	 * @access public
	 * @return void no return value;
	 * @author Dennis
	 */
	public function __set($name, $value) {
		try {
			self::_checkPropertyExists ( $this, $name );
			$this->{$name} = $value;
		} catch ( Exception $e ) {
			self::_error ( $e );
			return;
		} // end try catch	
	} // end magic method __set()
	

	/**
	 * 取得指定属性的值
	 *
	 * @param string $name
	 * @access public
	 * @return void no return value;
	 * @author Dennis
	 */
	public function __get($name) {
		try {
			self::_checkPropertyExists ( $this, $name );
		} catch ( Exception $e ) {
			self::_error ( $e );
			return null;
		} // end try catch
		return $this->{$name};
	} // end magic method __get()
	

	/**
	 * 检查属性是否存在
	 * @param class $class class 名称 
	 * @param string $var_name 属性名称
	 * @access private
	 * @author Dennis
	 */
	private static function _checkPropertyExists($class, $var_name) {
		if (! array_key_exists ( $var_name, get_object_vars ( $class ) ))
			throw new Exception ( 'variable <b><font color="blue">' . $var_name . '</font></b> is not exists' );
	} // end _checkPropertyExists()
	/**
	 * 显示错误信息
	 * @param Exception $e 
	 * @return void, 显示错误信息及 Debug 信息.
	 * @access private
	 * @author Dennis
	 */
	private static function _error(Exception $e) {
		trigger_error ( printf ( '<font color="red"><h3>PHP Fatal Error</h3><hr size="1">Tracing Information: %s<br/>' . '错误信息: %s  程式 %s </font> ', $e->getTraceAsString (), $e->getFile (), $e->getMessage () ), E_USER_ERROR );
		if ($GLOBALS ['debug']) {
			echo '<pre>';
			print_r ( $e->getTrace () );
			echo '</pre>';
		} // end if
	} // end _error()
} // end interface IBase
?>