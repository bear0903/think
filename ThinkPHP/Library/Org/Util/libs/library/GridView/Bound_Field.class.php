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
 * @package    GridView
 * @subpackage Bound_Field
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id: Bound_Field.class.php 2797 2007-12-25 02:09:30 PM Dennis$
 */

/**
 * 与 DB Column 对应的栏位,DB栏位值绑定此物件
 * @category   eHR
 * @package    GridView
 * @subpackage Bound_Field
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
class Bound_Field extends Data_Control_Field_Abstract
{
	/**
	 * null 值栏位显示 (如果不显示可能造成显示不好看)
	 * @var string
	 * @access public
	 */	
	public $nullValueText = '&nbsp;';
	
	/**
	 * 数据格式,如:日期 yyyy-mm-dd hh24:mi:ss
	 * @var string
	 */
	private $dataFormat;
	
	/**
	 * 数据数据库中栏位的名称
	 * @access protected
	 * @var string
	 */
	protected $dbFieldName;
	/**
	 * 数据库中此栏位存放的值
	 * @var mixed
	 * @access protected
	 */
	private $dbFieldValue;
	
	/**
	 * Bound Field 建构子, 初始化栏位所有属性
	 * @param  array $config 绑定栏位的属性,必须是属性为下标值为其属性
	 * @access pubic
	 */
	public function __construct($config)
	{	    
		$this->_init($config);
	}// end class constructor __construct()

	/**
	 * 初始化绑定栏位的属性
	 * @param  no parameters
	 * @return void no return value
	 * @access protected
	 * @author Dennis
	 */
	protected function _init($config)
	{
	    //pr($config);
	    //echo $this->_config['db_field_name'].'<br>';
		if (is_array($config) && count($config)>0)
		{
		    //dd($this->_config);
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
						//echo $value.'<br/>';
						if (isset($value))
						{
						    //echo $value.'<br/>';
						    
						    // format date & number value 
						    // 数字栏位保留小数位数以 config.inc.php 中配置为准
						    if ('number' == @$config['data_type'])
						    {
						        $num_cnt = isset($GLOBALS['config']['num_fmt'][$_GET['scriptname']][$config['db_field_name']]) ?
						                   $GLOBALS['config']['num_fmt'][$_GET['scriptname']][$config['db_field_name']] : 4;
						        //echo $_GET['scriptname'].'<br>'.$num_cnt.'<br/>';
						        $this->dbFieldValue = number_format($value,$num_cnt);
						    }else{
						        $this->dbFieldValue = formatData($value,@$config['format'],@$config['data_type']);
						    }
						    /*
						    $this->dbFieldValue = ('number' == @$config['data_type']) ? 
						    	number_format($value,$num_cnt): 
						    	formatData($value,@$config['format'],@$config['data_type']);
						    */

						}// end if
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
					case 'field_type':
						$this->fieldType = $value;
						break;
					case 'font_name':
						$this->fontName = $value;
						break;
					case 'color':
					    //echo $value;
						$this->fontColor = $value;
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
			               $this->_getAlign($this->dataType,$this->dbFieldValue);
		}// end if
	}// end function _init()
	/**
	 * 取得栏位资料预设对齐方式
	 * @param string $data_type
	 * @param string $db_value
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getAlign($data_type,$db_value)
	{
	    $al = '';
	    $data_type = strtolower($data_type);
	    //echo $data_type.'<br/>';
	    if ($data_type == 'number' || 
	        $data_type == 'int'    || 
	        $data_type == 'float'  ||
	        $data_type == 'real'   &&
	        is_numeric($db_value))
	    {
	        $al = 'right';
	    }// end if
	    return $al;
	}// end _getAlign();
	
	/**
	 * output filed html code
	 *
	 * @param no parameters
	 * @return string html code example: <td align='left'>xxx</td>
	 * @access public
	 * @author Dennis
	 */
	public function output()
	{
		$bound_field_html = '<td width="'.(isset($this->width) ? $this->width : self::DEFAULT_WIDTH).'"';
		//$bound_field_html = '<td '; // remark by dennis 2011-08-09
        $bound_field_html .= ($this->dataType == 'date' ? ' nowrap ' : ''); // add by dennis 2011-08-09
		$bound_field_html .= (isset($this->height)   ? ' height="'.$this->height.'"' : '');	
		$bound_field_html .= (empty($this->align)     ? '' : ' align="'.$this->align.'"');
		$bound_field_html .= (empty($this->bgColor)   ? '' : ' bgcolor="'.$this->bgColor.'"');		
		$bound_field_html .= (empty($this->className) ? '' : ' class="'.$this->className.'"');
		$bound_field_html .= (empty($this->name) ? '' : ' name="'.$this->name.'"');
		$bound_field_html .= (empty($this->dbFieldName) ? '' : ' dbfield="'.$this->dbFieldName.'"');
		$bound_field_html .='>';
		$bound_field_html .= (empty($this->fontName) ? '' : '<font name="'.$this->fontName.'"');
		
		if (!empty($this->fontName) && !empty($this->fontColor))
		{
			$bound_field_html .= 'color="'.$this->fontColor.'"';
		}
		if(empty($this->fontName) && !empty($this->fontColor))
		{
		    //echo $this->fontColor;
			$bound_field_html .= '<font color="'.$this->fontColor.'"';
		}
		if(!empty($this->fontName) || !empty($this->fontColor))
		{
			$bound_field_html .='>'; // font label 的结束符
		}// end if		
		$bound_field_html .= isset($this->dbFieldValue) ? $this->dbFieldValue : $this->nullValueText;
		if(!empty($this->fontName) || !empty($this->fontColor))
		{
			$bound_field_html .='</font>'; // font label 的结束label
		}// end if
		$bound_field_html .='</td>';
		return $bound_field_html;
	}// end output()
}// end class Bound_Field
?>