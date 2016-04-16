<?php

require_once 'Base.class.php';
class Layout_Tab extends Base {
	/**
	 *  tab id
	 *
	 * @var string
	 */
	private $_tabId;
	
	/**
	 * 2-d tab itme array
	 * 格式 
	 * $tabitems[0]['href'] = '#tab1',
	 * $tabitems[0]['label'] = 'Tab 01';
	 *
	 * @var array
	 */
	protected $_tabItems = array();
	
	/**
	 * 内容和 tab 在同一页的 tab item 的 id
	 *
	 * @var array
	 */
	private $_tabLinks = array();
	
	/**
	 *  tab 页面如是 ajax 载入,是否做 cache
	 *
	 * @var boolean
	 */
	public $isCache = false;
	
	/**
	 * Constructor of class Layout_Tab
	 *
	 * @param array  $tabitems  tab 页属性组成的二维数组, 格式 $tabitems[0]['href'] = '#tab1',
	 * $tabitems[0]['label'] = 'Tab 01';
	 * @param string $tabid		tab 所在的 div 的 id(重要, jquery 要依赖此id 产生 tab), 
	 * default null, 如果没有输入,程式自动产生
	 * @author Dennis
	 */
	public function __construct(array $tabitems,$tabid = null,$iscache = true)
	{
		$this->_tabItems = $tabitems;
		$this->_tabId = !empty($tabid) ? $tabid : 'tab_'.md5(uniqid(rand(), true));
		$this->isCache = $iscache;
	}// end class constructor
	
	/**
	 *  Get 组成 tab 的 html code, 如 
	 * <li>
	 * 	<a href="#home"><span>Tab Sample</span></a>
	 * </li>
	 *
	 * @param string $href     点击某个tab时连结到的锚点或是 link url
	 * @param string $tablabel 页签标题 
	 * @return string
	 * @author Dennis  2008-07-16 14:48
	 */
	private function _getTabItem($href,$tablabel)
	{
		/**
		 * 内容跟 tab 在同一页面时，取其link 到的 div 的 id
		 *  store tab link name (tab content page div id)
		 */
		if (substr($href,0,1) == '#')
		{
			$this->_tabLinks[] = str_replace('#','',$href);
		}// end if
		return '<li id=""><a href="'.$href.'"><span>'.$tablabel."</span></a></li>\r\n";
	}// end _getTabItem()
	
	/**
	 * Get 同一页的 Tab Page 内容 div
	 *
	 * @return string
	 */
	private function _getTabPageContent()
	{
		$tab_content_html = '';
		$len = count($this->_tabLinks);
		if (is_array($this->_tabLinks) && $len >0)
		{
			for ($i=0; $i<$len; $i++)
			{
				$tab_content_html .='<div id="'.$this->_tabLinks[$i].'"></div>';
			}// end for loop
		}//end if 
		return $tab_content_html;
	}// end _getTabPageContent()
	
	/**
	 * 通过 Jquery JS 初始化 tab 页的 js
	 *
	 * @return string javascript code
	 * @author Dennis last moify 20090723
	 */
	protected function _getTabInitJS()
	{
		$js = <<<eof
			<script type="text/javascript">
				$(document).ready(function() {
					if(($.browser.msie && $.browser.version>='7.0') || $.browser.mozilla)
					{
						$('#$this->_tabId').tabs({cache: true});
					}else{
					 	$('#$this->_tabId').tabs({remote: true});
					}
					$('#$this->_tabId').bind( "tabsselect", function(event, ui) {
						//autoResize('frameid');
					});
				});
			</script>
eof;
		return $js;
	}// end _getTabInitJS();
	
	/**
	 *  get tab html code
	 * @param  no
	 * @return string
	 * @author Dennis
	 */
	public function render()
	{
		$tab_html = '';
		$tab_html = self::_getTabInitJS();
		$len = count($this->_tabItems);
		if ($len > 0)
		{
			$tab_html .= '<div id="'.$this->_tabId.'"><ul>';
			for ($i=0; $i<$len; $i++) 
			{
				$tab_html .= $this->_getTabItem($this->_tabItems[$i]['href'],$this->_tabItems[$i]['label']);
			}// end for loop
		}else{
			$tab_html = '';
			return $tab_html;
			//throw new Exception('Can not generate out tab layout. No tab item input.');
		}// end if
		return $tab_html.'</ul></div>';
	}// end render()
	
	/**
	 * 输出 tab html code
	 * @param no
	 * @return void
	 * @author Dennis
	 */
	public function dispatch()
	{
		echo $this->render();
	}// end dispatch()
	
}// end class Layout_Tab

?>