<?php /* Smarty version 2.6.11, created on 2016-03-03 13:39:06
         compiled from emp_leave_apply_search.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'emp_leave_apply_search.html', 12, false),array('modifier', 'default', 'emp_leave_apply_search.html', 49, false),)), $this); ?>
<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/functions.js" type="text/javascript"></script>
</head>
<body class="page-container">
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['BLOCK_TITLE'],'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <form id="form1" name="form1" method="post">
        <table class="bordertable">
            <tr>
                <td class="column-label" ><?php echo $this->_tpl_vars['ABSENCE_NAME_LABEL']; ?>
</td>
                <td>
                    <select name="absence_seq_no" id="absence_seq_no">
                        <option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['leave_name_list'],'selected' => $_POST['absence_seq_no']), $this);?>

                    </select>
                </td>
            </tr>

            <tr>
                <td class="column-label"><?php echo $this->_tpl_vars['BEGIN_TIME_LABEL']; ?>
</td>
                <td>
                    <input type="text"
                           name="db_my_day1"
                           id="db_my_day1"
                           class="input-date"
                           value="<?php echo $_POST['db_my_day1']; ?>
"
                           title="Date Format:YYYY-MM-DD"/>
                   
                </td>
            </tr>
            <tr>
                <td class="column-label"><?php echo $this->_tpl_vars['END_TIME_LABEL']; ?>
</td>
                <td>
                    <input type="text"
                           name="db_my_day2"
                           id="db_my_day2"
                           class="input-date"
                           value="<?php echo $_POST['db_my_day2']; ?>
"
                           title="Date Format:YYYY-MM-DD"/>
                </td>
            </tr>
                        <?php if ($this->_tpl_vars['isassistant'] || $this->_tpl_vars['isadmin']): ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_dept_emp_cond.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
            <tr>
                <td class="column-label"><?php echo $this->_tpl_vars['WORKFLOW_STATUS_LABEL']; ?>
</td>
                <td>
                    <select name="flow_status" id="flow_status">
                        <option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['flow_status_list'],'selected' => ((is_array($_tmp=@$_GET['flowstatus'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_POST['flow_status']) : smarty_modifier_default($_tmp, @$_POST['flow_status']))), $this);?>

                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" name="submit" id="submit" value="<?php echo $this->_tpl_vars['SUBMIT_QRY_BTN_LABEL']; ?>
" class="button-submit"/>
                    <input type="button" name="reset"  value="<?php echo $this->_tpl_vars['RESET_LABEL']; ?>
"  onClick="clear_form('form1');clearParam();" class="button-submit" />
                </td>
            </tr>
        </table>
    </form>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php if (count ( $_POST ) > 0 || $_GET['flowstatus']): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_leave_apply_list.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php echo $this->_tpl_vars['pagingbar']; ?>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
    <script type="text/javascript">
	    $().ready(function(){
			$('#db_my_day1').datepicker({dateFormat:'yy-mm-dd',changeYear:true});
			$('#db_my_day2').datepicker({dateFormat:'yy-mm-dd',changeYear:true});
			attachClickEvent();
		});
        
        function clearParam()
        {
            var url = document.location.toString();
            document.location = url.substring(0,url.indexOf('&',0));
        }
        // add by dennis 2006-04-29 15:39:43
        function CancelWorkflow(obj)
        {
            var _cancel_comment = '';
            _cancel_comment = prompt('<?php echo $this->_tpl_vars['CONFIRM_CANCEL_MSG']; ?>
','');
            if (_cancel_comment!=null && _cancel_comment!='' )
            {
                obj.href += "&cancel_comment="+_cancel_comment;
                return true;
            }else{
                return false;
            }// end if
        }// end
    </script>