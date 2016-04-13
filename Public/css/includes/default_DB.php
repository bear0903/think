<?php
/*************************************************************\
 *  Copyright (C) 2008 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     所有的模组的首页
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/default_DB.php $
 *  $Id: default_DB.php 3261 2011-11-16 07:11:31Z dennis $
 *  $Rev: 3261 $ 
 *  $Date: 2011-11-16 15:11:31 +0800 (周三, 16 十一月 2011) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2011-11-16 15:11:31 +0800 (周三, 16 十一月 2011) $
 ****************************************************************************/

if (!defined('DOCROOT')) die ( 'Attack Error.' );
$moduleid = isset($_GET['moduleid']) && !empty($_GET['moduleid']) ? $_GET['moduleid'] : '';
if ($moduleid != '')
{
	/**
	 *  Get Sub Menu
	 * @param array  $menu
	 * @param string $parent_menu_id
	 * @param string $url
	 * @param string $icon
	 * @param string $target
	 * @return array
	 */
	function getSubMenu(array $menu,$parent_menu_id,$url,$icon,$target='mainframe')
    {
    	//global $g_tpl;
    	$n = count($menu);
    	//pr($menu);
    	$submenu = array();
    	$j = 0;
    	for ($i=0; $i<$n; $i++)
    	{
    		if($menu[$i]['P_NODEID'] == $parent_menu_id && 
    		   strtoupper($menu[$i]['NODETYPE']) == 'FORM')
    		{
    			$submenu[$j]['id'] = $menu[$i]['NODEID'];
    			$submenu[$j]['text'] = $menu[$i]['NODETEXT'];
    			if(array_key_exists($menu[$i]['NODEID'],$GLOBALS['config']['pub_app'])){
    				$submenu[$j]['href'] = $GLOBALS['config']['mgr_home'].'/redirect.php?scriptname='.
    									   $GLOBALS['config']['pub_app'][$menu[$i]['NODEID']].
    									   '&menu_offset='.$i.'&rand='.rand();
    				//echo $GLOBALS['config']['pub_app'][$menu[$i]['NODEID']].'<hr/>';
    				if(array_key_exists($GLOBALS['config']['pub_app'][$menu[$i]['NODEID']],
    									$GLOBALS['config']['md_app_map'])){
    					$submenu[$j]['href'] = $GLOBALS['config']['mgr_home'].'/redirect.php?scriptname='.
    										   $GLOBALS['config']['pub_app'][$menu[$i]['NODEID']].
    										   '&menu_offset='.$i.'&query_self=Y'.'&rand='.rand();
    				}
    			}else {
    				$submenu[$j]['href'] = $url.$menu[$i]['NODEID'].'&menu_offset='.$i.'&rand='.rand();
    			}
    			
    			$submenu[$j]['icon'] = $icon;
    			$submenu[$j]['target'] = $target;    			
    			$j++;
    		}// end if
    	}// end loop
    	return $submenu;
    }// end getSubMenu()
   
    //pr($_SESSION['sys_menu']);
	require_once 'Layout/Menu.php';
	$menuitems = getSubMenu($_SESSION['sys_menu'],$moduleid,'?scriptname=','Person.png',$moduleid);
	//pr($menuitems);exit;
	$menu = new Layout_Menu($menuitems,$moduleid);
	//echo $menu->render();exit;
	$g_tpl->assign ('submenu',$menu->render());
	$g_tpl->assign ('rand',rand());
}//end if
