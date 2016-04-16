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
 * @subpackage Data_Paging
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id: Data_Paging.class.php 2797 2007-12-27 03:29:30 PM Dennis$
 */

/**
 * Help class of GridView
 *  分页类, 支持四程分页方式, 默认是 baidu,google style.
 *  @example  模式四种分页模式：
 *  require_once('../library/Data_Paging.class.php');
 *  $page=new Data_Pagging(array('total_rows'=>1000,'page_size'=>20));
 *  echo 'mode:1<br>'.$page->show();
 *  echo '<hr>mode:2<br>'.$page->show(2);
 *  echo '<hr>mode:3<br>'.$page->show(3);
 *  echo '<hr>mode:4<br>'.$page->show(4);
 *  开启AJAX：
 *  $ajaxpage = new Data_Pagging(array('total_rows'=>1000,'page_size'=>20,'is_ajax_supported'=>'ajax_page','page_name'=>'test'));
 *  echo 'mode:1<br>'.$ajaxpage->show();
 * @category   eHR
 * @package    GridView
 * @subpackage Data_Paging
 * @copyright  (C)1980 - 2007 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
require_once 'Base.class.php';
class Data_Paging extends Base
{
	// constants variables
	/**
	 * 页面默认显示记录笔数
	 * @var int
	 */
	const DEFAULT_PAGE_SIZE  = 30;
	/**
	 * 第一页的页码
	 * @var int
	 */
	const FIRST_PAGE_OFFSET  = 1;
	/**
	 * 分页风格
	 */
	const WORD_PAGING_STYLE  = 1;
	const WORD_PAGING_STYLE2 = 2;
	const WORD_PAGING_STYLE3 = 3;
	const WORD_PAGING_STYLE4 = 4;
	const SIGN_PAGING_STYLE  = 5;
	/**
	 * 空白字符
	 */
	const WHITE_SPACE = '&nbsp;';
	
	/**
	 * Paging 标签, 用来控制 URL 页. 如 xx.php?datapaging=2
	 * &lt; < &gt; >
	 * @var string
	 */
	public $pageName = 'pageIndex';
	// 分页文字标识 > < |< >| >> <<
	public $nextPageFlag = '&gt;';
	public $prePageFlag = '&lt;';
	public $firstPageFlag = '|<&lt;';
	public $lastPageFlag = '&gt;|';
	public $preBarFlag = '&lt;&lt;';
	public $nextBarFlag = '&gt;&gt;';
	public $formatLeft = '[';
	public $formatRight = ']';
	
	/**
	 * 是否支持 Ajax 分页模式
	 * @var boolean
	 * @access pubic
	 */
	public $isAjaxSupported = false;

	private $_pageBarNum = 10;
	private $_totalPages = 0;
	private $_ajaxActionName = '';
	private $_currentPageIndex = 1;
	private $_url = '';
	private $_offset = 0;

	/**
	 * Data Paging configuration
	 * @var array
	 */
	private $_config = array();

	public function __construct(array $config)
	{
		$this->_config = $config;
		$this->_init();
	}// end class constructor __construct
	
	/**
	 * 初始化分页类的相关属性 call by class consturct
	 * @param no parameters
	 * @return void no return value
	 * @access public
	 * @author Dennis
	 */
	private function _init()
	{
		if (is_array($this->_config))
		{
			if (!array_key_exists('total_rows',$this->_config)) $this->error(__FUNCTION__,'数组参数中缺少 total_rows 为下标的元素.');
			$total_rows = intval($this->_config['total_rows']);
			$page_size = (array_key_exists('page_size',$this->_config)) ?
			intval($this->_config['page_size'])            :
			self::DEFAULT_PAGE_SIZE;
				
			$current_page_index = (array_key_exists('current_page',$this->_config)) ?
			intval($this->_config['current_page'])            :
							 	  '';

			$url = (array_key_exists('url',$this->_config)) ? $this->_config['url'] : '';
		}else{
			$total_rows = $this->_config;
			$page_size = DEFAULT_PAGE_SIZE;
			$current_page_index = '';
			$url = '';
		}// end if
		if (!is_int($total_rows) || $total_rows <0) $this->error(__FUNCTION__,'总记录数:'.$total_rows.' 不是一个正整数');
		if (!is_int($page_size) || $page_size <0) $this->error(__FUNCTION__,'页面显示记录数:'.$page_size.' 不是一个正整数');
		if (!empty($this->_config['page_name'])) $this->pageName = $this->_config['page_name']; // 设置 pagename
		$this->_setCurrentPageIndex($current_page_index);
		$this->_setURL($url);

		$this->_totalPages = ceil($total_rows/$page_size);// 算出总页数
		$this->_offset = ($this->_currentPageIndex - 1) * $page_size;
		if (!empty($this->_config['ajax_action'])) $this->openAjaxMode($this->_config['ajax_action']); // 打开 Ajax 模式
	}// end _init()
	/**
	 * 打开Ajax 模式
	 * @param string $ajax_action_name
	 * @return void no return value
	 */
	public function openAjaxMode($ajax_action_name)
	{
		$this->isAjaxSupported = true;
		$this->_ajaxActionName = $ajax_action_name;
	}// end _openAjaxMode()
	
	public function getLimitstring(){
		$from = $this->_config['page_size'] * ($this->_currentPageIndex - 1);
		$to = $this->_config['page_size'] * ($this->_currentPageIndex);
		return " (ROWNUM > ".$from." and ROWNUM <= ".$to.") ";
	}
	/**
	 * 取得 "下一页" 按钮的 link html code
	 * @param string $style "下一页" 按钮风格
	 * @return string 
	 * @access public
	 * @author Dennis
	 */
	public function getNextPageButton($style='')
	{
		if ($this->_currentPageIndex <$this->_totalPages)
		{
			return $this->_getLink($this->_getURL($this->_currentPageIndex+1),$this->nextPageFlag,$style);
		}// end if
		//return '<span '.(!empty($style) ? 'class="'.$style.'"' : '').'>'.$this->nextPageFlag.'</span>';
		return $this->_getPagingSymbol($style,$this->nextPageFlag);
	}// end getNextPageButton()
	/**
	 * 取得 "上一页"按钮的 link html code
	 * @param string $style "下一页" 按钮风格
	 * @return string 
	 * @access public
	 * @author Dennis
	 */
	public function getPrePageButton($style = '')
	{
		if ($this->_currentPageIndex >1)
		{
			return $this->_getLink($this->_getURL($this->_currentPageIndex-1),$this->prePageFlag,$style);
		}
		//return '<span '.(!empty($style) ? 'class="'.$style.'"' : '').'>'.$this->prePageFlag.'</span>';
		return $this->_getPagingSymbol($style,$this->prePageFlag);
	}// end getPrePageButton()
	/**
	 * 取得 "首页"按钮的 link html code
	 * @param string $style "首页" 按钮风格
	 * @return string 
	 * @access public
	 * @author Dennis
	 */
	public function getFirstPageButton($style ='')
	{
		if ($this->_currentPageIndex == 1)
		{
			return $this->_getPagingSymbol($style,$this->firstPageFlag);
		}
		return $this->_getLink($this->_getURL(self::FIRST_PAGE_OFFSET),$this->firstPageFlag,$style);
	}// end getFirstPageButton()
	/**
	 * 取得 "尾页"按钮的 link html code
	 * @param string $style "尾页" 按钮风格
	 * @return string 
	 * @access public
	 * @author Dennis
	 */
	public function getLastPageButton($style = '')
	{
		if ($this->_currentPageIndex == $this->_totalPages)
		{
			return $this->_getPagingSymbol($style,$this->lastPageFlag);
		}
		return $this->_getLink($this->_getURL($this->_totalPages),$this->lastPageFlag,$style);
	}// end getLastPageButton()
	/**
	 * 取得分页的 toolbar 如 <u>1</u><u>2</u><u>...</u>
	 * html code
	 * @param string $style
	 * @param string $current_index_style
	 * @return string
	 * @access public
	 * @author Dennis
	 */
	public function getCurrentPagingToolBar($style='',$current_index_style = '')
	{
		$plus = ceil($this->_pageBarNum/2);
		if (($this->_pageBarNum - $plus + $this->_currentPageIndex) > $this->_totalPages)
		{
			$plus = $this->_pageBarNum - $this->_totalPages + $this->_currentPageIndex;
		}
		$start_offset = $this->_currentPageIndex - $plus + 1;
		$start_offset = $start_offset >= 1 ? $start_offset : 1;
		$result = '';
		
		// 产生分页 Toolbar
		for ($i = $start_offset; $i < $start_offset + $this->_pageBarNum; $i++)
		{
			if ($i <= $this->_totalPages)
			{
				if ($i != $this->_currentPageIndex)
				{
					$result .= $this->_getText($this->_getLink($this->_getURL($i),$i,$style));
				}else{
					$result .= $this->_getText($this->_getPagingSymbol($current_index_style,$i));
				}// end if
			}else{
				break;
			}// end if
			$result .= self::WHITE_SPACE;
		}// end for loop
		unset($start_offset);
		return $result;
	}// end getCurrentPagingToolBar()
	/**
	 * 取得跳转页面的 List 框
	 * @param no
	 * @return string
	 * @access public
	 * @author Dennis
	 */
	public function getJumpList()
	{
		//onchange="self.location.href=\''.$this->url.'\'+this.options[this.selectedIndex].value "
		$selectcss = ' style="width:40px; margin-top:2px;" ';
		if ($this->isAjaxSupported)
		{
			$result = '<select name="jump_list"'.$selectcss.' onchange="'.$this->_ajaxActionName.'(\''.$this->_url.'\'+this.options[this.selectedIndex].value); ">';
			//$link = '<a '.$style.'href="javascript:'.$this->_ajaxActionName.'(\''.$url.'\')">'.$text.'</a>';
		}else{
			$result = '<select name="jump_list"'.$selectcss.' onchange="self.location.href=\''.$this->_url.'\'+this.options[this.selectedIndex].value; ">';
			
		}// end if
		
		for ($i=1; $i<=$this->_totalPages; $i++)
		{
			if ($i == $this->_currentPageIndex)
			{
				$result .= '<option value="'.$i.'" selected>'.$i.'</option>';
			}else{
				$result .= '<option value="'.$i.'">'.$i.'</option>';
			}// end if
		}// end for loop
		unset($i);
		$result .= '</select>';
		return $result;
	}// end getJumpList()
	/**
	 * 输出分页 toolbar  html code
	 * @param int $toolbar_style 风格代码 1,2,3,4,5 分别代表队不同的分页风格
	 * @return string
	 * @access public
	 * @author Dennis
	 */
	public function outputToolbar($toolbar_style = 1)
	{
		$di_text = 'Page of ';
		$page_text = '&nbsp;';
		$total_text = 'Total ';
		$paging_toolbar = '';
		switch (intval($toolbar_style))
		{
			case self::WORD_PAGING_STYLE:
				$this->nextPageFlag = '下一页';
				$this->prePageFlag = '上一页';
				$paging_toolbar = $this->getFirstPageButton().self::WHITE_SPACE.
								  $this->getCurrentPagingToolBar().self::WHITE_SPACE.
								  $this->getLastPageButton().self::WHITE_SPACE.
								  $di_text.$this->getJumpList().$page_text;
				//return $paging_toolbar;
				break;
			case self::WORD_PAGING_STYLE2:
				/*
				$this->nextPageFlag = '下一页';
				$this->prePageFlag = '上一页';
				$this->firstPageFlag = '首页';
				$this->lastPageFlag = '尾页 ';
				*/
				$paging_toolbar = $this->getFirstPageButton().self::WHITE_SPACE.
								  $this->getPrePageButton().self::WHITE_SPACE.
								  $this->getNextPageButton().self::WHITE_SPACE.
								  $this->getLastPageButton().$di_text.self::WHITE_SPACE.
								  $this->getJumpList().$page_text;
				//return $paging_toolbar;
				break;
			case self::WORD_PAGING_STYLE3:
				$this->nextPageFlag = '下一页';
				$this->prePageFlag = '上一页';
				$this->firstPageFlag = '首页';
				$this->lastPageFlag = '尾页';
				$paging_toolbar = $this->getFirstPageButton().self::WHITE_SPACE.
								  $this->getPrePageButton().self::WHITE_SPACE.
								  $this->getNextPageButton().self::WHITE_SPACE.
								  $this->getLastPageButton();
				//return $paging_toolbar;								  
				break;
			case self::WORD_PAGING_STYLE4:
				$this->nextPageFlag = '下一页';
				$this->prePageFlag = '上一页';
				$paging_toolbar = $this->getPrePageButton().self::WHITE_SPACE.
								  $this->getCurrentPagingToolBar().self::WHITE_SPACE.
								  $this->getNextPageButton();
				break;
			/*
			case SIGN_PAGING_STYLE:
				$paging_toolbar = $this->getPrePagingToolbar().$this->getPrePageButton().
								  $this->getCurrentPagingToolBar().$this->getCurrentPagingToolBar();
				break;*/
			default:break;
		}// end switch()
		//dd($paging_toolbar);
		// 无 Jump List 的 toolbar加 "第 x/xx 页",否则加 "共xx页"
		$ext_txt = !stristr($paging_toolbar,'select') ? 
				   self::WHITE_SPACE.$di_text.$this->_currentPageIndex.'/'.$this->_totalPages.$page_text :
				   $total_text.$this->_totalPages.$page_text;
		return $paging_toolbar.$ext_txt;
	}// end outputToolbar()
	
	/**
	 * 设定当前页面的页码数
	 * @param int $current_page_index
	 * @return void no return value
	 * @access private
	 * @author Dennis
	 */	
	private function _setCurrentPageIndex($current_page_index)
	{
		if (empty($current_page_index))
		{
			// 从 $_GET 中取得
			if (isset($_GET[$this->pageName])) $this->_currentPageIndex = intval($_GET[$this->pageName]);
		}else{
			// 手动设置
			$this->_currentPageIndex = intval($current_page_index);
		}// endif
	}// end _setCurrentPageIndex()
	/**
	 * 设定分页 link 的 URL
	 * @param string $url url 如 xxx.php?xx=ss
	 * @return void no return value
	 * @access private
	 * @author Dennis
	 */
	private function _setURL($url)
	{
		if (!empty($url))
		{
			// 手动设置url
			$this->_url = $url.((stristr($url,'?')) ? '&' : '?').$this->pageName.'='; 
		}else{// 自动获取			
			if(empty($_SERVER['QUERY_STRING']))
			{
				// No QUERY_STRING
				$this->_url = $_SERVER['REQUEST_URI'].'?'.$this->pageName.'=';
			}else{
				if (stristr($_SERVER['QUERY_STRING'],$this->pageName.'='))
				{
					$this->_url = str_replace($this->pageName.'='.$this->_currentPageIndex,'',$_SERVER['REQUEST_URI']);
					$last_char = $this->_url[strlen($this->_url)-1]; // 字符串自动转换成数组
					if ($last_char == '?' || $last_char = '&')
					{
						$this->_url .= $this->pageName.'=';
					}else{
						$this->_url .= '&'.$this->pageName.'=';
					}// end if
				}else{
					$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
					$this->_url = $request_uri.'&'.$this->pageName.'=';
				}// end if
			}// end if
		}// end if
	}// end _setURL()
	/**
	 * 取得分页的标识
	 * @example <u>[1]</u><u>[2]</u>
	 * @param string $style
	 * @param string $symboal 标识, 如 '<','<<','>>'
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getPagingSymbol($style,$symboal)
	{
		return '<span '.(!empty($style) ? 'class="'.$style.'"' : '').'>'.$symboal.'</span>';
	}
	/**
	 * 取得加左右格式的 link
	 * @example [<a href='xxx'>1</a>]
	 * @param string $link_text
	 * @return string
	 * @access private
	 * @author Dennis
	 */
	private function _getText($link_text)
	{
		return $this->formatLeft.$link_text.$this->formatRight;
	}// end _getText()
	/**
	 * 取得页码的link html 分一般和 Ajax enabled 两种
	 * @param string $url
	 * @param string $text
	 * @param string $style
	 * @return string 组合后 link html code
	 * @access private
	 * @author Dennis
	 */
	private function _getLink($url,$text,$style)
	{
		$link = '';
		$style = empty($style) ? '' : 'class="'.$style.'"';
		if ($this->isAjaxSupported)
		{
			$link = '<a '.$style.'href="javascript:'.$this->_ajaxActionName.'(\''.urlencode($url).'\')">'.$text.'</a>';
		}else{
			$link = '<a '.$style.' href="'.urlencode($url).'">'.$text.'</a>';
		}// end if
		return $link;
	}// end _getLink()
	/**
	 * 取得到指定页码的 URL 
	 * @param int $page_no 页码
	 * @return string url
	 * @access private
	 * @author Dennis
	 */
	private function _getURL($page_no = 1)
	{
		return $this->_url.$page_no;
	}// end _getURL()

}// end class Data_Paging

?>