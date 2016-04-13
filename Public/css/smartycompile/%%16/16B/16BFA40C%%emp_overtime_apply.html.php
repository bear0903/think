<?php /* Smarty version 2.6.11, created on 2016-03-03 18:49:16
         compiled from emp_overtime_apply.html */ ?>

<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/date.js" type = "text/javascript"></script>
<script type="text/javascript">
	var _dateFormat = "yyyy-MM-dd";
	var _timeFormat = "HH:mm";
	function CheckMustBeEntry(form)
	{
	    var _elCnt = form.elements.length;
	    for (var i=0; i< _elCnt ; i++ )
	    {
	        var _id  = form.elements[i].id;
	        var _msg = "";
	        var _warn = false;
	        if (form.elements[i].value == "")
	        {
	            switch (_id)
	            {
	                case "overtime_date":
	                    _msg = "請輸入加班日期.";
	                    _warn = true;
	                break;
	                case "begin_time":
	                    _msg  = "請輸入加班開始時間.";
	                    _warn = true;
	                break;
	                case "end_time":
	                    _msg  = "請輸入加班結束時間.";
	                    _warn = true;
	                break;
	                case "overtime_hours":
	                    _msg  = "請輸入加班時數.";
	                    _warn = true;
	                break;
	                /* add for utc shanghai
	                case "remark":
	                    _msg  = "加班原因明细必须输入.";
	                    _warn = true;
	                break;
	                */
	                /* follow HCP, HCP 中是非必须输入
	                case "overtime_reason":
	                    _msg  = "請選取加班原因.";
	                    _warn = true;
	                break;
	                */
	                	                case "overtime_fee_type1":
	                    _msg  = "請選取計費或是補休.";
	                    _warn = true;
	                break;
	                // 个人加班时，系统根据日期自动带出
	                case "overtime_type1":
	                    _msg  = "請選取加班類型.";
	                    _warn = true;
	                break;
	                	                default: break;
	            }
	        }else{
	            switch (_id)
	            {
	                case "overtime_date":
	                    if (!Date.isValid(form.elements[i].value,_dateFormat))
	                    {
	                        _warn = true;
	                        _msg  = "日期格式不對, 正確的日期格式為: YYY-MM-DD.";
	                    }
	                    break;
	                case "begin_time":
	                case "end_time":
	                    if (!Date.isValid(form.elements[i].value,_timeFormat))
	                    {
	                        _warn = true;
	                        _msg  = "時間格式不對, 正確的時間格式為: HH24:MI.";
	                    }
	                break;
	                case "overtime_hours":
	                    if (isNaN(parseFloat(form.elements[i].value)) || 
	                    	parseFloat(form.elements[i].value)< 0 )
	                    {
	                        _warn = true;
	                        _msg  = "時數必須為大於零的數位.";
	                    }else{
	                        form.elements[i].value = parseFloat(form.elements[i].value);
	                    }
	                break;
	                case "action": // 批量申請
	                	if (form.elements[i].value == 'batch_apply')
	                    {
	                        // 檢查是否有選取請假的員工
	                        // 動態載入的 row 所在 table 的 id,第一行是 column label
	                        var emplist = document.getElementById('emplist');
	                        if (emplist.rows.length < 2)
	                        {
	                        	 _warn = true;
	                             _msg  = "至少要選取一名員工.";
	                        }// end if
	                    }// end if
	                    break;
	                default: break;
	            }// end swtich
	        }// end if
	        if (_warn)
	        {
	            alert(_msg);
	            form.elements[i].focus();
	            return false;
	        }// end if
	    }// end if
	    return true;
	}// end CheckMustBeEntry()   
//-->
</script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/functions.js"></script>

<style type="text/css">
<!--
	/* add by dennis 2010-12-03 for 規則設定格式 */
	ol li{ list-style-type:decimal;}
-->
</style>
</head>
<body class="page-container">
<form name="form1" id="editForm" method="post" onsubmit="return CheckMustBeEntry(this);">
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['BLOCK_TITLE'],'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_overtime_apply.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php if (! $this->_tpl_vars['isassistant']): ?>
        <?php if ($this->_tpl_vars['overtime_rule']): ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['OVERTIME_RULE_LABEL'],'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php echo $this->_tpl_vars['overtime_rule']; ?>

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
		$("#overtime_date").mask("9999-99-99").blur(function(){
			if ($(this).val() != ''){
				if(!Date.isValid($(this).val(),_dateFormat)) {
					alert('日期输入有误');
					$(this).focus();
					return false;
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