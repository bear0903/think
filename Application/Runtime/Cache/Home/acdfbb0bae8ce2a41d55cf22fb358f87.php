<?php if (!defined('THINK_PATH')) exit();?><script type="text/javascript">
    $().ready(function(){
        $('#myjd').click(function(){
            window.open(this.href,'win','left=300px,top=200px,toolbar=no,menubar=no,scrollbars=yes');
            return false;});
        $('#myobj').click(function(){
            window.open(this.href,'win','left=300px,top=200px,toolbar=no,menubar=no,scrollbars=yes');
            return false;});
        $('#about').click(function(){
            showModalDialog(this.href,'about','dialogWidth=475px;dialogHeight=370px;dialogLeft=300px;dialogTop=200px;toolbar=no;menubar=no;scrollbars=no');
            return false;});

        $('#logout').click(function(){
            if(confirm('<!--<?php echo ($LOGOUT_WARN_MSG); ?>-->')){
                top.location='../ess/index.php?action=logout';
            }// end if
        });
    });
</script>
<style type="text/css">
	body{
		margin:0px;
		paddding:0px;
	}
	#header{
		margin:0px;
		padding:0px;
		height:75px;
	}
	#logo {
		background: url("<!--<?php echo ($company_logo); ?>-->") left top no-repeat #fff;
		margin:0px;
		width:15%;
		float:left;
		height:75px;
	}
	#topnav{
		background: url("<!--<?php echo ($IMG_DIR); ?>-->/ess-logo.png") center top no-repeat #fff;
		float:left;
		height:75px;
		width:65%;
	}
	#topnav a:link   {
		text-decoration:   none
	}  
	#topnav a:visited   {
		text-decoration:   none
	}  
	#topnav a:active   {
		text-decoration:   none
	}
	#topmenu{
		position:absolute;
		margin-top: 15px;
		left:47%;
	}
	#tophuman{
		background: url("<!--<?php echo ($IMG_DIR); ?>-->/human.png") right top no-repeat #fff;
		float:left;
		width:20%;
		height:75px;
	}
</style>
</head>
<body>
<div id="header">
  	<div id="logo"></div>
	<div id="topnav">
		<div id="topmenu">
			<!--{if $smarty.session.user.is_manager}-->
			<a href="../mgr/redirect.php">
				<img title="<!--<?php echo ($MSS_LINK_LABEL); ?>-->" src="<!--<?php echo ($IMG_DIR); ?>-->/icon_md.png" border="0"/>
			</a>
			<!--{/if}-->
			<a id="myobj" href="?scriptname=year_objective&appdesc=<!--<?php echo (urlencode($OBJ_LINK_LABEL)); ?>-->">
				<img title="<!--<?php echo ($OBJ_LINK_LABEL); ?>-->" src="<!--<?php echo ($IMG_DIR); ?>-->/icon_obj.png" alt="<!--<?php echo ($OBJ_LABEL); ?>-->" border="0"/>
			</a>	
			<a href="?scriptname=job_desc" id="myjd">
				<img title="<!--<?php echo ($MY_JD_LINK_LABEL); ?>-->" src="<!--<?php echo ($IMG_DIR); ?>-->/icon_jd.png" border="0"/>
			</a>
			<a href="?scriptname=about" id="about">
				<img title="<!--<?php echo ($ABOUT_LINK_LABEL); ?>-->" src="<!--<?php echo ($IMG_DIR); ?>-->/icon_about.png" border="0"/>
			</a>
			<a href="../docs/eHRUserGuideESS_<!--<?php echo ($smarty["session"]["user"]["language"]); ?>-->.pdf" id="help"  target="_blank">
				<img title="<!--<?php echo ($HELP_LINK_LABEL); ?>-->" src="<!--<?php echo ($IMG_DIR); ?>-->/icon_help.png"  border="0"/></a>
			<!--{if empty($smarty.session.sspi.user)}-->
		    <a href="#" id="logout">
		    	<img title="<!--<?php echo ($smarty["session"]["user"]["user_name"]); ?>--> <!--<?php echo ($LOGOUT_LINK_LABEL); ?>-->" src="<!--<?php echo ($IMG_DIR); ?>-->/icon_exit.png"  border="0"/>
		    </a>
		    <!--{/if}-->
	    </div>
	</div>
	<div id="tophuman"></div>
</div>
<!-- Main Tab Menu Begin -->
<!--<?php echo ($main_menu); ?>-->