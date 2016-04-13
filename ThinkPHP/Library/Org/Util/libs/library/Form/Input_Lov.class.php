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
 * @subpackage Input_Text
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_Text.class.php 2797 Jan 7, 2008 11:08:05 AM Dennis $
 */
 
 /**
 * Form Element Input LOV(List of Value )
 * @category   eHR
 * @package    Form
 * @subpackage Input_Lov
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
class Input_Lov extends Input_Abstract
{
    /**
     * 
     */   
    public function __construct(array $config)
    {
        // call parent constuctor init the input's public properties
        parent::__construct($config);
        // call self _init function
        self::_init($config);
        if (empty($this->_name))
        {
            error(__CLASS__.' -> '.__FUNCTION__,'input name must be assigned');
        }// end if
        
        if (empty($this->_id))
        {
            $this->_id = $this->_name;
            //error(__CLASS__.' -> '.__FUNCTION__,'input id must be assigned');
        }// end if		
    }// end class constructor
    
    /**
     * 初始化 input 属性
     * @param array $config
     * @return void no return value
     * @access protected
     * @author Dennis
     */
    protected function _init(array $config)
    {
       
        if (is_array($config))
        {
            foreach($config as $key=>$value)
            {
                switch(strtolower($key))
                {
                    case 'maxlength':
                        $this->maxLength = $value;
                        break;
                    case 'size':
                        $this->size = $value;
                        break;
                    case 'readonly':
                        $this->readonly = $value;
                        break;
                    case 'isrequired':
                        $this->isRequired = $value;
                        break;
                    case 'requiredfieldstyle':
                        $this->requiredFieldStyle = $value;
                        break;
                    case 'dataformat':
                        $this->dataFormat = $value;
                        break;
                    case 'normalstyle':
                        $this->normalStyle = $value;
                        break;
                    case 'focusstyle':
                        $this->focusStyle = $value;
                        break;
                    default:break;
                }// end switch()
            }// end foreach
        }// end if
    }// end _init()
    
    /**
     * Output <input type="text"> html code
     * @param  no
     * @return string
     * @access public
     * @author Dennis
     */
    public function render()
    {
        $input_text_html  = '<input type="text" ';
        $input_text_html .= 'name="'.$this->_name.'" ';
        $input_text_html .= 'id="'.$this->_id.'" ';
        $input_text_html .= (empty($this->className) && empty($this->requiredFieldStyle)) ? '' :
                            'class="'.(($this->isRequired && isset($this->requiredFieldStyle)) ?
                            $this->requiredFieldStyle : $this->className).'" ';      
        
        $align_style = isset($this->align) ? 'text-align:'.$this->align.';' : '';        
        $style_html  = empty($align_style) && empty($this->style) ? '' : $align_style.$this->style;       
        $input_text_html .= empty($style_html) ? '' : 'style="'.$style_html.';" ';                            
        $input_text_html .= empty($this->disabled)  ? '' : 'disabled="'.$this->disabled.'" ';
        $input_text_html .= empty($this->hideFocus) ? '' : 'hideFocus="'.$this->hideFocus.'" ';
        $input_text_html .= empty($this->tabIndex)  ? '' : 'tabIndex="'.$this->tabIndex.'" ';
        $input_text_html .= empty($this->tooltip)   ? '' : 'title="'.$this->toolTip.'" ';
        $input_text_html .= empty($this->onFocus)   ? '' : 'onFocus="'.$this->onFocus.'" ';
        $input_text_html .= (!$this->isRequired && isset($this->onBlur))  ?  'onBlur="'.$this->onBlur.'" ' : ''; 
        $input_text_html .= empty($this->onClick)   ? '' : 'onClick="'.$this->onClick.'" ';
        $input_text_html .= empty($this->onDblClick)? '' : 'onDblClick="'.$this->onDblClick.'" '; 
        $input_text_html .= (empty($this->readonly) || !$this->readonly) ? '' : 'readonly ';
        $input_text_html .= empty($this->maxLength) ? '' : 'maxLength="'.$this->maxLength.'" ';
        $input_text_html .= empty($this->size)      ? '' : 'size="'.$this->size.'" ';
        $input_text_html .= empty($this->unSelectable) ? '' : 'unSelectable="'.$this->unSelectable.'" ';
        $input_text_html .= empty($this->value)     ? '' : 'value="'.(isset($this->dataFormat)? formatData($this->value,$this->dataFormat) : $this->value).'" ';
        // 加上前台检查必须输入的栏位是否有值 JS
        $input_text_html .= (empty($this->isRequired) || !$this->isRequired || $this->readonly) ? '' : 'onblur="return Validation.Required(\''.$this->_name.'\');'.
                            (isset($this->onBlur) ? $this->onBlur : '').
                            ($this->normalStyle ? 'this.className="'.$this->normalStyle.'";' : '').'"';
       
        $input_text_html .= '/>'.($this->isRequired ? '<font color="blue">*</font>' : '');
        return $input_text_html;
    }// end render()
    
}// end class Input_Text
?>