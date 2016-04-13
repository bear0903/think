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
 * @subpackage Form_Abstract
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Form_Abstract.class.php Jan 31, 2008 17:05:13 AM Dennis $
 */
 
 /**
 * Form Element Abstract class
 *  Form 
 * @category   eHR
 * @package    Form
 * @subpackage Form_Abstract
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
require_once 'Base.class.php';
abstract class Form_Abstract extends Base
{
	/**
	 * 单栏式布局, 所有的栏位按顺序从上往下排
	 * @var string
	 */
	const SINGLE_COLUMN  = '1';
	
	/**
	 * 两栏式布局, 栏位按顺序从上往下排左右排两栏
	 * @var string
	 */
	const TWO_COLUMN	 = '2';
	
	/**
	 * 三栏式布局, 栏位按顺序从上往下排左右排三栏
	 * @var string
	 */
	const THREE_COLUMN	 = '3';
	
    /**
     * Retrieves the name of the object.
     * @var string
     */
    protected $_name = 'form1';
    
    /**
     * Retrieves the string identifying the object.
     * @var string
     */
    protected $_id = 'form1';
    
    /**
     * Form action 属性
     * 预设是本页
     * @var string
     */
    public $action;
    
    /**
     * Form 提交资料的方式 (get/post)
     *  预设是 Post
     * @var string
     */
    public $method = 'POST';
    
    /**
     * 资料提交页, 预设是本页
     *
     * @var string
     */
    public $target = '_self';
    
    /**
     * Encode 类型, 当有上传文件时会用到此属性
     *
     * @var unknown_type
     */
    public $enctype; //'multipart/form-data';
    
    /**
     * 几栏式布局
     *
     * @var string
     */
    public $layoutColumn;
    
    protected $_elementsConfig;
    
    /**
     * Constructor of class Form_Abstract
     *
     * @param array $form_config
     * @param array $element_config
     */
    public function __construct(array $form_config,array $element_config)
    {
        self::_init($form_config);
        $this->_elementsConfig = $element_config;
    }// end class constructor
    
    /**
     * 初始化 Form 属性
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
                    case 'action':
                        $this->action = $value;
                        break;
                    case 'target':
                        $this->target = $value;
                        break;
                    case 'classname':
                        $this->className = $value;
                        break;
                    case 'method':
                        $this->method = $value;
                        break;
                    default:break;
                }// end switch()
                // remove the public properties
                unset($config[$key]);
            }// end foreach
        }// end if
    }// end _init()
    
    /**
     * 布局 Form 元素
     *
     * @param array $form_elements
     * @return string
     * @access public
     * @abstract true
     * @author Dennis
     */
    abstract protected function _layout();
    
    /**
     * 输出Form html code 如: 
     * <code>
     * 	<form id='myform' name='form1' action='?' method='post'  enctype='multipart/form-data'>
     *  </form>
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