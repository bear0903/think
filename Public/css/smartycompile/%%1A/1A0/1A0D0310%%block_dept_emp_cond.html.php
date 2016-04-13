<?php /* Smarty version 2.6.11, created on 2016-03-03 13:39:06
         compiled from block_dept_emp_cond.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'block_dept_emp_cond.html', 9, false),array('modifier', 'default', 'block_dept_emp_cond.html', 9, false),)), $this); ?>
		
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['DEPT_START_LABEL']; ?>
</td>
			<td>
				<select name="start_dept" 
						id="start_dept"
						style="width:275px;">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['dept_list'],'selected' => ((is_array($_tmp=@$_POST['start_dept'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['s_dept_seqno']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['s_dept_seqno']))), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['DEPT_END_LABEL']; ?>
</td>
			<td>
				<select name="end_dept" 
						id="end_dept"
						style="width:275px;">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['dept_list'],'selected' => ((is_array($_tmp=@$_POST['end_dept'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['s_dept_seqno']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['s_dept_seqno']))), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['EMP_START_LABEL']; ?>
</td>
			<td>
				<input type="text" name="start_emp" class="input-text" value="<?php echo $_POST['start_emp']; ?>
"/>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['EMP_END_LABEL']; ?>
</td>
			<td>
				<input type="text" name="end_emp" class="input-text" value="<?php echo $_POST['end_emp']; ?>
"/>
			</td>
		</tr>