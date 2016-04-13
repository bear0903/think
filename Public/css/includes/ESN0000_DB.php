<?php
 /*************************************************************\
 *  Copyright (C) 2008 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     ESS Main Screen
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/ESN0000_DB.php $
 *  $Id: ESN0000_DB.php 3363 2012-10-16 06:53:10Z dennis $
 *  $Rev: 3363 $ 
 *  $Date: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 ****************************************************************************/
    if (! defined ('DOCROOT')) {
        die ('Attack Error.' );
    }// end if
    security_check($_SESSION['user']['user_seq_no'],$GLOBALS['config']['ess_home'].'/index.php?action=logout');
    require_once 'AresUser.class.php';
    require_once 'Layout/Tab.php';
    
    $User = new AresUser($_SESSION['user']['company_id'],
      					 $_SESSION['user']['user_name']);
      					 
    $g_menu = $User->GetMenu($_SESSION['user']['user_seq_no'],
                             'ess',
                             $GLOBALS['config']['default_lang']);
                             
    $_SESSION['sys_menu'] = $g_menu;
    
    function getDefaultMenuOffset($menu_id,$sys_menu)
    {
    	$app_map = $GLOBALS['config']['ess_app_map'];
    	$scriptname = $app_map[$menu_id];
    	foreach ($app_map as $k=>$v)
    	{
    		if ($k != $menu_id && $v == $scriptname)
    		{
    			for ($j=0; $j<count($sys_menu); $j++)
		    	{
		    		if ($sys_menu[$j]['NODEID'] == $k) return $j;
		    	}
    		}
    	}
    	return '';
    }
    
    $main_menu = $User->getMainMenu($g_menu,'ESN');
    //pr($main_menu);
    for ($i=0; $i<count($main_menu); $i++)
    {
    	// 注: 此 offset 是点到此 tab 时, 预设要显示的程式的名称的 offset
    	$menu_offset = getDefaultMenuOffset(strtoupper($main_menu[$i]['href']),$g_menu);
    	$main_menu[$i]['href'] = '?scriptname=default'.
    							 '&action=default&moduleid='.$main_menu[$i]['href'].
    							 '&moduledesc='.urlencode($main_menu[$i]['label']).
    							 '&menu_offset='.$menu_offset.'&rand='.rand(0,time());
    	//echo $main_menu[$i]['href'].'<br>';
    }
    $Menu = new Layout_Tab($main_menu,'tab-menu');
    $g_tpl->assign('main_menu',$Menu->render());
    // change cust company logo if exists
    $g_tpl->assign('company_logo',getLogoUrl());
    unset($User,$g_menu,$main_menu,$Menu);
    