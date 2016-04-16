<?php
/** 
 * Best view set tab 4 Created by Dennis.lan (C) Lan Jiangtao 
 * Description: public functions 
 * $HeadURL: https://svn.areschina.com/svn/ehr/trunk/eHR/libs/functions.php 
 * $ $Id: functions.php 3852 2014-10-21 07:51:02Z dennis $ 
 * $Rev: 3852 $ $Date: 2014-10-21 15:51:02 +0800 (周二, 21 十月 2014) $ 
 * $Author: dennis $ $LastChangedDate: 2013-11-15 15:56:26 +0800 (Fri, 15 Nov 2013) 
 * $ ***********************************************************************
 */

/**
 * Security Check when php script execute
 * 
 * @param $check_value validate	the value
 * @param $url the url when error occur redirect
 * @return void, no return value
 */
function security_check($check_value, $url) {
	if (is_null ( $check_value ) || empty ( $check_value ) || ! isset ( $check_value )) {
		header ( 'Location: ' . $url );
		exit ();
	} // end if
}

/**
 * For debug only,print array element
 * 
 * @param $array array        	
 * @return no return value, for debug only
 */
function pr($array) {
	if (is_array ( $array )) {
		print "<pre>";
		print_r ( $array );
		print "</pre>";
	} else {
		print "<font color='red'><b> Not a array variable.</b></font><br/>";
	}
}

/**
 * get real database column name from a string
 * the string must be start with "input_"
 * help function of comb_query_where()
 * 
 * @param $columnname string the string start with "input_"
 * @return string after clear "input_"
 * @author Dennis last update 2005-12-02 14:44:28
 */
function get_real_column_name($columnname) {
	return str_replace ( "input_", "", $columnname );
}

/**
 * combination query where condition from post variables
 * 
 * @param $data array url variable array, sample: array["key"]="value"
 * @return string query where condition
 * @author dennis last update 2005-12-02 14:54:39
 */
function comb_query_where($data) {
	$query_where = "";
	if (is_array ( $data )) {
		foreach ( $data as $key => $value ) {
			if (! empty ( $value )) {
				$query_where .= " and " . get_real_column_name ( $key ) . " like '%$value%' ";
			}
		}
	}
	return $query_where;
}

/**
 * get element from an array
 * @param array $array
 * @param fixed $element
 * @return string|number
 */
function binary_search($array, $element) {
	/**
	 * Returns the found $element or 0
	 */
	$low = 0;
	$high = count ( $array ) - 1;
	while ( $low <= $high ) {
		$mid = floor ( ($low + $high) / 2 ); // C floors for you
		if ($element == $array [$mid]) {
			return $array [$mid];
		} else {
			if ($element < $array [$mid]) {
				$high = $mid - 1;
			} else {
				$low = $mid + 1;
			}
		}
	}
	return 0; // $element not found
}

/*
 * Get Employee Find Condition add by jack 2006-9-7
 */
function get_query_where_in($whereArray) {
	if (empty ( $whereArray )) {
		return "";
	} else {
		$size1 = count ( $whereArray );
		$combine_string = "";
		for($i = 0; $i < $size1; $i ++) {
			$combine_string .= "'" . $whereArray [$i] . "',";
		}
		$combine_string = substr ( $combine_string, 0, strlen ( $combine_string ) - 1 );
		return $combine_string;
	}
}

/**
 * get user real ip
 * 但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。
 * 要想透过代理服务器取得客户端的真实 IP 地址，就要使用 
 * Request.ServerVariables("HTTP_X_FORWARDED_FOR") 来读取。 * 
 * @author Jack last update 2006-11-2
 */
function get_real_ip() {
	static $realip = NULL;
	if ($realip !== NULL) {
		return $realip;
	}
	if (isset ( $_SERVER )) {
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$arr = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			/* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
			foreach ( $arr as $ip ) {
				$ip = trim ( $ip );
				if ($ip != 'unknown') {
					$realip = $ip;
					break;
				}
			}
		} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
			$realip = $_SERVER ['HTTP_CLIENT_IP'];
		} else {
			if (isset ( $_SERVER ['REMOTE_ADDR'] )) {
				$realip = $_SERVER ['REMOTE_ADDR'];
			} else {
				$realip = '0.0.0.0';
			}
		}
	} else {
		if (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
			$realip = getenv ( 'HTTP_X_FORWARDED_FOR' );
		} elseif (getenv ( 'HTTP_CLIENT_IP' )) {
			$realip = getenv ( 'HTTP_CLIENT_IP' );
		} else {
			$realip = getenv ( 'REMOTE_ADDR' );
		}
	}
	preg_match ( "/[\d\.]{7,15}/", $realip, $onlineip );
	$realip = ! empty ( $onlineip [0] ) ? $onlineip [0] : '0.0.0.0';
	return $realip;
}

/*
 * **************************************** 
 * this will return an array composed of a 4 item array for each language
 *  the os supports 1. full language abbreviation, like en-ca 
 *  2. primary language, like en 
 *  3. full language string, like English (Canada) 
 *  4. primary language string, like English 
 *  *****************************************
 */

// choice of redirection header or just getting language data
// to call this you only need to use the $feature parameter
function get_language() {
	// add by dennis 2010-09-10
	if (isset ( $GLOBALS ['config'] ['default_language'] ))
		return $GLOBALS ['config'] ['default_language'];
		// get the languages
	$a_languages = languages ();
	$index = '';
	$complete = '';
	// prepare user language array
	$user_languages = array ();
	// check to see if language is set
	if (isset ( $_SERVER ["HTTP_ACCEPT_LANGUAGE"] )) {
		// explode languages into array
		$languages = strtolower ( $_SERVER ["HTTP_ACCEPT_LANGUAGE"] );
		$languages = explode ( ",", $languages );
		foreach ( $languages as $language_list ) {
			// pull out the language, place languages into array of full and primary
			// string structure:
			$temp_array = array ();
			// slice out the part before ; on first step, the part before - on second, place into array
			$temp_array [0] = substr ( $language_list, 0, strcspn ( $language_list, ';' ) ); // full language
			$temp_array [1] = substr ( $language_list, 0, 2 ); // cut out primary language
			                                                // place this array into main $user_languages language array
			$user_languages [] = $temp_array;
		}
		
		// start going through each one
		for($i = 0; $i < count ( $user_languages ); $i ++) {
			foreach ( $a_languages as $index => $complete ) {
				if ($index == $user_languages [$i] [0]) {
					// complete language, like english (canada)
					$user_languages [$i] [2] = $complete;
					// extract working language, like english
					$user_languages [$i] [3] = substr ( $complete, 0, strcspn ( $complete, ' (' ) );
				} // end if
			} // end foreach
		} // end for
	} else {
		// if no languages found
		$user_languages [0] = array (
				'',
				'',
				'',
				'' 
		); // return blank array.
	}
	return $user_languages [0] [0];
}

// end get_language()

/**
 * full standard ISO language code
 *
 * @return array
 */
function languages() {
	// pack abbreviation/language array
	// important note: you must have the default language as the last item in each major language, after all the
	// en-ca type entries, so en would be last in that case
	$a_languages = array (
			'af' => 'Afrikaans',
			'sq' => 'Albanian',
			'ar-dz' => 'Arabic (Algeria)',
			'ar-bh' => 'Arabic (Bahrain)',
			'ar-eg' => 'Arabic (Egypt)',
			'ar-iq' => 'Arabic (Iraq)',
			'ar-jo' => 'Arabic (Jordan)',
			'ar-kw' => 'Arabic (Kuwait)',
			'ar-lb' => 'Arabic (Lebanon)',
			'ar-ly' => 'Arabic (libya)',
			'ar-ma' => 'Arabic (Morocco)',
			'ar-om' => 'Arabic (Oman)',
			'ar-qa' => 'Arabic (Qatar)',
			'ar-sa' => 'Arabic (Saudi Arabia)',
			'ar-sy' => 'Arabic (Syria)',
			'ar-tn' => 'Arabic (Tunisia)',
			'ar-ae' => 'Arabic (U.A.E.)',
			'ar-ye' => 'Arabic (Yemen)',
			'ar' => 'Arabic',
			'hy' => 'Armenian',
			'as' => 'Assamese',
			'az' => 'Azeri',
			'eu' => 'Basque',
			'be' => 'Belarusian',
			'bn' => 'Bengali',
			'bg' => 'Bulgarian',
			'ca' => 'Catalan',
			'zh-cn' => 'Chinese (China)',
			'zh-hk' => 'Chinese (Hong Kong SAR)',
			'zh-mo' => 'Chinese (Macau SAR)',
			'zh-sg' => 'Chinese (Singapore)',
			'zh-tw' => 'Chinese (Taiwan)',
			'zh' => 'Chinese',
			'hr' => 'Croatian',
			'cs' => 'Czech',
			'da' => 'Danish',
			'div' => 'Divehi',
			'nl-be' => 'Dutch (Belgium)',
			'nl' => 'Dutch (Netherlands)',
			'en-au' => 'English (Australia)',
			'en-bz' => 'English (Belize)',
			'en-ca' => 'English (Canada)',
			'en-ie' => 'English (Ireland)',
			'en-jm' => 'English (Jamaica)',
			'en-nz' => 'English (New Zealand)',
			'en-ph' => 'English (Philippines)',
			'en-za' => 'English (South Africa)',
			'en-tt' => 'English (Trinidad)',
			'en-gb' => 'English (United Kingdom)',
			'en-us' => 'English (United States)',
			'en-zw' => 'English (Zimbabwe)',
			'en' => 'English',
			'us' => 'English (United States)',
			'et' => 'Estonian',
			'fo' => 'Faeroese',
			'fa' => 'Farsi',
			'fi' => 'Finnish',
			'fr-be' => 'French (Belgium)',
			'fr-ca' => 'French (Canada)',
			'fr-lu' => 'French (Luxembourg)',
			'fr-mc' => 'French (Monaco)',
			'fr-ch' => 'French (Switzerland)',
			'fr' => 'French (France)',
			'mk' => 'FYRO Macedonian',
			'gd' => 'Gaelic',
			'ka' => 'Georgian',
			'de-at' => 'German (Austria)',
			'de-li' => 'German (Liechtenstein)',
			'de-lu' => 'German (Luxembourg)',
			'de-ch' => 'German (Switzerland)',
			'de' => 'German (Germany)',
			'el' => 'Greek',
			'gu' => 'Gujarati',
			'he' => 'Hebrew',
			'hi' => 'Hindi',
			'hu' => 'Hungarian',
			'is' => 'Icelandic',
			'id' => 'Indonesian',
			'it-ch' => 'Italian (Switzerland)',
			'it' => 'Italian (Italy)',
			'ja' => 'Japanese',
			'kn' => 'Kannada',
			'kk' => 'Kazakh',
			'kok' => 'Konkani',
			'ko' => 'Korean',
			'kz' => 'Kyrgyz',
			'lv' => 'Latvian',
			'lt' => 'Lithuanian',
			'ms' => 'Malay',
			'ml' => 'Malayalam',
			'mt' => 'Maltese',
			'mr' => 'Marathi',
			'mn' => 'Mongolian (Cyrillic)',
			'ne' => 'Nepali (India)',
			'nb-no' => 'Norwegian (Bokmal)',
			'nn-no' => 'Norwegian (Nynorsk)',
			'no' => 'Norwegian (Bokmal)',
			'or' => 'Oriya',
			'pl' => 'Polish',
			'pt-br' => 'Portuguese (Brazil)',
			'pt' => 'Portuguese (Portugal)',
			'pa' => 'Punjabi',
			'rm' => 'Rhaeto-Romanic',
			'ro-md' => 'Romanian (Moldova)',
			'ro' => 'Romanian',
			'ru-md' => 'Russian (Moldova)',
			'ru' => 'Russian',
			'sa' => 'Sanskrit',
			'sr' => 'Serbian',
			'sk' => 'Slovak',
			'ls' => 'Slovenian',
			'sb' => 'Sorbian',
			'es-ar' => 'Spanish (Argentina)',
			'es-bo' => 'Spanish (Bolivia)',
			'es-cl' => 'Spanish (Chile)',
			'es-co' => 'Spanish (Colombia)',
			'es-cr' => 'Spanish (Costa Rica)',
			'es-do' => 'Spanish (Dominican Republic)',
			'es-ec' => 'Spanish (Ecuador)',
			'es-sv' => 'Spanish (El Salvador)',
			'es-gt' => 'Spanish (Guatemala)',
			'es-hn' => 'Spanish (Honduras)',
			'es-mx' => 'Spanish (Mexico)',
			'es-ni' => 'Spanish (Nicaragua)',
			'es-pa' => 'Spanish (Panama)',
			'es-py' => 'Spanish (Paraguay)',
			'es-pe' => 'Spanish (Peru)',
			'es-pr' => 'Spanish (Puerto Rico)',
			'es-us' => 'Spanish (United States)',
			'es-uy' => 'Spanish (Uruguay)',
			'es-ve' => 'Spanish (Venezuela)',
			'es' => 'Spanish (Traditional Sort)',
			'sx' => 'Sutu',
			'sw' => 'Swahili',
			'sv-fi' => 'Swedish (Finland)',
			'sv' => 'Swedish',
			'syr' => 'Syriac',
			'ta' => 'Tamil',
			'tt' => 'Tatar',
			'te' => 'Telugu',
			'th' => 'Thai',
			'ts' => 'Tsonga',
			'tn' => 'Tswana',
			'tr' => 'Turkish',
			'uk' => 'Ukrainian',
			'ur' => 'Urdu',
			'uz' => 'Uzbek',
			'vi' => 'Vietnamese',
			'xh' => 'Xhosa',
			'yi' => 'Yiddish',
			'zu' => 'Zulu' 
	);
	return $a_languages;
}

/**
 * Check emp photo exists
 *
 * @param string $filename file name (full path)
 * @param string $filext file extension (.jpg .png ect..)
 * @return boolean
 * @author Dennis
 */
function checkFileExists($filename, $filext) {
	$file_exts = explode ( ',', $filext );
	// pr($file_exts);
	foreach ( $file_exts as $ext ) {
		//echo $filename.$ext.'<br/>';
		//if (@fopen ( $filename . $ext, 'r' ))
		if (file_exists($filename.$ext))
			return $filename . $ext;
	} // end foreach
	return null;
}

/**
 * 过滤数据
 *
 * @param string $data        	
 * @return string
 */
function dataFilter($data) {
	return htmlentities ( $data, ENT_QUOTES );
}

// end dataFilter()

/**
 * 提交资料后显示相应的成功,失败,消息的页面
 *
 * @param string $msg_text        	
 * @param string $msg_type        	
 * @return void
 * @author Dennis 2008-09-12
 *        
 */
function showMsg($msg_text, $msg_type = 'information') {
	$allow_msg_type = array (
			'information',
			'success',
			'error',
			'warning' 
	);
	if (in_array ( $msg_type, $allow_msg_type )) {
		// &target=iframe was added by TerryWang 2011-8-22
		header ( 'Location: ?scriptname=page_' . $msg_type . '&msgtxt=' .urlencode($msg_text) . "&target=iframe" );
		exit ();
	} else {
		trigger_error ( 'Undefined Message Type :' . $msg_type . '<br> Only support following message type:' . var_export ( $allow_msg_type ), E_USER_ERROR );
	} // end if
}

// end showMsg()
/*
function showMsgPage($msg_text, $msg_type = 'information', $back_url = null) {
	$t = ucfirst ( $msg_type );
	$page_header = <<<eof
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"   "http://www.w3.org/TR/html4/strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
        <head>
            <meta http-equiv="Content-Type"     content = "text/html; charset=utf-8"/>
            <title></title>
            <link rel="stylesheet" href="../css/blueprint/screen.css" type="text/css" media="screen, projection">
            <!--[if IE]>
                <link rel="stylesheet" href="../css/blueprint/ie.css" type="text/css" media="screen, projection">
            <![endif]-->
            <link rel="stylesheet" href="../css/default.css" type="text/css" media="screen, projection">
            <link rel="stylesheet" href="../css/jqueryui/themes/{$GLOBALS['config']['sys_param']['default_theme']}/jquery.ui.all.css" type="text/css" />
            <script src="../js/jqueryui/jquery-1.4.4.min.js" type = "text/javascript"></script>
            <script src="../js/jqueryui/jquery-ui-1.8.11.custom.min.js" type = "text/javascript"></script>
    <body class="page-container">
eof;
	$msg_header = <<<eof
       <div style="overflow-y:auto;overflow-x:hidden;padding:10px; margin-bottom:10px; width:500px;margin-left:auto;margin-right:auto;margin-top:15%;"
            class="ui-widget-content ui-corner-all">
        <h4 class="ui-widget-header ui-corner-all" id="panel_title" style="padding:5px;">
            <img src=../img/{$msg_type}.png>{$t}
        </h4>
        <div class="margin-left:20px;">
eof;
	$msg_footer = '
        </div>
        <div align="center">
            <hr size="1"/>
            <button class="ui-button ui-button-text-only ui-widget ui-state-default ui-corner-all" id="btn-back">
               <span class="ui-button-text">返回</span>
            </button>
        </div>
        </div>';
	$page_footer = <<<eof
        </body>
        <script type="text/javascript">
        $().ready(function(){
            $('#btn-back').click(function(){
                if ('{$back_url}' != '')
                {
                    $(document).attr('location','{$back_url}');
                }else{
                    history.back(-1);
                }
            });
        });
        </script>
        </html>
eof;
	echo $page_header . $msg_header . $msg_text . $msg_footer . $page_footer;
	exit ();
}
*/
// end showMsg()

/**
 * 从多语中 Get 下拉清单
 *
 * @param string $langcode 语言代码,如 ZHS,ZHT,EN
 * @param string $listid 多语的 name
 * @author Dennis
 * @return array
 */
function getMultiLangList($langcode, $listid) {
	global $g_db_sql;
	$sql = <<<eof
        select seq as list_value, value as list_label
          from app_muti_lang
         where name = :list_id
           and lang_code = :lang_code
           and type_code = 'LL'
eof;
	$g_db_sql->SetFetchMode ( ADODB_FETCH_NUM );
	return $g_db_sql->GetArray ( $sql, array (
			'list_id' => $listid,
			'lang_code' => $langcode 
	) );
}

// end getMultiLangList()

/**
 * Get Error Message
 *
 * @param string $programno        	
 * @param string $langcode        	
 * @param string $msgkey        	
 */
function getMultiLangMsg($programno, $langcode, $msgkey) {
	global $g_db_sql;
	$sql = <<<eof
        select value as msg
          from app_muti_lang
         where program_no = :program_no
           and lang_code  = :lang_code
           and name       = :msg_key
eof;
	//$g_db_sql->debug = true;
	return $g_db_sql->GetOne ( $sql, array (
			'program_no' => $programno,
			'lang_code' => $langcode,
			'msg_key' => $msgkey 
	) );
}

// end getMultiLangMsg()

/*
 * array 返回 option 的html
 */
function gf_getDropDownListHtml($list, $select_value) {
	$html = '';
	foreach ( $list as $key => $row ) {
		$selected = ($row ['ID'] == $select_value) ? 'selected' : '';
		$html .= "<option value='" . $row ['ID'] . "'  " . $selected . " > " . $row ['TEXT'] . " </option> \r\n";
	}
	return $html;
}

/* 結束時間 */
function gf_getEndTime($p_date, $p_begin_time, $p_end_time) {
	// 计算加班起始结束日期时间
	$_date = explode ( '-', $p_date );
	$_btime = explode ( ':', $p_begin_time );
	$_etime = explode ( ':', $p_end_time );
	$_begin_date = mktime ( $_btime [0], $_btime [1], 0, $_date [1], $_date [2], $_date [0] );
	$_end_date = mktime ( $_etime [0], $_etime [1], 0, $_date [1], $_date [2], $_date [0] );
	$begin_time = date ( 'Y-m-d H:i', $_begin_date );
	$end_time = date ( 'Y-m-d H:i', $_end_date );
	
	// 如果加班结束时间小于开始时间, 表示其跨天
	if ($_end_date < $_begin_date) {
		$end_time = date ( 'Y-m-d H:i', mktime ( $_etime [0], $_etime [1], 0, $_date [1], $_date [2] + 1, $_date [0] ) );
	}
	return $end_time;
}

// 取菜单标题多语
function gf_getMenuTitle($langcode = '', $menu_code = '') {
	global $g_db_sql;
	if (empty ( $menu_code ))
		return '';
	$sql = "select aml.value
          from  APP_MUTI_LANG aml
          where aml.PROGRAM_NO = 'HCP'
             and aml.type_code='MT'
             and aml.lang_code='" . $langcode . "'
             and aml.name='" . $menu_code . "'
        ";
	// echo $sql;
	return $g_db_sql->GetOne ( $sql );
}

// end getMultiLangList()
function recombineArray($data) {
	$result = false;
	if (is_array ( $data )) {
		$cnt = count ( $data );
		for($i = 0; $i < $cnt; $i ++) {
			$result [strtoupper ( $data [$i] [0] )] = $data [$i] [1];
		} // end loop
	} // end if
	return $result;
}

// end _recombineArray()
/**
 * 取得提示信息(error message)多语资料
 *
 * @param string $langcode
 *        	语言代码
 * @param string $programno
 *        	程式代码
 * @return array
 * @author Dennis
 */
function get_multi_lang($langcode, $programno) {
	global $g_db_sql;
	$sql = <<<eof
        select name,value
         from  app_muti_lang
        where  program_no  = :program_no
          and  lang_code   = :langcode
          and  type_code   = 'MP'
eof;
	$g_db_sql->SetFetchMode ( ADODB_FETCH_NUM );
	$rs = $g_db_sql->GetArray ( $sql, array (
			'program_no' => $programno,
			'langcode' => $langcode 
	) );
	return recombineArray ( $rs );
}

// 找多语值
function get_app_muti_lang($program_no, $name, $lang_code, $type_code = 'IT') {
	global $g_db_sql;
	$sql = "select VALUE
         from
            app_muti_lang
       where program_no='" . $program_no . "'
         and name='" . $name . "'
         and type_code='" . $type_code . "'
         and lang_code='" . $lang_code . "'
         ";
	$value = $g_db_sql->GetOne ( $sql );
	return $value;
}

/**
 * cols is define in table return true
 */
function is_defined_column($table_name, $col_name) {
	global $g_db_sql;
	$sql = "
        select 1 from all_tab_columns
        where table_name='" . $table_name . "'
        and column_name='" . $col_name . "'
        ";
	return $g_db_sql->GetOne ( $sql );
}

/**
 * 是否是设定程式
 *
 * @param string $scripname        	
 * @return boolean
 * @author Dennis 20090608
 */
function is_app_defined($scripname) {
	global $g_db_sql;
	$sql = 'select 1 from ehr_program_setup_master where program_no = upper(:program_no)';
	// $g_db_sql->debug = true;
	return $g_db_sql->GetOne ( $sql, array (
			'program_no' => $scripname 
	) );
}

/**
 * Try PHP header redirect, then Java redirect, then try http redirect.:
 * 
 * @param
 *        	$url
 * @return void
 */
/* function redirect($url) {
	if (! headers_sent ()) { // If headers not sent yet... then do php redirect
		header ( 'Location: ' . $url );
		exit ();
	} else { // If headers are sent... do java redirect... if java disabled, do html redirect.
		echo '<script type="text/javascript">';
		echo 'window.location.href="' . $url . '";';
		echo '</script>';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
		echo '</noscript>';
		exit ();
	}
} */

// end redirect()

/**
 * Turn on HTTPS - Detect if HTTPS, if not on, then turn on HTTPS:
 * 
 * @return void
 */
function SSLon() {
	if ($_SERVER ['HTTPS'] != 'on') {
		$url = "https://" . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
		redirect ( $url );
	}
}

// end SSLon()

/**
 * Turn Off HTTPS -- Detect if HTTPS, if so, then turn off HTTPS:
 * 
 * @return unknown_type
 */
function SSLoff() {
	if ($_SERVER ['HTTPS'] == 'on') {
		$url = "http://" . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
		redirect ( $url );
	}
}

// end SSLoff()

/**
 * Company Logo
 * 
 * @author Dennis 2010-09-07
 */
function getLogoUrl() {
	// default logo
	$company_logo = $GLOBALS ['config'] ['img_dir'] . '/logo.gif';
	$support_ext = array (
			'png',
			'gif',
			'jpg',
			'jpeg',
			'PNG',
			'GIF',
			'JPG',
			'JPEG' 
	);
	$cust_logo = $GLOBALS ['config'] ['upload_dir'] . '/userfile/' . 'com_logo';
	foreach ( $support_ext as $v ) {
		$f = $cust_logo . '.' . $v;
		if (file_exists ( $f ))
			return $f;
	}
	return $company_logo;
}
function encrypt($string, $key) {
	$result = '';
	for($i = 0; $i < strlen ( $string ); $i ++) {
		$char = substr ( $string, $i, 1 );
		$keychar = substr ( $key, ($i % strlen ( $key )) - 1, 1 );
		$char = chr ( ord ( $char ) + ord ( $keychar ) );
		$result .= $char;
	}
	return base64_encode ( $result );
}
function decrypt($string, $key) {
	$result = '';
	$string = base64_decode ( $string );
	for($i = 0; $i < strlen ( $string ); $i ++) {
		$char = substr ( $string, $i, 1 );
		$keychar = substr ( $key, ($i % strlen ( $key )) - 1, 1 );
		$char = chr ( ord ( $char ) - ord ( $keychar ) );
		$result .= $char;
	}
	return $result;
}

/**
 *
 *
 * 检查程式是否有授权
 * 
 * @param string $appid        	
 * @param array $sysmenu        	
 * @return boolean
 * @author Dennis 2011-03-09
 */
function hasPermission($appid, array $sysmenu) {
	echo 'appid->' . $appid . '<br/>';
	$public_app = array (
			'esn0000',
			'esn0000_1',
			'mdn0000',
			'mdn0000_1',
			'subframe',
			'public_template',
			'home',
			'leave_approve',
			'mystaff' 
	);
	if (in_array ( strtolower ( $appid ), $public_app ))
		return true;
		// 如果有共用的程式 rewrite appid 为原来的 appid,
		// 因为 sysmenu 中只记 ess 或 mss menu, 不会同时有
	foreach ( $GLOBALS ['config'] ['pub_app'] as $k => $v ) {
		// if ($appid == $k) $appid = $v;
	}
	// echo $appid;exit;
	if (is_array ( $sysmenu )) {
		$cc = count ( $sysmenu );
		for($j = 0; $j < $cc; $j ++) {
			if (strtolower ( $sysmenu [$j] ['NODEID'] ) == strtolower ( $appid )) {
				return true;
			}
		}
	}
	return false;
}

/**
 * 身份证15位转18位
 * $str 15位号码
 */
function id_card_15to18($str) {
	if (! preg_match ( "/^[1-9]d{14}$/", $str ))
		return false;
	$str = substr ( $str, 0, 6 ) . '19' . substr ( $str, 6 );
	$wi = array (
			7,
			9,
			10,
			5,
			8,
			4,
			2,
			1,
			6,
			3,
			7,
			9,
			10,
			5,
			8,
			4,
			2 
	);
	$ai = array (
			'1',
			'0',
			'X',
			'9',
			'8',
			'7',
			'6',
			'5',
			'4',
			'3',
			'2' 
	);
	for($i = 0; $i < 17; $i ++)
		$sigma += (( int ) $str {$i}) * $wi [$i];
	return $str . $ai [($sigma % 11)];
}

/**
 * 身份证校验函数
 * $str 15位或18位号码
 */
function is_id_card($str) {
	if (preg_match ( "/^[1-9]d{14}(d{2}[0-9X])?$/", $str )) {
		if (strlen ( $str ) == 18) {
			$s = substr ( $str, 0, 17 );
			$wi = array (
					7,
					9,
					10,
					5,
					8,
					4,
					2,
					1,
					6,
					3,
					7,
					9,
					10,
					5,
					8,
					4,
					2 
			);
			$ai = array (
					'1',
					'0',
					'X',
					'9',
					'8',
					'7',
					'6',
					'5',
					'4',
					'3',
					'2' 
			);
			for($i = 0; $i < 17; $i ++)
				$sigma += (( int ) $s {$i}) * $wi [$i];
			if ($ai [($sigma % 11)] == $str {17})
				return true;
			else
				return false;
		}
		return true;
	}
	return false;
}

/**
 * Get User Default Layout
 * @param unknown $g_db_sql
 * @return unknown
 * @author Terry
 */
/*
function getDefaultLayout($g_db_sql) {
	if (isset ( $_SESSION ['layout'] ))
		return $_SESSION ['layout'];
	$sql = "select default_layout 
    		  from ehr_md_sys_setting 
    		 where company_no = :company_id
           	   and user_name = :user_name";
	$res = $g_db_sql->GetOne ( $sql, array (
			'company_id' => $_SESSION ['user'] ['company_id'],
			'user_name' => $_SESSION ['user'] ['user_name'] 
	) );
	$_SESSION ['layout'] = $res;
	return $res;
}*/

/**
 *
 * @param $arr 2 dimensional
 * @param $key1 string        	
 * @param $key2, string $key2 职称多个键名用,分割
 * @param $seperator 分割符号,default .
 * @return $arr 1 dimensional 将二维数组$arr转化成一维数组,like $arr['221'] =
 * @author Terry
 */
function assArray($array, $key1, $key2, $seperator = '.') {
	$arr = array ();
	$key = explode ( ',', $key2 );
	$n = count ( $key );
	foreach ( $array as $v ) {
		if (isset ( $v [$key1] )) {
			$str = '';
			for($i = 0; $i < $n; $i ++) {
				$str .= getVar ( $v, $key [$i] ) . $seperator;
			}
			$arr [$v [$key1]] = rtrim ( $str, $seperator );
		}
	}
	return $arr;
}

/**
 *
 * @param $arr
 * @param $key 
 * @return $arr[$key]
 * @author Terry 2011-9-8
 */
function getVar($arr, $key) {
	if (! isset ( $arr ) || ! is_array ( $arr ) || empty ( $arr ) || ! isset ( $arr [$key] ))
		return null;
	return $arr [$key];
}

/**
 * Check User First Login
 * 
 * @author Dennis 20120302
 */
function first_login_check() {
	// check token
	if (isset ( $_GET ['fistlogin'] ) && isset ( $_GET ['token'] )) {
		if ($_GET ['token'] !== session_id ()) {
			showMsg ( 'Attack Error', 'error', $GLOBALS ['config'] ['ess_home'] );
			exit ();
		}
	}	
	// 除找回密码和 ESNS001(修改密码网页不需要 Show 必须修改密码 Message, 其它都需要)
	if (isset ( $_SESSION ['user'] ) && (! isset ( $_SESSION ['user'] ['not_first_login'] ) || $_SESSION ['user'] ['not_first_login'] === 'Y')) {
		
		if (! isset ( $_GET ['scriptname'] ) || ($_GET ['scriptname'] != 'ESNS001' && (! in_array ( $_GET ['scriptname'], $GLOBALS ['config'] ['none_auth_list'] )))) {
			showMsg ( '密碼不符合安全規則(數字+字母+特殊字符[!,@,#,$,%,^,&,*,?,_,~,-,£,(,)]),請更改密碼.<br/>點擊"返回"按鈕進入修改密碼頁面.', 'warning', $GLOBALS ['config'] ['ess_home'] . '/redirect.php?scriptname=ESNS001&firstlogin=1&token=' . session_id () );
			exit ();
		}
	}
}

/**
 * Get ess/mss menu by pid
 * 
 * @param array $menu        	
 * @param string $pid        	
 * @return array
 * @author Dennis 2012-08-20
 */
function getMenuItem($menu, $pid) {
	$menu_items = array ();
	$idx = 0;
	foreach ( $menu as $v ) {
		if ($v ['P_NODEID'] == $pid) {
			$menu_items [$idx] ['menu_code'] = $v ['NODEID'];
			$menu_items [$idx] ['menu_text'] = $v ['NODETEXT'];
			$idx ++;
		}
		if ($idx > 0) {
			$idx1 = 0;
			foreach ( $menu as $v ) {
				if ($v ['P_NODEID'] == $menu_items [$idx - 1] ['menu_code']) {
					
					$menu_items [$idx - 1] [$idx1] ['menu_code'] = $v ['NODEID'];
					$menu_items [$idx - 1] [$idx1] ['menu_text'] = $v ['NODETEXT'];
					$idx1 ++;
				}
			}
		}
	}
	return $menu_items;
}
/**
 * Get App desc by appid from $_SESSION['sys_menu']
 * 
 * @param string $appid        	
 * @param array $sys_menu        	
 * @return string
 * @author Dennis 2012-08-20
 */
function getAppDescById($appid, array $sys_menu) {
	if (is_array ( $sys_menu )) {
		foreach ( $sys_menu as $arr ) {
			if ($arr ['NODEID'] == $appid) {
				return $arr ['NODETEXT'];
			}
		}
	}
	return '';
}