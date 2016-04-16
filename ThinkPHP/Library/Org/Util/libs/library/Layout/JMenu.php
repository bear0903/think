<?php
/*-----------------------------------------------------
* Description:
* 	jQuery Layout Menu
* Author: TerryWang 
* Date: 2011-8-16
* Version: 
* ----------------------------------------------------*/
require_once 'Base.class.php';
class JMenu extends Base{
	private $menu = null;
	private $mystaff = null;
	public function __construct($menu,$mystaff = null){
		$this->menu = $menu;
		$this->mystaff = $mystaff; 
	}
	/**
	 * @param $arr array
	 * @param $key string
	 * Return $arr[$key]
	 */
	private  function GetVar($arr=array(),$key){
		if(!is_array($arr) || empty($arr)) return null;
		if(isset($arr[$key])) return $arr[$key];
		return null;
	}
	/**
	 * @param void
	 * Return  main menu , Array
	 */
	private function GetMainMenu(){
		if(!$this->menu || !is_array($this->menu) || empty($this->menu)) return null;
		$m = array();
		foreach($this->menu as $one){
			if(strtoupper($this->GetVar($one,'NODETYPE')) === 'MENU' && $this->IsTopNode($this->GetVar($one,'P_NODEID'))){
				$m[] = $one;
			}
		}
		return $m;
	}
	/**
	 * @param $nodeid 
	 * Return Boolean
	 * 判断有没有子菜单
	 */
	private function IsHasPriorNode($nodeid){
		foreach($this->menu as $k => $v){
			if($v['P_NODEID'] == $nodeid) return true;
		}
		return false;
	}
	/**
	 * @param $nodeid
	 * Return Boolean 
	 * 判断是不是顶级菜单
	 */
	private function IsTopNode($p_nodeid){
		foreach ($this->menu as $k => $v){
			if($v['NODEID'] == $p_nodeid) return false;
		}
		return true;
	}
	/**
	 * @param $nodeid
	 * Return subMenu , Array
	 */
	private function GetSubMenu($nodeid){
		$subMenu = array();
		foreach($this->menu as $k => $v){
			if($v['P_NODEID'] == $nodeid){
				$subMenu[] = $v;
			}
		}
		return $subMenu;
	}
	/**
	 * @param $nodeid String
	 * Return String
	 * switch nodeid to scriptname
	 */
	public function _GetNodeMenu($nodeid){
		if(array_key_exists($nodeid, $GLOBALS['config']['pub_app'])){
			return $GLOBALS['config']['pub_app'][$nodeid];
		}
		return $nodeid;
	}
	public function GetNodeMenu($nodeid){
		return $this->_GetNodeMenu($nodeid);
	}
	
	/**
	 * Get Menu URL
	 * @param string $nodeid
	 * @param string $nodetext
	 */
	public function getMenuUrl($nodeid='',$nodetext=''){
		$uri = $this->GetVar($_SERVER,'REQUEST_URI') ? 
			   $this->GetVar($_SERVER,'REQUEST_URI') : 
			   getenv('REQUEST_URI');
		$n_nodeid = $this->GetNodeMenu($nodeid);
		// 处理 ess/mss 相互共用程式的情形
		if ($n_nodeid != $nodeid)
		{
			$search_str = substr($nodeid, 0,3) == 'ESN' ? 'ess' : 'mgr';
			$replace_str = substr($n_nodeid,0,3) == 'ESN' ? 'ess' : 'mgr';
			$uri = str_replace($search_str, $replace_str, $uri);
		}
		
		$url = $uri."?scriptname={$n_nodeid}&nodetext={$nodetext}";
		return $url;
	}
	/**
	 * @param void
	 * Return String like the follow
	 * <li>
	 * 		<span class='tocBtn'></span><a href=''>Menu title</a>
	 * 		<ul>
	 * 			<li>
	 * 				<span class='tocBtn'></span><a href=''>SubMenu title</a>
	 * 				<ul>
	 * 					<li><a href=''>ThirdMenu title</a></li>
	 * 					... ...
	 * 				</ul>
	 * 			</li>
	 * 			<li><a href=''>Menu title</a></li>
	 * 		</ul>
	 *  </li>
	 *  ... ...
	 */
	public function Render(){
		if(!$this->menu) return null;
		$mainMenu = $this->GetMainMenu();
		if(!$mainMenu) return null;
		$jMenu = '';
		foreach ($mainMenu as $one){
			// my staff
			if(strtoupper($this->GetVar($one,'NODEID')) === 'MDNA'){
				$query = $this->BuildQuery(array(
					'scriptname' => 'MDNA',
					'empseqno'	 => $_SESSION['user']['emp_seq_no'],
					'empname'	 => $_SESSION['user']['emp_name'],
					'companyid'  => $_SESSION['user']['company_id'],
					'emp_no'	 => $_SESSION['user']['emp_id'],
				));
				$jMenu .= "<li>
							<a href='?{$query}' target='mainFrame'>{$this->GetVar($one,'NODETEXT')}</a>";
				$jMenu .= "<ul>";
				$jMenu .= $this->RenderMystaffRecursive($this->mystaff);
			}else{
				$jMenu .= "<li><a href='{$this->getMenuUrl($this->GetVar($one,'NODEID'),$this->GetVar($one,'NODETEXT'))}' target='mainFrame'>{$this->GetVar($one,'NODETEXT')}</a>";
				$jMenu .= "<ul>";
			}	
			// my staff end
			// 二级菜单
			foreach ($this->menu as $key => $two){
				if(strtoupper($this->GetVar($one,'NODEID')) === strtoupper($this->GetVar($two,'P_NODEID'))){					
					if(strtoupper($this->GetVar($one,'NODEID')) != 'MDNA'){
						$jMenu .= "<li><a href='{$this->getMenuUrl($this->GetVar($two,'NODEID'),$this->GetVar($two,'NODETEXT'))}' target='mainFrame'>{$this->GetVar($two,'NODETEXT')}</a></li>";
					}
					unset($this->menu[$key]);
				}
			}
			$jMenu .= "</ul></li>";
		}
		return $jMenu;
	}
	/**
	 * @param $query , array 
	 * return query string
	 */
	public function BuildQuery($query = array()){
		return http_build_query($query);
	}
	/**
	 * @param $mystaff, array
	 * 部门名称不能是纯数字
	 * create by TerryWang 2011-8-25
	 * return string like 
	 * <li>
	 * 	<span class='tocBtn'></span>
	 * 	<a href=''>menu title</a>
	 * 	<ul>
	 * 		<li> menu </li>
	 * 			... ...
	 * 		<li>
	 * 			<span class='tocBtn'></span>
	 * 			<a href=''> menu title </a>
	 * 			<ul>
	 * 				<li> menu </li>
	 * 				... ...
	 * 			</ul>
	 * 			... ...
	 * 		</li>
	 * 	</ul>
	 * </li>
	 * ... ...
	 */
	public function RenderMystaffRecursive($mystaff){
		if(!$mystaff || !is_array($mystaff) || empty($mystaff)) return '';
		$str = '';
		foreach($mystaff as $k => $v){
			if(is_numeric($k)){
				$query = $this->BuildQuery(array(
					'scriptname' => 'MDNA',
					'empseqno'   => $this->GetVar($v,'EMP_SEQNO'),
					'empname'	 => $this->GetVar($v,'EMP_NAME'),
					'companyid'  => $this->GetVar($v,'COMPANY_ID'),
					'emp_no'	 => $this->GetVar($v,'EMP_ID'),
				));
				$str .= <<<eof
				<li>
					<img src='../img/Person{$this->GetVar($v,'SEX')}.gif' />
					<a href='?{$query}' target='mainFrame' class='person'>{$this->GetVar($v,'EMP_NAME')}</a>
				</li>
eof;
			}elseif($k !== 'deptinfo'){
				$query = $this->BuildQuery(array(
					'scriptname' => 'MDNA002',
					'action' => 'home',
					'deptseqno' => $this->GetVar($v['deptinfo'],'DEPT_SEQNO'),
					'deptname' => $k,
				));
				$str .= <<<eof
				<li>
					<span class='tocBtn'></span>
					<a href="?{$query}" target='mainFrame'>{$k}</a>
					<ul>
eof;
				$str .= $this->RenderMystaffRecursive($v);
				$str .= "</ul></li>";
			}
		}
		return $str;
	}
	
	/**
	 * Get ESS/MSS 共用程式的 URL
	 */
	private function _getPubAppUrl()
	{
		
	}
}