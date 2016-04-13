<?php /* Smarty version 2.6.11, created on 2016-03-03 16:42:58
         compiled from salary_home.html */ ?>

</head>
<body class="page-container">
	<?php if ($this->_tpl_vars['is_salary_slip_grant'] == 'Y'): ?>
	<div class="span-9">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['SALARY_SLIP_LABEL'],'showLine' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<a
			href="?scriptname=ESNC006&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['SALARY_SLIP_LABEL']; ?>
">
			<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/money_salary_slip.png" alt="" />
		</a>
		<?php echo $this->_tpl_vars['SALARY_SLIP_TEXT']; ?>

		<div align="right">
			<a
				href="?scriptname=ESNC006&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['SALARY_SLIP_LABEL']; ?>
">
				<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/view.gif" alt="" />
			</a>
		</div>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['is_ss_pay_grant'] == 'Y'): ?>
	<div class="span-9">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['SS_PAY_LABEL'],'showLine' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<a
			href="?scriptname=ESNC002&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['SS_PAY_LABEL']; ?>
">
			<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/money_ss.png" alt="" />
		</a>
		<?php echo $this->_tpl_vars['SS_PAY_TEXT']; ?>

		<div align="right">
			<a
				href="?scriptname=ESNC002&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['SS_PAY_LABEL']; ?>
">
				<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/view.gif" alt="" />
			</a>
		</div>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['is_personal_tax_grant'] == 'Y'): ?>
	<div class="span-9">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['PERSONAL_TAX_LABEL'],'showLine' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<a
			href="?scriptname=ESNC003&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['PERSONAL_TAX_LABEL']; ?>
">
			<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/money_tax.png" alt="" />
		</a>
		<?php echo $this->_tpl_vars['PERSONAL_INCOME_TAX_TEXT']; ?>

		<div align="right">
			<a
				href="?scriptname=ESNC003&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['PERSONAL_TAX_LABEL']; ?>
">
				<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/view.gif" alt="" />
			</a>
		</div>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['is_bonus_grant'] == 'Y'): ?>
	<div class="span-9">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['BONUS_LABEL'],'showLine' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<a
			href="?scriptname=ESNC005&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['PERSONAL_TAX_LABEL']; ?>
">
			<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/money_bonus.png" alt="" />
		</a>
		<?php echo $this->_tpl_vars['BONUS_TEXT']; ?>

		<div align="right">
			<a
				href="?scriptname=ESNC005&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['PERSONAL_TAX_LABEL']; ?>
">
				<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/view.gif" alt="" />
			</a>
		</div>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['is_sal_adjust_grant'] == 'Y'): ?>
	<div class="span-9">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['SALARY_ADJ_HIST_LABEL'],'showLine' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<a
			href="?scriptname=ESNC001&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['SALARY_ADJ_HIST_LABEL']; ?>
">
			<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/money_adj_his.png" alt="" />
		</a>
		<?php echo $this->_tpl_vars['SALARY_ADJUST_TEXT']; ?>

		<div align="right">
			<a
				href="?scriptname=ESNC001&fromscript=<?php echo $_GET['scriptname']; ?>
&appdesc=<?php echo $this->_tpl_vars['SALARY_ADJ_HIST_LABEL']; ?>
">
				<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/view.gif" alt="" />
			</a>
		</div>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<?php endif; ?>