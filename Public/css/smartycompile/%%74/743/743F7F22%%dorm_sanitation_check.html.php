<?php /* Smarty version 2.6.11, created on 2016-03-08 13:31:01
         compiled from dorm_sanitation_check.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'dorm_sanitation_check.html', 14, false),array('modifier', 'date_format', 'dorm_sanitation_check.html', 15, false),array('function', 'html_options', 'dorm_sanitation_check.html', 22, false),)), $this); ?>


<style type="text/css">
	select {width:100px;}
	.short-text{width:80px;}
</style>
</head>
<body class="page-container">
	<form enctype="multipart/form-data" method="post" name="form1" id="form1">
		<input type="hidden" name="doaction" value="query"/>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => ($this->_tpl_vars['BLOCK_TITLE']),'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<table class="bordertable">
		<tr>
			<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['DATE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '查房日期') : smarty_modifier_default($_tmp, '查房日期')); ?>
</th>
			<td><input type="text" name="check_date" id="check_date" class="input-date" value="<?php echo ((is_array($_tmp=((is_array($_tmp=@$_POST['check_date'])) ? $this->_run_mod_handler('default', true, $_tmp, time()) : smarty_modifier_default($_tmp, time())))) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
"/></td>
			<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['NTH_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '次数') : smarty_modifier_default($_tmp, '次数')); ?>
</th>
			<td><input type="text" name="check_times" value="<?php echo ((is_array($_tmp=@$_POST['check_times'])) ? $this->_run_mod_handler('default', true, $_tmp, 1) : smarty_modifier_default($_tmp, 1)); ?>
"/></td>
			<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['AREA_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '区域') : smarty_modifier_default($_tmp, '区域')); ?>
</th>
			<td>
				<select name="area_code" id="area_code">
					<option value="">-<?php echo ((is_array($_tmp=@$this->_tpl_vars['PLS_SELECT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '请选择') : smarty_modifier_default($_tmp, '请选择')); ?>
-</option>
					 <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['area_list'],'selected' => $_POST['area_code']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['AREA_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '栋别') : smarty_modifier_default($_tmp, '栋别')); ?>
</th>
			<td>
				<select name="building_grp_no" id="building_grp_no">
					<option value="">-<?php echo ((is_array($_tmp=@$this->_tpl_vars['PLS_SELECT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '请选择') : smarty_modifier_default($_tmp, '请选择')); ?>
-</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['building_grp_list'],'selected' => $_POST['building_grp_no']), $this);?>

				</select>
			</td>
			<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['AREA_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '楼号') : smarty_modifier_default($_tmp, '楼号')); ?>
</th>
			<td>
				<select name="building_no" id="building_no">
					<option value="">-<?php echo ((is_array($_tmp=@$this->_tpl_vars['PLS_SELECT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '请选择') : smarty_modifier_default($_tmp, '请选择')); ?>
-</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['building_list'],'selected' => $_POST['building_no']), $this);?>

				</select>
			</td>
			<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['AREA_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '房间号') : smarty_modifier_default($_tmp, '房间号')); ?>
</th>
			<td>
				<input type="text" name="room_no" id="room_no" value="<?php echo $_POST['room_no']; ?>
"/>
			</td>
			<td rowspan="2"><input type="button" id="btn_qry" class="button-submit" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['QRY_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '查询') : smarty_modifier_default($_tmp, '查询')); ?>
"/></td>
		</tr>
	</table>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</form>
	<?php if ($_POST['area_code']): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<form name="form2" method="post">
		<input type="hidden" name="doaction" value="save"/>
		<table class="bordertable">
			<tr>
				<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['DEPT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '部门') : smarty_modifier_default($_tmp, '部门')); ?>
</th>
				<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['SHIFT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '班别') : smarty_modifier_default($_tmp, '班别')); ?>
</th>
				<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['ROOM_NO_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '房间号') : smarty_modifier_default($_tmp, '房间号')); ?>
</th>
				<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['CHK_ITEM_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '评比项目') : smarty_modifier_default($_tmp, '评比项目')); ?>
</th>
				<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['ITEM_SCORE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '项目分数') : smarty_modifier_default($_tmp, '项目分数')); ?>
</th>
				<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['ASSESS_SCORE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '项目得分') : smarty_modifier_default($_tmp, '项目得分')); ?>
</th>
				<th><?php echo ((is_array($_tmp=@$this->_tpl_vars['REMARK_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '备注') : smarty_modifier_default($_tmp, '备注')); ?>
</th>
			</tr>
			<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['check_item_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
				<td><?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['ROOM_BY_DEPT']; ?>
</td>
				<td><?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['ROOM_BY_SHIFT_DESC']; ?>
</td>
				<td><?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['ROOM_NO']; ?>
</td>
				<td><?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['CHECK_ITEM_DESC']; ?>
</td>
				<td><?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['ITEM_SCORE']; ?>
</td>
				<td>
					<input type="hidden" name="detail_seqno[]" value="<?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['DETAIL_SEQNO']; ?>
"/>
					<input type="hidden" name="master_seqno[]" value="<?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['MASTER_SEQNO']; ?>
"/>					
					<input type="hidden" value="<?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['ITEM_SCORE']; ?>
"/>
					<input type="text" class="short-text" name="assess_score[]" value="<?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['ASSESS_SCORE']; ?>
"/>
				</td>
				<td><input type="text" name="comments[]" value="<?php echo $this->_tpl_vars['check_item_list'][$this->_sections['i']['index']]['COMMENTS']; ?>
" maxlength="250" style="width:300px;"/></td>
			</tr>
			<?php endfor; else: ?>
			<tr>
				<td colspan="7"><?php echo ((is_array($_tmp=@$this->_tpl_vars['NO_DATA_FOUND_MSG'])) ? $this->_run_mod_handler('default', true, $_tmp, '无符合条件的资料。') : smarty_modifier_default($_tmp, '无符合条件的资料。')); ?>
</td>
			</tr>
			<?php endif; ?>
			
		</table>
		<?php if (count ( $this->_tpl_vars['check_item_list'] ) > 0): ?>
		<div style="text-align:center;"><input type="submit" class="button-submit" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['SAVE_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '保存查房结果') : smarty_modifier_default($_tmp, '保存查房结果')); ?>
"/></div>
	<?php endif; ?>
	</form>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>
	<script type="text/javascript">
		/**
	     * 把 ajax 返回的 json data 装载到 Select List 中
	     * @param string list
	     * @param array data
	     * @param string month
	     * @author Dennis
	     */
	    function addOptionToList(list,data)
	    {
	        // Clear list before add options
	        $('#'+list).html('');
	        // append options via jquery 
			var html = '<option value="">-<?php echo ((is_array($_tmp=@$this->_tpl_vars['PLS_SELECT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '请选择') : smarty_modifier_default($_tmp, '请选择')); ?>
-</option>';
			for (var j=0; j<data.length; j++)
	        {
	            //s = (data[j][0] == month ? 'selected' : '');
	           html += '<option value='+data[j][0]+'>'+data[j][1]+'</option>';
	        }// end for loop
			$('#'+list).append(html);
	    }// end addOptionToList()

		$().ready(function(){

			$('.short-text').blur(function(){
				if(parseInt($(this).val()) > parseInt($(this).prev().val())){
					alert('<?php echo ((is_array($_tmp=@$this->_tpl_vars['SCORE_ERR_MSG'])) ? $this->_run_mod_handler('default', true, $_tmp, '实际分数不可以大于项目分。') : smarty_modifier_default($_tmp, '实际分数不可以大于项目分。')); ?>
');
					$(this).focus();
				}
			});
			$('#check_date').datepicker({dateFormat:'yy-mm-dd',changeMonth:true,changeYear:true});	
			// clear child value on change
			$('#area_code').change(function(){
				// clear child
				$('#building_grp_no').val('');
				// clear grandson
				$('#building_no').val('');
				// ajax get child data and fill to child select
				if ($(this).val() != ''){
					$.ajax({
					    url: '?scriptname=<?php echo $_GET['scriptname']; ?>
',
					    data:'doaction=ajaxcall&func=getBuildingGrpByArea&areacode='+$(this).val(), 
					    type: 'POST',
					    dataType: 'json',
					    timeout: 1000,
					    error: function(){
					        alert('Error Get Building Group Data');
					    },
					    success: function(json){
					        addOptionToList('building_grp_no',json);
					    }
					});
				}
			});

			// clear child value on change
			$('#building_grp_no').change(function(){
				$('#building_no').val('');
				if ($(this).val() != ''){
					$.ajax({
					    url: '?scriptname=<?php echo $_GET['scriptname']; ?>
',
					    data:'doaction=ajaxcall&func=getBuildingByGrp&areacode='+$('#area_code').val()+'&building_grp_no='+$(this).val(), 
					    type: 'POST',
					    dataType: 'json',
					    timeout: 1000,
					    error: function(){
					        alert('Error Get Building Data');
					    },
					    success: function(json){
					        addOptionToList('building_no',json);
					    }
					});
				}
			});

			// check condition before query 
			$('#btn_qry').click(function(){
				var r = true;
				$("#form1").find(":input,select").each(function(){
					if($(this).val() === ""){
						$(this).focus();
						var label_txt = $(this).parent().prev().html();
					    alert(label_txt+"<?php echo ((is_array($_tmp=@$this->_tpl_vars['CANNOT_NULL_MSG'])) ? $this->_run_mod_handler('default', true, $_tmp, '不能为空') : smarty_modifier_default($_tmp, '不能为空')); ?>
");
					    r = false;
					    return false;
					 }
				});
				if (r) $('#form1').submit();
			});
			
		});
	</script>