<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
        //$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
        //echo '123';
    	/* $user=M('ehr_department_v');
    	$condition['dept_type']='COMPANY'; */
    	/* $username="admin";
    	$this->assign("username",$username);
    	$this->display(); */
    	header('Content-Type:text/html; charset=utf-8');
    	$type = M("ehr_multilang_list");
    	$data = $type->find();
    	//dump($data);
    	$langlist = $type->field('language_code,language_name')->select();
    	//dump($langlist);
    	$this->assign('langlist',$langlist);
    	
    	$language = isset ( $_POST ['lang'] ) ?
    						$_POST ['lang'] :
    						(isset ( $_GET ['lang'] ) ?
    								 $_GET ['lang'] :
    								 $GLOBALS['config']['default_lang']);
    	$this->assign($s_lang_code,$language);
    	
    	$type=M(ehr_department_v);
    	$cpnlist = $type->field('dept_seq_no as company_id,
			                    dept_name as company_name')->select();
    	
    	$this->assign('cpnlist',$cpnlist);
    	$this->display();
    }
    
    /* public function lang(){
    	$type=M('ehr_multilang_list');
    	$type->field('language_code,language_name')->select();
    	//dump($type);
    	$this->assign('type',$type);
    	$this->display();
    } */
    
    public function login(){
    	$this->display();
    	
    }
    
    public function ff(){
    	$this->
    	$this->redirect('www.baidu.com','',3,"页面跳转中...");
    }

    
    /* public function index2(){
    	$form = D('Form')->find();
    	dump($form);
    	exit;
    } */
    
}