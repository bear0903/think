<?php
/**
 * 构建一个 JQuery Base Accordion Menu
 * 完整HTML代码如:
	<ul id="menu">
		<li>
			<a href="#">Sub menu heading</a>
			<ul>
				<li><a href="http://site.com/">Link</a></li>
				<li><a href="http://site.com/">Link</a></li>
				<li><a href="http://site.com/">Link</a></li>
				...
			</ul>
		</li>
		<li>
			<a href="#">Sub menu heading</a>
			<ul>
				<li><a href="http://site.com/">Link</a></li>
				<li><a href="http://site.com/">Link</a></li>
				<li><a href="http://site.com/">Link</a></li>
				...
			</ul>
		</li>
		...
	</ul>
	[MDNA100] => Array
	        (
	            [menu_id] => MDNA100
	            [menu_text] => 個人資訊
	            [item] => Array
	                (
	                    [0] => Array
	                        (
	                            [href] => ?scriptname=MDNA101
	                            [text] => 基本資料
	                            [icon] => showpic.gif
	                        )

 * Requirement
 *  jquery.min.js
 *  ui.core.min.js
 *  ui.accordion.min.js
 *  ui.accodion.css
 */
require_once 'Base.class.php';
class Layout_AccordionMenu extends Base   {
	
	///public $menuId;
	
	public $menuItems;
	
	public $expanded;
	
	/**
	 * Constructor of class Layout_AccordionMenu
	 * 初始化相关参数
	 *
	 * @param array $menuitems  menu item 2-d array
	 * @param boolean $expanded menu 是否预设展开
	 * @return void
	 * @author Dennis 2008-07-25
	 */
	public function __construct(array $menuitems,$expanded = false) {
		$this->menuItems = $menuitems;
		$this->expanded = $expanded;
	}// end __construct()
	
	/**
	 * Get Accordion Menu Contnet Html Code
	 * @param no
	 * @return string
	 * @author Dennis 2008-07-25
	 */
	private function _getMenuContent()
	{
		$menu_html = '';
		if (is_array($this->menuItems) && count($this->menuItems)>0)
		{
			foreach ($this->menuItems as $menuitems) {
				$menu_html .= '<li><a href="#">';
				$menu_html .= $menuitems['menu_text'];
				$menu_html .= '</a><ul>';
				// loop the sub menu
				for ($i=0; $i<count($menuitems['item']); $i++)
				{
					$menu_html .= '<li>';
					$menu_html .= '<a type="popup" href="'.$menuitems['item'][$i]['href'].'"';					
					$menu_html .= !empty($menuitems['item'][$i]['target']) ? ('target="'.$menuitems['item'][$i]['target'].'" ') : '';
					$menu_html .='>';
					$menu_html .= '<span><img src="../img/'.$menuitems['item'][$i]['icon'].'" alt=""/></span>';
					$menu_html .= $menuitems['item'][$i]['text'];
					$menu_html .= '</a></li>';
				}// end for loop
				$menu_html .= '</ul>';
			}// end foreach
			return $menu_html;
		}// end if
		return $menu_html;
	}// end _getMenuContent()
	
	private function _getJsTagStart()
	{
		return '<script type="text/javascript">';
	}// end _getJsTagStart();
	
	private function _getJsTagEnd()
	{
		return '</script>';
	}// end _getJsTagEnd()
	/**
	 * Get Accordion Menu Init Javascript Code (Jquery Base)
	 * @param no
	 * @return string
	 * @author Dennis 2008-07-25
	 *
	 */
	private function _getMenuJs()
	{
		return " function initMenu() {
		  $('#menu ul').hide();
		  //$('#menu ul:first').show();
		  $('#menu li a').click(
		    function() {
		      var checkElement = $(this).next();
		      if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
		        return false;
		        }
		      if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
		        $('#menu ul:visible').slideUp('normal');
		        checkElement.slideDown('normal');
		        return false;
		        }
		      }
		    );
		  }";
		
	}// end _getMenuJs()
	
	/**
	 * Get Accordion Menu Init Javascript Code (Jquery Base)
	 * @param no
	 * @return string
	 * @author Dennis 2008-07-25
	 *
	 */
	private function _getExpandMenuJs()
	{
		return "
			function initMenu() {
				$('#menu ul').hide();
				$('#menu li a').click(
					 function() {
					 	$(this).next().slideToggle('normal');
					 }
				);
  			}";
	}// end _getExpandMenuJs()
	
	/**
	 * Get Accordio Menu Init Javascript Code
	 *
	 * @return string
	 */
	private function _getJs()
	{
		$js = '';
		$js .= $this->_getJsTagStart();
		$js .= (($this->expanded) ? $this->_getExpandMenuJs(): $this->_getMenuJs());
		//$js .= '$(document).ready(function() {initMenu();alert(1);});';
		$js .='initMenu();';
		$js .= $this->_getJsTagEnd();
		return $js;	
	}// end getJs()
	
	/**
	 * Get full menu html code (contain js code)
	 * @param no
	 * @return string
	 * @author Dennis 2008-07-25
	 */
	public function render()
	{
		$menu_html = '<ul id="menu">';
		$menu_html .= $this->_getMenuContent();
		$menu_html .= '</ul>';
		$menu_html .= $this->_getJs();
		return $menu_html;
	}// end render()
	
	/**
	 * output menu content html code
	 *
	 */
	public function dispatch()
	{
		echo self::render();
	}// end dispatch()
	
}// end class Layout_AccordionMenu

?>