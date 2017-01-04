<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'docker';
chdir('public/ajax/');

class tablegenerator_unittest extends PHPUnit_Framework_TestCase
{
	public function testGetTableSamples() {
		ob_start();
		$_GET['p'] = 'getTableSamples';
		$_GET['search'] = 7;
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data)[0]->id,'7');
		$this->assertEquals(json_decode($data)[0]->samplename,'example_sample_1');
		ob_end_clean();
	}
	
	public function testGetTableRuns() {
		ob_start();
		$_GET['p'] = 'getTableRuns';
		$_GET['search'] = 1;
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data)[0]->sample_id,'1');
		$this->assertEquals(json_decode($data)[0]->run_id,'1');
		$this->assertEquals(json_decode($data)[0]->run_name,'barcode test');
		$this->assertEquals(json_decode($data)[0]->wkey,'J98Oe0bSZ18fBx9pPuDnsD8ITRVPGV');
		ob_end_clean();
	}
	
	//find wkey example
	public function testGetTableReportsList() {
		ob_start();
		$_GET['p'] = 'getTableReportsList';
		$_GET['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data)[0]->file,'rsem/genes_expression_tpm.tsv');
		$this->assertEquals(json_decode($data)[0]->json_parameters,null);
		ob_end_clean();
	}
	
	public function testSamplesWithRuns() {
		ob_start();
		$_GET['p'] = 'samplesWithRuns';
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data)[0]->sample_id,'1');
		ob_end_clean();
	}
	
	public function testCreateTableFile(){
		ob_start();
		$_GET['p'] = 'createTableFile';
		$_GET['samples'] = 'samples=1,2,3,4,5,6:3';
		$_GET['file'] = 'file=rsem/genes_expression_tpm.tsv';
		$_GET['common'] = 'common=gene,transcript';
		$_GET['key'] = 'key=gene';
		$_GET['format'] = 'format=json';
		$_GET['url'] = substr(getcwd(), 0, strlen(getcwd()) - 11) . 'public/api/getsamplevals.php';
		include('tablegenerator.php');
		$file = json_decode($data);
		$this->assertEquals(json_decode($data),$file);
		ob_end_clean();
		return $file;
	}
	
	/**
	 * @depends testCreateTableFile
	 */
	public function testCreateNewTable($file){
		ob_start();
		$_GET['p'] = 'createNewTable';
		$_GET['search'] = 'samples=1,2,3,4,5,6:3&file=rsem/genes_expression_tpm.tsv&common=gene,transcript&key=gene&format=json';
		$_GET['name'] = 'test_table';
		$_GET['file'] = $file;
		$_GET['group'] = '1';
		$_GET['perms'] = '15';
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data),'true');
		$_GET['p'] = 'createNewTable';
		$_GET['search'] = 'samples=1,2,3,4,5,6:3&file=rsem/genes_expression_tpm.tsv&common=gene,transcript&key=gene&format=json';
		$_GET['name'] = 'test_table2';
		$_GET['file'] = $file;
		$_GET['group'] = '1';
		$_GET['perms'] = '15';
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data),'true');
		ob_end_clean();
	}
	
	public function testGetCreatedTables(){
		ob_start();
		$_GET['p'] = 'getCreatedTables';
		$_GET['gids'] = '1';
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_table2');
		$this->assertEquals(json_decode($data)[0]->parameters,'samples=1,2,3,4,5,6:3&file=rsem/genes_expression_tpm.tsv&common=gene,transcript&key=gene&format=json');
		$this->assertEquals(json_decode($data)[0]->owner_id,'1');
		$this->assertEquals(json_decode($data)[0]->group_id,'1');
		$this->assertEquals(json_decode($data)[0]->perms,'15');
		$this->assertEquals(json_decode($data)[0]->last_modified_user,'1');
		ob_end_clean();
	}
	
	public function testDeleteTable(){
		ob_start();
		$_GET['p'] = 'deleteTable';
		$_GET['id'] = '1';
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data),'1');
		ob_end_clean();
	}
	/*
	public function testConvertToTSV(){
		ob_start();
		$_GET['p'] = 'convertToTSV';
		$_GET['url'] = substr(getcwd(), 0, strlen(getcwd()) - 11) . 'public/api/getsamplevals.php';
		$_GET['samples'] = 'samples=1,2,3,4,5,6:3';
		$_GET['file'] = 'file=rsem/genes_expression_tpm.tsv';
		$_GET['common'] = 'common=gene,transcript';
		$_GET['key'] = 'key=gene';
		$_GET['format'] = 'format=json';
		include('tablegenerator.php');
		$file = json_decode($data);
		$this->assertEquals(json_decode($data),$file);
		ob_end_clean();
		return $file;
	}
	*/
	/**
	 * @depends testConvertToTSV
	 */
	/*
	public function testRemoveTSV($file){
		ob_start();
		$_GET['p'] = 'removeTSV';
		$_GET['file'] = $file;
		include('tablegenerator.php');
		$this->assertEquals(json_decode($data),'deleted');
		ob_end_clean();
	}
	*/
}

?>
