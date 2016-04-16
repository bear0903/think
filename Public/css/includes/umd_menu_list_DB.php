<?php 
    if(empty($_GET['do']) && empty($_POST['do']))  $_GET['do']='MenuList';
    include_once 'umd_menu_create_DB.php';