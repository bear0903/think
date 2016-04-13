<?php

/**
 * 产生一个 日历
 *@example 
 * <style stype="text/css">
 *#cal {color:#FFF; background-color:#FFF; border: 1px solid #1a4551;font: 10px Verdana, Arial, Helvetica, sans-serif; }
 *#cal td { text-align:center; width: 60px;height:30px; background-color: #347a3b;}
 *tr#days-of-week td { background-color: #aaa;}
 *#cal thead td {background-color: #16414d; font-size: 12px; font-weight: bold;}
 *#cal thead a{ color: #FFF;}
 *#cal a{color: #ff0000; text-decoration:none;}
 *#cal .cal-prev-month-day, #cal .cal-next-month-day {color: #215f27;}
 *#cal-current-day {background-color: #1b5421 !important;}
 * </style>
 * <?php
 * // Include calendar class
 * require_once('calendar.class.php');
 * // Create calendar object
 * $cal = new calendar;
 * // Turn on navigation links
 * $cal->enableNavigation();
 * // Add event on current day
 * $cal->addEvent('example Event', 2007, 12, 1, 'http://www.example-event.com');
 * $cal->addEvent('example Event 2', 2007, 12, 2, 'http://www.example-event.com');
 * $cal->addEvent('example Event 3', 2007, 12, 3, 'http://www.example-event.com');
 * // Set innerborder to 1, this alter tables cellspacing, much easier than using CSS
 * $cal->setInnerBorder(1);
 * // Turn on non month days
 * $cal->enableNonMonthDays();
 * // Turn on pretty html output
 * $cal->enablePrettyHTML(TRUE);
 * // Display year along side month
 * $cal->enableYear(TRUE);
 * // Display calendar
 * $cal->display(@$_GET['month'], @$_GET['year'])    
 */
class PHPCalendar {
	
	// Events array
	private $events = array();
	// Defaults for day and month names
	private $dayNames   = array ( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
	private $monthNames = array ( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
	// Defaults for prev and next links
	private $prevMonthNavTxt = '&laquo';
	private $nextMonthNavTxt = '&raquo';
	private $calendarName;
	private $queryString;	
	private $startDay;
	
	public function __construct($name='cal') {
		// Assign name to calendar
		if (strpos($name, ' ') || strpos($name, '_') || is_numeric(substr($name, 0, 1)))
			throw new exception('Calendar name must be a valid CSS name');
		$this->calendarName = $name;

		// Names for special cases
		$this->markup = array('current_day' => 'current-day',
							  'last_day_of_week' =>'last-day-of-week',
							  'prev_month' => 'prev-month-day',
							  'next_month' => 'next-month-day',
							  'event' => 'event',
							  'header' => 'header',
							  'nav' => 'nav',
							  'days_of_week' => 'days-of-week');
	}
	
	// Get calendar name
	public function getCalendarName(){
		return $this->calendarName;
	}

	// Set text for previous and next calendar links
	public function setNavigationText($prev, $next) {
		$this->prevMonthNavTxt = $prev;
		$this->nextMonthNavTxt = $next;
	}
	// Get text for previous and next calendar links
	public function getNavigationText() {
		return array(	'prev' => $this->prevMonthNavTxt,
						'next' => $this->nextMonthNavTxt
					);
	}

	// Set inner border width. Would be too annoying to do this with CSS
	public function setInnerBorder($size) {
		$this->innerBorder = intval($size);
	}
	// Set inner border width. Would be too annoying to do this with CSS
	public function getInnerBorder($size) {
		return $this->innerBorder;
	}

	// Set the names of the days
	public function setDayNames($array) {
		if (count($array) == 7)
			$this->dayNames = $array;
		else
			throw new exception ('Invalid value for setDayNames()');
	}
	// Get the names of the days
	public function getDayNames($array) {
		return $this->dayNames;
	}

	// Set the names of the months
	public function setMonthNames($array) {
		if (count($array) == 12)
			$this->monthNames = $array;
		else
			throw new exception ('Invalid value for setMonthNames()');
	}
	// Set the names of the months
	public function getMonthNames($array) {
		return $this->monthNames;
	}

	// Sets the calendar start day 0=monday, 1=sunday, 2=saturday etc...
	public function setStartDay($day) {
		if (is_int($day) && $day >= 0 && $day <=6) {
			$this->startDay=$day;
			for ($i=0;$i<$day;$i++)
			array_unshift($this->dayNames, array_pop($this->dayNames));
		}
		else
			throw new exception('Invalid value for setStartDay()');
	}
	// Gets the calendar start day
	public function getStartDay($day) {
		return $this->startDay=$day;
	}

	// Enables prev and next month links
	public function enableNavigation() {
		$this->enableNav = true;
	}
	// Disables prev and next month links
	public function disableNavigation() {
		$this->enableNav = false;
	}

	// Gets the long name of the current month
	private function getMonthName() {
		return ucwords($this->monthNames[$this->month-1]);
	}
	// Get calendars month 1-12
	private function getMonth() {
		return $this->month;
	}
	// Get calendar year ####
	private function getYear() {
		return $this->year;
	}

	// Enables nicely formatted html instead of just one big line
	public function enablePrettyHTML() {
		$this->prettyHTML = true;
	}
	// Disables nicely formatted html instead of just one big line
	public function disablePrettyHTML() {
		$this->prettyHTML = false;
	}

	// Display the year along side the month
	public function enableYear() {
		$this->displayYear = true;
	}
	// Do not display year near the month
	public function disableYear() {
		$this->displayYear = false;
	}

	// Enables the displaying of prev and next month's days on the calendar
	public function enableNonMonthDays() {
		$this->displayNonMonthDays = true;
	}
	// Disables the displaying of previous and next month's days on the calendar
	public function disableNonMonthDays() {
		$this->displayNonMonthDays = false;
	}
	
	// get an event on a given date
	public function getEventByDate($year, $month, $day) {
		if (isset($this->events[$year][$month][$day]))
			return $this->events[$year][$month][$day];
		return FALSE;
	}

	// Add an event
	public function addEvent($eventTitle, $eventYear, $eventMonth, $eventDay, $eventLink) {
		$this->events[$eventYear][$eventMonth][$eventDay] = array(	'event_title' => $eventTitle, 'event_link' => $eventLink);
	}
	public function removeEvent($eventYear, $eventMonth, $eventDay) {
		unset($this->events[$eventYear][$eventMonth][$eventDay]);
	}

	// Offsets timestamp according to offset and returns the day month or year
	private function timeTravel($offset, $dmy, $timeStamp) {
		$dateVals = array (	'd' => 'j',
							'm' => 'n',
							'y' => 'Y'
							);
		return date($dateVals[$dmy], strtotime($offset, $timeStamp));
	}

	// Display calendar.true Supply month and year to override default value of current month
	public function display($month='', $year='') {
	
		// Remove whitespaces
		$year = trim($year);
		$month = trim($month);

		// Set day, month and year of calendar
		$this->day = 1;
		$this->month = ($month == '') ?	date('n') : $month;
		$this->year = ($year == '') ? date('Y') : $year;

		// Check for valid input	
		if (!preg_match('~[0-9]{4}~', $this->year))
			throw new exception('Invalid value for year');
		if (!is_numeric($this->month) || $this->month < 0 || $this->month > 13)
			throw new exception('Invalid value for month');

		// Set the current timestamp
		$this->timeStamp = mktime(1,1,1,$this->month, $this->day, $this->year);
		// Set the number of days in teh current month
		$this->daysInMonth = date('t',$this->timeStamp);

		// Start table
		$calHTML = sprintf("<table id=\"%s\" cellpadding=\"0\" cellspacing=\"%d\"><thead><tr>", $this->calendarName, $this->innerBorder);
		// Display previous month navigation
		if ($this->enableNav) {
			$pM = explode('-', date('n-Y', strtotime('-1 month', $this->timeStamp)));
			$calHTML .= sprintf("<td class=\"%s-%s\"><a href=\"?%smonth=%d&amp;year=%d\">%s</a></td>", $this->calendarName, $this->markup['nav'], $this->queryString, $pM[0], $pM[1],$this->prevMonthNavTxt);
		}
		
		// Month name and optional year
		$calHTML .= sprintf("<td colspan=\"%d\" id=\"%s-%s\">%s%s</td>", ($this->enableNav ? 5 : 7), $this->calendarName, $this->markup['header'], $this->getMonthName(), ($this->displayYear ? ' ' .$this->year : ''));

		// Display next month navigation
		if ($this->enableNav) {
			$nM = explode('-', date('n-Y', strtotime('+1 month', $this->timeStamp)));
			$calHTML .= sprintf("<td class=\"%s-%s\"><a href=\"?%smonth=%d&amp;year=%d\">%s</a></td>", $this->calendarName, $this->markup['nav'], $this->queryString, $nM[0], $nM[1],$this->nextMonthNavTxt);
		}

		$calHTML .= sprintf("</tr></thead><tbody><tr id=\"%s\">", $this->markup['days_of_week']);

		// Display day headers
		foreach($this->dayNames as $k => $dayName)
			$calHTML .= sprintf("<td>%s</td>", $dayName);

		$calHTML .= "</tr><tr>";
		
		/// What the heck is this
		$sDay = date('N', $this->timeStamp) + $this->startDay - 1;
		
		// Print previous months days
			for ($e=1;$e<=$sDay;$e++)
				$calHTML .= sprintf("<td class=\"%s-%s\">%s</td>", $this->calendarName, $this->markup['prev_month'],  (($this->displayNonMonthDays) ? $this->timeTravel("-" . ($sDay -$e) . " days", 'd', $this->timeStamp) : ''));
	
		// Print days
		for ($i=1;$i<=$this->daysInMonth;$i++) {
			// Set current day and timestamp
			$this->day = $i;
			$this->timeStamp = mktime(1,1,1,$this->month, $this->day, $this->year);
			
			// Set day as either plain text or event link
			if (isset($this->events[$this->year][$this->month][$this->day]))
				$this->htmlDay = sprintf("<a href=\"%s\" title=\"%s\">%s</a>", $this->events[$this->year][$this->month][$this->day]['event_link'], $this->events[$this->year][$this->month][$this->day]['event_title'], $this->day);
			else
				$this->htmlDay = $this->day;			
	
			// Display calendar cell
			$calHTML .= sprintf("<td %s%s>%s</td>", ($this->timeStamp == mktime(1,1,1,date('n'),date('j'),date('Y')) ? 'id="' . $this->calendarName . '-' . $this->markup['current_day'] . '" ' : ''), ((($sDay + $this->day) % 7 == 0) ? 'class="' . $this->calendarName . '-' . $this->markup['last_day_of_week'] . '"' : ''), $this->htmlDay);				
						
			// End row if necessary			
			if (($sDay + $this->day) % 7 == 0)
				$calHTML .= "</tr><tr>";
		}
		
		// Print next months days
		for ($e2=1;$e2 < (7 - (($sDay + $this->daysInMonth -1) % 7)); $e2++)
			$calHTML .= sprintf("<td class=\"%s-next-month-day%s\">%s</td>", $this->calendarName, ((($sDay + $this->day + $e2) % 7 == 0) ? ' ' . $this->calendarName . '-' . $this->markup['last_day_of_week'] : ''), (($this->displayNonMonthDays) ? $this->timeTravel("+$e2 days", 'd', $this->timeStamp) : ''));
		
		$calHTML .= "</tr></tbody></table>";
	
		// Tidy up html
		if ($this->prettyHTML) {
			$replaceWhat = array('<tr', '<td', '</tr>', '</table>', '<thead>', '</thead>', '<tbody>', '</tbody>');
			$replaceWith = array("\n\t\t<tr", "\n\t\t\t<td", "\n\t\t</tr>", "\n</table>", "\n\t<thead>", "\n\t</thead>", "\n\t<tbody>", "\n\t</tbody>");
			$calHTML = str_replace($replaceWhat, $replaceWith, $calHTML);
		}
		
		// Print calendar
		echo $calHTML;
	}
	
}
?>