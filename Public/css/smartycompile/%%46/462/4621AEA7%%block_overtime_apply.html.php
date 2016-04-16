<?php /* Smarty version 2.6.11, created on 2016-03-03 18:49:16
         compiled from block_overtime_apply.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'block_overtime_apply.html', 9, false),array('function', 'html_options', 'block_overtime_apply.html', 49, false),)), $this); ?>
	<table class="bordertable">
		<tr>
			<td width="100" class="column-label"><?php echo $this->_tpl_vars['OVERTIME_DATE_LABEL']; ?>
*</td>
			<td>
				<input type="text"
					   name="overtime_date"
					   id="overtime_date"
					   title="Date Format: YYYY-MM-DD"
					   value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
"/>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['BEGIN_TIME_LABEL']; ?>
*</td>
			<td>
				<input type="text"
					   name="begin_time"
					   id="begin_time"
					   class="input-text"
					   title="Time format: HH24:MI"
					   value="18:00"/>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['END_TIME_LABEL']; ?>
*</td>
			<td>
				<input type="text"
					   name="end_time"
					   id="end_time"
					   class="input-text"
					   title="Time format: HH24:MI"
					   value="21:00"/>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['HOURS_LABEL']; ?>
*</td>
			<td>
				<input type="text"
					   name="overtime_hours"
					   id="overtime_hours"
					   class="input-text"/>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['REASON_LABEL']; ?>
</td>
			<td>
				<select name="overtime_reason"
						id="overtime_reason">
				<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['overtime_reason_list'],'selected' => $this->_tpl_vars['s_reason_seqno']), $this);?>

				</select>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['OT_FEE_TYPE_LABEL']; ?>
*</td>
			<td>
				<select id="overtime_fee_type1" 
						<?php if (! $this->_tpl_vars['isassistant']): ?>disabled="true"<?php endif; ?>>
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['overtime_fee_type'],'selected' => $_POST['overtime_fee_type']), $this);?>

				</select>
				<input type="hidden" name="overtime_fee_type" id="overtime_fee_type" value=""/>
			</td>
		</tr>
		<tr>
			<td class="column-label"><?php echo $this->_tpl_vars['OT_TYPE_LABEL']; ?>
*</td>
			<td>
				<select name="overtime_type1" 
						id="overtime_type1" 
						<?php if (! $this->_tpl_vars['isassistant']): ?>disabled="true"<?php endif; ?>>
					<option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['overtime_type'],'selected' => $_POST['overtime_type']), $this);?>

				</select>
				<input type="hidden" name="overtime_type" id="overtime_type" value=""/>
			</td>
		</tr>
		<tr>
			<td valign="top" class="column-label"><?php echo $this->_tpl_vars['REMARK_LABEL']; ?>
</td>
			<td><textarea name="remark" id="remark"></textarea>
			</td>
		</tr>
        <?php if (! empty ( $this->_tpl_vars['isassistant'] )): ?>
        <tr>
            <td colspan="2">
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_batch_apply_detail.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            </td>
        </tr>
        <?php else: ?>
        <tr>
			<td></td>
			<td>
				<input type="submit" name="submit" value="<?php echo $this->_tpl_vars['SUBMIT_BTN_LABEL']; ?>
" class="button-submit"/>
			</td>
		</tr>
        <?php endif; ?>		
	</table>

	<script type="text/javascript">
		/* add by boll 2009-04-20
		**  取加班時數
		*/
		$("#overtime_date").change(function(){
			getFactHour();
		});
		$("#begin_time").change(function(){
			getFactHour();
		});
		$("#end_time").change(function(){
			getFactHour();
		});
		// change by dennis 2011-12-09
		$().ready(function(){
			getFactHour();
			<?php if ($this->_tpl_vars['isassistant']): ?>
			// add by dennis 2011-12-09 for fixed
			$('#overtime_type1').change(function(){
				$('#overtime_type').val($(this).val());
			});			
			$('#overtime_fee_type1').change(function(){
				$('#overtime_fee_type').val($(this).val());
			});
			<?php endif; ?>
			$('#overtime_date').datepicker({
				dateFormat:'yy-mm-dd',
				changeMonth:true,
				changeYear:true,
			 	showOn: "button",
			 	buttonImage: "../img/date.png",
			 	buttonImageOnly: true
			});
	    	
	    	$('.ui-datepicker-trigger').attr('style','margin-left:2px;margin-bottom:-4px;');
		});
		
		function getFactHour(){
			var action=$("#action").val(); // 批量申請不處理
			if(action=='batch_apply') return false;
			<?php echo $this->_tpl_vars['otfeejs_code']; ?>
 //add by dennis 2011-12-09 22:18
			var d1=$("#begin_time").val();
			var d2=$("#end_time").val();
			var d3=$("#overtime_date").val();
			$.ajax(
			{
				type:'post',
			   	url:'?scriptname=ajax_overtime&do=GetOverTime',
			   	data:{begin_time:    d1,
					  end_time:      d2,
					  overtime_date: d3},
			  	success: function(data){
					$("#overtime_hours").val(data['hours']);
					$("#overtime_type1").val(data['day_type']);
					$("#overtime_type").val(data['day_type']);
					//　如果没有抓到计费还是补休的话，就让其可以选择计费或是补休  // add by dennis 2011-12-09 22:18
					var otfeetype = otfee[data['day_type']] != '' ? otfee[data['day_type']] : '';
					if (otfeetype != '')
					{
						$("#overtime_fee_type1").attr('disabled',true);
						$("#overtime_fee_type1").val(otfeetype);
						$("#overtime_fee_type").val(otfeetype);
					}else{
						$("#overtime_fee_type1").attr('disabled',false);
						// 如果没有抓到计费还是补休的话，就让其可以选择计费或是补休  // add by dennis 2011-12-15 13:55
						$("#overtime_fee_type1").change(function(){
							$('#overtime_fee_type').val($(this).val());
						});
					}
			   },
			   dataType: 'json'});
		}
	</script>