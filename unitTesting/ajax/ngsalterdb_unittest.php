<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class ngsalterdb_unittest extends PHPUnit_Framework_TestCase
{	
	public function testSubmitPipelineInsert(){
		ob_start();
		$_POST['p'] = 'submitPipeline';
		$_POST['json'] = '{"genomebuild":"human,hg19","spaired":"no","resume":"no","fastqc":"no","barcodes":"none","adapters":"none","quality":"none","trim":"none","split":"none","commonind":"none","submission":"0"}';
		$_POST['outdir'] = '/home/travis/build/testrun1';
		$_POST['name'] = 'Import Initial Run';
		$_POST['desc'] = 'Import Initial Run within series: Travis';
		$_POST['runGroupID'] = 'new';
		$_POST['barcode'] = '0';
		$_POST['uid'] = '1';
		$_POST['group'] = '1';
		$_POST['perms'] = '32';
		$_POST['ids'] = '';
		include("ngsalterdb.php");
		$this->assertEquals(json_decode($data),'4');
		ob_end_clean();
	}
	
	public function testSubmitPipelineUpdate(){
		ob_start();
		$_POST['p'] = 'submitPipeline';
		$_POST['json'] = 'test_json_update';
		$_POST['outdir'] = '/home/travis/build/testrun1';
		$_POST['name'] = 'test insertPipeline update';
		$_POST['desc'] = 'unittesting insertPipeline update';
		$_POST['runGroupID'] = '3';
		$_POST['barcode'] = 'none';
		$_POST['uid'] = '1';
		$_POST['group'] = '1';
		$_POST['perms'] = '32';
		$_POST['ids'] = '1';
		include("ngsalterdb.php");
		$this->assertEquals(json_decode($data),4);
		ob_end_clean();
	}
	
	public function testInsertRunlist(){
		ob_start();
		$_POST['p'] = 'insertRunlist';
		$_POST['sampID'] = '7';
		$_POST['runID'] = '4';
		$_POST['uid'] = '1';
		$_POST['gids'] = '1';
		include("ngsalterdb.php");
		$this->assertEquals(json_decode($data),'7');
		ob_end_clean();
	}
	
	public function testNoAddedParamsRerun(){
		ob_start();
		$_POST['p'] = 'noAddedParamsRerun';
		$_POST['run_id'] = '1';
		include("ngsalterdb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testUpdateProfile(){
		ob_start();
		$_POST['p'] = 'updateProfile';
		$_POST['img'] = 'test_img.png';
		include("ngsalterdb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	/*
	public function testDeleteRunparams(){
		ob_start();
		$_POST['p'] = 'deleteRunparams';
		$_POST['run_id'] = '3';
		include("ngsalterdb.php");
		$this->assertEquals(json_decode($data),null);
		ob_end_clean();
	}
	public function testAlterAccessKey(){
		ob_start();
		$_POST['p'] = 'alterAccessKey';
		$_POST['id'] = '1';
		$_POST['a_key'] = 'ngsalterdb new key';
		include("ngsalterdb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	
	public function testAlterSecretKey(){
		ob_start();
		$_POST['p'] = 'alterSecretKey';
		$_POST['id'] = '1';
		$_POST['s_key'] = 'ngsalterdb new secret key';
		include("ngsalterdb.php");
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	*/
}

?>