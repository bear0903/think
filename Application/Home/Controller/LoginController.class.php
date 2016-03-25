<?php 
namespace Home\Controller;
use Think\Controller;

class LoginController extends Controller{
	/**
	 * 用户登录
	 */
	public function Login(){
		if(IS_POST){
			$login = D('login');
			
			//自动验证  收集数据集
			if(!$data = $login->create()){
				//防止输出乱码
				header("Content-type:text/html; charset=utf8-");
				exit($login->getError());
			}
			
			//组合查询条件
			$where = array();
			$where['username']=$data['username'];
			$result = $login->where($where)->field(
				'userid,username,nickname,password,lastdate,lastip'	
				)->find();
			
			//验证用户名 对比 密码
			if($result && $result['password'] == $result['password']){
				//存储session
				session('uid',$result['userid']);
				session('nickname',$result['nickname']);
				session('username',$result['username']);
				session('lastdate',$result['lastdate']);
				session('lastip',$result['lastip']);
				
				//更新用户登录信息
				$where['userid']=session('uid');
				M('user')->where($where)->setInc('loginnum');//登录次数+1
				M('user')->where($where)->save($data);//更新时间和登录ip
				
				$this->success('登录成功，正跳转至系统首页...',U('Index/index'));
			} else {
				$this->error('登录失败，用户名或密码不正确');
			}
		} else {
			$this->display();
		}
	}
	
	public function register(){
		//判断提交方式 做不同处理
		if(IS_POST){
			//实例化user对象
			$user = D('users');
			
			//自动验证 创建数据集
			if(!$data = $user->create()){
				//防止输出中文乱码
				header("Content-type:text/html; charset=utf-8");
				exit($user->getError());
			}
			
			/* if($id=$user->add($data)){
				//直接注册用户为超级管理员，子用户采用邀请注册的模式
				//遂设置公司id等于注册用户id，便于管理公司用户
				$user->where("userid = $id")->setField('companyid',$id);
				$this->success('注册成功',U('Index/index'),2);
			} else {
				$this->error('注册失败');
			} 
		} else {
				$this->display();
			}
	} */
	
	/**
	 * 用户注销
	 */
	public function logout(){
		//清除所有session
		session(null);
		redirect(U('Login/login',2,'正在退出登录'));
	}
	
	/**
	 * 验证码
	 */
	/* public function verify(){
		//实例化Verify对象
		$verify = new \Think\Verify();
		
		//配置验证码参数
		$verify->fontSize = 14;		//验证码字体大小
		$verify->length = 4;		//验证码位数
		$verify->imageH = 34;		//验证码高度
		$verify->useImgBg = true;	//开启验证码背景
		$verify->useNoise = false;	//关闭验证码干扰杂点
		$verify->entry();
	} */
	
}


?>