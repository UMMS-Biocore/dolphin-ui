<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'docker';
chdir('public/ajax/');

class ngsquerydb_unittest extends PHPUnit_Framework_TestCase
{
	public function testGetRunSamples(){
		ob_start();
		$_GET['p'] = 'getRunSamples';
		$_GET['gids'] = '1';
		$_GET['runID'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->sample_id,'1');
		$this->assertEquals(json_decode($data)[1]->sample_id,'2');
		$this->assertEquals(json_decode($data)[2]->sample_id,'3');
		$this->assertEquals(json_decode($data)[3]->sample_id,'4');
		$this->assertEquals(json_decode($data)[4]->sample_id,'5');
		$this->assertEquals(json_decode($data)[5]->sample_id,'6');
		ob_end_clean();
	}
	
	public function testGrabReload(){
		ob_start();
		$_GET['p'] = 'grabReload';
		$_GET['groupID'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->outdir,'/export/barcodetest');
		$this->assertEquals(json_decode($data)[0]->json_parameters,'{"genomebuild":"mousetest,mm10","spaired":"paired","resume":"resume","fastqc":"yes","barcodes":"distance,1:format,5 end read 1","adapter":"none","quality":"none","trim":"none","split":"none","commonind":"rRNA,miRNA,tRNA","pipeline":["RNASeqRSEM:--bowtie-e 70 --bowtie-chunkmbs 100:no:no"]}');
		$this->assertEquals(json_decode($data)[0]->run_name,'barcode test');
		$this->assertEquals(json_decode($data)[0]->run_description,'barcode test');
		$this->assertEquals(json_decode($data)[0]->group_id,null);
		$this->assertEquals(json_decode($data)[0]->perms,'3');
		ob_end_clean();
	}
	
	public function testGetReportNames(){
		ob_start();
		$_GET['p'] = 'getReportNames';
		$_GET['runid'] = '1';
		$_GET['samp'] = '1,2,3';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->file_name,'control_rep1.1.fastq.gz,control_rep1.2.fastq.gz');
		$this->assertEquals(json_decode($data)[0]->outdir,'/export/barcodetest');
		ob_end_clean();
	}
	
	public function testLanesToSamples(){
		ob_start();
		$_GET['p'] = 'lanesToSamples';
		$_GET['lane'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[1]->id,'2');
		$this->assertEquals(json_decode($data)[2]->id,'3');
		$this->assertEquals(json_decode($data)[3]->id,'4');
		$this->assertEquals(json_decode($data)[4]->id,'5');
		$this->assertEquals(json_decode($data)[5]->id,'6');
		ob_end_clean();
	}
	
	public function testGetAllSampleIds(){
		ob_start();
		$_GET['p'] = 'getAllSampleIds';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[1]->id,'2');
		$this->assertEquals(json_decode($data)[2]->id,'3');
		$this->assertEquals(json_decode($data)[3]->id,'4');
		$this->assertEquals(json_decode($data)[4]->id,'5');
		$this->assertEquals(json_decode($data)[5]->id,'6');
		$this->assertEquals(json_decode($data)[6]->id,'7');
		ob_end_clean();
	}
	
	public function testGetAllLaneIds(){
		ob_start();
		$_GET['p'] = 'getAllLaneIds';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[1]->id,'2');
		ob_end_clean();
	}
	
	public function testGetAllExperimentIds(){
		ob_start();
		$_GET['p'] = 'getAllExperimentIds';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[1]->id,'2');
		ob_end_clean();
	}
	
	public function testGetLaneIdFromSample(){
		ob_start();
		$_GET['p'] = 'getLaneIdFromSample';
		$_GET['sample'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		ob_end_clean();
	}
	
	public function testGetExperimentIdFromSample(){
		ob_start();
		$_GET['p'] = 'getExperimentIdFromSample';
		$_GET['sample'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		ob_end_clean();
	}
	
	public function testGetCustomTSV(){
		ob_start();
		$_GET['p'] = 'getCustomTSV';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data),array());
		ob_end_clean();
	}
	
	public function testCheckOutputDir(){
		ob_start();
		$_GET['p'] = 'checkOutputDir';
		$_GET['outdir'] = '/export/barcodetest';
		include("ngsquerydb.php");
		$this->assertEquals($data,'"\/export\/barcodetest"');
		ob_end_clean();
	}
	
	public function testChangeDataGroupNames(){
		ob_start();
		$_GET['p'] = 'changeDataGroupNames';
		$_GET['experiment'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals($data,'[{"id":"1","name":"umw_biocore"}]');
		ob_end_clean();
	}
	
	public function testChangeDataGroup(){
		ob_start();
		$_GET['p'] = 'changeDataGroup';
		$_GET['group_id'] = '1';
		$_GET['experiment'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data),'passed');
		ob_end_clean();
	}
	public function testGetExperimentSeriesGroup(){
		ob_start();
		$_GET['p'] = 'getExperimentSeriesGroup';
		$_GET['experiment'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->group_id,'1');
		$this->assertEquals(json_decode($data)[0]->owner_id,'1');
		ob_end_clean();
	}
	
	public function testGetGroups(){
		ob_start();
		$_GET['p'] = 'getGroups';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'umw_biocore');
		ob_end_clean();
	}
	
	public function testGetRunPerms(){
		ob_start();
		$_GET['p'] = 'getRunPerms';
		$_GET['run_id'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data),'3');
		ob_end_clean();
	}
	
	public function testChangeRunGroup(){
		ob_start();
		$_GET['p'] = 'changeRunGroup';
		$_GET['run_id'] = '1';
		$_GET['group_id'] = '2';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data),'pass');
		ob_end_clean();
	}
	
	public function testChangeRunPerms(){
		ob_start();
		$_GET['p'] = 'changeRunPerms';
		$_GET['run_id'] = '1';
		$_GET['perms'] = '15';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data),'pass');
		ob_end_clean();
	}
	
	public function testGetAllUsers(){
		ob_start();
		$_GET['p'] = 'getAllUsers';
		$_GET['experiment'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->username,'docker');
		ob_end_clean();
	}
	
	public function testChangeOwnerExperiment(){
		ob_start();
		$_GET['p'] = 'changeOwnerExperiment';
		$_GET['owner_id'] = '1';
		$_GET['experiment'] = '1';
		include("ngsquerydb.php");
		$this->assertEquals(json_decode($data),'passed');
		ob_end_clean();
	}
}

?>