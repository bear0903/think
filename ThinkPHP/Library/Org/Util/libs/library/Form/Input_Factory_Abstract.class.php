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
 * @subpackage Input_Factory_Abstract
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_Factory_Abstract.class.php 2797 Jan 15, 2008 1:15:54 PM Dennis $
 */

/**
 *
 * @category   eHR
 * @package    Form
 * @subpackage Input_Factory_Abstract
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
abstract class Input_Factory_Abstract
{
    abstract public function createInputText(array $config);
    abstract public function createInputCheckbox(array $config);
    abstract public function createInputRadio(array $config);
    abstract public function createInputList(array $config);
    abstract public function createInputPassword(array $config);
    abstract public function createInputHidden(array $config);
    abstract public function createInputButton(array $config);
    abstract public function createInputSubmit(array $config);
    abstract public function createInputReset(array $config);
    abstract public function createInputImgage(array $config);
    abstract public function createInputFile(array $config);
    abstract public function createInputTextArea(array $config);
    abstract public function createInputCalendar(array $config);
    abstract public function createInputLov(array $config);
}// end class Input_Factory_Abstract

?>