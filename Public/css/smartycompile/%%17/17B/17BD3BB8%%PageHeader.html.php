<?php /* Smarty version 2.6.11, created on 2016-02-02 17:08:36
         compiled from PageHeader.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'PageHeader.html', 16, false),)), $this); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type"     content = "text/html; charset=utf-8"/>
		<meta http-equiv="Content-Language" content="zh-cn" />
		<meta http-equiv="Pragma"           content="no-cache">
		<meta http-equiv="Cache-Control"    content="no-cache">
		<meta http-equiv="Expires"          content="0">
		<meta name = "owner"       content = "Dennis Lan/R&D/ARES CHINA"/>
		<meta name = "author"      content = "Dennis Lan, Lan Jiangtao"/>
		<meta name = "Copyright"   content = "ARES China Inc."/>
		<meta name = "create-date" content = "2004-07-16 15:21:30"/>
		<meta name = "update-date" content = "2008-07-09 13:19:30"/>
		<meta name = "description" content = "eHR for HCP"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
		<title><?php echo ((is_array($_tmp=@$this->_tpl_vars['DOCUMENT_TITLE'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</title>
		<link rel = "icon" href = "<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/ares.ico" type = "image/x-icon"/>
		<link rel = "shortcut icon" href = "<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/ares.ico"/>
		<link rel="stylesheet" href="<?php echo $this->_tpl_vars['CSS_DIR']; ?>
/blueprint/screen.css" type="text/css" media="screen, projection">
		<!--[if IE]>
			<link rel="stylesheet" href="<?php echo $this->_tpl_vars['CSS_DIR']; ?>
/blueprint/ie.css" type="text/css" media="screen, projection">
		<![endif]-->
		<link rel="stylesheet" href="<?php echo $this->_tpl_vars['CSS_DIR']; ?>
/jqueryui/themes/<?php echo $this->_tpl_vars['DEFAULT_THEME']; ?>
/jquery.ui.all.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->_tpl_vars['CSS_DIR']; ?>
/default.css?m=20130917" type="text/css" media="screen, projection">
	    <script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/jqueryui/jquery-1.4.4.min.js" type = "text/javascript"></script>
	    <script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/jqueryui/jquery-ui-1.8.11.custom.min.js" type = "text/javascript"></script>
	    <?php if ($_SESSION['user']['language'] <> 'US'): ?>
	    <script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/i18n/ui.datepicker-<?php if ($_SESSION['user']['language'] == 'ZHS'): ?>zh-CN<?php else: ?>zh-TW<?php endif; ?>.min.js" type = "text/javascript"></script>
	    <?php endif; ?>