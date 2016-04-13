<?php /* Smarty version 2.6.11, created on 2016-03-03 17:46:21
         compiled from block_compensatory_leave.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'block_compensatory_leave.html', 6, false),array('modifier', 'string_format', 'block_compensatory_leave.html', 16, false),)), $this); ?>

<table cellpadding="0" cellspacing="0" class="bordertable">
	<thead>
		<tr>
			<th><?php echo $this->_tpl_vars['SEQ_LABEL']; ?>
</th>
			<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['BASE_DATE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '基准日期') : smarty_modifier_default($_tmp, '基准日期')); ?>
</th>
			<th><?php echo $this->_tpl_vars['ALREADY_HOURS_LABEL']; ?>
</th>
			<th><?php echo $this->_tpl_vars['LEFT_HOURS_LABEL']; ?>
</th>
		</tr>
	</thead>
	<tbody>
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['compensatory_leave']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<td><?php echo $this->_sections['i']['rownum']; ?>
</td>
			<td><?php echo $this->_tpl_vars['compensatory_leave'][$this->_sections['i']['index']]['YM']; ?>
</td>
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['compensatory_leave'][$this->_sections['i']['index']]['ALREADY_HOURS'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%.2f') : smarty_modifier_string_format($_tmp, '%.2f')); ?>
</td>
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['compensatory_leave'][$this->_sections['i']['index']]['LEFT_HOURS'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%.2f') : smarty_modifier_string_format($_tmp, '%.2f')); ?>
</td>
		</tr>
		<?php endfor; else: ?>
		<tr>
			<td colspan="4">
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_info.html", 'smarty_include_vars' => array('msg_txt' => $this->_tpl_vars['NO_DATA_FOUND_MSG'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>