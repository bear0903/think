<?php /* Smarty version 2.6.11, created on 2016-03-03 16:43:11
         compiled from salary_slip_new.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'salary_slip_new.html', 26, false),array('modifier', 'number_format', 'salary_slip_new.html', 28, false),)), $this); ?>

</head>
 <script>
	$(function() {
		$( "#tabs").tabs({
			collapsible: true
		}).tabs({selected: <?php if (( $_GET['year'] == ( $this->_tpl_vars['year']-1 ) )): ?>1<?php else: ?>0<?php endif; ?>});
	});
</script>
<body class="page-container">
<h4 class="ui-widget-header ui-corner-all" id="panel_title" style="padding:5px;">
<?php echo $_GET['appdesc']; ?>

</h4>
<div id="tabs">
	<ul>
		<li><a href="#tabs-<?php echo $this->_tpl_vars['year']; ?>
"><?php echo $this->_tpl_vars['year']; ?>
</a></li>
		<?php if (count ( $this->_tpl_vars['pyear_sal_list'] ) > 0): ?>
		<li><a href="#tabs-<?php echo $this->_tpl_vars['year']-1; ?>
"><?php echo $this->_tpl_vars['year']-1; ?>
</a></li>
		<?php endif; ?>
	</ul>
	<div id="tabs-<?php echo $this->_tpl_vars['year']; ?>
">
		<table class="bordertable">
			<tr>
			<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['year_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
				<th width="12%" <?php if ($this->_tpl_vars['year_sal_list'][$this->_sections['i']['index']]['DETAIL_ID'] == $this->_tpl_vars['detail_id']): ?> class="ui-state-highlight" <?php endif; ?>>
				<?php echo ((is_array($_tmp=$this->_tpl_vars['year_sal_list'][$this->_sections['i']['index']]['YEAR_MON'])) ? $this->_run_mod_handler('substr', true, $_tmp, 5, 2) : substr($_tmp, 5, 2)); ?>
</th>
				<td width="13%" <?php if ($this->_tpl_vars['year_sal_list'][$this->_sections['i']['index']]['DETAIL_ID'] == $this->_tpl_vars['detail_id']): ?> class="ui-state-highlight" <?php endif; ?>>
					<a href="../ess/redirect.php?scriptname=salary_slip_new&empseqno=<?php echo $_GET['empseqno']; ?>
&salary_result_id=<?php echo $this->_tpl_vars['year_sal_list'][$this->_sections['i']['index']]['SAL_RESULT_ID']; ?>
&master_id=<?php echo $this->_tpl_vars['year_sal_list'][$this->_sections['i']['index']]['MASTER_ID']; ?>
&detail_id=<?php echo $this->_tpl_vars['year_sal_list'][$this->_sections['i']['index']]['DETAIL_ID']; ?>
&year=<?php echo ((is_array($_tmp=$this->_tpl_vars['year_sal_list'][$this->_sections['i']['index']]['YEAR_MON'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 4) : substr($_tmp, 0, 4)); ?>
&appdesc=<?php echo $_GET['appdesc']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['year_sal_list'][$this->_sections['i']['index']]['AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</a>
				</td>
			<?php if (in_array ( $this->_sections['i']['rownum'] , array ( 4 , 8 ) )): ?></tr><tr><?php endif; ?>
			<?php endfor; endif; ?>
			</tr>
		</table>
	</div>
	<?php if (count ( $this->_tpl_vars['pyear_sal_list'] ) > 0): ?>
	<div id="tabs-<?php echo $this->_tpl_vars['year']-1; ?>
">
		<table class="bordertable">
			<tr>
			<?php unset($this->_sections['r']);
$this->_sections['r']['name'] = 'r';
$this->_sections['r']['loop'] = is_array($_loop=$this->_tpl_vars['pyear_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['r']['show'] = true;
$this->_sections['r']['max'] = $this->_sections['r']['loop'];
$this->_sections['r']['step'] = 1;
$this->_sections['r']['start'] = $this->_sections['r']['step'] > 0 ? 0 : $this->_sections['r']['loop']-1;
if ($this->_sections['r']['show']) {
    $this->_sections['r']['total'] = $this->_sections['r']['loop'];
    if ($this->_sections['r']['total'] == 0)
        $this->_sections['r']['show'] = false;
} else
    $this->_sections['r']['total'] = 0;
if ($this->_sections['r']['show']):

            for ($this->_sections['r']['index'] = $this->_sections['r']['start'], $this->_sections['r']['iteration'] = 1;
                 $this->_sections['r']['iteration'] <= $this->_sections['r']['total'];
                 $this->_sections['r']['index'] += $this->_sections['r']['step'], $this->_sections['r']['iteration']++):
$this->_sections['r']['rownum'] = $this->_sections['r']['iteration'];
$this->_sections['r']['index_prev'] = $this->_sections['r']['index'] - $this->_sections['r']['step'];
$this->_sections['r']['index_next'] = $this->_sections['r']['index'] + $this->_sections['r']['step'];
$this->_sections['r']['first']      = ($this->_sections['r']['iteration'] == 1);
$this->_sections['r']['last']       = ($this->_sections['r']['iteration'] == $this->_sections['r']['total']);
?>
				<th width="12%" <?php if ($this->_tpl_vars['pyear_sal_list'][$this->_sections['r']['index']]['DETAIL_ID'] == $this->_tpl_vars['detail_id']): ?> class="ui-state-highlight" <?php endif; ?>>
				<?php echo ((is_array($_tmp=$this->_tpl_vars['pyear_sal_list'][$this->_sections['r']['index']]['YEAR_MON'])) ? $this->_run_mod_handler('substr', true, $_tmp, 5, 2) : substr($_tmp, 5, 2)); ?>
</th>
				<td width="13%" <?php if ($this->_tpl_vars['pyear_sal_list'][$this->_sections['r']['index']]['DETAIL_ID'] == $this->_tpl_vars['detail_id']): ?> class="ui-state-highlight" <?php endif; ?>>
					<a href="?scriptname=salary_slip_new&empseqno=<?php echo $_GET['empseqno']; ?>
&salary_result_id=<?php echo $this->_tpl_vars['pyear_sal_list'][$this->_sections['r']['index']]['SAL_RESULT_ID']; ?>
&master_id=<?php echo $this->_tpl_vars['pyear_sal_list'][$this->_sections['r']['index']]['MASTER_ID']; ?>
&detail_id=<?php echo $this->_tpl_vars['pyear_sal_list'][$this->_sections['r']['index']]['DETAIL_ID']; ?>
&year=<?php echo ((is_array($_tmp=$this->_tpl_vars['pyear_sal_list'][$this->_sections['r']['index']]['YEAR_MON'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 4) : substr($_tmp, 0, 4)); ?>
&appdesc=<?php echo $_GET['appdesc']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['pyear_sal_list'][$this->_sections['r']['index']]['AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</a>
				</td>
			<?php if (in_array ( $this->_sections['r']['rownum'] , array ( 4 , 8 ) )): ?></tr><tr><?php endif; ?>
			<?php endfor; endif; ?>
			</tr>
		</table>
	</div>
	<?php endif; ?>
</div>
<div style="font-size:18px;text-align:center;"><strong><?php echo $this->_tpl_vars['MON']; ?>
月薪资单</strong></div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<table class="bordertable" style="background:#f5f6f6;">
	<tr>
		<th width="12%">应发薪资</th><td width="13%"><?php echo ((is_array($_tmp=$this->_tpl_vars['yingfa_amount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</td>
		<th width="12%">个税</th><td width="13%"><?php echo ((is_array($_tmp=$this->_tpl_vars['PSN_TAX'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</td>
		<?php if ($this->_tpl_vars['SPEC_BONUS_TAX'] != 0): ?>
		<th width="12%">年终奖税</th><td width="13%"><?php echo ((is_array($_tmp=$this->_tpl_vars['SPEC_BONUS_TAX'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</td>
		<?php endif; ?>
		<th width="12%">实发薪资</th><td width="13%"><?php echo ((is_array($_tmp=$this->_tpl_vars['FACT_AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</td>
	</tr>
</table>
<div style="padding:10px; margin-bottom:10px;" class="ui-widget-content ui-corner-all">
	<div class="ui-panel">
		<h4 class="ui-widget-header ui-corner-all" id="panel_title" style="padding:5px;">
			固定薪资
		</h4>
		<table class="bordertable">
			<tr>
			<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['fix_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['show'] = true;
$this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = $this->_sections['j']['loop'];
    if ($this->_sections['j']['total'] == 0)
        $this->_sections['j']['show'] = false;
} else
    $this->_sections['j']['total'] = 0;
if ($this->_sections['j']['show']):

            for ($this->_sections['j']['index'] = $this->_sections['j']['start'], $this->_sections['j']['iteration'] = 1;
                 $this->_sections['j']['iteration'] <= $this->_sections['j']['total'];
                 $this->_sections['j']['index'] += $this->_sections['j']['step'], $this->_sections['j']['iteration']++):
$this->_sections['j']['rownum'] = $this->_sections['j']['iteration'];
$this->_sections['j']['index_prev'] = $this->_sections['j']['index'] - $this->_sections['j']['step'];
$this->_sections['j']['index_next'] = $this->_sections['j']['index'] + $this->_sections['j']['step'];
$this->_sections['j']['first']      = ($this->_sections['j']['iteration'] == 1);
$this->_sections['j']['last']       = ($this->_sections['j']['iteration'] == $this->_sections['j']['total']);
?>
				<th width="12%"><?php echo $this->_tpl_vars['fix_sal_list'][$this->_sections['j']['index']]['ITEM_DESC']; ?>
</th>
				<td width="13%">
					<?php echo ((is_array($_tmp=$this->_tpl_vars['fix_sal_list'][$this->_sections['j']['index']]['AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
			<?php if (in_array ( $this->_sections['j']['rownum'] , array ( 3 , 6 , 9 , 12 , 15 , 18 ) )): ?></tr><tr><?php endif; ?>
			<?php endfor; endif; ?>
			</tr>
		</table>
		<div style="text-align:right;margin-top:-10px;font-size:16px;"><strong>小计: </strong><?php echo ((is_array($_tmp=$this->_tpl_vars['fix_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</div>
	</div>
	<hr size="1"/>
	<?php if (count ( $this->_tpl_vars['tmp_sal_list'] ) > 0): ?>
	<div class="ui-panel">
		<h4 class="ui-widget-header ui-corner-all" id="panel_title" style="padding:5px;">
			临时薪资
		</h4>
		<table class="bordertable">
			<tr>
			<?php unset($this->_sections['r']);
$this->_sections['r']['name'] = 'r';
$this->_sections['r']['loop'] = is_array($_loop=$this->_tpl_vars['tmp_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['r']['show'] = true;
$this->_sections['r']['max'] = $this->_sections['r']['loop'];
$this->_sections['r']['step'] = 1;
$this->_sections['r']['start'] = $this->_sections['r']['step'] > 0 ? 0 : $this->_sections['r']['loop']-1;
if ($this->_sections['r']['show']) {
    $this->_sections['r']['total'] = $this->_sections['r']['loop'];
    if ($this->_sections['r']['total'] == 0)
        $this->_sections['r']['show'] = false;
} else
    $this->_sections['r']['total'] = 0;
if ($this->_sections['r']['show']):

            for ($this->_sections['r']['index'] = $this->_sections['r']['start'], $this->_sections['r']['iteration'] = 1;
                 $this->_sections['r']['iteration'] <= $this->_sections['r']['total'];
                 $this->_sections['r']['index'] += $this->_sections['r']['step'], $this->_sections['r']['iteration']++):
$this->_sections['r']['rownum'] = $this->_sections['r']['iteration'];
$this->_sections['r']['index_prev'] = $this->_sections['r']['index'] - $this->_sections['r']['step'];
$this->_sections['r']['index_next'] = $this->_sections['r']['index'] + $this->_sections['r']['step'];
$this->_sections['r']['first']      = ($this->_sections['r']['iteration'] == 1);
$this->_sections['r']['last']       = ($this->_sections['r']['iteration'] == $this->_sections['r']['total']);
?>
				<th width="12%"><?php echo $this->_tpl_vars['tmp_sal_list'][$this->_sections['r']['index']]['SAL_ITEM_DESC'];  if ($this->_tpl_vars['tmp_sal_list'][$this->_sections['r']['index']]['IS_TAX_ITEM'] == 'Y'): ?><font color="red">(应税)</font><?php endif; ?></th>
				<td width="13%">
					<?php echo ((is_array($_tmp=$this->_tpl_vars['tmp_sal_list'][$this->_sections['r']['index']]['AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
				<?php if (in_array ( $this->_sections['r']['rownum'] , array ( 3 , 6 , 9 , 12 , 15 , 18 ) )): ?></tr><tr><?php endif; ?>
			<?php endfor; endif; ?>
			</tr>
		</table>
		<div style="text-align:right;margin-top:-10px;font-size:16px;">
			<?php if ($this->_tpl_vars['tmp_tax_subtotal'] != 0): ?><strong>应税小计: </strong><?php echo ((is_array($_tmp=$this->_tpl_vars['tmp_tax_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%'));  endif;  if ($this->_tpl_vars['tmp_notax_subtotal'] != 0): ?><br/><strong>不应税小计: </strong><?php echo ((is_array($_tmp=$this->_tpl_vars['tmp_notax_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%'));  endif; ?>
		</div>
	</div>
	<hr size="1"/>
	<?php endif; ?>
	
	<?php if (count ( $this->_tpl_vars['ot_sal_list'] ) > 0): ?>
	<div class="ui-panel">
		<h4 class="ui-widget-header ui-corner-all" id="panel_title" style="padding:5px;">
			加班费
		</h4>
		<table class="bordertable">
			<tr>
				<th>加班类型</th>
				<th>时数</th>
				<th>加班费</th>
			</tr>
			<tr>
			<?php unset($this->_sections['k']);
$this->_sections['k']['name'] = 'k';
$this->_sections['k']['loop'] = is_array($_loop=$this->_tpl_vars['ot_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['k']['show'] = true;
$this->_sections['k']['max'] = $this->_sections['k']['loop'];
$this->_sections['k']['step'] = 1;
$this->_sections['k']['start'] = $this->_sections['k']['step'] > 0 ? 0 : $this->_sections['k']['loop']-1;
if ($this->_sections['k']['show']) {
    $this->_sections['k']['total'] = $this->_sections['k']['loop'];
    if ($this->_sections['k']['total'] == 0)
        $this->_sections['k']['show'] = false;
} else
    $this->_sections['k']['total'] = 0;
if ($this->_sections['k']['show']):

            for ($this->_sections['k']['index'] = $this->_sections['k']['start'], $this->_sections['k']['iteration'] = 1;
                 $this->_sections['k']['iteration'] <= $this->_sections['k']['total'];
                 $this->_sections['k']['index'] += $this->_sections['k']['step'], $this->_sections['k']['iteration']++):
$this->_sections['k']['rownum'] = $this->_sections['k']['iteration'];
$this->_sections['k']['index_prev'] = $this->_sections['k']['index'] - $this->_sections['k']['step'];
$this->_sections['k']['index_next'] = $this->_sections['k']['index'] + $this->_sections['k']['step'];
$this->_sections['k']['first']      = ($this->_sections['k']['iteration'] == 1);
$this->_sections['k']['last']       = ($this->_sections['k']['iteration'] == $this->_sections['k']['total']);
?>
				<th width="20%"><?php echo $this->_tpl_vars['ot_sal_list'][$this->_sections['k']['index']]['OT_TYPE_DESC']; ?>
</th>
				<td width="40"><?php echo ((is_array($_tmp=$this->_tpl_vars['ot_sal_list'][$this->_sections['k']['index']]['HOURS'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</td>
				<td width="40%">
					<?php echo ((is_array($_tmp=$this->_tpl_vars['ot_sal_list'][$this->_sections['k']['index']]['AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
				<?php if (in_array ( $this->_sections['k']['rownum'] , array ( 1 , 2 , 3 ) )): ?></tr><tr><?php endif; ?>
			<?php endfor; endif; ?>
			</tr>
		</table>
		<div style="text-align:right;margin-top:-10px;font-size:16px;"><strong>小计: </strong><?php echo ((is_array($_tmp=$this->_tpl_vars['ot_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</div>
	</div>
	<hr size="1"/>
	<?php endif; ?>
	<?php if (count ( $this->_tpl_vars['abs_sal_list'] ) > 0): ?>
	<div class="ui-panel">
		<h4 class="ui-widget-header ui-corner-all" id="panel_title" style="padding:5px;">
			请假扣款
		</h4>
		<table class="bordertable">
			<tr>
			<?php unset($this->_sections['m']);
$this->_sections['m']['name'] = 'm';
$this->_sections['m']['loop'] = is_array($_loop=$this->_tpl_vars['abs_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['m']['show'] = true;
$this->_sections['m']['max'] = $this->_sections['m']['loop'];
$this->_sections['m']['step'] = 1;
$this->_sections['m']['start'] = $this->_sections['m']['step'] > 0 ? 0 : $this->_sections['m']['loop']-1;
if ($this->_sections['m']['show']) {
    $this->_sections['m']['total'] = $this->_sections['m']['loop'];
    if ($this->_sections['m']['total'] == 0)
        $this->_sections['m']['show'] = false;
} else
    $this->_sections['m']['total'] = 0;
if ($this->_sections['m']['show']):

            for ($this->_sections['m']['index'] = $this->_sections['m']['start'], $this->_sections['m']['iteration'] = 1;
                 $this->_sections['m']['iteration'] <= $this->_sections['m']['total'];
                 $this->_sections['m']['index'] += $this->_sections['m']['step'], $this->_sections['m']['iteration']++):
$this->_sections['m']['rownum'] = $this->_sections['m']['iteration'];
$this->_sections['m']['index_prev'] = $this->_sections['m']['index'] - $this->_sections['m']['step'];
$this->_sections['m']['index_next'] = $this->_sections['m']['index'] + $this->_sections['m']['step'];
$this->_sections['m']['first']      = ($this->_sections['m']['iteration'] == 1);
$this->_sections['m']['last']       = ($this->_sections['m']['iteration'] == $this->_sections['m']['total']);
?>
				<th width="12%"><?php echo $this->_tpl_vars['abs_sal_list'][$this->_sections['m']['index']]['ABSENCE_NAME']; ?>
</th>
				<td width="13%">
					<?php echo ((is_array($_tmp=$this->_tpl_vars['abs_sal_list'][$this->_sections['m']['index']]['AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
				<?php if (in_array ( $this->_sections['m']['rownum'] , array ( 3 , 6 , 9 , 12 , 15 , 18 ) )): ?></tr><tr><?php endif; ?>
			<?php endfor; endif; ?>
			</tr>
		</table>
		<div style="text-align:right;margin-top:-10px;font-size:16px;"><strong>小计: </strong><?php echo ((is_array($_tmp=$this->_tpl_vars['abs_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</div>
	</div>
	<hr size="1"/>
	<?php endif; ?>
	<?php if (count ( $this->_tpl_vars['bonus_sal_list'] ) > 0 && $this->_tpl_vars['bonus_subtotal'] != 0): ?>
	<div class="ui-panel">
		<h4 class="ui-widget-header ui-corner-all" id="panel_title" style="padding:5px;">
			奖金(同工资一同发放)
		</h4>
		<table class="bordertable">
			<tr>
			<?php unset($this->_sections['n']);
$this->_sections['n']['name'] = 'n';
$this->_sections['n']['loop'] = is_array($_loop=$this->_tpl_vars['bonus_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['n']['show'] = true;
$this->_sections['n']['max'] = $this->_sections['n']['loop'];
$this->_sections['n']['step'] = 1;
$this->_sections['n']['start'] = $this->_sections['n']['step'] > 0 ? 0 : $this->_sections['n']['loop']-1;
if ($this->_sections['n']['show']) {
    $this->_sections['n']['total'] = $this->_sections['n']['loop'];
    if ($this->_sections['n']['total'] == 0)
        $this->_sections['n']['show'] = false;
} else
    $this->_sections['n']['total'] = 0;
if ($this->_sections['n']['show']):

            for ($this->_sections['n']['index'] = $this->_sections['n']['start'], $this->_sections['n']['iteration'] = 1;
                 $this->_sections['n']['iteration'] <= $this->_sections['n']['total'];
                 $this->_sections['n']['index'] += $this->_sections['n']['step'], $this->_sections['n']['iteration']++):
$this->_sections['n']['rownum'] = $this->_sections['n']['iteration'];
$this->_sections['n']['index_prev'] = $this->_sections['n']['index'] - $this->_sections['n']['step'];
$this->_sections['n']['index_next'] = $this->_sections['n']['index'] + $this->_sections['n']['step'];
$this->_sections['n']['first']      = ($this->_sections['n']['iteration'] == 1);
$this->_sections['n']['last']       = ($this->_sections['n']['iteration'] == $this->_sections['n']['total']);
?>
				<th width="12%"><?php echo $this->_tpl_vars['bonus_sal_list'][$this->_sections['n']['index']]['BONUS_DESC']; ?>
</th>
				<td width="13%">
					<?php echo ((is_array($_tmp=$this->_tpl_vars['bonus_sal_list'][$this->_sections['n']['index']]['AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
			<?php endfor; endif; ?>
			</tr>
		</table>
		<div style="text-align:right;margin-top:-10px;font-size:16px;"><strong>小计: </strong><?php echo ((is_array($_tmp=$this->_tpl_vars['bonus_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
</div>
	</div>
	<hr size="1"/>
	<?php endif; ?>
	<?php if (count ( $this->_tpl_vars['ss_sal_list'] ) > 0): ?>
	<div class="ui-panel">
		<h4 class="ui-widget-header ui-corner-all" id="panel_title" style="padding:5px;">
			社保/公积金
		</h4>
		<table class="bordertable">
			<tr>
				<th width="20%">名称</th>
				<th width="40%">个人缴交</th>
				<th width="40%">公司缴交</th>
			
			<?php unset($this->_sections['p']);
$this->_sections['p']['name'] = 'p';
$this->_sections['p']['loop'] = is_array($_loop=$this->_tpl_vars['ss_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['p']['show'] = true;
$this->_sections['p']['max'] = $this->_sections['p']['loop'];
$this->_sections['p']['step'] = 1;
$this->_sections['p']['start'] = $this->_sections['p']['step'] > 0 ? 0 : $this->_sections['p']['loop']-1;
if ($this->_sections['p']['show']) {
    $this->_sections['p']['total'] = $this->_sections['p']['loop'];
    if ($this->_sections['p']['total'] == 0)
        $this->_sections['p']['show'] = false;
} else
    $this->_sections['p']['total'] = 0;
if ($this->_sections['p']['show']):

            for ($this->_sections['p']['index'] = $this->_sections['p']['start'], $this->_sections['p']['iteration'] = 1;
                 $this->_sections['p']['iteration'] <= $this->_sections['p']['total'];
                 $this->_sections['p']['index'] += $this->_sections['p']['step'], $this->_sections['p']['iteration']++):
$this->_sections['p']['rownum'] = $this->_sections['p']['iteration'];
$this->_sections['p']['index_prev'] = $this->_sections['p']['index'] - $this->_sections['p']['step'];
$this->_sections['p']['index_next'] = $this->_sections['p']['index'] + $this->_sections['p']['step'];
$this->_sections['p']['first']      = ($this->_sections['p']['iteration'] == 1);
$this->_sections['p']['last']       = ($this->_sections['p']['iteration'] == $this->_sections['p']['total']);
?>
			<tr>
				<td><?php echo $this->_tpl_vars['ss_sal_list'][$this->_sections['p']['index']]['SS_ITEM_DESC']; ?>
</td>
				<td>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['ss_sal_list'][$this->_sections['p']['index']]['EMP_PAY'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
				<td>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['ss_sal_list'][$this->_sections['p']['index']]['COMPANY_PAY'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
			</tr>
			<?php endfor; endif; ?>
		<?php if (count ( $this->_tpl_vars['bonus_sal_list'] ) > 0): ?>
			<?php unset($this->_sections['t']);
$this->_sections['t']['name'] = 't';
$this->_sections['t']['loop'] = is_array($_loop=$this->_tpl_vars['bonus_sal_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['t']['show'] = true;
$this->_sections['t']['max'] = $this->_sections['t']['loop'];
$this->_sections['t']['step'] = 1;
$this->_sections['t']['start'] = $this->_sections['t']['step'] > 0 ? 0 : $this->_sections['t']['loop']-1;
if ($this->_sections['t']['show']) {
    $this->_sections['t']['total'] = $this->_sections['t']['loop'];
    if ($this->_sections['t']['total'] == 0)
        $this->_sections['t']['show'] = false;
} else
    $this->_sections['t']['total'] = 0;
if ($this->_sections['t']['show']):

            for ($this->_sections['t']['index'] = $this->_sections['t']['start'], $this->_sections['t']['iteration'] = 1;
                 $this->_sections['t']['iteration'] <= $this->_sections['t']['total'];
                 $this->_sections['t']['index'] += $this->_sections['t']['step'], $this->_sections['t']['iteration']++):
$this->_sections['t']['rownum'] = $this->_sections['t']['iteration'];
$this->_sections['t']['index_prev'] = $this->_sections['t']['index'] - $this->_sections['t']['step'];
$this->_sections['t']['index_next'] = $this->_sections['t']['index'] + $this->_sections['t']['step'];
$this->_sections['t']['first']      = ($this->_sections['t']['iteration'] == 1);
$this->_sections['t']['last']       = ($this->_sections['t']['iteration'] == $this->_sections['t']['total']);
?>
			<tr>
				<td>年终奖社保</td>
				<td>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['bonus_sal_list'][$this->_sections['t']['index']]['EMP_BONUS_INSURE_AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
				<td>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['bonus_sal_list'][$this->_sections['t']['index']]['COMP_BONUS_INSURE_AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

				</td>
				<?php endfor; endif; ?>
			</tr>
		<?php endif; ?>
		</table>
		<div style="text-align:right;margin-top:-10px;font-size:16px;">
			<strong>个人社保(公积金)小计: </strong><?php echo ((is_array($_tmp=$this->_tpl_vars['psnpay_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>

		</div>
	</div>
	<hr size="1"/>
	<?php endif; ?>
</div>
<!-- 
实发薪资=应发薪资+个人社保（公积金）小计+年终奖金社保小计+个人所得税+年终奖金税+临时加减项（不应税）小计
应发薪资=固定薪资+临时加减项（应税）小计+加班费小计+请假扣款小计+年终奖金小计
 -->
<div style="margin-top:25px;margin-left:20px;"><strong>
	实发薪资(<?php echo ((is_array($_tmp=$this->_tpl_vars['FACT_AMOUNT'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
) = 
	应发薪资(<?php echo ((is_array($_tmp=$this->_tpl_vars['yingfa_amount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php if ($this->_tpl_vars['psnpay_subtotal'] != 0): ?>
	+个人社保(公积金)小计(<?php echo ((is_array($_tmp=$this->_tpl_vars['psnpay_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php endif; ?>
	<?php if ($this->_tpl_vars['SPEC_BONUS_TAX'] != 0): ?>
	+年终奖金税(<?php echo ((is_array($_tmp=$this->_tpl_vars['SPEC_BONUS_TAX'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php endif; ?>
	<?php if ($this->_tpl_vars['PSN_TAX'] != 0): ?>
	+个税(<?php echo ((is_array($_tmp=$this->_tpl_vars['PSN_TAX'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php endif; ?>
	<?php if ($this->_tpl_vars['tmp_notax_subtotal'] != 0): ?>
	＋临时薪资(不应税)小计(<?php echo ((is_array($_tmp=$this->_tpl_vars['tmp_notax_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php endif; ?>
	<hr>
	应发薪资(<?php echo ((is_array($_tmp=$this->_tpl_vars['yingfa_amount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
) = 固定薪资小计(<?php echo ((is_array($_tmp=$this->_tpl_vars['fix_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php if ($this->_tpl_vars['tmp_tax_subtotal'] != 0): ?>
	＋临时薪资(应税)小计(<?php echo ((is_array($_tmp=$this->_tpl_vars['tmp_tax_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php endif; ?>
	<?php if ($this->_tpl_vars['ot_subtotal'] != 0): ?>
	＋加班费小计(<?php echo ((is_array($_tmp=$this->_tpl_vars['ot_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php endif; ?>
	<?php if ($this->_tpl_vars['abs_subtotal'] != 0): ?>
	+请假扣款小计(<?php echo ((is_array($_tmp=$this->_tpl_vars['abs_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php endif; ?>
	<?php if ($this->_tpl_vars['bonus_subtotal'] != 0): ?>
	＋年终奖金小计(<?php echo ((is_array($_tmp=$this->_tpl_vars['bonus_subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, '2%') : number_format($_tmp, '2%')); ?>
)
	<?php endif; ?>
</strong></div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>