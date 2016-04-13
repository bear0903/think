<?php 
/*----------------------------------------------------------------------------------
 * 文件上传类
 * 目前不支持上传文件名为数组的形式 
 * Usage :
 * 1、$Upload = new AresUpload($savePath);  $savePath 保存目录
 * 2、如果没有set文件的保存文件名，那么默认名称为md5(microtime()),再加上文件的扩展名来命名文件
 * 3、$Upload->set(filename.jpg);设置文件的名称(不包括扩展名)
 * 4、 $Upload->_upload();  上传文件
 * create by TerryWang
 ----------------------------------------------------------------------------------*/
class AresUpload{
	//允许上传文件的扩展名
	private $allowExts = array(
		'jpg','jpeg','gif','png'
	);
	private $uploadInfo = array();
	//允许上传文件的最大值,-1表示只受php.ini文件中upload_max_filesize的限制,默认是2M
	private $maxSize = -1;
	//允许上传的文件类型,为空不受限制
	private $allowType = array();
	//文件的保存目录
	private $savePath = null;
	//文件的保存路径
	private $destination = '';
	//上传错误信息
	private $error = '';
	//保存文件名
	private $fileName = '';
	function __construct($savePath = '',$fileName = ''){
		$this->fileName = $fileName;
		$this->savePath = $savePath;
	}
	/**
	 * @param void
	 * 上传文件  
	 * return 上传文件的信息
	 */
	function _upload(){
		//echo $this->savePath;
		$files=$this->dealFiles();
		if(!$files) return false;
		foreach ($files as $k => $v){
			if($this->checkFile($v)){
				if($this->saveFile($v)){
					$v['savePath']=$this->destination;
					$this->uploadInfo[$k]=$v;
				}
			}
		}
		return $this->uploadInfo;
	}
	/**
	 * @param $file,文件名
	 * 设置文件的完整保存路径,如果没有设置保存文件名，则文件名为md5(microtime()),再加上其扩展名
	 */
	function destination($file){
		$exts=$this->getExts($file);
		$fileName = $this->fileName ? $this->fileName : md5(microtime()); 
		return rtrim($this->savePath,'/').'/'.$fileName.".".$exts;
	}
	/**
	 * @param $file, 一维数组
	 * 保存文件到指定的路径(包括文件名)
	 */
	function saveFile($file){
		$this->destination=$this->destination($file);
		//echo $this->destination.'<hr/>';
		//echo $file['tmp_name'];
		if(!move_uploaded_file($file['tmp_name'],$this->destination)){
			$this->error="上传文件保存错误";
			return false;
		}
		return true;
	}
	/**
	 * @param $file , 一维数组
	 * 对上传文件进行校验 
	 */
	function checkFile($file){
		if(!$this->checkExts($file)){
			$this->error='不允许上传此扩展名的文件';
			return false;
		}
		if(!$this->checkType($file)){
			$this->error="不允许上传此类型的文件";
			return false;
		}
		if($file['error']!=0){
			$this->error=$this->error($file['error']);
			return  false;
		}
		if($this->maxSize != -1 && $this->maxSize < $file['size']){
			$this->error='上传文件大小超出了限制';
			return false;
		}
		if(!is_uploaded_file($file['tmp_name'])){
			$this->error='非法上传文件';
			return  false;
		}
		return true;
	}
	/**
	 * @param void
	 * 处理$_FILES数组,过滤掉无用的上传,将所有的上传文件放到一个二维数组中
	 * 目前不支持上传文件名为数组的形式 
	 */
	function dealFiles(){
		
		if(!$_FILES){
			return false;
		}
		$files=array();
		foreach($_FILES as $k => $v){
			if(is_array($v) && $v['name'] && $v['size']){
				$files[$k]=$v;
			}
		}
		return $files;
	}
	/**
	 * @param $filename,文件名
	 * 校验文件的扩展名 
	 */
	function checkExts($filename){
		$exts=$this->getExts($filename);
		if(in_array($exts,$this->allowExts)){
			return true;
		}
		return false;
	}
	/**
	 * @param $filename,文件名称
	 * 获取文件的扩展名并返回 
	 */
	function getExts($filename){
		$pathinfo=pathinfo($filename['name']);
		return strtolower($pathinfo['extension']);
	}
	/**
	 * @param $errorNo, 错误代码
	 * 根据错误代码设置错误信息 
	 */
	function error($errorNo){
         switch($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                $this->error = '文件只有部分被上传';
                break;
            case 4:
                $this->error = '没有文件被上传';
                break;
            case 6:
                $this->error = '找不到临时文件夹';
                break;
            case 7:
                $this->error = '文件写入失败';
                break;
            default:
                $this->error = '未知上传错误！';
        }
        return ;
    }
    /**
     * @param $all,一维数组,like array('jpg','jpeg','gif')
     * 设置允许上传文件的扩展名 
     */
	function setAllowExts($allow=array()){
		$this->allowExts=$allow;
	}
	/**
	 * @param void
	 * 获取上传文件的信息 
	 */
	function getUploadInfo(){
		return  $this->uploadInfo;
	}
	/**
	 * @param $size ,上传文件的大小
	 * 设置上传文件的大小 
	 */
	function setMaxSize($size){
		$this->maxSize=$size;
	}
	/**
	 * @param $allowType, like array('application/octet-stream')
	 * 设置允许上传文件的类型 
	 */
	function setAllowType($allowType=array()){
		$this->allowType=$allowType;
	}
	/**
	 * @param $file , 一维数组 ,like array('type'=>'application/octet-stream',...)
	 * 获取上传文件的类型
	 */
	function getType($file = array()){
		return $file['type'];
	}
	/**
	 * @param $file , 一维数组 ,
	 * 检验文件上传文件类型是否符合要求，如果$allowType为空，允许所有类型的文件 
	 */
	function checkType($file){
		if(empty($this->allowType)){
			return true;
		}else{
			$type=$this->getType($file);
			if(in_array($type,$this->allowType)){
				return true;
			}
			return false;
		}
	}
	/**
	 * @param $fileName,文件名(不包括扩展名)
	 * 设置保存文件名 
	 */
	public function setFileName($fileName){
		$this->fileName = $fileName;
	}
	/**
	 * @param void
	 * return String, Error Message 
	 */
	function getErrorMsg(){
		return $this->error;
	}
}
?>