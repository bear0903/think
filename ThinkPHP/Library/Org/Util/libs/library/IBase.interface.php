<?php
/*
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
 * @version    $Id:IBase.interface.php Dec 28, 2007 5:23:54 PM Dennis $
 */
 
 /**
 * Interface
 * define 2 empty magic methods
 * <b>__set</b> and <b>__get</b>
 * @category   eHR
 * @package    
 * @subpackage IBase
 * @copyright  (C)1980 - 2007  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
interface IBase
{
	/**
	 * 设定属性的值
	 *
	 * @param string $name   属性名称
	 * @param stting $value  属性值
	 * @access public
	 * @return void no return value;
	 * @author Dennis
	 */
	public function __set($name, $value);

	/**
	 * 取得指定属性的值
	 *
	 * @param string $name
	 * @access public
	 * @return void no return value;
	 * @author Dennis
	 */
	public function __get($name);
}
?>