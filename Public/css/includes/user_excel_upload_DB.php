<?php
/*
 *  Upload Import data excel
 *  
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/user_excel_upload_DB.php $
 *  $Id: user_excel_upload_DB.php 3769 2014-05-30 07:28:26Z dennis $
 *  $Rev: 3769 $ 
 *  $Date: 2014-05-30 15:28:26 +0800 (周五, 30 五月 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-05-30 15:28:26 +0800 (周五, 30 五月 2014) $
 ****************************************************************************/

include_once 'AresAction.class.php';
class ExcelUpload extends AresAction {
	
	public $program_name = "user_excel_upload";
	
	public function actionList(){
		$id=empty($_GET['id'])?'':$_GET['id'];
		if(!empty($id)){
			$this->tpl->assign("showRunType", 'Y');
		}else{
			$this->tpl->assign("showRunType", 'N');
		}
		$sql = "select setup_id id, import_desc text, import_sql_format, is_active
				  from ehr_upload_setup";
		$rs=$this->db->getArray($sql);
		
		if(!empty($_GET['used_program'])){
			$sql =<<<eof
				select setup_id id
				  from ehr_upload_setup
				 where action_type = 'program'
				   and is_active = 'Y'
				   and import_sql_format = :sql_format
eof;
			$setup_id=$this->db->GetOne($sql,array('sql_format'=>$_GET['used_program']));
			if(!empty($setup_id)) $id=$setup_id;
		}
		
		$sql = "select import_desc text from ehr_upload_setup where setup_id = :setup_id";
		$import_desc = $this->db->GetOne($sql,array('setup_id'=>$id)); //echo $import_desc;exit;
		$this->tpl->assign("import_desc_lable", $import_desc);

		$this->tpl->assign("list", $rs);
		$this->tpl->assign('download_link',$this->_getDownloadLink($rs,$id));
		$this->tpl->assign('setupid',$id);
		//pr($rs);exit;
		$this->tpl->assign("show", 'List');
	}
	
	/**
	 * Get Template Download Link
	 * @param array  $rs template type array
	 * @param string $id
	 * @return string
	 */
	private function _getDownloadLink($rs,$id)
	{
		$html ='';
		foreach ($rs as $key=>$row)
		{
			if (strtolower($row['ID']) == strtolower($id))
			{
				$html .= '<a href="'.DOCROOT."/docs/".$row['IMPORT_SQL_FORMAT'].'.xls">'.$row['TEXT'].'</a>';
				break;
			}
		}
		return $html;
	}
    
    /*
	* Adde by dennis 2012-03-24 for fixed error message not clear
	*/
	function file_upload_error_message($error_code) {
		switch ($error_code) { 
			case UPLOAD_ERR_INI_SIZE: 
				return "檔案大小超過最大上載限制:".ini_get('upload_max_filesize');
			case UPLOAD_ERR_FORM_SIZE: 
				return "檔案個數超過表單最大限制.".@$_POST['MAX_FILE_SIZE'];
			case UPLOAD_ERR_PARTIAL: 
				return "檔案上載不完整,上載失敗.";
			case UPLOAD_ERR_NO_FILE: 
				return "未上載任何檔案."; 
			case UPLOAD_ERR_NO_TMP_DIR: 
				return '無檔案上載暫存目錄，請聯絡系統管理員.'; 
			case UPLOAD_ERR_CANT_WRITE: 
				return "檔案上載後無法寫到硬盤，請聯絡系統管理員."; 
			case UPLOAD_ERR_EXTENSION: 
				return "不支持的檔案類型.";
			case UPLOAD_ERR_EMPTY:
				return "檔案是空的";
			default: 
				return '未知檔案上載錯誤,請重試'; 
		} 
	}
	
	public function actionSave(){
		//pr($_POST);
		if(empty($_POST['ORG_PIC_SEQ'])){
			$sql = "select ehr_md_org_chart_s.nextval from dual";
			$_POST['ORG_PIC_SEQ']= $this->db->GetOne($sql);
		}		
		$uploaddir = $this->getUploadDir();		
		if ($_FILES['userfile']['error'] !== UPLOAD_ERR_OK)
		{
			$error_message = $this->file_upload_error_message($_FILES['userfile']['error']);
			showMsg($error_message,'error');exit;
		}
		if (!empty($_FILES['userfile']['name']))
		{
			$uploadfile = $uploaddir . str_pad($_POST['ORG_PIC_SEQ'], 4, "0", STR_PAD_LEFT ).'_'. basename($_FILES['userfile']['name']);
			copy($_FILES['userfile']['tmp_name'], $uploadfile);
		}else{
			$uploadfile = $uploaddir.$_POST['hidden_file_name'];
		}
			
		$this->save2db($_POST['SETUP_ID'],$uploadfile);
		
		$sql = "select setup_id, import_desc, import_sql_format, action_type, is_active
				  from ehr_upload_setup
				 where setup_id = :setup_id";
		$rs = $this->db->getRow($sql,array('setup_id'=>$_POST['SETUP_ID']));
		//pr($rs);
		if($rs['ACTION_TYPE']=='program'){
			header('Location: ?scriptname='.$rs['IMPORT_SQL_FORMAT'].'&id='.$_POST['SETUP_ID']);exit;
		}
		//showMsg('導入成功/Import Successfull.','success' );
	}
	/**
	 * Get Excel Data & save to database
	 * @param string $id
	 * @param string $url
	 * @return void
	 */
	function save2db($id,$url){
		include_once 'phpExcelReader/Excel/reader.php';
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('UTF-8');
		$data->read($url);
		$numCols = $data->sheets[0]['numCols']>50 ? 50 : $data->sheets[0]['numCols'];
		
		/// 清除当前user导入临时表的数据
		/* move tto emp_emport_DB.php, delete after import success
		$sql = <<<eof
		   delete from ehr_upload_data 
		 	where company_id = :company_id
			  and emp_id = :emp_id 
			  and setup_id = :setup_id
eof;
		$this->db->Execute($sql,array('company_id'=>$this->_companyId,
				'emp_id'=>$this->_userEmpId,'seutp_id'=>$id));*/
		
		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {			
			$strCols= '';
			$strVal = '';
			for ($j = 1; $j <= $numCols; $j++) {
				$cellval = isset($data->sheets[0]['cells'][$i][$j]) ?
						   $data->sheets[0]['cells'][$i][$j] : '*.*';				
				if ($cellval!= '*.*'){
					$strCols .= ',col'.$j;
					$strVal  .= ",'".$cellval."'";
				}
			}
			if ($cellval != '*.*')
			{
				$sql="insert into ehr_upload_data(
							company_id,
							emp_id,
							setup_id,
							line_no
							".$strCols."
					   ) values (
					        '".$this->_companyId."',
					        '".$this->_userEmpId."',
					        '".$id."',
					        '".$i."'
					        ".$strVal."
					   )";
				//echo $sql."<hr>";
				$ok = $this->db->Execute($sql);
				if (!$ok){
					showMsg('导入数据失败.SQL:<br>'.$sql.'<br>,Error Message:'.$this->db->ErrorMsg(),'error');
				}
			}
		}
		// delete file when imp finished
		unlink($url);
	}
	/**
	 * Get File Upload Dir from database
	 * delete the file after read
	 * @author Dennis 2011-11-16 last update by Dennis 2013-03-05
	 */
	function getUploadDir() {
		return '../upload/excel/temp/'; // modify by dennis 2013-03-05 for fixed overtime_apply_import error
		/*
		$sql = <<<eof
		select parameter_value
		  from pb_parameters
		 where parameter_type = 'ATTACH'
		   and parameter_id = 'UPLOAD_DIR' -- HCP upload.php used, cannot change by dennis 2013-03-05
		   and seg_segment_no = :company_id
eof;
		$uploaddir = $this->db->GetOne($sql,array('company_id'=>$_SESSION['user']['company_id']));
		return $uploaddir;
		*/
	}
}

/*  controller */
if(empty($_GET['do'])) $_GET['do']='List';
$upload = new ExcelUpload();
$upload->run();
