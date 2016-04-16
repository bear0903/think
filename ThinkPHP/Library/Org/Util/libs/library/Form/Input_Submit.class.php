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
 * @subpackage Input_Submit
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_Submit.class.php Jan 7, 2008 11:08:05 AM Dennis $
 */
 
 /**
 * Form Element Input Submit(提交按钮)
 * @category   eHR
 * @package    Form
 * @subpackage Input_Submit
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
class Input_Submit extends Input_Button_Abstract
{
    /**
     * Constructor of class Input_Submit
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
     * 初始化
     *
     * @param array $config
     */
    protected function _init(array $config)
    {
    }// end _init()
    
    public function render()
    {
        $input_submit_html  = '<input type="submit" ';
        $input_submit_html .= 'name="'.$this->_name.'" ';
        $input_submit_html .= 'id="'.$this->_id.'" ';
        /*
        $input_submit_html .= (empty($this->className) && empty($this->requiredFieldStyle)) ? '' :
                            'class="'.(($this->isRequired && isset($this->requiredFieldStyle)) ?
                            $this->requiredFieldStyle : $this->className).'" ';
        */   
        
        $align_style = isset($this->align) ? 'text-align:'.$this->align.';' : '';        
        $style_html  = empty($align_style) && empty($this->style) ? '' : $align_style.$this->style;       
        $input_submit_html .= empty($style_html) ? '' : 'style="'.$style_html.';" ';
        $input_submit_html .= empty($this->className) ? '' : 'class="'.$this->className.'" ';                            
        $input_submit_html .= empty($this->disabled)  ? '' : 'disabled="'.$this->disabled.'" ';
        $input_submit_html .= empty($this->hideFocus) ? '' : 'hideFocus="'.$this->hideFocus.'" ';
        $input_submit_html .= empty($this->tabIndex)  ? '' : 'tabIndex="'.$this->tabIndex.'" ';
        $input_submit_html .= empty($this->tooltip)   ? '' : 'title="'.$this->toolTip.'" ';
        $input_submit_html .= empty($this->value)     ? '' : 'value="'.$this->value.'" ';
        $input_submit_html .= empty($this->onFocus)   ? '' : 'onFocus="'.$this->onFocus.'" ';
        $input_submit_html .= empty($this->onBlur)    ? '' : 'onBlur="'.$this->onBlur.'" '; 
        $input_submit_html .= empty($this->onClick)   ? '' : 'onClick="'.$this->onClick.'" ';
        $input_submit_html .= empty($this->onDblClick)? '' : 'onDblClick="'.$this->onDblClick.'" ';        
        
        $input_submit_html .= '/>';
        return $input_submit_html;
    }// end render()
    
}// end class Input_Submit
?>