<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">

<script type="text/javascript" src="../js/jquery-1.11.1.js"></script>
<style><!--
body{ 
position:absolute;
margin:0;
padding:0;
width: 100%;
height: 100%;
/*background-image:url(img/beijing000.png);*/
background-position: center 100%; 
/*background-repeat: no-repeat; 
background-attachment:fixed; 
background-size: cover; 
-webkit-background-size: cover;
-o-background-size: cover;
-yy-background-size: cover;*/
zoom: 1;
z-index: -1;
}

#con{ 
margin:0;
padding: 0;
overflow-y:auto;

top:0; 
left:0; 
height:100%; 
width:100%; 
/*background-image:url(img/beijing000.png);*/
background-position: center 0; 
background-repeat: no-repeat; 
background-attachment:fixed; 
background-size: cover; 
-webkit-background-size: cover;
-o-background-size: cover;
-yy-background-size: cover;
-moz-background-size: cover;
/*background-color:black;*/
zoom: 1;
}

/*#box {
position: relative;
/*background-image:url("../img/beijing000.png");*/
background-position: center 0; 
background-repeat: no-repeat; 
background-size: cover; 
-webkit-background-size: cover;
-o-background-size: cover;
-yy-background-size: cover;
}*/



#box login-div{height: 100%;width: 100%}

input{ border:none; list-style:none; color:#000;}
a{ text-decoration:none;}
img{ display: block; border:none;}
/*.top{ width:100%; height:30px; background-color:#eff4fa;}*/
/*.topzi{ width:335px;float:left; margin-top:6px; margin-right:10px;position: absolute;z-index: 6;height: 400px}*/
/*.topzi a{ display:block; float:left; padding:8px 0; width:80px; line-height:12px; text-align:center; font-size:12px; color:black; background-image:url(img/xian.png); background-repeat:no-repeat; background-position:center right;font-family:"微软雅黑";}*/
/*.topzi a:hover{ color:#C33;}*/
#login-div{ width:100%;overflow:hidden; position: relative;height:100%;text-align: center; }
/*.login-div img{overflow:hidden;height:100%}*/
width:100% 

#bg{
width:100%;
height: 100%;
/*background-image:url("../img/beijing000.png");*/
background-size:cover;
filter : progid:DXImageTransform.Microsoft.AlphaImageLoader ( sizingMethod='scale' , src="../img/beijing000.png") 
}

.topzi{ width:335px;float:left; margin-top:6px; margin-right:10px;position: absolute;z-index: 6;top: 610px;left: 200px}
.topzi a{ display:block; float:left; padding:8px 0; width:80px; line-height:12px; text-align:center; font-size:12px; color:white; background-image:url(img/xian.png); background-repeat:no-repeat; background-position:center right;font-family:"微软雅黑";}
.topzi a:hover{ color:#C33;}

.hcp {position: absolute;z-index: 6;top:40px;right:50%;}
.zitong {position: absolute;z-index: 6;top:75px;right:30%;}

.abc {position: absolute;color: green;z-index: 10}

.loginbox{ width:340px; height:380px; background-color:#FFF; border:2px solid #a0b1c4; border-radius:15px; position: absolute; z-index:6; top:200px; left:50%;margin:auto;margin-left: -170px;top:50%;margin-top: -190px;text-align: center;overflow: hidden;box-shadow: 0 0 30px #000;}
/*#loginbox{vertical-align:middle;margin: 0 auto;}*/

.loginboxtop{ width:340px; height:50px; background-color:#f9fbfe; border-bottom:1px solid #c0cdd9;}
.loginbox .wzbox{ width:280px; margin:0 auto}
.loginboxtop a{ float:left; display:block; width:100px;font-size:18px; color:#333333; line-height:50px; font-family:"微软雅黑"; font-weight: 500;} 
.loginboxtop .cuttent{ color:#999999;}


.button-submit{ width:282px; height:40px; border:2px solid #0066FF ; font-size:16px; color:white; display:block; margin:0 auto; margin-top:50px; border-radius:3px;background-color: #0066FF ;font-family:"微软雅黑"}

.three1,.four1{ width:282px; height:auto; border:1px solid #96a5b4; font-size:14px; color:#aaaaaa; display:block; margin:0 auto; margin-top:0; border-radius:1px;font-family:"微软雅黑";margin-left: 28px;padding: 0;z-index: 20;position: absolute;background-color: white;}
.li1,.li2,.li3,.li4,.li5,.li6,.li7,.li8{text-align: left;text-indent:16px;}
.two{ margin-top:10px;}
.san{width:142px; height:40px; border:1px solid #96a5b4; font-size:12px; color:#aaaaaa; display:block; margin-left:29px;margin-top:10px; border-radius:1px; float: left;}
.yak{ float:right; margin-top:11px; margin-right:40px;font-size:14px;font-family:"微软雅黑";margin-left: 0px}
.forget:hover{color:#C33;}
.ts{ width:312px; line-height:34px; text-align:right;color:#225592;}
.forget{margin-left:130px;color:black}

.k1{ width:282px; height:42px; border:1px solid #96a5b4; font-size:14px; color:#aaaaaa; display:block; margin:0 auto; margin-top:10px; border-radius:3px;font-family:"微软雅黑";text-indent:16px;line-height: 40px;margin-left: 28px;}
.k2{ width:282px; height:42px; border:1px solid #96a5b4; font-size:14px; color:#aaaaaa; display:block; margin:0 auto; margin-top:10px; border-radius:3px;font-family:"微软雅黑";text-indent:16px;line-height: 40px;margin-left: 28px;}
.k3{ width:282px; height:42px; border:1px solid #96a5b4; font-size:14px; color:#aaaaaa; display:block; margin:0 auto; margin-top:10px; border-radius:3px;font-family:"微软雅黑";line-height: 40px;margin-left: 28px;}
.k4{ width:282px; height:42px; border:1px solid #96a5b4; font-size:14px; color:#aaaaaa; display:block; margin:0 auto; margin-top:10px; border-radius:3px;font-family:"微软雅黑";line-height: 40px;margin-left: 28px;}

.one{ width:260px; height:40px; border:none; font-size:14px; color:#aaaaaa; display:block; margin:0; font-family:"微软雅黑";line-height: 39px;margin-left: 22px;}
.two{ width:260px; height:40px; border:none; font-size:14px; color:#aaaaaa; display:block; margin:0; font-family:"微软雅黑";line-height: 39px;margin-left: 22px;}
.three{ width:264px; height:42px; border:none; font-size:14px; color:#aaaaaa; display:block; margin:0; font-family:"微软雅黑";line-height: 39px;margin-left: 18px;}
.four{ width:264px; height:42px; border:none; font-size:14px; color:#aaaaaa; display:block; margin:0; font-family:"微软雅黑";line-height: 39px;margin-left: 18px;appearance: none;}

.one,.two,.three,.four,.y4,.y5,.y6,.textarea:focus{outline: none;}

.y4{width:260px; height:40px; border:none; font-size:14px; color:black; display:block; margin:0;;font-family:"微软雅黑";line-height: 40px;margin-left: 22px;}
.y5{width:260px; height:40px; border:none; font-size:14px; color:black; display:block; margin:0;;font-family:"微软雅黑";line-height: 40px;margin-left: 22px;}
.y6{width:260px; height:40px; border:none; font-size:14px; color:#aaaaaa; display:block; margin:0;;font-family:"微软雅黑";line-height: 40px;margin-left: 22px;}

.y1{width:280px; height:38px; border:2px solid #009FCC; font-size:14px; color:#aaaaaa; display:block; margin:0 auto; margin-top:10px; border-radius:1px;font-family:"微软雅黑";text-indent:16px;line-height: 40px;margin-left: 28px;box-shadow: 0 0 10px #009FCC;color: black;}
.y2{width:282px; height:40px; border:1px solid #96a5b4; font-size:14px; color:#aaaaaa; display:block; margin:0 auto; margin-top:10px; border-radius:1px;font-family:"微软雅黑";text-indent:16px;line-height: 40px;margin-left: 28px;color: black;}
.y3{width:282px; height:40px; border:1px solid #96a5b4; font-size:14px; color:#aaaaaa; display:block; margin:0 auto; margin-top:10px; border-radius:1px;font-family:"微软雅黑";text-indent:16px;line-height: 40px;margin-left: 28px;}

/*.y7{ width:264px; height:42px; border:none; font-size:14px; color:black; line-height: 39px;margin-left: 18px;appearance: none;}
*/


select {

  appearance:none;
  -o-appearance:none;
  -ie-appearance:none;
  -moz-appearance:none;
  -webkit-appearance:none;
}

/*box-shadow: 0 0 10px #009FCC;*/

.three,.four,.y1,.y2,.y3{
background: url(img/dao6.png) no-repeat right;
}


.select{background-color: none;}

--></style>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
</head>
<body>
<p name="type">echo <?php echo ($type); ?></p>
<div id="con">
  
  <div class="login-div" id="login-div">
     <form name="frm_logon" action="<!--<?php echo ($INC_DIR); ?>-->/login.php"
				autocomplete='off' id="frm_logon" method="post">
     <div class="loginbox" id="loginbox">
       <div class="loginboxtop">
         <div class="wzbox">
         
         <a href=""></a>
         </div>
       </div>
       <div id="k1" class="k1">
       
       <input id="username" name="username" class="one" type="text" value="请输入账号" onclick="if(this.value==defaultValue) {this.value='';this.className='y4';this.type='text';}else{this.className='y4';} " onblur="if(!value) {value=defaultValue;this.className='y6';this.type='text';}else{this.className='y5';} " >
       </div>
       <div id="k2" class="k2">
       
       <input id="password" name="password" class="two" type="text" value="请输入密码" onclick="if(this.value==defaultValue) {this.value='';this.className='y4';this.type='password';}else{this.className='y4';this.type='password'}" onblur="if(!value) {value=defaultValue;this.className='y6'; this.type='text';}else{this.className='y5';}">
       </div>
       
       <div id="k3" class="k3">
		<select name="lang" id="lang" class="three" onchange="document.location='index.php?lang='+this.value;" onclick="f1()">
	    <option value="0">请选择</option>
	    <?php if(is_array($langlist)): $i = 0; $__LIST__ = $langlist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo1["lang"]); ?>" selected><?php echo ($s_lang_code); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
	   </select>
		
		</div>
		<div id="k4" class="k4">
		
		<select name="companyno" id="companyno" class="four">
		<option value="0">请选择</option>
	    	<?php if(is_array($cpnlist)): $i = 0; $__LIST__ = $cpnlist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo2): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo2["companyno"]); ?>"><?php echo ($vo2["companyno"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
		</select>
		</div>

       <div class="yak">
	
       </div>
       <!-- <div class="ts">看不清，换一张</div> -->
       <input class="button-submit" type="submit" id="lgoin" name="logon" value="111" ">
	   
     </form>
  </div>
</div>
</body>


<script type="text/javascript" src="../js/jquery-1.11.1.js"></script>
<script type="text/javascript">
/*$(function(){
	$("#ul1").hide();
	$("#ul1 li").on("mouseover",function(e){
		$(e.target).css("backgroundColor","#ccc");
	});
	$("#ul1 li").on("mouseout",function(e){
		$(e.target).css("backgroundColor","#fff");
			});
	$("#ul1 li").on("click",function(e){
		$("#lang").val(e.target.innerHTML);
		$("#ul1").hide();
			});
});
$(function(){
	$("#ul2").hide();
	$("#ul2 li").on("mouseover",function(e){
		$(e.target).css("backgroundColor","#ccc");
	});
	$("#ul2 li").on("mouseout",function(e){
		$(e.target).css("backgroundColor","#fff");
			});
	$("#ul2 li").on("click",function(e){
		$("#companyno").val(e.target.innerHTML);
		$("#ul2").hide();
			});
});
function f1(){
	if($("#ul1").is(":hidden")){
		$("#ul1").show();
	}else{
		$("#ul1").hide();
	}
}
function f2(){
	if($("#ul2").is(":hidden")){
		$("#ul2").show();
	}else{
		$("#ul2").hide();
	}
}

$("body").click(function(e){
	if($(e.target).prop("id")!="lang"){
		alert("11111");
		$("#ul1").hide();
	}
	if($(e.target).prop("id")!="companyno"){
		alert("22222");
		$("#ul2").hide();
	}
});*/
$(function(){
	$("body").click(function(e){
		if($(e.target).prop("id")!="lang"){
			$("#ul1").hide();
		}
		if($(e.target).prop("id")!="companyno"){
			$("#ul2").hide();
		}
	});
	
	$("#ul1").hide();
	$("#ul1 li").on("mousemove",function(e){
		$("#ul1 li").removeClass("select");
		$(e.target).addClass("select");
	});
	$("#ul1 li").on("click",function(e){
		$("#lang").val(e.target.innerHTML);
		$("#ul1").hide();
			});
	$("body").on("keydown",function(e){
		//alert(e.keyCode);
        if (e.keyCode == 13) 
			{
        	if(!$("#ul1").is(":hidden")){
        		$("#lang").val($("#ul1 .select").html());
        		$("#ul1").hide();
    		}
        	if(!$("#ul2").is(":hidden")){
        		$("#companyno").val($("#ul2 .select").html());
        		$("#ul2").hide();
    		}
			}
        if (e.keyCode == 39||e.keyCode == 40) 
		{
    	if(!$("#ul1").is(":hidden")){
    		var $select=$("#ul1 .select").next();
    		if($select.length==0){return;}
    		$("#ul1 .select").removeClass("select");
    		$select.addClass("select");
		}
    	if(!$("#ul2").is(":hidden")){
    		var $select=$("#ul2 .select").next();
    		if($select.length==0){return;}
    		$("#ul2 .select").removeClass("select");
    		$select.addClass("select");
		}
		}
        if (e.keyCode == 37||e.keyCode == 38) 
		{
    	if(!$("#ul1").is(":hidden")){
    		var $select=$("#ul1 .select").prev();
    		if($select.length==0){return;}
    		$("#ul1 .select").removeClass("select");
    		$select.addClass("select");
		}
    	if(!$("#ul2").is(":hidden")){
    		var $select=$("#ul2 .select").prev();
    		if($select.length==0){return;}
    		$("#ul2 .select").removeClass("select");
    		$select.addClass("select");
		}
		}
					});
$("#ul2").hide();
$("#ul2 li").on("mousemove",function(e){
	$("#ul2 li").removeClass("select");
	$(e.target).addClass("select");
});

$("#ul2 li").on("click",function(e){
	$("#companyno").val(e.target.innerHTML);
	$("#ul2").hide();
		});
}
		);
function f1(){
	if($("#ul1").is(":hidden")){
		$("#ul1 li").removeClass("select");
		for(var i=0;i<$("#ul1 li").length;i++)
		{if($("#ul1 li").eq(i).html()==$("#lang").val())
		{$("#ul1 li").eq(i).addClass("select");}}
		$("#ul1").show();
	}else{
		$("#ul1").hide();
	}
}
function f2(){
	if($("#ul2").is(":hidden")){
		$("#ul2 li").removeClass("select");
		for(var i=0;i<$("#ul2 li").length;i++)
		{if($("#ul2 li").eq(i).html()==$("#companyno").val())
		{$("#ul2 li").eq(i).addClass("select");}}
		$("#ul2").show();
	}else{
		$("#ul2").hide();
	}
}


</script>

<!-- <script type="text/javascript">
	function f3(){
		alert("欢迎使用");
		}
</script> -->

<script src="<!--<?php echo ($JS_DIR); ?>-->/flash_detect_min.js"
		type="text/javascript"></script>
<script type="text/javascript">
        /*
         * Check the current docuent
         */
        function init_check() {
            if (!FlashDetect.installed)
            {
                $('#banner-txt').html('<img src="<!--<?php echo ($IMG_DIR); ?>-->/img1.jpg" alt="" border="0"/>');
            }
            if (self != top) {
                top.document.location = self.location;
            }
        }// end init_check()
        $().ready(function(){
            $('.button-submit').button();
            var $username = $('#username');
            $username.focus();
            init_check();
            $('form').submit(function(){
            	var $company = $('#companyno');
            	var $password = $('#password');
            	if ($company.val() == "") {
                    //alert("Please Choose Your Company.");
                    alert("<!--<?php echo ($CHECK_LOGON_MSG01); ?>-->");
                    $company.focus();
                    return false;
                }
                if ($username.val() == "") {
                    //alert("Please Entry Your User Name");
                    alert("<!--<?php echo ($CHECK_LOGON_MSG02); ?>-->");
                    $username.focus(1);
                    return false;
                }
                // check password entry
                if ($password.val() == "") {
                    //alert("Please Entry Your Password");
                    alert("<!--<?php echo ($CHECK_LOGON_MSG03); ?>-->");
                    $password.focus();
                    return false;
                }
                return true;
            });
        });
    </script>

<!-- <script type="text/javascript">
$(function(){
    $('#bg').height($(window).height());
    $('#bg').width($(window).width());
}); 
</script> -->

</html>


</html>