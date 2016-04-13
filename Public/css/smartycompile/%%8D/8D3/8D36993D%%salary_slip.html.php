<?php /* Smarty version 2.6.11, created on 2016-03-18 11:44:59
         compiled from salary_slip.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'string_format', 'salary_slip.html', 26, false),array('modifier', 'number_format', 'salary_slip.html', 26, false),)), $this); ?>

</head>
<body class="page-container">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_salary_period.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<table class="bordertable">
	<tr>
		<th nowrap><?php echo $this->_tpl_vars['ROW_NO_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['SALARY_YM_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['FACT_SALARY_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['FIX_SALARY_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['TEMPORARY_SALARY_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['OVERTIME_SALARY_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['LEAVE_SALARY_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['INSURANCE_SALARY_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['TAX_AMOUNT_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['WELFARE_AMOUNT_LABEL']; ?>
</th>
		<!-- {* 福利金 *}-->
		<th nowrap><?php echo $this->_tpl_vars['SALARY_TAX_LABEL']; ?>
</th>
		<th nowrap><?php echo $this->_tpl_vars['VIEW_RESULT_LABEL']; ?>
</th>
	</tr>
	<?php unset($this->_sections['salary_form']);
$this->_sections['salary_form']['name'] = 'salary_form';
$this->_sections['salary_form']['loop'] = is_array($_loop=$this->_tpl_vars['salary_form_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['salary_form']['show'] = true;
$this->_sections['salary_form']['max'] = $this->_sections['salary_form']['loop'];
$this->_sections['salary_form']['step'] = 1;
$this->_sections['salary_form']['start'] = $this->_sections['salary_form']['step'] > 0 ? 0 : $this->_sections['salary_form']['loop']-1;
if ($this->_sections['salary_form']['show']) {
    $this->_sections['salary_form']['total'] = $this->_sections['salary_form']['loop'];
    if ($this->_sections['salary_form']['total'] == 0)
        $this->_sections['salary_form']['show'] = false;
} else
    $this->_sections['salary_form']['total'] = 0;
if ($this->_sections['salary_form']['show']):

            for ($this->_sections['salary_form']['index'] = $this->_sections['salary_form']['start'], $this->_sections['salary_form']['iteration'] = 1;
                 $this->_sections['salary_form']['iteration'] <= $this->_sections['salary_form']['total'];
                 $this->_sections['salary_form']['index'] += $this->_sections['salary_form']['step'], $this->_sections['salary_form']['iteration']++):
$this->_sections['salary_form']['rownum'] = $this->_sections['salary_form']['iteration'];
$this->_sections['salary_form']['index_prev'] = $this->_sections['salary_form']['index'] - $this->_sections['salary_form']['step'];
$this->_sections['salary_form']['index_next'] = $this->_sections['salary_form']['index'] + $this->_sections['salary_form']['step'];
$this->_sections['salary_form']['first']      = ($this->_sections['salary_form']['iteration'] == 1);
$this->_sections['salary_form']['last']       = ($this->_sections['salary_form']['iteration'] == $this->_sections['salary_form']['total']);
?>
	<tr>
		<td align="center"><?php echo $this->_sections['salary_form']['index']+1; ?>
</td>
		<td align="center"><?php echo $this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['PERIOD_DETAIL_ID1']; ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['EMP_TOTAL_AMOUNT'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['FIX_AMOUNT'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['TEMP_AMOUNT'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['OVERTIME_AMOUNT'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['ABSENCE_AMOUNT'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['INSURE_EMP_AMOUNT'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['TAX_AMOUNT'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['WELFARE_AMOUNT'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td align="center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['SALARY_TAX'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
		<td>
			<a href="?scriptname=salary_slip_detail&salary_period_id=<?php echo $this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['PERIOD_DETAIL_ID']; ?>
&emp_seq_no=<?php echo $this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['EMP_SEQ_NO']; ?>
&salary_key_id=<?php echo $this->_tpl_vars['salary_form_list'][$this->_sections['salary_form']['index']]['PERIODSALARY_RESULT_ID']; ?>
&period_year=<?php echo $this->_tpl_vars['year']; ?>
&period_month=<?php echo $this->_tpl_vars['month']; ?>
&appdesc=<?php echo $this->_tpl_vars['BLOCK_TITLE']; ?>
">
			<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/pe.png" width="16" height="16" border="0"> </a>
		</td>
	</tr>
	<?php endfor; else: ?>
	<tr>
		<td colspan="12"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_info.html", 'smarty_include_vars' => array('msg_txt' => $this->_tpl_vars['NO_DATA_FOUND_MSG'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
	</tr>
	<?php endif; ?>
</table>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>