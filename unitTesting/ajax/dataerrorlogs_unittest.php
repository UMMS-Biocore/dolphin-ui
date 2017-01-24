<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class dataerrorlogs_unittest extends PHPUnit_Framework_TestCase
{
    public function testGetStdOut(){
        ob_start();
		$_GET['p'] = 'getStdOut';
        $_GET['run_id'] = '1';
		include("dataerrorlogs.php");
		$this->assertEquals(json_decode($data)[0],'Error (line:36)running runSQL<br>');
		ob_end_clean();
    }
    
    public function testCheckQueued(){
        ob_start();
		$_GET['p'] = 'checkQueued';
        $_GET['run_id'] = '1';
		include("dataerrorlogs.php");
		$this->assertEquals(json_decode($data)[0],'0');
		$this->assertEquals(json_decode($data)[1],'0');
		$this->assertEquals(json_decode($data)[2],'J98Oe0bSZ18fBx9pPuDnsD8ITRVPGV');
		ob_end_clean();
    }
    
    public function testErrorCheck(){
        ob_start();
		$_GET['p'] = 'errorCheck';
        $_GET['run_id'] = '1';
		include("dataerrorlogs.php");
		$this->assertEquals(json_decode($data),'3');
		ob_end_clean();
    }
}

?>
