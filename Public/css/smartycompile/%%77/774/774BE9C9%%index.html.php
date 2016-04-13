<?php /* Smarty version 2.6.11, created on 2016-04-11 17:12:07
         compiled from index.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'index.html', 138, false),array('modifier', 'default', 'index.html', 180, false),)), $this); ?>

<style type="text/css">
#main-div {
	width: 755px;
	text-align: center;
	margin-right: auto;
	margin-left: auto;
}

#top {
	text-align: left;
	margin-top: 50px;
	padding-bottom: 5px;
	padding-top: 50px;
}

#login-div {
	text-align: left;
	width: 755px;
	position: relative;
	height: 400px;
	background: url(<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/index_banner_2.jpg) no-repeat
}

#loginbox {
	width: 310px;
	position: absolute;
	top: -59px;
	right: 17px;
}

#loginbox-title {
	background: url(<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/loginbg.gif) no-repeat;
	background-position: left -95px;
	width: 320px;
	height: 15px;
}

#loginbox-body {
	border-right: #e1eaee 1px solid;
	background: #eff9fe;
	border-left: #e1eaee 1px solid;
	text-align: center;
	width: 308px;
	height: 240px;
}

#loginbox-bottom {
	background: url(<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/loginbg.gif) no-repeat;
	background-position: left -173px;
	width: 320px;
	height: 15px;
}

#intr-div {
	position: absolute;
	left: 280px;
	width: 320px;
	top: 338px;
	height: 180px;
	text-align: center;
}

#intr-title-div {
	background: url(<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/loginbg.gif) no-repeat;
	background-position: 40px -335px;
	width: 320px;
	height: 24px;
	z-index: -1;
}

#banner-txt {
	text-align: left;
	width: 100%;
	position: absolute;
	height: 155px;
	background: url(<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/img1.jpg) no-repeat;
}

#main-div .input-text {
	width: 160px;
}

#main-div .input-select {
	width: 164px;
}

table {
	font-size: 16px;
}

.txt {
	height: 22px;
	font-size: 16px;
	line-height: 22px;
}
.
</style>
</head>
<body>
	<div id="main-div">
		<div id="top">
			<img height="56" width="140" src="<?php echo $this->_tpl_vars['company_logo']; ?>
"
				alt="Company Logo" />
		</div>
		<div id="login-div">
			<div id="banner-txt">
				<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
					codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0"
					width="450" height="155">
					<param name="movie" value="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/login.swf">
					<param name="quality" value="high">
					<param name="wmode" value="transparent">
					<embed src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/login.swf" quality="high"
						wmode="transparent" quality="high"
						pluginspage="http://www.macromedia.com/go/getflashplayer"
						type="application/x-shockwave-flash" width="450" height="155" />
				</object>
			</div>
			<form name="frm_logon" action="<?php echo $this->_tpl_vars['INC_DIR']; ?>
/login.php"
				autocomplete='off' id="frm_logon" method="post">
				<div id="loginbox">
					<div id="loginbox-title"></div>
					<div id="loginbox-body">
						<span>Version:<?php echo @ESS_APP_VERSION; ?>
</span>
						<table width="310" cellpadding="0" cellspacing="0">
							<tr>
								<td colspan="4">&nbsp;</td>
							</tr>
							<tr>
								<td width="30">&nbsp;</td>
								<td width="100">
									<?php echo $this->_tpl_vars['LANG_LABEL']; ?>

								</td>
								<td width="125"><select name="lang" id="lang"
									class="input-select txt"
									onChange="document.location='index.php?lang='+this.value;">
										<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['language_list'],'selected' => $this->_tpl_vars['s_lang_code']), $this);?>

								</select></td>
								<td width="30">&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<?php echo $this->_tpl_vars['COMPANY_NAME_LABEL']; ?>

								</td>
								<td><select name="companyno" id="companyno"
									class="input-select txt">
										<!-- begin company list -->
										<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['company_list'],'selected' => $this->_tpl_vars['s_company_id']), $this);?>

										<!-- end company list -->
								</select></td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<?php echo $this->_tpl_vars['USER_ID_LABEL']; ?>

								</td>
								<td><input type="text" id="username" name="username"
									class="input-text txt" maxlength="16" value="" /></td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<?php echo $this->_tpl_vars['PASSWORD_LABEL']; ?>

								</td>
								<td><input type="password" id="password" name="password"
									class="input-text txt" maxlength="16" value="" /></td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><input type="submit" id="lgoin"
									value="<?php echo $this->_tpl_vars['LOGIN_BTN_LABEL']; ?>
" name="logon"
									class="button-submit" />&nbsp;&nbsp;&nbsp;
									<a
									href="redirect.php?scriptname=findpasswd&action=findpasswd&lang=<?php echo ((is_array($_tmp=@$_GET['lang'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['s_lang_code']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['s_lang_code'])); ?>
">
										<?php echo $this->_tpl_vars['LOST_PASSWORD_LABEL']; ?>

									</a></td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td colspan="4">&nbsp; 
								<?php if ($_GET['loginerror']): ?>
									<div class="error">
										<img src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/error.png"
											style="margin-bottom: -4px;" alt="" />
										<?php echo $_GET['loginerror']; ?>

									</div> <?php endif; ?>
								</td>
							</tr>
						</table>
					</div>
					<div id="loginbox-bottom"></div>
				</div>
			</form>
		</div>
		<div id="intr-div" style="text-align:center;">
			<div id="intr-title-div" style="width:320px;margin-left:75px;" ></div>
		</div>
	</div>

	<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/flash_detect_min.js"
		type="text/javascript"></script>
	