<?php
//	Include files needed to test ngsimport
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['uid'] = '1';
$_SESSION['user'] = 'docker';
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
		$this->assertEquals($funcs->tool_path,'/home/travis/build/dolphin_tools/src');
		$this->assertEquals($funcs->remotehost,'N');
		$this->assertEquals($funcs->jobstatus,'N');
		$this->assertEquals($funcs->config,'Travis');
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
		$this->assertEquals($funcs->tool_path,'/home/travis/build/dolphin_tools/src');
		$this->assertEquals($funcs->remotehost,'N');
		$this->assertEquals($funcs->jobstatus,'N');
		$this->assertEquals($funcs->config,'Travis');
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
		$this->assertEquals($funcs->checkFile($params),"{\"Result\":\"Ok\"}");
		$params['username'] = 'root';
		$params['file'] = 'does_not_exist.php';
		$this->assertEquals($funcs->checkFile($params),"{\"ERROR\": \"ls: cannot access ".$params['file'].": No such file or directory\"}");
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
	
	public function testCheckStartTime(){
		ob_start();
		$funcs  = new funcs();
		$date=$funcs->queryAVal('SELECT start_time FROM jobs WHERE wkey = \'Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD\'');
		$this->assertEquals(strpos($date,'20') > -1, true );
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
		$params['username'] = 'ak97w';
        $params['wkey'] = 'Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD';
		$this->assertEquals($funcs->checkStatus($params), 'DONE: Service ended successfully (stepCheck)!!!');
		ob_end_clean();
	}
	
	public function testGetServiceOrder(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->getServiceOrder('1','1','Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD'), '-1');
		ob_end_clean();
	}
	
	public function testGetWorkflowID(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->getWorkflowID('Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD'), '1');
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
	
	public function testGetWorkflowInformation(){
		ob_start();
		$funcs  = new funcs();
		$wkey = 'Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD';
		$this->assertEquals($funcs->getWorkflowInformation($wkey)[0], 'galaxy');
		ob_end_clean();
	}
	
	public function testUpdateInputParam(){
		ob_start();
		$funcs  = new funcs();
		$wkey = 'Od1HnRuJ0BJAeMpHOTwsH9rqxBDiD';
		$username = 'galaxy';
		$inputparam = '{"genomebuild":"human,hg19","spaired":"paired","resume":"resume","fastqc":"yes","barcodes":"none","submission":"0","adapters":"none","quality":"none","trim":"none","split":"none","commonind":"none","pipeline":[{"Type":"RNASeqRSEM","Params":"--bowtie-e 70 --bowtie-chunkmbs 100","RSeQC":"no","IGVTDF":"no","BAM2BW":"no","ExtFactor":"0"},{"Type":"DESeq","Name":"","Conditions":"Cond1,Cond1,Cond2,Cond2","Columns":"control_rep2_encode,control_rep3_encode,exper_rep1_encode,exper_rep2_encode","FitType":"parametric","HeatMap":"Yes","padj":"0.01","foldChange":"2","DataType":"RSEM"}]}';
		$this->assertEquals($funcs->updateInputParam($wkey, $username, $inputparam), '{"genomebuild":"human,hg19","spaired":"paired","resume":"resume","fastqc":"yes","barcodes":"none","submission":"0","adapters":"none","quality":"none","trim":"none","split":"none","commonind":"none","pipeline":[{"Type":"RNASeqRSEM","Params":"--bowtie-e 70 --bowtie-chunkmbs 100","RSeQC":"no","IGVTDF":"no","BAM2BW":"no","ExtFactor":"0"},{"Type":"DESeq","Name":"","Conditions":"Cond1,Cond1,Cond2,Cond2","Columns":"control_rep2_encode,control_rep3_encode,exper_rep1_encode,exper_rep2_encode","FitType":"parametric","HeatMap":"Yes","padj":"0.01","foldChange":"2","DataType":"RSEM"}]}');
		ob_end_clean();
	}
	
	public function testUpdateDefaultParam(){
		ob_start();
		$funcs  = new funcs();
		$workflowname = 'seqmapping_workflow';
		$username = 'galaxy';
		$defaultparam = '/usr/local/share/dolphin_tools/default_params/Dolphin_v1.3_Docker.txt';
		$funcs->updateDefaultParam($workflowname, $username, $defaultparam);
		$workflow=$funcs->queryAVal('SELECT defaultparam FROM workflows WHERE workflowname = \'' . $workflowname . '\' AND username = \'' . $username . '\'');
		$this->assertEquals($defaultparam, $workflow);
		ob_end_clean();
	}
	
	public function testGetCommand(){
		ob_start();
		$funcs  = new funcs();
		$servicename = 'stepCheck';
		$username = 'galaxy';
		$inputcommand = '@RUNCLEAN -c @CONFIG -l 0 -u @USERNAME -p @PUBDIR -w @WKEY -d @DBCOMMCMD  -o @OUTDIR';
		$defaultparam = '/usr/local/share/dolphin_tools/default_params/Dolphin_v1.3_Docker.txt';
		$this->assertEquals($funcs->getCommand($servicename, $username, $inputcommand, $defaultparam), '@RUNCLEAN -c @CONFIG -l 0 -u @USERNAME -p @PUBDIR -w @WKEY -d @DBCOMMCMD  -o @OUTDIR');
		ob_end_clean();
	}
	
	public function testStartWorkflow(){
		ob_start();
		$funcs  = new funcs();
		$params['inputparam'] = '{"genomebuild":"human,hg19","spaired":"paired","resume":"resume","fastqc":"yes","barcodes":"none","submission":"0","adapters":"none","quality":"none","trim":"none","split":"none","commonind":"none","pipeline":[{"Type":"RNASeqRSEM","Params":"--bowtie-e 70 --bowtie-chunkmbs 100","RSeQC":"no","IGVTDF":"no","BAM2BW":"no","ExtFactor":"0"},{"Type":"DESeq","Name":"","Conditions":"Cond1,Cond1,Cond2,Cond2","Columns":"control_rep2_encode,control_rep3_encode,exper_rep1_encode,exper_rep2_encode","FitType":"parametric","HeatMap":"Yes","padj":"0.01","foldChange":"2","DataType":"RSEM"}]}';
        $params['defaultparam'] = '/usr/local/share/dolphin_tools/default_params/Dolphin_v1.3_default.txt';
        $params['workflow'] = 'workflow';
        $params['username'] = 'galaxy';
        $params['status'] = '0';
        $params['outdir'] = '/export/barcodetest';
        $params['services'] = '15';
		$params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
		$this->assertEquals($funcs->startWorkflow($params), '3pl8cmzYJ4ezgX2a9RevZxHmihpOA');
		ob_end_clean();
	}
	
	public function testStartService(){
		ob_start();
		$funcs  = new funcs();
                $params['username'] = 'ak97w';
                $params['servicename'] = 'stepCheck';
                $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
		$params['command'] = '@RUNCLEAN -c @CONFIG -l 0 -u @USERNAME -p @PUBDIR -w @WKEY -d @DBCOMMCMD  -o @OUTDIR';
		$this->assertEquals($funcs->startService($params), 'DONE: Service ended successfully (stepCheck)!!!');
		ob_end_clean();
	}
	
	public function testCheckLastServiceJobs(){
		ob_start();
		$funcs  = new funcs();
		$this->assertEquals($funcs->checkLastServiceJobs('3pl8cmzYJ4ezgX2a9RevZxHmihpOA'), 1);
		ob_end_clean();
	}
	
	public function testEndWorkflow(){
		ob_start();
		$funcs  = new funcs();
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
		$this->assertEquals($funcs->endWorkFlow($params), 'Success!!!');
		ob_end_clean();
	}
	
	public function testInsertJob(){
		ob_start();
		$funcs  = new funcs();
        $params['username'] = 'kucukura';
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
        $params['com'] = '/home/ak97w/outTophat6/tmp/src/stepFastQC.submit.bash';
        $params['jobname'] = 'stepCheck';
        $params['servicename'] = 'stepCheck';
        $params['jobnum'] = '99999';
        $params['result'] = 0;
		$params['resources'] = 'none';
		$this->assertEquals($funcs->insertJob($params), 1);
		ob_end_clean();
	}
	
	public function testUpdateJob(){
		ob_start();
		$funcs  = new funcs();
        $params['username'] = 'kucukura';
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
        $params['com'] = '/home/ak97w/outTophat6/tmp/src/stepFastQC.submit.bash';
        $params['jobname'] = 'stepCheck';
		$params['field'] = ''; 
        $params['servicename'] = 'stepCheck';
        $params['jobnum'] = '99999';
        $params['result'] = 0;
		$this->assertEquals($funcs->updateJob($params), 'update jobs set ``=now(), `result`=\'0\' where `wkey`=\'3pl8cmzYJ4ezgX2a9RevZxHmihpOA\' and `job_num`=\'99999\'');
		ob_end_clean();
	}
	
	public function testCheckAllJobsFinished(){
		ob_start();
		$funcs  = new funcs();
        $params['username'] = 'kucukura';
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
        $params['servicename'] = 'stepCheck';
		$this->assertEquals($funcs->checkAllJobsFinished($params), 0);
		#$this->assertEquals(explode("\n", $funcs->checkAllJobsFinished($params))[0], 'Should be still running 1 [1:0]');
		ob_end_clean();
	}
	
	public function testUpdateService(){
		ob_start();
		$funcs  = new funcs();
        $wkey = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
		$service_id = '99999';
        $result = '3';
		$this->assertEquals($funcs->updateService($wkey, $service_id, $result), 1);
		ob_end_clean();
	}
	
	public function testInsertJobOut(){
		ob_start();
		$funcs  = new funcs();
        $params['username'] = 'kucukura';
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
        $params['jobnum'] = '99998';
		$params['jobout'] = 'blob-output';
		$this->assertEquals($funcs->insertJobOut($params), 1);
		ob_end_clean();
	}
	
	/*
	 *
	#	Tables have not been created?
	public function testInsertJobStats(){
		ob_start();
		$funcs  = new funcs();
        $params['username'] = 'kucukura';
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
        $params['jobnum'] = '99998';
		$stats['CPU time'] = '1';
		$stats['Average Memory'] = '1';
		$stats['Max Processes'] = '1';
		$stats['Max Memory'] = '1';
		$stats['Total Requested Memory'] = '2';
		$stats['Max Threads'] = '10';
		$stats['Delta Memory'] = '1';
		$params['stats'] = json_encode($stats);
		$this->assertEquals($funcs->insertJobStats($params), 1);
		ob_end_clean();
	}
	*/
	
	public function testGetJobNums(){
		ob_start();
		$funcs  = new funcs();
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
		$params['jobnum'] = '99998';
		$this->assertEquals($funcs->getJobNums($params)[0]['job_num'], '25863');
		ob_end_clean();
	}
	
	public function testUpdateRunParams(){
		ob_start();
		$funcs  = new funcs();
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
        $params['runparamsid'] = '1';
		$this->assertEquals($funcs->updateRunParams($params), 1);
		ob_end_clean();
	}
	
	public function testInsertReportTable(){
		ob_start();
		$funcs  = new funcs();
        $params['version'] = '1';
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
        $params['type'] = '';
		$params['file'] = '';
		$this->assertEquals($funcs->insertReportTable($params), 1);
		ob_end_clean();
	}
	
	public function testCheckJob(){
		ob_start();
		$funcs  = new funcs();
        $params['jobname'] = 'stepCheck';
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
		$this->assertEquals($funcs->checkJob($params), '{"Result":"START"}');
		ob_end_clean();
	}
	
	/*
	 *
	#	Tables have not been created?
	public function testGetJobParams(){
		ob_start();
		$funcs  = new funcs();
        $params['name'] = 'stepCheck';
        $params['wkey'] = '3pl8cmzYJ4ezgX2a9RevZxHmihpOA';
		$params['servicename'] = 'stepCheck';
		$this->assertEquals($funcs->getJobParams($params), '');
		ob_end_clean();
	}
	
	public function testGetPredValues(){
		
	}
	*/
	
	public function testGetSampleList(){
		//ob_start();
		$funcs  = new funcs();
        $params['barcode'] = 'none';
        $params['runparamsid'] = '1';
		echo $funcs->getSampleList($params);
		$this->assertEquals($funcs->getSampleList($params), array());
		//ob_end_clean();
	}
	
	public function testGetAmazonCredentials(){
		ob_start();
		$funcs  = new funcs();
        $params['username'] = 'docker';
		$this->assertEquals($funcs->getAmazonCredentials($params)[0]['id'], '1');
		$this->assertEquals($funcs->getAmazonCredentials($params)[0]['bucket'], 'test_bucket');
		ob_end_clean();
	}
	
	public function testUpdateInitialFileCounts(){
		ob_start();
		$funcs  = new funcs();
        $params['tablename'] = 'ngs_temp_sample_files';
        $params['total_reads'] = '9999';
		$params['file_id'] = '1';
		$this->assertEquals($funcs->updateInitialFileCounts($params), '1');
		ob_end_clean();
	}
	
	public function testGetFastqFileId(){
		ob_start();
		$funcs  = new funcs();
		$params['sample_id'] = '1';
		$this->assertEquals($funcs->getFastqFileId($params)[0]['sample_id'], '1');
		ob_end_clean();
	}
	
	public function testUpadateFastqFile(){
		ob_start();
		$funcs  = new funcs();
        $params['sample_id'] = '1';
        $params['md5sum'] = 'test_md5sum';
		$params['total_reads'] = '10000';
		$params['owner_id'] = '1';
		$this->assertEquals($funcs->upadateFastqFile($params), '1');
		ob_end_clean();
	}
	
	public function testInsertFastqFile(){
		ob_start();
		$funcs  = new funcs();
        $params['filename'] = 'test_file_name_insertFastqFile';
        $params['total_reads'] = '9999';
		$params['checksum'] = 'test_checksum_1';
		$params['sample_id'] = '100';
		$params['lane_id'] = '1';
		$params['dir_id'] = '1';
		$params['owner_id'] = '1';
		$params['group_id'] = '1';
		$params['perms'] = '32';
		$this->assertEquals($funcs->insertFastqFile($params), '1');
		ob_end_clean();
	}
	
	public function CheckReadCounts(){
		ob_start();
		$funcs  = new funcs();
        $params['sample_id'] = '1';
        $params['tablename'] = 'ngs_temp_sample_files';
		$this->assertEquals($funcs->upadateFastqFile($params), '1');
		ob_end_clean();
	}
}

?>
