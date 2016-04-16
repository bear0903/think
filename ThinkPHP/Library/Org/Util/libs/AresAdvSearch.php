<?php
/*
 *  进阶查询 Base Class
 *  create by Dennis 20090701
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresAdvSearch.php $
 *  $Id: AresAdvSearch.php 3524 2013-09-03 03:39:59Z dennis $
 *  $Rev: 3524 $
 *  $Date: 2013-09-03 11:39:59 +0800 (周二, 03 九月 2013) $
 *  $LastChangedDate: 2013-09-03 11:39:59 +0800 (周二, 03 九月 2013) $
 ****************************************************************************/

class AresAdvSearch {
	private   $_appId;
	protected $_companyId;
	protected $_userSeqno;
	protected $_isLayoutAllow = false;
	/**
	 * 所有栏位
	 *
	 * @var array
	 */
	protected $_defLayoutCols;

	/**
	 * 设定为隐藏的栏位
	 *
	 * @var array
	 */
	private   $_hiddenCols;
	private   $_orderByCol;
	private   $_orderByType;

	/**
	 * 设定为显示的栏位
	 *
	 * @var array
	 */
	private   $_displayCols;
	private   $_langcode;
	protected $_db;
	protected $_pagerToolbar;
	//private   $_pageIndex;

	/**
	 * 多语代码
	 *
	 * @var string
	 */
	private   $_multiLangKey;

	/**
	 * construct of class
	 *
	 * @param string $appid     程式代码
	 * @param string $companyid 公司代码
	 * @param string $usersenqo 使用者代码流水号
	 * @author Dennis 20090701
	 */
	public function __construct($appid,$companyid,$usersenqo)
	{
		global $g_db_sql;
		$this->_db        = $g_db_sql;
		$this->_appId     = $appid;
		$this->_companyId = $companyid;
		$this->_userSeqno = $usersenqo;
		$this->_langcode  = $GLOBALS['config']['default_lang'];
		//$this->_db->debug = 1;
		// auto init layout columns if allow layout
		if ($this->_isLayoutAllow){
			$this->_multiLangKey = $this->_getMultiLanKey();
			$this->_initLayoutCols();
		}
	}// end class constructor

	/**
	 * 取得当前使用者已保存的查询清单的 SQL
	 * 因为传给 parserSelect(), 所以这里只传 SQL 就 OK
	 * @return string
	 * @author Dennis 20090701
	 */
	public function getFilterList()
	{
		 $sql = <<<eof
		 select search_seq,search_name
 		   from ehr_md_search
 		  where company_seqno = '$this->_companyId'
 		    and user_seqno    = '$this->_userSeqno'
 		    and program_name  = '$this->_appId'
eof;
		//echo $sql.'<hr/>';
		return $sql;
	}// end funciton _getFilterList()


	/**
	 * 根据查询器的ID取得查询器查询条件细项
	 *
	 * @param number $filter_seqno
	 * @return array
	 * @author Dennis 20090701
	 */
	public function getFilterDetail($filter_seqno)
	{
		$sql = <<<eof
			select arr_key   as col_name,
			       arr_value as col_val,
			       tablename as table_name,
			       dis_val   as dis_val
			  from ehr_md_search_detail
			 where search_seq = :filter_seqno
eof;
		$this->_db->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs = $this->_db->GetArray($sql,array('filter_seqno'=>$filter_seqno));
		$c = count($rs);
		// must be an array
		$detail_items = array();
		if($c>0)
		{
			foreach ($rs as $val)
			{
				if (empty($val['DIS_VAL']))
				{
					// 数据库栏位
					$k = $val['TABLE_NAME'].'00'.strtoupper($val['COL_NAME']);
				}else{
					// 查询栏位
					$k = $val['TABLE_NAME'].'11'.strtoupper($val['COL_NAME']);
				}// end if
				$detail_items["$k"] = $val['COL_VAL'];
			}// end for each
		}// end if
		//pr($detail_items);
		return $detail_items;
	}// end _getFilterDetail()

	private function _insertFilterMaster($filter_desc)
	{
		$sql = <<<eof
			insert into ehr_md_search
			  (search_seq, company_seqno, user_seqno, program_name, search_name)
			values
			  (ehr_md_search_s.nextval,:company_id, :user_seqno, :program_name, :filter_name)
eof;
        	return $this->_db->Execute($sql,array('company_id'=>$this->_companyId,
        										  'user_seqno'=>$this->_userSeqno,
        										  'program_name'=>$this->_appId,
        										  'filter_name'=>$filter_desc));
	}

	/**
	 * 查询器的条件细项
	 *
	 * @param array $detail_items
	 * @return mixed  boolean/error message
	 * @author Dennis 20090701
	 */
	private function _insertFilterDetail($detail_items)
	{
		$sql = <<<eof
			insert into ehr_md_search_detail
			  (search_seq, arr_key, arr_value, tablename,dis_val)
			values
			  (ehr_md_search_s.currval, :col_name, :col_val, :table_name,:dis_val)
eof;
		$c = count($detail_items);
		$this->_db->BeginTrans();
		for ($i=0; $i<$c; $i++)
		{
			$r = $this->_db->Execute($sql,array('col_name'=>$detail_items[$i]['colname'],
												'col_val'=>$detail_items[$i]['colval'],
												'table_name'=>$detail_items[$i]['tablename'],
												'dis_val'=>$detail_items[$i]['dis_val']));
			if ($r){
				continue;
			}else{
				return $this->_db->ErrorMsg();
			}// end if
		}// end for loop
		return $r;
	}// end _insertFilterDetail()

	/**
	 * 保存查询器
	 *
	 * @param string $filter_desc
	 * @param array $filter_detail_items
	 * @return mixed  boolean/error message
	 * @author Dennis 20090701
	 */
	public function saveFilter($filter_desc,$filter_detail_items)
	{
		// insert master/detail
		// 同时成功,同时失败
		//$this->_db->debug = 1;
		$r1 = false;
		$this->_db->BeginTrans();
		$r = $this->_insertFilterMaster($filter_desc);
		if($r){
			$r1 = $this->_insertFilterDetail($filter_detail_items);
		}else{
			return $r;
		}// end if
		if($r1){
			$this->_db->CommitTrans(true);  // do commit
		}else{
			$this->_db->RollbackTrans();
			return $r1;
		}// end if
		return $r1;
	}// end saveFilter()

	private function _deleteFilterMaster($filter_seqno)
	{
		$sql = <<<eof
		delete ehr_md_search where search_seq = :filter_seqno
eof;
		return $this->_db->Execute($sql,array('filter_seqno'=>$filter_seqno));
	}// end _deleteFilterMaster()

	private function _deleteFileterDetail($filter_seqno)
	{
		$sql = <<<eof
		delete ehr_md_search_detail where search_seq = :filter_seqno
eof;
		return $this->_db->Execute($sql,array('filter_seqno'=>$filter_seqno));
	}// end _deleteFileterDetail()

	private function _deleteFilterLayout()
	{
		// 暂未用到
	}// end _deleteFilterLayout

	/**
	 * 删除查询器
	 *
	 * @param number $filter_seqno
	 * @return mixed boolean/error msg
	 * @author Dennis 20090702
	 */
	public function deleteFilter($filter_seqno)
	{
		// delete master/detail
		// 同时成功,同时失败
		$r1 = false;
		$this->_db->BeginTrans();
		$r = $this->_deleteFilterMaster($filter_seqno);
		if($r){
			$r1 = $this->_deleteFileterDetail($filter_seqno);
		}else{
			return $this->_db->ErrorMsg();
		}//end if
		if($r1){
			$this->_db->CommitTrans(true);   // do commit
		}else{
			$this->_db->RollbackTrans();
			return $this->_db->ErrorMsg();
		}// end if
		return $r1;
	}// end deleteFilter()

	/**
	 * Help Function
	 * 初始化设置布局的栏位名称(根据多语)
	 *
	 */
	protected function _getColMutiLang()
	{
		$sql = <<<eof
				select distinct name, value
		          from app_muti_lang
		         where program_no = :program_no
		           and lang_code  = :lang_code
		           and type_code  = 'IT'
eof;
	    //$this->_db->debug = true;
		$this->_db->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_db->GetArray($sql,array('program_no'=>$this->_multiLangKey,
											   'lang_code' =>$this->_langcode));
	}// end _initLayoutColumn()

	/**
	 * Help method
	 * 重组可布局栏位的 array
	 * @param array $colstitle
	 */
	private function _setLayoutColTitle($colstitle)
	{
		//pr($colstitle);
		$c = count($colstitle);
		for ($j = 0; $j<count($this->_defLayoutCols); $j++)
		{
			for ($i=0; $i<$c; $i++)
			{
				if (strtoupper($this->_defLayoutCols[$j][1]) == $colstitle[$i]['NAME']){
					$this->_defLayoutCols[$j][1] = $colstitle[$i]['VALUE'];
				}// end if
			}// end for loop
		}// end for loop
	}// end _setLayoutColTitle()

	private function _initLayoutCols()
	{
		//$colsTitle = $this->_getColMutiLang();
		$this->_setLayoutColTitle($this->_getColMutiLang());
		$this->_setLayoutCols();
	}// end _initLayoutCols()

	/**
	 * 取得可布局栏位当前语言的栏位标题
	 *
	 * @return array
	 * @author Dennis 20090702
	 */
	function getDefaultLayoutCols()
	{
		return $this->_defLayoutCols;
	}// end getDefaultLayoutCols()

	/**
	 * set 显示/隐藏的栏位清单
	 *
	 * @param string $displayed Y_显示栏位 N_隐藏栏位
	 * @return void
	 * @author Dennis 20090702
	 */
	private function _setLayoutCols()
	{
		$sql = <<<eof
			select col_name,
			       col_title,
			       displayed,
			       display_index,
			       orderby_index,
			       is_order_by_col,
			       order_by_type
			  from ehr_md_search_display_column
			 where program_name = :program_name
			   and user_seqno   = :user_seqno
			 order by display_index
eof;
		//$this->_db->debug  = true;
		$rs = $this->_db->GetArray($sql,array('program_name'=>$this->_appId,
											  'user_seqno'=>$this->_userSeqno));
		// 如果没有显示栏位的设定,预设显示所有的栏位
		$c = count($rs);
		if ($c>0)
		{
			$c1 = count($this->_defLayoutCols);
			for ($i=0; $i<$c; $i++)
			{
				for ($j=0; $j<$c1;$j++)
				{
					if (strtolower($rs[$i]['COL_NAME']) == strtolower($this->_defLayoutCols[$j]['COL_NAME']))
					{
						$this->_defLayoutCols[$j]['DISPLAYED']     = $rs[$i]['DISPLAYED'];
						$this->_defLayoutCols[$j]['DISPLAY_INDEX'] = $rs[$i]['DISPLAY_INDEX'];
						if ('Y' == $this->_defLayoutCols[$j]['DISPLAYED'])
						{
							$this->_displayCols[] = $this->_defLayoutCols[$j];
						}else{
							$this->_hiddenCols[] = $this->_defLayoutCols[$j];
						}// end if
						// set order by column and order by type (desc/asc)
						if ('Y' == $rs[$i]['IS_ORDER_BY_COL'])
						{
							$this->_orderByCol  = $rs[$i]['COL_NAME'];
							$this->_orderByType = $rs[$i]['ORDER_BY_TYPE'];
						}//end if
					}// end if
				}// end for loop
			}// end for loop
		}else{
			$this->_displayCols = $this->_defLayoutCols;
		}// end if
	}// end getLayoutCols()
	/**
	 * 取得布局设定中隐藏栏位
	 *
	 * @return array
	 * @author Dennis 20090706
	 */
	public function getHiddenCols()
	{
		//pr($this->_hiddenCols);
		return $this->_hiddenCols;
	}// end getHiddenCols()

	/**
	 * 取得布局设定中显示栏位
	 * @return array
	 * @author Dennis 20090708
	 */
	public function getDisplayCols()
	{
		//pr($this->_displayCols);
		return $this->_displayCols;
	}// end getDisplayCols()

	/**
	 * 取得所有查询的栏位
	 * @return array
	 * @author Dennis 20090708
	 */
	public function getAllCols()
	{
		return $this->_defLayoutCols;
	}// end getAllCols()

	/**
	 * 取得布局设定的排序栏位
	 * @return string
	 * @author Dennis 20090708
	 */
	public function getOrderByCol()
	{
		return $this->_orderByCol;
	}/// end getOrderByCol()
	/**
	 * 取得布局设定的排序方式 asc,desc
	 * @return string
	 * @author Dennis 20090708
	 */
	public function getOrderByType()
	{
		return $this->_orderByType;
	}// end getOrderByType()

	/**
	 * 保存布局, 如果有资料就 update, 无资料就 insert
	 * 利用 ADODB 的 replace
	 *
	 * @param array $arrFields key=>value 格式的 array
	 * @return mixed >0 insert/update 0 on failure,
	 * 								  1 if update statement worked
	 * 								  2 if no record was found and the insert was executed successfully.
	 * @author Dennis 20090702
	 */
	public function saveLayout($arrFields)
	{
		/*
		# single field primary key
	    $ret = $db->Replace('atable',
	    array('id'=>1000,'firstname'=>'Harun','lastname'=>'Al-Rashid'),
	        'id',
	        'firstname',$autoquote = true);
	    # generates UPDATE table SET
	    # firstname='Harun',lastname='Al-Rashid'
	    # WHERE id=1000
	    # or INSERT INTO atable (id,firstname,lastname)
	    # VALUES (1000,'Harun','Al-Rashid')

	    # compound key
	    $ret = $db->Replace('atable2',
	    array('firstname'=>'Harun','lastname'=>'Al-Rashid', 'age' => 33, 'birthday' => 'null'),
	    array('lastname','firstname'),
	        'firstname',$autoquote = true);

	    # no auto-quoting
	    $ret = $db->Replace('atable2',
	    array('firstname'=>"'Harun'",'lastname'=>"'Al-Rashid'", 'age' => 'null'),
	    array('lastname','firstname'),
	        'firstname');
		*/
		$tableName = 'ehr_md_search_display_column';
		$keyCols   =  array('program_name','user_seqno','col_name','search_seq');

		$defColsVals = array('program_name'=>$this->_appId,
							 'user_seqno'  =>$this->_userSeqno);
		foreach ($arrFields as $val)
		{
			$arrf = array('col_name'=>$val[0],
						  'col_title'=>$val[0].'_LABEL',
						  'displayed'=>$val[1],
						  'display_index'=>$val[2],
						  'is_order_by_col'=>$val['is_ob_col'],
						  'order_by_type'=>$val['orderbytype']);
			//$this->_db-> debug = 1;
			$result = $this->_db->Replace($tableName,
										  array_merge($arrf,$defColsVals),
										  $keyCols,
										  true);
			if ($result == '0') return $this->_db->ErrorMsg();
		}// end if
		return true;
	}// end saveLayout()

	/**
	 * Help Function
	 * 根据 scriptname 在 config 文件找出其对应的程式 ID
	 * @return string
	 * @author Dennis 20090702
	 */
	private function _getMultiLanKey()
	{
        return $this->_appId; // modify by dennis 2011-09-26 因为 $GLOBALS['config']['md_app_map']有修改
		//return  array_search($this->_appId,$GLOBALS['config']['md_app_map']);
		/*
		if (!$key)
		{
			trigger_error('No multiple language value found. application id:'.$this->_appId,E_USER_ERROR);
		}
		return $key;
		*/
	}// end _getMultiLanKey()

	/**
	 * 取得实际显示的 db filed name
	 *
	 * @return string
	 * @author Dennis 20090702
	 */
	protected function _getRealCols()
	{
		$c = count($this->_displayCols);
		$realCols = '';
		for ($i=0; $i<$c; $i++)
		{
			$realCols .= $this->_displayCols[$i][0];
			$realCols .= ',';
		}// end for loop
		return $realCols;
	}// end _getRealCols()

	/*
	 * 取得查询结果的 Toolbar
	 */
	public function getPagerToolbar()
	{
		return $this->_pagerToolbar;
	}// end getPagerToolbar()

	/**
	 * 把员工代码条件组成 where condition 中 in(xx,xx) 的 string
	 * @param $empseqnos	array 员工代码流水号数组
	 * @return string
	 * @author Dennis 20090708
	 */
	protected function _getEmpSeqNoSet($empseqnos)
	{
		$empseqno_str = '';
		foreach ($empseqnos as $arr)
		{
			foreach ($arr as $val) {
				$empseqno_str .= $val[0].',';
			}// end foreach
		}// end foreach()
		//echo $empseqno_str;
		// 去除重复后重组
		$rs = array_unique(split(',',substr($empseqno_str,0,-1)));
		//pr($rs);
		$empseqno_str = '';
		foreach ($rs as $val)
		{
			$empseqno_str .= $val.',';
		}
		return substr($empseqno_str,0,-1);
	}// end _setEmpSeqNoSet()

	/**
	 * help function
	 * @param $totalrows number  资料总笔数
	 * @param $pagesize  number  每页显示的资料笔数
	 * @return void
	 * @author Dennis 20090709
	 */
	protected function _setPagerToolbar($totalrows,$pagesize)
	{
		if($totalrows > $pagesize){
			include_once 'GridView/Data_Paging.class.php';
			$pager = new Data_Paging(array('total_rows'=>$totalrows,
										   'page_size'=>$pagesize));
	        $pager->openAjaxMode('gotopage');
	        $this->_pagerToolbar = $pager->outputToolbar(2);
		}// end if
	}// end _setPagerToolbar()

	/**
	 * 从Post 的变量中解析出查询条件
	 *
	 * @param array $cols_vals
	 * @return array
	 * @author Dennis 20090709
	 */
	public function getSWhere($cols_vals)
	{
		//pr($cols_vals);
		$qwhere     = '';
		$i = 0;
		foreach ($cols_vals as $key => $val)
		{
			$p = stripos($key,'00');
			$p1 = stripos($key,'11');
			if (($p>0 || $p1>0) && !empty($val))
			{
				if ($p>0)
				{
					$qwhere[$i]['colname']   = strtolower(substr($key,$p+2));
					$qwhere[$i]['colval']    = $val;
					$qwhere[$i]['tablename'] = substr($key,0,$p);
					$qwhere[$i]['dis_val']   = '';
				}
				if ($p1>0){
					$qwhere[$i]['colname']   = strtolower(substr($key,$p1+2));
					$qwhere[$i]['colval']    = $val;
					$qwhere[$i]['tablename'] = substr($key,0,$p1);
					$qwhere[$i]['dis_val']   = $val;
				}
				$i++;
			}// end if
		}// end foreach
		return $qwhere;
	}// end getSWhere()

	/**
	 * register current login user information
	 * for get privileges
	 * @return void
	 * @author Dennis 20090702
	 */
	protected function _registerUserInfo()
	{
		$stmt = 'begin pk_erp.p_set_segment_no(:companyid);pk_department.p_set_base_date(sysdate);pk_erp.p_set_username(:user_seq_no);end;';
		$this->_db->Execute($stmt, array ('companyid'   => $this->_companyId,
										  'user_seq_no' => $this->_userSeqno));
	}// end _registerUserInfo()

}// end class AresAdvSearch

