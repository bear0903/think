<?php /* Smarty version 2.6.11, created on 2016-03-21 13:08:40
         compiled from emp_trans_apply.html */ ?>

<script language="JavaScript" type="text/javascript">
<!--
	function CheckMustBeEntry(form)
	{
	    var _elCnt = form.elements.length;
	    var _dateFormat = "yyyy-MM-dd";
	    var _timeFormat = "HH:mm";
	    for (var i=0; i< _elCnt ; i++ )
	    {
	        var _id  = form.elements[i].id;
	        var _msg = "";
	        var _warn = false;
	        if (form.elements[i].value == "")
	        {
	            switch (_id)
	            {
	                case "trans_date":
	                    _msg = "請輸入异动日期.";
	                    _warn = true;
	                break;
	                
	                case "trans_type":
	                    _msg  = "請輸入异动类别.";
	                    _warn = true;
	                break;
	                case "new_department":
	                    _msg  = "請輸入新部门.";
	                    _warn = true;
	                break;
	                default: break;
	            }
	        }else{
	            switch (_id)
	            {
	                case "trans_date":
	                    if (!Date.isValid(form.elements[i].value,_dateFormat))
	                    {
	                        _warn = true;
	                        _msg  = "日期格式不對, 正確的日期格式為: YYY-MM-DD.";
	                    }
	                    break;
	                default: break;
	            }// end swtich
	        }// end if
	        
	        if (_warn)
	        {
	            alert(_msg);
	            return false;
	        }// end if
	    }// end if
	    return true;
	}// end CheckMustBeEntry()

	
    function changeSubListValue(transtype_id)
    {
		var v_newreason_array = new Array();
        <?php echo $this->_tpl_vars['js_array']; ?>

        for (var i=0; i<v_newreason_array.length;i++)
        {
            if (transtype_id ==  v_newreason_array[i]["TRANSTYPE_MASTER_ID"])
            {
            	//document.getElementById("remark").value = v_newreason_array[i]["TRANSTYPE_DETAIL_ID"];
                document.getElementById("new_reason").value = v_newreason_array[i]["TRANSTYPE_DETAIL_ID"];
                break;
            }// end if
        }// end for loop
    }// end changeSubListValue()
    
    function removeRowFromTable(tabid,rowindex)
	{
	  var tbl = document.getElementById(tabid);
	  var lastRow = tbl.rows.length;
	  if (lastRow > 2) tbl.deleteRow(rowindex);
	}// end removeRowFromTable()
	
    function CheckAllRows(tabid, bCheck) {
        var tab = document.getElementById(tabid);
        var rows =  tab.rows.length;
        for (var i = 0; i < rows; i++) {
            //alert(tab.rows[i].cells[0].childNodes[0].type)
            if (tab.rows[i].cells[0].childNodes[0].type == 'checkbox') {
            	tab.rows[i].cells[0].childNodes[0].checked = bCheck;
            }//end if
        }// end for loop
    }// end CheckAll()

    var hasLoaded = false;
 	// If there isn't an element with an onclick event in your row, then this function can't be used.
    function deleteCurrentRow()
    {
        var element = event.target || event.srcElement;
    	if (hasLoaded) {
    		var delRow = element.parentNode.parentNode;
    		var tbl = delRow.parentNode.parentNode;
    		var rIndex = delRow.sectionRowIndex;
    		var rowArray = new Array(delRow);
    		deleteRows(rowArray);
    		//reorderRows(tbl, rIndex);
    	}// end if
    }// end deleteCurrentRow()

    function deleteRows(rowObjArray)
    {
    	if (hasLoaded) {
    		for (var i=0; i<rowObjArray.length; i++) {
    			var rIndex = rowObjArray[i].sectionRowIndex;
    			rowObjArray[i].parentNode.deleteRow(rIndex);
    		}
    	}// end if
    }// end DeleteRows()

    function deleteSelectedRows(tabid)
    {
        var tab = document.getElementById(tabid);
        var c = tab.rows.length;
        for (var i=c-1; i>0; i--)
        {
            if (tab.rows[i].cells[0].childNodes[0].checked)
            {
        		tab.deleteRow(i);
            }// end if
        }// end for loop
    }// end deleteSelectedRows()
//-->
</script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/functions.js"></script>
</head>
<body class="page-container">
<form name="form1" id="editForm" method="post" onsubmit="return CheckMustBeEntry(this);">
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => $this->_tpl_vars['BLOCK_TITLE'],'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_trans_apply.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</form>	