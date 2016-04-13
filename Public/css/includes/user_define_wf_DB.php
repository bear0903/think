<?php
if (!defined ('DOCROOT'))die( 'Attack Error.');
require_once 'AresUserDefineWF.php';
$udwf = new AresUserDefineWF($_SESSION['user']['company_id'],
							 $_SESSION['user']['user_seq_no']);
							 
$doaction  = isset($_REQUEST['doaction']) && !empty($_REQUEST['doaction']) ?
             $_REQUEST['doaction']:
             'showform';
//echo $doaction.'<hr/>';
switch ($doaction)
{
	case 'showform': 	// show apply form
		if (isset($_GET['scriptname'])  && 
		    !empty($_GET['scriptname']) &&
		    count($_POST) == 0)
		{
			$define_master = $udwf->getMasterDefine($_GET['scriptname']);
			//pr($define_master);
			if (isset($define_master['APPLY_RULES_DESC']))	
				$g_tpl->assign('apply_rule_desc',$define_master['APPLY_RULES_DESC']);
				
			$r = $udwf->checkDBSchema($_GET['scriptname']);
			if (false !=$r)
			{
				showMsg($r,'error');
			}
			$g_tpl->assign('template',$udwf->renderForm($_GET['scriptname'],
														$_GET['scriptname']));
		}// end if
		break;
	case 'submit': // sumbit apply data
	case 'tmpsave':// temp save data
		//pr($_POST);
		//pr($_FILES);
		$r  = $udwf->save($_GET['scriptname'],$_SESSION['user']['emp_seq_no'],$_POST,@$_FILES);
		if(is_array($r))
		{
			if ($r['is_success'] == 'Y')
			{
				//showMsg('提交成功,您的申请单已送出.','information');
				showMsg($udwf->getErrorMsg(AresUserDefineWF::WF_MSG_SUBMIT_SUCCESS),'information');
			}else{
				// 返回保留原来输入的数据
				//header('Cache-control: private, must-revalidate');
				//showMsg('提交失败,原因:<br/>'.$r['error_msg'],'error');
				showMsg($udwf->getErrorMsg(AresUserDefineWF::WF_MSG_SUBMIT_FAILURE).':'.$r['error_msg'],'error');
			}
		}else{
			if ($r == '1')
			{
				//showMsg('暂存成功,申请单只是暂存,未送出.','information');
				showMsg($udwf->getErrorMsg(AresUserDefineWF::WF_MSG_SAVE_SUCCESS),'information');
			}else{
				//header('Cache-control: private, must-revalidate');
				//showMsg('暂存失败,原因:<br/>'.$r,'error');
				showMsg($udwf->getErrorMsg(AresUserDefineWF::WF_MSG_SAVE_SUCCESS).':'.$r,'error');
			}
		}
		break;
	case 'applylist':	//  get apply history, 已核中的申请单 (状态 ='03' 表已经核准的)
		$where = '';
		if (isset($_GET['status']) && !empty($_GET['status'])){
			$op = $_GET['status']== '05' ? '>=' : '=';
			$where =  ' and a.status '.$op.'\''.$_GET['status'].'\'';
			$where = $_GET['status']== '02' ? 
			         ' and (a.status =\''.$_GET['status'].'\'or a.status =\'01\')' :
					 $where;
		}
		// 加查询条件
		if (isset($_POST['apply_b_date']) && !empty($_POST['apply_b_date']) && 
		    isset($_POST['apply_e_date']) && !empty($_POST['apply_e_date'])){
	    	$where .= ' and a.create_date >= to_date(\''.$_POST['apply_b_date'].'\',\'YYYY-MM-DD\')';
	    	$where .= ' and a.create_date <= to_date(\''.$_POST['apply_e_date'].'\',\'YYYY-MM-DD\')';
	    }
	    // get columns according the apply form
		$cols = $udwf->getDetailDefine($_GET['scriptname']);
		$c = count($cols);
		for($i=0; $i<$c; $i++)
		{
			$cols[$i][0] = strtoupper($cols[$i]['COL_NAME']);
			$cols[$i][1] = $cols[$i]['COL_LABEL'];
		}
		//pr($cols);
		$g_tpl->assign('cols_cnt',$c+5);
	    $g_parser->ParseTable('title_list',$cols);
	    
	    // 查询被当前 user 签核过的申请单
		if (isset($_GET['approved_his']) && !empty($_GET['approved_his'])){
		
			if (!empty($_POST['dept_id_s']) && !empty($_POST['dept_id_e']))
			{
				$where .= ' and a.dept_id >= \''.$_POST['dept_id_s'].'\'';
				$where .= ' and a.dept_id <= \''.$_POST['dept_id_e'].'\'';
			}
			if (!empty($_POST['emp_s']) && !empty($_POST['emp_e']))
			{
				$where .= ' and a.emp_id >= \''.$_POST['emp_s'].'\'';
				$where .= ' and a.emp_id <= \''.$_POST['emp_e'].'\'';
			}
			//echo $_GET['approved_his'];
			$totalrows = $udwf->getApprovedByMe($_GET['scriptname'],
												$where,$cols,
												$_SESSION['user']['emp_seq_no'],true);
	    }else{
	    	$totalrows = $udwf->getUDWFApply($_GET['scriptname'],$where,$cols,'myself',true);
	    }
	    
		//echo 'total rows-> '.$totalrows.'<br/>';
		if ($totalrows > 0) {
			require_once 'GridView/Data_Paging.class.php';
			$pagesize = 10;
			$pageIndex = isset ($_GET ['pageIndex'] ) ? $_GET ['pageIndex'] : 1;
			// 重置 pageIndex, 比如开始查的资料有5 页，点到第5页后又下了条件，结查查询出来的
			// 的资料只有 1 页了，因为 url 上 pageIndex 还是  5, 不会显示资料，所以这里重置
			$pageIndex = $pageIndex>ceil($totalrows/$pagesize) ? 1: $pageIndex;
			$Paging = new Data_Paging (array ('total_rows' => $totalrows, 'page_size' => $pagesize));
			$Paging->openAjaxMode('gotopage');
			$g_tpl->assign('pagingbar', $Paging->outputToolbar(2));
			
			// 查询由当前使用者签核的申请单
			if (isset($_GET['approved_his']) && !empty($_GET['approved_his']))
			{
				$apply_list = $udwf->getApprovedByMe($_GET['scriptname'],
													$where,$cols,
													$_SESSION['user']['emp_seq_no'],false,$pagesize,$pagesize*($pageIndex-1));
			}else{
				$apply_list = $udwf->getUDWFApply($_GET['scriptname'],$where,$cols,'myself',false,$pagesize,$pagesize*($pageIndex-1));
			}
			//pr($apply_list);
			$g_parser->ParseTable('apply_list',$apply_list);
		} // end 分页
		//$g_parser->ParseTable('apply_list',$udwf->getUDWFApply($_GET['scriptname'],$where));
		$g_tpl->assign('userseqno',$_SESSION['user']['user_seq_no']);
		$g_parser->ParseSelect('menu_code_list',$udwf->getWFAppList($GLOBALS['config']['default_lang'],true),'','');
		// flow status 多语清单
		require_once 'AresWorkflow.class.php';
		$Workflow = new AresWorkflow();
		$g_parser->ParseSelect('flow_status_list',$Workflow->getWFStatusList($GLOBALS['config']['default_lang']),'','');
		break;
		
	case 'dosubmit':// 暂存后提交
		$r = $r =  $udwf->submitWorkflow($_GET['scriptname'],$_GET['apply_flowseqno'],$_GET['apply_type']);
		if ('Y' == $r['is_success'])
		{
			//showMsg('提交成功','information');
			showMsg($udwf->getErrorMsg(AresUserDefineWF::WF_MSG_SUBMIT_SUCCESS),'information');
		}else{
			//showMsg('提交失败<br/>'.$r['error_msg'],'error');
			showMsg($udwf->getErrorMsg(AresUserDefineWF::WF_MSG_SUBMIT_FAILURE).$r['error_msg'],'error');
		}
		break;
		
	case 'delete':  // 删除暂存提交的资料
		if (isset($_GET['apply_flowseqno']) && !empty($_GET['apply_flowseqno']))
		{
			$r =  $udwf->deleteWorkflowApply($_GET['scriptname'],$_GET['apply_flowseqno']);
			if (1 == $r)
			{
				//showMsg('删除成功','information');
				showMsg($udwf->getErrorMsg(AresUserDefineWF::WF_MSG_DEL_SUCCESS),'information');
			}else{
				//showMsg('删除失败<br/>'.$r,'error');
				showMsg($udwf->getErrorMsg(AresUserDefineWF::WF_MSG_DEL_FAILURE).'<br/>'.$r,'error');
			}
		}
		break;
	default:break;             
}// end switch
