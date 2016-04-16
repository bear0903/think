<?php /* Smarty version 2.6.11, created on 2016-02-02 17:08:36
         compiled from PageFooter.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strpos', 'PageFooter.html', 20, false),)), $this); ?>


<!-- PageFooter Created by Dennis 2006-01-11 11:59:21  -->
<div id="<?php echo $_GET['scriptname']; ?>
"></div>
</body>
</html>
<script type="text/javascript">
	// salary page timeout script
    <?php echo $this->_tpl_vars['logoutscript']; ?>

    $().ready(function(){
        <?php echo $this->_tpl_vars['timer_js']; ?>

        // auto reisze
        <?php if ($_GET['scriptname'] != ''): ?>
        if (parent.document)
        {
        	// set manin content div height
        	var h = $('#<?php echo $_GET['scriptname']; ?>
').parent().height()+35+'px';
        	var m = "<?php echo $_GET['scriptname']; ?>
".substring(0,4);
        	// 处理 MSS共用ESS程式画面未自动扩展
        	if ("<?php echo ((is_array($_tmp=$_SERVER['HTTP_REFERER'])) ? $this->_run_mod_handler('strpos', true, $_tmp, 'mgr') : strpos($_tmp, 'mgr')); ?>
">0)
       		{
        		m = m.replace('ESN','MDN');
       		}
        	// handle default frame hieght
        	if (m != 'defa')
        	$('#frameid_'+m,window.parent.document).attr('height',h);
        }
        <?php endif; ?>
        // set all buttons to jquery ui which with .button-submit class
        $('.button-submit').button();        
        $('#tab-menu').height('100%');
    });
</script>