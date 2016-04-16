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
 * @subpackage Input_Factory
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_Factory.class.php 2797 Jan 15, 2008 1:42:06 PM Dennis $
 */

/**
 * Form Element Factory
 * @category   eHR
 * @package    Form
 * @subpackage Input_Factory
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
class Input_Factory //extends Input_Factory_Abstract
{
    /*
     static private $_inputType = array('Input_Text',
     'Input_Checkbox',
     'Input_Radio',
     'Input_List',
     'Input_Password',
     'Input_Hidden',
     'Input_Button',
     'Input_Submit',
     'Input_Reset',
     'Input_Image',
     'Input_File',
     'Input_TextArea',
     'Input_Calendar',
     'Input_Lov');*/
    /**
     * Generate Input Element by $input_type
     *
     * @param string $input_type
     * @param array $config
     * @return object
     * @access public
     * @author Dennis
     */
    static public function Factory($input_type,array $config)
    {
        try
        {
            return new $input_type($config);
        }
        catch (Exception $e)
        {
            trigger_error($e->getMessage(),E_USER_ERROR);
            exit();
        }// end try catch
    }// end static function Factory()

}// end class Input_Factory
?>