<?php

namespace Home\Controller;
use Think\Controller;
define('APP_PATH','./Application/');
import('Org.Util.libs.AresUser');

class RedirectController extends FormController{
	function index(){
		header('Content-Type:text/html; charset=utf-8');
		//$User = new \AresUser($_SESSION['user']['company_id'], $_SESSION['user']['user_name']);
		//dump($User);
		$company_id = $_SESSION['user']['company_id'];
		$user_id = $_SESSION['user']['user_name'];
		$mod = M('app_users_base');
		$where['username_no_sz'] = $user_id;
		$user_seq_no = $mod->where($where)->getField();
		echo $company_id;
		echo $user_seq_no;
		$mod1 = M();
		//exit();
// 		$stmt1 = 'begin pk_erp.p_set_segment_no(:company_id); pk_menu.p_set_language(:language);pk_erp.p_set_username(:user_seq_no);end;';
// 		$stmt=$mod1->execute($stmt1,array('company_id'=>$this->$company_id,
// 										  'lang'=>$this->$lang,
// 										  'user_seq_no'=>$this->$user_seq_no
// 		));

		$sql="begin pk_erp.p_set_segment_no('".$_SESSION['user']['company_id']."');pk_menu.p_set_language('".$_SESSION['user']['language']."');pk_erp.p_set_username('".$user_seq_no."');end;";
		$data=M()->execute($sql);
		dump($data);
		
		//$result = 'begin pk_erp.p_set_segment_no(.$company_id.); pk_menu.p_set_language(."ZHS".);pk_erp.p_set_username(.$user_seq_no.);end;';
		//dump($stmt);
		
		$mod2 = M('ess_function_menu_v');
		$map['parent_id'] = array('NEQ','ROOT'); 	
		$g_menu = $mod2->where($map)->getField('program_no as nodeid,
											   program_name as nodetext,
											   parent_id as p_nodeid,
											   program_type as nodetype');
		//dump($g_menu);
		//exit();
		
		$_SESSION['user']['sys_menu'] = $g_menu;
		//dump($g_menu);exit;
		echo '1111111111';
		$menu_list = getMenuItem($g_menu, 'ESN');
		//$menu_list_second = getMenuItem($g_menu, 'ESN');
		$this->assign('menu_list',$menu_list);
		//$this->assign('menu_id',$menu_list['menu_id']);
		//dump($menu_list);
		//dump($menu_list['menu_id']);
		//exit();
		//$this->assign('menu_list_second',$menu_list_second);
		//dump($menu_list);
		//exit();
// 		for($i=1;$i<12;$i++){
// 			$menu_list_a = $menu_list[$i]['menu_text'];
// 			$this->assign('menu_list_a',$menu_list_a);
// 			//dump($menu_list_a);
// 		}
 		$menu_list_a = $menu_list['1']['menu_text'];
// 		$menu_list_b = $menu_list['2']['menu_text'];
		
 		$this->assign('menu_list_a',$menu_list_a);
		
		//dump($menu_list_b);
		$menu_list_a_1 = $menu_list['1']['1']['menu_text'];
		$this->assign('menu_list_a_1',$menu_list_a_1);
		//$list = ParseTable('menu_list',$menu_list);
		//dump($list);
		//exit();
		
		
// 		$sql=$mod2->query('select program_no   as nodeid,
// 				   		program_name as nodetext,
// 				   		parent_id    as p_nodeid,
// 				   		program_type as nodetype
// 			  			from ess_function_menu_v
// 			 			where parent_id <> "ROOT"');
		//parent::GetMenu();
		//$this->assign($user_seqno,$_SESSION['user']['user_name']);
		//$g_menu =$User->GetMenu($user_seqno, 'ess');
		
		//$g_menu = $User->GetMenu($user_seqno, $sys_name, $lang)
		/* $user = D("user");
		$user = _initialize(); */
		//echo "hello";
		/* if (ob_get_length() === FALSE                &&
				!ini_get('zlib.output_compression')         &&
				ini_get('output_handler') != 'ob_gzhandler' &&
				ini_get('output_handler') != 'mb_output_handler') {
					ob_start('ob_gzhandler');
				} */
		
		$this->display('ESN0000_1');
	}
	
	function ESNH000(){
// 		$data = array('a'=>1,'b'=>2,'c'=>3,'d'=>4);
// 		$this->assign('test',$data);
		$y = date('Y');
		$m = date('m');
		$companyid = $_SESSION['user']['company_id'];
		$empseqno = $_SESSION['user']['emp_seq_no'];
		//echo $empseqno;exit();
		//$calendar = new SolarCalendar($y,$m,$companyid,$empseqno,strtolower ($GLOBALS['config']['default_lang']));
		//echo $_SESSION['user']['language'];exit();
		$AresCalendar = new \Org\Util\libs\AresCalendar($y,$m,$companyid,$empseqno,$_SESSION['user']['language']);
		
// 		echo $companyid;
// 		echo $empseqno;
// 		exit();
		
		//$calendar=getMonthView($m,$y,'N');
		$calendar = $AresCalendar->getMonthView($m, $y,'N');
		$this->assign('calendar',$calendar);
		
		//echo $y,$m;
		$this->display('PageHeader');
		$this->display();
		$this->display('PageFooter');
	}
	
	
}
