<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class export_excel_unittest extends PHPUnit_Framework_TestCase
{
    public function testExportExcel(){
        ob_start();
        $_GET['p'] = 'exportExcel';
        $_GET['samples'] = '1';
		include("ngs_tables.php");
		$this->assertEquals($name,"/tmp/files/".$user."_".date('Y-m-d-H-i-s').".xls");
        ob_end_clean();
        return $name;
    }
    
    public function testCheckExperimentSeries(){
        ob_start();
        $_GET['p'] = 'checkExperimentSeries';
        $_GET['samples'] = '1';
		include("ngs_tables.php");
		$this->assertEquals($experiment_series, '1');
        ob_end_clean();
    }
    
    /**
	 * @depends testExportExcel
	 */
    public function testDeleteExcel(){
         ob_start();
        $_GET['p'] = 'deleteExcel';
        $_GET['file'] = $name;
		include("ngs_tables.php");
        //create file check
		//$this->assertEquals();
        ob_end_clean();
    }
}

?>