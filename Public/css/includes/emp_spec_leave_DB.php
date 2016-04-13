<?php
    /**
     * 员工特别假资料查询 （特别假是 HCP 中一种特殊的假别，需要 HR 开假才可以申请)
     */
    if (! defined ( 'DOCROOT' )) {
        die ( 'Attack Error.' );
    }// end if
    
    function getSpecLeaveList($companyid,$psnid)
    {
        global $g_db_sql;
        $sql = <<<eof
        select psn_id_no    as emp_id,
               psn_name     as emp_name,
               absence_code as abs_type_id,
               absence_name as abs_name,
               funeral_days as rule_days,
               already_days as rest_days,
               create_date  as create_date
          from hr_funeral_v
         where seg_segment_no = :companyid
           and psn_id = :psnid
           and is_active = 'Y'
eof;
        //$g_db_sql->debug = 1;
        return $g_db_sql->GetArray($sql,array('companyid'=>$companyid,'psnid'=>$psnid));
    }
    $spec_leave_list = getSpecLeaveList($_SESSION['user']['company_id'], $_SESSION['user']['emp_seq_no']);
    $g_parser->ParseTable('spec_leave_list', $spec_leave_list);