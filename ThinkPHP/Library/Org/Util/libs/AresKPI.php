<?php
/*
 *  KPI 警示系统
 *  create by Dennis 20090716
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresKPI.php $
 *  $Id: AresKPI.php 3552 2013-09-28 07:38:38Z dennis $
 *  $Rev: 3552 $ 
 *  $Date: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 *  $LastChangedDate: 2013-09-28 15:38:38 +0800 (周六, 28 九月 2013) $
 ****************************************************************************/

class AresKPI
{
	private $_userSeqNo;
	private $_companyId;
	private $_dbConn;
	private $dept_id_permission;
	private $company_no;
	private $_config;
	
	/**
	 * Constructor of class AresKPI
	 * @param $companyid
	 * @param $userseqno
	 */
	public function __construct($companyid,$userseqno)
	{
		global $g_db_sql,$config;
		$this->_companyId = $companyid;
		$this->_userSeqNo = $userseqno;
		$this->_dbConn = $g_db_sql;
		$this->_config = $config;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->dept_id_permission="select in_gl_segment.segment_no_sz   dept_id
				            from gl_segment  in_gl_segment
				            where exists (select in_gl_segment.segment_no_sz as dept_id
				                            from app_usercompany_v  in_app_usercompany_v, 
				                                 app_userdepartment   in_app_userdepartment
				                           where in_app_usercompany_v.appusr_seg_segment_no = in_app_userdepartment.appusr_seg_segment_no
				                             and in_app_usercompany_v.appusr_username = in_app_userdepartment.appusr_username
				                             and in_app_userdepartment.appusr_seg_segment_no = in_gl_segment.seg_segment_no
				                             and in_app_userdepartment.department = in_gl_segment.segment_no
				                             and in_gl_segment.begindate < sysdate
				                             and (in_gl_segment.enddate is null or in_gl_segment.enddate > trunc(sysdate))
				                             and in_app_usercompany_v.appusr_seg_segment_no = '".$this->_companyId."'
				                             and in_app_usercompany_v.personself_yn = 'N'
				                             and in_app_usercompany_v.APPUSR_USERNAME = '".$this->_userSeqNo."'
				                           )
				            connect by prior in_gl_segment.segment_no  = in_gl_segment.parent_segment_no";
		// get company_no
		$this->company_no = $this->_dbConn->GetOne("select gs.segment_no_sz COMPANY_NO  from gl_segment  gs
													where gs.segment_type='COMPANY' 
													and gs.segment_no='".$this->_companyId."'"
													);
	}// end class constructor
	
	/**
	 * Get Current User KPI Setting List
	 *  
	 * @return array
	 * @author Dennis 20090717
	 */
	public function getKPIAlertList($where)
	{
		$sql = <<<eof
			select a.rowid ALERT_ID,
				   a.kpi_desc,
			       a.kpi_catalog_id,
			       b.summary_design_no,
			       b.summary_design_desc,
			       b.dim_01,
			       ehr_stander.f_get_dim_desc(b.dim_01) as dim01_desc,
			       b.dim_02,
			       ehr_stander.f_get_dim_desc(b.dim_02) as dim02_desc,
			       b.dim_03,
			       ehr_stander.f_get_dim_desc(b.dim_03) as dim03_desc,
			       b.dim_04,
			       ehr_stander.f_get_dim_desc(b.dim_04) as dim04_desc,
			       b.dim_05,
			       ehr_stander.f_get_dim_desc(b.dim_05) as dim05_desc,
			       c.kpi_no,
			       c.period_type,   
			       a.important_level,
			       d.summary_kpi_define_name as kpi_define_name,
			       a.kpi_r_val,
			       a.kpi_y_val,
			       a.fact_kpi_val,
			       a.r_action_desc,
			       a.y_action_desc,
			       a.g_action_desc,
			       b.grneral_data_time as data_time,
			       b.period_type
			  from ehr_user_kpi_alert     a,
			       hcp_summary_design     b,
			       hcp_summary_design_kpi c,
			       hcp_summary_kpi_define d
			 where a.company_id        = b.seg_segment_no
			   and a.kpi_catalog_id    = b.summary_design_id
			   and a.company_id        = c.seg_segment_no
			   and a.KPI_COLUMN_NAME        = c.summary_design_kpi_id
			   and b.summary_design_id = c.summary_design_id
			   and c.seg_segment_no    = d.seg_segment_no
			   and c.kpi_no            = d.summary_kpi_define_no		
			   and a.company_id        = :company_id
			   and a.user_seqno        = :user_seqno
			   $where		
eof;
		//$this->_dbConn->debug = true;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId,
		                                           'user_seqno'=>$this->_userSeqNo));
	}// end getKPIAlertList()
	
	/**
	 *  改写function getKPIAlertList() 
	 *  群组,kpi的title从生成的表中去抓
	 */
	public function getKpiAlertListFromGenerate($where="")
	{
		$sql = <<<eof
		select a.company_id,
		       a.user_seqno,
		       a.kpi_desc,
		       a.kpi_catalog_id,
		       a.kpi_column_name,
		       a.important_level,
		       a.kpi_r_val,
		       a.kpi_r_op,
		       a.kpi_y_val,
		       a.dim_01,
		       a.dim_02,
		       a.dim_03,
		       a.dim_04,
		       a.dim_05,
		       a.dim_06,
		       a.dim_07,
		       a.dim_08,
		       a.dim_09,
		       a.r_priority,
		       a.y_priority,
		       a.g_priority,
		       a.r_action_desc,
		       a.y_action_desc,
		       a.g_action_desc,
		       a.r_receiver_to_val,
		       a.r_receiver_cc_val,
		       a.r_receiver_bcc_val,
		       a.y_receiver_to_val,
		       a.y_receiver_cc_val,
		       a.y_receiver_bcc_val,
		       a.g_receiver_to_val,
		       a.g_receiver_cc_val,
		       a.g_receiver_bcc_val,
		       a.is_contain_sub,
		       b.summary_design_no,
		       b.summary_design_desc,
		       b.general_table_name,
		       b.grneral_data_time as data_time,
		       b.dim_01 group_dim_01,
		       b.dim_02 group_dim_02,
		       b.dim_03 group_dim_03,
		       b.dim_04 group_dim_04,
		       b.dim_05 group_dim_05,
		       b.dim_06 group_dim_06,
		       b.dim_07 group_dim_07,
		       b.dim_08 group_dim_08,
		       b.dim_09 group_dim_09,
		       a.period_type,
		       'kpi name'     kpi_define_name,
		       'fact_kpi_val' fact_kpi_val
		  from ehr_user_kpi_alert a, hcp_summary_design b
		 where a.company_id     = b.seg_segment_no
		   and a.kpi_catalog_id = b.summary_design_id
		   and a.company_id     = :company_id
		   and a.user_seqno     = :user_seqno
		   $where
eof;
		//$this->_dbConn->debug = true;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs=$this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId,'user_seqno'=>$this->_userSeqNo));//pr($rs);exit;
		if(is_array($rs)){
			foreach ($rs as $key=>$value){
				// kpi名称
				$sql="select ".$value['KPI_COLUMN_NAME']." from ".$value['GENERAL_TABLE_NAME']."  k  where k.flag='1'";
				$rs[$key]['KPI_DEFINE_NAME']=$this->_dbConn->GetOne($sql);
				// kpi值 ,  HCP_SUMMARY_KPI_xxx 的数据由job更动更新,kpi值只保存最新的
				$rs[$key]['FACT_KPI_VAL']=$this->getKpiFactValue($value);
				$rs[$key]['KPI_LIGHT']=$this->getKpiLightHtml($rs[$key]['FACT_KPI_VAL'],$rs[$key]['KPI_R_VAL'],$rs[$key]['KPI_Y_VAL']);
				$rs[$key]['KPI_LIGHT_TYPE']=$this->getLightType($rs[$key]['FACT_KPI_VAL'],$rs[$key]['KPI_R_VAL'],$rs[$key]['KPI_Y_VAL']);
			}
		}
		return $rs;
	}
	/**
	 * 取kpi实际值
	 */
	public function getKpiFactValue($row=array()){
		$sql="select sum(k.".$row['KPI_COLUMN_NAME'].")  from ".$row['GENERAL_TABLE_NAME']." k 
		      where k.COL_COMPANY_ID='".$this->company_no."'";
		for($i=1;$i<10;$i++){
			if(!empty($row['GROUP_DIM_0'.$i])){
				if($row['GROUP_DIM_0'.$i]=='01' && $row['IS_CONTAIN_SUB']=='Y'){ //部门含下阶
					$subsql="select 
								gs.segment_no_sz
								/*,gs.segment_name,
								gs.segment_no,
								gs.parent_segment_no */
							from gl_segment gs
							where gs.segment_type='DEPARTMENT'
							and  gs.seg_segment_no='".$this->_companyId."'
							start with gs.segment_no_sz='".$row['DIM_0'.$i]."'
							connect by prior gs.segment_no=gs.parent_segment_no
							";
					$sql .= " and COL_GROUP_01_ID in (".$subsql.")";
				}else{
					$conval=empty($row['DIM_0'.$i])?" is null ":" ='".$row['DIM_0'.$i]."'";
					$sql .= " and COL_GROUP_".$row['GROUP_DIM_0'.$i]."_ID".$conval;
				}
			}
		}
		//echo $sql;
		$kpiVal=$this->_dbConn->GetOne($sql);
		//echo $sql;
		return $kpiVal;
	}
	
	/**
	 * 
	 */
	public function getKpiLightHtml($factKpiValue,$kpi_r_val,$kpi_y_val){
		if($factKpiValue>=$kpi_r_val){
			return '<img border=0 src="../img/LampRed.png">';
		}else if($factKpiValue>=$kpi_y_val){
			return '<img border=0 src="../img/LampYellow.png">';
		}else{
			return '<img border=0 src="../img/LampGreen.png">';
		}
	}
	
	public function getLightType($fact_val,$r_val,$y_val)
	{
		if ($fact_val >= $r_val)                     return 'R';
		if ($fact_val >= $y_val && $fact_val<$r_val) return 'Y';
		if ($fact_val < $y_val)                      return 'G';
		return '';
	}// end _getLightType()
	/**
	 * 根据 KPI Alert 名称取得KPI Alert 设定
	 * @param $kpi_desc
	 * @return array
	 * @author Dennis 20090807
	 */
	public function getKPIAlertSetting($kpi_desc)
	{
		$sql = <<<eof
		select kpi_desc,
			   kpi_catalog_id,
		       kpi_column_name,
		       important_level,
		       kpi_r_val,
		       kpi_r_op,
		       kpi_y_val,
		       r_action_desc,
		       y_action_desc,
		       g_action_desc,
		       r_receiver_to_txt,
		       r_receiver_to_val,
		       r_receiver_cc_txt,
		       r_receiver_cc_val,
		       r_receiver_bcc_txt,
		       r_receiver_bcc_val,
		       y_receiver_to_txt,
		       y_receiver_to_val,
		       y_receiver_cc_txt,
		       y_receiver_cc_val,
		       y_receiver_bcc_txt,
		       y_receiver_bcc_val,
		       g_receiver_to_txt,
		       g_receiver_to_val,
		       g_receiver_cc_txt,
		       g_receiver_cc_val,
		       g_receiver_bcc_txt,
		       g_receiver_bcc_val,
		       r_priority,
		       y_priority,
		       g_priority,
		       a.dim_dept_seqno,
		       b.segment_name as dept_name,
		       a.is_contain_sub,
		       a.dim_val_str,
		       a.dim_code_str,
		       a.display_at_home,
		       a.dim_01,
		       a.dim_02,
		       a.dim_03,
		       a.dim_04,
		       a.dim_05,
		       a.dim_06,
		       a.dim_07,
		       a.dim_08,
		       a.dim_09,
		       a.dim_10,
		       a.period_type,
		       a.kpi_table_name
		  from ehr_user_kpi_alert a,
		       gl_segment         b
		 where a.company_id = b.seg_segment_no(+)
		   and a.dim_dept_seqno = b.segment_no(+)
		   and company_id = :company_id
		   and user_seqno = :user_seqno
		   and kpi_desc = :kpi_desc
eof;
		//$this->_dbConn->debug = TRUE;
		return  $this->_dbConn->GetRow($sql,array('company_id'=>$this->_companyId,
												  'user_seqno'=>$this->_userSeqNo,
												  'kpi_desc'=>$kpi_desc));
	}// end getKPIAlertSetting()
	
	
	/**
	 * 有资料就执行 update, 无资料就执行 insert
	 * @param $rowdata array 以DB 栏位名称为下标的 array
	 * @return boolean
	 * @author Dennis 20090807
	 */
	private function _doReplace($rowdata)
	{
		$tableName = 'ehr_user_kpi_alert';
		$keyCols   = array('kpi_desc','company_id','user_seqno');
		
		$defColsVals = array('company_id'  =>$this->_companyId,
							 'user_seqno' =>$this->_userSeqNo);
		//$this->_dbConn->debug = true;
		$result = $this->_dbConn->Replace($tableName,
										  array_merge($rowdata,$defColsVals),
										  $keyCols,
										  true);
		if ($result == '0') return $this->_dbConn->ErrorMsg();
		return true;
	}// end _doReplace()
	
	/**
	 * 新增 KPI 警示
	 * @param $rowdata
	 * @return mixed
	 */
	public function addKPISetting($rowdata)
	{
		return $this->_doReplace($rowdata);
	}// end addKPI()
	
	/**
	 * 编辑 KPI设定
	 * @param $rowdata
	 * @return unknown_type
	 */
	public function updateKPISetting($rowdata)
	{
		return $this->_doReplace($rowdata);
	}// end updateKPI()
	
	/**
	 * 删除自定义 KPI警示
	 * @param $kpi_desc 
	 * @return number
	 */
	public function deleteKPISetting($kpi_desc)
	{
		$sql = 'delete from ehr_user_kpi_alert 
		         where company_id = :company_id
		           and user_seqno = :user_seqno
		           and kpi_desc   = :kpi_desc';
		//$this->_dbConn->debug = true;
		return $this->_dbConn->Execute($sql,array('company_id'  =>$this->_companyId,
							 					  'user_seqno' =>$this->_userSeqNo,
							 					  'kpi_desc'=>$kpi_desc));
	}// end deleteKPI()
	
	/**
	 * 取得 KPI 目录
	 * @return array
	 * @author Dennis 20090717
	 */
	public function getCatalog()
	{
		$sql = <<<eof
		select summary_design_id   as catalog_seqno,
		       summary_design_no ||' - '||
		       summary_design_desc as catalog_desc
		  from hcp_summary_design
		 where seg_segment_no = :company_id
eof;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_NUM);
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId));
	}// end getCatalog()
	
	/**
	 * 根据目录代码取得其可用的 KPI
	 * @param $catalog_seqno
	 * @return array
	 * @author Dennis 20090717
	 */
	public function getKPIByCatalog($catalog_seqno)
	{
		$sql = <<<eof
		select a.summary_design_kpi_id                      as kpi_seqno,
		       a.kpi_no|| ' - ' ||b.summary_kpi_define_name as kpi_desc
		  from hcp_summary_design_kpi a,
		       hcp_summary_kpi_define b
		 where a.seg_segment_no = b.seg_segment_no
		   and a.kpi_no = b.summary_kpi_define_no
		   and a.summary_design_id = :kpi_catalog_seqno
		   and a.seg_segment_no = :company_id
eof;
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId,
												   'kpi_catalog_seqno'=>$catalog_seqno));
	}// end getKPIByCatalog()
	
	/**
	 * KPI 维度
	 * @return array
	 * @author Dennis 20090717
	 */
	public function getDimByCatalog($catalog_seqno)
	{
		$sql = <<<eof
		select dim_01 || ':' || ehr_stander.f_get_dim_desc(dim_01)|| ';' || 
		       dim_02 || ':' || ehr_stander.f_get_dim_desc(dim_02)|| ';' ||
		       dim_03 || ':' || ehr_stander.f_get_dim_desc(dim_03)|| ';' || 
		       dim_04 || ':' || ehr_stander.f_get_dim_desc(dim_04)|| ';' ||
		       dim_05 || ':' || ehr_stander.f_get_dim_desc(dim_05) as dims,
		       period_type,
		       general_table_name
		  from hcp_summary_design
		 where summary_design_id = :catalog_seqno
		   and seg_segment_no    = :company_id
eof;
		//$this->_dbConn->debug=true;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_ASSOC);
		$r = $this->_dbConn->GetRow($sql,array('catalog_seqno'=>$catalog_seqno,
											   'company_id'=>$this->_companyId));
		//pr($r);
		$dims = explode(';',$r['DIMS']);
		$rs = '';
		$k = 0;
		$rx = ''; // 把 KPI dim 和 measure value
		$is_dept = false;
		// 重组符合 json_encode 的 array
		foreach($dims as $k=>$v)
		{
			$v1 = explode(':',$v);
			if (!empty($v1[0])){
				$rs[$k]['DIM_ID'] = $v1[0];
				$rs[$k]['DIM_DESC'] = $v1[1];
				// 如果是部门维度,挑权限内部门资料供选取
				$is_dept = ($rs[$k]['DIM_ID'] == '01');
				// 取权限内的部门资料
				if ($is_dept){
					$rs[$k][$v1[0]] = $this->getReceiverByDept('');
				}
				// reset to false
				$is_dept = false;
				$k++;
			} // end if
		}// end foreach
		if ($rs != '')
		{
			$rx['dim'] = $rs;
			$rx['kpi'] = $this->getKPIByCatalog($catalog_seqno);
			$rx['period'] = $r['PERIOD_TYPE'];
		}// end if
		//pr($rx);exit;
		return $rx;
	}// end getDimByCatalog()

	/**
	 * 取得权限内的员工清单
	 * @param $where
	 * @return array
	 * @author Dennis 20090717
	 */
	public function getReceiverByEmp($where)
	{
		$this->_register();
		$sql = <<<eof
			select a.id            as emp_seqno,
				   a.id_no_sz      as emp_id,
			       a.name_sz       as emp_name,
			       b.segment_no_sz as dept_id,
			       b.segment_name  as dept_name
			  from hr_personnel_base a, gl_segment b
			 where a.seg_segment_no = b.seg_segment_no
			   and a.seg_segment_no_department = b.segment_no
			   and pk_user_priv.f_user_priv(a.id) = 'Y'
			   and a.seg_segment_no = :company_id
			   $where
eof;
		//$this->_dbConn->debug = true;
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId));
	}// end getReceiver()
	
	
	/**
	 * 预先设定的邮件群组
	 * @param $where
	 * @return array
	 * @author Dennis 20090721
	 */
	public function getReceiverByGroup($where)
	{
		$sql= <<<eof
		select group_id, group_desc
		  from ehr_mail_group_master
		 where company_seqno = :company_id
		   and user_seqno    = :user_seq_no
		   $where
eof;
		//$this->_dbConn-> debug  = 1;
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId,
												   'user_seq_no'=>$this->_userSeqNo));
	}// end getReceiverByGroup()
	
	/**
	 * 选取部门，会发给当前的部门主管 (权限内部门)
	 * 
	 * @param $where
	 * @return array
	 * @author dennis 20090721
	 */
	public function getReceiverByDept($where)
	{
		//$this->_dbConn->debug = 1;
		$sql = <<<eof
			select segment_no    as dept_seqno, 
			       segment_no_sz as dept_id, 
			       segment_name  as dept_name
  			  from gl_segment
  		     where seg_segment_no = :company_id
  		       and pk_user_priv.f_dept_priv(:user_seq_no,:company_id1,segment_no) = 'Y'
  		       $where
eof;
		return $this->_dbConn->GetArray($sql,array('company_id'=>$this->_companyId,
												   'user_seq_no'=>$this->_userSeqNo,
												   'company_id1'=>$this->_companyId));
	}// end getReceiverByDept()
	
	/**
	 * Get 权限内的部门资料
	 * 调用 GetReceiverByDept(),然后重组
	 * @param no
	 * @return array index by number
	 * @author Dennis 20090817
	 */
	public function getMyDept()
	{
		$r = $this->getReceiverByDept('');
		$c = count($r);
		$r1 = '';
		if($c>0)
		{
			for($i=0; $i<$c; $i++)
			{
				$r1[$i][0] = $r[$i]['DEPT_SEQNO'];
				$r1[$i][1] = $r[$i]['DEPT_NAME'];
			}// end for loop
		}// end if
		return $r1;
	}// end getMyDept()
	
	/**
	 * Help method
	 * regsiger current login user information
	 * @return void
	 * @author Dennis 20090723
	 */
	private function _register()
	{
		$stmt = <<<eof
		begin pk_erp.p_set_date(sysdate);pk_erp.p_set_segment_no(:company_id);pk_erp.p_set_username(:user_seq_no);end;
eof;
		//$this->_dbConn->debug = 1;
		$this->_dbConn->Execute($stmt,array('company_id'=>$this->_companyId,
											'user_seq_no'=>$this->_userSeqNo));
	}// end _register()
	
	/**
	 * 取指定id的汇部报表设定中的数据
	 *
	 * @param unknown_type $KPI_CATALOG_ID
	 * @return unknown
	 */
	public  function getCatalogRow($KPI_CATALOG_ID){
		$sql="SELECT GENERAL_TABLE_NAME,
		             DIM_01,DIM_02,DIM_03,DIM_04,DIM_05,
		             DIM_06,DIM_07,DIM_08,DIM_09,
		             (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_01
					 ) DIM_01_TITLE,
					 (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_02
					 ) DIM_02_TITLE,
					 (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_03
					 ) DIM_03_TITLE,
					 (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_04
					 ) DIM_04_TITLE,
					 (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_05
					 ) DIM_05_TITLE,
					 (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_06
					 ) DIM_06_TITLE,
					 (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_07
					 ) DIM_07_TITLE,
					 (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_08
					 ) DIM_08_TITLE,
					 (select g.group_define_name 
					    from hcp_summary_group_define g
					    where g.group_define_no=A.DIM_09
					 ) DIM_09_TITLE
		        FROM HCP_SUMMARY_DESIGN a  
		       where a.seg_segment_no='".$this->_companyId."'  
		         and a.summary_design_id='".$KPI_CATALOG_ID."'
		     ";
		//echo $sql;
		$this->_dbConn->SetFetchMode(ADODB_FETCH_BOTH);
		return $this->_dbConn->getRow($sql);
	}
	/**
	 *  get summary group html
	 */
	public function getDimHtml($KPI_CATALOG_ID,$rowdata=array())
	{
		//pr($rowdata);exit;//$rowdata['KPI_CATALOG_ID']
		//部门含下阶
		$is_contain_sub=(!empty($rowdata['IS_CONTAIN_SUB']) && $rowdata['IS_CONTAIN_SUB']=='Y')?'checked':'';
		$row_design=$this->getCatalogRow($KPI_CATALOG_ID);
		//pr($row_design);exit;
		$html='<TABLE  class="bordertable">
				';
		for($i=1;$i<10;$i++){
			if(!empty($row_design['DIM_0'.$i])){
				$selected=empty($rowdata['DIM_0'.$i])?'':$rowdata['DIM_0'.$i];
				/*
				$html .= '<TR><TD width="10%" nowrap class="column-label">'.$row_design['DIM_0'.$i.'_TITLE'].'</TD>  <TD width="90%">';
				$html .= $this->getDimValueList($row_design['GENERAL_TABLE_NAME'],$row_design['DIM_0'.$i],$selected);
				$html .= '</TD></TR>';
				*/
				$html .=  $row_design['DIM_0'.$i.'_TITLE'].'<br>';
				$html .= $this->getDimValueList($row_design['GENERAL_TABLE_NAME'],$i,$row_design['DIM_0'.$i],$selected,$is_contain_sub);
				$html .= '<br>';
				
			}
		}
		$html .= ' </table>';
		//echo $html;	exit;
		return $html;
	}
	/**
	 * 取得Dim值列表
	 *
	 * @param unknown_type $table_name
	 * @param unknown_type $cols_seq
	 * @param unknown_type $selected
	 * @return unknown
	 */
	public function getDimValueList($table_name,$cols_seq,$cols_code,$selected,$is_contain_sub='')
	{
		if(is_defined_column($table_name,"COL_GROUP_".$cols_code."_NAME")){
			$sql="select distinct COL_GROUP_".$cols_code."_ID ID,
		                     COL_GROUP_".$cols_code."_NAME TEXT
		        from ".$table_name."
		         where flag='0'
		      ";	
		}else{
			$sql="select distinct COL_GROUP_".$cols_code."_ID ID,
			                      COL_GROUP_".$cols_code."_ID TEXT
			        from ".$table_name."
			         where flag='0'
			      ";
		}
		if(is_defined_column($table_name,"COL_GROUP_01_ID")){
			//$sql .= " and COL_GROUP_01_ID in (".$this->dept_id_permission.")";
		}
		$rs_dim_value_list = $this->_dbConn->GetArray($sql);
		$html  ='<SELECT name="DIM_0'.$cols_seq.'">'."\r\n";
		$html .= gf_getDropDownListHtml($rs_dim_value_list,$selected);
		$html .= '</SELECT>'."\r\n";
		if($cols_code=='01'){
			$html .= '<div><input '.$is_contain_sub.' style="VERTICAL-ALIGN: middle;" id="is_contain_sub" value="Y" type="checkbox" name="is_contain_sub"> 含下阶</div>';
		}
		//echo $sql;
		return $html;
	}
	/**
	 * get kpi option 
	 *
	 */
	public function getKpiColsHtml($kpi_catalog_id,$selected=''){
		//echo $kpi_catalog_id;exit;
		$catalogRow=$this->getCatalogRow($kpi_catalog_id);//pr($catalogRow);exit;
		$kpiTable=$catalogRow['GENERAL_TABLE_NAME'];
		$sql=" select COLUMN_NAME ID
		  			  from all_tab_cols 
					 where table_name = upper('".$kpiTable."')
					   and column_name like 'COL_KPI_%'
				  ";
		$arrCols=$this->_dbConn->GetArray($sql);
		$n=count($arrCols);
		for($i=0;$i<$n;$i++){
			$sql="select ".$arrCols[$i]['ID']." from ".$kpiTable."  k  where k.flag='1'";
			$arrCols[$i]['TEXT']=$this->_dbConn->GetOne($sql);
		}
		return gf_getDropDownListHtml($arrCols,$selected);
	}
	
/**
	 * get kpi option 
	 *
	 */
	public function getKpiTableName($KPI_CATALOG_ID){
		$sql="SELECT GENERAL_TABLE_NAME
		        FROM HCP_SUMMARY_DESIGN a  
		       where a.seg_segment_no='".$this->_companyId."'  
		         and a.summary_design_id='".$KPI_CATALOG_ID."'
		     ";
		//echo $sql;
		return $this->_dbConn->GetOne($sql);
		
	}
	
	/**
	 * 解析邮件地址
	 */ 
	public  function _parseMailAddr($mstr)
	{
		//$mstr = 'rtype:emp|emp_seqno:62026|emp_id:0102TEST|emp_name:夜班津贴;rtype:emp|emp_seqno:62052|emp_id:CH00008|emp_name:小小;rtype:group|group_id:141|group_desc:testt;rtype:dept|dept_seqno:4053|dept_id:TEST01|dept_name:第一部門;rtype:dept|dept_seqno:4054|dept_id:TEST02|dept_name:測試3;';
		$mail_addrs = '';
		if ($mstr)
		{
			$s = explode(';',substr($mstr,0,-1));
			//pr($s);
			$c = count($s);
			$rs = '';				
			for ($i=0; $i<$c; $i++)
			{
				$rs[$i] = explode('|',$s[$i]);

				for ($j=0; $j<count($rs[$i]); $j++)
				{   
					$s1 = explode(':',$rs[$i][$j]);
					$rs[$i][$j] = $s1[1];
				}// end loop
				switch ($rs[$i][0])
				{
					case 'emp':
						$mail_addrs .= $this->_getMailByEmpSeqno($rs[$i][1]);
						break;
					case 'dept':
						$mail_addrs .= $this->_getMailByDept($rs[$i][1]);
						break;
					case 'group':
						$mail_addrs .= $this->_getMailByMailGroup($rs[$i][1]);
						break;
					default:break;
				}// end switch
			}// end loop
		}// end if
		//echo $mail_addrs;
		return substr($mail_addrs,0,-1);
	}// end _parseMailAddr()
	
	/**
	 * 根据员工代码流水号挑邮件地址
	 */
	private function _getMailByEmpSeqno($emp_seqno_no)
	{
		$sql = <<<eof
			select email 
	          from hr_personnel_base 
	         where id = :emp_seqno 
	           and pk_history_data.f_get_value(seg_segment_no,id, sysdate, 'E') = 'JS1'
	           and email is not null
eof;
		$r = $this->_dbConn->GetOne($sql,array('emp_seqno'=>$emp_seqno_no));
		if($r)
		{
			return $r.';';
		}
		return '';
	}// end _getMailByEmpSeqno()
	/**
	 * 挑当下部门主管(第一主管/第二主管)的邮件地址
	 *
	 * @param string $dept_senqo
	 * @return string mail address
	 * @author Dennis 20090813
	 */
	private function _getMailByDept($dept_senqo)
	{
		$sql = <<<eof
			select b.email as mgr1_mail, 
			       c.email as mgr2_mail
			  from gl_segment_his    a, 
			       hr_personnel_base b, 
			       hr_personnel_base c
			 where a.seg_segment_no = b.seg_segment_no
			   and a.dept_id = b.seg_segment_no_department
			   and a.seg_segment_no = c.seg_segment_no
			   and a.dept_id = c.seg_segment_no_department
			   and ((a.leader_emp_id = b.id and
			         pk_history_data.f_get_value(b.seg_segment_no, b.id, sysdate, 'E') ='JS1' and
			         b.email is not null) or
			        (a.leader_emp_id2 = b.id and
			         pk_history_data.f_get_value(c.seg_segment_no, c.id, sysdate, 'E') = 'JS1' and 
			         c.email is not null))
			   and  a.dept_id = :dept_seqno
eof;
		$r = $this->_dbConn->GetRow($sql,array('dept_seqno'=>$dept_senqo));
		$m = '';
		if (is_array($r))
		{
			if ($r['MGR1_MAIL']) $m .= $$r['MGR1_MAIL'].';';
			if ($r['MGR2_MAIL']) $m .= $$r['MGR2_MAIL'].';';
		}
		return $m;
	}// end _getMailByDept()
		
	/**
	 * 根据群组取邮件地址
	 * 
	 * @param number $group_seqno  mail group seqno
	 * @return string
	 * @author Dennis 20090818
	 */
	private function _getMailByMailGroup($group_seqno)
	{
		$sql = <<<eof
			select b.email
			  from ehr_mail_group_detail a, hr_personnel_base b
			 where a.company_seqno      = b.seg_segment_no
			   and a.receiver_emp_seqno = b.id
			   and a.group_id           = :group_id
			   and pk_history_data.f_get_value(a.company_seqno,
			                                   a.receiver_emp_seqno,
			                                   sysdate,
			                                   'E') = 'JS1'
			   and b.email is not null
eof;
		$r = $this->_dbConn->GetArray($sql,array('group_id'=>$group_seqno));
		$mails = '';
		if (is_array($r))
		{
			for ($i=0; $i<count($r); $i++)
			{
				$mails .= $r[$i]['EMAIL'].';';
			}//end for loop
		}// end if
		return $mails;
	}// end _getMailByMailGroup()

	/**
	 * Send Mail
	 *
	 * @param string $subject  mail subject
	 * @param string $to       mail to     
	 * @param string $cc       mail cc
	 * @param string $bcc      mail bcc
	 * @param string $body     mail content
	 * @param number $priority 紧急度 1 - High 3 - Normal(default) 5 - Low
	 * @param string $attach_file  attachment path(absolute/relate) 
	 * @return void
	 * @author Dennis 20090813
	 */
	public  function _sendMail($subject,$to,$cc='',$bcc='',$body='',$priority=1,$attach_file = '')
	{
		$smtp = $this->_getSMTPInfo();//pr($smtp);
		$mail = new PHPMailer();
		$mail->Priority = $priority;	// 紧急度 1 - High 3 - Normal(default) 5 - Low
		$mail->CharSet= 'utf-8';		// 设置邮件内容字符集
		$mail->SetLanguage('zh');		// 设置语言,出错时显示的错误信息.
		$mail->IsSMTP();				// 设置使用 SMTP
		$mail->Host = $smtp['SMTP_SERVER'];   // 指定的 SMTP 服务器地址
		$mail->SMTPAuth = true;         // 设置为安全验证方式
		$mail->Username = $smtp['SMTP_USER'];  // SMTP 发邮件人的用户名
		$mail->Password = $smtp['SMTP_PASS'];  // SMTP 密码
		$mail->From 	= 'developer@areschina.com';
		$mail->FromName = 'eHR System KPI Alert';
		// to mail
		if ($to != '')
		{
			$to_m = explode(';',$to);
			for ($i=0;$i<count($to_m); $i++)
			{
				$mail->AddAddress($to_m[$i]);//收件人地址
			}// end loop
		}// end loop
		
		// cc mail
		if ($cc != '')
		{
			$cc_m = explode(';',$cc);
			for ($i=0;$i<count($cc_m); $i++)
			{
				$mail->AddCC($cc_m[$i]);  // 抄送
			}// end loop
		}// end loop
		
		// bcc mail
		if ($bcc != '')
		{
			$bcc_m = explode(';',$bcc);
			for ($i=0;$i<count($bcc_m); $i++)
			{
				$mail->AddCC($bcc_m[$i]);  // 密送
			}// end loop
		}// end loop     
		$mail->AddReplyTo('no-replay@areschin.com', 'Do not replay mail');   //回复地址
		$mail->Subject = $subject;     // 标题
		$message = $body;
		$message .= '<hr size="1"/>';
		$message .= 'Power by eHR&trade; Alert System <br><div align="right"> Copyright &copy;'.date('Y').' ARES CHINA.</div>';
		//$mail->AddEmbeddedImage('D:/eHR4/eHR3/img/logo.gif', 'logo', 'logo.gif','base64', 'image/gif');
		//$mail->AddEmbeddedImage('D:\code.jpg', 'logo', 'logo.jpg','base64', 'image/jpeg');
		//if (is_readable('D:\test.txt')) unlink('D:\test.txt');
		//$mail->WordWrap = 50;			// set word wrap to 50 characters
		$mail->IsHTML(true);           // 设置邮件格式为 HTML                              // 
		//$mail->AddAttachment('D:/eHR4/eHR3/img/hcplogin_wellcome.swf');         // add attachments
		//$mail->AddAttachment('D:/eHR4/eHR3/img/hcplogin_ess.gif', 'ess.gif');    // optional name
		//$mail->Body    = '<img src="cid:logo" alt="logo"/>'.$message;
		if(!empty($attach_file)){    //$attach_file='f:/test_php/testMail/02-mvc_in_fleaphp.png';
			$mail->AddAttachment($attach_file);         // add attachments
		}
		$mail->Body    =  $message;
		$mail->AltBody = 'Please enable your mail application HTML support functional.';
		if(!$mail->Send())
		{
		  $this->wirteLog('error','Mailer Error: ' . $mail->ErrorInfo);
		  
		  // 邮件发送失败,发邮件通知管理员
		  if ($this->_config['mail_error_log'])
		  {
			  $this->_sendMail('Mail Send Error, Please Check It',
			  				   $this->_config['admin_mail'],'','',
			  				   $mail->ErrorInfo,1);
		  }// end if
		}else{
		  $this->wirteLog('success','Mail Sent: ' . $to);
		}// end if
	}// end _sendMail()
	
	public function wirteLog($log_type,$log_txt)
	{
		if ($this->_config['writlog'])
		{
			if($log_type == 'error')
			{
				$logfile = $this->_config['log_path'].'/error.log';
			}
			if ($log_type == 'success')
			{
				$logfile = $this->_config['log_path'].'/success.log';
			}// end if
			$fh = fopen($logfile,'a+');
			if (is_resource($fh))
			{
				fwrite($fh,$log_txt);
			}// end if
		}// end if
	}// end wirteLog()
	
	/**
	 * Help Function
	 * Get SMTP information for send mail
	 * @param no
	 * @return array   row type array
	 * @author Dennis  20090813
	 */
	private function _getSMTPInfo()
	{
		$sql = <<<eof
           select parameter_value as smtp_server,
                  value1          as smtp_user,
                  value2          as smtp_pass
             from pb_parameters
            where parameter_type = 'SMTP'
              and parameter_id   = 'IP ADDRESS'
              and seg_segment_no = :company_id
eof;
		return $this->_dbConn->GetRow($sql,array('company_id'=>$this->_companyId));
	}// end _getSMTPInfo()
		
}// end class AresKPI

