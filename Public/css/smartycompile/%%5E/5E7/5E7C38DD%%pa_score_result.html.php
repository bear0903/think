<?php /* Smarty version 2.6.11, created on 2016-03-09 14:11:38
         compiled from pa_score_result.html */ ?>

</head>
<body class="page-container">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'block_box_header.html', 'smarty_include_vars' => array('title' => ($this->_tpl_vars['BLOCK_TITLE']),'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'block_pa_period_where.html', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'block_box_footer.html', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'block_box_header.html', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<table class="bordertable">
		<tr>
			<th width="15%"><?php echo $this->_tpl_vars['YEAR_LABEL']; ?>
</th>
			<th width="70%"><?php echo $this->_tpl_vars['PA_PERIOD_LABEL']; ?>
</th>
			<th	width="10%"><?php echo $this->_tpl_vars['RANK_LABEL']; ?>
</th>
		</tr>
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['pa_score_result']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
        	<td><?php echo $this->_tpl_vars['pa_score_result'][$this->_sections['i']['index']]['PA_YEAR']; ?>
</td>
        	<td>
        		<?php echo $this->_tpl_vars['pa_score_result'][$this->_sections['i']['index']]['PA_PERIOD_ID']; ?>

        	    <?php echo $this->_tpl_vars['pa_score_result'][$this->_sections['i']['index']]['PA_PERIOD_DESC']; ?>
</td>
        	<td><?php echo $this->_tpl_vars['pa_score_result'][$this->_sections['i']['index']]['PA_SCORE']; ?>
/
        		<?php echo $this->_tpl_vars['pa_score_result'][$this->_sections['i']['index']]['PA_RANK']; ?>
</td>

        </tr>
        <?php endfor; else: ?>
	    <tr>
	        <td colspan="3">
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_info.html", 'smarty_include_vars' => array('msg_txt' => $this->_tpl_vars['NO_DATA_FOUND_MSG'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</td>
	    </tr>
	    <?php endif; ?>
	</table>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'block_box_footer.html', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>