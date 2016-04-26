<?php 

//add by bear[2016/3/16]
//thinkPHP实现移动端访问自动切换主题模板

use Think\Controller;

// function ParseTable($table_name,$data_source)
// {
// 	$recordset = $data_source;
// 	$Smarty->assign($table_name,$recordset);
// }



function getMonthView($m, $y,$show_header = 'Y'){
		$daynames;
	if(isset($m) && isset($y)){
		$a = adjustDate($m, $y);
		$month = $a['0'];
		$year = $a['1'];
		$daysInMonth = getDaysInMonth($month, $year);
		$date = getdate(mktime(12, 0, 0, $month, 1, $year));
		echo 'text17:21';
		$first = $date["wday"];
		$monthName = $month;
			
		$prev = adjustDate($month - 1, $year );
		$next = adjustDate($month + 1, $year );
			
		$prevYear = getCalendarLink($month, $year - 1 );
		$prevMonth = getCalendarLink($prev[0], $prev[1] );
		$nextYear = getCalendarLink($month, $year + 1 );
		$nextMonth = getCalendarLink($next[0], $next[1] );
			
		$showYear = 1;
			
		$header = (($showYear > 0) ? " " . $year : "") . "年" . $monthName."月";
		$s .= "<table class=\"bordertable\" width=\"100%\" border=\"1\" cellpadding=\"8\" cellspace=\"5\" style=\"border-color:#d4d0c8;\">\n";
		$s .= "<tr>\n";
		$s .= "<td colspan=\"7\" style=\"text-align:center;font-size:14px\">\n";
		$s .= $header;
		$s .= "</td>\n";
		$s .= "</tr>\n";
			
		$s .= "<tr>\n";
		$s .= "<td style=\"text-align:center;\" valign=\"top\">" . (($prevMonth == "") ? " " : "<a href=\"$prevYear\"><img src=\"../img/first.png\" border=\"0\" alt=\"Previous year\"/>") . "</a></td>\n";
		$s .= "<td style=\"text-align:center;\" valign=\"top\">" . (($prevMonth == "") ? " " : "<a href=\"$prevMonth\"><img src=\"../img/previous.png\" border=\"0\" alt=\"Previous month\"/>") . "</a></td>\n";
		$s .= "<td style=\"text-align:center;\" valign=\"top\" colspan=\"3\" style=\"text-align:center;\"><a href=\"" . "&month=".date("m")."&year=".date("Y")."\">今天:" . date("Y-m-d"). "</a></td>\n";
		$s .= "<td style=\"text-align:center;\" valign=\"top\">" . (($nextMonth == "") ? " " : "<a href=\"$nextMonth\"><img src=\"../img/next.png\" border=\"0\" alt=\"Next month\"//>") . "</a></td>\n";
		$s .= "<td style=\"text-align:center;\" valign=\"top\">" . (($nextMonth == "") ? " " : "<a href=\"$nextYear\"><img src=\"../img/last.png\" border=\"0\" alt=\"Next year\"/>") . "</a></td>\n";
		$s .= "</tr>\n";
			
		$startDay = 0;
			
		$s .= "<tr>\n";
		$s .= "<th width=\"14%\">" . $dayNames[($startDay) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $dayNames[($startDay + 1) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $dayNames[($startDay + 2) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $dayNames[($startDay + 3) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $dayNames[($startDay + 4) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $dayNames[($startDay + 5) % 7] . "</th>\n";
		$s .= "<th width=\"14%\">" . $dayNames[($startDay + 6) % 7] . "</th>\n";
		$s .= "</tr>\n";
			
		$d = $startDay + 1 - $first;
			echo 'text17:21';
		while($d > 1){
			$d -= 7;
		}
		while($d <= $daysInMonth){
			$s .= "<tr>\n";
			for($i = 0; $i < 7; $i ++) {
				//<< begin add by dennis 2005-11-30 14:38:36
				$tooltip = array ("bgcolor" => "#fff", "ftcolor" => "#000", "tooltip" => "" );
				if ($d > 0 && $d <= $daysInMonth) {
					$tooltip = getCellAttrs($year, $month, $d );
				}
				//>> end add by dennis 2005-11-30 14:38:47
				$s .= "\t <td style=\"background:" . $tooltip["bgcolor"] . ";color:" . $tooltip["ftcolor"] . ";\" align=\"center\" valign=\"top\" " . $tooltip["tooltip"] . ">";
				if ($d > 0 && $d <= $daysInMonth) {
					$chineseDate = "";
					//<< begin add by dennis 2005-11-30 13:14:32
					//$chineseDate = get_chinese_date(getdate(mktime(0, 0, 0, $month, $d, $year)));
					//>>end add by dennis
					$link = getDateLink($d, $month, $year );
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
			
		dump($prevMonth);
		echo $monthName;
		exit();
		dump($a);
	}
}

function formatNumber($n) {
	if ((int)$n < 10) {
		return "0" .(int)$n;
	}
	return $n;
}

function getDateLink($day, $month, $year) {
	$fullMonth = formatNumber($month );
	$_fullDay = formatNumber($day );
	$link = "";

	if (is_array($calendar)&& isset($calendar[$day - 1]["SHIFT_IN_TIME"])&& $calendar[$day - 1]["SHIFT_IN_TIME"] == "$year-$fullMonth-$_fullDay" && isset($calendar[$day - 1]["ABSENCE_NAME"] )) {
		$link = "<a href=\"";
		$link .= $leaveDetailUrl;
		$link .= "&myday=";
		$link .= $calendar[$day - 1]["SHIFT_IN_TIME"];
		$link .= "\">";
		$link .= $calendar[$day - 1]["ABSENCE_NAME"];
		$link .= "</a>";
	}
	return $link;
}


function getCellAttrs($year, $month, $day) {
	$calendar;
	$fullMonth = formatNumber($month );
	$_fullDay = formatNumber($day );

	$_tooltipHtml = "无排程/No Shift Data"; // add default value 2006-04-20 17:17:11 by dennis
	$_cellAtts = array ();
	// Clear cell attributes
	/*$_cellAtts = array("bgcolor"=>"",
	 "ftcolor"=>"",
	 "tooltip"=>"");*/
	
	$legendColors = array ("today" => "#FFD6EB",
			"weekendBgColor" => "#93FF93",
			"weekendFtColor" => "#6600FF",
			"legalHoliday" => "#C641C6",
			"exceptionDay" => "#F8EDC6",
			"unschedule" => "#C6C3C6",
			"fontcolor" => "#000000",
			"normalDay" => "#FFFFFF" );
	
	$legendColors = array ("today" => "#FFD6EB",
			"weekendBgColor" => "#93FF93",
			"weekendFtColor" => "#6600FF",
			"legalHoliday" => "#C641C6",
			"exceptionDay" => "#F8EDC6",
			"unschedule" => "#C6C3C6",
			"fontcolor" => "#000000",
			"normalDay" => "#FFFFFF" );
	
	$_cellAtts["ftcolor"] = $legendColors["fontcolor"];
	// add by dennis 2006-04-20 17:14:47
	$_cellAtts["bgcolor"] = $legendColors["unschedule"];
	$dday = 0;
	if (is_array($calendar)&& count($calendar)> 0) {
			
		$pieces = explode("-", $calendar[0]["SHIFT_IN_TIME"] );
		$startworktime = mktime(0, 0, 0, $pieces[1], $pieces[2], $pieces[0] ); //工作开始时间戳
		$starttime = mktime(0, 0, 0, $month, $day, $year ); //每月的开始时间戳
		$startday = substr($calendar[0]["SHIFT_IN_TIME"], - 2 ); //开始工作时间日期

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
			
		if (isset($calendar[$dday - 1]["SHIFT_IN_TIME"])&& ("$starttime" > "$startworktime" or ($calendar[$dday - 1]["SHIFT_IN_TIME"] == "$year-$fullMonth-$_fullDay"))) {
			//预设是正常工作日
			$_cellAtts["bgcolor"] = $legendColors["normalDay"];
			$_tooltipHtml = $calendar[$dday - 1]["WORKGROUP_NAME"];
			$_tooltipHtml .= "\n-------------------\n" . $calendar[$dday - 1]["IN_TIME"];
			$_tooltipHtml .= "\n" . $calendar[$dday - 1]["OUT_TIME"];

			if ((isset($calendar[$dday - 1]["HOLIDAY_CODE"])&& $calendar[$dday - 1]["HOLIDAY_CODE"] == "S")) {
				// get week-end day bgcolor and fontcolor
				$_cellAtts["bgcolor"] = $his->legendColors["weekendBgColor"];
				$_cellAtts["ftcolor"] = $legendColors["weekendFtColor"];
				$_tooltipHtml = "\n" . $calendar[$dday - 1]["HOLIDAY"];
			} // end 周末


			// 国定假日
			if (isset($calendar[$dday - 1]["HOLIDAY_CODE"])&& $calendar[$dday - 1]["HOLIDAY_CODE"] == "H") {
				// get legal holiday bgcolor and name
				$_cellAtts["bgcolor"] = $legendColors["legalHoliday"];
				$_tooltipHtml = "\n" . $calendar[$dday - 1]["HOLIDAY"] . "\n";
			} // end 国定假日
		}
	}
	if ($dday>0){
		$_cellAtts['distext'] = isset($calendar[$dday - 1]["HOLIDAY"]) ?
		$calendar[$dday - 1]["HOLIDAY"] :
		(isset($calendar[$dday - 1]["WORKGROUP_NAME"]) ? $calendar[$dday - 1]["WORKGROUP_NAME"] : '');
	}
	// 今天的背景色优先级最高
	$_cellAtts["bgcolor"] = $year . $fullMonth . $_fullDay == date("Ymd")? $legendColors["today"] : $_cellAtts["bgcolor"];
	$_cellAtts["tooltip"] = 'title="' . $_tooltipHtml . '"';
	return $_cellAtts;
}


function getCalendarLink($month, $year) {
	return "&month=" . $month . "&year=" . $year;
}

function getDaysInMonth($month, $year) {
	if ($month < 1 || $month > 12) {
		return 0;
	}

	$d = $month;

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
}

function getMenuItem($menu, $pid) {
	$menu_items = array ();
	$idx = 0;
	foreach ( $menu as $v ) {
		if ($v ['p_nodeid'] == $pid) {
			$menu_items [$idx] ['menu_code'] = $v ['nodeid'];
			$menu_items [$idx] ['menu_text'] = $v ['nodetext'];
			//$menu_items [$idx] ['menu_id'] = '1234';
			$idx ++;
		}
		if ($idx > 0) {
			$idx1 = 0;
			foreach ( $menu as $v ) {
				if ($v ['p_nodeid'] == $menu_items [$idx - 1] ['menu_code']) {
						
					$menu_items [$idx - 1] ['menu_id'] [$idx1] ['menu_code'] = $v ['nodeid'];
					$menu_items [$idx - 1] ['menu_id'] [$idx1] ['menu_text'] = $v ['nodetext'];
					$idx1 ++;
				}
			}
		}
	}
	return $menu_items;
}

function GetMenu($user_seqno,$sys_name)
	{
		//$_dBConn->debug = true;
		$stmt = 'begin pk_erp.p_set_segment_no(:company_id); pk_erp.p_set_username(:user_seq_no);end;';
		$_dBConn->Execute($stmt,array('company_id'=>$_companyId,
										    'user_seq_no'=>$user_seqno));
		// follow statement for improve performance
		/* remark by dennis 2011-08-02 閺堫亞鏁ら崚棰佷簰娑撳娈� temporary table
		$_dBConn->Execute('delete from ess_userfunction_sz');
		$_dBConn->Execute('insert into ess_userfunction_sz
								  select rolefunction
								    from app_userfunction
								   where rolefunction_type != \'ROOT\'
								   start with userrole = :user_seq_no
								  connect by userrole = prior rolefunction',
								array('user_seq_no'=>$user_seqno));
								*/
		// Get app menu tree structure data
		$_view_name = $sys_name.'_function_menu_v'; // get view name start by mgr_ or ess_
		$sql = <<<eof
			select program_no   as nodeid,
				   program_name as nodetext,
				   parent_id    as p_nodeid,
				   program_type as nodetype
			  from $_view_name
			 where parent_id <> 'ROOT'
eof;
		$_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $_dBConn->GetArray($sql);
	}


	
	function _initialize(){
		//移动设别浏览，则切换模板
		return "1";
		/* if(ismobile()){
			//设置默认默认主题为Mobile
			C('DEFAULT_THEME','Mobile'); */
		}
		//更多我的代码

	

	
	function _recombineArray($data)
	{
		$result = false;
		if (is_array($data))
		{
			$cnt = count($data);
			for ($i=0; $i<$cnt; $i++)
			{
				//$result[strtoupper($data[$i][0])] = $data[$i][1];
				$result[$data[$i][0]] = $data[$i][1]; // remove the strtoupper() by dennis 2014/01/03 鎶婂�艰浆鎴愬ぇ鍐欎笉鍚堢悊
			}// end loop
		}// end if
		return $result;
	}
	
	function GetOne($sql,$inputarr=false)
	{
		global $ADODB_COUNTRECS,$ADODB_GETONE_EOF;
		$crecs = $ADODB_COUNTRECS;
		$ADODB_COUNTRECS = false;
	
		$ret = false;
		$rs->$Execute($sql,$inputarr);
		if ($rs) {
			if ($rs->EOF) $ret = $ADODB_GETONE_EOF;
			else $ret = reset($rs->fields);
				
			$rs->Close();
		}
		$ADODB_COUNTRECS = $crecs;
		return $ret;
	}
	
// 	function Execute($sql,$inputarr=false)
// 	{
// 		if ($fnExecute) {
// 			$fn = $fnExecute;
// 			$ret = $fn($this,$sql,$inputarr);
// 			if (isset($ret)) return $ret;
// 		}
// 		if ($inputarr) {
// 			if (!is_array($inputarr)) $inputarr = array($inputarr);
				
// 			$element0 = reset($inputarr);
// 			# is_object check because oci8 descriptors can be passed in
// 			$array_2d = is_array($element0) && !is_object(reset($element0));
// 			//remove extra memory copy of input -mikefedyk
// 			unset($element0);
				
// 			if (!is_array($sql) && !$_bindInputArray) {
// 				$sqlarr = explode('?',$sql);
// 				$nparams = sizeof($sqlarr)-1;
// 				if (!$array_2d) $inputarr = array($inputarr);
// 				foreach($inputarr as $arr) {
// 					$sql = ''; $i = 0;
// 					//Use each() instead of foreach to reduce memory usage -mikefedyk
// 					while(list(, $v) = each($arr)) {
// 						$sql .= $sqlarr[$i];
// 						// from Ron Baldwin <ron.baldwin#sourceprose.com>
// 						// Only quote string types
// 						$typ = gettype($v);
// 						if ($typ == 'string')
// 							//New memory copy of input created here -mikefedyk
// 							$sql .= $qstr($v);
// 							else if ($typ == 'double')
// 								$sql .= str_replace(',','.',$v); // locales fix so 1.1 does not get converted to 1,1
// 								else if ($typ == 'boolean')
// 									$sql .= $v ? $true : $false;
// 									else if ($typ == 'object') {
// 										if (method_exists($v, '__toString')) $sql .= $qstr($v->__toString());
// 										else $sql .= $qstr((string) $v);
// 									} else if ($v === null)
// 										$sql .= 'NULL';
// 										else
// 											$sql .= $v;
// 											$i += 1;
	
// 											if ($i == $nparams) break;
// 					} // while
// 					if (isset($sqlarr[$i])) {
// 						$sql .= $sqlarr[$i];
// 						if ($i+1 != sizeof($sqlarr)) $outp_throw( "Input Array does not match ?: ".htmlspecialchars($sql),'Execute');
// 					} else if ($i != sizeof($sqlarr))
// 						$outp_throw( "Input array does not match ?: ".htmlspecialchars($sql),'Execute');
	
// 						$ret = $_Execute($sql);
// 						if (!$ret) return $ret;
// 				}
// 			} else {
// 				if ($array_2d) {
// 					if (is_string($sql))
// 						$stmt = $Prepare($sql);
// 						else
// 							$stmt = $sql;
	
// 							foreach($inputarr as $arr) {
// 								$ret = $_Execute($stmt,$arr);
// 								if (!$ret) return $ret;
// 							}
// 				} else {
// 					$ret = $_Execute($sql,$inputarr);
// 				}
// 			}	
// 		} else {
// 			$ret = $_Execute($sql,false);
// 		}
	
// 		return $ret;
// 	}
?>