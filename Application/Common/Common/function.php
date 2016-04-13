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
	
	function GetOne($sql,$inputarr=false)
	{
		global $ADODB_COUNTRECS,$ADODB_GETONE_EOF;
		$crecs = $ADODB_COUNTRECS;
		$ADODB_COUNTRECS = false;
	
		$ret = false;
		$rs->$this->Execute($sql,$inputarr);
		if ($rs) {
			if ($rs->EOF) $ret = $ADODB_GETONE_EOF;
			else $ret = reset($rs->fields);
				
			$rs->Close();
		}
		$ADODB_COUNTRECS = $crecs;
		return $ret;
	}
	
	function Execute($sql,$inputarr=false)
	{
		if ($this->fnExecute) {
			$fn = $this->fnExecute;
			$ret = $fn($this,$sql,$inputarr);
			if (isset($ret)) return $ret;
		}
		if ($inputarr) {
			if (!is_array($inputarr)) $inputarr = array($inputarr);
				
			$element0 = reset($inputarr);
			# is_object check because oci8 descriptors can be passed in
			$array_2d = is_array($element0) && !is_object(reset($element0));
			//remove extra memory copy of input -mikefedyk
			unset($element0);
				
			if (!is_array($sql) && !$this->_bindInputArray) {
				$sqlarr = explode('?',$sql);
				$nparams = sizeof($sqlarr)-1;
				if (!$array_2d) $inputarr = array($inputarr);
				foreach($inputarr as $arr) {
					$sql = ''; $i = 0;
					//Use each() instead of foreach to reduce memory usage -mikefedyk
					while(list(, $v) = each($arr)) {
						$sql .= $sqlarr[$i];
						// from Ron Baldwin <ron.baldwin#sourceprose.com>
						// Only quote string types
						$typ = gettype($v);
						if ($typ == 'string')
							//New memory copy of input created here -mikefedyk
							$sql .= $this->qstr($v);
							else if ($typ == 'double')
								$sql .= str_replace(',','.',$v); // locales fix so 1.1 does not get converted to 1,1
								else if ($typ == 'boolean')
									$sql .= $v ? $this->true : $this->false;
									else if ($typ == 'object') {
										if (method_exists($v, '__toString')) $sql .= $this->qstr($v->__toString());
										else $sql .= $this->qstr((string) $v);
									} else if ($v === null)
										$sql .= 'NULL';
										else
											$sql .= $v;
											$i += 1;
	
											if ($i == $nparams) break;
					} // while
					if (isset($sqlarr[$i])) {
						$sql .= $sqlarr[$i];
						if ($i+1 != sizeof($sqlarr)) $this->outp_throw( "Input Array does not match ?: ".htmlspecialchars($sql),'Execute');
					} else if ($i != sizeof($sqlarr))
						$this->outp_throw( "Input array does not match ?: ".htmlspecialchars($sql),'Execute');
	
						$ret = $this->_Execute($sql);
						if (!$ret) return $ret;
				}
			} else {
				if ($array_2d) {
					if (is_string($sql))
						$stmt = $this->Prepare($sql);
						else
							$stmt = $sql;
	
							foreach($inputarr as $arr) {
								$ret = $this->_Execute($stmt,$arr);
								if (!$ret) return $ret;
							}
				} else {
					$ret = $this->_Execute($sql,$inputarr);
				}
			}
		} else {
			$ret = $this->_Execute($sql,false);
		}
	
		return $ret;
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