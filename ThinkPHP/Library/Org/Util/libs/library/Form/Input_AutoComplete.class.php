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
 * @subpackage Input_AutoComplete
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_AutoComplete.class.php Jan 17, 2008 10:34:59 AM Dennis $
 */

/**
 *
 * @category   eHR
 * @package    Form
 * @subpackage Input_AutoComplete
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
class Input_AutoComplete extends Input_Abstract
{
    public function __construct(array $config)
    {
    	parent::_init($config);
    	self::_init($config);
    }// end class constructor

    protected function _init(array $config)
    {
    	if (is_array($config))
    	{
    		foreach ($config as $key=>$value)
    		{
    			switch ($key) {
    				case '':
    				break;
    				
    				default:
    				break;
    			}
    		}
    	}// end if
    }// end _init()

    public function render()
    {

    }// end render()

}
?>