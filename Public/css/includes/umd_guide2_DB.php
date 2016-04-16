<?php
/*
 *  create by boll 20090410
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/umd_guide2_DB.php $
 *  $Id: umd_guide2_DB.php 3130 2011-07-01 07:04:46Z dennis $
 *  $Rev: 3130 $ 
 *  $Date: 2011-07-01 15:04:46 +0800 (周五, 01 七月 2011) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2011-07-01 15:04:46 +0800 (周五, 01 七月 2011) $
 *********************************************************/
require_once 'AresUserDefineProgram.class.php';
if(!empty($_POST['PROGRAM_NO'])) $_GET['PROGRAM_NO']=$_POST['PROGRAM_NO'];
if(!empty($_GET['PROGRAM_NO'])) $_POST['PROGRAM_NO']=$_GET['PROGRAM_NO'];
$_POST['next_url']="?scriptname=umd_guide3&rand=".rand();
if(empty($_POST['do'])) $_POST['do']='EditBasic';
$udp = new AresUserDefineProgram();
$udp->run();

?>