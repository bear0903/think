<?php /* Smarty version 2.6.11, created on 2016-03-03 19:47:52
         compiled from public_template.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'public_template.html', 6, false),)), $this); ?>

<script src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/functions.js" type="text/javascript"></script>
</head>
<body class="page-container">
<?php if (empty ( $_GET['empseqno'] )): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_header.html", 'smarty_include_vars' => array('title' => ((is_array($_tmp=@$this->_tpl_vars['BLOCK_TITLE'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_GET['doctitle']) : smarty_modifier_default($_tmp, @$_GET['doctitle'])),'showLine' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
<?php echo $this->_tpl_vars['template']; ?>

<?php if (empty ( $_GET['empseqno'] )): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "block_box_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<form name="form2" id="form2" method="post" style="display:none;">
	<textarea rows="0" cols="0" name="lastsql"><?php echo $this->_tpl_vars['sql']; ?>
</textarea>
	<input type="hidden" name="appid" value="<?php echo $this->_tpl_vars['scriptname']; ?>
"/>
	<input type="submit" id="btn_submit"/>
</form>

<script type="text/javascript">
	$().ready(function(){
		$('#tb_newin').click(function(){
			openw(document.location,0,700,400);
		});
				
		$('#tb_print').click(function(){
			$('#form2').attr('action','?scriptname=gridview_print');
			$('#form2').attr('target','_blank');
			$('#btn_submit').click();
		});
		
		$('#tb_export').click(function(){
			$('#form2').attr('action','?scriptname=gridview_export');
			$('#form2').attr('target','self');
			$('#btn_submit').click();
		});
		
		document.title = '<?php echo $_GET['doctitle']; ?>
';
		<?php if ($_SESSION['layout'] == 'iframeLayout'): ?>		
		/* add to adjust iframe's height in the iframeLayout, add by Terry 2011-8-26*/
		var _iframe = $("iframe",parent.document.body);
		var h1 = _iframe.height();
		var h2 = $(document.body).height() + 30;
		var h = h1 > h2 ? h1 : h2;
		_iframe.height(h);
		<?php endif; ?>
	});
</script>