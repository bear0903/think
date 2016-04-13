<?php /* Smarty version 2.6.11, created on 2016-02-22 15:31:05
         compiled from home.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'home.html', 30, false),)), $this); ?>

</head>
<body class="page-container">
	<!-- 公告通知合并在一个 table | by dennis 2014/02/07 -->
	<?php if (count ( $this->_tpl_vars['personal_news_list'] ) > 0 || count ( $this->_tpl_vars['company_news_list'] ) > 0): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_personal_news_list.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>
	<div class="span-10">
		<?php echo $this->_tpl_vars['calendar']; ?>

		<table class="bordertable">
			<tr>
				<td><label
					style="background: #93FF93; border-style: solid; border-width: 1px; width: 20px; height: 10px; line-height: 18px; margin: 10px; padding: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;</label>
				</td>
				<td><label
					style="background: #fff; border-style: solid; border-width: 1px; width: 20px; height: 10px; line-height: 18px; margin: 10px; padding: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;</label>
				</td>
				<td><label
					style="background: #FFD6EB; border-style: solid; border-width: 1px; width: 20px; height: 10px; line-height: 18px; margin: 10px; padding: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;</label>
				</td>
				<td><label
					style="background: #C6C3C6; border-style: solid; border-width: 1px; width: 20px; height: 10px; line-height: 18px; margin: 10px; padding: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;</label>
				</td>
				<td><label
					style="background: #C641C6; border-style: solid; border-width: 1px; width: 20px; height: 10px; line-height: 18px; margin: 10px; padding: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;</label>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo ((is_array($_tmp=@$this->_tpl_vars['WEEKEND_DAY_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '休息日') : smarty_modifier_default($_tmp, '休息日')); ?>

				</td>
				<td>
					<?php echo ((is_array($_tmp=@$this->_tpl_vars['WORKDAY_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '工作日') : smarty_modifier_default($_tmp, '工作日')); ?>

				</td>
				<td>
					<?php echo ((is_array($_tmp=@$this->_tpl_vars['CURRENT_DAY_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '今天') : smarty_modifier_default($_tmp, '今天')); ?>

				</td>
				<td>
					<?php echo ((is_array($_tmp=@$this->_tpl_vars['ARRANGE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '未排班') : smarty_modifier_default($_tmp, '未排班')); ?>

				</td>
				<td>
					<?php echo ((is_array($_tmp=@$this->_tpl_vars['NATION_DAY_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '国定假日') : smarty_modifier_default($_tmp, '国定假日')); ?>

				</td>
			</tr>
		</table>
	</div>
	<?php if (count ( $this->_tpl_vars['pa_period_list'] ) > 0 || count ( $this->_tpl_vars['pa_forms_list'] ) > 0 || count ( $this->_tpl_vars['pa_goal_list'] ) > 0 || count ( $this->_tpl_vars['pa_goal_edit_list'] ) > 0 || count ( $this->_tpl_vars['pa_goal_emp_list'] ) > 0): ?>
	<div class="span-9">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "pa_period_list.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<?php endif; ?>

	<?php if (count ( $this->_tpl_vars['user_define_wf_list'] ) > 0 || $this->_tpl_vars['leave_apply_count'] > 0 || $this->_tpl_vars['cancel_leave_apply_count'] > 0 || $this->_tpl_vars['overtime_apply_count'] > 0 || $this->_tpl_vars['trans_apply_count'] > 0 || $this->_tpl_vars['nocard_apply_count'] > 0 || $this->_tpl_vars['resign_apply_count'] > 0): ?>
	<div class="span-9">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_task_list.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<?php endif; ?>
	    <!--<div class="span-9">-->
		<!--  </div> -->
	
	<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/functions.js" type="text/javascript"></script>
	<script type="text/javascript">
		$().ready(function(){
			attachClickEvent();
			
			$('#quick_approve').click(function(){
				$.ajax({
					type:'post',
					url:'?scriptname=quickApprove&approveType=OT',
					success: function(data){},
					dataType: 'json'
				});
			});
			
		});
	</script>