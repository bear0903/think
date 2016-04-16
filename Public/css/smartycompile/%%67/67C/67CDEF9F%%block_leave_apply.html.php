<?php /* Smarty version 2.6.11, created on 2016-03-03 13:39:16
         compiled from block_leave_apply.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'block_leave_apply.html', 9, false),array('modifier', 'default', 'block_leave_apply.html', 15, false),array('modifier', 'date_format', 'block_leave_apply.html', 38, false),)), $this); ?>
<table class="bordertable">
    <tr>
        <td width="120" class="column-label"><?php echo $this->_tpl_vars['ABSENCE_NAME_LABEL']; ?>
 *</td>
        <td>
            <select name="absence_id"
                    id  ="absence_id"
                    style="width:188px;">
                <option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['leave_name_list'],'selected' => $this->_tpl_vars['s_leave_id']), $this);?>

            </select>
        </td>
    </tr>
    <!-- Ajax Get 当前所选的假别的已休时数 (天数)，可休时数 (天数) #todo-->
    <tr id="leave_type_info" style="display:none;">
    	<td class="column-label"><?php echo ((is_array($_tmp=@$this->_tpl_vars['LEAVE_TYPE_INFO_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '假况') : smarty_modifier_default($_tmp, '假况')); ?>
</td>
    	<td>
    		<?php echo ((is_array($_tmp=@$this->_tpl_vars['CAN_REST_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '可休') : smarty_modifier_default($_tmp, '可休')); ?>
<span id="can_rest"></span><span id="rest_unit"></span>
	        <?php echo ((is_array($_tmp=@$this->_tpl_vars['HAD_REST_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '已休') : smarty_modifier_default($_tmp, '已休')); ?>
<a href=""></a><span id="had_rest"></span><span id="rest_unit"></span>
    	</td>
    </tr>
    <!--特別假 親屬類別 -->
    <tr id="Layer_funeral" style="display:none;">
        <td class="column-label"><?php echo ((is_array($_tmp=@$this->_tpl_vars['FUNERAIL_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '名称') : smarty_modifier_default($_tmp, '名称')); ?>
 *</td>
        <td>
            <select name="funeral_id" id="funeral_id">
                <option value="-1"></option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="column-label"><?php echo $this->_tpl_vars['BEGIN_TIME_LABEL']; ?>
 *</td>
        <td>
            <input type="text"
                   name="begin_date"
                   id="begin_date"
                   title="Date Format:YYYY-MM-DD"
                   style="width:80px;"
                   value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
"/>
                   <a href="#" style="width:20px;height:20px;margin-bottom:-6px;background:url(../img/date.png) no-repeat center;" id="btn_begin_date"></a>
                   <input type="text" name="begin_time" 
                   		  id="begin_time" title="Time Format: HH24:MI"
                   		   class="input-time"
                   		   style="width:45px;"/>
        </td>
    </tr>
    <tr>
        <td class="column-label"><?php echo $this->_tpl_vars['END_TIME_LABEL']; ?>
 *</td>
        <td>
            <input type="text"
                   name="end_date"
                   id="end_date"
                   title="Date Format: YYYY-MM-DD HH24:MI"
                   style="width:80px;"
                   value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
"/>
                   <a href="#" style="width:20px;height:20px;margin-bottom:-6px;background:url(../img/date.png) no-repeat center;" id="btn_end_date"></a>
                   <input type="text" name="end_time" 
                   		  id="end_time" title="Time Format: HH24:MI"
                   		  class="input-time"
                   		  style="width:45px;"/>
        </td>
    </tr>
    <tr>
        <td class="column-label"><?php echo ((is_array($_tmp=@$this->_tpl_vars['LEAVE_HOURS_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '时数') : smarty_modifier_default($_tmp, '时数')); ?>
</td>
        <td id="leave_hours">test</td>
    </tr>
    <tr>
        <td class="column-label"><?php echo $this->_tpl_vars['LEAVE_REASON_LABEL']; ?>
 *</td>
        <td>
            <textarea name="leave_reason" id="leave_reason"></textarea>
        </td>
    </tr>
    <tr>
        <td class="column-label"><?php echo $this->_tpl_vars['UPLOAD_FILE_LABEL']; ?>
</td>
        <td>
            <input type="file" name="doc_paper" size="26" id="doc_paper" title="support file type: gif,jpg,jpeg,png,bmp"/>
            .gif,.jpg,.jpeg,.png,.bmp,.pdf
        </td>
    </tr>
    <?php if (empty ( $this->_tpl_vars['isassistant'] )): ?>
    <!-- 代理类型: 1:全权代理; 2: 受限代理 这一栏位为必输入 boll 2009-12-28-->
    <tr>
        <td class="column-label"><?php echo $this->_tpl_vars['AGENT_TYPE_LBEL']; ?>
 *</td>
        <td>
            <input type="radio"  name="assign_type" id="assign_type_0" value='0' checked><?php echo ((is_array($_tmp=@$this->_tpl_vars['NO_AGENT_LABEL'])) ? $this->_run_mod_handler('default', true, $_tmp, '不授權') : smarty_modifier_default($_tmp, '不授權')); ?>

            <input type="radio"  name="assign_type" id="assign_type_1" value='1'><?php echo $this->_tpl_vars['FULL_AGENT_LABEL']; ?>

            <input type="radio"  name="assign_type" id="assign_type_2" value='2'><?php echo $this->_tpl_vars['CONDITION_AGENT_LABEL']; ?>


        </td>
    </tr>
    <tr>
        <td class="column-label"><?php echo $this->_tpl_vars['AGENT_LABEL']; ?>
 *</td>
        <td>
            <input type="text" name="agent" id="agent" value='无' readonly class="input-lov" style="width:84px;"/>
            <input type="hidden" name="agent_id" id="agent_id">
            <input type="hidden" name="agent_code" id="agent_code">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <!-- remark by dennis 去掉暫存功能  <input type="submit" name="save"   value="<?php echo $this->_tpl_vars['TMP_SUBMIT_BTN_LABEL']; ?>
" class="button-submit" title="Save Data Only"/> -->
            <input type="button" id="doPost"  value="<?php echo $this->_tpl_vars['SUBMIT_BTN_LABEL']; ?>
" class="button-submit" title="Submit Apply to Workflow"/>
        </td>
    </tr>
    <?php else: ?>
    <tr>
        <td colspan="2">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_batch_apply_detail.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </td>
    </tr>
    <?php endif; ?>
</table>
<input type="submit" name="submit" id="submitEditForm" style="display:none;">
<script type="text/javascript">
	
    /* add by boll 2009-04-20
     **  檢驗開始間、結束時間是不是在排程時間內
     */

    $("#doPost").click(function(){
        return postData();
    });
    function postData(){
        var ok=CheckMustBeEntry(document.editForm);
        if(!ok) return false;

        var action=$("#action").val(); // 批量申請不檢驗
        if(action=='batch_apply') return $("#submitEditForm").click();

        var begin_time = $("#begin_date").val() +' '+ $("#begin_time").val();// + ':' + $("#begin_minute").val();
        var end_time   = $("#end_date").val() +' '+ $("#end_time").val();//   + ':' + $("#end_minute").val();
        //alert(begin_time);alert(end_time);

        $.post('?scriptname=ajax_overtime&do=CheckLeaveApplyTimeArea',
        		{ 
        			begin_time: begin_time,
        			end_time: end_time 
        		},
		        function(data){
		            //alert(data);
		            if(data =='1'){
		                //alert('開始時間非排程時間, 請重新輸入!');
		                alert('<?php echo $this->_tpl_vars['BEGIN_TIME_ERROR_MSG']; ?>
');
		                return false;
		            }
		            if(data =='2'){
		                //alert('結束時間非排程時間, 請重新輸入!');
		                alert('<?php echo $this->_tpl_vars['END_TIME_ERROR_MSG']; ?>
');
		                return false;
		            }
		            //alert(111);
		            if(confirm('<?php echo $this->_tpl_vars['CONFIRM_SUBMIT_MSG']; ?>
')){
		                $("#submitEditForm").click();
		            }
		        }
		    );
    	}

    /*
     *  代理人通过lov选取
     */

    $("#agent").click(function(){
        if($("#assign_type_0").attr('checked')) return false;
        var url="../mgr/redirect.php?scriptname=employee_lov";
        var txt_id = document.getElementById("agent_id");
        var txt_no = document.getElementById("agent_code");
        var txt_name = document.getElementById("agent");
        showLov(url,txt_id,txt_no,txt_name,400,500);
    });
    //added by Gracie at 20090612
    $("#agent1").click(function(){
        var url="../mgr/redirect.php?scriptname=employee_lov";
        var txt_id = document.getElementById("agent_id1");
        var txt_no = document.getElementById("agent_code1");
        var txt_name = document.getElementById("agent1");
        showLov(url,txt_id,txt_no,txt_name,400,500);
    });
    //added end;
    
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
		var html = '';
		for (var j=0; j<data.length; j++)
        {
            //s = (data[j][0] == month ? 'selected' : '');
           html += '<option value='+data[j][0]+'>'+data[j][1]+'</option>';
        }// end for loop
		$('#'+list).append(html);
    }// end addOptionToList()
    /**
     *  特別假處理
     * last modify by dennis 2013-10-22
     * 有特别假设定才去 bind 这个事件
     */
    <?php if (( $this->_tpl_vars['spec_abs'] )): ?>
    $("#absence_id").change(function(){
        var spec_abs = [];
        // 先挑出特别假 id
        if ('<?php echo $this->_tpl_vars['spec_abs']; ?>
' != '' &&  '<?php echo $this->_tpl_vars['isassistant']; ?>
' != '1')
        {
            spec_abs = <?php echo $this->_tpl_vars['spec_abs']; ?>
;
            var is_spec = false;
            $("#Layer_funeral").hide();
            $('#funeral_id').val(-1);
            for (var i=0; i<spec_abs.length; i++)
            {
                // 是特别假的 show select
                if ($(this).val() == spec_abs[i])
                {
                    $("#Layer_funeral").show();
                    is_spec = true;
                    break;
                }
            }
           
            if(is_spec)
            {
            	$.ajax({
                    type:"get",
                    url:"<?php echo $_SERVER['REQUEST_URI']; ?>
&ajaxcall=1&func=GetFamilyType&abs_type_id="+$(this).val(),
                    async: false, 
                    timeout:1000,
                    dataType:'json',
                    success: function(json){
                    	addOptionToList('funeral_id',json);
                    },
    				error:function(d){
    					alert('error->'+d.responseText);
    				}// end function
                });
            }
        }
    });
    <?php endif; ?>

    //代理人不受權
    $("#assign_type_0").click(function(){
        $("#agent").val('無');
    });
    $("#assign_type_1").click(function(){
        $("#agent").val('');
    });
    $("#assign_type_2").click(function(){
        $("#agent").val('');
    });
    
    $().ready(function(){
    	$('#begin_date,#end_date').datepicker({
			dateFormat:'yy-mm-dd',
			changeMonth:true,
			changeYear:true,
		 	showOn: "button",
		 	buttonImage: "../img/date.png",
		 	buttonImageOnly: true
		});
    	
    	$('.ui-datepicker-trigger').attr('style','margin-left:2px;margin-bottom:-4px;');
    });
</script>