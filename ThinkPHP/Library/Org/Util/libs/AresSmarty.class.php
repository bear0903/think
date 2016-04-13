<?php
/**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) Lan Jiangtao 
 *	 
 *	Description:
 *     Smarty Template Setup Class
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresSmarty.class.php $
 *  $Id: AresSmarty.class.php 3316 2012-03-05 08:56:26Z dennis $
 *  $Rev: 3316 $ 
 *  $Date: 2012-03-05 16:56:26 +0800 (周一, 05 三月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-03-05 16:56:26 +0800 (周一, 05 三月 2012) $
 \****************************************************************************/
require_once SMARTY_DIR . 'Smarty.class.php';
class AresSmarty extends Smarty {
	// constructor of SmartyTPL
	function __construct($smarty_home) {
		// impletation parent counstructor
		parent::Smarty ();
		$this->_custSmartyDirs($smarty_home);
		$this->_registerModifier();
		// 两级目录存放 Compile File, add by dennis 2008-6-19
		// for improve performance
		$this->use_sub_dirs = true;
		// $this->caching = true;
		// $this->cache_lifetime = 2; // default 2 minutes
	} // end AresSmarty()
	
	/**
	 * move code from config.inc.php
	 * register modifiers
	 * @param no
	 * @author Dennis 2012-03-05
	 */
	private function _registerModifier() {
		$this->register_modifier('number_format', 'number_format');
		$this->register_modifier('stripslashes', 'stripslashes');
		$this->register_modifier('urldecode', 'urldecode');
		$this->register_modifier('urlencode', 'urlencode');
	}
	
	private function _custSmartyDirs($smarty_home)
	{
		// Setting Smarty template dir
		$this->template_dir = $smarty_home . '/smartytemplate/';
		$this->compile_dir = $smarty_home . '/smartycompile/';
		$this->config_dir = $smarty_home . '/smartyconfig/';
		$this->cache_dir = $smarty_home . '/smartycache/';
		$this->left_delimiter = '<!--{';
		$this->right_delimiter = '}-->';
	}
}// end class AresSmarty
?>