<?php
/*************************************************************\
 *  Copyright (C) 2006 Ares China Inc.
 *  Created By Dennis Lan, Lan Jiangtao
 *  Description:
 *     请假申请(个人/助理批量申请)
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/emp_leave_apply_DB.php $
 *  $Id: emp_leave_apply_DB.php 3858 2014-11-06 01:56:06Z dennis $
 *  $LastChangedDate: 2014-11-06 09:56:06 +0800 (周四, 06 十一月 2014) $
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3858 $
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if

//pr($_SESSION);exit;
// back 保留表单资料
session_cache_limiter('private');

require_once 'AresAttend.class.php';
$Attend = new AresAttend ($_SESSION ['user'] ['company_id'],
						  $_SESSION ['user'] ['emp_seq_no']);
// add by dennis 2010-04-29 ajax call
if (isset($_GET['ajaxcall']) && $_GET['ajaxcall'] == 1 && !empty($_GET['func']))
{
    $func = $_GET['func'];
    $param = isset($_GET['shiftdate']) ? $_GET['shiftdate']:null;
    // add by Dennis for fixed 特别假亲属类型未按特别假条件抓取的 issue
    if ($func == 'GetFamilyType'){
        $param = isset($_GET['abs_type_id']) ? $_GET['abs_type_id'] : null;
    }
	echo json_encode($Attend->$func($param));
	exit;
}
// end ajax call
/**
 * Get Current Page URL
 * @param no
 * @author google 2010-11-10
 */
function curPageURL()
{
	$pageURL = 'http';
	if (@$_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

$result = '';
$g_tpl->assign ('leave_rule',html_entity_decode($g_db_sql->CacheGetOne("select text from ehr_md_content where code='leave_apply_rule'")));

// 列出代理项目 added by Gracie
$g_parser->ParseSelect ('agent_item',
						$Attend->getListMultiLang('ESNA012',
							  					  'AGENT_ITEM',
												  $GLOBALS['config']['default_lang']),'');
$g_parser->ParseSelect ('agent_item1',
						 $Attend->getListMultiLang('ESNA012',
												   'AGENT_ITEM',
						 						   $GLOBALS ['config'] ['default_lang']),'');
//added end

// 处理特殊假
$g_tpl->assign('spec_abs',$Attend->getSpecAbsence());

// 如果是批量请假就查询出所有的假别,否则就加当前使用者性别的条件
$_sex = $_GET['scriptname'] == 'ESNE002' ? null : $_SESSION ['user']['sex'];
$submit_type = isset($_POST['submit']) ? 'Y' : 'N';

if (isset ( $_POST ['absence_id'] ) && ! empty ( $_POST ['absence_id'] )) {
    
    $begin_date = $_POST['begin_date'].' '.$_POST['begin_time'];
    $end_date   = $_POST['end_date'].' '.$_POST['end_time'];
    
	function _checkAttachFile()
	{
		$r['file_allow'] = true;
		// remark by dennis 2011-10-21 for fixed assistant cannot submit issue
		if ($_FILES['doc_paper']['name']!='')
		{
			$r['file_allow'] = false;
			if ($_FILES ['doc_paper'] ['error'] != '4') {
				//pr($_FILES);
				//$support_type = array('jpg','jpeg','gif','png','bmp');
				$filename = basename ( $_FILES ['doc_paper'] ['name'] );
				$r['file_ext'] = $ext = substr ( $filename, strrpos ( $filename, '.' ) + 1 );
				$filetype = strtolower ( $_FILES ['doc_paper'] ['type'] );
				//echo '后缀名是 -> '.$ext.'<br/>file type=>'.$filetype.'<br/>';exit;
				// size 是以 byte 为单位
				$size = $_FILES ['doc_paper'] ['size'];
				switch($_FILES ['doc_paper'] ['error'])
				{
					case UPLOAD_ERR_OK: // value = 0
						switch (strtolower ($ext)) {
							case 'jpg' :
								$r['file_allow'] = ($filetype == 'image/jpeg' || $filetype == 'image/jpg' || $filetype == 'image/pjpeg');
								break;
							case 'jpeg' : // for fixed jpeg type issue (some .jpeg type image is .pjpeg)
								$r['file_allow'] = ($filetype == 'image/jpeg'|| $filetype == 'image/pjpeg');
								break;
							case 'gif' :
								$r['file_allow'] = ($filetype == 'image/gif');
								break;
							case 'png' :
								$r['file_allow'] = ($filetype == 'image/png' || $filetype == 'image/x-png');
								break;
							case 'bmp' :
								$r['file_allow'] = ($filetype == 'image/bmp');
								break;
							case 'pdf' :
								$r['file_allow'] = ($filetype == 'application/pdf');
								break;
							default :break;
						} // end switch()
						break;
					case UPLOAD_ERR_INI_SIZE :// value = 1
						$max_upload_size = ini_get('upload_max_filesize' );
						$r['file_allow'] = false;
						showMsg('附件太大, 目前附件最大支持只支持' . $max_upload_size . '<br/>','error' );
						break;
					case UPLOAD_ERR_PARTIAL:// value  = 3
						showMsg('附件只有部分上传，上传失败.','error');
						$r['file_allow'] = false;
						break;
					/*
					case UPLOAD_ERR_NO_FILE://value = 4
						showMsg('没有检测到有附件上传.','error');
						$r = false;
						break;
					case 5:
					break;*/
				} // end if
				if (false == $r['file_allow'])
				{
					showMsg( '不允许的文件类型:'.$ext.'<br> 只允许上传以下类型附件:<br/> .jpg .jpeg .gif .png .bmp','error');
				}// end if
			}// end if
		} // end if (end file type & size check)
		return $r;
	}
	
	/**
	 * 有附件的请假，打开查看的方式改回原来的 open window,
	 * 原因：jqueryui dialog load image 时要改的东西太多
	 * a type="popupwin"
	 * @param string $ext
	 * @return string
	 */
	function _getAttachLink($ext)
	{
		$new_filename =  $GLOBALS['config']['upl_dir']  . '/' .md5(microtime().$_SESSION['user']['emp_seq_no']). '.' . $ext;
		$view_file_link = dirname(curPageURL()).'/'.$new_filename.'?';
		$view_file_link = '<br/><a type="popupwin" href="' . $view_file_link . '">查看附件 </a>';
		$r['new_filename'] = $new_filename;
		$r['view_file_link'] = $view_file_link;
		return $r;
	}

	if (isset ( $_POST ['action'] )        &&
		$_POST ['action'] == 'batch_apply' &&
		isset ($_POST ['emp_seqno'] )      &&
		is_array ($_POST ['emp_seqno']))
	{
		// 批量请假
		
		$emp_cnt 	= count($_POST['emp_seqno']);
		$r = _checkAttachFile();
		if ($r['file_allow'] == true)
		{ 
			$view_file_link = '';
			$new_filename = '';
			// 只支持助理帮一个人申请请假时上传附件
			if (isset($r['file_ext']) &&  $r['file_ext']!= '' && $emp_cnt == 1)
			{
				$res = _getAttachLink($r['file_ext']);
				$new_filename = $res['new_filename'];
				$view_file_link = $res['view_file_link'];
			}
			// add by Dennis 2014/05/30
			$max_batch_count = 10;  // 直接处理的最大笔数
			$n = count($_POST['emp_seqno']);
			if($n>$max_batch_count){
			    include_once DOCROOT.'/libs/AresConcurrentRequest.class.php';
			    $concurrentRequest = new AresConcurrentRequest();
			    //pr($_POST);
			    $concurrentRequest->leaveBatchApply(
			            $_SESSION['user']['user_seq_no'],
			            $begin_date,
			            $end_date,
			            0,
			            $_POST['absence_id'],
			            $_POST['leave_reason'].$view_file_link,
			            $submit_type,
			            $_POST['emp_seqno'],
			            $_POST['dept_seqno'],
			            $_SESSION['user']['company_id']);
			    exit;
			}
			// end add by dennis 2014/05/30
           
			$result = $Attend->batchLeaveApply($_SESSION['user']['user_seq_no'],
											   $_POST['absence_id'],
											   $begin_date,
											   $end_date,
											   $_POST['leave_reason'].$view_file_link,
											   $submit_type,
											   $_POST ['emp_seqno']);
            //exit(pr($result));
			$success_count=0;
			$failure_count=0;
			for ($i=0; $i<count($result);$i++)
			{
				$result[$i]['dept_id']   = $_POST['dept_id'][$i];
				$result[$i]['dept_name'] = $_POST['dept_name'][$i];
				$result[$i]['emp_id']    = $_POST['emp_id'][$i];
				$result[$i]['emp_name']  = $_POST['emp_name'][$i];

				if($result[$i]['is_success']=='Y'){
					$success_count++;
				}else{
					$failure_count++;
				}
			}// end for loop
			// upload attactch file
			// 只支持助理帮一个人申请请假时上传附件
			if ($success_count==1 && $emp_cnt == 1 &&
				isset($r['file_ext']) && $r['file_ext']!='')
			{
				//echo $new_filename;
				//Check if the file with the same name is already exists on the server
				$rollback = false;
				$error_msg = '';
				if (!file_exists($new_filename)) {
					//Attempt to move the uploaded file to it's new place
					if (!move_uploaded_file($_FILES ['doc_paper'] ['tmp_name'],$new_filename)) {
						$error_msg =  '附件上传失败.<br/>请假申请未提交,请重新输入请假单.<br/>';
						$rollback = true;
					} // end if
				} else {
					$error_msg =  '附件上传失败,文件 ' . $new_filename . '已经存在.<br/>请假申请未提交,请重新输入请假单.<br/>';
					$rollback = true;
				} // end if
				// 如果附件上传失败,删除提交的申请单
				if ($rollback == true) {
					$Attend->DeleteWorkflowApply($result[0]['flow_seqno'], 'absence');
					showMsg($error_msg,'error');
				}
			}

			// 显示提交的结果
			$g_tpl->assign ('success_count', $success_count);
			$g_tpl->assign ('failure_count', $failure_count);

			$g_parser->ParseOneRow($_POST);
			$g_tpl->assign('apply_type','leave');
			$g_tpl->assign('leave_name',$Attend->getLeaveNameById($_POST['absence_id']));
			$g_tpl->assign('begin_date',$begin_date);
			$g_tpl->assign('end_date',$end_date);
			$g_tpl->assign('hours',$result[0]['hours']);
			$g_tpl->assign('days',$result[0]['days']);
			$g_parser->ParseTable('apply_result',$result);
			// rewrite 最后显示的画面的模板(显示申请结果的模版)
			$actual_file_name = 'apply_result';
		}
	} else {
		// check attach file type, only support bmp,png,gif,jpg
		//$ext = '';
		$r = _checkAttachFile();
		//only for current login user
		// 保存请假单

		// add by boll 经理代理人处理
		$agent_manager_str= ($_SESSION['user']['is_manager1']=='1')?'Manager ':'';
		$is_manager		  = ($_SESSION['user']['is_manager1']=='1')?'Y':'N';
		//echo $agent_manager_str;

		//特別假 funeral_id
		$funeral_id   = empty($_POST['funeral_id'])? null : $_POST['funeral_id'];
		$agent_id1    = isset($_POST['agent_id1']) ? $_POST['agent_id1'] : '';
		$agent_item   = isset($_POST['agent_item']) ? $_POST['agent_item'] : '';
		$agent_item1  = isset($_POST['agent_item1']) ? $_POST['agent_item1'] : '';

		$leave_reason_str=($_POST['assign_type']=='0')?
						   $_POST['leave_reason']     :
						   $_POST['leave_reason'].'<hr size="1" is_manager="'.
						   $is_manager.'" assign_type="'.$_POST['assign_type'].
						   '" agent_id="'.$_POST['agent_id'].'" agent_id1="'.
						   $agent_id1.'" agent_item="'.$agent_item.
						   '" agent_item1="'.$agent_item1.
						   '" emp_seq_no="'.$_SESSION['user']['emp_seq_no'].'" >'.
						   $agent_manager_str.'Agent:'.$_POST['agent'];
		//代理人不授权 echo $leave_reason_str;exit;
		// Modify by Dennis 2010-11-30
		// 当设定为 email 实时通知时,附件 link 看不到, 因为附件是后 update 上去的
		$view_file_link = '';
		$new_filename = '';
		if ($r['file_allow'] == true && @$r['file_ext'] != '')
		{
			$res = _getAttachLink($r['file_ext']);
			$new_filename = $res['new_filename'];
			$view_file_link = $res['view_file_link'];
		}
		$result = $Attend->SaveLeaveForm ($_SESSION['user']['user_seq_no'],
										  $_POST['absence_id'],
										  $begin_date,
										  $end_date,
										  $leave_reason_str.$view_file_link,
										  $submit_type,
										  $funeral_id);
										  //$_POST['leave_reason'].'<hr size="1" is_manager="'.$is_manager.'" assign_type="'.$_POST['assign_type'].'" agent_id="'.$_POST['agent_id'].'" emp_seq_no="'.$_SESSION['user']['emp_seq_no'].'" >'.$agent_manager_str.'Agent:'.$_POST['agent'],
		//pr($result);exit;//updated by Gracie at 20090615
		if ($result ['is_success'] == 'Y') {
			// 请假单提交成功后开始上载附件
			//Сheck that we have a file
			if (@$r['file_ext']!='') {
				//echo $new_filename;
				//Check if the file with the same name is already exists on the server
				$rollback = false;
				$error_msg = '';
				if (!file_exists($new_filename)) {
					//Attempt to move the uploaded file to it's new place
					if (!move_uploaded_file($_FILES ['doc_paper'] ['tmp_name'],$new_filename)) {
						$error_msg =  '附件上传失败.<br/>请假申请未提交,请重新输入请假单.<br/>';
						$rollback = true;
					} // end if
				} else {
					$error_msg =  '附件上传失败,文件 ' . $new_filename . '已经存在.<br/>请假申请未提交,请重新输入请假单.<br/>';
					$rollback = true;
				} // end if
				// 如果附件上传失败,删除提交的申请单
				if ($rollback == true) {
					$Attend->DeleteWorkflowApply($result['flow_seqno'], 'absence');
					showMsg($error_msg,'error');
				}
			} // end if
			// 提交请假申请单
			if (! empty ( $_POST ['submit'] )) {
				showMsg($result ['msg'].'<br>您可以到 <strong> <a href="?scriptname=emp_leave_apply_search&flowseqno='.$result ['flow_seqno'].'">请假申请查询 </a></strong> 查询所提交的申请单详情及流程.','success');
				//exit;
			}// end if
			// 保存请假申请单
			if (! empty ( $_POST ['save'] )) {
				showMsg($result ['msg'].'<br>您的请假申请只是<b>暂存没有提交送出签出核</b>,请假单无法被核准. 如要提交送出,请返回查询刚才暂存的请假单提交.<br/>' );
				exit();
			}// end if
		} else {
			// 申请单提交或暂存失败
			showMsg ($result ['msg'].'[申请单提交或暂存失败]','error' );
			exit;
		} // end if
	} // end if
}else{
	// 列出我/助理可以请的假别
	$g_parser->ParseSelect ('leave_name_list',$Attend->GetLeaveNameList($_sex),'s_leave_id', '' );
	//$g_tpl->assign('sick_leave_id',$Attend->getSickLeaveId());
}// end if