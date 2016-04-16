<?php
/*************************************************************\
 *  Copyright (C) 2004 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *    Print Gridview  Dennis 2011-06-15
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/gridview_print_DB.php $
 *  $Id: gridview_print_DB.php 3152 2011-08-01 03:01:19Z dennis $$Rev: 3152 $   
 *  $LastChangedDate: 2008-11-21 09:26:45 +0800 (星期五, 21 十一月 2008) 
 *  $Author: dennis $ 
 ****************************************************************************/

	include_once 'gv_print_exp.php';
	$lang = $_SESSION['user']['language'];
	$appid = $_POST['appid'];
	$reportname = getReportName($appid,$lang);
	$g_tpl->assign('lastsql',$_POST['lastsql']);
	$g_tpl->assign('appid',$appid);
	$g_tpl->assign('DOCUMENT_TITLE',$reportname);
	$g_tpl->assign('reportname',$reportname);
	
	/**
	 * Get Thead HTML code
	 * @param array $col_title
	 */
	function getTabHeader($col_title)
	{
		$thead = '<thead>';
		foreach ($col_title as $val)
		{
			$thead .= '<th>'.$val['PROMPT_TEXT'].'</th>';
		}
		$thead .= '</thead>';
		return $thead;
	}
	
	function _getPageBreakBegin()
	{
		return '<div style="page-break-before: always;">';
	}
	
	function _getPageBreakEnd()
	{
		return '</div>';
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $numperpage
	 * @param unknown_type $startrow
	 */
	function getTabBody($data,$numperpage = 20,$startrow = 0)
	{
		$tbody = '<tbody>';
		$endrow = $startrow+$numperpage;
		for($i=$startrow; $i<$endrow; $i++)
		{
			if (isset($data[$i]))
			{
				$tbody .= '<tr>';
				foreach ($data[$i] as $val)
				{
					$tbody .= '<td>'.$val.'</td>';
				}
				$tbody .= '</tr>';
			}
		}
		$tbody .= '</tbody>';
		return $tbody;
	}
	
	function getTabBegin()
	{
		return '<table style="border-collapse:collapse;width:100%;" border="1">';
	}
	
	function getTabEnd()
	{
		return '</table>';
	}
	
	function getReport($sql,$appid,$lang,$numperpage = 20,$repeat_title = 1)
	{
		$data = getData($sql);
		$c = count($data);
		$rpt_html = '';
		$tabBegin = getTabBegin();
		$col_title = getGVColumnConfig($appid,$lang);
		$thead  = getTabHeader($col_title);		
		$tbody  = getTabBody($data,$numperpage);
		$tabEnd = getTabEnd();
		$rpt_html = $tabBegin.$thead.$tbody.$tabEnd;
		if ($c<=$numperpage) return $rpt_html;
		$page_num = ceil($c/$numperpage); // caculate number of pages
		$page_breaker_b = _getPageBreakBegin();
		$page_breaker_e = _getPageBreakEnd();
		for($i=1;$i<$page_num;$i++)
		{
			$rpt_html .= $page_breaker_b;
			$rpt_html .= $tabBegin;
			if ($repeat_title == 1)	$rpt_html .= $thead;
			$rpt_html .= getTabBody($data,$numperpage,$i*$numperpage);
			$rpt_html .= $tabEnd;
			$rpt_html .= $page_breaker_e;
		}
		return $rpt_html;
	}
	
	if (isset($_POST['print_action']) && $_POST['print_action'] != '')
	{
		$g_tpl->assign('sysdate',date('Y-m-d H:i:s'));
		$g_tpl->assign('author',$_SESSION['user']['emp_name']);
		$g_tpl->assign('reportname',$reportname);
		$repeate_title = isset($_POST['repeat_title']) ? $_POST['repeat_title'] : 0;
		$numperpage = isset($_POST['numperpage']) ? (int)$_POST['numperpage'] : 20;
		$g_tpl->assign('report_data',getReport($_POST['lastsql'],$appid,$lang,$numperpage,$repeate_title));
	}
	
	
	