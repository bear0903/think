<?php /* Smarty version 2.6.11, created on 2016-03-18 11:44:59
         compiled from block_salary_period.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'block_salary_period.html', 83, false),array('function', 'html_options', 'block_salary_period.html', 96, false),)), $this); ?>

<script language="JavaScript">
    <!--
    function check() {
        if ($('#period_year')[0].value=="")
        {
            alert("<?php echo $this->_tpl_vars['PLEASE_SELECT_YEAR_LABEL']; ?>
.");
            $('#period_year')[0].focus();
            return false;
        }// end if
        if ($('#period_month')[0].value=="")
        {
            alert("<?php echo $this->_tpl_vars['PLEASE_SELECT_MONTH_LABEL']; ?>
.");
            $('#period_month')[0].focus();
            return false;
        }// end if
        return true;
    }// end check
    /**
     * 根据年份装载其月份资料(Ajax Call)
     * @param string year
     * @param string month
     */
    function loadMonth(year)
    {
        if (year != ''){
            $.ajax({
                type:"POST",
                url:"<?php echo $_SERVER['REQUEST_URI']; ?>
",
                data:"ajaxcall=1&year="+year,
                async: false, 
                timeout:1000,
                dataType:'json',
                success: function(json){
                    //alert(json)
                    addOptionToList('period_month',json);
                }// end function
            });
        }else{
            clearList('period_month'); // add by dennis 20091231
        }// end if
    }// end loadMonth

    /**
     *	移除月份清单，保留文字 "请选择"
     *	@param string list
     *	@return void
     */
    function clearList(list)
    {
        var oList = $('#'+list)[0];
        // 从最后一个Option 往前移除, 所以 loop 从 options.length-1 开始
        for (var i=oList.options.length-1;i>=1;i--)
        {
            // oList.options.remove(i); // ie only
            // support ie & ff
            oList.remove(i);
        }// end for loop
        //oList.selectedIndex = -1;
    }// end clearList()

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
        clearList(list)
        // append options via jquery 
        for (var j=0; j<data.length; j++)
        {
            //s = (data[j][0] == month ? 'selected' : '');
            $('#'+list).append('<option value='+data[j][0]+'>'+data[j][1]+'</option>');
        }// end for loop
    }// end addOptionToList()

    //-->
</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => ((is_array($_tmp=@$this->_tpl_vars['BLOCK_TITLE'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_POST['appdesc']) : smarty_modifier_default($_tmp, @$_POST['appdesc'])),'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<form name="form1" id="form1" method="post" action="../ess/redirect.php?scriptname=<?php echo $_GET['scriptname']; ?>
&companyid=<?php echo $_GET['companyid']; ?>
&empseqno=<?php echo $_GET['empseqno']; ?>
&appdesc=<?php echo $this->_tpl_vars['BLOCK_TITLE']; ?>
" onsubmit="return check();">
    <input type="hidden" name="appdesc" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['BLOCK_TITLE'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_POST['appdesc']) : smarty_modifier_default($_tmp, @$_POST['appdesc'])); ?>
"/>
    <table class="bordertable">
        <tr>
            <td class="column-label">
                <?php echo $this->_tpl_vars['SALARY_YEAR_LABEL']; ?>

            </td>
            <td>
                <select name="period_year" id="period_year"
                        onchange="loadMonth(this.options[this.selectedIndex].value);">
                    <option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_YEAR_LABEL']; ?>
</option>
                                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['year_list'],'selected' => $this->_tpl_vars['s_year']), $this);?>

                                    </select>
            </td>
        </tr>
        <tr>
            <td class="column-label">
                <?php echo $this->_tpl_vars['SALARY_MONTH_LABEL']; ?>

            </td>
            <td>			
                <select name="period_month" id="period_month">
                    <option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_MONTH_LABEL']; ?>
</option>
                                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['month_list'],'selected' => $this->_tpl_vars['s_month']), $this);?>

                                    </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input name="submit" type="submit" value="<?php echo $this->_tpl_vars['SUBMIT_QRY_BTN_LABEL']; ?>
" class="button-submit"/>
            </td>
        </tr>
    </table>
</form>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>