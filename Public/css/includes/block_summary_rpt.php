<?php
/*
 *  summary_rpt 汇总报表的处理 create by boll 20090728 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/block_summary_rpt.php $
 *  $Id: block_summary_rpt.php 3083 2011-03-17 05:54:16Z dennis $
 *  $Rev: 3083 $ 
 *  $Date: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2011-03-17 13:54:16 +0800 (周四, 17 三月 2011) $
 *********************************************************/
if (isset($_GET ['scriptname'])){
	//过滤掉下条件的群组
	function filter_group($cols_arr,$filter_arr){
		$rs_arr=array();
		foreach ($cols_arr as $key=>$value){
			$sub_key='';
			$sub = substr($key,strlen($key)-3,3);
			if($sub == '_ID'){
				$sub_key= substr($key,0,strlen($key)-3);
			}
			$sub = substr($key,strlen($key)-5,5);
			if($sub == '_NAME'){
				$sub_key= substr($key,0,strlen($key)-5);
			}
			$key_id   = $sub_key.'_ID';
			$key_name = $sub_key.'_NAME';
			//echo $key_id.'--'.$key_name.'<br>';
			if(array_key_exists($key_id, $filter_arr)){
				if(!empty($filter_arr[$key_id]) && $filter_arr['_op_'.$key_id]=='='){	
				}else{
					$rs_arr[$key]=$value;
				}
			}else if(array_key_exists($key_name, $filter_arr)){
				if(!empty($filter_arr[$key_name]) && $filter_arr['_op_'.$key_name]=='='){
				}else{
					$rs_arr[$key]=$value;
				}
			}else{
				$rs_arr[$key]=$value;

			} 
		}
		return $rs_arr;
	}
	
	//取得群组array, 系列
	function get_col_summary_group_array($p_colstr,$p_columns_Array,$p_sql){
		global $g_db_sql;
		
		$sql="select distinct ";
		foreach ($p_columns_Array as $key=>$value){	
			$pos = strpos( $key,$p_colstr);
			if(false===$pos){
				//echo 'no exist';
			}else {
				$sql .= $key.',';
			}
		}
		if($sql=="select distinct ") return array();
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " from (".$p_sql.")";
		$rs = $g_db_sql->getArray($sql); //echo $sql;pr($rs);
		return $rs;
	}
	
	//取得统计日期array ,x轴
	function get_col_summary_date_array($p_colstr,$p_columns_Array,$p_sql){
		$rs=get_col_summary_group_array($p_colstr,$p_columns_Array,$p_sql);
		$col_summary_date_array=array();
		if(empty($rs)) $rs=array();
		foreach ($rs as $key=>$value){
			list($arrkey,$arrvalue)=each($value);
			$pos = strpos( $arrkey,$p_colstr);
			if(false===$pos){
				//echo 'no exist';
			}else {
				$col_summary_date_array[]=$arrvalue;
			}
		}
		return $col_summary_date_array;
	}
	
	// 初始化kpi数组 ，Y轴
	function ini_graph_arry($p_group_arr,$p_date_arr,$p_kpi_cols_arr){
		$arr=array();
		$x=count($p_group_arr);
		$y=count($p_date_arr);
		foreach ($p_kpi_cols_arr as $kpi_key=>$kpi_val){
			for($i=0;$i<$x;$i++)
			   for($j=0;$j<$y;$j++)
			      $arr[$kpi_val][$i][$j] = null;
		}
		return $arr;
	}
	
	//取绘图数据array
	function getGraphData($p_data_arr,$p_group_arr,$p_date_arr,$p_date_column_name,$p_kpi_cols_arr=array()){
		//pr($p_kpi_cols_arr);
		$rs_data=ini_graph_arry($p_group_arr,$p_date_arr,$p_kpi_cols_arr);
		if(empty($p_data_arr)) $p_data_arr=array();
		foreach ($p_data_arr as $key=>$val){
			//pr($val);
			$group_pos = 0;
			$n=count($p_group_arr);
			for ($i=0;$i<$n;$i++){ //与栏位比较
				$is_equ=true;
				foreach ($p_group_arr[$i] as $col_key=>$col_value){
					if($val[$col_key]<>$col_value) {
						 $is_equ=false;
						 break;
					}
				}
				if($is_equ){
					$group_pos=$i;
					break;
				}
			}
			///echo '--'.$group_pos.'--<br>';//系列的下标
			$date_pos=array_search($val[$p_date_column_name],$p_date_arr);
			//echo '--'.$group_pos.'--*--'.$date_pos.'--<br>';//统计时间的下标
			foreach ($p_kpi_cols_arr as $kpi_key=>$kpi_val){
				$rs_data[$kpi_val][$group_pos][$date_pos]=$val[$kpi_val];
			}
		}
		return $rs_data;
	}
	
	// 统计日期字段名
	function get_date_column_name($p_colstr,$p_columns_Array){
		foreach ($p_columns_Array as $key=>$value){	
			$pos = strpos( $key,$p_colstr);
			if(false===$pos){
				//echo 'no exist';
			}else {
				return $key;
			}
		}
		return '';
	}
	
	// kpi字段名
	function get_kpi_column_name_array($p_colstr,$p_columns_Array){
		$kpi_col_arr=array();
		foreach ($p_columns_Array as $key=>$value){	
			$pos = strpos( $key,$p_colstr);
			if(false===$pos){
				//echo 'no exist';
			}else {
				$kpi_col_arr[] = $key;
			}
		}
		return $kpi_col_arr;
	}
	
	// kpi字段名/描述   id/text
	function get_kpi_column_list_array($p_colstr,$p_columns_Array){
		$kpi_col_arr=array();
		foreach ($p_columns_Array as $key=>$value){	
			$pos = strpos( $key,$p_colstr);
			if(false===$pos){
				//echo 'no exist';
			}else {
				$kpi_col_arr[] = array('ID'=>$key,'TEXT'=>$value['title']);
			}
		}
		return $kpi_col_arr;
	}
	
	$menu_code=$_GET ['scriptname'];
	$summary_rpt_queryform=empty($_SESSION [$menu_code] ['queryform'])?array():$_SESSION [$menu_code] ['queryform'];
	
	$data_source=$gridview->getDataSource();// 描图数据与分页绑定
	$data_source=$g_db_sql->getArray($sql);// 描图数据不绑定分页
	
	$date_column_name        = get_date_column_name('_COL_SUMMARY_DATE',$column_config);
	$col_summary_date_array  = get_col_summary_date_array('_COL_SUMMARY_DATE',$column_config,$sql);
	$kpi_cols_arr            = get_kpi_column_name_array('_COL_KPI_',$column_config);
	$filter_group_arr        = filter_group($column_config,$summary_rpt_queryform);
	//不显示下了=条件的群组字段名
	$col_summary_group_array = get_col_summary_group_array('_COL_GROUP_',$filter_group_arr,$sql);
	//显示所有群组字段名
	if(count($col_summary_group_array)==0) $col_summary_group_array = get_col_summary_group_array('_COL_GROUP_',$column_config,$sql);
	$graph_data=getGraphData(
								$data_source,
								$col_summary_group_array,
								$col_summary_date_array,
								$date_column_name,
								$kpi_cols_arr
							);
							
	//$_SESSION [$_GET ['scriptname']] ['data']        = $gridview->getDataSource();
	$_SESSION [$menu_code] ['column']                  = $column_config;
	$_SESSION [$menu_code] ['col_summary_date_array']  = $col_summary_date_array;
	$_SESSION [$menu_code] ['col_summary_group_array'] = $col_summary_group_array;
	$_SESSION [$menu_code] ['graph_data']              = $graph_data;
	
	
	//  处理界面，用户选绘图条件
	$g_tpl->assign ('is_summary_rpt', true );
	$g_tpl->assign ('menu_code', $menu_code );
	$kpi_column_list  = get_kpi_column_list_array('_COL_KPI_',$column_config);
	
	$kpi_column = empty($_SESSION[$menu_code]['kpi_column'])?'':$_SESSION[$menu_code]['kpi_column'];
	$g_tpl->assign ('kpi_option', gf_getDropDownListHtml($kpi_column_list,$kpi_column));
	
	$chart_type = empty($_SESSION[$menu_code]['chart_type'])?'':$_SESSION[$menu_code]['chart_type'];
	$g_tpl->assign ('chart_type', $chart_type);
	
	//pr($_SESSION);
	//pr($col_summary_group_array);
	//echo $menu_code.'<br>'; 
	//pr($column_config);
	//echo $sql;
	//pr($data_source);
}


	
?>
