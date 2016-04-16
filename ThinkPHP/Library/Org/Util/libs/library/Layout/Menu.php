<?php
require_once 'Base.class.php';
class Layout_Menu extends Base {
	
	/**
	 * menu id (optional), 如果没有指定,程式会自动产生
	 *
	 * @var string
	 */
	public $menuId;
	
	/**
	 * Menu Title
	 * default ''
	 * @var string
	 */
	public $title = '';
	
	/**
	 * 2-d array, menu item properties array
	 * 
	 * @var array
	 */
	public $menuItems = array();
	
	/**
	 * Constructor of class Layout_Menu
	 *
	 * @param array $menuitems 2d array
	 *  $menuitems[0]['text'] = 'My Profile'
	 *  $menuitems[0]['icon'] = 'aa.gif'
	 *  $menuitems[0]['href'] = '?scriptname=abc'
	 *  $menuitems[0]['target'] = 'main'
	 * @param string $menuid menu 所在的 div 的 id (optional), 如果没有系统会自动产生
	 * @param string $title  menu title
	 */
	public function __construct(array $menuitems,$menuid = null,$title = '&nbsp;') {
		$this->title = $title;
		$this->menuId = !empty($menuid) ? $menuid : 'menu_'.md5(uniqid(rand(), true));
		$this->menuItems = $menuitems;
	}// end __construct()
	
	
	/**
	 * get menu item html code such as :
	 * <tr><td>
	 * <span><img src="showpic.gif"/></span>
	 * <a id="xx" target="main" href="">测试菜单</a>
	 * </td></tr>
	 * @param string $text       menu text
	 * @param string $href       url link
	 * @param string $icon       image file name(full path)
	 * @param string $target     link target( default '_self')
	 * @return string
	 * @author Dennis
	 */
	private function _getMenuItem($text,$href='#',$icon='',$target = '',$menu_id)
	{
		$menu_item_html = '';
		if (!empty($text))
		{
			$menu_item_html .= '<tr><td onMouseOut="lightIcon(\'icon_img_'.$menu_id.'\');" onMouseOver="darkIcon(\'icon_img_'.$menu_id.'\');" onClick="lockDarkIcon(\'icon_img_'.$menu_id.'\');">';
			$menu_item_html .= !empty($icon) ? '<span><img id="icon_img_'.$menu_id.'" style="vertical-align: middle;" src="../img/'.$icon.'" alt=""/>&nbsp;</span>' : '';
			$menu_item_html .= '<a href="'.$href.'"';
			$menu_item_html .= !empty($target) ? 'target="'.$target.'"' : '';
			$menu_item_html .= '>';
			$menu_item_html .= $text;
			$menu_item_html .= '</a></td></tr>';
		}else {
			throw new Exception('Can not generate menu item, menu text must be assigned.');
		}// end if
		//echo $menu_item_html.'<br/>';
		return $menu_item_html;
	}// end _getMenuItem()
	
	/**
	 * Get Menus Body
	 *
	 * @return string
	 */
	protected function _getMenuContent()
	{
		//pr($this->menuItems);
		$menu_html = '';
		$len = count($this->menuItems);
		if (is_array($this->menuItems) && $len >0)
		{
			for ($i=0; $i<$len; $i++)
			{
				$icon = isset($this->menuItems[$i]['icon']) ? $this->menuItems[$i]['icon'] : '';
				$href = isset($this->menuItems[$i]['href']) ? $this->menuItems[$i]['href'] : '#';
				$text = $this->menuItems[$i]['text'];
				$target = isset($this->menuItems[$i]['target']) ? $this->menuItems[$i]['target'] : '';
				$menu_html .= $this->_getMenuItem($text,$href,$icon,$target,$this->menuItems[$i]['id']);
			}// end for loop
		}// end if
		//echo $menu_html;
		return $menu_html;
	}// end _getMenuContent()

	/**
	 * get menu html code
	 * @param no
	 * @return string
	 * @author Dennis
	 */
	public function render()
	{
		$panel_html =  '<div id="'.$this->menuId.'">
							<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
							<tbody>';
		$panel_html .= self::_getMenuContent();
		$panel_html .= '</tbody>
						</table>
					</div>';
		return $panel_html;
	}// end render()
	
	public function dispatch()
	{
		echo $this->render();
	}// end dispatch()
}// end class Layout_Panel

?>