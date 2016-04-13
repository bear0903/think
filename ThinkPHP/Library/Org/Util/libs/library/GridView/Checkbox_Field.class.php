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
 * If you did not receive a copy o	f the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@areschina.com so we can send you a copy immediately.
 *
 * @category   eHR
 * @package    GridView
 * @subpackage Checkbox_Field
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @version    $Id: Checkbox_Field.class.php 2008-01-04 16:37:30 PM Dennis$
 */

/**
 * Checkbox 栏位
 * @category   eHR
 * @package    GridView
 * @subpackage Checkbox_Field
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
class Checkbox_Field extends Data_Control_Field_Abstract
{
    const DEFAULT_ALIGN = 'center';
    /**
     * Checkbox 选中时的值
     * @var mixed
     */
	public $checkedValue;
	
	public $dbFieldValue;
	
	public $dbFieldName;
	
	/**
	 * Checkbox 是否可以 click
	 * GridView 暂只是显示，所以预设是 false
	 * @var boolean
	 */
	public $enabled	= false;
		
	/**
	 * Checkbox class constructor
	 * @param  array $config       栏位的属性组成的数组
	 * @access public
	 */
	public function __construct(array $config)
	{
		$this->_init($config);
	}// end class constructor __construct()
	
	/**
	 * 初始化绑定栏位的属性
	 * @param  array $config       栏位的属性组成的数组
	 * @return void no return value
	 * @access private
	 * @author Dennis
	 */
	protected function _init($config)
	{
	    //echo $this->_config['db_field_name'].'<br>';
		if (is_array($config) && count($config)>0)
		{
		    //dd($config);
			foreach($config as $key=>$value)
			{
			   // echo $key .' = '.$value.'<br/>';
				switch(strtolower($key))
				{
					case 'name':
						$this->name = $value;
						break;
					case 'db_field_name':
						$this->dbFieldName = $value;
						break;
					case 'db_field_value':
						$this->dbFieldValue = $value;
						break;
					case 'checked_value':
					    $this->checkedValue = $value;
					    break;
					case 'width':
						$this->width = $value;
						break;
					case 'height':
						$this->height = $value;
						break;
					case 'data_type':
						$this->dataType = $value;
						break;
					case 'align':
						//echo $value.'<hr>';
						$this->align = $value;
						break;
					case 'bgcolor':
						//echo $value.'<- Color<br>';
						$this->bgColor = $value;
						break;
					case 'class_name':
						$this->className = $value;
						break;
					default:break;
				}// end switch
			}// end foreach
			//echo $this->align.'<br>';
			$this->align = isset($this->align) ? 
			               $this->align        : 
			               self::DEFAULT_ALIGN;
		}// end if
	}// end function _init()

	/**
	 * output filed html code
	 * @param  no
	 * @access public
	 * @author Dennis
	 */
	public function output()
	{
	    $checkbox_field_html = '<td width="'.(isset($this->width) ? $this->width : self::DEFAULT_WIDTH).'"';
		$checkbox_field_html .= (isset($this->height)    ? ' height="'.$this->height.'"' : '');	
		$checkbox_field_html .= (empty($this->align)     ? '' : ' align="'.$this->align.'"');
		$checkbox_field_html .= (empty($this->bgColor)   ? '' : ' bgcolor="'.$this->bgColor.'"');		
		$checkbox_field_html .= (empty($this->className) ? '' : ' class="'.$this->className.'"');
		$checkbox_field_html .='>';
		$checkbox_field_html .= '<input type="checkbox" ';
		$checkbox_field_html .= (empty($this->dbFieldName) ? '' : ' dbfield="'.$this->dbFieldName.'"');
		$checkbox_field_html .= (empty($this->name)        ? '' : ' name="'.$this->name.'"');
		//是否为选中状态
		$checkbox_field_html .= (isset($this->checkedValue) && $this->checkedValue == $this->dbFieldValue ? ' checked="true"' : '');
		// enabled default:false for readonly
		$checkbox_field_html .= ($this->enabled ? '' : ' disabled="true" ');
		$checkbox_field_html .='/></td>';		
		return $checkbox_field_html;
	}// end output()
}// end class Checkbox_Field

?>