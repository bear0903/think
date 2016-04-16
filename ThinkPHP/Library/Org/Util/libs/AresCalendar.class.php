<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) ARES International Inc.
 *	 
 *	Description:
 *     Calendar Class
 *	!!! Note: year must be 1970 ~ 2050
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresCalendar.class.php $
 *  $Id: AresCalendar.class.php 3716 2014-04-04 07:17:10Z dennis $
 *  $Rev: 3716 $ 
 *  $Date: 2014-04-04 15:17:10 +0800 (周五, 04 四月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-04-04 15:17:10 +0800 (周五, 04 四月 2014) $
 \****************************************************************************/
//include_once("AresCalendarCN.php");
class SolarCalendar {
	public $startDay = 0;
	public $startMonth = 1;
	public $year;
	public $month;
	public $DBConn;
	public $defaultLang = "us";
	public $dayNames;
	public $monthNames;
	public $calendar;
	public $url; // link to self page, for refresh current page
	
	public $dayNamesMultiLang = array ("zhs" => array ("日", "一", "二", "三", "四", "五", "六" ), 
										"zht" => array ("日", "一", "二", "三", "四", "五", "六" ), 
										"us" => array ("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"));
	public $monthNamesMultiLang = array ("zhs" => array ("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"),
	 "zht" => array ("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12" ), 
	 "us" => array ("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"));
	
	public $daysInMonth = array (31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
	
	public $legendColors = array ("today" => "#FFD6EB", 
								"weekendBgColor" => "#93FF93", 
								"weekendFtColor" => "#6600FF", 
								"legalHoliday" => "#C641C6", 
								"exceptionDay" => "#F8EDC6", 
								"unschedule" => "#C6C3C6", 
								"fontcolor" => "#000000", 
								"normalDay" => "#FFFFFF" );
	
	protected $_showHeaderBar = true; // add by dennis 2014/02/07 for control calendar header toolbar show/hide
	
	const  DATA_CACHE_SECCOND = 3600;// 1 HOUR
	
	/**
	 * Constructor of class SolarCalendar
	 *
	 * @param number $year
	 * @param number $month
	 * @param string $company_id
	 * @param number $emp_seq_no
	 * @param string $language
	 * @param string $url
	 * @return Calendar
	 * @author Dennis 
	 */
	function __construct($year, $month, $company_id, $emp_seq_no, $language = "zhs",$url='') {
		global $g_db_sql;
		$this->DBConn = &$g_db_sql;
		$this->year = $year;
		$this->month = $month;
		$this->defaultLang = $language;
		$this->dayNames = $this->dayNamesMultiLang[$this->defaultLang];
		$this->monthNames = $this->monthNamesMultiLang[$this->defaultLang];
		$this->calendar = $this->GetMyCalendar($year, $month, $company_id, $emp_seq_no);
		$this->url = $url;
	}// end Calendar()
	
	
	/**
	 *	Employee Calendar list
	 *	@param $year number, full year digital number
	 *	@param $month number, month number , start by 0
	 *	@param $company_id string, the selected employee's company id
	 *	@param $emp_seq_no string, the selected employee's employee sequence no(psn_id)
	 *	@return 2-d array
	 *	@author: dennis.lan at 2006-02-20 16:26:06 
	 *	@last update: 2006-04-17 14:25:42  by dennis
	 */
	function GetMyCalendar($year, $month, $company_id, $emp_seq_no) {
		$sql_string = <<<eof
			select to_char(my_day,'YYYY-MM-DD') as my_day,
				   workgroup_name,
				   to_char(in_time,'YYYY-MM-DD') as shift_in_time,
				   to_char(in_time,'YYYY-MM-DD HH24:MI:SS')  as in_time,
				   to_char(out_time,'YYYY-MM-DD HH24:MI:SS') as out_time,
				   holiday_code,
				   holiday
			  from ehr_calendar_v
			 where company_id = '$company_id'
			   and emp_seq_no = '$emp_seq_no'
			   and to_char(my_day, 'YYYYMM') = to_char(to_date('$year$month','YYYYMM'),'YYYYMM')
eof;
		//$this->DBConn->debug = 1;
		return $this->DBConn->CacheGetArray(self::DATA_CACHE_SECCOND,$sql_string );
	}
	
	function getDayNames() {
		return $this->dayNames;
	}
	
	function setDayNames($names) {
		$this->dayNames = $names;
	}
	
	function getMonthNames() {
		return $this->monthNames;
	}
	
	function setMonthNames($names) {
		$this->monthNames = $names;
	}
	
	function getStartDay() {
		return $this->startDay;
	}
	
	function setStartDay($day) {
		$this->startDay = $day;
	}
	
	function getStartMonth() {
		return $this->startMonth;
	}
	
	function setStartMonth($month) {
		$this->startMonth = $month;
	}
	
	function getCalendarLink($month, $year) {
		return $this->url . "&month=" . $month . "&year=" . $year;
	}
	
	function getDateLink($day, $month, $year) {
		$_fullMonth = $this->formatNumber($month );
		$_fullDay = $this->formatNumber($day );
		$link = "";
		
		if (is_array($this->calendar)&& isset($this->calendar[$day - 1]["SHIFT_IN_TIME"])&& $this->calendar[$day - 1]["SHIFT_IN_TIME"] == "$year-$_fullMonth-$_fullDay" && isset($this->calendar[$day - 1]["ABSENCE_NAME"] )) {
			$link = "<a href=\"";
			$link .= $this->leaveDetailUrl;
			$link .= "&myday=";
			$link .= $this->calendar[$day - 1]["SHIFT_IN_TIME"];
			$link .= "\">";
			$link .= $this->calendar[$day - 1]["ABSENCE_NAME"];
			$link .= "</a>";
		}
		return $link;
	}
	
	function formatNumber($n) {
		if (( int)$n < 10) {
			return "0" .(int)$n;
		}
		return $n;
	}
	/**
	 *   get cell day tooltip/bgcolor/fontcolor text/html code
	 *   @param  $year  string full year digital number
	 *   @param  $month string month name number
	 *   @param  $day   string the day name of month
	 *   @return string, according the calendar, generator tooltip text or html code
	 *	@author: dennis
	 *	@last update 2006-04-20 17:14:54  by dennis
	 *	@update log:
	 *		1. for control no shift day 2006-02-21 15:28:40 by dennis
	 *       2. 拿掉显示当日请假假别或是起始日期的功能, ?-> 一天请有多笔假的时候显示有误, 
	 *          暂时拿掉以后再加此功能 2006-04-14 17:35:02  by dennis
	 *       3. 加预设的背景色为未排程 2006-04-20 17:15:29  by dennis
	 */
	function getCellAttrs($year, $month, $day) {
		$_fullMonth = $this->formatNumber($month );
		$_fullDay = $this->formatNumber($day );
		
		$_tooltipHtml = "无排程/No Shift Data"; // add default value 2006-04-20 17:17:11 by dennis
		$_cellAtts = array ();
		// Clear cell attributes 
		/*$_cellAtts = array("bgcolor"=>"",
						   "ftcolor"=>"",
						   "tooltip"=>"");*/
		$_cellAtts["ftcolor"] = $this->legendColors["fontcolor"];
		// add by dennis 2006-04-20 17:14:47 
		$_cellAtts["bgcolor"] = $this->legendColors["unschedule"];
		$dday = 0;
		if (is_array($this->calendar)&& count($this->calendar)> 0) {
			
			$pieces = explode("-", $this->calendar[0]["SHIFT_IN_TIME"] );
			$startworktime = mktime(0, 0, 0, $pieces[1], $pieces[2], $pieces[0] ); //工作开始时间戳
			$starttime = mktime(0, 0, 0, $month, $day, $year ); //每月的开始时间戳
			$startday = substr($this->calendar[0]["SHIFT_IN_TIME"], - 2 ); //开始工作时间日期

			if (substr($startday, 0, 1)> 0) {
				$startday = $startday;
			} else {
				$startday = substr($startday, - 1 );
			}
			if ($startday > 1) {
				$dday = $day - $startday + 1;
			} else {
				$dday = $day;
			}
			
			if (isset($this->calendar[$dday - 1]["SHIFT_IN_TIME"])&& ("$starttime" > "$startworktime" or ($this->calendar[$dday - 1]["SHIFT_IN_TIME"] == "$year-$_fullMonth-$_fullDay"))) {
				//预设是正常工作日
				$_cellAtts["bgcolor"] = $this->legendColors["normalDay"];
				$_tooltipHtml = $this->calendar[$dday - 1]["WORKGROUP_NAME"];
				$_tooltipHtml .= "\n-------------------\n" . $this->calendar[$dday - 1]["IN_TIME"];
				$_tooltipHtml .= "\n" . $this->calendar[$dday - 1]["OUT_TIME"];
				
				if ((isset($this->calendar[$dday - 1]["HOLIDAY_CODE"])&& $this->calendar[$dday - 1]["HOLIDAY_CODE"] == "S")) {
					// get week-end day bgcolor and fontcolor
					$_cellAtts["bgcolor"] = $this->legendColors["weekendBgColor"];
					$_cellAtts["ftcolor"] = $this->legendColors["weekendFtColor"];
					$_tooltipHtml = "\n" . $this->calendar[$dday - 1]["HOLIDAY"];
				} // end 周末               
				

				// 国定假日
				if (isset($this->calendar[$dday - 1]["HOLIDAY_CODE"])&& $this->calendar[$dday - 1]["HOLIDAY_CODE"] == "H") {
					// get legal holiday bgcolor and name
					$_cellAtts["bgcolor"] = $this->legendColors["legalHoliday"];
					$_tooltipHtml = "\n" . $this->calendar[$dday - 1]["HOLIDAY"] . "\n";
				} // end 国定假日
			}
		}
		if ($dday>0){
    		$_cellAtts['distext'] = isset($this->calendar[$dday - 1]["HOLIDAY"]) ? 
    		                        $this->calendar[$dday - 1]["HOLIDAY"] : 
    		                        (isset($this->calendar[$dday - 1]["WORKGROUP_NAME"]) ? $this->calendar[$dday - 1]["WORKGROUP_NAME"] : '');
		}
		// 今天的背景色优先级最高
		$_cellAtts["bgcolor"] = $year . $_fullMonth . $_fullDay == date("Ymd")? $this->legendColors["today"] : $_cellAtts["bgcolor"];
		$_cellAtts["tooltip"] = 'title="' . $_tooltipHtml . '"';
		return $_cellAtts;
	} //end function getCellAttrs()
	

	function getCurrentMonthView() {
		$d = getdate(time () );
		return $this->getMonthView($d["mon"], $d["year"] );
	}
	
	function getCurrentYearView() {
		$d = getdate(time () );
		return $this->getYearView($d["year"] );
	}
	
	/**
	 *   Modify by Dennis 2005-11-18 15:07:07 
	 */
	function getMonthView($month, $year,$show_header = 'Y') {
		if (isset($month)&& isset($year )) {
			return $this->getMonthHTML($month, $year );
		} else {
			return $this->getCurrentMonthView ();
		}
	}
	
	function getYearView($year) {
		return $this->getYearHTML($year );
	}
	
	function getDaysInMonth($month, $year) {
		if ($month < 1 || $month > 12) {
			return 0;
		}
		
		$d = $this->daysInMonth[$month - 1];
		
		if ($month == 2) {
			if ($year % 4 == 0) {
				if ($year % 100 == 0) {
					if ($year % 400 == 0) {
						$d = 29;
					}
				} else {
					$d = 29;
				}
			}
		}
		
		return $d;
	}
	
	function getMonthHTML($m, $y, $showYear = 1) {
		$s = "";
		
		$a = $this->adjustDate($m, $y );
		$month = $a[0];
		$year = $a[1];
		
		$daysInMonth = $this->getDaysInMonth($month, $year );
		$date = getdate(mktime(12, 0, 0, $month, 1, $year));
		
		$first = $date["wday"];
		$monthName = $this->monthNames[$month - 1];
		
		$prev = $this->adjustDate($month - 1, $year );
		$next = $this->adjustDate($month + 1, $year );
		
		if ($showYear == 1) {
			$prevYear = $this->getCalendarLink($month, $year - 1 );
			$prevMonth = $this->getCalendarLink($prev[0], $prev[1] );
			$nextYear = $this->getCalendarLink($month, $year + 1 );
			$nextMonth = $this->getCalendarLink($next[0], $next[1] );
		} else {
			$prevYear = "";
			$prevMonth = "";
			$nextYear = "";
			$nextMonth = "";
		}
		$header = (($showYear > 0) ? " " . $year : "") . "年" . $monthName."月";
		$s .= "<table class=\"bordertable\" width=\"100%\" border=\"1\" cellpadding=\"8\" cellspace=\"5\" style=\"border-color:#d4d0c8;\">\n";
		$s .= "<tr>\n";
		$s .= "<td colspan=\"7\" style=\"text-align:center;font-size:14px\">\n";
		$s .= $header;
		$s .= "</td>\n";
		$s .= "</tr>\n";
		
		if ($this->_showHeaderBar)
		{
    		$s .= "<tr>\n";
    		$s .= "<td style=\"text-align:center;\" valign=\"top\">" . (($prevMonth == "") ? " " : "<a href=\"$prevYear\"><img src=\"../img/first.png\" border=\"0\" alt=\"Previous year\"/>") . "</a></td>\n";
    		$s .= "<td style=\"text-align:center;\" valign=\"top\">" . (($prevMonth == "") ? " " : "<a href=\"$prevMonth\"><img src=\"../img/previous.png\" border=\"0\" alt=\"Previous month\"/>") . "</a></td>\n";
    		$s .= "<td style=\"text-align:center;\" valign=\"top\" colspan=\"3\" style=\"text-align:center;\"><a href=\"" . $this->url . "&month=".date("m")."&year=".date("Y")."\">今天:" . date("Y-m-d"). "</a></td>\n";
    		$s .= "<td style=\"text-align:center;\" valign=\"top\">" . (($nextMonth == "") ? " " : "<a href=\"$nextMonth\"><img src=\"../img/next.png\" border=\"0\" alt=\"Next month\"//>") . "</a></td>\n";
    		$s .= "<td style=\"text-align:center;\" valign=\"top\">" . (($nextMonth == "") ? " " : "<a href=\"$nextYear\"><img src=\"../img/last.png\" border=\"0\" alt=\"Next year\"/>") . "</a></td>\n";
    		$s .= "</tr>\n";
		}
		$s .= "<tr>\n";
		$s .= "<th width=\"14%\">" . $this->dayNames[($this->startDay) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $this->dayNames[($this->startDay + 1) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $this->dayNames[($this->startDay + 2) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $this->dayNames[($this->startDay + 3) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $this->dayNames[($this->startDay + 4) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $this->dayNames[($this->startDay + 5) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $this->dayNames[($this->startDay + 6) % 7] . "</th>\n";
		$s .= "</tr>\n";
		
		$d = $this->startDay + 1 - $first;
		
		while($d > 1){
			$d -= 7;
		}
		while($d <= $daysInMonth){
			$s .= "<tr>\n";
			for($i = 0; $i < 7; $i ++) {
				//<< begin add by dennis 2005-11-30 14:38:36
				$tooltip = array ("bgcolor" => "#fff", "ftcolor" => "#000", "tooltip" => "" );
				if ($d > 0 && $d <= $daysInMonth) {
					$tooltip = $this->getCellAttrs($year, $month, $d );
				}
				//>> end add by dennis 2005-11-30 14:38:47 
				$s .= "\t <td style=\"background:" . $tooltip["bgcolor"] . ";color:" . $tooltip["ftcolor"] . ";\" align=\"center\" valign=\"top\" " . $tooltip["tooltip"] . ">";
				if ($d > 0 && $d <= $daysInMonth) {
					$chineseDate = "";
					//<< begin add by dennis 2005-11-30 13:14:32 
					//$chineseDate = get_chinese_date(getdate(mktime(0, 0, 0, $month, $d, $year)));
					//>>end add by dennis
					$link = $this->getDateLink($d, $month, $year );
					$s .= (($link == "") ? $d . "<br>" . $chineseDate : "$d<br/>$link<br/>$chineseDate");
					//print $year."-".$month."-".$d."<br>";
				} else {
					$s .= " ";
				}
				$s .= isset($tooltip['distext']) ? $tooltip['distext'] : ' ';// add by Dennis 2014/02/11
				$s .= "</td>\n";
				$d ++;
			}
			
			$s .= "</tr>\n";
		}
		
		$s .= "</table>\n";
		
		return $s;
	}
	
	function getYearHTML($year) {
		$s = "";
		$prev = $this->getCalendarLink(1, $year - 1 );
		$next = $this->getCalendarLink(1, $year + 1 );
		
		$s .= "<table border=\"1\">\n";
		if ($this->_showHeaderBar)
		{
    		$s .= "<tr>";
    		$s .= "<td align=\"center\" valign=\"top\" align=\"left\">" . (($prev == "") ? " " : "<a href=\"$prev\"><<") . "</td>\n";
    		$s .= "<td class=\"headerCell\" valign=\"top\" align=\"center\">" . (($this->startMonth > 1) ? $year . " - " . ($year + 1) : $year) . "</td>\n";
    		$s .= "<td align=\"center\" valign=\"top\" align=\"right\">" . (($next == "") ? " " : "<a href=\"$next\">>>") . "</td>\n";
    		$s .= "</tr>\n";
		}
		$s .= "<tr>";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(0 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(1 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(2 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "</tr>\n";
		$s .= "<tr>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(3 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(4 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(5 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "</tr>\n";
		$s .= "<tr>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(6 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(7 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(8 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "</tr>\n";
		$s .= "<tr>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(9 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(10 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "<td valign=\"top\">" . $this->getMonthHTML(11 + $this->startMonth, $year, 0). "</td>\n";
		$s .= "</tr>\n";
		$s .= "</table>\n";
		
		return $s;
	}
	
	/**
	 * 调整日期
	 *
	 * @param number $month
	 * @param number $year
	 * @return array
	 */
	function adjustDate($month, $year) {
		$a = array ();		
		$a[0] = $month;
		$a[1] = $year;		
		while($a[0] > 12){
			$a[0] -= 12;
			$a[1] ++;
		}		
		while($a[0] <= 0){
			$a[0] += 12;
			$a[1] --;
		}		
		return $a;
	}// end adjustDate()
	
	public function setHeaderBarOff()
	{
	    $this->_showHeaderBar = false;
	}
}// end class 

