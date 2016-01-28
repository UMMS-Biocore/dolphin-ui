<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class dataservice_unittest extends PHPUnit_Framework_TestCase
{
    public function testDataservice(){
        ob_start();
		$_GET['wkey'] = 'J98Oe0bSZ18fBx9pPuDnsD8ITRVPGV';
		include("dataservice.php");
		$this->assertEquals(json_decode($data)[0]->id,'id_31');
		ob_end_clean();
    }
}

?>