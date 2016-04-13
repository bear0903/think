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
 * @subpackage Input_Abstract
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_Abstract.class.php Jan 7, 2008 11:05:13 AM Dennis $
 */
 
 /**
 * Form Element Abstract class
 * 定义 Input 共有和属性及方法
 * @category   eHR
 * @package    Form
 * @subpackage Input_Abstract
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
require_once 'Base.class.php';
abstract class Input_Abstract extends Base
{
    /**
     * Retrieves the name of the object.
     * @var string
     */
    protected $_name;
    
    /**
     * Retrieves the string identifying the object.
     * @var string
     */
    protected $_id;
    
    /**
     * Sets or retrieves the class of the object. 
     *
     * @var string
     */
    public    $className;
    
    /**
     * Sets an inline style for the element.
     *
     * @var string
     */
    public    $style;
    
    /**
     * Sets or retrieves how the object is aligned with adjacent text. 
     *
     * @var string
     */
    public    $align;
    
    /**
     * Sets or retrieves a value that indicates 
     * whether the user can interact with the object. 
     *
     * @var string "true" or "false"
     */
    public    $disabled;
    
    /**
     * Sets or retrieves the index that defines 
     * the tab order for the object.
     *
     * @var int
     */
    public    $tabIndex;
    
    /**
     * Sets or retrieves advisory information (a ToolTip) for the object.
     *
     * @var string
     */
    public    $tooltip;
    
    /**
     * Specifies that an element cannot be selected.
     *
     * @var string
     */
    public    $unSelectable;
    
    /**
     * Sets or retrieves the default or selected value of the control.
     *
     * @var string
     */
    public    $value;
    
    /**
     * 栏位预设值
     *
     * @var mixed
     */
    public	  $defaultValue;
    
    /**
     * Sets or gets the value that indicates
     * whether the object visibly shows that it has focus.
     *
     * @var boolean
     */
    public    $hideFocus;
    
    /**
     * onFocus Event
     * 元素获得焦点(Cursor)时要执行的 Javascript Function 或是一段 Script
     *
     * @var string
     */
    public    $onFocus;
    
    /**
     * onBlur Event
     * 元素失去焦点(Cursor)时要执行的 Javascript Function 或是一段 Script
     *
     * @var string
     */
    public    $onBlur;
    
     /**
     * onClick Event
     *  Mouse 单击元素时要执行的 Javascript Function 或是一段 Script
     *
     * @var string
     */
    public    $onClick;
    
    /**
     * onDblClick Event
     *  Mouse 双击元素时要执行的 Javascript Function 或是一段 Script
     *
     * @var string
     */
    public    $onDblClick;
    
    
    /**
     * Constructor of class Input_Abstract
     *
     * @param unknown_type $config
     */
    public function __construct(array $config)
    {
        self::_init($config);
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
                    case 'name':
                        $this->_name = $value;
                        break;
                    case 'id':
                        $this->_id = $value;
                        break;
                    case 'align':
                        $this->align = $value;
                        break;
                    case 'style':
                        $this->style = $value;
                        break;
                    case 'classname':
                        $this->className = $value;
                        break;
                    case 'disabled':
                        $this->disabled = $value;
                        break;
                    case 'hidefocus':
                        $this->hideFocus = $value;
                        break;
                    case 'tabindex':
                        $this->tabIndex = $value;
                        break;
                    case 'tooltip':
                        $this->tooltip = $value;
                        break;
                    case 'unselectable':
                        $this->unSelectable = $value;
                        break;
                    case 'value':
                        $this->value = $value;
                        break;
                    case 'defaultvalue':
                    	$this->defaultValue;
                    	break;
                    case 'onfocus':
                        $this->onFocus = $value;
                        break;
                    case 'onblur':
                        $this->onBlur = $value;
                        break;
                    case 'onclick':
                        $this->onClick = $value;
                        break;
                    case 'ondblclick':
                        $this->onDblClick = $value;
                        break;
                    default:break;
                }// end switch()
                // remove the public properties
                unset($config[$key]);
            }// end foreach
        }// end if
    }// end _init()
    
    /**
     * 输出input html code 如: 
     * <code>
     * 	<input type='text' name='myname' id='myid' value='10' readonly='true'/>
     * </code>
     * 抽象方法,必须由其继承类实作
     * @param no
     * @abstract true;
     * @access public
     * @author Dennis
     */
    abstract public function render();
}// end abstract class Input_Abstract
?>