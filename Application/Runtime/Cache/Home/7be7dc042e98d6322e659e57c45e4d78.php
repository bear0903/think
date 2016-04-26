<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<head xmlns="http://www.w3.org/1999/html">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title><!--<?php echo ($title); ?>--></title>
	<link rel = "icon" href = "/think/Public/img/ares.ico" type = "image/x-icon"/>
	<link rel = "shortcut icon" href = "/think/Public/img/ares.ico"/>
	<link rel="stylesheet" href="/think/Public/css/jqueryui/themes/redmond/jquery.ui.all.css" type="text/css" />
	<link rel="stylesheet" href="/think/Public/css/layout/layout.min.css" type="text/css" />
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
			background:url(/think/Public/img/Person.png) no-repeat;
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
		<img src="/think/Public/img/logo.gif">
	</div>
	<div id="topnav">
		<div id="topmenu">
			<!--{if $smarty.session.user.is_manager}-->
			<a href="../mgr/redirect.php">
				<img title="<!--<?php echo ($MSS_LINK_LABEL); ?>-->" src="/think/Public/img/icon_md.png" border="0"/>
			</a>
			<!--{/if}-->
			<a id="myobj" href="?scriptname=year_objective&appdesc=<!--<?php echo (urlencode($OBJ_LINK_LABEL)); ?>-->">
				<img title="<!--<?php echo ($OBJ_LINK_LABEL); ?>-->" src="/think/Public/img/icon_obj.png" alt="<!--<?php echo ($OBJ_LABEL); ?>-->" border="0"/>
			</a>	
			<a href="?scriptname=job_desc" id="myjd">
				<img title="<!--<?php echo ($MY_JD_LINK_LABEL); ?>-->" src="/think/Public/img/icon_jd.png" border="0"/>
			</a>
			<a href="?scriptname=about" id="about">
				<img title="<!--<?php echo ($ABOUT_LINK_LABEL); ?>-->" src="/think/Public/img/icon_about.png" border="0"/>
			</a>
			<a href="../docs/eHRUserGuideESS_<!--<?php echo ($smarty["session"]["user"]["language"]); ?>-->.pdf" id="help"  target="_blank">
				<img title="<!--<?php echo ($HELP_LINK_LABEL); ?>-->" src="/think/Public/img/icon_help.png"  border="0"/></a>
			<!--{if empty($smarty.session.sspi.user)}-->
		    <a href="#" id="logout">
		    	<img title="<!--<?php echo ($smarty["session"]["user"]["user_name"]); ?>--> <!--<?php echo ($LOGOUT_LINK_LABEL); ?>-->" src="/think/Public/img/icon_exit.png"  border="0"/>
		    </a>
		    <!--{/if}-->
	    </div>
	</div>
	<div id="tophuman"></div>
</div><!-- end top -->

<div class="ui-layout-west" >
				<!-- style="display:none;" -->
	<div class="header" style="height:20px;text-indent:5px;">
		Welcome,TestRYH
	</div>
	<div class="ui-layout-content" id="accordion">
		<?php if(is_array($menu_list)): foreach($menu_list as $key=>$c1): ?><h4><a href="1111" target="mainFrame">
		<?php echo ($c1["menu_text"]); ?></a></h4>
		<div>
			<ul>
				<?php if(is_array($c1["menu_id"])): foreach($c1["menu_id"] as $key=>$c2): if($c2["menu_code"] != 'E'): ?><li><a href="<?php echo ($c2["menu_code"]); ?>" target="mainFrame"><?php echo ($c2["menu_text"]); ?></a></li><?php endif; endforeach; endif; ?>
			</ul>
		</div><?php endforeach; endif; ?>	
		<!--{/section}-->
	</div>
</div>
<?php if($menu_list_second ['father_id'] == $menu_list['menu_code']): endif; ?>
<!--left end-->
<div class="ui-layout-center content">
	<iframe name="mainFrame" src="ESNH000" frameborder="0" height="99%" width="100%"  scrolling="auto"></iframe>
</div>
<!--right end-->
<script type="text/javascript" src="/think/Public/js/jqueryui/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="/think/Public/js/jqueryui/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript" src="/think/Public/js/jquery.layout.min-1.2.0.js"></script>
<script type="text/javascript" src="/think/Public/js/jquery.cookie.min.js"></script>
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
			if(confirm('<!--<?php echo ($LOGOUT_WARN_MSG); ?>-->')){
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