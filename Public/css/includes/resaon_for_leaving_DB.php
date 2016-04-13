<?php
/**********************************************************************\
  * (C)  2008 ARES CHINA All Rights Reserved.  http://www.areschina.com
  *
  *  Desc
  *   离职原因调查
  *  Create By: Dennis  Create Date: 2008-12-19 ����01:28:30
  *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/ess/includes/resaon_for_leaving_DB.php $
  *  $Id: resaon_for_leaving_DB.php 1608 2009-05-22 03:37:37Z dennis $
  *  $LastChangedDate: 2009-05-22 11:37:37 +0800 (周五, 22 五月 2009) $
  *  $LastChangedBy: dennis $
  *  $LastChangedRevision: 1608 $  
  * 
 \ **********************************************************************/ 
if (! defined ( 'DOCROOT' )) {
	die ( 'Attack Error.' );
}// end if 
require_once 'AresLeaving.class.php';
$ALeaving = new AresLeaving($_SESSION['user']['company_id'],
				 			$_SESSION['user']['emp_seq_no']);
// add by dennis 2009-03-02
if($ALeaving->isSubmitted())
{
	showMsg('您的離職原因問卷已經填寫過.','information');	
}// end if

if(count($_POST)>0)
{
	//pr($_POST);
	/*
	 * $ques_seqno,
	   $leaving_date,
	   $after_con_addr,
	   $after_con_tel,
	   $leaving_type,
	   $other_type_desc,
	   $emp_suggestion,
	   $form_status,
	   array $data
	 */
	if (isset($_POST['tmpsubmit']) || isset($_POST['relsubmit']))
	{
		if(date("Y-m-d",strtotime($_POST['leaving_date']))<date("Y-m-d"))
		{
			showMsg('預估離職日期不能小於當前日期','warning');
		}
		$form_status = isset($_POST['tmpsubmit']) ? '0' : '1';
		$other_type_desc = (isset($_POST['leaving_type']) && $_POST['leaving_type'] == '0') ? 
						   $_POST['other_type_desc'] : 
						   '';
		$detail_data = '';
		$c = count($_POST['reason_item']);
		$item_keys = array_keys($_POST['reason_item']);
		$item_values = array_values($_POST['reason_item']);
		for ($i=0; $i<$c; $i++)
		{
			$detail_data[$i]['ques_seqno'] = $_POST['ques_id'];
			$detail_data[$i]['cate_seqno'] = $_POST['cate_id'][$item_keys[$i]];
			$detail_data[$i]['item_seqno'] = $item_keys[$i];
			$detail_data[$i]['remark'] = $_POST['reason_comments'][$item_keys[$i]];
		}// end for loop
		//pr($detail_data);
		if(isset($_POST['master_seqno']) && !empty($_POST['master_seqno']))
		{
			$ALeaving->update($_POST['master_seqno'],
							  $_POST['leaving_date'],
							  $_POST['after_leaving_addr'],
							  $_POST['after_leaving_tel'],
							  $_POST['leaving_type'],
							  $other_type_desc,
							  $_POST['emp_suggestion'],
							  $form_status,
							  $detail_data);
		}else{
			$ALeaving->insert($_POST['ques_id'],
							  $_POST['leaving_date'],
							  $_POST['after_leaving_addr'],
							  $_POST['after_leaving_tel'],
							  $_POST['leaving_type'],
							  $other_type_desc,
							  $_POST['emp_suggestion'],
							  $form_status,
							  $detail_data);
		}//end if
	}//  end if 
}// end if
$master_row = $ALeaving->getLeavingMaster();
$g_parser->ParseOneRow($master_row);
$r = $ALeaving->getReasonCatalog();
$c = count($r);
if ($c>0)
{
	$r1 = $ALeaving->getItemByCatalog($r[0]['QUES_ID']);
	$r2 = $ALeaving->getLeavingDetail($master_row['SEQNO']);
	//pr($r2);
	$c1 = count($r1);
	$c2 = count($r2);
	//pr($r1);
	if ($c1>0)
	{
		for ($i=0; $i<$c; $i++)
		{
			$n = 0;
			for ($j=0; $j<$c1; $j++)
			{
				if ($r[$i]['CATE_ID'] == $r1[$j]['CATE_ID'])
				{
					$r[$i]['REASON_ITEMS'][$n]['ITEM_SEQNO'] = $r1[$j]['ITEMS_ID'];
					$r[$i]['REASON_ITEMS'][$n]['ITEM_DESC']  = $r1[$j]['ITEMS_DESC'];
					// 解析答案
					if ($c2 > 0)
					{
						for($k=0; $k<$c2; $k++)
						{
							if ($r2[$k]['CATE_SEQNO'] == $r1[$j]['CATE_ID'] &&
								$r2[$k]['ITEM_SEQNO'] == $r1[$j]['ITEMS_ID']) {
									$r[$i]['REASON_ITEMS'][$n]['CHECKED'] = 'checked';
									$r[$i]['REASON_ITEMS'][$n]['REMARK'] = $r2[$k]['REMARKS'];
								}// end if
						}// end for loop
					}// end if
					$n++;
				}// end if
			}// end for loop
		}// end for loop
	}// end if
	//pr($r);
	$g_parser->ParseTable('leaving_item_list',$r);
}// end if

