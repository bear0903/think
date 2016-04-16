<?php
/**
 * 部门资料汇总查询
 *
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresDepartment.php $
 *  $Id: AresDepartment.php 3217 2011-09-26 09:47:01Z dennis $
 *  $Rev: 3217 $
 *  $Date: 2011-09-26 17:47:01 +0800 (周一, 26 九月 2011) $
 *  $Author: dennis $
 *  $LastChangedDate: 2011-09-26 17:47:01 +0800 (周一, 26 九月 2011) $
 \****************************************************************************/
class AresDepartment {
	private $_companyId;
	private $_deptSeqNo;
    private $_dbConn;
	public function __construct($companyid,$deptseqno)
	{
        global $g_db_sql;
		$this->_companyId = $companyid;
		$this->_deptSeqNo = $deptseqno;
        $this->_dbConn = $g_db_sql;
	}// end construct

	/**
	 * Get Department Basic Information
	 * Update by dennis 2011-09-26
	 *@return array
     *
	 */
	public function getDepartmentInfo()
	{
		$sql = <<<eof
            select a.segment_no_sz as dept_id,
                   a.segment_name  as dept_name,
                   c.id            as manager_emp_seqno,
                   c.id_no_sz      as manager_emp_id,
                   c.name_sz       as manager_emp_name,
                   b.segment_no_sz as p_dept_id,
                   b.segment_name  as p_dept_name,
                   a.begindate     as found_date,
                   a.enddate       as expired_date
              from gl_segment a, gl_segment b, hr_personnel_base c
             where a.seg_segment_no = b.seg_segment_no(+)
               and a.parent_segment_no = b.segment_no(+)
               and a.seg_segment_no = c.seg_segment_no(+)
               and a.leader_emp_id = c.id(+)
               and a.seg_segment_no = :company_id
               and a.segment_no = :dept_seqno
eof;
		return $this->_dbConn->CacheGetRow(3600,$sql,array('company_id'=>$this->_companyId,
                                                 'dept_seqno'=>$this->_deptSeqNo));
	}// end getDepartmentInfo()

    /**
     * Get Headcount by Dept.
     * @return array
     * @author Dennis 2011-09-26
     */
	public function getHeadcount()
	{
		$sql = <<<eof
           select sex, count(1) as headcount
              from hr_personnel_base
             where seg_segment_no = :company_id
               and seg_segment_no_department = :dept_seqno
               and pk_history_data.f_get_status(seg_segment_no, id, sysdate) = 'JS1'
             group by sex
eof;
		$r = $this->_dbConn->CacheGetArray(3600,$sql,array('company_id'=>$this->_companyId,
											'dept_seqno'=>$this->_deptSeqNo));
		$x = null;
		for ($i=0; $i<count($r); $i++)
		{
			$x[$r[$i]['SEX'].'_HEADCOUNT'] = $r[$i]['HEADCOUNT'];
		}// end for loop
		return $x;
	}// end getHeadcount()

	public function getTitleCount()
	{
		$sql = <<<eof
        select a.title_no_sz, nvl(a.titlename, 'Unknow'), count(1) as headcount
          from hr_title a, hr_personnel_base b
         where a.seg_segment_no(+) = b.seg_segment_no
           and a.title(+) = b.title
           and pk_history_data.f_get_status(b.seg_segment_no, b.id, sysdate) =
               'JS1'
           and b.seg_segment_no = :company_id
           and b.seg_segment_no_department = :dept_seqno
         group by a.title_no_sz, a.titlename
eof;
		return $this->_dbConn->CacheGetArray(3600,$sql,array('company_id'=>$this->_companyId,
											  'dept_seqno'=>$this->_deptSeqNo));
	}// end getHeadcount()
}// end class AresDepartment

?>