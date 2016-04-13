<?php
/*
 *  自定程序导出
 *  Create by BollYuan 20090806
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/umd_program_export_DB.php $
 *  $Id: umd_program_export_DB.php 3083 2011-03-17 05:54:16Z dennis $
 *  $Rev: 3083 $ 
 *  $Date: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 *  $LastChanged: bollyuan $   
 *  $LastChangedDate: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 *********************************************************/
/*****
	delete from EHR_PROGRAM_SETUP_TABLE where PROGRAM_NO='ESND001';
	delete from EHR_PROGRAM_SETUP_MASTER where PROGRAM_NO='ESND001';
	delete from EHR_PROGRAM_SETUP_GROUP where PROGRAM_NO='ESND001';
	delete from EHR_PROGRAM_SETUP_COLUMN where PROGRAM_NO='ESND001';
	
	delete from HCP_MUTI_LANG_PK a
	where exists (select 1 from  ehr_program_setup_column epsc
	                       where epsc.muti_lang_pk=a.muti_lang_pk
				 and epsc.program_no='ESND001');
	select * from EHR_PROGRAM_SETUP_TABLE where PROGRAM_NO='ESND001';
	select * from EHR_PROGRAM_SETUP_MASTER where PROGRAM_NO='ESND001';
	select * from EHR_PROGRAM_SETUP_GROUP where PROGRAM_NO='ESND001';
	select * from EHR_PROGRAM_SETUP_COLUMN where PROGRAM_NO='ESND001';
   
    select * from  HCP_MUTI_LANG_PK a
	   where exists (select 1 from  ehr_program_setup_column epsc
	                       where epsc.muti_lang_pk=a.muti_lang_pk
			         and epsc.program_no='ESND001');
*/

$program_no = empty($_GET['program_no'])?'user_define_program':$_GET['program_no'];

header("Pragma: public");
header("Cache-Control: private");
header("Expires: 0"); 
header("Content-type: text/html; charset=utf-8"); 
header('Content-Disposition: attachment; filename="'.$program_no.'.sql"');
header("Content-Description: PHP3 Generated Data"); 

function doExpor($program_no,$table_name){
	global  $g_db_sql;
	$sql1 = "select * from ".$table_name." 
			where PROGRAM_NO='".$program_no."'
			";
	$sql2 = "select * from  HCP_MUTI_LANG_PK a
   			 where exists (select 1 from  ehr_program_setup_column epsc
                       where epsc.muti_lang_pk=a.muti_lang_pk
		         and epsc.program_no='".$program_no."')
			 ";
	$sql3 = "select a.*  from EHR_PROGRAM_COLUMN_LANG a
			 where exists (
						 select 1 from EHR_PROGRAM_SETUP_GROUP b 
						 where a.muti_lang_pk=b.muti_lang_pk
						 and b.program_no='".$program_no."'
						 )
			";
	if(strtoupper($table_name)=='EHR_PROGRAM_COLUMN_LANG'){
		$sql=$sql3;
	}else if(strtoupper($table_name)=='HCP_MUTI_LANG_PK'){
		$sql=$sql2;
	}else{
		$sql=$sql1;
	}
	$g_db_sql->SetFetchMode(ADODB_FETCH_ASSOC);
	$rs=$g_db_sql->getArray($sql);
	echo "\r\n--------------".$table_name."--------------\r\n";
	$n=count($rs);
	for($i=0;$i<$n;$i++){
	    $row = $rs[$i];	
		$str_cols="";
		$str_value="";
		foreach ($row as $key=>$value){
			$str_cols .= $key.",";
			if($value==null){
				$str_value .= "null,";
			}else {
				if($key=='CREATE_DATE' || $key=='UPDATE_DATE'){
					$str_value .= "sysdate,";
				}else{
					$str_value .= "'".str_ireplace("'", "''", $value)."',";
				}
			}
		}
		$str_cols=substr($str_cols,0,strlen($str_cols)-1);
		$str_value=substr($str_value,0,strlen($str_value)-1);
		echo "insert into ".$table_name." ( ".$str_cols.") values (".$str_value.");\r\n\r\n";
	}
}
function doExportDelteSql($program_no=''){
	$sql = "delete from HCP_MUTI_LANG_PK a  where exists (select 1 from  ehr_program_setup_column epsc  where epsc.muti_lang_pk=a.muti_lang_pk and epsc.program_no='".$program_no."');\r\n";
	$sql .= "delete from EHR_PROGRAM_COLUMN_LANG a
			 where exists (
						 select 1 from EHR_PROGRAM_SETUP_GROUP b 
						 where a.muti_lang_pk=b.muti_lang_pk
						 and b.program_no='".$program_no."'
						 );\r\n";
	$sql  .= "delete from EHR_PROGRAM_SETUP_TABLE where PROGRAM_NO='".$program_no."';\r\n";
	$sql .= "delete from EHR_PROGRAM_SETUP_MASTER where PROGRAM_NO='".$program_no."';\r\n";
	$sql .= "delete from EHR_PROGRAM_SETUP_GROUP where PROGRAM_NO='".$program_no."';\r\n";
	$sql .= "delete from EHR_PROGRAM_SETUP_COLUMN where PROGRAM_NO='".$program_no."';\r\n";
	
	echo $sql;
}


echo "begin;\r\n";
doExportDelteSql($program_no);
doExpor($program_no,'EHR_PROGRAM_SETUP_TABLE');
doExpor($program_no,'EHR_PROGRAM_SETUP_MASTER');
doExpor($program_no,'EHR_PROGRAM_SETUP_GROUP');
doExpor($program_no,'EHR_PROGRAM_SETUP_COLUMN');
doExpor($program_no,'HCP_MUTI_LANG_PK');
doExpor($program_no,'EHR_PROGRAM_COLUMN_LANG');
exit("\r\n");
?>