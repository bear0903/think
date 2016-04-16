<?php
/*
 * eHR Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.areschina.com/license/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@areschina.com so we can send you a copy immediately.
 *
 * @category   eHR
 * @package    Form
 * @subpackage Form_Query
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Form_Query.class.php Jan 31, 2008 11:05:13 AM Dennis $
 */

/**
 * Query Form Components
 *   查询 Form 组件
 * @category   eHR
 * @package    Form
 * @subpackage Form_Query
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
require_once 'Form_Abstract.class.php';
class Form_Query extends Form_Abstract
{
	/**
	 * 提交后是否保留 where 条件, default 'true'
	 * @var boolean
	 */
	public $allowKeepCondition = true;

	/**
	 * 是否允许保存查询条件(暂不支持保存查询,以后enhance)
	 *
	 * @var boolean
	 */
	public $allowSaveCondition = false;

	/**
	 * 是否显示 <b>重置</b> 按钮, default 'false'
	 *
	 * @var boolean
	 */
	public  $showResetButton = true;

	private $_operatorList = array(array(0=>'=',1=>'='),
								   array(0=>'>',1=>'&gt;'),
								   array(0=>'>=',1=>'&gt;='),
								   array(0=>'<',1=>'&lt;'),
								   array(0=>'<=',1=>'&lt;='),
								   array(0=>'like',1=>'like'));
    private $_elementCnt;
    
    /**
     * 查询按钮文字
     *
     * @var string
     */
    private $_submit_qry_btn_label;
    
    /**
     * 重设查询条件文字
     *
     * @var string
     */
    private $_reset_btn_label;

	/**
	 * Construct of class Form_Query
	 *
	 * @param array $form_config
	 * @param array $element_config
	 * @author Dennis
	 */
	public function __construct(array $form_config,array $element_config)
	{
		//pr($element_config);
		if (is_array($element_config) && count($element_config)>0)
		{
			parent::__construct($form_config,$element_config);
			self::_init($form_config);
	
			// rewrite form name or id
			if ('form1' == $this->_name)
			{
				$this->_name = getGUID('form');
			}// end if
			if ('form1' == $this->_id)
			{
				$this->_id = getGUID('form');
			}// end if
		}
		$this->_elementCnt = count($element_config);
	}// end class constructor

	/**
	 * 初始化 Form 属性
	 * @param array $config
	 * @return void no return value
	 * @access protected
	 * @author Dennis
	 */
	protected function _init(array $config)
	{
		if (is_array($config))
		{
			foreach($config as $key=>$value)
			{
				switch(strtolower($key))
				{
					case 'allowkeepcondition':
						$this->allowKeepCondition = $value;
						break;
					case 'allowsavecondition':
						$this->allowSaveCondition = $value;
						break;
					case 'showresetbutton':
						$this->showResetButton = $value;
						break;
						/*
						 case 'target':
						 $this->target = $value;
						 break;
						 case 'classname':
						 $this->className = $value;
						 break;
						 case 'method':
						 $this->method = $value;
						 break;*/
					default:break;
				}// end switch()
				// remove the public properties
				unset($config[$key]);
			}// end foreach
		}// end if
		$btnLabels = $this->_getBtnLabel();
		$this->_submit_qry_btn_label = $btnLabels['SUBMIT_QRY_BTN_LABEL'];
		$this->_reset_btn_label      = $btnLabels['RESET_BTN_LABEL'];
	}// end _init()
	
	/**
	 *  根据上下文多语Get 共用 Button Label
	 *
	 * @return array
	 * @author Dennis 20090531
	 */
	private function _getBtnLabel()
	{
		global $g_db_sql;
		$sql = <<<eof
				select name,value 
		          from app_muti_lang 
		         where program_no = :program_no 
		           and type_code  = :type_code
		           and lang_code  = :lang_code
eof;
		$g_db_sql->SetFetchMode(ADODB_FETCH_NUM);
		return recombineArray($g_db_sql->GetArray($sql,array('program_no'=>'ESN0000',
															 'type_code'=>'BL',
															 'lang_code'=>$GLOBALS['config']['default_lang'])));
	}// end _getBtnLabel()
	
	/**
	 * Get Box Header HTML code
	 *
	 * @return string
	 * @author Dennis 
	 */
	private function _getBoxHeader()
	{
		/*
		return '<div class="sidebox resources">
					<div class="x-box-tl">
						<div class="x-box-tr">
							<div class="x-box-tc"></div>
						</div>
					</div>
					<!-- Header Box End -->
					<div class="x-box-ml">
					<div class="x-box-mr">
					<div class="x-box-mc">
					<!-- Grid Title -->
					<h3 style="margin:1px; padding:1px;"></h3>';
					*/
		return '<div style="overflow-y:auto;overflow-x:auto;padding:10px; margin-bottom:10px;" class="ui-widget-content ui-corner-all">';
	}// end _getBoxHeader()
	
	/**
	 * Get Box Footer Html Code
	 *
	 * @return string
	 */
	private  function _getBoxFooter()
	{
		//return '</div></div></div><div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div></div>';
		return '</div>';
	}// end _getBoxFooter();
	
	/**
	 * Help function of _layout()
	 * @author Dennis 2011-11-16
	 */
	private function _getJsBegin()
	{
		$jscode = <<<eof
			<script type="text/javascript">
				$().ready(function(){
eof;
		return $jscode;
	}
	/**
	 * Help function of _layout()
	 * @author Dennis 2011-11-16
	 */
	private function _getJsEnd()
	{
		$jscode = <<<eof
			});
		</script>
eof;
		return $jscode;
	}
	/**
	 * 
	 * Get JqueryUI Date Picker js code
	 * @param string $id input id
	 * @author Dennis 2011-11-16
	 */
	private function _getDatePickerJS($id)
	{
		$jscode = <<<eof
			$('#$id').datepicker({changeYear:true,changeMonth:true,dateFormat:'yy-mm-dd'});
eof;
		return $jscode;
	}
	/**
	 * 自动布局查询栏位
	 * @param no
	 * @return string
	 * @see Form_Abstract::_layout()
	 * @access protected
	 * @author Dennis
	 */
	protected  function _layout()
	{
		//pr($_POST);
		/**
		 * 查询栏位的个数
		 */
		$dash   = '_1';
		$column_html_code = '<table border="0" cellpadding="0" cellspacing="0" class="bordertable">';
	
        // 资料行开始 html code
        $row_start_html  = '<tr><td>';
        $row_mid_html    = '</td><td>';
        $row_end_html    = '</td></tr>';
		foreach($this->_elementsConfig as $column)
		{
			//echo $column['TABLE_NAME'].'<br/>';
			$column_html = '';
			// 因为栏位名称如果是 table.columnname post 之后会变成 table_columnname 所以
			// 这里用 '|' 后面 replace 成 '.'
			//$column_name = $column['TABLE_NAME'].'|'.$column['COLUMN_NAME']; // remark by Dennis 2008-05-21
			// 用栏位别名来做条件查询, HCP程式在组合 SQL 自动加了别名
			// Modified by Dennis 2008-05-21
			$column_name = $column['TABLE_NAME'].'_'.$column['COLUMN_NAME'];
			$is_rang     = $column['IS_RANG_CONDITION'];
			//$val         = isset($_POST[$column_name]) ? $_POST[$column_name] : '';
			$val         = isset($_SESSION[$_GET['scriptname']]['queryform'][$column_name]) ? 
			                 $_SESSION[$_GET['scriptname']]['queryform'][$column_name]        :
			                 '';
			//$val1        = isset($_POST[$column_name.$dash]) ? $_POST[$column_name.$dash] : '';
			
			$val1        = isset($_SESSION[$_GET['scriptname']]['queryform'][$column_name.$dash]) ? 
                             $_SESSION[$_GET['scriptname']]['queryform'][$column_name.$dash]        :
                             '';
			switch(strtolower($column['QUERY_COLUMN_TYPE']))
			{
				case 'text':
					$txt = new Input_Text(array('name'=>$column_name,
												'id'=>$column_name,
												'value'=>$val));
					$column_html = $txt->render();
					// 区间条件
					if ('1' == $is_rang)
					{
						$txt1 = new Input_Text(array('name'=>$column_name.$dash,
													 'id'=>$column_name.$dash,
													 'value'=>$val1));
						$column_html .= '-'.$txt1->render();
					}// end if
					break;
				case 'list':
					$config = array('name'=>$column_name,
	    							'id'=>$column_name,
	    							'dataSource'=>$column['DATA_SOURCE'],
	    							'selectedValue'=>$val);
					$list = new Input_List($config);
					$column_html .= $list->render();
					if ('1' == $is_rang)
					{
						$config = array('name'=>$column_name.$dash,
		    							'id'=>$column_name.$dash,
		    							'dataSource'=>$column['DATA_SOURCE'],
		    							'selectedValue'=>$val1);
						$list1 = new Input_List($config);
						$column_html .= '-'.$list1->render();
					}// end if
					break;
				case 'calendar':
					//$js_path = dirname(dirname($_SERVER['PHP_SELF'])).'/libs/Library/JsCalendar/';
					// modify by dennis for fixed IE6 issue (compal 20091114)
					//$js_path = DOCROOT.'/libs/library/JsCalendar/';
					//echo $js_path;
					$jscode = $this->_getDatePickerJS($column_name);
					$cal = new Input_Calendar(array('name'=>$column_name,
													'id'=>$column_name,
													//'jsPath'=>$js_path,
													'value'=>$val,
													/*'lang'=>$GLOBALS['config']['default_lang']*/));
					$column_html = $cal->render();
					// 区间条件
					if ('1' == $is_rang)
					{
						$cal1 = new Input_Calendar(array('name'=>$column_name.$dash,
														 'id'=>$column_name.$dash,
														 //'jsPath'=>$js_path,
														 'value'=>$val1,
														 /*'lang'=>$GLOBALS['config']['default_lang']*/));
						$column_html .= '-'.$cal1->render();
						$jscode .= $this->_getDatePickerJS($column_name.$dash);
					}// end if
					$column_html .=$this->_getJsBegin().$jscode.$this->_getJsEnd();
					break;
				case 'checkbox':
					// checkbox 不存在区间问题
					$cvalue = ('on'==strtolower($val) ? $column['CHECKED_VALUE'] : '');
					$checkbox = new Input_Checkbox(array('name'=>$column_name,
														 'id'=>$column_name,
														 'value'=>$cvalue,
														 'checkedValue'=>$column['CHECKED_VALUE']));
					$column_html = $checkbox->render();
					break;
				default : break;
			}// end switch
			$op_html = '';
			if ('1' != $is_rang)
			{
				/*
				$op = isset($_POST['_op_'.$column_name]) ?
					  $_POST['_op_'.$column_name]        :
					  '';
                */
					  
			    $op = isset($_SESSION[$_GET['scriptname']]['queryform']['_op_'.$column_name]) ?
                      $_SESSION[$_GET['scriptname']]['queryform']['_op_'.$column_name]        :
                      '';
                      
				$op_config = array('name'=>'_op_'.$column_name,
	    						   'dataSource'=>$this->_operatorList,
	    						   'selectedValue'=>$op,
	    						   'className'=>'select-operateor');
				$op_list = new Input_List($op_config);
				$op_html = $op_list->render();
			}// end if
			//dd($column);
			$column_html_code .= $row_start_html;
			// 没有多语时显示栏位名称
			$column_html_code .= empty($column['PROMPT_TEXT']) ?
								 $column['COLUMN_NAME']        : 
							     $column['PROMPT_TEXT'];
			$column_html_code .= $row_mid_html;
			$column_html_code .= $op_html.$column_html.$row_end_html;
		}// end foreach
		// submit & reset button
		if (!empty($column_html_code))
		{			
			$rbtn = new Input_Reset_Js(array('formId'=>$this->_id,
											 'name'=>'reset',
											 'className'=>'button-submit',
											 'value'=>$this->_reset_btn_label));
			// add sumbit button id by Dennis 2014/01/16
            $sbtn = new Input_Submit(array('name'=>'submit_button',
                                           'id'=>'submit',
            							   'className'=>'button-submit',
            							   'value'=>$this->_submit_qry_btn_label));
			$column_html_code .= '<tr><td>';
            $column_html_code .= '&nbsp;';
            $column_html_code .= $row_mid_html;
            $column_html_code .= $sbtn->render().'&nbsp;&nbsp;&nbsp;'.$rbtn->render();
            $column_html_code .= $row_end_html;
		}// end if
		return self::_getBoxHeader().$column_html_code.'</table>'.self::_getBoxFooter();
	}// end layout()

	/**
	 * 输出Form html code 如:
	 * <code>
	 * 	<form id='myform' name='form1' action='?' method='post' enctype='multipart/form-data'>
	 *  </form>
	 * </code>
	 * @param no
	 * @return string
	 * @access public
	 * @author Dennis
	 */
	public function render()
	{
		$form_html_code = '';
		if ($this->_elementCnt>0)
		{
			$form_html_code  = '<form name="'.$this->_name.'" id="'.$this->_id.'" ';
			$form_html_code .= empty($this->action) ? '' : 'action="'.$this->action.'" ' ;
			$form_html_code .= empty($this->method) ? '' : 'method="'.$this->method.'" ' ;
			$form_html_code .= empty($this->target) ? '' : 'target="'.$this->target.'" ' ;;
			$form_html_code .= '>';
			$form_html_code .= '<input type="hidden" name="do" value="query"/>';
			$form_html_code .= $this->_layout();
			// 放到 _layout function 中去了
			// modified by Dennis 2008-03-26
			//$form_html_code .= '<hr size="1"/><div align="center">';
			//$btn = new Input_Reset_Js(array('formId'=>$this->_id,'value'=>'Reset'));
			//$form_html_code .= $btn->render();
			//$sbtn = new Input_Submit(array('value'=>'Submit'));
			//$form_html_code .= $sbtn->render();
			$form_html_code .= '</form>';
		}
		return $form_html_code;
	}// end render();

	public function dispatch()
	{
		echo $this->render();
	}// end dispatch()
}// end class Form_Query
?>