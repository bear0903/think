<?php
/**
 *	Integration Authentication with Microsoft Windows Active Directory 
 *  Create By: Dennis
 *  Create Date: 2009-04-02 13:10
 * 
 *  $HeadURL: https://192.168.0.101/svn/ehr/trunk/eHR/libs/AresIAuthAdapter.php $
 *  $Id: AresIAuthAdapter.php 1831 2009-06-22 02:31:39Z dennis $
 *  $LastChangedDate: 2009-06-22 10:31:39 +0800 (周一, 22 六月 2009) $ 
 *  $LastChangedBy: dennis $
 *  $LastChangedRevision: 1831 $  
 \****************************************************************************/
interface AresIAuthAdapter{
	function authenticate();
}// end interface AresIAuthAdpater
