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
 * @package    Form
 * @subpackage Input_Checkbox
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_Checkbox.class.php 2797 Jan 7, 2008 11:08:05 AM Dennis $
 */

/**
 * Form Input Checkbox Element
 * @category   eHR
 * @package    Form
 * @subpackage Input_Checkbox
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
class Input_Checkbox extends Input_Abstract
{
    /**
     * Checkbox 选中时的值
     *
     * @var mixed
     */
    public $checkedValue;
        
    /**
     * Constructor of class Input_Checkbox
     *
     * @param array $config
     * @return void
     * @access public
     * @author Dennis
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        self::_init($config);
    }// end class constructor
    
    /**
     * 初始化checkbox属性
     *
     * @param array $config
     * @return void
     * @access protected
     * @author Dennis
     */
    protected function _init(array $config)
    {
        if (is_array($config))
        {
            foreach($config as $key=>$value)
            {
                switch($key)
                {
                    case 'checkedValue':
                        $this->checkedValue = $value;
                        break;
                    default:break;
                }// end switch
            }// end foreach
        }// end if
    }// end _init()
    
    /**
     * 输入出组合后的 <input type="checkbox" xxx/> html 源码
     * @param no
     * @return string
     * @access public
     * @author Dennis
     */
    public function render()
    {
        $input_checkbox_html  = '<input type="checkbox" ';
        $input_checkbox_html .= 'name="'.$this->_name.'" ';
        $input_checkbox_html .= 'id="'.$this->_id.'" ';
        $input_checkbox_html .= (empty($this->className) && empty($this->requiredFieldStyle)) ? 
        						'' :
                            	'class="'.(($this->isRequired && isset($this->requiredFieldStyle)) ?  
                                $this->requiredFieldStyle : 
                                $this->className).'" ';

        $align_style = isset($this->align) ? 'text-align:'.$this->align.';' : '';
        $style_html  = empty($align_style) && empty($this->style) ? '' : $align_style.$this->style;
        $input_checkbox_html .= empty($style_html) ? '' : 'style="'.$style_html.';" ';
        $input_checkbox_html .= empty($this->value) ? '': 'value="'.$this->value.'" ';
        $input_checkbox_html .= empty($this->disabled)  ? '' : 'disabled="'.$this->disabled.'" ';
        $input_checkbox_html .= empty($this->hideFocus) ? '' : 'hideFocus="'.$this->hideFocus.'" ';
        $input_checkbox_html .= empty($this->tabIndex)  ? '' : 'tabIndex="'.$this->tabIndex.'" ';
        $input_checkbox_html .= empty($this->tooltip)   ? '' : 'title="'.$this->toolTip.'" ';
        $input_checkbox_html .= empty($this->onFocus)   ? '' : 'onFocus="'.$this->onFocus.'" ';
        $input_checkbox_html .= empty($this->onBlur)    ? '' : 'onBlur="'.$this->onBlur.'" '; 
        $input_checkbox_html .= empty($this->onClick)   ? '' : 'onClick="'.$this->onClick.'" ';
        $input_checkbox_html .= empty($this->onDblClick)? '' : 'onDblClick="'.$this->onDblClick.'" ';        
        $input_checkbox_html .= isset($this->checkedValue) && $this->value == $this->checkedValue ? 'checked' : '';
        $input_checkbox_html .= '/>';
        return $input_checkbox_html;
    }// end render()
}// end class Input_Checkbox
?>