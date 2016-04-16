<?php /* Smarty version 2.6.11, created on 2016-03-03 18:49:16
         compiled from block_emp_list.html */ ?>

<table class="bordertable" id="emplist">
    <tr>
        <th nowrap>
            <input type="checkbox" id="chkall" onclick="CheckAllRows('emplist',this.checked);" title="<?php echo $this->_tpl_vars['CHECK_ALL_LABEL']; ?>
"/>
        </th>
        <th nowrap><?php echo $this->_tpl_vars['DEPT_ID_LABEL']; ?>
</th>
        <th nowrap><?php echo $this->_tpl_vars['DEPT_NAME_LABEL']; ?>
</th>
        <th nowrap><?php echo $this->_tpl_vars['EMP_ID_LABEL']; ?>
</th>
        <th nowrap><?php echo $this->_tpl_vars['EMP_NAME_LABEL']; ?>
</th>
        <!-- added by Gracie at 20090624 -->
        <th nowrap><?php echo $this->_tpl_vars['OVERTIMETYPE_LABEL']; ?>
</th>
        <th nowrap><?php echo $this->_tpl_vars['OVERTIMETYPE_NAME_LABEL']; ?>
</th>
        <!-- added end -->
    </tr>
</table>

<script type="text/javascript">
	var hasLoaded = false;
	
	function CheckAllRows(tabid, bCheck) {
		$rows = $('#'+tabid+" input[name='emp_seqno_chk[]']").attr('checked',bCheck);
	}// end CheckAll()
		    
	function deleteSelectedRows(tabid){
		$rows = $('#'+tabid+" input[name='emp_seqno_chk[]']:checked");
		if ($rows.length>0){
			if (confirm('确定要删除选中的记录?')){
		    	$rows.each(function(){
		    		$(this).parent().parent().remove();
		    	});
		    	$('#chkall').attr('checked',false);
			}
		}else{
			alert('沒有選中記錄');
			return false;
		}
	}
</script>