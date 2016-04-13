<?php

/**
 * 单笔记录的Form
 * @category eHR
 * @package  Layout
 */
require_once 'Base.class.php';
class Layout_SingleRowForm extends Base {

	/**
	 * 栏位属性
	 *
	 * @var array
	 */
	private $_columnConfig;
	/**
	 * 待 List 成 Form 形式的资料(query result)
	 *
	 * @var array (2-d array)
	 */
	public $_data;
	/**
	 * 资料分组显示的组名称清单
	 *
	 * @var array
	 */
	private $_dataGroup;

	/**
	 * Constructor of class Layout_SingleRowForm
	 *
	 * @param AdoDB $db
	 * @param string $sql
	 * @param array $column_config
	 * @param array $data_groups default null
	 * @access public
	 * @author Dennis
	 */
	function __construct($db, $sql, array $column_config,$data_group = null) {
		// 设定 fetch 方式，以栏位名称为下标
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->_data = $db->GetRow( $sql );
		//dd($this->_data);
		$this->_columnConfig = $column_config;
		if (is_array($data_group))
		{
			$this->_dataGroup = $data_group;
		}// end if
	} // end class constructor()
	/**
	 * Get Box Header HTML code
	 *
	 * @return string
	 * @author Dennis
	 */
	private function _getBoxHeader($title)
	{
		/*
		return '<div class="sidebox resources">
					<div class="x-box-tl">
						<div class="x-box-tr">
							<div class="x-box-tc"></div>
						</div>
					</div>
					<!-- Header Box End -->
					<div class="x-box-ml">
					<div class="x-box-mr">
					<div class="x-box-mc">
					<!-- Grid Title -->
					<h3 style="margin:1px;padding:1px;">'.$title.'</h3>
					<hr noshade="noshade" size="1" style="color: rgb(128, 128, 128);" />';
					*/
		return '<div style="overflow-y:auto;overflow-x:auto;padding:10px; margin-bottom:10px;" class="ui-widget-content ui-corner-all"><h4 class="ui-widget-header">'.$title.'</h4>';
	}// end _getBoxHeader()

	/**
	 * Get Box Footer Html Code
	 *
	 * @return string
	 */
	private  function _getBoxFooter()
	{
		//return '</div></div></div><div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div></div>';
		return '</div>';
	}// end _getBoxFooter();

	/**
	 * 产生出单笔记录的 html code
	 * @param no
	 * @return string
	 * @access public
	 * @author Dennis
	 */
	public function render() {
		$single_row_form_html = '';
		if (is_array ( $this->_data ) && is_array ( $this->_columnConfig )) {
			$single_row_form_html .= '<div>';
			// 分组标题包装开始
			//$group_title_box_begin = self::_getBoxHeader('x');
			// 分组标题包装结束
			//$group_title_box_end = self::_getBoxFooter();
			// table start html
			$table_begin_html = '<table border="0" cellpadding="0" cellspacing="0" class="bordertable">';
			$table_end_html   = '</table>';

			// 资料行开始 html code
			$row_start_html  = '<tr><td class="column-label" width="150">';
			$row_mid_html    = '</td><td class="td-md">';
			$row_end_html    = '</td></tr>';

			// 栏位标题 class
			//$label_class = 'tbla_color_f';
			if (is_array($this->_dataGroup))
			{
				//dd($this->_data);
				// go go go!
				foreach ($this->_dataGroup as $group)
				{
					$single_row_form_html .= self::_getBoxHeader($group['PROMPT_TEXT']);
					$single_row_form_html .= $table_begin_html;
					//dd($this->_columnConfig);
					foreach ($this->_columnConfig as $key =>$config)
					{
						if(isset($config['group_id']) &&
						   $config['group_id'] == $group['GROUP_ID'])
						{
							$label = isset($config['title']) ? $config['title'] : $key;
							//echo 'key->'.$key;
							$single_row_form_html .= $row_start_html.$label.$row_mid_html;
							//echo $this->_data[$key].'<br/>';
							$data = (!empty($this->_columnConfig[$key]['format']) && !empty($this->_data[$key]))?
									formatData($this->_data[$key],$this->_columnConfig[$key]['format'],$this->_columnConfig[$key]['data_type']):
									$this->_data[$key];
							//echo $data.'<br/>';
							$single_row_form_html .= $data.$row_end_html;
							// 移除掉这个已经有分组的栏位,为后面检查未分组的栏位做准备
							//echo $this->_data[$key].'<br/>';
							unset($this->_data[$key]);
						}// end if
					}// end foreach
					$single_row_form_html .= $table_end_html;
					$single_row_form_html .= self::_getBoxFooter();
					//echo $single_row_form_html;
				}// end foreach

				//pr($this->_data);

				// 显示未分组的资料
				if (count($this->_data)>0)
				{
					//echo 'ungroup data here <hr>';
					$single_row_form_html .= self::_getBoxHeader('');

					$single_row_form_html .= $table_begin_html;
					//************ 相同code wait for refactor
					foreach ( $this->_data as $key => $value ) {
	                    foreach ( $this->_columnConfig as $key1 => $value1 ) {
	                        if (strtolower($key) == strtolower($key1))
	                        {
	                            $label = !empty($value1['title']) ? $value1['title'] : $key;
	                            $single_row_form_html .= $row_start_html.$label.$row_mid_html;
	                            $single_row_form_html .= $value.$row_end_html;
								unset($this->_data[$key]);
	                        }// end if
	                    }// end foreach
	                } // end foreach
	                $single_row_form_html .= $table_end_html;
	                $single_row_form_html .= self::_getBoxFooter();
	                //*************** 相同code wait for refactor
					// 再次检查, 看是否还有没有栏位没有显示(SQL 里有挑这个栏位，但是没有设定栏位属性的栏位)
					if (count($this->_data)>0)
					{
						$single_row_form_html .= $table_begin_html;
						foreach($this->_data as $key=>$value)
						{
							 $single_row_form_html .= $row_start_html.$key.$row_mid_html;
	                         $single_row_form_html .= $value.$row_end_html;
						}// end foreach
						$single_row_form_html .= $table_end_html;
					}// end if
				}// end if
				//echo count($this->_data);
			}else{
				$single_row_form_html .= $table_begin_html;
				//************ 相同code, wait for refactor
				foreach ( $this->_data as $key => $value ) {
					foreach ( $this->_columnConfig as $key1 => $value1 ) {
						if (strtolower($key) == strtolower($key1))
						{
							$label = !empty($value1['title']) ? $value1['title'] : $key;
							$single_row_form_html .= $row_start_html.$label.$row_mid_html;
							$single_row_form_html .= $value.$row_end_html;
						}// end if
					}// end foreach
				} // end foreach
				$single_row_form_html .= $table_end_html;
				//*************** 相同code wait for refactor
			}// end if
			$single_row_form_html .= '</div>';
		} // end if
		return (empty($single_row_form_html) ? 'No data found.': $single_row_form_html);
	} // end render()
	/**
	 * 输出单笔记录表单
	 * @param  no
	 * @return string single row form html code
	 * @see self::render()
	 * @access public
	 * @author Dennis
	 */
	public function dispatch() {
		echo $this->render ();
	} // end dispatch()
} // end class Layout_SingleRowForm

?>
