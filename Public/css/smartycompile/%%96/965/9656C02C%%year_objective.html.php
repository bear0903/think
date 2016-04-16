<?php /* Smarty version 2.6.11, created on 2016-03-09 14:09:12
         compiled from year_objective.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'nl2br', 'year_objective.html', 9, false),)), $this); ?>

</head>
<body class="page-container">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['BLOCK_TITLE'],'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<table class="bordertable">
	<tr>
		<td>
			<?php if ($this->_tpl_vars['year_goal']): ?>
				<pre><?php echo ((is_array($_tmp=$this->_tpl_vars['year_goal'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</pre>
			<?php else: ?>
	              <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_info.html", 'smarty_include_vars' => array('msg_txt' => $this->_tpl_vars['NO_YEAR_OBJECTIVE_MSG'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			<?php endif; ?>
		</td>
	</tr>
</table>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script type="text/javascript">
    $(document).attr('title','<?php echo $this->_tpl_vars['BLOCK_TITLE']; ?>
');
</script>