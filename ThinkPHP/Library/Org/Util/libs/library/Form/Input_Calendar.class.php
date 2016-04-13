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
 * @package    Calendar
 * @subpackage Input_Calendar
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_Calendar.class.php 2797 Jan 7, 2008 11:08:05 AM Dennis $
 */

/**
 * Form Element Calendar Input
 * @category   eHR
 * @package    Form
 * @subpackage Input_Calendar
 * @example
 * property 	type 	description 	default
 inputField 	string 	The ID of your input field. 	null
 displayArea string 	This is the ID of a <span>, <div>, or any other element that you would like to use to display the current date. This is generally useful only if the input field is hidden, as an area to display the date. 	null
 button 	    string 	The ID of the calendar ``trigger''. This is an element (ordinarily a button or an image) that will dispatch a certain event (usually ``click'') to the function that creates and displays the calendar. 	null
 eventName 	string 	The name of the event that will trigger the calendar. The name should be without the ``on'' prefix, such as ``click'' instead of ``onclick''. Virtually all users will want to let this have the default value (``click''). Anyway, it could be useful if, say, you want the calendar to appear when the input field is focused and have no trigger button (in this case use ``focus'' as the event name). 	``click''
 ifFormat 	string 	The format string that will be used to enter the date in the input field. This format will be honored even if the input field is hidden. 	``%Y/%m/%d''
 daFormat 	string 	Format of the date displayed in the displayArea (if specified). 	``%Y/%m/%d''
 singleClick boolean 	Wether the calendar is in ``single-click mode'' or ``double-click mode''. If true (the default) the calendar will be created in single-click mode. 	true
 disableFunc function 	A function that receives a JS Date object. It should return true if that date has to be disabled, false otherwise. DEPRECATED (see below). 	null
 dateStatusFunc 	function 	A function that receives a JS Date object and returns a boolean or a string. This function allows one to set a certain CSS class to some date, therefore making it look different. If it returns true then the date will be disabled. If it returns false nothing special happens with the given date. If it returns a string then that will be taken as a CSS class and appended to the date element. If this string is ``disabled'' then the date is also disabled (therefore is like returning true). For more information please also refer to section 5.3.8. 	null
 firstDay 	integer 	Specifies which day is to be displayed as the first day of week. Possible values are 0 to 6; 0 means Sunday, 1 means Monday, ..., 6 means Saturday. The end user can easily change this too, by clicking on the day name in the calendar header. 	0
 weekNumbers 	boolean 	If ``true'' then the calendar will display week numbers. 	true
 align 	string 	Alignment of the calendar, relative to the reference element. The reference element is dynamically chosen like this: if a displayArea is specified then it will be the reference element. Otherwise, the input field is the reference element. For the meaning of the alignment characters please section 5.3.11. 	``Bl''
 range 	array 	An array having exactly 2 elements, integers. (!) The first [0] element is the minimum year that is available, and the second [1] element is the maximum year that the calendar will allow. 	[1900, 2999]
 flat 	string 	If you want a flat calendar, pass the ID of the parent object in this property. If not, pass null here (or nothing at all as null is the default value). 	null
 flatCallback 	function 	You should provide this function if the calendar is flat. It will be called when the date in the calendar is changed with a reference to the calendar object. See section 2.2 for an example of how to setup a flat calendar. 	null
 onSelect 	function 	If you provide a function handler here then you have to manage the ``click-on-date'' event by yourself. Look in the calendar-setup.js and take as an example the onSelect handler that you can see there. 	null
 onClose 	function 	This handler will be called when the calendar needs to close. You don't need to provide one, but if you do it's your responsibility to hide/destroy the calendar. You're on your own. Check the calendar-setup.js file for an example. 	null
 onUpdate 	function 	If you supply a function handler here, it will be called right after the target field is updated with a new date. You can use this to chain 2 calendars, for instance to setup a default date in the second just after a date was selected in the first. 	null
 date 	date 	This allows you to setup an initial date where the calendar will be positioned to. If absent then the calendar will open to the today date. 	null
 showsTime 	boolean 	If this is set to true then the calendar will also allow time selection. 	false
 timeFormat 	string 	Set this to ``12'' or ``24'' to configure the way that the calendar will display time. 	``24''
 electric 	boolean 	Set this to ``false'' if you want the calendar to update the field only when closed (by default it updates the field at each date change, even if the calendar is not closed) 	true
 position 	array 	Specifies the [x, y] position, relative to page's top-left corner, where the calendar will be displayed. If not passed then the position will be computed based on the ``align'' parameter. Defaults to ``null'' (not used). 	null
 cache 	boolean 	Set this to ``true'' if you want to cache the calendar object. This means that a single calendar object will be used for all fields that require a popup calendar 	false
 showOthers 	boolean 	If set to ``true'' then days belonging to months overlapping with the currently displayed month will also be displayed in the calendar (but in a ``faded-out'' color)
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
require_once 'Input_Text.class.php';
class Input_Calendar extends Input_Text
{
    public $calendarJSPath;

    public $language = 'US';
    /**
     * Date Format
     *  %a 	abbreviated weekday name
     %A 	full weekday name
     %b 	abbreviated month name
     %B 	full month name
     %C 	century number
     %d 	the day of the month ( 00 .. 31 )
     %e 	the day of the month ( 0 .. 31 )
     %H 	hour ( 00 .. 23 )
     %I 	hour ( 01 .. 12 )
     %j 	day of the year ( 000 .. 366 )
     %k 	hour ( 0 .. 23 )
     %l 	hour ( 1 .. 12 )
     %m 	month ( 01 .. 12 )
     %M 	minute ( 00 .. 59 )
     %n 	a newline character
     %p 	``PM'' or ``AM''
     %P 	``pm'' or ``am''
     %S 	second ( 00 .. 59 )
     %s 	number of seconds since Epoch (since Jan 01 1970 00:00:00 UTC)
     %t 	a tab character
     %U, %W, %V 	the week number
     %u 	the day of the week ( 1 .. 7, 1 = MON )
     %w 	the day of the week ( 0 .. 6, 0 = SUN )
     %y 	year without the century ( 00 .. 99 )
     %Y 	year including the century ( ex. 1979 )
     %% 	a literal % character
     *
     * @var string
     */
    public $ifFormat;

    /**
     * Date Format
     * @see $ifFormat
     * @var string
     */
    public $daFormat;

    /**
     * display time on the calendar
     *
     * @var boolean
     */
    public $showsTime = false;

    /**
     * time formate, 12 or 24
     *
     * @var string
     */
    public $timeFormat;

    /**
     * Show Calendar Button at the end of the Input Field
     *
     * @var boolean
     */
    public $button = false;
    
    /**
     * Single click choose date
     *
     * @var boolean
     */
    public $singleClick = true;

    /**
     * configurations array of Calendar
     *
     * @var array
     */
    private $_calendarConfig = array();

    /**
     * configurations array of Calendar Input Field
     *
     * @var array
     */
    private $_inputFieldConfig = array();

    /**
     * Constructor of class Input_Calendar
     *
     * @param array $config calendar or input field array
     * @return void no return value;
     */
    public function __construct(array $config)
    {
        //if (!is_dir($config['jsPath'])) trigger_error('Application Error: Calendar JS Path Is Not a Directory',E_USER_ERROR);
        //$this->calendarJSPath = $config['jsPath'];
        //$this->language = isset($config['lang']) ? $config['lang'] : $this->language;
        //$this->_setCalendarProperty('inputField',$config['name']);
        parent::__construct($config);
        // hardcode assign class date lov column
        $this->className = 'input-date';
        self::_init($config);
    }// end class constructor

    /**
     * Init Calendar properties
     *
     * @param array $config Calendar properties array
     * @return void no return value
     * @access protected
     * @author Dennis
     */
    protected function _init(array $config)
    {
    	//pr($config);
        reset($config);
        foreach($config as $key=>$value)
        {
            switch($key)
            {
                case 'ifFormat':
                case 'daFormat':
                case 'showsTime':
                case 'timeFormat':
                case 'singleClick':
                    $this->_setCalendarProperty($key,$value);
                    break;
                default:break;
            }// end switch
        }// end foreach
    }// end _init()

    private function _setCalendarProperty($key,$value)
    {
        $this->_calendarConfig[$key] = $value;
    }// end _setCalendarProperty()

    private function _getCalendarSetupScript()
    {
    	require_once 'JsCalendar/Calendar.class.php';
        $script_code = '';
        //$session_calendar = new Zend_Session_Namespace('calendar');

        $calendar = new Calendar($this->calendarJSPath,$this->language);
        //if(!isset($session_calendar->isLoaded) && $session_calendar->isLoaded == false)
        //{
        $script_code .= $calendar->getLoadFilesCode();
        //$session_calendar->isLoaded = true;
        //}
        //echo $script_code;
        $script_code .= $calendar->makeCalendar($this->_calendarConfig);
        return  $script_code;
    }// end _getCalendarSetupScript()
 
    public function render()
    {
        $calendar_html_code  = parent::render();
        //echo $this->_getCalendarSetupScript();
        //$calendar_html_code .= $this->_getCalendarSetupScript();
        //echo  $calendar_html_code;
        return $calendar_html_code;
    }// end render()
    
}// end class Input_Calendar
?>