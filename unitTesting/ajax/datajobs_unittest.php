<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class datajobs_unittest extends PHPUnit_Framework_TestCase
{
    public function testDatajobs(){
        ob_start();
		$_GET['id'] = '1';
		include("datajobs.php");
		$this->assertEquals(json_decode($data)[0]->id,'id_1');
		ob_end_clean();
    }
}

?>