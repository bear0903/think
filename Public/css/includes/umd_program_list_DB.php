<?php
/*
 *  自定程式设置   mappng  ares211
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/umd_program_list_DB.php $
 *  $Id: umd_program_list_DB.php 2002 2009-07-24 07:27:31Z boll $
 *  $Rev: 2002 $ 
 *  $Date: 2009-07-24 15:27:31 +0800 (周五, 24 七月 2009) $
 *  $Author: boll $   
 *  $LastChangedDate: 2009-07-24 15:27:31 +0800 (周五, 24 七月 2009) $
 *********************************************************/
require_once 'AresUserDefineProgram.class.php';
if(empty($_GET['do']))  $_GET['do']='New';
/*  controller */
$udp = new AresUserDefineProgram();
$udp->run();

?>