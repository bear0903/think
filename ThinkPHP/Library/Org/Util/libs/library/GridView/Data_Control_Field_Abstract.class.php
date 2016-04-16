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
 * @subpackage Data_Control_Field_Abstract
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id: Data_Control_Field_Abstract.class.php 2797 2007-12-25 02:09:30 PM Dennis$
 */
/**
 * GridView Column Field 抽象类,所有的 Field都会继承此类
 * @category   eHR
 * @package    GridView
 * @subpackage Data_Control_Field_Abstract
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
require_once 'Base.class.php';
abstract class Data_Control_Field_Abstract extends Base 
{
	/**
	 * default column width
	 * @var const int
	 * @access public
	 */
	const DEFAULT_WIDTH = 100;

	/**
	 * default column data type
	 * @var const string
	 * @access public
	 */
	const DEFAULT_DATATYPE = 'string';

	/**
	 * 栏位类型, 如 text,input_text,image,checkbox,
	 * @var string
	 * @access pubic
	 */
	public $fieldType;

	/**
	 * 栏位资料类型,如 int,date,boolean,
	 * @var string
	 * @access pubic
	 */
	public $dataType;
	
	/**
	 * 栏宽
	 * @var int
	 * @access pubic
	 */
	public $width;

	/**
	 * 栏高
	 * @var int
	 * @access public
	 */
	public $height;

	/**
	 * 字体名称
	 * @var string
	 * @access public
	 */
	public $fontName;

	/**
	 * 字体颜色
	 * @var string
	 * @access public
	 */
	public $fontColor;

	/**
	 * 背景颜色
	 * @var string
	 * @access public
	 */
	public $bgColor;
	
	/**
	 * 栏位名称
	 * @var string
	 * @access public
	 */
	public $name;
	
	/**
	 * style 名称
	 * @var string
	 * @access public
	 */
	public $className;
	
	/**
	 * 对齐方式
	 * @var string
	 * @access public
	 */
	public $align;
	
    /**
     * Initilization fied properties
     * @param array $config field properties array
     */
	protected abstract function _init($config);
	
	/**
	 * 输出最后组合好的 field html code
	 * @param  no parameters
	 * @abstract
	 * @access public
	 * @author Dennis
	 */
	public abstract function output();
}// end class Data_Control_Field_Abstract
?>