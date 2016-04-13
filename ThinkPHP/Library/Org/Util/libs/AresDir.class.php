<?php
/*
 * This class is used to operate dirs
 * create by Terry Wang
 * 2011-6-28 
 */
class AresDir{
	private $dirname=null;   //要创建目录的路径,支持多级目录a/b/c..
	private $cover=false;  //是否覆盖原来的目录
	private $msg='';
	private $mode=0777; //目录的权限 
	/*
	 *错误代码说明
	 *0、没有错误
	 *1、目录已经存在
	 *2、目录名称不合法 
	*/
	private $errCode=0;
	function __construct($dirname=null){
		//设置目录名称
		$this->dirname=$dirname;
	}
	//检测目录是否存在以及是否可写
	public function chkDir(){
		if(is_dir($this->dirname)){
			$this->errCode=1;
			return ;
		}
		if(!$this->dirname){
			$this->errCode=2;
			return ;
		}
		//目录名称不能包含/\:*?"<>|特殊字符
		if(preg_match("/([\:\*\?\"\<\>\|])+/", $this->dirname)){
			$this->errCode=2;
			return ;
		}
		return ;
	}
	//递归创建目录
	public function _create($dirpath){
		if(!file_exists($dirpath)){
			$this->_create(dirname($dirpath));
			@ mkdir($dirpath,$this->mode);
		}
	}
	//创建目录
	public function create(){
		$this->chkDir();
		switch ($this->errCode){
			case 0:
				$this->_create($this->dirname);
				if(file_exists($this->dirname)){
					$this->msg='目录创建成功！';
				}else{
					$this->msg="目录创建失败,未知错误！";
				}
				break;
			case 1:
				if($this->cover){
					if(!$this->_create($this->dirname)){
						$this->msg='目录已经存在且不能被覆盖！';
					}
				}else{
					$this->msg='目录已经存在！';
				}
				break;
			case 2:
				$this->msg='目录名称不合法';
				break;
			default:
				$this->msg='未知错误';
		}
	}
	//设置目录权限
	public function setMode($mode){
		$this->mode=$mode;
	}
	//设置是否覆盖已存在的目录,true cover,false not cover
	public function setCover($cover=false){
		$this->cover=$cover;
	}
	//获取创建目录信息
	public function getMsg(){
		return $this->msg;
	}
}