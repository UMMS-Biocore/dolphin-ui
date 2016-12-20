<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'docker';
chdir('public/ajax/');

class ngsfastlanddb_unittest extends PHPUnit_Framework_TestCase
{
	public function testExperimentSeriesCheck(){
		ob_start();
		$_GET['p'] = 'experimentSeriesCheck';
		$_GET['name'] = 'Barcode Sep Test ';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testLaneCheck(){
		ob_start();
		$_GET['p'] = 'laneCheck';
		$_GET['experiment'] = '1';
		$_GET['lane'] = 'New Lane';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testSampleCheck(){
		ob_start();
		$_GET['p'] = 'sampleCheck';
		$_GET['experiment'] = '1';
		$_GET['lane'] = '1';
		$_GET['sample'] = 'control_rep1';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testDirectoryCheck(){
		ob_start();
		$_GET['p'] = 'directoryCheck';
		$_GET['input'] = '/export/genome_data/mousetest/mm10/barcodetest';
		$_GET['backup'] = '/export/backup/barcodetest';
		$_GET['amazon'] = 's3';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testInsertExperimentSeries(){
		ob_start();
		$_GET['p'] = 'insertExperimentSeries';
		$_POST['name'] = 'test_series_1';
		$_POST['gids'] = '1';
		$_POST['perms'] = '32';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testInsertLane(){
		ob_start();
		$_GET['p'] = 'insertLane';
		$_POST['experiment'] = '3';
		$_POST['lane'] = 'test_lane_1';
		$_POST['gids'] = '1';
		$_POST['perms'] = '32';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testInsertSample(){
		ob_start();
		$_GET['p'] = 'insertSample';
		$_GET['experiment'] = '3';
		$_GET['lane'] = '3';
		$_GET['organism'] = 'tst_org,test_organism';
		$_GET['barcode'] = 'test_barcode';
		$_GET['sample'] = 'test_sample_1';
		$_GET['gids'] = '1';
		$_GET['perms'] = '32';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testInsertDirectories(){
		ob_start();
		$_GET['p'] = 'insertDirectories';
		$_POST['input'] = '/input/directory/test';
		$_POST['backup'] = '/backup/directory/test';
		$_POST['amazon'] = 'S3:testAmazon';
		$_POST['gids'] = '1';
		$_POST['perms'] = '32';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testInsertTempSample(){
		ob_start();
		$_GET['p'] = 'insertTempSample';
		$_POST['filename'] = 'test_filename_sample';
		$_POST['sample_id'] = '11';
		$_POST['input'] = '3';
		$_POST['gids'] = '1';
		$_POST['perms'] = '32';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testInsertTempLane(){
		ob_start();
		$_GET['p'] = 'insertTempLane';
		$_POST['file_name'] = 'test_filename_lane';
		$_POST['lane_id'] = '3';
		$_POST['dir_id'] = '3';
		$_POST['gids'] = '1';
		$_POST['perms'] = '32';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testSendProcessData(){
		ob_start();
		$_POST['p'] = 'sendProcessData';
		$_POST['info_array'] = array('test1','test2');
		$_POST['post'] = 'test_post';
		include("ngsfastlanedb.php");
		$this->assertEquals($_SESSION['test_post'],'test1,test2');
		ob_end_clean();
	}
	
	public function testObtainGroupFromName(){
		ob_start();
		$_GET['p'] = 'obtainGroupFromName';
		$_GET['name'] = 'umw_biocore';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testGetClusterName(){
		ob_start();
		$_GET['p'] = 'getClusterName';
		include("ngsfastlanedb.php");
		$this->assertEquals(json_decode($data)[0]->username,'kucukura');
		$this->assertEquals(json_decode($data)[0]->clusteruser,'ak97w');
		ob_end_clean();
	}
}

?>