<?php
 /**************************************************************************\
 *   Best view set tab 4
 *   Created by Dennis.lan (C) Lan Jiangtao 
 *	 
 *	Description:
 *     ehr Training Module
 *
 *     !!! notice get_query_where() function reference to "functions.php"
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresTraining.class.php $
 *  $Id: AresTraining.class.php 698 2008-11-19 05:51:54Z dennis $
 *  $Rev: 698 $ 
 *  $Date: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 *  $Author: dennis $   
 *  $LastChangedDate: 2008-11-19 13:51:54 +0800 (周三, 19 十一月 2008) $
 \****************************************************************************/
    class AresTraining
    {
        var $companyID;
        var $empSeqNO;
        var $DBConn;
        /**
        *   Counstructor of class AresTraining
        *   init property companyid and emplyee seq no (psn_id)
        *   @param $companyid string, the employee's company id
        *   @param $emp_seqno string, the login user's sequence no in app_users, default ""
        *   @return void.
        */
        function AresTraining($companyid,$emp_seqno = "")
        {
            global $g_db_sql;
            $this->companyID = $companyid;
            $this->empSeqNO  = $emp_seqno;
            $this->DBConn = $g_db_sql;
        }// end class contructor AresTraining()

		/**
		*	Get Course name list
		*	@param no parameters
		*	@return string, the select sql string
		*/
		function GetCourseList()
		{
            $companyid = $this->companyID;
            $sql_string = <<<_sql_
                select course_seqno, 
                       course_id || ' - ' || course_name as course_name
                  from ehr_course_plan_v
                 where company_id = '$companyid'
                 group by course_seqno,course_id,course_name
_sql_;
            return $this->DBConn->GetArray($sql_string);
		}// end function GetCourseList()

		/**
		*	Get plan year list 
		*	@param no parameters
		*	@return string, the select sql string
		*/
		function GetYearList()
		{
            $companyid = $this->companyID;
            $sql_string = <<<_sql_
                select my_date as year1, my_date as year2
                  from ehr_course_plan_v
                 where company_id = '$companyid'
                 group by my_date
_sql_;
            return $this->DBConn->GetArray($sql_string);
		}// end function GetYearList()

        /**
        *   Get company un-expired news list
        *   @param $whercond string, query where condition
        *   @return array, a 2-dimensional array of records
        *   @author: Dennis
        *   @last udpate:2006-04-19 15:18:02 by dennis
        */
        function GetYearCoursePlan($whercond)
        {
            $companyid = $this->companyID;
            $sql_string = <<<_YearCoursePlan_
                select plan_seqno,
					   my_date,
					   course_seqno,
					   course_id,
					   course_name,
					   students_cnt,
					   suggest_students,
					   training_type,
					   training_org_id,
					   training_org_name,
					   class_cnt,
					   course_hours,
					   suggest_hours,
					   per_class_cost,
					   total_cost,
					   remark
				  from ehr_course_plan_v
                 where company_id = '$companyid'
                    $whercond
_YearCoursePlan_;
		    //print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }// end function GetYearCoursePlan()

		/**
        *   Get plan course detail information
        *   @param $plan_id number, master table key planid
        *   @return array, a 2-dimensional array of records
		*	@author: dennis 2006-02-28 19:58:15 
		*	@last update :2006-02-28 19:58:22  by dennis 
        */
		function GetCoursePlanDetail($plan_id)
		{
			$companyid = $this->companyID;
            $sql_string = <<<_YearCoursePlanDetail_
			select course_begin_date,
				   num_of_class,
				   remark
			  from ehr_course_plan_detail_v
			 where company_id = '$companyid'
			   and plan_id = '$plan_id'
_YearCoursePlanDetail_;
			return $this->DBConn->GetArray($sql_string);
		}// end function GetCoursePlanDetail()

        /**
        *   Get login user private noties list
        *   @param $where string,where condition string
        *   @return array, a 2-dimensional array of records
        */
        function GetMustStudyCourse($where)
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $sql_string = <<<_MustStudyCourse_
                select course_no,
                       course_id,
                       course_name,
					   begin_date,
                       end_date,
                       score,
                       is_pass,
                       is_studied
                  from ehr_must_stduy_result_v
                 where company_id = '$companyid'
                   and emp_seq_no = '$emp_seqno'
                   $where
_MustStudyCourse_;
			//print $sql_string;
            return $this->DBConn->GetArray($sql_string);
        }// end function GetMustStudyCourse();

        /**
        *   Get course training score 
        *   @param $wherecond string, query where condition
        *   @return array, an array of records
		*	@author: dennis 2006-03-01 13:41:37 
		*	@laste update: 2006-03-01 13:41:48 by dennis
        */
        function GetScore($wherecond)
        {
            $companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;

            $sql_string = <<<_ScoreDetail_
				select a.course_id,
					   a.course_name,
					   a.course_hours,
					   a.class_name,
					   a.class_desc,
					   a.actual_begin_date,
					   a.actual_end_date,
					   a.student_grp_desc,
					   a.training_org_name,
					   a.training_type,
					   a.eval_method,
					   a.passing_score,
					   b.is_absent,
					   b.is_leave,
					   b.is_passed,
					   b.c_level,
					   b.score,
					   b.remark
				  from ehr_training_score_v a, ehr_training_score_detail_v b
				 where a.company_id = '$companyid'
				   and b.emp_seq_no = '$emp_seqno'
				   and a.company_id = b.company_id
				   and a.class_no = b.class_no
					   $wherecond
_ScoreDetail_;
            return $this->DBConn->GetArray($sql_string);
        }// end function GetScore()

		/**
		*	Get Course detail information
		*	@param $course_id string, course id
		*	@return array 2-d array
		*	@author : dennis 2006-03-02 11:27:42 
		*	@last update: 2006-04-19 16:05:41  by dennis
		*/
		function GetCourseDetail($course_seqno)
		{
			$sql_string = <<<_CourseDetail_
				select course_id,
					   course_name,
					   class_id,
					   class_name,
					   actual_begin_date,
					   actual_end_date,
					   training_org_name,
					   training_type,
					   student_grp_desc,
					   eval_method,
					   class_desc
				  from ehr_training_score_v
				 where course_no = '$course_seqno'
_CourseDetail_;
			return $this->DBConn->GetRow($sql_string);
		}// end function GetCourseDetail()

		/**
        *   Get Employee Course Opinion
        *   @param $whereArray where condition array
        *   @return array, an array of records
		*	@author: dennis 2006-03-01 13:41:37 
		*	@laste update: 2006-03-01 13:41:48 by dennis
        */
		function GetCourseOpinion($whereArray)
		{
			$_type = 2;// course
			$companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;
            $_where_cond = is_array($whereArray) ? get_query_where($whereArray) : "";

			$sql_string = <<<_CourseOpinionList_
			select survey_detail_no   as opinion_id,
				   survey_detail_desc as opinion,
				   score              as score,
				   remark             as remark
			  from hr_class_survey_all_v
			 where seg_segment_no = '$companyid '
			   and psn_id = '$emp_seqno'
			   and survey_type = $_type
				   $_where_cond
_CourseOpinionList_;
			   return $this->DBConn->GetArray($sql_string);
		}//

		/**
        *   Get Employee Education List
        *   @param $wherecond where condition string
        *   @return array, an array of records
		*	@author: jack 2006-12-11 13:41:37 
        */
		function GetEducationList($wherecond,$cond_1,$cond_2)
		{
			$companyid = $this->companyID;
            $emp_seqno = $this->empSeqNO;

			$sql_string = <<<_GetEducateList_
				select emp_seq_no,
					   emp_name,
					   subject_class_id,
					   subject_class_no,
					   subject_class_name,
					   subject_no,
					   subject_name,
					   no_subject_desc,
					   subject_hour,
					   subject_date_begin,
					   subject_date_end,
					   class_score
				  from (select A.*, rownum rn
						  from (select id as emp_seq_no,
									   name_sz as emp_name,
									   subject_class_id,
									   subject_class_no,
									   subject_class_name,
									   subject_no,
									   subject_name,
									   no_subject_desc,
									   subject_hour,
									   subject_date_begin,
									   subject_date_end,
									   class_score
								  from hr_psn_edu_v
								 where seg_segment_no = '$companyid'
								   and id = '$emp_seqno'
								 $wherecond
								 order by subject_date_begin desc) A
						 where rownum <= $cond_2)
				 where rn >= $cond_1
_GetEducateList_;
			   return $this->DBConn->GetArray($sql_string);
		}//
    }// end class AresTraining
?>