<?php
/*************************************************************\
 *  Copyright (C) 2008 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     User define report wizard
 *     
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/user_def_rpt_wiz_DB.php $
 *  $Id: user_def_rpt_wiz_DB.php 3828 2014-08-20 07:11:27Z dennis $
 *  $Rev: 3828 $ 
 *  $Date: 2014-08-20 15:11:27 +0800 (周三, 20 八月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-08-20 15:11:27 +0800 (周三, 20 八月 2014) $
 ****************************************************************************/
if (!defined('DOCROOT')) die ('Attack Error.');

require_once 'EUC/Wizard.php';
$w = new EUC_Wizard();
// default list all ess module
$g_tpl->assign('module_list',$w->getModule());

if (isset($_GET['action']) && $_GET['action'] == 'update' && 
    isset($_GET['rptid']) && !empty($_GET['rptid']) && !isset($_POST['func']))
{
	$menucode = $_GET['rptid'];
	$g_parser->ParseOneRow($w->getRptMaster($menucode));
	$g_tpl->assign('cols_list',json_encode($w->getRptCols($menucode)));
}

if (isset($_POST['func']) && !empty($_POST['func']))
{
	if (isset($_POST['ajaxcall']) && $_POST['ajaxcall'] == 1)
	{
		if (method_exists($w,$_POST['func']))
		{
			$r = call_user_func_array(array($w,$_POST['func']),array($_POST['arg1']));
		}else{
			$r['error'] = '无此功能:'.$_POST['func'];
		}
		exit(json_encode($r));
	}
	elseif($_POST['func'] == 'save' || $_POST['func'] == 'update')
	{
		/**
		 * Get column group according the user setting
		 * @param array $colgrp
		 * @param string $colname
		 * @return string
		 * @author Dennis 2011-05-11
		 */
		function getGroupId($colgrp,$colname)
		{
			$grpid = '';
			if (is_array($colgrp))
			{
				foreach($colgrp as $k=>$v)
				{
					if (in_array($colname,$v['cols_list']))
					{
						$grpid = $v['group_id'];
						break;
					}
				}
			}
			return $grpid;
		}
		/**
		 * Get column group setting
		 * Array
		(
		    [0] => Array
		        (
		            [grp_name] => sdfd
		            [col_list] => ID_NO_SZ,NAME_SZ
		        )		
		    [1] => Array
		        (
		            [grp_name] =>  aaa
		            [col_list] => PERIOD_MASTER_NO,PERIOD_MASTER_DESC
		        )
		)
		 * @param string $menucode
		 * @param string $colgrups  json string
		 * @return array
		 * @access global
		 * @author Dennis 2011-05-12
		 */
		function getColGroup($menucode,$colgrups)
		{
			$colgrp          = json_decode($colgrups,true);
			$rpt_colgrp      = '';
			$rpt_colgrp_lang = '';
			if (is_array($colgrp))
			{
				foreach ($colgrp as $k=>$v)
				{
					$rpt_colgrp[$k]['program_no'] = $menucode;
					$rpt_colgrp[$k]['group_id']   = $k+1;
					$rpt_colgrp[$k]['group_name'] = $menucode.'-'.$k;
					$rpt_colgrp[$k]['group_desc'] = $v['grp_name'];
					$rpt_colgrp[$k]['muti_lang_pk'] = $menucode.'-'.$k;
					$rpt_colgrp[$k]['sort_seq']   = $k+1;
					$rpt_colgrp[$k]['cols_list']  = explode(',',$v['col_list']);
					// colgroup lang
					$rpt_colgrp_lang[$k]['program_no'] = $menucode;
					$rpt_colgrp_lang[$k]['lang_code']  = 'ZHS';
					$rpt_colgrp_lang[$k]['grp_desc']   = $v['grp_name'];
					$rpt_colgrp_lang[$k]['muti_lang_pk'] = $menucode.'-'.$k;
				}
			}
			$rptcolgrp  = array($rpt_colgrp,$rpt_colgrp_lang);
			return $rptcolgrp;
		}
		
		/*
		Array
		(
		    [wiz_step] => 5
		    [func] => save
		    [colsattr] => {"ID_NO_SZ":{"data_type":"","ui_type":"","chked_val":"","tgt_url":"",
		    						   "query_allow":"","range_allow":"","groupby_allow":"",
		    						   "groupby_type":"","date_fmt":"","num_format":"","col_width":"",
		    						   "txt_align":"","dec_num":"","unsign_fmt":"","uf_font_color":"",
		    						   "uf_bg_color":"","is_list_cond":"","list_data_source":"",
		    						   "formual_val":"","sort_allow":"1","sort_type":"","sort_seq":"0",
		    						   "col_label_cn":"","col_label_tw":"","col_label_en":""},
		    [colsgroup] => 
		    [report_name] => 12
		    [layout_type] => 2
		    [allow_paging] => 1
		    [numperpage] => 20
		    [pagerbarpos] => bottom
		    [allow_sort] => 1
		    [allow_query] => 1
		    [datasource] => HR_RPT_EMPLOYEE_V
		    [default_where] => 
		    [data_type] => 
		    [ui_type] => text
		    [chked_val] => 
		    [col_width] => 
		    [tgt_url] => 
		    [sort_type] => 
		    [sort_seq] => 
		    [groupby_type] => count
		    [date_fmt] => 
		    [dec_num] => 
		    [unsign_fmt] => 
		    [uf_font_color] => 
		    [uf_bg_color] => 
		    [is_list_cond] => 
		    [list_data_source] => 
		    [col_label_cn] => 
		    [col_label_tw] => 
		    [col_label_en] => 
		    [username] => 
		    [empname] => 
		)*/
		/**
		 * 
		 * Check post value issetting
		 * @param string $v post variable index
		 * @return string
		 */
		function getVal($v)
		{
			return isset($_POST[$v]) ? $_POST[$v] : '';
		}
		
		/**
		 * Parse default where
		 * 
		 * @param string $wherestr
		 * @return string
		 */
		function _parseWhereConst($wherestr)
		{
			$patterns = array('/1.用户公司代码/',
							  '/2.用户部门代码/',
							  '/3.用户员工代码/',
							  '/4.系统日期/');
			$replacements = array(':companyid',':deptid',':empid','sysdate');
			return preg_replace($patterns,$replacements,$wherestr);
		}
		/**
		 * 
		 * Get least result sql
		 * 
		 * @param string $tablenane
		 * @param array  $cols
		 * @param string $defwhere
		 */
		function getLastSQL($tablenane,$cols,$defwhere)
		{
			$cols_list 	   = '';
			$orderby_cols  = '';
			$orderby       = '';
			$groupby_cols  = '';
			$groupby       = '';
						
			$def_where = _parseWhereConst($defwhere);
			// collect order by columns
			$j = 0;
			foreach($cols as $k=>$v)
			{
				if ($v['sort_allow'] == '1')
				{
					$orderby_cols[$v['sort_seq']] = $k;
				}
				if ($v['groupby_allow'] == '1')
				{
					//echo 'group by '.$k.'<br/>';
					$groupby_cols[$j]['col'] = $k;
					$groupby_cols[$j]['stts_type'] = $v['groupby_type'];
					$j++;
				}else{
					$cols_list .= $k.' as '.str_replace('.','-',$k).',';
				}
			}
			// group by columns (all columns)
			if ($j>0)
			{
				foreach ($cols as $k=>$v)
				{
					if ($v['groupby_allow'] != '1')	
					$groupby .= $k.',';
				}
				
				$groupby = substr($groupby,0,-1);
				
				foreach ($groupby_cols as $v)
				{
					$col_name = str_replace('.','-',$v['col']);
					$stts_type = (empty($v['stts_type']) ? 'count' : $v['stts_type']);
					$col       =  $stts_type.'('.$v['col'].') ';
					$cols_list .= $col.' as '.$stts_type.'_'.$col_name.',';
				}
			}
			
			$cols_list = substr($cols_list,0,-1);
			
			if (is_array($orderby_cols))
			{
				foreach ($orderby_cols as $v)
				{
					$orderby .= $v.',';
				}
			}
			$orderby = substr($orderby,0,-1);
			$tabs  = _getFromTabList($tablenane);
			$sql = 'select '.$cols_list.
			        ' from '.$tabs.
				   (!empty($def_where) ?
				   ' where '.$def_where : '').
				   (!empty($groupby)? ' group by '.$groupby : '').
				   (!empty($orderby) ?
				' order by '.$orderby : '');
			
			for($i=0; $i<count($tablenane);$i++)
			{
				$sql = str_replace($tablenane[$i].'.','T'.($i+1).'.',$sql);
				$sql = str_replace($tablenane[$i].'-','T'.($i+1).'_',$sql);
			}
			//exit(print($sql));
			return $sql;
		}
		/**
		 * Help Function of getLastSql
		 * @param array $tab_arr tables list array
		 * @return string
		 * @author Dennis
		 */
		function _getFromTabList(array $tab_arr)
		{
			$from_tabs = '';
			for($i=0; $i<count($tab_arr); $i++)
			{
				$from_tabs .= $tab_arr[$i].' t'.($i+1).',';
			}
			return substr($from_tabs,0,-1);
		}
		
		function getGroupbyFunc($stts_func)
		{
			$func_desc = array('count'=>'计数',
							   'avg'=>'平均',
							   'min'=>'最小',
							   'max'=>'最大',
							   'sum'=>'加总');
			
			return $func_desc[$stts_func];
		}
		
		$rbac = new EUC_RBAC();
		 // insert 菜单成功将返回菜单代码(菜单代码由后台自动生成)
		$menucode = $_POST['func'] == 'save' ? $rbac->addMenu($_POST['moduleid'],$_POST['report_name']) : $_POST['program_no'];
		//var_dump($menucode);
		if ($menucode !== false)
		{
			$cols       = isset($_POST['colsattr']) ? json_decode($_POST['colsattr'],true) : null;
			$rpt_master = '';
			$rpt_master['program_no']		= $menucode;
			$rpt_master['page_size']		= getVal('numperpage');
			$rpt_master['default_where']	= getVal('default_where');
			$rpt_master['default_order_by']	= '';
			$rpt_master['allow_paging'] 	= getVal('allow_paging');
			$rpt_master['allow_sorting']	= getVal('allow_sort');
			$rpt_master['header_paging']	= (getVal('pagerbarpos') == 'both'||getVal('pagerbarpos') == 'top') ? 1 : 0;
			$rpt_master['footer_paging'] 	= (getVal('pagerbarpos') == 'both'||getVal('pagerbarpos') == 'bottom') ? 1 : 0;
			$rpt_master['allow_querying'] 	= getVal('allow_query');
			$rpt_master['application_type']	= getVal('layout_type')== 2 ? 'gridview' : 'singlerow';
			$rpt_master['allow_grouping'] 	= getVal('allow_grouping');
			$rpt_master['allow_stts_grp']   = getVal('allow_stts_group');
			$rpt_master['allow_exp']		= getVal('allow_exp');
			$rpt_master['allow_print'] 		= getVal('allow_print');
			$rpt_master['result_sql']  		= getLastSQL(getVal('datasource'), $cols, getVal('default_where'));
			
			// for store multiple datasources
			$data_source = '';
			$ds_list = getVal('datasource');
			$cnt = count($ds_list);
			for ($i=0; $i<$cnt; $i++)
			{
				$data_source[$i]['table_name']			= $ds_list[$i];
				$data_source[$i]['table_allies_name']	= 't'.$i;
				$data_source[$i]['program_no']			= $menucode;
			}
			$rpt_col_grp = '';
			if (isset($_POST['colsgroup']))
			{
				$rpt_col_grp = getColGroup($menucode,$_POST['colsgroup']); 
			}
			$rpt_cols     = '';
			$rpt_col_lang = '';
			
			//$tablename= $_POST['datasource']; unused
			if(is_array($cols))
			{
				$i = 0;
				foreach ($cols as $k=>$val)
				{
					if ($val['groupby_allow'] != '1')
					{
						$rpt_cols[$i]['program_no']     = $menucode;
						$rpt_cols[$i]['table_name']     = 'T1';
						$rpt_cols[$i]['column_name']    = $k;
						$rpt_cols[$i]['data_type']      = empty($val['attr_data_type']) ? 'vachar2' : $val['attr_data_type'];
						$rpt_cols[$i]['allow_sorting']  = empty($val['sort_allow']) ? '0' : $val['sort_allow'];
						$rpt_cols[$i]['width']          = $val['col_width'];
						$rpt_cols[$i]['height']         = '';
						$rpt_cols[$i]['align']          = $val['txt_align'];
						$rpt_cols[$i]['class_name']     = '';
						$rpt_cols[$i]['format_str']     = $val['date_fmt'];
						$rpt_cols[$i]['column_type']    = $val['ui_type'];
						$rpt_cols[$i]['bgcolor']        = $val['uf_bg_color'];
						$rpt_cols[$i]['font_color']     = $val['uf_bg_color'];
						$rpt_cols[$i]['font_name']      = '';
						$rpt_cols[$i]['checked_value']  = $val['chked_val'];
						$rpt_cols[$i]['data_source']    = $val['list_data_source'];
						$rpt_cols[$i]['muti_lang_pk']   = 'T_'.strtoupper($k);
						$rpt_cols[$i]['column_seq']     = $i;
						$rpt_cols[$i]['display']        = 1;
						$rpt_cols[$i]['allow_querying'] = $val['query_allow'];
						$rpt_cols[$i]['is_rang_condition']   = $val['range_allow'];
						$rpt_cols[$i]['query_column_type']   = !empty($val['list_data_source']) ? 'list' : 'text';
						$rpt_cols[$i]['data_source_type']    = $val['is_list_cond'];
						$rpt_cols[$i]['group_id']            = getGroupId($rpt_col_grp[0],$k);
						$rpt_cols[$i]['column_actual_value'] = $val['formual_val'];
						$rpt_cols[$i]['tgt_url']      = $val['tgt_url'];
						$rpt_cols[$i]['groupby_type'] = $val['groupby_type'];
						$rpt_cols[$i]['date_fmt']     = $val['date_fmt'];
						$rpt_cols[$i]['num_format']   = $val['num_format'];
						$rpt_cols[$i]['dec_num']      = $val['dec_num']; 
						$rpt_cols[$i]['unsign_fmt']   = $val['unsign_fmt'];
						$rpt_cols[$i]['uf_font_color']= $val['uf_font_color'];
						$rpt_cols[$i]['uf_bg_color']  = $val['uf_bg_color'];
						// 栏位多语
						$rpt_col_lang[$i]['muti_lang_pk'] = 'T_'.strtoupper($k);
						$rpt_col_lang[$i]['prompt_text'] =  !empty($val['attr_col_title']) ? $val['attr_col_title'] : $k ;
						$rpt_col_lang[$i]['uiculture_code'] =  'ZHS';
						$rpt_col_lang[$i]['create_by']  = $_SESSION['user']['user_name'];
						$rpt_col_lang[$i]['reverse1']  = $menucode;					
						
					}else{
						$stts_type = (empty($val['groupby_type']) ? 'count' : $val['groupby_type']);
						$col       = ($stts_type == 'count' ? 'count(1)' : $stts_type.'('.$k.') ');
						$rpt_cols[$i]['program_no']     = $menucode;
						$rpt_cols[$i]['table_name']     = 'T1';
						$rpt_cols[$i]['column_name']    = $stts_type.'_'.$k;
						$rpt_cols[$i]['data_type']      = empty($val['attr_data_type']) ? 'vachar2' : $val['attr_data_type'];
						$rpt_cols[$i]['allow_sorting']  = 0;
						$rpt_cols[$i]['width']          = '';
						$rpt_cols[$i]['height']         = '';
						$rpt_cols[$i]['align']          = $val['txt_align'];
						$rpt_cols[$i]['class_name']     = '';
						$rpt_cols[$i]['format_str']     = '';
						$rpt_cols[$i]['column_type']    = '';
						$rpt_cols[$i]['bgcolor']        = '';
						$rpt_cols[$i]['font_color']     = '';
						$rpt_cols[$i]['font_name']      = '';
						$rpt_cols[$i]['checked_value']  = '';
						$rpt_cols[$i]['data_source']    = '';
						$rpt_cols[$i]['muti_lang_pk']   = 'T_'.$stts_type.'_'.$k;
						$rpt_cols[$i]['column_seq']     = $i+100;
						$rpt_cols[$i]['display']        = 1;
						$rpt_cols[$i]['allow_querying'] = '';
						$rpt_cols[$i]['is_rang_condition']   = '';
						$rpt_cols[$i]['query_column_type']   = '';
						$rpt_cols[$i]['data_source_type']    = '';
						$rpt_cols[$i]['group_id']            = '';
						$rpt_cols[$i]['column_actual_value'] = $col;
						$rpt_cols[$i]['tgt_url']      = '';
						$rpt_cols[$i]['groupby_type'] = $stts_type;
						$rpt_cols[$i]['date_fmt']     = '';
						$rpt_cols[$i]['num_format']   = '';
						$rpt_cols[$i]['dec_num']      = ''; 
						$rpt_cols[$i]['unsign_fmt']   = '';
						$rpt_cols[$i]['uf_font_color']= '';
						$rpt_cols[$i]['uf_bg_color']  = '';
						// 栏位多语
						$rpt_col_lang[$i]['muti_lang_pk']   = 'T_'.$stts_type.'_'.$k;
						$rpt_col_lang[$i]['prompt_text']    =  (!empty($val['attr_col_title']) ? $val['attr_col_title'] : $k ).'('.getGroupbyFunc($stts_type).')';
						$rpt_col_lang[$i]['uiculture_code'] =  'ZHS';
						$rpt_col_lang[$i]['create_by']      = $_SESSION['user']['user_name'];
						$rpt_col_lang[$i]['reverse1']       = $menucode;						
					}
					$i++;
				}
				// 如果有 Group by 栏位,把其加到栏位明细里
			}
			
			if (is_array($rpt_col_grp[0]))
			{
				for($i=0; $i<count($rpt_col_grp[0]); $i++)
				{
					unset($rpt_col_grp[0][$i]['cols_list']);
				}
			}
			$r = $w->addRpt($rpt_master,
							$data_source,
							$rpt_cols,
							$rpt_col_grp[0],
							$rpt_col_grp[1],
							$rpt_col_lang);
			if($r == true)
			{
				header('Location: ?scriptname='.$menucode.'&rptview=1');
				exit;
			}else{
				showMsg($_POST['report_name'].'报表设定失败,原因:'.$r['error']);
			}
		}
	}
}

/* end file */