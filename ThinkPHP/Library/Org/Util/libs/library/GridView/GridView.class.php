<?php
/**
 * eHR Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.areschina.com/license/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@areschina.com so we can send you a copy immediately.
 *
 * @category   eHR
 * @package    GridView
 * @subpackage no
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @version    $Id: GridView.class.php 2007-12-25 02:09:30 PM Dennis$
 */

/**
 * GridView 基类
 * @category   eHR
 * @package    GridView
 * @subpackage no
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
require_once 'Base.class.php';
class GridView extends Base {
	const TEXT_FIELD		= 'text';
	const CHECKBOX_FIELD	= 'checkbox';
	const HYPERLINK_FIELD	= 'hyperlink';
	const IMAGE_FIELD		= 'image';
	// 暂时未用到
	//const BUTTON_FIELD    = 'button';
	//const COMMAND_FIELD   = 'command';
	//const LIST_FIELD      = 'list';

	const REMOET_SORT		= 'remote';
	const LOCAL_SORT		= 'local';
	const DEFAULT_COL_WIDTH = 80;

	/**
	 * 序号栏位宽度
	 * @var int
	 */
	const SEQNO_COL_WIDTH   = 25;

	/**
	 * GridView 的 ID
	 * @var string
	 */
	public $id;

	/**
	 * GridView Title
	 * @var string
	 * @access public
	 */
	public $title;

	// 暂时未用到
	//public $name;
	/**
	 * 每页显示记录笔数, 预设值 10
	 * @var int
	 * @access public
	 */
	private $_pageSize = 10;

	/**
	 * 是否允许排序,对整个 GridView的设定
	 * @var boolean 预设允许排序
	 * @access public
	 */
	public $isSortable = true;

	/**
	 * 排序方式,remote_重新到 DB查询排序后显示, local_只排序画面上显示的记录
	 * 预设 remote 方式, 暂时只支持 remote sorting
	 * @var string
	 * @access public
	 */
	public $sortMode = 'remote';

	/**
	 * 是否显示分页工具列, 预设显示
	 * @var boolean
	 * @access public
	 */
	public $isPaging = true;

	/**
	 * 是否自动产生出标题栏位
	 * @var boolean
	 * @access public
	 */
	//public $isAutoGenerateColumns = true;

	/**
	 * 是否显示分页信息在GridView顶部
	 * @var boolean
	 * @access public
	 */
	public $headerPaging = false;

	/**
	 * 是否显示分页信息在GridView底部
	 * @var boolean
	 * @access public
	 */
	public $footerPaging = true;

	/* GridView Style Setting Properties */
	public $uiStyle             = 'blue';		// ui style (ext based)
	public $pagingTheme         = 4; 			// 分页toolbar 的风格
	public $width;			// GridView Width
	public $height;			// GridView Height
	public $gridViewStyle       = 'gridview';	// GridView Style
	public $rowHeight           = 17;			// default row height 17px
	public $alternatingRowStyle = 'alternating-row-style';
	public $mouseoverRowStyle   = 'mouseover-row-style';
	public $noramlRowStyle      = 'normal-row-style';
	public $titleStyle          = 'title-style';	// GridView 标题
	public $headerRowStyle      = 'header-style';	// gridview 栏位标题列 style
	public $selectedRowStyle    = 'selected-row-style';

	protected $_allowPrint		= false;
	protected $_allowExport		= false;
	/**
	 * 是否显示 row number
	 * @var boolean
	 */
	public $enableRowNum		= true;

	/**
	 * 是否允许 row 交替颜色
	 * @var boolean
	 * @access public
	 */
	public $isAlternatingColor  = false;

	/**
	 * row 交替颜色深色背景色色码 (#xxxxxx)
	 * @var string
	 * @access public
	 */
	public $alternatingBgcolor  = '#e1e1e1';

	/**
	 * row 交替颜色深色字体颜色 (#xxxxxx)
	 * @var string
	 * @access public
	 */
	public $alternatingFontColor = '#000000';

	/* end GridView Style Setting Properties */
	// 以下参数暂不开放
	//public  $isEditable  = false;
	//public  $isDeletable = false;
	//public  $isUpdatable = false;
	/**
	 * 是否 Highlight 选中的 row
	 * @var boolean
	 * @access public
	 */
	public  $isSelectable = true;

	/**
	 * 是否处理 Mouse 事件(onmouseover/onmouseout/onclick)
	 * @var boolean
	 * @access public
	 */
	public  $isHandleMouse = true;

	/**
	 * GridView 的资料来源
	 * @var object
	 * @access private
	 */
	private $_dataSource;

	/**
	 * GridView 属性组成的数组
	 * @example
	 *  $config = array('is_sortable'=>true,'page_size'=20)
	 * @var array
	 * @access private
	 */
	private $_config;

	/**
	 * GridView 栏位属性组成的数组
	 * @example
	 *  $config = array('ID'=>array('width'=>60,
	 *			                    'align'=>'right',
	 *			                    'bgcolor'=>'red');
	 * @var array
	 * @access private
	 */
	private $_columnConfig;

	/**
	 * 栏位名称数组, 数字为下标
	 * @var array
	 */
	private $_columns;

	/**
	 * 排序方式 desc,asc
	 * @var string
	 * @access private
	 */
	private $_sortDirect = 'desc';

	/**
	 * 排序的栏位名称 (Must be database field)
	 * @var string
	 * @access private
	 */
	private $_sortKey = null;

	/**
	 * 总记录笔数
	 * @var int
	 * @access private
	 */
	private $_totalRows = 0;

	/**
	 * 实际挑到资料笔数, default 0
	 * @var int
	 * @access private
	 */
	private $_numRows = 0;

	/**
	 * 栏位个数
	 * @var int
	 * @access private
	 */
	private $_numCols = 0;

	/**
	 * 页码索引
	 * @var int
	 * @access public
	 */
	private $_pageIndex = 1;

	private $_gridViewPath;

	private $_appId;

	/**
	 * GridView Constructor
	 * @param ADODB  $db_adapter  type of abstract class Zend_Db_Adapter_Abstract
	 * @param string $sql			Gridview 资料来源 SQL 语句
	 * @param array  $gridview_config Gridview 属性组成的数组. 以属性为下标. 如 $gridview_config = array('allowPaging'=>'1','pageSize'=>20,...)
	 * @param array  $column_config
	 * @author Dennis
	 */
	public function __construct($db_adapter,
								$sql,
								$gridview_config = null,
								$column_config = null)
	{
		$this->_init($db_adapter,$sql,$gridview_config,$column_config);
	}// end class constructor __construct()

	/**
	 * 初始化 GridView, 分别调用
	 *  _initGridView
	 *  _initGridViewColumn
	 *  _initDataSource
	 *
	 * @param  Zend_Db_Adapter_Abstract $db_adapter instance of class Zend_Db_Abstract
	 * @param  string $sql              gridview data sql statement
	 * @param  array  $gridview_config  gridview properties array
	 * @param  array  $column_config    header columns properties array
	 * @access private
	 * @author Dennis
	 */
	private function _init($db_adapter,$sql,$gridview_config,$column_config)
	{
		//echo dirname(__FILE__);
		$this->_initGridView($gridview_config);
		$this->_initGridViewColumn($column_config);
		$this->_initDataSource($db_adapter,$sql);
		// resize gridview width if the width is null after initilization
		if (!isset($this->width))
		{
			$this->width = $this->_gridViewWidth();
		}// end if
	}// end _init();

	/**
	 * 初始化 GridView 相关属性
	 * @param array $config GridView 相关属性组成的数组
	 * @return void
	 * @access private
	 * @author Dennis
	 */
	private function _initGridView($config)
	{
		//dd($config);
		// get unique gridview id
		$this->id = getGUID();
		// init path
		$this->_gridViewPath = dirname(dirname($_SERVER['PHP_SELF'])).'/libs/library/GridView/';
		// 遍历属性
		if (is_array($config))
		{
			//dd($config);
			foreach($config as $key=>$value)
			{
				if (isset($value))
				{
					switch($key)
					{
						case 'pageSize': // display rows per page
							$this->_pageSize = intval($value) > 0 ? $value : $this->_pageSize;
							break;
						case 'pageIndex': // index of page
							$this->_pageIndex = intval($value) > 0 ? $value : $this->_pageIndex;
							break;
						case 'sortDirect': // column sort direction
							$this->_sortDirect = (strtolower($value) === 'asc' ? 'desc' : 'asc');
							break;
						case 'sortKey':
							$this->_sortKey = $value;
							break;
						case 'allowSorting':
							$this->isSortable = $value;
							break;
						case 'sortMode':
							$this->sortMode = $value;
							break;
						case 'allowSelected':
							$this->isSelectable = $value;
							break;
						case 'handleMouse':
							$this->isHandleMouse = $value;
							break;
						case 'isPaging':
							$this->isPaging = $value;
							break;
						case 'headerPaging':
							$this->headerPaging = $value;
							break;
						case 'footerPaging':
							$this->footerPaging = $value;
							break;
						case 'pagingTheme':
							$this->pagingTheme = $value;
							break;
						case 'isAlternatingColor':
							$this->isAlternatingColor = $value;
							break;
						case 'alternatingRowStyle':
							$this->alternatingRowStyle = $value;
							break;
						case 'alternatingBgColor':
							$this->alternatingBgcolor = $value;
							break;
						case 'alternatingFontColor':
							$this->alternatingFontColor = $value;
							break;
						case 'gridViewStyle':
							$this->gridViewStyle = $value;
							break;
						case 'headerRowStyle':
							$this->headerRowStyle = $value;
							break;
						case 'selectedRowStyle':
							$this->selectedRowStyle = $value;
							break;
						case 'width':
							$this->width = intval($value);
							break;
						case 'height':
							$this->height = $value;
							break;
						case 'allowExp':
							$this->_allowExport = $value;
							break;
						case 'allowPrint':
							$this->_allowPrint = $value;
							break;
						case 'ui_style':
							$this->uiStyle = $value;
							break;
						/* add by dennis for compability old version eHR*/
                        case 'scriptname':
                            $this->_appId = $value;
                            break;
						default:break;
					}// end switch
				}// end if
			}// end foreach
		}// end if
		//echo $this->alternatingBgcolor.'<br>';
	}// end _initGridView()

	/**
	 * 如果没有设定 GridView 宽度,计算出预设的宽度
	 * help function of _init()
	 * @param no
	 * @return int
	 * @access private
	 * @author Dennis
	 */
	private function _gridViewWidth()
	{
		$width = 0;
		foreach($this->_columns as $col_name)
		{
			if (isset($this->_columnConfig["$col_name"]['width']))
			{
				$width += intval($this->_columnConfig["$col_name"]['width']);
			}else{
				$width += self::DEFAULT_COL_WIDTH;
			}// end if;
		}// end foreach
		$width += self::SEQNO_COL_WIDTH;
		return $width;
	}// end _gridViewWidth()

	/**
	 * 如果没有设定 GridView 宽度,计算出预设的宽度
	 * help function of _init()
	 * @param no
	 * @return int
	 * @access private
	 * @author Dennis
	 */
	private function _gridViewWidth1()
	{
		$width = 0;
		if (is_array($this->_columns))
		{
			foreach($this->_columns as $col_name)
			{
				if (isset($this->_columnConfig["$col_name"]['width']))
				{
					$width += intval($this->_columnConfig["$col_name"]['width']);
				}else{
					$width += self::DEFAULT_COL_WIDTH;
				}// end if;
			}// end foreach
			$width += self::SEQNO_COL_WIDTH;
		}// end if
		//echo 'width->'.$width;
		return $width;
	}// end _gridViewWidth1()

	/**
	 * 初始化GridView 栏位属性
	 * @param array $config
	 */
	private function _initGridViewColumn($config)
	{
		if (is_array($config))
		{
			$this->_columnConfig = $config;
			$this->_columns = array_keys($this->_columnConfig);
		}// end if
	}// end _initGridViewColumn()

	private function _initDataSource($db_adapter,$sql)
	{
		//$db_adapter->debug  = true;
		// set fetch mode
		$db_adapter->SetFetchMode(ADODB_FETCH_ASSOC);
		// 取得资料总笔数
		$this->_totalRows = $db_adapter->GetOne($this->_getTotalRowsSQL($sql));
		//echo '资料笔数-》'.$this->_totalRows.'<br/>';
		// 当使用者有点栏位排序时,加上 order by 语句
		//echo 'sort key -> '.$this->_sortKey;
		if (isset($this->_sortKey))
		{
			$orderby_stmt = 'order by '.$this->_sortKey.' '.$this->_sortDirect;
			$sql = $this->_getOrderBySQL($sql,$orderby_stmt);
		}// end if;

		// 设定 select limit, ADODB 会重组 sql, 达成 MySQL like Limit function
		//echo 'SQL-》'.$sql.'<br/>';
		$rs = $db_adapter->SelectLimit($sql,
									   $this->_pageSize,
									   $this->_getOffset($this->_pageIndex,$this->_pageSize));
		// 执行查询
		// echo $sql.'<br>';
		if($rs)
		{
			$this->_dataSource = $rs->GetArray();
		}// end if
		//dd($this->_dataSource);
		// 关闭数据库连结
		//$db_adapter->closeConnection();
		// 实际 select 记录笔数
		$this->_numRows = count($this->_dataSource);
		/*
		// 取得所有栏位名称
		if ($this->_numRows>0)
		{
			$this->_columns = array_keys($this->_dataSource[0]);
		}// end if
		*/
		// 清除数据库连结物件
		unset($db_adapter);

	}// end _initDataSource()

	/**
	 * 产生 GridView 外框 Header 部分
	 *
	 * @param string $title GridView 标题
	 * @return string header 部分 html code
	 * @access private
	 * @author Dennis
	 */
	private function _getHeaderBox($title)
	{
		//$w = $this->width + 31;
		//$w = $this->width;
		$h = $this->height;
		$scriptname = $_GET['scriptname'];
		$toolbar = $this->headerPaging ? $this->_setPagingToolbar() : '';
		$tb = $this->_getGVToolbar($scriptname);
		// <div style="width:{$w}px; height:{$h}px" class="x-box-$this->uiStyle">
		/*
		$header_box_html = <<<headerbox
		<div style="width:100%; height:{$h}px" class="x-box-$this->uiStyle">
		<!-- header box -->
		<div class="x-box-tl">
		<div class="x-box-tr">
		<div class="x-box-tc"></div>
		</div>
		</div>
		<!-- Header Box End -->
		<div class="x-box-ml">
		<div class="x-box-mr">
		<div class="x-box-mc">
		<!-- Grid Title -->
		<h3 style="margin:2px;padding:2px;">$tb</h3>
		<!-- Toolbar -->
		$toolbar
headerbox;*/
		//overflow-y:auto;overflow-x:auto;
$header_box_html = <<<headerbox
		<div style="width:100%; height:{$h}px" class="x-box-$this->uiStyle">
		<div style="padding:10px; margin-bottom:10px;" class="ui-widget-content ui-corner-all">
		$toolbar
headerbox;
		return $header_box_html;
	}// end _getHeaderBox();

	/**
	 * Get Gridview Toolbar
	 * @param $scriptname string
	 * @return string
	 * @author Dennis 2011-06-08
	 */
	private function _getGVToolbar($scriptname)
	{
		$html = '<a href="#" id="tb_newin"><img src="../img/pe.png" alt="在新窗口打开" title="在新窗口打开"/>新窗口打开</a>';
		if ($this->_allowPrint)
		{
			//$html .= '&nbsp;&nbsp;<a id="tb_print_direct" href="?scriptname="><img src="../img/b_print.png" alt="直接打印" title="直接打印"/>直接打印</a>';
			$html .= '&nbsp;&nbsp;<a id="tb_print" href="#"><img src="../img/b_print.png" alt="打印设置" title="打印设置"/>打印设置</a>';
		}
		if ($this->_allowExport)
		$html .= '&nbsp;&nbsp;<a href="#" id="tb_export"><img src="../img/page_excel.png"  alt="导出Excel" title="导出Excel"/>导出 Excel</a>';
		return $html;
	}

	/**
	 * 产生 GridView 外框 footer 部分 html code
	 * @return string
	 * @param no
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getFooterBox()
	{
		$toolbar = $this->footerPaging ? $this->_setPagingToolbar() : '';
		/*
		$footer_box_html = <<<footerbox
		<!-- tooblar -->
		$toolbar
			</div>
		</div>
	</div>
	<!-- Footer Box -->
	<div class="x-box-bl">
		<div class="x-box-br">
			<div class="x-box-bc"></div>
		</div>
	</div>
	<!-- Footer Box  End -->
</div>
footerbox;*/
$footer_box_html = <<<footerbox
		<!-- tooblar -->
		$toolbar
	</div>
footerbox;
		return $footer_box_html;
	}// end _getFooterBox()


	/**
	 * 设定 GridView 分页Toolbar
	 * 调用此 function 之前必须调用 init() function
	 * @access private
	 * @return string paging toolbar html code
	 * @author Dennis
	 */
	private function _getPagingToolbar()
	{
		$paging_html_code = '';
		if (intval($this->_totalRows) >0)
		{
			$DataPaging = new Data_Paging(array('total_rows'=>$this->_totalRows,
    											'page_size'=>$this->_pageSize,
    											'gridvew_id'=>$this->id));
			//add  解决公用翻页不出来
			$DataPaging->openAjaxMode('gotopage');
			$paging_html_code = $DataPaging->outputToolbar($this->pagingTheme);
			//$paging_html_code = $DataPaging->outputToolbar(2);
			//echo $this->pagingTheme;exit;
		}// end if
		return $paging_html_code;
	}// end _getPagingToolbar()

	/**
	 * 设定分页 Toolbar, 加外框
	 * @param no
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _setPagingToolbar()
	{
		$paging_toolbar_html = '';
		if ($this->isPaging)
		{
			if ($this->_totalRows > $this->_pageSize)
			{
				if ($this->headerPaging || $this->footerPaging)
				$paging_toolbar_html = '<div class="ui-widget-content" style="padding:5px;margin:5px 0px 5px 0px;text-align:right;">'.$this->_getPagingToolbar().'</div>';
			}// end if
		}// end if
		return $paging_toolbar_html;
	}// end _setPagingToolbar()

	/**
	 * 取得栏位排序指示图片(从小到大 - 向上三角符号 从大到小 - 向下三角符号)
	 * @param string $db_column_name
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getSortDirectImg($db_column_name)
	{
		$sort_flag_img = '';
		if (!empty($this->_sortDirect) &&
		!empty($this->_sortKey)    &&
		$this->_sortKey == $db_column_name)
		{
			//echo $this->_sortKey.'<br/>';
			$sort_flag_img = '<img src="'.$this->_gridViewPath.$this->_sortDirect.'.gif" alt="'.$this->_sortDirect.'" border="0"/>';
		}// end if
		//dd($sort_flag_img);
		return $sort_flag_img;
	}// end _getSortDirectImg()

	/**
	 * 取得栏位标题文字
	 * @param string $column_name 栏位名称
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getPromptText($column_name)
	{
		$prompt_text = $column_name;
		if (isset($this->_columnConfig[$column_name]['title']))$prompt_text = $this->_columnConfig[$column_name]['title'];
		return $prompt_text;
	}// end _getPromptText()

	/**
	 * 取得排序的 url,依据如下: gridview 允许排序, 栏位允许排序
	 * 不是 Zend_Column 栏位
	 * @param string $db_column_name 排序栏位的名称
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getColSortUrl($db_column_name)
	{
		$col_sort_link = $this->_getPromptText($db_column_name);
		//echo $this->_config['field']["$db_column_name"]['allow_sorting'].'<br>';
		$sortale = isset($this->_columnConfig["$db_column_name"]['allow_sorting']) ? $this->_columnConfig["$db_column_name"]['allow_sorting'] : 1;
		//print $db_column_name.'=>'.$sortale.'<br>';
		// 1.ZEND_DB_ROWNUM 是 Zend DB 自动加的seqno 栏位
		// 2.GridView 允许排序
		// 3.不是仅页面资料排序
		if ($this->isSortable &&
		strtoupper($db_column_name) !== 'ZEND_DB_ROWNUM' &&
		$sortale)
		{
			/*
			// $this->_sortDirect 在 _initGridView 中被初始化并根据所传的 asc 或 desc 被重写
			if($this->sortMode == self::REMOET_SORT)
			{
				$col_sort_link = '<a href="?sortKey='.$db_column_name.'&sortDirect='.$this->_sortDirect.'">'.$col_sort_link.$this->_getSortDirectImg($db_column_name).'</a>';
			}else{
				$col_sort_link = '<a href="#sortKey='.$db_column_name.'&sortDirect='.$this->_sortDirect.'">'.$col_sort_link.$this->_getSortDirectImg($db_column_name).'</a>';
			}// end if
            */
			/** remark by boll
			if($this->sortMode == self::REMOET_SORT)
            {
                //echo 'appid =>'.$this->_appId.'<br>';
                $col_sort_link = '<a href="?sortKey='.$db_column_name.'&sortDirect='.$this->_sortDirect.'&scriptname='.$this->_appId.'">'.$col_sort_link.$this->_getSortDirectImg($db_column_name).'</a>';
            }else{
                // 暂时不支持 local sort 所以和 remote sort 一样
                $col_sort_link = '<a href="?sortKey='.$db_column_name.'&sortDirect='.$this->_sortDirect.'&scriptname='.$this->_appId.'">'.$col_sort_link.$this->_getSortDirectImg($db_column_name).'</a>';
            }// end if
            */
			/* 解决 参数丢失的问题 begin*/
			$parentParaUrl="";
			if(!empty($_GET['empseqno'])){
				$parentParaUrl = "&empseqno=".$_GET['empseqno'];
			}

			if($this->sortMode == self::REMOET_SORT)
            {
                //echo 'appid =>'.$this->_appId.'<br>';
                $col_sort_link = '<a href="?sortKey='.$db_column_name.'&sortDirect='.$this->_sortDirect.'&scriptname='.$this->_appId.$parentParaUrl.'">'.$col_sort_link.$this->_getSortDirectImg($db_column_name).'</a>';
            }else{
                // 暂时不支持 local sort 所以和 remote sort 一样
                $col_sort_link = '<a href="?sortKey='.$db_column_name.'&sortDirect='.$this->_sortDirect.'&scriptname='.$this->_appId.$parentParaUrl.'">'.$col_sort_link.$this->_getSortDirectImg($db_column_name).'</a>';
            }// end if
            /* 解决 参数丢失的问题 end */
		}// end if
		return $col_sort_link;
	}// end _getColSortUrl()

	/**
	 * 取得 GridView 标题栏 Row
	 * @param no
	 * @return string, 标题栏组成的TR, 如 <tr><th>xxx</th><th>xxx</th><th>xxx</th></tr>
	 * @access private
	 * @author Dennis
	 */
	private function _getHeader()
	{
		$header_html = '<tr height="'.$this->rowHeight.'"'.(isset($this->headerRowStyle)? ' class="'.$this->headerRowStyle.'"' :'').'>';
		//$db_columns = array();
		// 自动产生栏位名称
		if ($this->_totalRows >0 )
		{
			foreach(array_keys($this->_columnConfig) as $value)
			{
				$header_html .= '<th nowrap>'.$this->_getColSortUrl($value).'</th>';
			}// end if;
		}// end if
		$header_html .= '</tr>';
		//echo '_getGridViewHeader is called';
		//echo $header_html;
		return $header_html;
	}// end _getGridViewHeader()

	/**
	 * 取得每一个 Cell html code
	 * @param array $config
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getCell($config)
	{
		//dd($config);
		$cell_html = '';
		if (!is_array($config)) error(__CLASS__.'::'.__FUNCTION__,'Table 单元格属性设定必须是一个数组');
		$cell_type = !isset($config['filed_type']) ? self::TEXT_FIELD : $config['filed_type'];
		switch($cell_type)
		{
			case self::TEXT_FIELD:
				$Bound_Field = new Bound_Field($config);
				$cell_html = $Bound_Field->output();
				break;

			case self::CHECKBOX_FIELD:
				$Checkbox_Field = new Checkbox_Field($config);
				$cell_html = $Checkbox_Field->output();
				break;

			case self::HYPERLINK_FIELD:
				$Hyperlink_Field = new Hyperlink_Field($config);
				$cell_html = $Hyperlink_Field->output();
				break;

			case self::IMAGE_FIELD:
				$Image_Field = new Image_Field($config);
				$cell_html =$Image_Field->output();
				break;

			default:break;
		}// end switch
		return $cell_html;
	}// end _getGridViewCell()

	/**
	 * 取得GridView Row html code 无结束的 tag <b>&lt;/tr&gt;</b>
	 * @param int $row_index table row index (tr index)
	 * @return string tr html code
	 * @access private
	 * @author Dennis
	 */
	private function _getRow($row_index)
	{
		$normal_style = $this->noramlRowStyle;

		$row_html = '<tr bgcolor="#ffffff"';
		// 设交替色
		if ($this->isAlternatingColor && intval($row_index)%2 ==1)
		{
			// 如果有设 Alternating row style 就以 style 为准,否则读背景色和前景色的设定
			$row_html .= (empty($this->alternatingRowStyle) ?
						 ' style="background:'.$this->alternatingBgcolor.'; color:'.$this->alternatingFontColor.';"':
			             ' class="'.$this->alternatingRowStyle.'"');
			$normal_style = $this->alternatingRowStyle;
		}// end if

		// 设定 row 的高度
		$row_html .= (intval($this->rowHeight)>0 ? ' hieght = "'.$this->rowHeight.'"':'');

		// 设定 Selected Row Style 事件 (必须引入 GridView.js 否则会出错)
		$row_html .= (($this->isHandleMouse && $this->isSelectable) ?
					 ' onClick="GridView_SetRowSelected(\''.$this->id.'\',this,\''.$this->selectedRowStyle.'\',\''.$normal_style.'\');" ' :
					 '');
		// 设定 Mouse over Mouse out 事件
		$row_html .= ($this->isHandleMouse ?
		             ' onmouseover="if(this.className !=\''.$this->selectedRowStyle.'\')'.
		             ' this.className = \''.$this->mouseoverRowStyle.'\'"'.
		             ' onmouseout="if(this.className !=\''.$this->selectedRowStyle.'\')'.
		             ' this.className =\''.$normal_style.'\';"' :
					 '');
		return $row_html.'>';
	}// end _getGridViewRow()

	/**
	 * include GridView 要用到的Javascript File
	 * @param string $js_dir Javascript Library 所在目录, 默认是当前目录
	 * @return string javascript import html code
	 * @access private
	 * @author Dennis
	 */
	private function _getJavascript()
	{
		return '<script type="text/javascript" src="'.$this->_gridViewPath.'GridView.js"></script>';
	}// end _getGridViewJavascript

	/**
	 * 未挑到任何资料时显示的信息
	 * @param no
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _noDataFound()
	{
		return '<div class="ui-widget">
                    <div class="ui-state-highlight ui-corner-all">
                        <p style="padding: 5px;margin:0px;">
                            <span style="float:left; margin-right: .3em;" class="ui-icon ui-icon-info"></span>No Data Found.
                        </p>
                    </div>
                </div>';
	}// end _noDataFound()

	/**
	 * 取得 tr 结束标签 <b>&lt;/tr&gt;</b>
	 * @param no
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getTrEndTag()
	{
		return '</tr>';
	}// end _getTrEndTag()

	/**
	 * 取得 GridView data table 部分
	 * @param no
	 * @return string
	 * @author Dennis
	 * @last update: 2008-5-4
	 *
	 */
	private function _getBody()
	{
		$gridview_body = '';
		if ( $this->_totalRows >0)
		{
			// 取得画面上显示资料的笔数, 在分页的情况下如果资料的笔数小于
			// pageSize 则显示的笔数为资料实际笔数
			// 如果不分页, 显示所有记录, 不考虑 pageSize 的值
			/*
			 $this->_pageSize = $this->isPaging ?
			 ($this->_pageSize >= $total_rows ? $total_rows : $this->_pageSize):
			 $total_rows;
			 */
			// 产生资料 table <tr><td>xxxx</td><td>xxxx</td><td>xxxx</td></tr>
			//dd($this->_dataSource);
			$cellConfig = array();
			for ($i=0; $i<$this->_numRows; $i++)
			{
				$gridview_body .= $this->_getRow($i);
				// 产生资料格 td 的内容
				// 根据栏位的排序输出资料
				foreach($this->_columns as $colname)
				{
					//echo $colname.'<br/>';
					$cellConfig['name'] = $colname;
					$cellConfig['db_field_value'] = $this->_dataSource[$i][$colname];
					$cellConfig['db_field_name'] = $colname;
					$cellConfig = array_merge($cellConfig,$this->_columnConfig["$colname"]);
					$gridview_body .= $this->_getCell($cellConfig);
					unset($cellConfig);
				}// end foreach
				$gridview_body .= $this->_getTrEndTag();
			}// end for loop
		}// end if
		return $gridview_body;
	}// end _getGridViewBody()

	/**
	 * 产生取资料总笔数的 sql 语句
	 * @param string $sql sql 语句
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getTotalRowsSQL($sql)
	{
		return 'select count(*) as cnt from ('.$sql.')';
	}// end _getCountRowSQL()

	/**
	 * 取得最后 order by sql statement
	 * @param string $sql
	 * @param string $orderby
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getOrderBySQL($sql,$orderby)
	{
		return 'select * from ('.$sql.')'.$orderby;
	}// end _getOrderBySQL()

	/**
	 * 取得挑资料 offset
	 * @param int $page_index  当前的页码
	 * @param int $page_size   每页显示记录数
	 * @return int
	 */
	private function _getOffset($page_index,$page_size)
	{
		return ($page_index >1 ? $page_index * $page_size-$page_size :0 );
	}// end _getOffset()

	/**
	 * 在显示 GridView 之前 Call Programmer 定义的
	 * function <b>preDispatch($func_args)</b>
	 * @param array $func_args
	 * @return void no return values
	 * @access private
	 * @author Dennis
	 */
	private function _preDispatch($func_args)
	{
		call_user_func('preDispatch',$func_args);
	}// end preDispatch

	/**
	 * 在建立每一个记录时Call Programmer 定义的
	 * function <b>onRowCreate($func_args)</b>
	 * @param array $func_args onRowCreate 的参数组成的数组
	 * @return void no return values
	 * @access private
	 * @author Dennis
	 */
	private function _onRowCreate($func_args)
	{
		call_user_func('onRowCreate',$func_args);
	}// end _onRowCreate

	/**
	 * 在显示 GridView 之后 Call Programmer 定义的
	 * function <b>postDispatch($func_args)</b>
	 * @param array $func_args
	 * @return void no return values
	 * @access private
	 * @author Dennis
	 */
	private function _postDispatch($func_args)
	{
		call_user_func('postDispatch',$func_args);
	}// end preDispatch

	/**
	 * 根据data source 产生出最后的 GridView Html Code
	 *
	 * @param array $pre_func_args  user function preDispath  参数组成的数组
	 * @param array $on_func_args   user function onCreateRow 参数组成的数组
	 * @param array $post_func_args user function postDispath 参数组成的数组
	 * @return string
	 */
	public function render($pre_func_args=null,$on_func_args=null,$post_func_args=null)
	{
		$gridview = '';
		if ($this->_numRows > 0)
		{
			// call preDispatch() function before output
			if(!empty($pre_func_args))$this->_preDispatch($pre_func_args);

			$gridview .= '<link href="'.$this->_gridViewPath.'gridview.css" rel="stylesheet" type="text/css" title="GridView" />';
			/*
			if ($this->uiStyle != 'blue')
			{
				$gridview .='<link rel="stylesheet" type="text/css" href="'.$this->_gridViewPath.'/Theme/css/xtheme-'.$this->uiStyle.'.css" />';
			}// end if
            */

			// 是否可以select row
			if ($this->isSelectable) $gridview .= $this->_getJavascript();
			// print table header
			$gridview .= $this->_getHeaderBox($this->title);
			$gridview .= '<div id="data_area"><table id="'.$this->id.'" border="0" cellpadding="0" cellspacing="0"';
			$gridview .= (!empty($this->gridViewStyle) ? ' class="'.$this->gridViewStyle.'" ':'');
			// remark by dennis 2011-06-08 for print data
			//$gridview .= (!empty($this->width) ? ' width="'.$this->width.'" ':'');
			$gridview .= (!empty($this->height) ? ' height="'.$this->height.'" ':'').'>';

			// 栏位标题 <tr><th>xxx</th><th>xxx</th><th>xxx</th></tr>
			$gridview .= $this->_getHeader();
			// gridview 数据
			$gridview .= $this->_getBody();
			// bottom 分页 toolbar
			//$gridview .= $this->footerPaging ? $paging_toolbar : '';
			$gridview .= '</table></div><br>';
			$gridview .= $this->_getFooterBox();
			if(!empty($post_func_args)) $this->_postDispatch($post_func_args);
		}else{
			$gridview = $this->_noDataFound();
		}// end if
		return $gridview;
	}// end render()

	public function dispatch($pre_func_args=null,$on_func_args=null,$post_func_args=null)
	{
		echo $this->render($pre_func_args=null,$on_func_args=null,$post_func_args=null);
	}// end dispatch()

	// add by boll 获取数据源
	public function getDataSource(){
	    return $this->_dataSource;
	}
}// end class GridView
?>