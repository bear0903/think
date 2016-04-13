<?php /* Smarty version 2.6.11, created on 2016-03-03 13:39:16
         compiled from emp_leave_apply.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'emp_leave_apply.html', 40, false),)), $this); ?>
<style>
.input-time {
	border: 1px solid #ccc;
	background: url(<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/icon-time.gif) no-repeat right;
	background-color: #fff;
	padding-right: 16px;
}
</style>
<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/functions.js" type = "text/javascript"></script>
<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/date.js" type = "text/javascript"></script>
<script type="text/javascript">
var _dateFormat = "yyyy-MM-dd";
var _timeFormat = "H:m";
function CheckMustBeEntry(form)
{
    var _elCnt = form.elements.length;
   
    for (var i=0; i< _elCnt ; i++ )
    {
        var _id  = form.elements[i].id;
        var _val = form.elements[i].value;
        var _msg = "";
        var _warn = false;
        if (_val == "" || _val == "-1")
        {
            switch (_id)
            {
                case "absence_id":
                    //_msg = "請選取所請的假別.";
                    _msg = "<?php echo $this->_tpl_vars['LEAVE_TYPE_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                // add by dennis
                case 'funeral_id':
                	var dis = $('#Layer_funeral').css('display');
                	// IE 和 Firefox 下不同
                    if (dis == 'block' || dis == 'table-row')
                    {
                   	 	//_msg  = '请选择亲属类别.';
                        _msg = "<?php echo ((is_array($_tmp=@$this->_tpl_vars['SPEC_FAMILY_TYPE_MSG'])) ? $this->_run_mod_handler('default', true, $_tmp, '請選取親屬類別') : smarty_modifier_default($_tmp, '請選取親屬類別')); ?>
";
                        _warn = true;
                    }
                    break;
                case "begin_date":
                    //_msg  = "請選取請假開始日期."
                    _msg = "<?php echo $this->_tpl_vars['BEGIN_DATE_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
               
                case "begin_time":
                    _msg  = "請選取請假開始時間."
					//_msg = "<?php echo $this->_tpl_vars['BEGIN_TIME_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                case "end_time":
                    _msg  = "請選取請假結束時間."
					//_msg = "<?php echo $this->_tpl_vars['END_TIME_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                /*
                case "begin_minute":
                    //_msg  = "請選取請假開始分鐘.";
                    _msg = "<?php echo $this->_tpl_vars['BEGIN_TIME_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                */
                case "end_date":
                    //_msg  = "請選取請假結束日期.";
                    _msg = "<?php echo $this->_tpl_vars['END_DATE_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                /*
                case "end_hour":
                    _msg  = "請選取請假結束時數.";
                    _msg = "<?php echo $this->_tpl_vars['END_TIME_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                case "end_minute":
                    //_msg  = "請選取請假結束分鐘.";
                    _msg = "<?php echo $this->_tpl_vars['END_TIME_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                */
                case "leave_reason":
                    _msg  = "請輸入事由.";
                    _msg = "<?php echo $this->_tpl_vars['LEAVE_REASON_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                case "agent":
                    //_msg  = '請輸入代理人.';
                    _msg = "<?php echo $this->_tpl_vars['AGENT_REQUIRED_MSG']; ?>
";
                    _warn = true;
                break;
                default: break;
            }// end
        }else{
        	switch (_id)
            {
                case "begin_date":
                case "end_date":
                    // 日期欄位檢查
                    if (!Date.isValid(_val,_dateFormat))
                    {
                        //_msg  = "日期格式不對, 正確的日期格式為: YYY-MM-DD.";
                        _msg = "<?php echo $this->_tpl_vars['DATE_FORMAT_ERROR_MSG']; ?>
YYYY-MM-DD";
                        _warn = true;
                    }// end if
                break;
                case "action": // 批量申請
                	if (_val == 'batch_apply')
                    {
                        // 檢查是否有選取請假的員工
                        // 動態載入的 row 所在 table 的 id,第一行是 column label
                        var emplist = document.getElementById('emplist');
                        if (emplist.rows.length < 2)
                        {
                            //_msg  = "至少要選取一名員工.";
                            _msg = "<?php echo $this->_tpl_vars['EMP_REQUIRED_MSG']; ?>
";
                            _warn = true;
                        }// end if
                    }// end if
                    break;
                default: break;
            }// end swtich
        }// end if

        if (_warn)
        {
            $('#'+_id)[0].focus();
            alert(_msg);
            return false;
        }// end if
    }// end for loop
    return true;
}// end CheckMustBeEntry()

</script>
<style type="text/css">
<!--
	/* add by dennis 2010-12-03 for 規則設定格式 */
	ol li{ list-style-type:decimal;}
-->
</style>
</head>
<body class="page-container">
<form enctype="multipart/form-data"
	  method="post"
	  name="editForm"
	  id="editForm">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => ($this->_tpl_vars['BLOCK_TITLE']),'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_leave_apply.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php if (! $this->_tpl_vars['isassistant']): ?>
    <?php if ($this->_tpl_vars['leave_rule']): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => ($this->_tpl_vars['LEAVE_RULE_LABEL']),'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php echo $this->_tpl_vars['leave_rule']; ?>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
<?php endif; ?>
</form>

<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/jquery.maskedinput.min.js" type = "text/javascript"></script>
<script>
	$().ready(function(){
		$("#begin_date,#end_date").mask("9999-99-99").blur(function(){
			if ($(this).val() != ''){
				if(!Date.isValid($(this).val(),_dateFormat)) {
					alert('日期输入有误');
					$(this).focus();
					return false;
				}
				if ('<?php echo $this->_tpl_vars['isassistant']; ?>
'==''){
					// add ajax call get the shift data begin time
					var _id = $(this).attr('id');
					//alert(_id);
					var _funcname 		= _id == 'begin_date' ? 'getShiftBeginTime' : 'getShiftEndTime';
					var _feedback_col 	= _id == 'begin_date' ? 'begin_time' 		: 'end_time';
					
					//alert(_feedback_col)
					
					$.ajax({
	                    type:"get",
	                    url:"<?php echo $_SERVER['REQUEST_URI']; ?>
&ajaxcall=1&func="+_funcname+"&shiftdate="+$(this).val(),
	                    async: false, 
	                    timeout:1000,
	                    dataType:'json',
	                    success: function(json){
	                    	$('#'+_feedback_col).val(json);
	                    },
	    				error:function(d){
	    					alert('Get Shift Time Error:'+d.responseText);
	    				}// end function
	                });
				}
			}
		});
		
		$("#begin_time,#end_time").mask("99:99").blur(function(){
			if ($(this).val() != ''){
				if(!Date.isValid($(this).val(),_timeFormat)) {
					alert('时间输入有误');
					$(this).focus();
					return false;
				}
			}
		});
	});
</script>