<?php /* Smarty version 2.6.11, created on 2016-03-21 13:08:40
         compiled from block_trans_apply.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'block_trans_apply.html', 10, false),array('function', 'html_options', 'block_trans_apply.html', 22, false),)), $this); ?>
	<table class="bordertable">
		<tr>
			<td width="100" class="column-label"><?php echo $this->_tpl_vars['TRANS_DATE_LABEL']; ?>
 *</td>
			<td>
				<input type="text" 
					   name="trans_date" 
					   id="trans_date"
					   class="input-date"
					   title="Date Format: YYYY-MM-DD"
					   value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
"/>
				<script type="text/javascript">
				$().ready(function(){
					$('#trans_date').datepicker({dateFormat:'yy-mm-dd',changeMonth:true,changeYear:true});
					
				});
				</script>
			</td>
			<td width="100" class="column-label"><?php echo $this->_tpl_vars['NEW_NB_NEWLEADER_LABEL']; ?>
</td>
			<td>
				<select name="new_nb_newleader" id="new_nb_newleader">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_nb_list'],'selected' => $this->_tpl_vars['s_new_nb_newleader']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['TRANS_TYPE_LABEL']; ?>
 *</td>
			<td>
				<select name="trans_type" 
						id="trans_type"
						onchange="changeSubListValue(this.options[this.selectedIndex].value)">
				<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['transtype_list'],'selected' => $this->_tpl_vars['s_transtype_master_id']), $this);?>

				</select>
			</td>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_TRANSFER_RESON_LABEL']; ?>
</td>
			<td>
				<select name="new_reason" id="new_reason">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_reason'],'selected' => $_POST['new_reason']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_DEPARTMENT_LABEL']; ?>
 *</td>
			<td>
				<select name="new_department" id="new_department">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_department_list'],'selected' => $this->_tpl_vars['s_new_department']), $this);?>

				</select>
			</td>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_OVERTIME_TYPE_LABEL']; ?>
</td>
			<td>
				<select name="new_overtime_type_id" id="new_overtime_type_id">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_otype_list'],'selected' => $this->_tpl_vars['s_new_otype']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_TITLE_LABEL']; ?>
 *</td>
			<td>
				<select name="new_title_id" id="new_title_id">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_title_list'],'selected' => $this->_tpl_vars['s_new_title']), $this);?>

				</select>
			</td>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_ABSENCE_TYPE_LABEL']; ?>
</td>
			<td>
				<select name="new_absence_type_id" id="new_absence_type_id">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_absence_list'],'selected' => $this->_tpl_vars['s_new_absence']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_JOBCATEGORY_LABEL']; ?>
 *</td>
			<td>
				<select name="new_jobcategory" id="new_jobcategory">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_jobcategory_list'],'selected' => $this->_tpl_vars['s_new_jobcategory']), $this);?>

				</select>
			</td>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_YEAR_TYPE_LABEL']; ?>
</td>
			<td>
				<select name="new_yeartype_id" id="new_yeartype_id">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_year_list'],'selected' => $this->_tpl_vars['s_new_year']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_PERIOD_LABEL']; ?>
 *</td>
			<td>
				<select name="new_period_id" id="new_period_id">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_period_list'],'selected' => $this->_tpl_vars['s_new_period']), $this);?>

				</select>
			</td>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_JOB_LABEL']; ?>
</td>
			<td>
				<select name="new_job_id" id="new_job_id">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_job_list'],'selected' => $this->_tpl_vars['s_new_job']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_COSTALLOCATION_LABEL']; ?>
</td>
			<td>
				<select name="new_costallocation" id="new_costallocation">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_costallocation_list'],'selected' => $this->_tpl_vars['s_new_costallocation']), $this);?>

				</select>
			</td>
			<td class="column-label"><?php echo $this->_tpl_vars['NEW_TAX_LABEL']; ?>
</td>
			<td>
				<select name="new_tax_id" id="new_tax_id">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_tax_list'],'selected' => $this->_tpl_vars['s_new_tax']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
		  <td class="column-label"><?php echo $this->_tpl_vars['NEW_CONTRACT_LABEL']; ?>
</td>
			<td colspan="3">
				<select name="new_contract" id="new_contract">
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['new_contract_list'],'selected' => $this->_tpl_vars['s_new_contract']), $this);?>

				</select>
			</td>
	    </tr>
	    <tr>
			<td valign="top" class="column-label"><?php echo $this->_tpl_vars['REMARK_LABEL']; ?>
</td>
			<td colspan="3"><textarea name="remark" id="remark" ></textarea>
			</td>
		</tr>
		<tr>
			<td></td>
			<td colspan="3">
				<input type="submit" name="submit" value="<?php echo $this->_tpl_vars['SUBMIT_BTN_LABEL']; ?>
" class="button-submit"/>
			    <input type="submit" name="save"   value="<?php echo $this->_tpl_vars['TMP_SUBMIT_BTN_LABEL']; ?>
" class="button-submit" title="Save Data Only"/>
			</td>
		</tr>
	</table>