<?php


//add by bear[2016/3/16]
//thinkPHP实现移动端访问自动切换主题模板

	$DBConn;
	$Smarty;

	function _initialize(){
		//移动设别浏览，则切换模板
		if(ismobile()){
			//设置默认默认主题为Mobile
			C('DEFAULT_THEME','Mobile');
		}
		//更多我的代码

	}

	
	function _recombineArray($data)
	{
		$result = false;
		if (is_array($data))
		{
			$cnt = count($data);
			for ($i=0; $i<$cnt; $i++)
			{
				//$result[strtoupper($data[$i][0])] = $data[$i][1];
				$result[$data[$i][0]] = $data[$i][1]; // remove the strtoupper() by dennis 2014/01/03 鎶婂�艰浆鎴愬ぇ鍐欎笉鍚堢悊
			}// end loop
		}// end if
		return $result;
	}

	/* function ParseSelect($select_name,$data_source,$s_option_name,$s_option_value = '',$cache_data = 'N')
	{
		$result = $data_source; // default pass an array
		if (!empty($data_source) && is_string($data_source)){
			// change fetch mode to default, for get integer base index result
			//$DBConn->SetFetchMode(0);
			if ($cache_data == 'N'){
				$result = $this->DBConn->GetArray($data_source);
			}else{
				$result = $this->DBConn->CacheGetArray(3600,$data_source);
			}
		}
		//pr($this->_recombineArray($result));
		$this->Smarty->assign($select_name,$this->_recombineArray($result));
		$this->Smarty->assign($s_option_name,$s_option_value);
	} */