<?php
/*
 * 权限定义
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/umd_menu_filter_DB.php $
 *  $Id: umd_menu_filter_DB.php 1384 2009-04-07 07:38:36Z dennis $
 *  $Rev: 1384 $ 
 *  $Date: 2009-04-07 15:38:36 +0800 (周二, 07 四月 2009) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2009-04-07 15:38:36 +0800 (周二, 07 四月 2009) $
 *********************************************************/

class Umd extends AresAction 
{
    public function actionPermission(){
    	pr($_GET);
    }
}
if(empty($_GET['do']))  $_GET['do']='New';
/*  controller */
$umd = new Umd();
$umd->run();

?>