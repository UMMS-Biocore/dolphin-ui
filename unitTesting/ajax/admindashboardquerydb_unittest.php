<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class admindashboardquerydb_unittest extends PHPUnit_Framework_TestCase
{
    public function testGetMonthlyRuns(){
        ob_start();
		$_GET['p'] = 'getMonthlyRuns';
		include("admindashboardquerydb.php");
		$this->assertEquals(json_decode($data),array());
		ob_end_clean();
    }
    
    public function testGetDailyRuns(){
        ob_start();
		$_GET['p'] = 'getDailyRuns';
		include("admindashboardquerydb.php");
		$this->assertEquals(json_decode($data),array());
		ob_end_clean();
    }
    
    public function testGetMonthlyJobs(){
        ob_start();
		$_GET['p'] = 'getMonthlyJobs';
		include("admindashboardquerydb.php");
		$this->assertEquals(json_decode($data)[0]->countSucess,'178');
		ob_end_clean();
    }
}

?>