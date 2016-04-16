<?php
/*
 *  create by boll 20090410
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/umd_guide3_DB.php $
 *  $Id: umd_guide3_DB.php 2002 2009-07-24 07:27:31Z boll $
 *  $Rev: 2002 $ 
 *  $Date: 2009-07-24 15:27:31 +0800 (周五, 24 七月 2009) $
 *  $Author: boll $   
 *  $LastChangedDate: 2009-07-24 15:27:31 +0800 (周五, 24 七月 2009) $
 *********************************************************/
require_once 'AresUserDefineProgram.class.php';
$_POST['next_url']="?scriptname=umd_guide3&rand=".rand();
if(empty($_POST['do'])) $_POST['do']='ListGroup';
$udp = new AresUserDefineProgram();
$udp->run();
?>