<?php 
namespace Home\Controller;
use Think\Controller;
class FormController extends Controller{
	
	function GetMenu($user_seqno,$sys_name)
	{
		//$this->_dBConn->debug = true;
		$stmt = 'begin pk_erp.p_set_segment_no(:company_id);pk_erp.p_set_username(:user_seq_no);end;';
		$this->_dBConn=Execute($stmt,array('company_id'=>$this->_companyId,
				'user_seq_no'=>$user_seqno));
		// follow statement for improve performance
		/* remark by dennis 2011-08-02 閺堫亞鏁ら崚棰佷簰娑撳娈� temporary table
			$this->_dBConn->Execute('delete from ess_userfunction_sz');
			$this->_dBConn->Execute('insert into ess_userfunction_sz
			select rolefunction
			from app_userfunction
			where rolefunction_type != \'ROOT\'
			start with userrole = :user_seq_no
			connect by userrole = prior rolefunction',
			array('user_seq_no'=>$user_seqno));
			*/
		// Get app menu tree structure data
		$_view_name = $sys_name.'_function_menu_v'; // get view name start by mgr_ or ess_
		$sql = <<<eof
			select program_no   as nodeid,
				   program_name as nodetext,
				   parent_id    as p_nodeid,
				   program_type as nodetype
			  from $_view_name
			 where parent_id <> 'ROOT'
eof;
		$this->_dBConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dBConn->GetArray($sql);
	}
}

?>