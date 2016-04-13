<?php
/*************************************************************\
 *  Copyright (C) 2005 Ares China Inc.
 *  Created By Park Hang, Hang Kun
 *  Description:
 *		签核流程图生成程序：
 * 		1.流程图绘图函数
 *  	2.申请单签核流程状态视图 数据整合
 *  	3.对 签核数据不完整或尚未提交申请 的情况给出提示信息.
 *  $Id: AresDrawFlowchart.class.php v1.0 2006-04-28 16:58:08 Dennis Lan exp$
 *************************************************************/
class AresDrawFlowchart {
	// private variables
	protected $companyID; // login user company id
	protected $DBConn; 	  // database connection handle
	protected $flowSeqno; // the apply ID
	protected $applyType; // the apply type A--absence O--overtime
	protected $flowSite = array();
	protected $_multi_lang = array();
	
	/**
	 * constructor of AresDrawFlowchart
	 * @param string $company_id
	 * @param string $flow_seqno
	 * @param string $apply_type
	 */
	function __construct($company_id, $flow_seqno, $apply_type) {
		global $g_db_sql;
		$this->DBConn = &$g_db_sql;
		$this->companyID = $company_id;
		$this->flowSeqno = $flow_seqno;
		$this->applyType = $apply_type;
		$this->_multi_lang = $this->_getFlowchartLang();
	}	
	/**
	 *   print the header       
	 *   @param  $img_path string of the workflow images path
	 */
	private function _getProcessBlockHeader($img_path) {
		$html_string = "<td width='32'>
	                      <img src='$img_path/process.png'/>
	                   </td>
	                   <td width='160'>			
	                       <table class='bordertable' cellpadding='0' cellspacing='0'>";
		return $html_string;
	}// end getProcessBlockHeader()
	
	/**
	 *   print the footer       
	 *   @param  no parameters
	 */
	private function _getProcessBlockFooter() {
		return '</table></td>';
	}
	
	/**
	 * 取得某一个 workstation 的签核情况
	 *
	 * @param string $img_path
	 * @param string $is_approved
	 * @param string $is_key
	 * @param string $user_name
	 * @param string $agency_name_sz
	 * @param boolean $is_current
	 * @return string
	 * @author Dennis 20090602
	 */
	private function _getProcessBlockBody($img_path,
	                                      $is_approved,
	                                      $is_key,
	                                      $user_name,
	                                      $agency_name_sz,
	                                      $approve_date,
	                                      $is_current = null) {
		$pic = 'user_sign';
		//签核者是否签核过
		switch ($is_approved) {
			case 'Y' :
				$pic = $pic . '_approve';
				//$desc = '核准';
				$desc = $this->_multi_lang['APPROVED_LABEL'];
				break;
			case 'N' :
				$pic = $pic . '_refuse';
				//$desc = '驳回';
				$desc = $this->_multi_lang['REJECT_LABEL'];
				break;
			default :
				$pic = $pic . '_unknown';
				//$desc = '未签核';
				$desc = $this->_multi_lang['WAIT_APPROVED_LABEL'];
		}// end switch
		//签核者是否必须签核
		$pic = $pic . ($is_key == 'Y' ? '_key' : '');
		//$must_approve = $is_key == 'Y' ? '是' : '否';
		$must_approve = $is_key == 'Y' ?
		                $this->_multi_lang['YES_LABEL'] : 
		                $this->_multi_lang['NO_LABEL'];
		//$opacity = ($is_current == 'Y' ? '100' : '30');
		//$is_off_img = $is_current == 'Y' ? '' : '_off';
		
		//返回HTML代码
		/* remark by dennis 2011-11-09 这段代码未用到
		$html_string = "<tr>
                           <td colspan='2' style='border:0;text-align:center;'>
                               <img src='$img_path/$pic.png' alt='$desc'>
                           </td>
                       </tr>
                       <tr>
                          <td class='column-label' width='50%'>".$this->_multi_lang['APPROVE_BY_LABEL']."</td>
                          <td  width='50%'>$user_name</td>
                      </tr>
                      <tr>
                        <td class='column-label'>".$this->_multi_lang['AGENT_BY_LABEL']."</td>
                        <td>$agency_name_sz</td>
                      </tr>
                      <tr>
                         <td class='column-label'>".$this->_multi_lang['CURR_STATUS_LABEL']."</td>
                         <td>$desc</td>
                      </tr>
                      <tr>
                         <td class='column-label'>".$this->_multi_lang['IS_REQUIRED_LABEL']."</td>
                         <td>$must_approve</td>
                      </tr>";*/
        $this->flowSite[] = '<br><br><img src="'.$img_path.'/process.png"/><br><br>';
        $this->flowSite[] = '<img src="'.$img_path.'/'.$pic.'.png" alt="'.$desc.'"><br>'
        					.$this->_multi_lang['FLOW_STATUS_LABEL'].': '.$desc.'<br>'
        					.$this->_multi_lang['APPROVE_BY_LABEL'].': '.$user_name.'<br>'
        					.$this->_multi_lang['AGENT_BY_LABEL'].': '.$agency_name_sz.'<br>'
        					.'签核日期: '.$approve_date.'<br>' // add by dennis 2011-11-09
        					.$this->_multi_lang['IS_REQUIRED_LABEL'].': '.$must_approve.'<br>';
		//return $html_string; /// remark by dennis
	}// end _getProcessBlockBody()
	
	
	/**
	 * Get Workflow Workstation Chart
	 * 
	 * @param array $users
	 * @param string $img_path
	 */
	protected function _getProcessBlock($users = array(), $img_path) {
		$html_string = "";		
		//表头
		$html_string = $html_string . $this->_getProcessBlockHeader($img_path);
		
		//签核者群组
		$string = "";
		foreach ( $users as $value ) {
			$string = $string . $this->_getProcessBlockBody($img_path, 
			                                                $value['APPROVE_FLAG'], 
			                                                $value['MUST_APPROVE'], 
			                                                $value['NAME_SZ'], 
			                                                $value['AGENCY_NAME_SZ'],
			                                                $value['APPROVE_DATE'], // add by dennis 2011-11-09
			                                                $value['CAN_APPROVE'] );
		}
		$html_string = $html_string . $string;
		
		//表尾
		$html_string = $html_string . $this->_getProcessBlockFooter();
		return $html_string;
	}// end _getProcessBlock()
	
	/**
	 * Draw flowchart
	 *
	 * @return string
	 */
	function DrawFlowchart($menu_code = '') {
        //取得流程图基本信息(区分请假/加班)
        switch ($this->applyType) {
            case "overtime" :
                $apply_view = 'wf.hrs_overtime_flow_status_v';
                //$apply_table = 'hrs_overtime_flow_sz';
                $flow_id = 'overtime_flow_sz_id';
                $approve_id = 'overtime_approve_sz_id';
                break;
            case "absence" :
                $apply_view = 'wf.hrs_absence_flow_status_v';
                //$apply_table = 'hrs_absence_flow_sz';
                $flow_id = 'absence_flow_sz_id';
                $approve_id = 'absence_approve_sz_id';
                break;
            case "trans" :
                $apply_view = 'wf.hrs_trans_flow_status_v';
                $flow_id = 'trans_flow_sz_id';
                $approve_id = 'trans_approve_sz_id';
                break;
            case "nocard" :
                $apply_view = 'wf.hrs_nocard_flow_status_v';
                $flow_id = 'nocard_flow_sz_id';
                $approve_id = 'nocard_approve_sz_id';
                break;
            case "resign" :
                $apply_view = 'wf.hrs_resign_flow_status_v';
                $flow_id = 'resign_flow_sz_id';
                $approve_id = 'resign_approve_sz_id';
                break;
            case "cancel_absence" :
                $apply_view = 'wf.hrs_c_absence_flow_status_v';
                //$apply_table = 'hrs_cancel_absence_flow_sz';
                $flow_id = 'cancel_absence_flow_sz_id';
                $approve_id = 'cancel_absence_approve_sz_id';
                break;
            default : // user defined workflow added by dennis 20091117
            	$apply_view = 'udwf_'.$menu_code.'_flow_status_v';
                $flow_id    = 'flow_sz_id';
                $approve_id = 'approve_sz_id';
                break;
        }// end switch
        //print $apply_view."<hr>";
       	//$this->DBConn->debug = 1;
        // add approve_date by dennis 2011-11-09
        // change agent display text by dennis 2013-02-20
        $sql = <<<eof
	        select $approve_id,
			       $flow_id,
			       seg_segment_no,
			       psn_id,
			       id_no_sz,
			       name_sz,
			       lev_cnt,
			       lev,
			       decode(approve_flag, null, 'X', approve_flag) as approve_flag,
			       must_approve,
			       can_approve,
			       cnt_per_lev,
			       approve_date,
			       decode(status, '03', 'Y', '04', 'Y', '05', 'Y', '06', 'Y', 'N') as is_end,
			       decode(status,
			              '01',
			              '已提交',
			              '02',
			              '流程中',
			              '03',
			              '核准',
			              '04',
			              '驳回',
			              '05',
			              '作废',
			              '06',
			              '异常') as status,
			       status as status_code,
			       decode(agency_names,
			              null,
			              decode(agency_name_sz,
			                     null,
			                     '无',
			                     agency_name_sz || '[代签]'),
			              agency_names) as agency_name_sz
			  from $apply_view
			 where $flow_id = '$this->flowSeqno'
			   and seg_segment_no = '$this->companyID'
			 order by lev
eof;
        //取得结果
        $this->DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
        $workflow_info = $this->DBConn->GetArray($sql);       
        //pr($workflow_info);
        if ($workflow_info) {
            $lev_cnt = $workflow_info [0] ['LEV_CNT'];          
            $lev_first = $workflow_info [0] ['LEV'];            
            //$lev_last = $lev_first + $lev_cnt - 1;            
            $img_path = $GLOBALS['config']['img_dir'].'/workflow';
            $level = $lev_first;
            $users = array ();
            $cn = 0;
            $users[$cn] = array ();
            $html_string = '';
            $html = '';            
            //流程图开始图标
            $is_end = $workflow_info [0] ['IS_END'];            
            //$opacity = ($is_end == 'N' ? 100 : 30); 
            //$is_off_img  = $is_end == 'N' ? '' : '_off';
            $row_amount = 3;
            $html ="<table cellpading='0' cellspacing='0'>
	                    <tr>
	                        <td width='160'>
	                            <table class='bordertable'>
		                            <tr>
		                                <td colspan='2' style='border:0;text-align:center;'>
				                            <img src='$img_path/start.png' alt='Workflow Start'/>
				                        </td>
				                    </tr>
				                    <tr>
				                        <td class='column-label' width='50%'>".$this->_multi_lang['FLOW_STATUS_LABEL']."</td>
				                        <td width='50%'></td>
				                    </tr>
			                  </table>
			                </td>";
			$this->flowSite[] = '<img src="'.$img_path.'/start.png" alt="Start"/>'.
						        '<br>'.$this->_multi_lang['FLOW_STATUS_LABEL'].':'.$this->_multi_lang['START_LABEL'].'<br><br><br><br>';
            //print '<br> level='.$level;
            //流程图HTML代码 详细签核层级
            for($i = 0; $i < count ( $workflow_info); $i ++) {
                if ($workflow_info [$i] ['LEV'] != $level) {
                    $level = $workflow_info [$i]['LEV'];
                    if (fmod($level - 1,$row_amount ) == 0) {
                        $html_string = $html_string . "</tr><tr>";
                    }
                    if (! empty ( $users )) {
                        $html_string = $html_string . $this->_getProcessBlock ($users, $img_path );
                    }
                    $users = array ();
                    $cn = 0;
                    $users [$cn] = array ();
                }// end if
                if ($i < count ( $workflow_info )) {
                    $users [$cn] = array ();
                    $users [$cn ++] = $workflow_info [$i];
                }// end if
            }
            if (! empty ( $users )) {
                $html_string = $html_string . $this->_getProcessBlock( $users, $img_path );
            }// end if
            //$is_end = $workflow_info[0]['IS_END'];
            //$opacity = ($is_end == 'Y' ? 100 : 30);
            
            $pic = 'end';
            switch ($workflow_info [0] ['STATUS_CODE']) {
                case '03' :
                    $pic = $pic . '_approve';
                    break;
                case '04' :
                    $pic = $pic . '_refuse';
                    break;
                case '05' :
                    $pic = $pic . '_pause';
                    break;
                case '06' :
                    $pic = $pic . '_unknown';
                    break;
                default :
                    $pic = $pic . '';
            }// end switch            
            //$flow_result = $is_end == 'Y' ? '流程结束' : '流程进行中';
            $flow_result = $is_end == 'Y' ? $this->_multi_lang['FLOW_END_LABEL'] : $this->_multi_lang['FLOW_INPROCESS_LABEL'];
            
            $flow_status= $workflow_info [0] ['STATUS'];
            //流程图HTML代码 结束
            $html = $html . $html_string . "
                             <td width='32' valign='middle'><img src='$img_path/process.png'/></td>
                             <td width='160'>
	                             <table class='bordertable'>
	                                <tr>
	                                    <td colspan='2' style='border:0;text-align:center;'>
	                                        <img src='$img_path/$pic.png' alt='$flow_result'>
	                                    </td>
	                                </tr>
	                                <tr><td class='column-label'>流程状态</td><td>$flow_result</td></tr>
	                                <tr><td class='column-label'>签核结果</td><td>$flow_status</td></tr>
	                             </table>
	                         </td>
	                         <td>&nbsp;</td>
	                      </tr>
	                    </table>";
	        $this->flowSite[] = '<br><br><img src="'.$img_path.'/process.png"/><br><br>';
	        $this->flowSite[] = '<img src="'.$img_path.'/'.$pic.'.png" alt="'.$flow_result.'"/>'
						    	.'<br>'.$this->_multi_lang['FLOW_STATUS_LABEL'].': '.$flow_result
						    	.'<br>'.$this->_multi_lang['FLOW_RESULT_LABEL'].': '.$flow_status
						    	.'<br><br><br>';
        }

        //pr($this->flowSite);
        $html = '<table class="noborder" border="0" width="100%" id="table1" cellspacing="1"><tr  class="noborder">';
        foreach ($this->flowSite as $value){
        	$html .='<td valign="top" nowrap>'.$value.'</td>';
        }
        $html .= '</tr></table>';
        // 签核数据不完整或尚未提交申请
        if (empty($lev_cnt )) {
            //$html = "签核数据不完整或尚未提交申请";
            $html = $this->_multi_lang['FLOW_INVALID_ERROR_MSG'];
        }// end if
        return $html;
    }// end DrawFlowchart()
    
    /**
     * Get flowchart 中用到的多语
     *
     * @return unknown
     */
    private function _getFlowchartLang()
    {
    	$sql = 'select name,value 
    	          from app_muti_lang 
    	         where program_no = :program_no 
    	           and lang_code = :lang_code';
    	$this->DBConn->SetFetchMode(ADODB_FETCH_NUM);
    	return recombineArray($this->DBConn->CacheGetArray(36000,$sql,
    			array('program_no'=>'ESNA013',
    				  'lang_code'=>$GLOBALS['config']['default_lang'])));
    }// end _getFlowchartLang()
}
