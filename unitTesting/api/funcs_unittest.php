<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'kucukura';
chdir('public/api/');

include 'funcs.php';

class funcs_unittest extends PHPUnit_Framework_TestCase
{
	public function testReadINI(){
		ob_start();
		$funcs  = new funcs();
		$funcs->readINI();
		$this->assertEquals($funcs->dbhost,'localhost');
		$this->assertEquals($funcs->db,'biocore');
		$this->assertEquals($funcs->dbuser,'bioinfo');
		$this->assertEquals($funcs->dbpass,'bioinfo2013');
		$this->assertEquals($funcs->tool_path,'/usr/local/share/dolphin_tools/src');
		$this->assertEquals($funcs->remotehost,'N');
		$this->assertEquals($funcs->jobstatus,'N');
		$this->assertEquals($funcs->config,'Docker');
		$this->assertEquals($funcs->python,'python');
		$this->assertEquals($funcs->schedular,'N');
		ob_end_clean();
	}
	
	public function testGetINI(){
		ob_start();
		$funcs  = new funcs();
		$funcs = $funcs->getINI();
		$this->assertEquals($funcs->dbhost,'localhost');
		$this->assertEquals($funcs->db,'biocore');
		$this->assertEquals($funcs->dbuser,'bioinfo');
		$this->assertEquals($funcs->dbpass,'bioinfo2013');
		$this->assertEquals($funcs->tool_path,'/usr/local/share/dolphin_tools/src');
		$this->assertEquals($funcs->remotehost,'N');
		$this->assertEquals($funcs->jobstatus,'N');
		$this->assertEquals($funcs->config,'Docker');
		$this->assertEquals($funcs->python,'python');
		$this->assertEquals($funcs->schedular,'N');
		ob_end_clean();
	}
	
	public function testSetCMDs(){
		ob_start();
		$funcs  = new funcs();
		$funcs->setCMDs();
		$this->assertEquals($funcs->checkjob_cmd,'ps -ef|grep "[[:space:]][[:space:]]"|awk \'{printf("%s	%s",$8,$2)}\'');
		ob_end_clean();
	}
	
	public function testGetCMDs(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->getCMDS(''),'');
		ob_end_clean();
	}
	
	public function testCheckFile(){
		ob_start();
		$funcs  = new funcs();
		$params['username'] = 'root';
		$params['file'] = 'funcs.php';
		var_dump($params);
		$this->assertEquals($funcs->checkFile($params),"{\"Result\":\"Ok\"}");
		$params['username'] = 'root';
		$params['file'] = 'does_not_exist.php';
		$this->assertEquals($funcs->checkFile($params),"{\"ERROR\": \"No such file or directory: ".$params['file']."\"}");
		ob_end_clean();
	}
	
	public function testCheckPermissions(){
		ob_start();
		$funcs  = new funcs();
		$params['username'] = 'root';
		$params['outdir'] = 'funcs_dir_test';
		$this->assertEquals($funcs->checkPermissions($params),"{\"Result\":\"Ok\"}");
		ob_end_clean();
	}
	
	public function testGetKey(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals(strlen($funcs->getKey()),30);
		ob_end_clean();
	}
	
	public function testRunSQL(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->runSQL('SELECT * FROM ngs_samples')->type,0);
		ob_end_clean();
	}
	
	public function testQueryAVal(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->queryAVal('SELECT id FROM ngs_samples WHERE samplename = \'control_rep1\''),'1');
		ob_end_clean();
	}
	
	public function testQueryTable(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->queryTable('SELECT id FROM ngs_samples WHERE samplename = \'control_rep1\'')[0]['id'],'1');
		ob_end_clean();
	}
	
	public function testSyscall(){
		ob_start();
		$funcs  = new funcs();
		$command = 'ls funcs.php';
		$this->assertEquals(str_replace("\n", "", $funcs->syscall($command)),'funcs.php');
		$command = 'ls hootnanny';
		$this->assertEquals(str_replace("\n", "", $funcs->syscall($command)),"ls: cannot access hootnanny: No such file or directory");
		ob_end_clean();
	}
	
	public function testGetSSH(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->getSSH(),"ssh -o ConnectTimeout=30  ". $funcs->username. "@" . $funcs->remotehost . " ");
		ob_end_clean();
	}
	
	public function testIsJobRunning(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->isJobRunning('test_wkey', '99999', 'root'), 'EXIT');
		ob_end_clean();
	}
	
	public function testCheckJobInDB(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->checkJobInDB('test_wkey', '99999', 'root'), 0);
		ob_end_clean();
	}
	
	public function testRerunJob(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->rerunJob('stepCheck', 'stepCheck', 'kucukura', 'Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD'), 1);
		ob_end_clean();
	}
	
	public function testCheckStatus(){
		ob_start();
		$funcs  = new funcs();
		$params['servicename'] = 'stepCheck';
        $params['wkey'] = 'Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD';
		$this->assertEquals($funcs->checkStatus($params), 'START');
		ob_end_clean();
	}
	
	public function testGetServiceOrder(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->getServiceOrder('1','1','Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD'), '');
		ob_end_clean();
	}
	
	public function testGetWorkflowID(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->getWorkflowID('Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD'), '');
		ob_end_clean();
	}
	
	public function testGetId(){
		ob_start();
		$funcs  = new funcs();
		$name = 'workflow';
		$username = 'galaxy';
		$val = 'seqmapping_workflow';
		$wkey = 'Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD';
		$defaultparam = '/usr/local/share/dolphin_tools/default_params/Dolphin_v1.3_default.txt';
		$this->assertEquals($funcs->getID($name, $username, $val, $wkey, $defaultparam), '1');
		ob_end_clean();
	}
}

?>