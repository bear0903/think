<?php /* Smarty version 2.6.11, created on 2016-03-18 10:28:08
         compiled from hr_check_data.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'hr_check_data.html', 46, false),)), $this); ?>
</head>
<body class="page-container">
<form name="form2" method="post">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => ($this->_tpl_vars['BLOCK_TITLE']),'showLine' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<table class="bordertable" id="emplist">
	<tr>
		<th></th>
		<th>审核说明</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['DEPT_ID_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "部門代碼") : smarty_modifier_default($_tmp, "部門代碼")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['DEPT_NAME_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "部門名稱") : smarty_modifier_default($_tmp, "部門名稱")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['EMP_ID_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "員工代碼") : smarty_modifier_default($_tmp, "員工代碼")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['EMP_NAME_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "姓名") : smarty_modifier_default($_tmp, "姓名")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['HOME_ADDRD_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "家庭地址") : smarty_modifier_default($_tmp, "家庭地址")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['HOME_TEL_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "家庭電話") : smarty_modifier_default($_tmp, "家庭電話")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['FILE_ADDR_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "戶籍地址") : smarty_modifier_default($_tmp, "戶籍地址")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['FILE_CONTACT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "戶籍地聯繫人") : smarty_modifier_default($_tmp, "戶籍地聯繫人")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['FILE_POSTCODE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "戶籍地郵編") : smarty_modifier_default($_tmp, "戶籍地郵編")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['CURR_CONTACT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "居住地聯繫人") : smarty_modifier_default($_tmp, "居住地聯繫人")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['CURR_POSTCODE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "居住地郵編") : smarty_modifier_default($_tmp, "居住地郵編")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['EMERGENCY_CONTACT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "緊急聯繫人") : smarty_modifier_default($_tmp, "緊急聯繫人")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['EMERGENCY_TEL_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "緊急聯繫電話") : smarty_modifier_default($_tmp, "緊急聯繫電話")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['CELLPHONE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "手機") : smarty_modifier_default($_tmp, "手機")); ?>
</th>
		<th nowrap><?php echo ((is_array($_tmp=@$this->_tpl_vars['EXTESION_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, "分機號") : smarty_modifier_default($_tmp, "分機號")); ?>
</th>
	</tr>
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['emp_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<tr>
		<!-- <th><input type="checkbox" name="emp_seqno[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMP_SEQNO']['val']; ?>
"/></th> -->
		<td nowrap>
			<input type="hidden" name="emp_seqno[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMP_SEQNO']['val']; ?>
"/>

            <input type="radio"
            	   id="none_action<?php echo $this->_sections['i']['index']; ?>
"
            	   name="approve_action<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMP_SEQNO']['val']; ?>
"
            	   value="none" checked />
            <label for="none_action<?php echo $this->_sections['i']['index']; ?>
"><?php echo ((is_array($_tmp=@$this->_tpl_vars['NONE_ACTION_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '無決定') : smarty_modifier_default($_tmp, '無決定')); ?>
</label><br/>
            <input type="radio"
                   id="approve_action<?php echo $this->_sections['i']['index']; ?>
"
            	   name="approve_action<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMP_SEQNO']['val']; ?>
"
            	   value="Y"
            	    />
        	<label for="approve_action<?php echo $this->_sections['i']['index']; ?>
"><?php echo ((is_array($_tmp=@$this->_tpl_vars['APPROVE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '核准') : smarty_modifier_default($_tmp, '核准')); ?>
</label><br/>
            <input type="radio"
                   id="reject_action<?php echo $this->_sections['i']['index']; ?>
"
            	   name="approve_action<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMP_SEQNO']['val']; ?>
"
            	   value="N"
            	    />
            <label for="reject_action<?php echo $this->_sections['i']['index']; ?>
"><?php echo ((is_array($_tmp=@$this->_tpl_vars['REJECT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '駁回') : smarty_modifier_default($_tmp, '駁回')); ?>
</label>
        </td>
        <td><textarea name="approve_remark<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMP_SEQNO']['val']; ?>
" cols="4"></textarea></td>
		<td><?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['DEPT_ID']['val']; ?>
</td>
		<td><?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['DEPT_NAME']['val']; ?>
</td>
		<td><?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMP_ID']['val']; ?>
</td>
		<td><?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMP_NAME']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_ADDRESS']['class']; ?>
">
			<input type="hidden" name="mailaddress[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_ADDRESS']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_ADDRESS']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['HOME_TEL']['class']; ?>
">
			<input type="hidden" name="address_tel[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['HOME_TEL']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['HOME_TEL']['val']; ?>

		</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['PERMANENT_ADDRESS']['class']; ?>
">
			<input type="hidden" name="address[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['PERMANENT_ADDRESS']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['PERMANENT_ADDRESS']['val']; ?>

		</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['ADDRESS_CONTACTOR']['class']; ?>
">
			<input type="hidden" name="address_man[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['ADDRESS_CONTACTOR']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['ADDRESS_CONTACTOR']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['POSTCODE']['class']; ?>
">
			<input type="hidden" name="addresszipcode[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['POSTCODE']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['POSTCODE']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_CONTACTOR']['class']; ?>
">
			<input type="hidden" name="mailaddress_man[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_CONTACTOR']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_CONTACTOR']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_POSTCODE']['class']; ?>
">
			<input type="hidden" name="mailaddresszipcode[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_POSTCODE']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['MAIL_POSTCODE']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMERGENCY_CONTRACTOR']['class']; ?>
">
			<input type="hidden" name="emergencycontactor[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMERGENCY_CONTRACTOR']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMERGENCY_CONTRACTOR']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMERGENCY_TEL']['class']; ?>
">
			<input type="hidden" name="emergencycontactor_tel[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMERGENCY_TEL']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EMERGENCY_TEL']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['CELLPHONE_NO']['class']; ?>
"><input type="hidden" name="mobiletel[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['CELLPHONE_NO']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['CELLPHONE_NO']['val']; ?>
</td>
		<td class="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EXTENSION']['class']; ?>
"><input type="hidden" name="tel_part[]" value="<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EXTENSION']['val']; ?>
"/>
			<?php echo $this->_tpl_vars['emp_list'][$this->_sections['i']['index']]['EXTENSION']['val']; ?>
</td>
	</tr>
	<?php endfor; else: ?>
    <tr>
        <td colspan="17">
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_info.html", 'smarty_include_vars' => array('msg_txt' => $this->_tpl_vars['NO_DATA_FOUND_MSG'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</td>
    </tr>
    <?php endif; ?>
</table>
<input type='hidden' id=rowcount value="<?php echo $this->_sections['i']['rownum']; ?>
"/>
<?php if (count ( $this->_tpl_vars['emp_list'] ) > 0): ?>
	<div align="center">
		<br/>
		<input type="submit" name="submitform" value="<?php echo $this->_tpl_vars['SUBMIT_BTN_LABEL']; ?>
" class="button-submit"/>
		<input type="button" id="approveAll"   value="<?php echo $this->_tpl_vars['APPROVE_ALL_BTN_LABEL']; ?>
" class="button-submit"/>
		<input type="button" id="rejectAll"    value="<?php echo $this->_tpl_vars['REJECT_ALL_BTN_LABEL']; ?>
" class="button-submit"/>
		<input type="button" id="resetAll"     value="<?php echo $this->_tpl_vars['RESET_BTN_LABEL']; ?>
" class="button-submit"/>
	</div>
<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</form>
<script type="text/javascript">
$("#approveAll").click(function(){
	setRadioChecked("approve_action");
});
$("#rejectAll").click(function(){
	setRadioChecked("reject_action");
});
$("#resetAll").click(function(){
	setRadioChecked("none_action");
});

function setRadioChecked(obj_name){
	var n=$("#rowcount").val();
	for(i=0;i<n;i++){
		$("#"+obj_name+i).attr("checked","true");
	}
}
function CheckAllRows(tabid, bCheck) {
    var tab = document.getElementById(tabid);
    var rows =  tab.rows.length;
    for (var i = 0; i < rows; i++) {
        //alert(tab.rows[i].cells[0].childNodes[0].type)
        if (tab.rows[i].cells[0].childNodes[0].type == 'checkbox') {
        	tab.rows[i].cells[0].childNodes[0].checked = bCheck;
        }//end if
    }// end for loop
}// end CheckAll()

</script>