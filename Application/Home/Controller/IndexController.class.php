<?php
namespace Home\Controller;
use Think\Controller;
define('APP_DEBUG', true);
define('APP_PATH','./Application/');
import('Org.Util.Ares.AresParser');

$language = isset ( $_POST ['lang'] ) ?
$_POST ['lang'] :
(isset ( $_GET ['lang'] ) ? $_GET ['lang'] : $GLOBALS['config']['default_lang']);


class IndexController extends Controller {
    function index(){
    	header('Content-Type:text/html; charset=utf-8');
    	/*$Model = new Model();
    	 $Model->execute("begin dodecrypt();end;");*/
    	$Comp  = M('gl_segment'); //实例化Data数据模型
    	/* $Comp = $Data->find($username);
    	$map['username_no_sz'] = array(array('gt', '0000'),array('lt', '0015')); */
    	$map['segment_type'] = 'COMPANY';
    	$map['segment_no_sz'] = array('in','APLUS_SZ,ARES_SH,SiLanAz,SZCNHR');
    	$this->Com = $Comp->where($map)->select();//getField('user_desc');
    	   
    	
    	//$map['segment_no_sz'] = array('in','APLUS_SZ,ARES_SH,SiLanAz,SZCNHR');
    	//$this->Com = $Comp->where($map)->select();//getField('user_desc');
    	//$this->data = $Data->select();
    	//dump($Comp);
    	//$this->assign('name',$name);
    	
    	$Muti  = M('thinkphp_multilang_list'); //实例化Data数据模型
    	$this->Mut = $Muti->select();//getField('user_desc');
    	$this->display();
    	
        //$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
        //echo '123';
    	/* $user=M('ehr_department_v');
    	$condition['dept_type']='COMPANY'; */
    	/* $username="admin";
    	$this->assign("username",$username);
    	$this->display(); */
    	
    	
    	
    	//$type = M("ehr_multilang_list");
    	//$data = $type->find();
    	//dump($data);
    	/* $_POST = cleanArray($_POST); */
    	
    	/* $language = isset ( $_POST ['lang'] ) ?
    	$_POST ['lang'] :
    	(isset ( $_GET ['lang'] ) ? $_GET ['lang'] : $GLOBALS['config']['default_lang']);
    	
    	$type=M("ehr_multilang_list");
    	$langlist=$type->field('language_name')->select();
    	dump($langlist);
    	$this->assign('langlist',$langlist);
    	
    	$type=M("ehr_department_v");
    	$cpnlist = $type->field('dept_name')->select();
    	$this->assign('cpnlist',$cpnlist);
    	//dump($cpnlist);
    	$this->display(); */
    	
    }
    
    function login(){
    	$companyid = '';
    	$username  = '';
    	$passwd    = '';
    	$cookieDomain = dirname(dirname($_SERVER['PHP_SELF']));
    	$home_url  = $GLOBALS['config']['curr_home'] . '/index.php';
    	$authtype = isset($_GET['authtype']) && !empty($_GET['authtype']) ?
    	$_GET['authtype'] : 'default';
    	
    	if(IS_POST){
    		
    		if (isset($_POST['companyno']) &&
    				isset($_POST['username'])  &&
    				isset($_POST['password']))
    		{
    			$username  = htmlentities ($_POST['username'], ENT_QUOTES, 'UTF-8' );
    			$companyid = $_POST['companyno'];
    			$passwd    = $_POST['password'];
    		}
    		
    		$login = D('login');
    		$db = M('app_users_base');
    		
    		if(!$data = $login->create()){
    			header("Content-type:text/html; charset=utf-8");
    			exit($login->getError());
    		}
    		
    		$data = array();
    		$where['username'] = $data['username'];
    		$result = $db->where($where)->field('companyno,username,password')->find();
    		
    		//验证用户
    		/* if($result && $result['password'] == $result['password']){
    			//存储session
    			session('username',$result['username']);
    			session();
    		} */
    		
    		if(!empty($companyid) && !empty($username) && !empty($passwd)){
    			$langcode  = isset($_POST['lang']) && !empty($_POST['lang']) ?
    			$_POST['lang']:
    			$GLOBALS['config']['default_lang'];
    			
    			echo $companyid ;
    			echo $username;
    			
    			$KLUser = new \Kl_AresUser($companyid,$username);
    			$username = $KLUser->KL_check_user($username);
    			
    			echo $companyid;
    			echo $username;
    			
    			$User = new \AresUser($companyid, $username);
    			$home_url .= '?lang=' .$langcode. '&companyno=' .$companyid;
    			$home_url .= '&loginerror=';
    			
    			if ($User->IsUserExits()){
    				if ($User->isPasswordValid($passwd)){
    					$mss_perm = $User->CheckPermission('MDN');
    					if($User->CheckPermission($mss_perm)){
    						setCookie ('companyid',$companyid, time () + 3600 * 24 * 365, $cookieDomain );
    						setCookie ('language', $langcode, time () + 3600 * 24 * 365, $cookieDomain );
    						setCookie ('username', $username, time () + 3600 * 24 * 365, $cookieDomain );
    						$_SESSION ['user']['language'] = $langcode;
    						//get user information
    						$result=$User->GetUserInfo();
    						session('user.company_id','$companyid');
    						session('user.user_seq_no','$result.USER_SEQ_NO');
    						session('user.emp_seq_no','$result.USER_EMP_SEQ_NO');
    						session('user.emp_id','$result.USER_EMP_ID');
    						session('user.emp_name','$result.USER_EMP_NAME');
    						session('user.user_name','$username');
    						session('user.sex','$result.SEX');
    						session('user.dept_seqno','$result.DEPT_SEQNO');
    						session('user.dept_id','$result.DEPT_ID');
    						session('user.dept_name','$result.DEPT_NAME');
    						session('user.title_id','$result.TITLE_ID');
    						session('user.title_name','$result.TITLE_NAME');
    						session('user.title_level','$result.TITLE_LEVEL');
    						session('user.join_date','$result.JOIN_DATE');
    						//session('user.is_manager1')=$User->IsManager($result ['USER_EMP_SEQ_NO']);
    						session('user.is_manager1','true');
    						session('user.is_manager','$mss_perm');
    						unset($result);
    						//session('user.not_first_login')=$User->isFirstLogin($passwd);
    						session('user.not_first_login','Y');
    						$this->success('登录成功，跳转至主页',U(__APP__."/Home/Index/index"));
    						
    					}
    				}
    			}
    			
    		}
    		
    	}
    }
    
    function lang(){
    	
    	$this->display();
    }
    
    /* public function lang(){
    	$type=M('ehr_multilang_list');
    	$type->field('language_code,language_name')->select();
    	//dump($type);
    	$this->assign('type',$type);
    	$this->display();
    } */
    
    /* function ff(){
    	$this->
    	$this->redirect('www.baidu.com','',3,"页面跳转中...");
    } */

    
    /* public function index2(){
    	$form = D('Form')->find();
    	dump($form);
    	exit;
    } */
    
}