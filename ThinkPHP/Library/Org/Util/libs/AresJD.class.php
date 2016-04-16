<?php
/**
 * Get Employee Job Description
 * 
 *  $Id: AresJD.class.php 3363 2012-10-16 06:53:10Z dennis $
 *  $Rev: 3363 $ 
 *  $Date: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2012-10-16 14:53:10 +0800 (周二, 16 十月 2012) $
 \****************************************************************************/
class AresJD {
    /**
     * Company ID
     *
     * @var string
     */
    private $_companyId;
    
    /**
     * Employee Sequence no
     *
     * @var number
     */
    private $_empSeqNo;
    
    /**
     * Employee Job Desc. ID
     *
     * @var string
     */
    private $_jdId;
    
    /**
     * Database handle
     *
     * @var object
     */
    private $_db;
    
    
    /**
     * Constructor of class AresJD
     *
     * @param string $companyid
     * @param string $emp_seqno
     * @author Dennis
     */
    public function __construct($companyid,$emp_seqno)
    {
        global $g_db_sql;
        $this->_db = $g_db_sql;
        //$this->_db->debug = true;
        $this->_companyId = $companyid;
        $this->_empSeqNo = $emp_seqno;
        $this->_jdId = $this->_getJDId();
    }// end class constructor
    
    /**
     * Get Employee JD No.
     * @param  no parameters
     */
    private function _getJDId()
    {
        try {
            $sql = 'select job_id from hr_personnel_base where seg_segment_no = %s and id = %s';
            return $this->_db->getOne(sprintf($sql,'\''.$this->_companyId.'\'','\''.$this->_empSeqNo.'\''));
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            exit;
        }// end try catch()
    }// end getJDId()
    
    /**
     * Get JD Master Data
     * @param no parameter
     * @return array
     * @author Dennis
     */
    public function getJDMaster()
    {
        $sql = "select a.jd_master_no     as jd_id,
                       a.jd_master_desc   as jd_desc,
                       a.segment_no_sz    as dept_id,
                       a.segment_name     as dept_name,
                       a.titlename        as title_name,
                       a.grade            as title_level,
                       a.prepared_by      as prepared_emp_id,
                       c.name_sz          as prepared_emp_name,
                       a.prepared_date    as prepared_date,
                       a.approve_by       as approved_emp_id,
                       d.name_sz          as approved_emp_name,
                       a.approve_date     as approved_date,
                       a.jd_purpose       as jd_purpose,
                       e.edu_type_name    as edu_bg,
                       a.subject_desc     as major,
                       a.shift_require    as is_shift_required,
                       a.experience       as work_experience,
                       a.special_skill    as special_skill,
                       a.profess_training as profess_training,
                       a.others_require   as others_require,
                       a.accountability   as accountability,
                       a.authority_super  as authority_super,
                       a.imme_super       as imme_super,
                       a.job_position     as job_position
                  from hr_jd_master_v     a,
                       hr_personnel_base  b, 
                       hr_personnel_base  c,
                       hr_personnel_base  d,
                       hr_edu_type        e
                 where a.seg_segment_no = b.seg_segment_no
                   and a.seg_segment_no = c.seg_segment_no
                   and a.prepared_by    = c.id_no_sz
                   and a.seg_segment_no = d.seg_segment_no
                   and a.approve_by     = d.id_no_sz
                   and a.seg_segment_no = e.seg_segment_no
                   and a.education      = e.edu_type_no
                   and a.seg_segment_no = '".$this->_companyId."'
                   and b.id             = '".$this->_empSeqNo."'
                   and a.jd_master_id   =  '".$this->_jdId."'
                   ";
        //echo sprintf($sql);exit;
        return $this->_db->getRow(sprintf($sql,
										'\''.$this->_companyId.'\'',
										'\''.$this->_empSeqNo.'\'',
										'\''.$this->_jdId.'\''));
    }// end getJDMaster()
    
    public function getDutyList()
    {
        $sql = 'select assignment_desc, assignment_per
                  from hr_jd_assignment
                 where seg_segment_no = %s
                   and jd_master_id   = %s';
        return $this->_db->getAll(sprintf($sql,'\''.$this->_companyId.'\'','\''.$this->_jdId.'\''));
    }// end getDutyList()

    public function getSubDept()
    {
        $sql = 'select b.title_no_sz  as title_id,
                       b.titlename    as title_desc
                  from hr_jd_title a, hr_title b
                 where a.seg_segment_no = b.seg_segment_no
                   and a.title_id = b.title
                   and a.seg_segment_no = %s
                   and a.jd_master_id   = %s';
        return $this->_db->getAll(sprintf($sql,
								'\''.$this->_companyId.'\'',
								'\''.$this->_jdId.'\''));
    }// end getSubDept()
    
    public function getCompetence()
    {
    	
		$sql = 'select competence_master_no   as competence_id,
                       competence_master_desc as competence_desc,
                       competence_weight      as competence_weight,
                       jd_competence_level    as competence_level,
                       remark_sz              as remark
                  from hr_jd_competence_v
                 where seg_segment_no = %s
                   and jd_master_id   = %s';
		//$this->_db->debug=true;
    	return $this->_db->getAll(sprintf($sql,
										  '\''.$this->_companyId.'\'',
										  '\''.$this->_jdId.'\''));

	}// end getCompetence()

	public function getPMD() {
		$sql = 'select pmd_no, pmd_desc, pmd_weight
				  from hr_jd_pmd
				 where seg_segment_no = %s
				   and jd_master_id = %s';
		return $this->_db->getAll(sprintf($sql,
										  '\''.$this->_companyId.'\'',
										  '\''.$this->_jdId.'\''));
	}// end getPMD()
	/**
	 * Get 当前使用者所在部门的长官的员工代码流水号
	 * (为了挑当前使用者)
	 * @param string $deptid
	 * @return string
	 * @author Dennis
	 */
	public function getDeptLeaderId($deptid)
	{
		$sql = 'select pk_department_message.f_get_dept_leader(%s,%s,%s) as leader_emp_seqno
  				  from dual';
		//$this->_db->debug = true;
		return $this->_db->getOne(sprintf($sql,
										'\''.$this->_companyId.'\'',
										'\''.$deptid.'\'',
										'\'1\''));
	}// end getDeptLeaderId()

}// end class AresJD

?>
