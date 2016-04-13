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
 * @subpackage Input_Radio
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_Radio.class.php Jan 7, 2008 11:08:05 AM Dennis $
 */
 
 /**
 * Form Element Input Radio(单选按钮)
 * @category   eHR
 * @package    Form
 * @subpackage Input_Radio
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis  
 */
class Input_Radio extends Input_Abstract
{
    public $groupName;
    public $checkedValue;
    
    public function __construct(array $config)
    {
        parent::__construct($config);
        self::_init($config);
    }// end class constructor
    
    protected function _init(array $config)
    {
        if (is_array($config))
        {
            foreach($config as $key=>$value)
            {
                switch($key)
                {
                    case 'groupName':
                        $this->groupName = $value;
                        break;
                    case 'checkedValue':
                        $this->checkedValue = $value;
                        break;
                    default:break;
                }// end switch
            }// end foreach
        }// end if
        
    }// end _init()
    
    public function render()
    {
        $input_radio_html  = '<input type="radio" ';
        $input_radio_html .= '/>';
        return $input_radio_html;
    }// end render()
}// end class Input_Radio
?>