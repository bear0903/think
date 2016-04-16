<?php
/**
 * 使用者自定义 workflow 定义
 *  create by dennis 20090922
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/wf_user_define_DB.php $
 *  $Id: wf_user_define_DB.php 2655 2010-02-24 05:32:53Z dennis $
 *  $Rev: 2655 $ 
 *  $Date: 2010-02-24 13:32:53 +0800 (周三, 24 二月 2010) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2010-02-24 13:32:53 +0800 (周三, 24 二月 2010) $
 */
if (! defined ('DOCROOT' ))	die ( 'Attack Error.');
// 返回保留原来输入的数据
header('Cache-control: private, must-revalidate');
include_once 'AresUserDefineWF.php';
$WFDefine = new AresUserDefineWF($_SESSION['user']['company_id'],
								 $_SESSION['user']['user_seq_no']);

$doaction = isset($_REQUEST['doaction']) && !empty($_REQUEST['doaction']) ? 
            $_REQUEST['doaction'] : 
            'list';
//pr($_POST);
if (in_array($doaction,array('insert','update','delete')))
{
	if ($_POST['menu_code'] == '0')
	{
		//showMsg('程式代码必须要输入','warning');
		showMsg($WFDefine->getErrorMsg(AresUserDefineWF::WF_MSG_APPID_REQUIRED),'warning');
	}
	
	foreach ($_POST['multi_lang'] as $v)
	{
		$v = trim($v);
		if (empty($v))
		{
			//showMsg('签核类型多语未输入完整.请重新输入.');
			showMsg($WFDefine->getErrorMsg(AresUserDefineWF::WF_MSG_MULTI_LANG_REQUIRED),'warning');
		}
	}
}
switch ($doaction)
{
	case 'add':
		$g_parser->ParseTable('lang_list',$WFDefine->getLangSupport());
		$g_parser->ParseSelect('wf_app_list',$WFDefine->getWFAppList($GLOBALS['config']['default_lang']),'','');
		break;
	case 'update':
		$r = $WFDefine->updateMasterDefine($_POST['menu_code'],
										   $_POST['workflow_type_seqno'],
										   $_POST['workflow_type_code'],
										   $_POST['multi_lang'],
										   $_POST['layout_cols'],
										   $_POST['tmp_save_allowed'],
										   $_POST['apply_rules_desc']);
		if ($r)
		{
			//showMsg('修改成功','information','?scriptname=wf_user_define');
			showMsg($WFDefine->getErrorMsg(AresUserDefineWF::WF_MSG_UPDATE_SUCCESS),'information','?scriptname=wf_user_define');
		}else{
			//showMsg('修改失败,原因<br/>'.$r,'error','?scriptname=wf_user_define');
			showMsg($WFDefine->getErrorMsg(AresUserDefineWF::WF_MSG_UPDATE_FAILURE).'<br/>'.$r,'error','?scriptname=wf_user_define');
		}
		break;
	case 'insert':
		$r = $WFDefine->insertMasterDefine($_POST['menu_code'],
										   $_POST['workflow_type_code'],
										   $_POST['multi_lang'],
										   $_POST['layout_cols'],
										   $_POST['tmp_save_allowed'],
										   $_POST['apply_rules_desc']);
		if (true === $r)
		{
			//showMsg('新增成功','information','?scriptname=wf_user_define');
			showMsg($WFDefine->getErrorMsg(AresUserDefineWF::WF_MSG_INSERT_SUCCESS),'information','?scriptname=wf_user_define');
		}else{
			//showMsg('新增失败,原因:<br/>'.$r,'error','?scriptname=wf_user_define');
			showMsg($WFDefine->getErrorMsg(AresUserDefineWF::WF_MSG_INSERT_FAILURE).'<br/>'.$r,'error','?scriptname=wf_user_define');
		}
	break;
	case 'delete':
		$r = $WFDefine->deleteDefine($_POST['menu_code'],$_POST['workflow_type_seqno'],$_POST['workflow_type_code']);
		if (true === $r)
		{
			//showMsg('删除成功','information','?scriptname=wf_user_define');
			showMsg($WFDefine->getErrorMsg(AresUserDefineWF::WF_MSG_DELETE_SUCCESS),'information','?scriptname=wf_user_define');
		}else{
			//showMsg('删除失败,原因<br/>'.$r,'error','?scriptname=wf_user_define');
			showMsg($WFDefine->getErrorMsg(AresUserDefineWF::WF_MSG_DELETE_FAILURE).'<br/>'.$r,'error','?scriptname=wf_user_define');
		}
		break;
	case 'edit':
		// 查支持的多语和现在
		$lang_list = $WFDefine->getLangSupport();
		$appr_type = $WFDefine->getApproveMultiLang($_GET['flow_type_code']);
		for($j= 0; $j<count($lang_list); $j++)
		{
			for ($i=0; $i<count($appr_type); $i++)
			{
				if ($lang_list[$j]['LANGUAGE_CODE'] == $appr_type[$i]['LANG_CODE'])
				{
					$lang_list[$j]['LANG_TEXT'] = $appr_type[$i]['VALUE'];
				}
			}
		}
		$g_parser->ParseTable('lang_list',$lang_list);
		$g_parser->ParseOneRow($WFDefine->getMasterDefine($_GET['menu_code']));
		break;
	case 'list':
		$g_parser->ParseTable('wf_define_list',$WFDefine->getMasterDefine());
		break;
	default:break;
}

