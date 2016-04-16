<?php /* Smarty version 2.6.11, created on 2016-03-03 18:49:16
         compiled from block_batch_apply_detail.html */ ?>

	<input type="hidden" name="action" id="action" value="batch_apply"/>
    <div align="right" id="toolbar" style="margin-bottom:10px;">
        <span><?php echo $this->_tpl_vars['CHOSE_EMP_LABEL']; ?>
</span>
        <span><?php echo $this->_tpl_vars['DELET_SELECTED_LABEL']; ?>
</span>
        <span><?php echo $this->_tpl_vars['IMPORT_LABEL']; ?>
</span>
        <span id="doPost"><?php echo $this->_tpl_vars['SUBMIT_BTN_LABEL']; ?>
</span>
    </div>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_emp_list.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script type="text/javascript">
	$(function() {
		$( "#toolbar span:first" ).button({
            icons: {
                primary: "ui-icon-newwin"
            }
        }).click(function(){
           openw('?scriptname=emp_lov','emplov',700,400);
        }).next().button({
            icons: {
                primary: "ui-icon-trash"
            }
        }).click(function(){
            return deleteSelectedRows('emplist');
        }).next().button({
            icons: {
                primary: "ui-icon-gear"
            }
        }).click(function(){
            openw('?scriptname=user_excel_upload&used_program=emp_import','emplov',470,400);
        }).next().button({
        	icons: {
                primary: "ui-icon-disk"
            }
        }).click(function(){
        	$('form').submit();// add by dennis 2013/09/16
        });
	});
</script>