<?php

require_once 'Base.class.php';
class Layout_Panel extends Base {
	
	public $title;
	
	public $panelContent;
	
	/**
	 * Constructor of class Layout_Panel
	 */
	public function __construct($title,$panel_content) {
		$this->title = $title;
		$this->panelContent = $panel_content;	
	}// end __construct()
	
	public function render()
	{
		$panel_html =  '<div id="rc2">
						<div class="w t l"></div>
						<div class="w t r"></div>
						<div class="c"></div>
						<div class="w o l"></div>
						<div class="w o r"></div>
						<div class="c"></div>
						<div class="w p l"></div>
						<div class="w p r"></div>
						<div class="c"></div>
						<div class="tt">';
		$panel_html .= $this->title;
		$panel_html .= '</div><div id="rc2_c">';
		$panel_html .= $this->panelContent;
		$panel_html .= '</div></div>';
		return $panel_html;
	}// end render()
	
	public function dispatch()
	{
		echo $this->render();
	}// end dispatch()
}// end class Layout_Panel

?>