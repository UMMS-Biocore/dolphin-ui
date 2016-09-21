<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class browse_edit_unittest extends PHPUnit_Framework_TestCase
{
    public function testUpdateDatabase(){
        ob_start();
		$_GET['p'] = 'updateDatabase';
		$_GET['id'] = '1';
        $_GET['type'] = 'organism';
        $_GET['table'] = 'ngs_samples';
        $_GET['value'] = 'human';
		include("browse_edit.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
    }
	
	public function testCheckPerms(){
		ob_start();
		$_GET['p'] = 'checkPerms';
        $_GET['id'] = '1';
        $_GET['uid'] = '1';
        $_GET['table'] = 'ngs_samples';
		include("browse_edit.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testGetDropdownValues(){
		ob_start();
		$_GET['p'] = 'getDropdownValues';
        $_GET['type'] = 'organism';
		include("browse_edit.php");
		$this->assertEquals(json_decode($data)[0]->organism,'mousetest');
		ob_end_clean();
	}
	
	public function testGetExperimentPermissions(){
		ob_start();
		$_GET['p'] = 'getExperimentPermissions';
        $_GET['experiments'] = '1';
		include("browse_edit.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		ob_end_clean();
	}
	
	public function testGetLanePermissions(){
		ob_start();
		$_GET['p'] = 'getLanePermissions';
        $_GET['lanes'] = '1';
		include("browse_edit.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		ob_end_clean();
	}
	
	public function testGetSamplePermissions(){
		ob_start();
		$_GET['p'] = 'getSamplePermissions';
        $_GET['samples'] = '1';
		include("browse_edit.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		ob_end_clean();
	}
	
	public function testDeleteSelected(){
		ob_start();
		$_GET['p'] = 'deleteSelected';
        $_GET['samples'] = '11';
		$_GET['lanes'] = '4';
		$_GET['experiments'] = '3';
		include("browse_edit.php");
		$this->assertEquals($data,'');
		ob_end_clean();
	}
	
	public function testIntialRunCheck(){
		ob_start();
		$_GET['p'] = 'intialRunCheck';
        $_GET['samples'] = '1';
		include("browse_edit.php");
		$this->assertEquals(json_decode($data)[0]->sample_id,'1');
		ob_end_clean();
	}
}

?>