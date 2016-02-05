<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class dashboardquerydb_unittest extends PHPUnit_Framework_TestCase
{
    public function testGetMonthlyRuns(){
        ob_start();
		$_GET['p'] = 'getMonthlyRuns';
		include("dashboardquerydb.php");
		$this->assertEquals(json_decode($data),array());
		ob_end_clean();
    }
    
    public function testGetDailyRuns(){
        ob_start();
		$_GET['p'] = 'getDailyRuns';
		include("dashboardquerydb.php");
		$this->assertEquals(json_decode($data),array());
		ob_end_clean();
    }
    
    public function testGetMonthlyJobs(){
        ob_start();
		$_GET['p'] = 'getMonthlyJobs';
		include("dashboardquerydb.php");
		$this->assertEquals(json_decode($data),array());
		ob_end_clean();
    }
}

?>