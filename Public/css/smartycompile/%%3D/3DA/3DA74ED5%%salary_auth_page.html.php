<?php /* Smarty version 2.6.11, created on 2016-03-03 16:43:03
         compiled from salary_auth_page.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'salary_auth_page.html', 4, false),)), $this); ?>

</head>
<body  class="page-container">
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => ((is_array($_tmp=((is_array($_tmp=@$_GET['appdesc'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['BLOCK_TITLE']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['BLOCK_TITLE'])))) ? $this->_run_mod_handler('default', true, $_tmp, @$_POST['appdesc']) : smarty_modifier_default($_tmp, @$_POST['appdesc'])),'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <form name="form1"
          method="post"
          autocomplete="off"
          action="../ess/redirect.php?scriptname=<?php echo ((is_array($_tmp=@$_GET['scriptname'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_POST['fromscript']) : smarty_modifier_default($_tmp, @$_POST['fromscript'])); ?>
&appdesc=<?php echo $_GET['appdesc']; ?>
">
        <input type="hidden" name="fromscript" value="<?php echo ((is_array($_tmp=@$_GET['fromscript'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_POST['fromscript']) : smarty_modifier_default($_tmp, @$_POST['fromscript'])); ?>
"/>
        <input type="hidden" name="appdesc" value="<?php echo ((is_array($_tmp=((is_array($_tmp=@$_GET['appdesc'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_POST['appdesc']) : smarty_modifier_default($_tmp, @$_POST['appdesc'])))) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['BLOCK_TITLE']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['BLOCK_TITLE'])); ?>
"/>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_info.html", 'smarty_include_vars' => array('msg_txt' => ((is_array($_tmp=@$this->_tpl_vars['AUTH_PWD_TIP_TEXT'])) ? $this->_run_mod_handler('default', true, $_tmp, '请输入登录时的密码和随机验证码') : smarty_modifier_default($_tmp, '请输入登录时的密码和随机验证码')))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <table class="bordertable">
            <tr>
                <td class="column-label"><?php echo $this->_tpl_vars['PASSWORD_LABEL']; ?>
(*)</td>
                <td>
                    <input type="password" name="password" class="input-text"/>
                    <?php if ($this->_tpl_vars['password_msg']): ?>
                    <span class="error">
                        <img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/error.png" alt=""/>
                        <?php echo $this->_tpl_vars['password_msg']; ?>

                    </span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="column-label" style="vertical-align:top;"><?php echo $this->_tpl_vars['AUTH_CODE_LABEL']; ?>
(*)</td>
                <td>
                    <input type="text" name="authcode" id="authcode" class="input-text"/>
                    <?php if ($this->_tpl_vars['authcode_msg']): ?>
                    <span class="error">
                        <img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/error.png" alt=""/>
                        <?php echo $this->_tpl_vars['authcode_msg']; ?>

                    </span>
                    <?php endif; ?>
                    <br/>
                    <img align="left"
                         style="border:0;width:188px;margin-top:15px;"
                         src="../libs/securimage/securimage_show.php"
                         onclick="this.src = '../libs/securimage/securimage_show.php?sid=' + Math.random(); return false"
                         alt="Authentication Code,click to refresh" />
                    <br clear="all"/>
                    <?php echo $this->_tpl_vars['NOTICE_MSG']; ?>

                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" name="submit" value="<?php echo $this->_tpl_vars['SUBMIT_BTN_LABEL']; ?>
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