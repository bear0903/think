<?php
/**
 * 考核单
 *  $CreateBy: Dennis $
 *  $CreateDate: 2008-11-18$
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/pa_form_DB.php $
 *  $Id: pa_form_DB.php 3828 2014-08-20 07:11:27Z dennis $
 *  $LastChangedDate: 2014-08-20 15:11:27 +0800 (周三, 20 八月 2014) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 3828 $  
 ****************************************************************************/
if (! defined ( 'DOCROOT' )) {
    die ( 'Attack Error.' );
}// endif
require_once 'AresPA.class.php';
$myself = $_SESSION['user']['emp_seq_no'];
$PA = new AresPA($_SESSION['user']['company_id'],$myself);
			 
// Ajax Call Delete File
if(isset($_POST['doaction'])         &&
   $_POST['doaction']== 'deletefile' &&
   isset($_POST['formseqno'])){
	// 删除文件
	if (file_exists($_POST['filename']))
	{
		unlink($_POST['filename']);
	}// end if
	// 更新 DB
	$r = $PA->deleteFileName($_POST['formseqno']);
	echo $r;
	exit;
}// end if

// 输入分数即时得到等第　add by dennis 20090507
if(isset($_GET['ajaxcall'])          && 
   $_GET['ajaxcall']== 1             && 
   isset($_GET['std_master_seqno'])  &&
   !empty($_GET['std_master_seqno']) &&
   isset($_GET['mgr3_score'])        &&
   !empty($_GET['mgr3_score']))
{
	echo json_encode($PA->getPALevelIDByScore($_GET['std_master_seqno'],$_GET['mgr3_score']));
	exit;
}// end if

/*目标考核部分*/
$goal_seqno = '605';
if (isset($goal_seqno) && $goal_seqno != '') {
    $pa_form_seqno = $goal_seqno;
    $g_parser->ParseOneRow($PA->getEmpInfo($pa_form_seqno));
    
    $goal_master = $PA->getGoalMasterList($pa_form_seqno);
    $goal_detail = $PA->getGoalDetailList($pa_form_seqno);
    $g_parser->ParseSelect('goal_type_list', $PA->getGoalTypeList(), '');
    $mcnt = count($goal_master);
    $dcnt = count($goal_detail);
    
    // recombine the array, set the detail data as master array sub-array
    for ($i = 0; $i < $mcnt; $i ++) {
        $k = 0;
        for ($j = 0; $j < $dcnt; $j ++) {
            if ($goal_detail[$j]['MASTER_GOAL_SEQNO'] ==
                     $goal_master[$i]['MASTER_GOAL_SEQNO']) {
                $goal_master[$i]['detail'][$k] = $goal_detail[$j];
                $k ++;
            }
        }
        //$goal_master[$i]['ROWSPAN'] = $k;
        $goal_master[$i]['ROWSPAN'] = $k>1 ? 'rowspan="'.$k.'"' : '';
    }
    // $g_tpl->assign('mgr_comment',(count($goal_master)>0?
    // $goal_master[0]['MGR_COMMENT']:''));
    $g_parser->ParseTable('pa_goal_list', $goal_master);
}
/*目标考核部分*/

//更新考核单内容 (master/detail)
$filename = null;
$new_filename = null;
if (isset($_POST['pa_form_seqno'])  && 
 	!empty($_POST['pa_form_seqno']) &&
    isset($_POST['doaction'])       && 
	!empty($_POST['doaction'])      &&
	isset($_POST['whoami'])         && 
	!empty($_POST['whoami'])) {
	//pr($_POST);
	//pr($_FILES);
	$form_status = $PA->getFormStatus($_POST['whoami'],$_POST['doaction']);
	//echo 'form status ->'.$form_status.'<hr>';
	// 上传附件
	if(isset($_FILES['pa_attach']) &&
	   $_FILES['pa_attach']['size']  >0 && 
	   $_FILES['pa_attach']['error'] != UPLOAD_ERR_NO_FILE){
		$filename = basename ($_FILES ['pa_attach'] ['name'] );
		$ext = substr ($filename, strrpos ( $filename, '.' ) + 1 );			
		$filetype = strtolower ( $_FILES ['pa_attach'] ['type'] );
		//echo '后缀名是 -> '.$ext.'<br/>file type=>'.$filetype.'<br/>';
		// size 是以 byte 为单位
		$size = $_FILES ['pa_attach'] ['size'];
		switch($_FILES ['pa_attach'] ['error'])
		{
			case UPLOAD_ERR_OK: // value = 0
				switch (strtolower ($ext)) {
					case 'xls' :
						$r = ($filetype == 'application/vnd.ms-excel');
						break;
					case 'doc' :
						$r = ($filetype == 'application/msword') ||($filetype == 'application/vnd.ms-word') ;
						break;
					case 'ppt' :
						$r = ($filetype == 'application/vnd.ms-powerpoint');
						break;
					case 'xlsx' :
						$r = ($filetype == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
						break;
					case 'docx' :
						$r = ($filetype == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
						break;
					case 'pptx' :
						$r = ($filetype == 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
						break;
					default :break;
				} // end switch()
				break;
			case UPLOAD_ERR_INI_SIZE :// value = 1
				$max_upload_size = (int)str_replace('M','',ini_get ( 'upload_max_filesize' ))*1024*1024;
				$r = false;
				showMsg('附件太大, 目前附件最大支持只支持' . $max_upload_size/1024/1024 . 'MB <br/>','error' );
				break;
			case UPLOAD_ERR_PARTIAL:// value  = 3
				showMsg('附件只有部分上传，上传失败.','error');
				$r = false;
				break;
			/*			
			case UPLOAD_ERR_NO_FILE://value = 4
				showMsg('没有检测到有附件上传.','error');
				$r = false;
				break;
			case 5:
			break;*/
		} // end switch
		if ($r) {
			$rand_file_name = mt_rand(0,time());
			$new_filename = $GLOBALS['config']['upload_dir'].'/userfile/'.$_POST['pa_form_seqno'].'-'.$rand_file_name.'.'.$ext;
			//echo $new_filename;				
			//Check if the file with the same name is already exists on the server
			$rollback = false;
			// 如果文件存在,先删除
			if (file_exists ($new_filename)) {
				unlink($new_filename);
			} else {
				//Attempt to move the uploaded file to it's new place				
				if (! move_uploaded_file ( $_FILES ['pa_attach'] ['tmp_name'], $new_filename )) {
					showMsg( '附件上傳失敗.<br/>考核單未送出,請重新輸入','error' );
					$rollback = true;
				} // end if
			} // end if
		}else{ 
			showMsg( '不允許的文件類型:'.$filetype.'<br/> 支持以下類型的文件:<br/> .doc .docx .xls .xlsx .ppt .pptx','error');
		}// end if
	}// end if
	$form_status_changed = false;	
	// 前半年目標及任務達成 ,附件上传成功才执行后面的 update
	if (isset($_POST['emp_goal']) || !is_null($new_filename))
	{
		$r = $PA->updateEmpGoal($_POST['pa_form_seqno'],
								$_POST['emp_goal'],
								$_POST['emp_achieve_goal'],
								$filename,
								$new_filename,
								$form_status);
		if (1 != $r )
		{
			showMsg($r,'error');								
		}// end if 
		$form_status_changed = true;
	}// end if
	
	// 更新考核单工作伦理态度项目
	if (isset($_POST[$_POST['whoami'].'_score']))
	{
		
		$r = $PA->updatePAItem($_POST['pa_form_seqno'],
							   $_POST['whoami'],
							   $_POST[$_POST['whoami'].'_score'],
							   $_POST[$_POST['whoami'].'_comment']);
		if (1 != $r )
		{
			showMsg($r,'error');
		}// end if
	}// end if
	
	// 更新考核单自我改善与发展项目
	if (isset($_POST['improve_item']))
	{
		$r = $PA->updateQAItem($_POST['pa_form_seqno'],
							   $_POST['whoami'],
							   $_POST['improve_item'],
							   'improve');
		if (1 != $r )
		{
			showMsg($r,'error');								
		}// end if
	}// end if
	
	if(isset($_POST['interview_item']))
	{
		// 更考核单员工面谈确认项目
		$r = $PA->updateQAItem($_POST['pa_form_seqno'],
							   $_POST['whoami'],
							   $_POST['interview_item'],
							   'interview');
		if (1 != $r )
		{
			showMsg($r,'error');								
		}// end if
		if (strtolower($_POST['whoami']) == 'mgr1' &&
		    isset($_POST['doaction'])              &&
		    'submitform_interview_comment' == $_POST['doaction'])
		{
			/**
			*  处理一二,二三,或是一二三 阶主管是一样的情况
			*/
			if ($myself == $_POST['mgr2_emp_seqno'])
			{	// 當前"我"是第一二階主管(一二階主管一樣的情形)
				// 必須是面談提交時才會 update 第二階的成績
				$r = $PA->autoUpdatePAItemScore($_POST['pa_form_seqno']);
				if ($r > 0)
				{
					// add by dennis 2009-05-07
					$PA->updatePAFormStatus($_POST['pa_form_seqno'],
									    	$PA->getFormStatus('mgr1',$_POST['doaction']));
					$PA->updatePAFormStatus($_POST['pa_form_seqno'],
									    	$PA->getFormStatus('mgr2',$_POST['doaction']));
					$form_status_changed = true;
				}
			}
			if ($myself == $_POST['mgr3_emp_seqno'])
			{	// 當前"我"是第一二三階主管(一二三階主管一樣的情形)
				$r = $PA->autoUpdateLastScore($_POST['pa_form_seqno']);
				
				if ($r == 1)
				{
					// add by dennis 2009-05-07
					
					$PA->updatePAFormStatus($_POST['pa_form_seqno'],
									    	$PA->getFormStatus('mgr1',$_POST['doaction']));
									    	
					$PA->updatePAFormStatus($_POST['pa_form_seqno'],
									    	$PA->getFormStatus('mgr2',$_POST['doaction']));
									    	
					$PA->updatePAFormStatus($_POST['pa_form_seqno'],
									    	$PA->getFormStatus('mgr3',$_POST['doaction']));
					$form_status_changed = true;
				}								  
			}
		}// end if
	}// end if
	
	if (isset($_POST['doaction']) &&
        'submitform' == $_POST['doaction'] && 
	    strtolower($_POST['whoami']) == 'mgr2')
	{
		// 當"我"是二三階主管時
		if ($myself == $_POST['mgr3_emp_seqno'])
		{
			// 當前"我"是第一二階主管(一二階主管一樣的情形)
			// 加總細項分數,update 到核等的分數中去
			// 提交時才做 update 核等的成績, 暫存時不管
			$r = $PA->autoUpdateLastScore($_POST['pa_form_seqno'],strtolower($_POST['whoami']));			
			if ($r == 1)
			{
				// add by dennis 2009-05-07
				$PA->updatePAFormStatus($_POST['pa_form_seqno'],
								    	$PA->getFormStatus('mgr2',$_POST['doaction']));
				$PA->updatePAFormStatus($_POST['pa_form_seqno'],
								    	$PA->getFormStatus('mgr3',$_POST['doaction']));
				$form_status_changed = true;											    					   
			}
		}// end if
	}// end if
	
	// 更考核单核定等第
	if(isset($_POST['approve_score']))
	{
		$r = $PA->updateLastScore($_POST['pa_form_seqno'],
								  $_POST['approve_score'],
								  $_POST['approve_rank_comment'],
								  $form_status);
		if (1 != $r )
		{
			showMsg($r,'error');
		}// end if
		// add by dennis 2009-05-07
		$PA->updatePAFormStatus($_POST['pa_form_seqno'],
						    	$PA->getFormStatus('mgr3',$_POST['doaction']));
		$form_status_changed = true;
	}// end if
	
	// 更新考核单状态, 前面已经更改过的就不必须更新了
	if(!$form_status_changed){
		$r = $PA->updatePAFormStatus($_POST['pa_form_seqno'],$form_status);
		if (1 != $r )
		{
			showMsg($r,'error');								
		}// end if
	}// end if
	
	// 更新主管确认项目
	//$PA->updateMgrConfirm();
	 $msg = in_array($form_status,array(1,3,6,8)) ? '考核單暫存成功.' : '考核單送出成功.';
	 if (!is_null($new_filename)) $msg .= '<br/>文件:'. $filename .'上傳成功.';
	 showMsg($msg ,'success',isset($_SESSION['pa_emp_list_url']) ? urldecode($_SESSION['pa_emp_list_url']) : '?'.$_SERVER['QUERY_STRING']);
}// end if
// &doaction=viewonly&pa_form_seqno=641&emp_seqno=95430
if(isset($_GET['pa_form_seqno'])    && 
   !empty($_GET['pa_form_seqno'])   &&
   isset($_GET['pa_period_seqno'])  && 
   !empty($_GET['pa_period_seqno']) &&
   isset($_GET['whoami'])           && 
   !empty($_GET['whoami']) )
{
	$g_tpl->assign('whoami',$_GET['whoami']);
	$g_tpl->assign('confirm_submit_msg',getMultiLangMsg('ESNB007',
														$GLOBALS['config']['default_lang'],
														'CONFIRM_SUBMIT_MSG'));
	// 被考核员工基本信息
	$pa_emp_info = $PA->getPAEmpInfo($_GET['pa_form_seqno']);
	
	// 取得上一期目标设定
	// add by dennis 2010-01-13
	$emp_seqno = isset($_GET['emp_seqno']) ? $_GET['emp_seqno'] : null;
	$pa_emp_info['PRE_EMP_GOAL'] = $PA->getPrePAGoal($_GET['pa_form_seqno'],$emp_seqno);
	$pa_emp_info['SEG_SEGMENT_NO'] = $_SESSION['user']['company_id'];//Added this row by hunk at 20160115 for bothhand tw cust
	//pr($pa_emp_info);
	$g_parser->ParseOneRow($pa_emp_info);	
	
	// 考核項目
	$pa_items = $PA->getPAItem($_GET['pa_form_seqno']);
	$cnt = count($pa_items);
	if ($cnt>0)
	{
		$pa_item_scroe_range = $PA->getPAItemScoreStd($_GET['pa_form_seqno']);
		//pr($pa_item_scroe_range);
		for ($i=0; $i<$cnt; $i++)
		{
			for($j=0; $j<count($pa_item_scroe_range); $j++)
			{
				if ($pa_items[$i]['PA_ITEM_RANGE_SEQNO'] == $pa_item_scroe_range[$j]['PA_ITEM_RANGE_SEQNO'])
				{
					$pa_items[$i]['MIN_SCORE'] = $pa_item_scroe_range[$j]['MIN_SCORE'];
					$pa_items[$i]['MAX_SCORE'] = $pa_item_scroe_range[$j]['MAX_SCORE'];
					continue;
				}// end if
			}// end loop
		}// end loop
	}// end if
	$g_parser->ParseTable('pa_item_list',$pa_items);
	//pr($pa_items);
	
	// 评等标准
	$pa_std = '';
	if(is_array($pa_items) && count($pa_items)>0)
	{
		// 计算总分和等级
		$emp_total_score = 0;
		$mgr1_total_score = 0;
		$mgr2_total_score = 0;
		for ($k=0; $k<count($pa_items);$k++)
		{
			$emp_total_score  += $pa_items[$k]['EMP_SCORE'] * $pa_items[$k]['PA_ITEM_WEIGHT']/100;
			$mgr1_total_score += $pa_items[$k]['MGR1_SCORE']* $pa_items[$k]['PA_ITEM_WEIGHT']/100;
			$mgr2_total_score += $pa_items[$k]['MGR2_SCORE']* $pa_items[$k]['PA_ITEM_WEIGHT']/100;
		}//end loop
		$g_tpl->assign('emp_total_core',number_format($emp_total_score,2));
		$g_tpl->assign('mgr1_total_core',number_format($mgr1_total_score,2));
		$g_tpl->assign('mgr2_total_core',number_format($mgr2_total_score,2));
		
		$pa_std = $PA->getPARankStd($pa_items[0]['PA_STD_MASTER_SEQNO']);
		$g_tpl->assign('emp_rank', $PA->getRank($emp_total_score,$pa_std));
		$g_tpl->assign('mgr1_rank',$PA->getRank($mgr1_total_score,$pa_std));
		$g_tpl->assign('mgr2_rank',$PA->getRank($mgr2_total_score,$pa_std));
		$pa_std_comments = '';
		//pr($pa_std);
		for ($i=0; $i<count($pa_std);$i++)
		{
			$pa_std_comments .= '&nbsp;&nbsp;<u>'.$pa_std[$i]['SCORE1'].'~'.
								$pa_std[$i]['SCORE2'].' : '.
								$pa_std[$i]['EVALUATION_LEVEL_NO'].'</u>';
								
		}// end for loop
		// 组成一串 string
		$g_tpl->assign('pa_std_comments',$pa_std_comments);
		$g_tpl->assign('pa_std_json',json_encode($pa_std));
	}// end if
	switch($_GET['whoami'])
	{
		// 员工本人
		case 'emp':
			// 判断是否有接班人要维护
			if ($PA->getSuccessorSetting($_GET['pa_period_seqno']))
			{
				if($PA->getSuccessorPlanList($_GET['pa_period_seqno'])<1)
				{
					// show error msg
					showMsg(getMultiLangMsg('ESNB010',
											$GLOBALS['config']['default_lang'],
											'NEED_MAINTAIN_SUCCESSOR'),
							'warning',
							'?scriptname=pa_add_successor&pa_period_seqno='.
							$_GET['pa_period_seqno'].'&pa_period_desc='.
							urlencode($_GET['pa_period_desc']).
							'&doaction=insert');
				}// end if
			}// end if
			
			// get 上传附件最大 size
			$g_tpl->assign('upload_max_filesize',$PA->returnBytes(ini_get('upload_max_filesize')));
			break;
		// 初评主管(直属主管)
		case 'mgr1':
			// mgr1 能签核的条件 1. 员工已提交(2,5)
			$g_tpl->assign('mgr1_can_approve',$PA->isCanPA('mgr1',
														    $pa_emp_info['MGR1_BEGIN_DATE'],
														    $pa_emp_info['MGR1_END_DATE'],
														    $pa_emp_info['FORM_STATUS']));
			break;
		// 复评主管
		case 'mgr2':
			$g_tpl->assign('mgr2_can_approve',$PA->isCanPA('mgr2',
														   $pa_emp_info['MGR2_BEGIN_DATE'],
														   $pa_emp_info['MGR2_END_DATE'],
														   $pa_emp_info['FORM_STATUS']));
			$g_parser->ParseTable('interview_item_list',$PA->getInterviewConfirmItem($_GET['pa_form_seqno']));
			break;
		// 核等主管
		case 'mgr3':
			$g_tpl->assign('mgr3_can_approve',$PA->isCanPA('mgr3',
															$pa_emp_info['MGR3_BEGIN_DATE'],
															$pa_emp_info['MGR3_END_DATE'],
															$pa_emp_info['FORM_STATUS']));
			$g_parser->ParseSelect('level_list',$pa_std,'');
			break;
		default:break;
	}// end switch
	
	// 自我改善發展項目
	$pa_improve_items = $PA->getQAItem($_GET['pa_form_seqno'],'improve_item');
	//pr($pa_improve_items);
	// 自我改善發展項目由谁填写,从多语挑名称
	$owner_desc = getMultiLangList($GLOBALS['config']['default_lang'],'HR_EVA_SELFDEVELOPE_TW.REPLY_SUBJECT');
	// 单选型题目的选项  section nesting 
	for($i=0; $i<count($pa_improve_items);$i++)
	{
		// modify by dennis 20091216
		//$pa_improve_items[$i]['ITEM_OWNER_DESC'] = $owner_desc[$pa_improve_items[$i]['ITEM_OWNER']-1]['LIST_LABEL'];
		$pa_improve_items[$i]['ITEM_OWNER_DESC'] = $owner_desc[$pa_improve_items[$i]['ITEM_OWNER']-1][1];
		if ($pa_improve_items[$i]['ANSWER_TYPE'] == '1')
		{
			$pa_improve_items[$i]['KEYLIST'] = $PA->getAnswerItemList($pa_improve_items[$i]['ANSWER_LIST_KEY']);
		}else{
			$pa_improve_items[$i]['KEYLIST'] = '';
		}// end if
	}// end for loop
	//pr($pa_improve_items);
	$g_tpl->assign('pa_improve_item_list',$pa_improve_items);	
	// 绩效面谈项目
	$pa_interview_items = $PA->getQAItem($_GET['pa_form_seqno'],'interview_item');
	
	for($i=0; $i<count($pa_interview_items);$i++)
	{
		//$pa_interview_items[$i]['ITEM_OWNER_DESC'] = $owner_desc[$pa_interview_items[$i]['ITEM_OWNER']-1]['LIST_LABEL'];
		// modify by dennis 20091216
		$pa_interview_items[$i]['ITEM_OWNER_DESC'] = $owner_desc[$pa_interview_items[$i]['ITEM_OWNER']-1][1];
		if ($pa_interview_items[$i]['ANSWER_TYPE'] == '1')
		{
			$pa_interview_items[$i]['KEYLIST'] = $PA->getAnswerItemList($pa_interview_items[$i]['ANSWER_LIST_KEY']);
		}else{
			$pa_interview_items[$i]['KEYLIST'] = '';
		}// end if
	}// end for loop
	//pr($pa_interview_items);
	$g_tpl->assign('pa_interview_item_list',$pa_interview_items);
	//PR($_GET);PR($_POST);PR($pa_emp_info);
	// 奖惩历史
	$g_parser->ParseTable('rp_list',$PA->getRewardsPunishment($pa_emp_info['PA_EMP_SEQNO'],
															  $pa_emp_info['STTS_BEGIN_DATE'],
															  $pa_emp_info['STTS_END_DATE']));
	// 考勤纪录
	$g_parser->ParseTable('abs_list',$PA->getAbsSummary($pa_emp_info['PA_EMP_SEQNO'],
														$pa_emp_info['STTS_BEGIN_DATE'],
														$pa_emp_info['STTS_END_DATE']));
	$cust_abs_list = $PA->getAbsSummary_new($pa_emp_info['PA_EMP_SEQNO'], $_GET['pa_period_seqno']);//Added by hunk at 20151210 for bothhand cust
	$g_parser->ParseOneRow($cust_abs_list);//Added by hunk at 20151210 for bothhand cust
	// 最近三次绩考成绩
	$g_parser->ParseTable('pa_his_list',$PA->getPAHis($pa_emp_info['PA_EMP_SEQNO']));
	
}else{
	showMsg('沒有考核單. <br/> 請聯繫績效考核相關人員.','warning');
}// end if
