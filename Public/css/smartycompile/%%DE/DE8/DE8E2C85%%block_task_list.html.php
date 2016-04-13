<?php /* Smarty version 2.6.11, created on 2016-02-22 15:31:05
         compiled from block_task_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'block_task_list.html', 4, false),array('modifier', 'urlencode', 'block_task_list.html', 5, false),)), $this); ?>
<table class="bordertable">
    <?php if ($this->_tpl_vars['leave_apply_count'] > 0): ?> 
    <tr>
        <td class="column-label" width="100"><?php echo ((is_array($_tmp=@$this->_tpl_vars['WAIT_APPROVE_LEAVE_APPLY_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '请假待签') : smarty_modifier_default($_tmp, '请假待签')); ?>
</td>
        <td><a href="../mgr/redirect.php?scriptname=MDNH104&appdesc=<?php echo ((is_array($_tmp=$this->_tpl_vars['WAIT_APPROVE_LEAVE_APPLY_LABEL'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
"><?php echo $this->_tpl_vars['leave_apply_count']; ?>
</a></td>
    </tr>
    <?php endif; ?> 
    <?php if ($this->_tpl_vars['cancel_leave_apply_count'] > 0): ?> 
    <tr>
        <td class="column-label" width="100"><?php echo $this->_tpl_vars['WAIT_APPROVE_CANCEL_LEAVE_APPLY_LABEL']; ?>
</td>
        <td><a href="../mgr/redirect.php?scriptname=MDNH109&appdesc=<?php echo ((is_array($_tmp=$this->_tpl_vars['WAIT_APPROVE_CANCEL_LEAVE_APPLY_LABEL'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
"><?php echo $this->_tpl_vars['cancel_leave_apply_count']; ?>
</a></td>
    </tr>
    <?php endif; ?> 
    <?php if ($this->_tpl_vars['overtime_apply_count'] > 0): ?> 
    <tr>
        <td class="column-label"><?php echo $this->_tpl_vars['WAIT_APPROVE_OVERTIME_APPLY_LABEL']; ?>
</td>
        <td><a href="../mgr/redirect.php?scriptname=MDNH105&appdesc=<?php echo ((is_array($_tmp=$this->_tpl_vars['WAIT_APPROVE_OVERTIME_APPLY_LABEL'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
"><?php echo $this->_tpl_vars['overtime_apply_count']; ?>
</a></td>
    </tr>
    <?php endif; ?>
    <?php if ($this->_tpl_vars['trans_apply_count'] > 0): ?> 
    <tr>
        <td class="column-label" width="100"><?php echo $this->_tpl_vars['WAIT_APPROVE_TRANS_APPLY_LABEL']; ?>
</td>
        <td><a href="../mgr/redirect.php?scriptname=MDNH106&appdesc=<?php echo ((is_array($_tmp=$this->_tpl_vars['WAIT_APPROVE_TRANS_APPLY_LABEL'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
"><?php echo $this->_tpl_vars['trans_apply_count']; ?>
</a></td>
    </tr>
    <?php endif; ?>
    <?php if ($this->_tpl_vars['nocard_apply_count'] > 0): ?> 
    <tr>
        <td class="column-label" width="100"><?php echo $this->_tpl_vars['WAIT_APPROVE_NOCARD_APPLY_LABEL']; ?>
</td>
        <td><a href="../mgr/redirect.php?scriptname=MDNH107&appdesc=<?php echo ((is_array($_tmp=$this->_tpl_vars['WAIT_APPROVE_NOCARD_APPLY_LABEL'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
"><?php echo $this->_tpl_vars['nocard_apply_count']; ?>
</a></td>
    </tr>
    <?php endif; ?>
    <?php if ($this->_tpl_vars['resign_apply_count'] > 0): ?> 
    <tr>
        <td class="column-label" width="100"><?php echo $this->_tpl_vars['WAIT_APPROVE_RESIGN_APPLY_LABEL']; ?>
</td>
        <td><a href="../mgr/redirect.php?scriptname=MDNH108&appdesc=<?php echo ((is_array($_tmp=$this->_tpl_vars['WAIT_APPROVE_RESIGN_APPLY_LABEL'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
"><?php echo $this->_tpl_vars['resign_apply_count']; ?>
</a></td>
    </tr>
    <?php endif; ?>
    <?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['user_define_wf_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
        <td class="column-label" width="100"><?php echo ((is_array($_tmp=$this->_tpl_vars['user_define_wf_list'][$this->_sections['i']['index']]['FLOW_TYPE_DESC'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
</td>
        <td>
            <a href="?scriptname=user_define_wf_approve&doaction=search&flow_type=<?php echo $this->_tpl_vars['user_define_wf_list'][$this->_sections['i']['index']]['FLOW_TYPE_CODE']; ?>
&menu_code=<?php echo $this->_tpl_vars['user_define_wf_list'][$this->_sections['i']['index']]['MENU_CODE']; ?>
">
                <?php echo $this->_tpl_vars['user_define_wf_list'][$this->_sections['i']['index']]['CNT']; ?>

            </a>
        </td>
    <tr>
    <?php endfor; endif; ?>
</table>