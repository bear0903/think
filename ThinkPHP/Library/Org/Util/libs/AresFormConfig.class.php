<?php
/**
 *  
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresFormConfig.class.php $
 *  $Id: AresFormConfig.class.php 698 2008-11-19 05:51:54Z dennis $
 *  $Rev: 698 $ 
 *  $Date: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 \****************************************************************************/
	class FormConfig
	{
		private $_appId;
		private $_dbAdapater;

		/*
		* GridView & SingleForm Configuration
		* 2-D array for config
		*@var array 
		*/
		private $_formConfig;		
		public function __construct($appid,$dbadapter)
		{
			$this->_appId = $appid;
			$this->_dbAdapter = $dbadapter;

			// default Get Cofiguration Data
			$this->_getFormConfig();
		}// end class constructor

		public function __get($varname)
		{
			if(array_key_exists(strtoupper($varname),$this->_formConfig))
			{
				return $this->$varname;
			}
			else
			{
				trigger_error('Undefined Property :<b>'.$varname.'<b/>',E_USER_ERROR);
			}// end try catch
			return '';
		}// end magic function __get

		private function _getFormConfig()
		{
			$sql = "select page_size,
						   default_where,
						   default_order_by,
						   allow_sorting,
						   sort_mode,
						   allow_selected,
						   allow_mouse_event,
						   allow_paging,
						   allow_querying,
						   allow_grouping,
						   header_paging,
						   footer_paging,
						   paging_theme,
						   application_type,
						   allow_alternating_row,
						   alternating_row_style,
						   alternating_bgcolor,
						   alternating_fontcolor,
						   gridview_style,
						   header_style,
						   selected_row_style,
						   width,
						   height,
						   ui_style,
						   comments,
						   is_show,   /* Default Query Gridview Data */
						   show_where,/* Query Default Where Only Use Once */
						   comments,
						   result_sql
					  from ehr_program_setup_master 
					 where program_no ='%s'";
			$this->_formConfig = $this->_dbAdapter->fetchRow(sprintf($sql,$this->_appId));
		}// end _getFormConfig()

		private function _getTargetTable()
		{
			$sql = "select table_name, 
						   table_allies_name, 
						   is_define, 
						   relation_sql
					  from ehr_program_setup_table
				     where program_no = '%s'";
			Return $this->_dbAdapter->fetchAll(sprintf($sql,$this->_appId));
		}// end _getSingleForm()

		public function getColumnConfig($lang)
		{
			$sql ="select table_name,
						   column_name,
						   ehr_stander.f_get_muti(muti_lang_pk,'%s')as prompt_text,
						   data_type,
						   allow_sorting,
						   width,
						   height,
						   align,
						   class_name,
						   format_str,
						   column_type,
						   bgcolor,
						   font_color,
						   font_name,
						   checked_value,
						   data_source,
						   data_source_type,
						   muti_lang_pk,
						   allow_querying,
						   is_rang_condition,
						   query_column_type,
						   data_source,
						   group_id,
						   column_seq
					  from ehr_program_setup_column 
					 where program_no = '%s'
					   and display = '1'
					 order by column_seq";
			return $this->_dbAdapter->fetchAll(sprintf($sql,$lang,$this->_appId));
		}// end getColumnConfig()
	}// end class FormConfig()
?>