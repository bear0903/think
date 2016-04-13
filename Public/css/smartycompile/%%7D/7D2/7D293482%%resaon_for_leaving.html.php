<?php /* Smarty version 2.6.11, created on 2016-03-18 10:26:54
         compiled from resaon_for_leaving.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'resaon_for_leaving.html', 27, false),)), $this); ?>

<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/functions.js" language = "JavaScript" type = "text/javascript" charset = "utf-8"></script>
<script type="text/javaScript">
	$().ready(function(){
		$('#leaving_date').datepicker({dateFormat:'yy-mm-dd',changeMonth:true,changeYear:true});
	});
	function compareDate(DateOne,DateTwo)  
	{
		var OneMonth = DateOne.substring(5,DateOne.lastIndexOf ("-"));
		var OneDay = DateOne.substring(DateOne.length,DateOne.lastIndexOf ("-")+1);
		var OneYear = DateOne.substring(0,DateOne.indexOf ("-"));
		var TwoMonth = DateTwo.substring(5,DateTwo.lastIndexOf ("-"));
		var TwoDay = DateTwo.substring(DateTwo.length,DateTwo.lastIndexOf ("-")+1);
		var TwoYear = DateTwo.substring(0,DateTwo.indexOf ("-"));
		
		if (Date.parse(OneMonth+"/"+OneDay+"/"+OneYear) >= Date.parse(TwoMonth+"/"+TwoDay+"/"+TwoYear))
		{
			return true;
		}else{
			return false;
		}
	}
	
	function checkRequired()
	{
		if(checkUserInputData('form1') == false) return false;
		if (!compareDate($("#leaving_date")[0].value, '<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
'))
		{
			//alert('預估離職日期不能小於當天.');
			alert("<?php echo $this->_tpl_vars['DATE_LESS_ERROR_MSG']; ?>
");
			return false;
		}
		if (!Date.isValid($("#leaving_date")[0].value,"yyyy-MM-dd"))
		{
			$("#leaving_date")[0].focus();
			alert("<?php echo $this->_tpl_vars['DATE_FORMAT_ERROR_MSG']; ?>
");
			return false;
		}// end if
		if ($("#other_type")[0].checked && $("#other_type_desc")[0].value == '' )
		{
			alert("<?php echo $this->_tpl_vars['INPUT_LEAVING_TYPE_MSG']; ?>
");
			$("#other_type_desc")[0].focus();
			return false;
		}// end if
		if ($('input[@type=checkbox][@checked]').length <3)
		{
			alert("<?php echo $this->_tpl_vars['LEST_CHOOSE_MSG']; ?>
");
			return false;
		}// end if
		if (confirm('<?php echo $this->_tpl_vars['CONFIRM_MSG']; ?>
'))
		{
			return true;
		}else{
			return false;
		}
		return true;
	}// end checkRequired()

	function checkOtherType(leavetype)
	{
		if(leavetype == '0')
		{
			$("#other_type_desc").get(0).disabled = false;
		}else{
			$("#other_type_desc")[0].value = '';
			$("#other_type_desc").get(0).disabled = true;
		}// end if
	}// end checkLestChecked()
</script>
</head>
<body class="page-container">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('showLine' => 1,'title' => $this->_tpl_vars['BLOCK_TITLE'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<!-- 
     得知您將離開本公司,我們深感遺憾;但仍感謝您在本公司工作期間付出的努力,也感謝您對公司作出的貢獻!
    在您離開本公司之前,為能了解您真實的離職原因,也為公司改善留任人才提供意見;我們將佔用您幾分鐘的時間.
    請您本著誠信的原則認真填寫下面的表格. 感謝您的配合!
-->
<?php echo $this->_tpl_vars['EXPLAIN_TXT01_MSG']; ?>
;
<?php echo $this->_tpl_vars['EXPLAIN_TXT02_MSG']; ?>
,
<?php echo $this->_tpl_vars['EXPLAIN_TXT03_MSG']; ?>
!
<?php echo $this->_tpl_vars['EXPLAIN_TXT04_MSG']; ?>
,
<?php echo $this->_tpl_vars['EXPLAIN_TXT05_MSG']; ?>
.
<?php echo $this->_tpl_vars['EXPLAIN_TXT06_MSG']; ?>
.
<?php echo $this->_tpl_vars['EXPLAIN_TXT07_MSG']; ?>
!
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<form name="form1" id="form1" method="post" onsubmit="return checkRequired();">
<input type="hidden" name="master_seqno" value="<?php echo $this->_tpl_vars['SEQNO']; ?>
"/>
<table class="bordertable">
	<tr>
		<td class="column-label" width="25%"><?php echo $this->_tpl_vars['LEAVING_DATE_LABEL']; ?>
(*)</td>
		<td>
			<?php if ($this->_tpl_vars['FORM_STATUS'] != 1): ?>
			<input type="text" 
				   name="leaving_date" 
				   id="leaving_date"
				   isDate="Y" 
				   required="Y" 
				   title="<?php echo $this->_tpl_vars['PLS_INPUT_LABEL'];  echo $this->_tpl_vars['LEAVING_DATE_LABEL']; ?>
  Date Format:YYYY-MM-DD"
				   value="<?php echo $this->_tpl_vars['LEAVING_DATE']; ?>
" 
				   class="input-date"
				   style="width:83px;"/>
            <?php else: ?>
            <?php echo $this->_tpl_vars['LEAVING_DATE']; ?>

            <?php endif; ?>
		</td>
	</tr>
	<tr>
		<td class="column-label"><?php echo $this->_tpl_vars['AFTER_CON_ADDR_LABEL']; ?>
(*)</td>
		<td>
			<?php if ($this->_tpl_vars['FORM_STATUS'] != 1): ?>
			<textarea required="Y" 
					  name="after_leaving_addr"
					   title="<?php echo $this->_tpl_vars['PLS_INPUT_LABEL'];  echo $this->_tpl_vars['AFTER_CON_ADDR_LABEL']; ?>
"><?php echo $this->_tpl_vars['AFTER_CON_ADDR']; ?>
</textarea>
			<?php else: ?>
            <?php echo $this->_tpl_vars['AFTER_CON_ADDR']; ?>

            <?php endif; ?>
		</td>
	</tr>
	<tr>
		<td class="column-label"><?php echo $this->_tpl_vars['AFTER_CON_TEL_LABEL']; ?>
(*)</td>
		<td>
			<?php if ($this->_tpl_vars['FORM_STATUS'] != 1): ?>
			<input type="text" 
			       name="after_leaving_tel" 
				   class="input-text" 
				   required="Y" 
				   value="<?php echo $this->_tpl_vars['AFTER_CON_TEL']; ?>
"
				   title="<?php echo $this->_tpl_vars['PLS_INPUT_LABEL'];  echo $this->_tpl_vars['AFTER_CON_TEL_LABEL']; ?>
"/>
			<?php else: ?>
            <?php echo $this->_tpl_vars['AFTER_CON_TEL']; ?>

            <?php endif; ?>
		</td>
	</tr>
	<tr>
		<td class="column-label"><?php echo $this->_tpl_vars['LEAVING_TYPE_LABEL']; ?>
(*)</td>
		<td>
			<input type="radio" 
				   name="leaving_type" 
				   id="self_apply" 
				   value="1"
				   <?php if ($this->_tpl_vars['LEAVING_TYPE'] == 1): ?>checked<?php endif; ?>
				   <?php if ($this->_tpl_vars['FORM_STATUS'] == 1): ?>disabled<?php endif; ?>
				   required="Y" 
				   title   ="<?php echo $this->_tpl_vars['PLS_INPUT_LABEL'];  echo $this->_tpl_vars['LEAVING_TYPE_LABEL']; ?>
"
				   onclick="checkOtherType(this.value);"/>
			<label for="self_apply"><?php echo $this->_tpl_vars['SELF_APPLY_LABEL']; ?>
</label>
			<input type="radio" 
				   name="leaving_type" 
				   id="out_of_line" 
				   value="2" 
				   <?php if ($this->_tpl_vars['LEAVING_TYPE'] == 2): ?>checked<?php endif; ?>
				   <?php if ($this->_tpl_vars['FORM_STATUS'] == 1): ?>disabled<?php endif; ?>
				   required="Y" 
				   title="<?php echo $this->_tpl_vars['PLS_INPUT_LABEL'];  echo $this->_tpl_vars['LEAVING_TYPE_LABEL']; ?>
"
				   onclick="checkOtherType(this.value);"/>
			<label for="out_of_line"><?php echo $this->_tpl_vars['OUT_OF_LINE_LABEL']; ?>
</label>
			<input type="radio" 
				   name="leaving_type" 
				   id="fail" 
				   value="3" 
				   <?php if ($this->_tpl_vars['LEAVING_TYPE'] == 3): ?>checked<?php endif; ?>
				   <?php if ($this->_tpl_vars['FORM_STATUS'] == 1): ?>disabled<?php endif; ?>
				   required="Y" 
				   title="<?php echo $this->_tpl_vars['PLS_INPUT_LABEL'];  echo $this->_tpl_vars['LEAVING_TYPE_LABEL']; ?>
"
				   onclick="checkOtherType(this.value);"/>
			<label for="fail"><?php echo $this->_tpl_vars['FAIL_LABEL']; ?>
</label>
			<input type="radio" 
				   name="leaving_type" 
				   id="other_type" 
				   value="0" 
				   <?php if ($this->_tpl_vars['LEAVING_TYPE'] == '0'): ?>checked<?php endif; ?>
				   <?php if ($this->_tpl_vars['FORM_STATUS'] == 1): ?>disabled<?php endif; ?>
				   required="Y"
				   onclick="checkOtherType(this.value);"/>
			<label for="other_type"><?php echo $this->_tpl_vars['OTHER_TYPE_LABEL']; ?>
</label>
			<?php if ($this->_tpl_vars['FORM_STATUS'] != 1): ?>
			<input type="text" 
			       class="input-text" 
			       name="other_type_desc"
			       id="other_type_desc"
			       <?php if ($this->_tpl_vars['LEAVING_TYPE'] != '0'): ?> disabled=true<?php endif; ?>
				   value="<?php echo $this->_tpl_vars['OTHER_TYPE_DESC']; ?>
"/>
			<?php else: ?>
				<?php echo $this->_tpl_vars['OTHER_TYPE_DESC']; ?>

			<?php endif; ?>
		</td>
	</tr>
</table>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('showLine' => '1','title' => $this->_tpl_vars['REASON_RESEACH_LABEL'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<table class="bordertable">
	<tr>
		<th width="15%"><?php echo $this->_tpl_vars['REASON_TYPE_LABEL']; ?>
</th>
		<th width="50%"><?php echo $this->_tpl_vars['REASON_LABEL']; ?>
</th>
		<th width="35%"><?php echo $this->_tpl_vars['REMARK_LABEL']; ?>
</th>
	</tr>
	<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['leaving_item_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<tr>
		<td>
			<input type="hidden" 
				   name="ques_id" 
				   value="<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['QUES_ID']; ?>
"/>
			<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['CATE_NO']; ?>
/
			<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['CATE_DESC']; ?>

		</td>
		<td colspan="2">
			<table border="0" style="padding:0px; margin:0px;">
			<?php unset($this->_sections['k']);
$this->_sections['k']['name'] = 'k';
$this->_sections['k']['loop'] = is_array($_loop=$this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<tr>
				<td width="80%">
					<input type="hidden" name="cate_id[<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['ITEM_SEQNO']; ?>
]" 
						  value="<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['CATE_ID']; ?>
"/>
					<input type="checkbox" 
						   name="reason_item[<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['ITEM_SEQNO']; ?>
]" 
						   id="<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['ITEM_SEQNO']; ?>
"
						   value="<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['ITEM_SEQNO']; ?>
"
						   <?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['CHECKED']; ?>

						   <?php if ($this->_tpl_vars['FORM_STATUS'] == 1): ?>disabled<?php endif; ?>
						   />
					<?php if ($this->_tpl_vars['FORM_STATUS'] != 1): ?>
					<label for="<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['ITEM_SEQNO']; ?>
">
						<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['ITEM_DESC']; ?>

					</label>
					<?php else: ?>
					<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['ITEM_DESC']; ?>

					<?php endif; ?>
				</td>
				<td width="20%">
					<?php if ($this->_tpl_vars['FORM_STATUS'] != 1): ?>
					<textarea name="reason_comments[<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['ITEM_SEQNO']; ?>
]"><?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['REMARK']; ?>
</textarea>
					<?php else: ?>
					<?php echo $this->_tpl_vars['leaving_item_list'][$this->_sections['j']['index']]['REASON_ITEMS'][$this->_sections['k']['index']]['REMARK']; ?>
&nbsp;
					<?php endif; ?>
				</td>
			</tr>
			<?php endfor; endif; ?>
			</table>
		</td>
	</tr>
	<?php endfor; endif; ?>
</table>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('showLine' => '1','title' => $this->_tpl_vars['SUGGESTION_LABEL'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php if ($this->_tpl_vars['FORM_STATUS'] != 1): ?>
<textarea name="emp_suggestion" style="width:97%;"><?php echo $this->_tpl_vars['EMP_SUGGESTION']; ?>
</textarea>
<?php else: ?>
<?php echo $this->_tpl_vars['EMP_SUGGESTION']; ?>

<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php if ($this->_tpl_vars['FORM_STATUS'] != 1): ?>
<div align="center">
<input type="submit" name="tmpsubmit" class="button-submit" value="<?php echo $this->_tpl_vars['TMP_SUBMIT_BTN_LABEL']; ?>
"/>
<input type="submit" name="relsubmit" class="button-submit" value="<?php echo $this->_tpl_vars['SUBMIT_BTN_LABEL']; ?>
"/>
</div>
<?php endif; ?>
</form>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>