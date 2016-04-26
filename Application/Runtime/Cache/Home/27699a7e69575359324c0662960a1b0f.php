<?php if (!defined('THINK_PATH')) exit();?>
</head>

<body class="page-container">
	<!-- 鍏憡閫氱煡鍚堝苟鍦ㄤ竴涓� table | by dennis 2014/02/07 -->
	<!--{if count($personal_news_list)>0 ||  count($company_news_list)>0}-->
	<!--{include file="block_personal_news_list.html"}-->
	<!--{/if}-->
	<div class="span-10">
	<br />
<!-- 	{foreach $test as $key=>$data}
	<?php echo ($key); ?>:<?php echo ($data); ?><br />
	{/foreach} -->
		<?php echo ($calendar); ?>
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
					<?php echo ((isset($WEEKEND_DAY_LABEL) && ($WEEKEND_DAY_LABEL !== ""))?($WEEKEND_DAY_LABEL):'休息日'); ?>
				</td>
				<td>
					<?php echo ((isset($WORKDAY_LABEL) && ($WORKDAY_LABEL !== ""))?($WORKDAY_LABEL):'工作日'); ?>
				</td>
				<td>
					<?php echo ((isset($CURRENT_DAY_LABEL) && ($CURRENT_DAY_LABEL !== ""))?($CURRENT_DAY_LABEL):'今天'); ?>
				</td>
				<td>
					<?php echo ((isset($ARRANGE_LABEL) && ($ARRANGE_LABEL !== ""))?($ARRANGE_LABEL):'未排班'); ?>
				</td>
				<td>
					<?php echo ((isset($NATION_DAY_LABEL) && ($NATION_DAY_LABEL !== ""))?($NATION_DAY_LABEL):'国定假日'); ?>
				</td>
			</tr>
		</table>
	</div>
	<!--{if count($pa_period_list)>0 	 || 
       		count($pa_forms_list)>0  	 || 
       		count($pa_goal_list)> 0  	 ||
       		count($pa_goal_edit_list)> 0 ||
       		count($pa_goal_emp_list) > 0 }-->
	<div class="span-9">
		<!--{include file="pa_period_list.html"}-->
	</div>
	<!--{/if}-->

	<!--{if count($user_define_wf_list)>0 	|| 
				$leave_apply_count>0 		|| 
				$cancel_leave_apply_count>0 || 
				$overtime_apply_count>0 	|| 
				$trans_apply_count>0 		|| 
				$nocard_apply_count>0 		|| 
				$resign_apply_count>0 }-->
	<div class="span-9">
		<!--{include file="block_task_list.html"}-->
	</div>
	<!--{/if}-->
	<!--{*if $leave_apply_count			== 0 	&&
                $cancel_leave_apply_count	== 0 	&&
                $overtime_apply_count		== 0 	&&
                $trans_apply_count			== 0 	&&
                $nocard_apply_count			== 0 	&&
                $resign_apply_count			== 0 	&&
                count($company_news_list) 	== 0 	&&
                count($personal_news_list) 	== 0 	&&
                count($pa_period_list) 		== 0 	&&
                count($pa_forms_list) 		== 0 	&&
                count($pa_goal_list) 		== 0 	&&
                count($pa_goal_edit_list) 	== 0 	&&
                count($pa_goal_emp_list) 	== 0*}-->
    <!--<div class="span-9">-->
	<!--{*include file="block_info.html" msg_txt=$NO_NEW_MSG*}-->
	<!--  </div> -->
	<!--{*/if*}-->

	<script src="<!--<?php echo ($JS_DIR); ?>-->/functions.js" type="text/javascript"></script>
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