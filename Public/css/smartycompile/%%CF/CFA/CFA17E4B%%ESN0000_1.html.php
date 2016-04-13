<?php /* Smarty version 2.6.11, created on 2016-02-22 15:31:02
         compiled from ESN0000_1.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'urlencode', 'ESN0000_1.html', 75, false),)), $this); ?>
<!DOCTYPE html>
<head xmlns="http://www.w3.org/1999/html">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title><?php echo $this->_tpl_vars['title']; ?>
</title>
	<link rel = "icon" href = "<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/ares.ico" type = "image/x-icon"/>
	<link rel = "shortcut icon" href = "<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/ares.ico"/>
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['CSS_DIR']; ?>
/jqueryui/themes/redmond/jquery.ui.all.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['CSS_DIR']; ?>
/layout/layout.min.css" type="text/css">
	<!--[if lte IE 7]>
		<style type="text/css"> body { font-size: 85%; } </style>
	<![endif]-->
	<!-- REQUIRED scripts for layout widget -->
	<style type="text/css">
		#logo {
			width: 20%;
			float: left;
		}
		
		#topnav {
			background-color: #eee;
			float: left;
			height: 78px;
			width: 60%;
		}
		
		#topnav a:link {
			text-decoration: none
		}
		
		#topnav a:visited {
			text-decoration: none
		}
		
		#topnav a:active {
			text-decoration: none
		}
		
		#topmenu {
			position: absolute;
			margin-top: 15px;
			left: 50%;
		}
		#accordion{
			width:98%;
			margin:2px 15px 2px 2px;
			padding:0;
			font-size:13px;
		}
		#accordion ul{
			margin:0;
			padding:0;
		} 
		#accordion ul li{ 
			background:url(<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/Person.png) no-repeat;
			list-style-type:none;
			text-indent:18px;
			margin-left:-10px;
			padding-bottom:5px;
		}
	</style>
</head>
<body>
<div class="ui-layout-north">
	<div id="logo">
		<img src="<?php echo $this->_tpl_vars['company_logo']; ?>
">
	</div>
	<div id="topnav">
		<div id="topmenu">
			<?php if ($_SESSION['user']['is_manager']): ?>
			<a href="../mgr/redirect.php">
				<img title="<?php echo $this->_tpl_vars['MSS_LINK_LABEL']; ?>
" src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/icon_md.png" border="0"/>
			</a>
			<?php endif; ?>
			<a id="myobj" href="?scriptname=year_objective&appdesc=<?php echo ((is_array($_tmp=$this->_tpl_vars['OBJ_LINK_LABEL'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
">
				<img title="<?php echo $this->_tpl_vars['OBJ_LINK_LABEL']; ?>
" src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/icon_obj.png" alt="<?php echo $this->_tpl_vars['OBJ_LABEL']; ?>
" border="0"/>
			</a>	
			<a href="?scriptname=job_desc" id="myjd">
				<img title="<?php echo $this->_tpl_vars['MY_JD_LINK_LABEL']; ?>
" src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/icon_jd.png" border="0"/>
			</a>
			<a href="?scriptname=about" id="about">
				<img title="<?php echo $this->_tpl_vars['ABOUT_LINK_LABEL']; ?>
" src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/icon_about.png" border="0"/>
			</a>
			<a href="../docs/eHRUserGuideESS_<?php echo $_SESSION['user']['language']; ?>
.pdf" id="help"  target="_blank">
				<img title="<?php echo $this->_tpl_vars['HELP_LINK_LABEL']; ?>
" src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/icon_help.png"  border="0"/></a>
			<?php if (empty ( $_SESSION['sspi']['user'] )): ?>
		    <a href="#" id="logout">
		    	<img title="<?php echo $_SESSION['user']['user_name']; ?>
 <?php echo $this->_tpl_vars['LOGOUT_LINK_LABEL']; ?>
" src="<?php echo $this->_tpl_vars['IMG_DIR']; ?>
/icon_exit.png"  border="0"/>
		    </a>
		    <?php endif; ?>
	    </div>
	</div>
	<div id="tophuman"></div>
</div><!-- end top -->

<div class="ui-layout-west" style="display:none;">
	<div class="header" style="height:20px;text-indent:5px;">
		Welcome,<?php echo $_SESSION['user']['emp_name']; ?>

	</div>
	<div class="ui-layout-content" id="accordion">
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['menu_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
		<h4><a href="?scriptname=<?php echo $this->_tpl_vars['menu_list'][$this->_sections['i']['index']]['menu_code']; ?>
" target="mainFrame"><?php echo $this->_tpl_vars['menu_list'][$this->_sections['i']['index']]['menu_text']; ?>
</a></h4>
		<div>
			<ul>
				<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['menu_list'][$this->_sections['i']['index']]) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['show'] = true;
$this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = $this->_sections['j']['loop'];
    if ($this->_sections['j']['total'] == 0)
        $this->_sections['j']['show'] = false;
} else
    $this->_sections['j']['total'] = 0;
if ($this->_sections['j']['show']):

            for ($this->_sections['j']['index'] = $this->_sections['j']['start'], $this->_sections['j']['iteration'] = 1;
                 $this->_sections['j']['iteration'] <= $this->_sections['j']['total'];
                 $this->_sections['j']['index'] += $this->_sections['j']['step'], $this->_sections['j']['iteration']++):
$this->_sections['j']['rownum'] = $this->_sections['j']['iteration'];
$this->_sections['j']['index_prev'] = $this->_sections['j']['index'] - $this->_sections['j']['step'];
$this->_sections['j']['index_next'] = $this->_sections['j']['index'] + $this->_sections['j']['step'];
$this->_sections['j']['first']      = ($this->_sections['j']['iteration'] == 1);
$this->_sections['j']['last']       = ($this->_sections['j']['iteration'] == $this->_sections['j']['total']);
?>
					<?php if ($this->_tpl_vars['menu_list'][$this->_sections['i']['index']][$this->_sections['j']['index']]['menu_code'] != ''): ?>
					<li><a href="?scriptname=<?php echo $this->_tpl_vars['menu_list'][$this->_sections['i']['index']][$this->_sections['j']['index']]['menu_code']; ?>
&appdesc=<?php echo ((is_array($_tmp=$this->_tpl_vars['menu_list'][$this->_sections['i']['index']][$this->_sections['j']['index']]['menu_text'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
" target="mainFrame"><?php echo $this->_tpl_vars['menu_list'][$this->_sections['i']['index']][$this->_sections['j']['index']]['menu_text']; ?>
</a></li>
					<?php endif; ?>
				<?php endfor; endif; ?>
			</ul>
		</div>
		<?php endfor; endif; ?>
	</div>
</div>
<!--left end-->
<div class="ui-layout-center content">
	<iframe name="mainFrame" src="?scriptname=ESNH000" frameborder="0" height="99%" width="100%"  scrolling="auto"></iframe>
</div>
<!--right end-->
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/jqueryui/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/jqueryui/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/jquery.layout.min-1.2.0.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JS_DIR']; ?>
/jquery.cookie.min.js"></script>
<script type="text/javascript">
	var pageLayout;
	$(document).ready(function(){
		// create page layout
		pageLayout = $('body').layout({
			scrollToBookmarkOnLoad:		false, // handled by custom code so can 'unhide' section first
			defaults: {},
			north: {
				size:					75,
				spacing_open:			0,
				closable:				false,
				resizable:				false
			},
			west: {
				size:					200,
				spacing_closed:			22,
				togglerLength_closed:	140,
				togglerAlign_closed:	"top",
				togglerContent_closed:	"显<BR>示<BR>菜<BR>单<BR>",
				togglerTip_closed:		"Open & Pin Contents",
				sliderTip:				"Slide Open Contents",
				slideTrigger_open:		"mouseover"
			}
		});

		$('#myjd').click(function(){
			window.open(this.href,'win','left=300px,top=200px,toolbar=no,menubar=no,scrollbars=yes');
			return false;
		});
		$('#myobj').click(function(){
			window.open(this.href,'win','left=300px,top=200px,toolbar=no,menubar=no,scrollbars=yes');
			return false;
		});

		$('#about').click(function(){
			showModalDialog(this.href,'about','dialogWidth=475px;dialogHeight=370px;dialogLeft=300px;dialogTop=200px;toolbar=no;menubar=no;scrollbars=no');
			return false;
		});
		$('#logout').click(function(){
			if(confirm('<?php echo $this->_tpl_vars['LOGOUT_WARN_MSG']; ?>
')){
				top.location='../ess/index.php?action=logout';
			}// end if
		});
		$('#accordion').accordion({
			fillSpace: true,
			collapsible: true,
			autoHeight: true,
			navigation: true,
			clearStyle: true,
			active: false
		});
	});
</script>
</body>
</html>