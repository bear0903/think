<?php
if (! defined ( 'DOCROOT' )) die ( 'Attack Error.' );

include 'AresConcurrentRequest.class.php';
//pr($_GET);
//pr($_POST);
$AresConc = new AresConcurrentRequest();
/*
if ((isset($_POST['doaction']) && $_POST['doaction'] == 'query')){
     
    if (isset($_POST['_op_requeset_no']) &&
            !empty($_POST['_op_requeset_no']) &&
            isset($_POST['request_no']) &&
            !empty($_POST['request_no']) ){
        $where .= 'and  request_no '.$_POST['_op_requeset_no']."'".$_POST['request_no']."'";
    }

    if (isset($_POST['_op_req_status']) &&
            !empty($_POST['_op_req_status']) &&
            isset($_POST['req_status']) &&
            !empty($_POST['req_status']) ){
        $where .= ' and  status '.$_POST['_op_req_status']."'".$_POST['req_status']."'";
    }
}

if (isset($_GET['requestno']) && !empty($_GET['requestno'])){
    $where .= ' and request_no ='.$_GET['requestno'];
}*/

$batch_run_detail = $AresConc->getOTBatchRunDetailbyBatchSeq($_GET['requestno'],'overtime_apply');
//pr($batch_run_detail);
$g_parser->ParseTable('batch_run_detail', $batch_run_detail);
