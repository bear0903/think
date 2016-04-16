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
 * @subpackage Hyperlink_Field
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id: Hyperlink_Field.class.php 2797 2007-12-27 03:18:30 PM Dennis$
 */

/**
 * Hyperlink field
 * @category   eHR
 * @package    GridView
 * @subpackage Hyperlink_Field
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */

/**
 * Hyperlink field 设定类
 * @category   eHR
 * @package    GridView
 * @subpackage Hyperlink_Field
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
class Hyperlink_Field extends Data_Control_Field_Abstract
{
    /**
     * Hyperlink href string
     * @var string
     */
    public $url;
    
    /**
     * Hyperlink target frame, default '_self'
     * @var string, 可取的值为 frameName,_blank,_self,_parent
     */
    public $target = '_self';
    
    /**
     * Hyperlink 的行为, 如 query/delete/update
     * @var unknown_type
     */
    public $action = '';
    
    /**
     * Constructor of class Hyperlink_Field
     * @param array $config  Hyperlink 属性组成的数组
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
	 * @access protected
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
					case 'url':
					    $this->url = $value;
					    break;
					case 'url':
					    $this->url = $value;
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
		}// end if
	}// end function _init()
	
	/**
	 * output filed html code
	 * 
	 * @param no parameters
	 * @access public
	 * @author Dennis
	 */
	public function output()
	{
		
	}// end output()
	
}// end class Hyperlink_Field
?>