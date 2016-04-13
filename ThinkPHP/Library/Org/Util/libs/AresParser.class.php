<?php
 /**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) Lan Jiangtao 
 *	 
 *	Description:
 *       Parse tmeplate page, depand on Smarty Template
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresParser.class.php $
 *  $Id: AresParser.class.php 3706 2014-03-21 03:03:02Z dennis $
 *  $Rev: 3706 $ 
 *  $Date: 2014-03-21 11:03:02 +0800 (鍛ㄤ簲, 21 涓夋湀 2014) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2014-03-21 11:03:02 +0800 (鍛ㄤ簲, 21 涓夋湀 2014) $
 \****************************************************************************/
    class AresParser{
        public $Smarty;
        public $DBConn;
        
        /**
        * Constructor of class AresParser
        * @param no parameter
        */
        function __construct()
        {
            global $g_tpl,$g_db_sql;
            $this->Smarty = &$g_tpl;
            $this->DBConn = &$g_db_sql;
        }// end __construct()
        
        /**
        * Private function, recombination array for match smarty template
        *  after recomabination like "$key"=>"value" the "$key" is a smarty template variable
        * @access private
        * @param $data array, the recordset returned by ADODB GetArray() function
        * @return mix, if success return array esle return false
        */
        private function _recombineArray($data)
        {
            $result = false;
            if (is_array($data))
            {
                $cnt = count($data);
                for ($i=0; $i<$cnt; $i++)
                {
                    //$result[strtoupper($data[$i][0])] = $data[$i][1];
                    $result[$data[$i][0]] = $data[$i][1]; // remove the strtoupper() by dennis 2014/01/03 鎶婂�艰浆鎴愬ぇ鍐欎笉鍚堢悊
                }// end loop
            }// end if
            return $result;
        }// end _recombineArray()

        /**
        *   Parse multi language
        * @param $app_id string , the application id
        * @param $lang_code string , language code,ZHS,ZHT,US upper case
        * @return no return value
        */
        function ParseMultiLang($app_id,$lang_code)
        {
	    $this->DBConn->SetFetchMode(ADODB_FETCH_DEFAULT);
            $sql = <<<eof
                select name  as label_name,
                       value as label_text
                  from app_muti_lang
                 where program_no = :program_no
                   and lang_code =  :lang_code
				   and type_code = 'IT'
eof;
            //print $sql."<br/>";
            // last modify by Dennis 2011-08-17 add cache 1 month
            $rs = $this->DBConn->CacheGetArray(360000,$sql,array('program_no'=>$app_id,
            									            'lang_code'=>$lang_code));
            $result = $this->_recombineArray($rs);
            $this->Smarty->assign($result);
        }// end ParseMultiLang()
        
        /**
        *   Parse select option componets
        * @param $select_name string, the variable name in template
        * @param $data_source mixed, array or select sql string       
        * @param $s_option_name string, selected option variable namein template
        * @param $s_option_value string, the selected option value, default ""
        * @return no return value
        * @sample
        *   
            $smarty->assign('cust_options', array(
                                                    1001 => 'Joe Schmoe',
                                                    1002 => 'Jack Smith',
                                                    1003 => 'Jane Johnson',
                                                    1004 => 'Charlie Brown'));
            $smarty->assign('customer_id', 1001);
            @last update : 2006-03-17 10:57:17  by dennis 
            @change log
               1. add SetFetchMode(ADODB_FETCH_DEFAULT)
        */
        function ParseSelect($select_name,$data_source,$s_option_name,$s_option_value = '',$cache_data = 'N')
        {
            $result = $data_source; // default pass an array
            if (!empty($data_source) && is_string($data_source)){
                // change fetch mode to default, for get integer base index result
                
                $this->DBConn->SetFetchMode(ADODB_FETCH_DEFAULT);
                
                if ($cache_data == 'N'){
                    $result = $this->DBConn->GetArray($data_source);
                }else{
                    $result = $this->DBConn->CacheGetArray(3600,$data_source);
                }
            }
			//pr($this->_recombineArray($result));
            $this->Smarty->assign($select_name,$this->_recombineArray($result));
            $this->Smarty->assign($s_option_name,$s_option_value);
        }
        
        /**
        *   parse multi-rows table data
        * @param $table_name string ,the loop data, variable in Smarty template
        * @param $data_source mix, if a sql string will fetch data vie ADODB Connection handle
        * @return no return value, assign data to template
        * @author: dennis 
        * @last udpate 2006-01-12 11:23:26 by dennis
        * @sample template
        *   <table border="1" width="500">
                <tr>
                    <td>Company ID</td>
                    <td>Dept. ID/td>
                    <td>Dept. Name</td>
                </tr>
                <!--{section name=dept loop=$dept_list}-->
                
                <tr bgcolor="<!--{cycle values="#ccccff,#e1e1e1" advance=true}-->">
                    <td><!--{$dept_list[dept].COMPANY_ID}--></td>
                    <td><!--{$dept_list[dept].DEPT_ID}--></td>
                    <td><!--{$dept_list[dept].DEPT_NAME}--></td>
                </tr>
                <!--{/section}-->
            </table>
            <table border="1" width="500">
                <!--{foreach from=$dept_list item="dept"}-->
                <!--{pager rowcount=$LISTDATA.rowcount limit=$LISTDATA.limit txt_first="..." class_num="fl" class_numon="fl" class_text="fl"}-->
                    <tr bgcolor="<!--{cycle values="#ccccff,#e1e1e1" advance=true}-->">
                        <td><!--{$dept.COMPANY_ID}--></td>
                        <td><!--{$dept.DEPT_ID}--></td>
                        <td><!--{$dept.DEPT_NAME}--></td>
                    </tr>
                <!--{foreachelse}-->
                    <tr>
                        <td colspan="3" align="center">No records</td>
                    </tr>
                <!--{/foreach}-->
                <!--{/pager}-->
            </table>
        */
        function ParseTable($table_name,$data_source)
        {
            $recordset = $data_source; // suppose default pass an array
            if (is_string($data_source))
            {
                // change fetch mode to default, for get integer base index result
                $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
                $recordset = $this->DBConn->GetArray($data_source);
                //pr($recordset);
                //$recordset = $this->_recombineArray($rs);
            }
            //$pargerlink = $this->GetPagerLinks();
            //$this->Smarty->assign("pagerLink","");
            $this->Smarty->assign($table_name,$recordset);
        }

        function ParseOneRow($data_source)
        {
            $row = $data_source; // suppose default pass an array
            $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
            if (is_string($data_source))
            {
                $row = $this->DBConn->GetRow($data_source);
            }
            $this->Smarty->assign($row);
        }
    }