<?php if (!defined('THINK_PATH')) exit();?>
</head>
<body class="page-container">
	<!-- 公告通知合并在一个 table | by dennis 2014/02/07 -->
	<!--{include file="block_personal_news_list.html"}-->
	<div class="span-10">
		<!--<?php echo ($calendar); ?>-->
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

	
	<div class="span-9">
		<!--{include file="block_task_list.html"}-->
	</div>
	
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