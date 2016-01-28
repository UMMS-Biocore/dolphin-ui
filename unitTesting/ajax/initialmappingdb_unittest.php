<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class initialmappingdb_unittest extends PHPUnit_Framework_TestCase
{
    public function testSampleChecking(){
        ob_start();
        $_GET['p'] = 'sampleChecking';
        $_SESSION['uid'] = '1';
        $_GET['gids'] = '1';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data)[0]->id,'1');
        ob_end_clean();
    }
    
    public function testLaneChecking(){
        ob_start();
        $_GET['p'] = 'laneChecking';
        $_GET['uid'] = '1';
        $_GET['gids'] = '1';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data)[0]->lane_id,'1');
        ob_end_clean();
    }
    
    public function testLaneToSampleChecking(){
        ob_start();
        $_GET['p'] = 'laneToSampleChecking';
        $_GET['sample_ids'] = '1';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data)[0]->sample_id,'1');
        ob_end_clean();
    }
    
    public function testGetCounts(){
        ob_start();
        $_GET['p'] = 'getCounts';
        $_GET['samples'] = '1,2';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data)[0]->total_reads,'24788');
        ob_end_clean();
    }
    
    public function testCheckRunList(){
        ob_start();
        $_GET['p'] = 'checkRunList';
        $_GET['sample_ids'] = '1';
        $_GET['run_ids'] = '1';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data)[0]->run_id,'1');
        ob_end_clean();
    }
    
    public function testCheckRunParams(){
        ob_start();
        $_GET['p'] = 'checkRunParams';
        $_GET['run_ids'] = '1';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data),array());
        ob_end_clean();
    }
    
    public function testCheckRunToSamples(){
        ob_start();
        $_GET['p'] = 'checkRunToSamples';
        $_GET['run_id'] = '1';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data)[0]->sample_id,'1');
        ob_end_clean();
    }
    
    public function testCheckFileToSamples(){
        ob_start();
        $_GET['p'] = 'checkFileToSamples';
        $_GET['run_id'] = '1';
        $_GET['file_name'] = 'control_rep1.1.fastq.gz,control_rep1.2.fastq.gz';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data)[0]->file_name,'control_rep1.1.fastq.gz,control_rep1.2.fastq.gz');
        ob_end_clean();
    }
    
    public function testRemoveRunlistSamples(){
        ob_start();
        $_GET['p'] = 'removeRunlistSamples';
        $_GET['run_id'] = '1';
        $_GET['sample_ids'] = '1';
        include("initialmappingdb.php");
        $this->assertEquals(json_decode($data),null);
        ob_end_clean();
    }
}

?>