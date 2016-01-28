<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/ajax/');

class sessionrequests_unittest extends PHPUnit_Framework_TestCase
{
	public function testSessionTest(){
		ob_start();
		$_GET['p'] = 'sessionTest';
		include('sessionrequests.php');
		$this->assertEquals(1,1);
		ob_end_clean();
	}
	
	public function testSendBasketInfo(){
		ob_start();
		$_GET['p'] = 'sendBasketInfo';
		$_POST['id'] = '2';
		$_SESSION['basket'] = '1';
		include('sessionrequests.php');
		$this->assertEquals($_SESSION['basket'],'1,2');
		ob_end_clean();
	}
	
	public function testGetBasketInfo(){
		ob_start();
		$_GET['p'] = 'getBasketInfo';
		$_SESSION['basket'] = '1';
		include('sessionrequests.php');
		$this->assertEquals($_SESSION['basket'],'1');
		ob_end_clean();
	}
	
	public function testRemoveBasketInfo(){
		ob_start();
		$_GET['p'] = 'removeBasketInfo';
		$_POST['id'] = '2';
		$_SESSION['basket'] = '1,2';
		include('sessionrequests.php');
		$this->assertEquals($_SESSION['basket'],'1');
		ob_end_clean();
	}
	
	public function testFlushBasketInfo(){
		ob_start();
		$_GET['p'] = 'flushBasketInfo';
		$_SESSION['basket'] = '1,2';
		include('sessionrequests.php');
		$this->assertEquals(isset($_SESSION['basket']),false);
		ob_end_clean();
	}
	
	public function testSendWKey(){
		ob_start();
		$_GET['p'] = 'sendWKey';
		$_POST['wkey'] = 'wkey';
		include('sessionrequests.php');
		$this->assertEquals($_SESSION['wkey'],'wkey');
		ob_end_clean();
	}
	
	public function testGetWkey(){
		ob_start();
		$_GET['p'] = 'getWKey';
		$_POST['wkey'] = 'wkey';
		include('sessionrequests.php');
		$this->assertEquals($_POST['wkey'],'wkey');
		ob_end_clean();
	}
	
	public function testFlushWKey(){
		ob_start();
		$_GET['p'] = 'flushWKey';
		$_SESSION['wkey'] = 'wkey';
		include('sessionrequests.php');
		$this->assertEquals(isset($_SESSION['wkey']),false);
		ob_end_clean();
	}
	
	public function testTableToggle(){
		ob_start();
		$_GET['p'] = 'tableToggle';
		$_GET['table'] = 'samples';
		include('sessionrequests.php');
		$this->assertEquals($_SESSION['ngs_samples'],'extend');
		ob_end_clean();
	}
	
	public function testGetTableToggle(){
		ob_start();
		$_GET['p'] = 'getTableToggle';
		$_GET['table'] = 'samples';
		include('sessionrequests.php');
		$this->assertEquals($_SESSION['ngs_samples'],'');
		ob_end_clean();
	}
	
	public function testSetPlotToggle(){
		ob_start();
		$_GET['p'] = 'setPlotToggle';
		$_GET['type'] = 'generated';
		$_GET['file'] = 'test.file';
		include('sessionrequests.php');
		$this->assertEquals($_SESSION['plot_file'],'test.file');
		ob_end_clean();
	}
	
	public function testGetPlotToggle(){
		ob_start();
		$_GET['p'] = 'getPlotToggle';
		$_SESSION['plot_type'] = 'generated';
		$_SESSION['plot_file'] = 'test.file';
		include('sessionrequests.php');
		$this->assertEquals(isset($_SESSION['plot_file']),true);
		ob_end_clean();
	}
	
	public function testChangeRunType(){
		ob_start();
		$_GET['p'] = 'changeRunType';
		$_GET['run_type'] = '1';
		include('sessionrequests.php');
		$this->assertEquals(isset($_SESSION['run_type']),true);
		$this->assertEquals($_SESSION['run_type'],1);
		ob_end_clean();
	}
	
	public function testGetRunType(){
		ob_start();
		$_GET['p'] = 'getRunToggle';
		include('sessionrequests.php');
		$this->assertEquals(isset($_SESSION['run_type']),false);
		ob_end_clean();
	}
}

?>