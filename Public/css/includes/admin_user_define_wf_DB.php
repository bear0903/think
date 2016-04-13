<?php
/**
 * User defined workflow admin
 * create by dennis 20091123
 */
if (! defined ( 'DOCROOT' )) die ( 'Attack Error.' );

require_once 'AresUserDefineWF.php';
$doaction = isset($_REQUEST['doaction']) &&  !empty($_REQUEST['doaction']) ?
			$_REQUEST['doaction'] :
			'';
$udwf = new AresUserDefineWF($_SESSION['user']['company_id'],
							 $_SESSION['user']['user_seq_no']);

require_once 'AresWorkflow.class.php';
$Workflow = new AresWorkflow();

switch ($doaction)
{
	case 'applylist':
		$where  = '';
		if (!empty($_POST['dept_id_s']) && !empty($_POST['dept_id_e'])  )
		{
			$where .= ' and b.dept_id >= \''.$_POST['dept_id_s'].'\'';
			$where .= ' and b.dept_id <= \''.$_POST['dept_id_e'].'\'';
		}
		if (!empty($_POST['emp_s']) && !empty($_POST['emp_e'])  )
		{
			$where .= ' and b.emp_id >= \''.$_POST['emp_s'].'\'';
			$where .= ' and b.emp_id <= \''.$_POST['emp_e'].'\'';
		}
		if (!empty($_POST['apply_b_date']) && !empty($_POST['apply_e_date'])  )
		{
			$where .= ' and a.create_date >= to_date(\''.$_POST['apply_b_date'].'\',\'yyyy-mm-dd\')';
			$where .= ' and a.create_date <= to_date(\''.$_POST['apply_e_date'].'\',\'yyyy-mm-dd\')';
		}
		if (!empty($_POST['flow_status']))
		{
			$where .= ' and a.status = \''.$_POST['flow_status'].'\'';
		}
		// get columns												  
		$cols = $udwf->getDetailDefine($_POST['menu_code']);
		$c = count($cols);
		for($i=0; $i<$c; $i++)
		{
			$cols[$i][0] = strtoupper($cols[$i]['COL_NAME']);
			$cols[$i][1] = $cols[$i]['COL_LABEL'];
		}
		//pr($cols);
		$g_tpl->assign('cols_cnt',$c+5);
	    $g_parser->ParseTable('title_list',$cols);
	    $totalrows = $udwf->getUDWFApply($_POST['menu_code'],$where,$cols,'admin',true);
		//echo 'total rows-> '.$totalrows.'<br/>';
		if ($totalrows > 0) {
			require_once 'GridView/Data_Paging.class.php';
			$pagesize = 10;
			$pageIndex = isset ($_GET ['pageIndex'] ) ? $_GET ['pageIndex'] : 1;
			// 重置 pageIndex, 比如开始查的资料有5 页，点到第5页后又下了条件，结查查询出来的
			// 的资料只有 1 页了，因为 url 上 pageIndex 还是  5, 不会显示资料，所以这里重置
			$pageIndex = $pageIndex>ceil($totalrows/$pagesize) ? 1: $pageIndex;
			$Paging = new Data_Paging ( array('total_rows' => $totalrows, 'page_size' => $pagesize));
			$Paging->openAjaxMode('gotopage');
			$g_tpl->assign ('pagingbar', $Paging->outputToolbar(2) );
			$g_parser->ParseTable('apply_list', 
								  $udwf->getUDWFApply($_POST['menu_code'],$where,$cols,'admin',false,$pagesize,$pagesize*($pageIndex-1)));
		} // end 分页
		break;
	case 'cancelflow': // 作废申请单
		/**
		 *  [scriptname] => ESNF004
		    [doaction] => cancelflow
		    [apply_type] => car
		    [apply_flowseqno] => 56
		    [menu_code] => ESNW201
		    [cancel_comment] => SSSS
		 * 
		 */
		$result = $udwf->cancelWorkflow($_GET['apply_flowseqno'],
										$_GET['menu_code'],
										$_GET['apply_type'],
										$_GET['cancel_comment']);
		if ($result['is_success'] == 'Y')
		{
			showMsg('作废成功');
		}else{
			showMsg('作废流程不成功,原因<br/>'.$result['error_msg'],'error');
		}
		break;
	default:break;
}
$g_parser->ParseSelect('menu_code_list',  $udwf->getWFAppList($GLOBALS['config']['default_lang'],true),'','');
$g_parser->ParseSelect('flow_status_list',$Workflow->getWFStatusList($GLOBALS['config']['default_lang']),'','');
