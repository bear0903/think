<?php
/**
 * 使用者自定义流程，申请单栏位设定(insert/update)
 * 
 * @author Dennis
 * @last update 2010-02-21
 */

if (! defined ('DOCROOT' ))	die ( 'Attack Error.');
include_once 'AresUserDefineWF.php';
$WFDefine = new AresUserDefineWF($_SESSION['user']['company_id'],
							 	 $_SESSION['user']['user_seq_no']);						 	 
if (count($_POST)>0)
{
	$a = strtolower($_POST['doaction']);
		
	if($a == 'add'|| $a == 'update')
	{
		if(empty($_POST['col_name']))
		{
			//echo '栏位名称必须输入';
			echo $WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_COL_NAME_UNDEFINE);
			exit;
		}
		if(!empty($_POST['col_data_type']))
		{
			switch (strtolower($_POST['col_data_type']))
			{
				case 'varchar2':
					if (empty($_POST['col_data_length']))
					{
						//echo '文本型栏位必须输入栏位长度';
						echo $WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_COL_LEN_UNDEFINE);
						exit;
					}
					break;
				case 'date':
					// 日期栏位不需要长度
					if (!empty($_POST['col_data_length']))
					{
						unset($_POST['col_data_length']);
					}// endif
					break;
				case 'number':
					break;
				default:break;
			}
		}
		$coltype = strtolower($_POST['col_type']);
		switch ($coltype) {
			case 'select':
				if(empty($_POST['select_data_type']))
				{
					//echo '下拉清单资料来源类型必须输入';
					echo $WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_DATA_SOURCE_T_UNDEFINE);
					exit;
				}				
				if(empty($_POST['data_source']))
				{
					/*
					echo '下拉清单资料来源必须输入.'.chr(10).
					     '静态的格式为: key1:val1;key2:val2'.chr(10).
					     '动态的为 Select SQL 语句,可用的变量有'.chr(10).
					     '公司代码: $company_id'.chr(10).
					     '部门代码: $dept_id'.chr(10).
					     '员工代码: $emp_id';*/
					echo $WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_DATA_SOURCE_UNDEFINE).chr(10).
					     $WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_STATIC_DATA_FORMAT).': key1:val1;key2:val2'.chr(10).
					     $WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_DYNAMIC_SQL_VAR).chr(10).
					     '公司代码: $company_id'.chr(10).
					     '部门代码: $dept_id'.chr(10).
					     '员工代码: $emp_id';
					exit;
				};
			break;
			case 'file':
				if (isset($_POST['validate_rule']))
				unset($_POST['validate_rule']);
				if (isset($_POST['min_val']))
				unset($_POST['min_val']);
				if (isset($_POST['max_val']))
				unset($_POST['max_val']);
				if (isset($_POST['checked_val']))
				unset($_POST['checked_val']);
				if (isset($_POST['col_select_data_type']))
				unset($_POST['col_select_data_type']);
				if (isset($_POST['col_data_source']))
				unset($_POST['col_data_source']);
			default:
			break;
		}
				
		if(empty($_POST['col_label']))
		{
			//echo '栏位标签必须输入';
			echo $WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_COL_NAME_UNDEFINE);
			exit;
		}
		
		if(empty($_POST['layout_order']))
		{
			//echo '栏位序位必须输入';
			echo $WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_COL_SEQ_UNDEFINE);
			exit;
		}
	}
	
	//sleep(3);
	switch ($a)
	{
		case 'add':
			echo $WFDefine->insertDetailDefine($_POST['menu_code'],
											   $_POST['col_name'], 
											   $_POST['col_data_type'],
											   @$_POST['col_data_length'],
											   $_POST['col_label'],
											   $_POST['col_type'],
											   $_POST['layout_order'],
											   $_POST['is_required'],
											   @$_POST['validate_rule'],
											   @$_POST['min_val'],
											   @$_POST['max_val'],
											   @$_POST['date_format'],
											   @$_POST['checked_val'],
											   @$_POST['select_data_type'],
											   @$_POST['data_source']);
			break;
		case 'update':
			echo  $WFDefine->updateDetailDefine($_POST['menu_code'],
												$_POST['col_name'],
												$_POST['col_data_type'],
												@$_POST['col_data_length'],
												$_POST['col_label'],
												$_POST['col_type'],
												$_POST['layout_order'],
												$_POST['is_required'],
												@$_POST['validate_rule'],
												@$_POST['min_val'],
												@$_POST['max_val'],
												@$_POST['date_format'],
												@$_POST['checked_val'],
												@$_POST['select_data_type'],
												@$_POST['data_source']);
			break;
		case 'delete':
			echo $WFDefine->deleteDetailDefine($_POST['menu_code'],
											   $_POST['col_name']);
			break;
		case 'gen_db':
			echo $WFDefine->createSchema($_POST['menu_code']);
			break;
		default:break;
	}
	exit;
}else{
	$r = $WFDefine->checkDBSchema($_GET['menu_code']);
	if ( false !== $r)
	{
		//$GLOBALS['g_tpl']->assign('warn_msg',$r.'<br/>栏位加减或修改后,请点 <strong>"生成申请单"</strong>按钮重新生成申请单.');
		$GLOBALS['g_tpl']->assign('warn_msg',$r.'<br/>'.$WFDefine->getErrorMsg(AresUserDefineWF::WF_ERR_COL_CHANGED));						 
	}else{
		$GLOBALS['g_tpl']->assign('table_created','1');
	}
	$GLOBALS['g_parser']->ParseTable('cols_list',$WFDefine->getDetailDefine($_GET['menu_code']));
}
