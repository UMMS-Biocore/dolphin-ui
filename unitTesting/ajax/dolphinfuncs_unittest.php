<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class dolphinfuncs_unittest extends PHPUnit_Framework_TestCase
{
    public function testGetFileList(){
        ob_start();
		$_GET['p'] = 'getFileList';
		include("dolphinfuncs.php");
		$this->assertEquals(json_decode($data)[0]->file_name,'control_rep1.1.fastq.gz,control_rep1.2.fastq.gz');
		ob_end_clean();
    }
    
    public function testUpdateHashBackup(){
        ob_start();
		$_GET['p'] = 'updateHashBackup';
        $_GET['input'] = 'control_rep1.1.fastq.gz,control_rep1.2.fastq.gz';
        $_GET['dirname'] = '/export/genome_data/mousetest/mm10/barcodetest';
        $_GET['hashstr'] = 'test_hash';
		include("dolphinfuncs.php");
		$this->assertEquals(json_decode($data),array());
		ob_end_clean();
    }
}

?>