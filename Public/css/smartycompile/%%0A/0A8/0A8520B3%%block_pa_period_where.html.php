<?php /* Smarty version 2.6.11, created on 2016-03-09 14:11:38
         compiled from block_pa_period_where.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'block_pa_period_where.html', 11, false),array('modifier', 'default', 'block_pa_period_where.html', 11, false),)), $this); ?>

	<form name="form1" method="post">
	    <table class="bordertable">
	        <tr>
	            <td class="column-label" width="100"><?php echo $this->_tpl_vars['PA_PERIOD_LABEL']; ?>
</td>
	            <td>
	                <select name="pa_period_seqno" 
	                        id  ="pa_period_seqno"
	                        style= "margin:0px;">
	                    <option value=""><?php echo $this->_tpl_vars['PLEASE_SELECT_LABEL']; ?>
</option>
	                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['pa_period_list'],'selected' => ((is_array($_tmp=@$this->_tpl_vars['pa_seqno'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_POST['pa_period_seqno']) : smarty_modifier_default($_tmp, @$_POST['pa_period_seqno']))), $this);?>

	                </select>
	                <input type="submit" name="submitquery" class="button-submit" value="<?php echo $this->_tpl_vars['SUBMIT_QRY_BTN_LABEL']; ?>
"/>
	            </td>
	        </tr>
	    </table>
    </form>