<?php
require_once("../../config/config.php");

class funcs
{
    public $dbhost = "";
    public $db = "";
    public $dbuser = "";
    public $dbpass = "";
    public $tool_path = "";
    public $remotehost = "";
    public $jobstatus = "";
    public $config = "";
    public $python = "";
    public $schedular = "";
    public $checkjob_cmd = "";
    public $job_num = "";
    public $username = "";
    function readINI()
    {
            $this->dbhost     = DB_HOST;
            $this->db         = DB_NAME;
            $this->dbpass     = DB_PASSWORD;
            $this->dbuser     = DB_USER;
            $this->tool_path  = DOLPHIN_TOOLS_SRC_PATH;
            $this->remotehost = REMOTE_HOST;
            $this->jobstatus  = JOB_STATUS;
            $this->config     = CONFIG;
            $this->python     = PYTHON;
            $this->schedular  = SCHEDULAR;
            $this->setCMDs();
    }
    function getINI()
    {
        $this->readINI();
        return $this;
    }
    function setCMDs()
    { 
        if($this->schedular == "LSF")
        {
            $this->checkjob_cmd = $this->getSSH() . " \"" . $this->jobstatus . " $this->job_num\"|grep " . $this->job_num . "|awk '{printf (\"%s\t%s\",\$3,\$1)}'";
        }
        else if($this->schedular == "SGE")
        {
            #Put SGE commands here
        }
        else
        {
            $this->checkjob_cmd = "ps -ef|grep \"[[:space:]]" . $this->job_num . "[[:space:]]\"|awk '{printf(\"%s\t%s\",\$8,\$2)}'";
            #$this->checkjob_cmd = "ps -ef|grep \"[[:space:]]" . $this->job_num . "[[:space:]]\"";
        }
    }
    function getCMDs($com)
    {
        
        if($this->schedular == "LSF" || $this->schedular == "SGE")
        {
            $com=str_replace("\"", "\\\"", $com);
            $com=$this->getSSH() . " \"" . $com . "\"";
        } 
        return $com;
    }
    function sendLog($run_id, $description, $retval){
        $result = "";
        $mkdir = $this->syscall($this->getCMDs("mkdir -p ../../tmp/logs/run".$run_id));
        if (preg_match('/cannot create directory/', $mkdir)) {
            $file = "../../tmp/logs/run".$run_id."/API.log";
        }else if($mkdir == ""){
            $file = "../../tmp/logs/run".$run_id."/API.log";
        }else{
            return $mkdir;
        }
        
        $logging = fopen($file, "a");
        if($logging != NULL){
            fwrite($logging, $description . PHP_EOL . $retval . PHP_EOL);
            fclose($logging);
            return 'pass';
        } else {
            return "ERROR 104: Cannot Log Error";
        }
    }
    function checkFile($params)
    {
        $this->username=$params['username'];
        $this->readINI();
        $file_array = explode(",",$params['file']);
        $com = 'ls ';
        $files = '';
        foreach($file_array as $fa){
            $files .= $fa . " ";
        }
        $com .= "$files | grep XXXXXXXXXXX";
        $retval = $this->syscall($this->getCMDs($com));
        if (preg_match('/No such file or directory/', $retval)) {
            return "{\"ERROR\": \"".trim($retval)."\"}";
        }else if (preg_match('/Permission denied/', $retval)) {
            return "{\"ERROR\": \"".trim($retval)."\"}";
        }else if (preg_match('/usage:/', $retval)){
            return "{\"ERROR\": \"Your user account is not within the GHPCC cluster.  Please contact an admin to be added to the cluster.\"}";
        }else if (preg_match(('/Your password has expired./'), $retval)){
            return "{\"ERROR\": \"Cluster account password has expired.\"}";
        }else if (preg_match('/password:/', $retval)){
            return "{\"ERROR\": \"Dolphin cannot access your cluster account.  Please log into the GHPCC cluster and run this script: /project/umw_biocore/bin/addKey.bash\"}";
        }
        $com2 = "ls -lshd $files | awk '{print \\\$2}' | grep d";
        $retval = $this->syscall($this->getCMDs($com2));
        var_dump($retval);
        if(preg_match("/d/", $retval)){
            return "{\"ERROR\":\"Cannot use a directory as a specified file.  Please check your file input for inconsistencies\"}";
        }
        return "{\"Result\":\"Ok\"}";
    }
    function checkPermissions($params)
    {
        $this->username=$params['username'];
        $this->readINI();
        if (isset($params['outdir'])){
          $com = "mkdir -p ".$params['outdir'].";cd ".$params['outdir'].";touch permstest.txt;rm permstest.txt";
        }else{
          $com = "ls";
        }
        $retval = $this->syscall($this->getCMDs($com));
        if (preg_match('/Permission denied/', $retval)) {
             return "{\"ERROR\": \"Permission denied: ".$params['outdir']."\"}";
        }else if (preg_match('/cannot create directory/', $retval)) {
             return "{\"ERROR\": \"Permission denied: ".$params['outdir']."\"}";
        }else if (preg_match('/usage:/', $retval)){
            return "{\"ERROR\": \"Your user account is not within the GHPCC cluster.  Please contact an admin to be added to the cluster.\"}";
        }else if (preg_match(('/Your password has expired./'), $retval)){
            return "{\"ERROR\": \"Cluster account password has expired.\"}";
        }else if (preg_match('/password:/', $retval)){
            return "{\"ERROR\": \"Dolphin cannot access your cluster account.  Please log into the GHPCC cluster and run this script: /project/umw_biocore/bin/addKey.bash\"}";
        }
        return "{\"Result\":\"Ok\"}";
    }
    function checkNewRun($params){
        $directory = $params['outdir'];
        $this->readINI();
        $sql = "SELECT id FROM ngs_runparams WHERE outdir = '$directory/initial_run' OR outdir = '$directory//initial_run' OR outdir = '".$directory."initial_run' OR outdir = '$directory' OR outdir = '$directory/'";
        $res=$this->queryTable($sql);
        if (count($res) > 0){
            return "{\"ERROR\": \"Run already exists: ".$params['outdir']."\"}";
        }
        return "{\"Result\":\"Ok\"}";
    }
    function directoryContents($params){
        $this->username=$params['username'];
        $this->readINI();
        if (isset($params['directory'])){
          $com = 'ls -1 '.$params['directory'];
        }else{
          $com = 'ls -1';
        }
        $retval = $this->syscall($this->getCMDs($com));
        return $retval;
    }
    function removeSuccessFile($run_id, $dir, $file, $clusteruser)
    {
        $this->username=$clusteruser;
        $this->readINI();
        if($file != ""){
            $com = "rm -rf $dir/tmp/track/$file*";
            $removal = $this->syscall($this->getCMDs($com));
            $logging = $this->sendLog($run_id, $removal, $file . " removal message: ");
            return $logging;
        }else{
            return "File given is an empty string";
        }
    }
    function removeAllSampleSuccessFiles($dir, $samplenames, $clusteruser){
        $this->username=$clusteruser;
        $this->readINI();
        $com = "";
        foreach ($samplenames as $sample){
            $com.= "rm -rf $dir/tmp/track/*$sample.*.success && ";
        }
        $com.= "echo \"end $dir\"";
        $removal = $this->syscall($this->getCMDs($com));
        return $removal;
    }
    function getKey()
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $wkey       = "";
        $ret        = "";
        for ($i = 0; $i < 30; $i++) {
            $wkey .= $characters[rand(0, strlen($characters) - 1)];
        }
        # If this random key exist it randomize another key
        if ($this->getWorkflowId($wkey))
            $ret = $this->getKey();
        else
            $ret = $wkey;
        return $ret;
    }
    function runSQL($sql)
    {
        sleep(1);
        $this->readINI();
        $link = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->db);
        // check connection
        if (mysqli_connect_errno()) {
            exit('Connect failed: ' . mysqli_connect_error());
        }
        $i = 0;
        while ($i < 3) {
            $result = $link->query($sql);
            
            if ($result) {
                $link->close();
                return $result;
            }
            sleep(5 * ($i + 1));
            $i++;
        }
        $link->close();
        return $sql;
    }
    function queryAVal($sql)
    {
        $res = $this->runSQL($sql);
        
        $num_rows = $res->num_rows;
        
        if (is_object($res) && $num_rows > 0) {
            $row = $res->fetch_array();
            return $row[0];
        }
        return "0";
    }
    
    function queryTable($sql)
    {
        $data = array();
        if ($res = $this->runSQL($sql)) {
            while (($row = $res->fetch_assoc())) {
                $data[] = $row;
            }
            $res->close();
        }
        return $data;
    }
    
    function syscall($command)
    {
        $result = "";
        if ($proc = popen("($command)2>&1", "r")) {
            while (!feof($proc))
                $result .= fgets($proc, 1000);
            pclose($proc);
            return $result;
        } else {
            return "ERROR 104: Cannot run $command!";
        }
    }
    function sysback($command)
    {
        Proc_Close (Proc_Open ("($command)2>&1 &", Array (), $foo));
    }
    function getSSH()
    {
       sleep(1);
       return "ssh -o ConnectTimeout=30  ". $this->username. "@" . $this->remotehost . " ";
    }

    function isJobRunning($wkey, $job_num, $username)
    {
        $this->job_num = $job_num; 
        $this->username = $username;
        $this->readINI();
        $retval = $this->syscall($this->checkjob_cmd);
        
        if ($retval == "") {
            $ret = $this->checkJobInDB($wkey, $job_num, $username);
            if ($ret == 0) {
                $retval = "EXIT";
            } else {
                $retval = "DONE";
            }
        }
        return $retval;
    }


    function updateStartTime($wkey)
    {
        $sql = "update jobs set start_time=submit_time where wkey='$wkey' and start_time=0 ";
        $this->runSQL($sql);
    }


    function checkStartTime($wkey, $job_num,$username)
    {
        $sql = "update jobs set start_time=now() where wkey='$wkey' and job_num='$job_num'  and start_time=0 and username='$username'";
        $this->runSQL($sql);
    }
    function checkJobInDB($wkey, $job_num, $username)
    {
        #sleep(5);
        $sql      = "select * from jobs j where wkey='$wkey' and job_num='$job_num' and result=3 and username='$username'";
        $res      = $this->runSQL($sql);
        $num_rows = $res->num_rows;
        #Check if there are jobs which are failed or running
        if ($num_rows > 0) {
            return "Job Finsihed Sucessfully!!!";
        }
        return 0;
    }

    function rerunJob($servicename, $jobname, $jobnum, $wkey)
    {
       $sql="select count(wkey) count, run_script from jobs where jobname='$jobname' and wkey = '$wkey' group by run_script";

       $res=$this->queryTable($sql);
       
       if (isset($res[0]))
       {
         $count=$res[0]['count'];
         $run_script=$res[0]['run_script'];
         if ($count < 4) 
         {
            $com = $this->getCMDs( $this->python . " " . $run_script); 
            $retval = $this->sysback($com);
            return 1;
         }
       }
       return 0;
    }
 
    function checkStatus($params)
    {
        $servicename = $params['servicename'];
        $wkey        = $params['wkey'];
        $sql      = "select DISTINCT j.service_id from jobs j, services s where s.service_id=j.service_id and j.jobname='$servicename' and j.wkey='$wkey'";
        #return $sql;
        $service_id   = $this->queryAVal($sql);
        #sleep(1); 
        if ($service_id > 0) {
            $sql        = "select DISTINCT j.job_num job_num, j.jobname jobname, j.result jresult, s.username username from jobs j, services s where s.service_id=j.service_id and s.servicename='$servicename' and wkey='$wkey' and result<3 and jobstatus=1";
            $res      = $this->runSQL($sql);
            $num_rows = $res->num_rows;
            
            #Check if there are jobs which are failed or running
            if (is_object($res) && $num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    # If job is running, it turns 1 otherwise 0 and it needs to be restarted
                    # If it doesn't turn Error and if job is working it turns wkey to che
                    $retval = $this->isJobRunning($wkey, $row['job_num'], $row['username']);
                    $this->checkStartTime($wkey, $row['job_num'], $row['username']);

                    if (preg_match('/^EXIT/', $retval)) {
                      if (!$sqlstr=$this->rerunJob( $servicename, $row['jobname'], $row['job_num'], $wkey ) )
                      {
                        $sql    = "SELECT j.jobname, jo.jobout FROM jobs j, jobsout jo where j.wkey=jo.wkey and j.job_num=jo.jobnum and j.job_num=" . $row['job_num'] . " and jo.wkey='$wkey' and jobstatus=1";
                        $resout = $this->runSQL($sql);
                        $rowout = $resout->fetch_assoc();
                        require_once('class.html2text.inc');
                        
                        $h2t = new html2text($rowout['jobout']);
                        $jobout = $h2t->get_text();
                        return 'ERROR:' . $retval . "\n" . $rowout['jobname'] . " Failed\nCheck LSF output\n" . $jobout;
                      }
                    }
                    if (preg_match('/DONE/', $retval)) {
                        $jn     = rtrim(substr($retval, 5));
                        $sql    = "select * from jobs where result=3 and job_num='" . $jn . "' and wkey='$wkey' and jobstatus=1";
                        $result = $this->runSQL($sql);
                        if (is_object($result)) {
                            $sql    = "UPDATE jobs set result='3', end_time=now() where job_num='" . $jn . "' and wkey='$wkey'";
                            $result = $this->runSQL($sql);
                        } 
                    }
                }
            } else {
                 if ($this->checkAllJobsFinished($params)){
                    $this->updateStartTime($wkey);
                    return "DONE: Service ended successfully ($servicename)!!!";
                 }
            }
            return "RUNNING(1):[retval=$retval]:SERVICENAME:$servicename";
        }
        return 'START';
    }
    
    function getServiceOrder($workflow_id, $service_id, $wkey)
    {
        $sql    = "select service_order from workflow_services where workflow_id=$workflow_id and service_id=$service_id";
        $result = $this->runSQL($sql);
        if (is_object($result) && $row = $result->fetch_row())
            return -1;
        
        $sql    = "select max(service_order) from workflow_services w, workflow_run wr where wr.workflow_id=$workflow_id and wr.workflow_id=w.workflow_id and wr.wkey='$wkey'";
        $result = $this->runSQL($sql);
        if (is_object($result) && $row = $result->fetch_row()) {
            $id = $row[0] + 1;
        } else {
            $id = 1;
        }
        
        return ($id);
    }
    
    function getWorkflowId($wkey)
    {
        $sql    = "select workflow_id from workflow_run where wkey='$wkey'";
        $result = $this->runSQL($sql);
        if (is_object($result) && $row = $result->fetch_row()) {
            $id = $row[0];
        } else {
            return 0;
        }
        return $id;
    }
    
    function getId($name, $username, $val, $wkey, $defaultparam)
    {
        $sql    = "select " . $name . "_id from " . $name . "s where `" . $name . "name`='$val' and username='$username'";
        $result = $this->runSQL($sql);
        if (is_object($result) && $row = $result->fetch_row()) {
            $id = $row[0];
            if ($name == "workflow") {
                $sql = "update " . $name . "_id from " . $name . "s where `" . $name . "name`='$val' and username='$username'";
            }
        } else {
            #If workflow and service doesn't exist. This registers those workflows automatically. 
            
            $sql    = "insert into " . $name . "s(`" . $name . "name`, `description`, `username`, `defaultparam`) values('" . $val . "', 'Service description', '$username', '$defaultparam')";
            $result = $this->runSQL($sql);
            $id     = $this->getId($name, $username, $val, $wkey, $defaultparam);
        }
        
        if ($name == "service") {
            $workflow_id   = $this->getWorkflowId($wkey);
            $service_order = $this->getServiceOrder($workflow_id, $id, $wkey);
            if ($service_order > 0 && $workflow_id > 0) {
                $sql    = "insert into workflow_services(`workflow_id`, `service_id`, `service_order`) values($workflow_id,$id, $service_order)";
                $result = $this->runSQL($sql);
            }
        }
        return $id;
    }
    
    function getWorkflowInformation($wkey)
    {
        $sql    = "select wr.username, wr.inputparam, wr.outdir, w.defaultparam from workflow_run wr, workflows w  where w.workflow_id=wr.workflow_id and wr.wkey='$wkey'";
        $result = $this->runSQL($sql);
        if (is_object($result) && $row = $result->fetch_row()) {
            return $row;
        }
        return "ERROR 001: in getWorkflowInformation:$sql";
    }
    function updateInputParam($wkey, $username, $inputparam)
    {
        $sql = "select inputparam from workflow_run where wkey='$wkey' and username='$username'";
        
        $result = $this->runSQL($sql);
        if (is_object($result) && $row = $result->fetch_row()) {
            
            if ($inputparam != $row[0]) {
                
                $sql    = "update workflow_run set inputparam='" . $inputparam . "' where wkey='$wkey' and username='$username'";
                $result = $this->runSQL($sql);
            }
            return $inputparam;
        }
        return "ERROR 002: in getWorkflowInput $sql";
    }
    
    function updateDefaultParam($workflowname, $username, $defaultparam)
    {
        
        $sql    = "update workflows set defaultparam='" . $defaultparam . "' where username='$username' and workflowname='$workflowname'";
        $result = $this->runSQL($sql);
        
    }
    
    function getCommand($servicename, $username, $inputcommand, $defaultparam)
    {
        $sql = "select command, defaultparam from services where servicename='$servicename' and username='$username'";
        
        $result = $this->runSQL($sql);
        if (is_object($result) && $row = $result->fetch_row()) {
            if ($inputcommand != $row[0] || $defaultparam != $row[1]) {
                $sql    = "update services set command='" . $inputcommand . "', defaultparam='" . $defaultparam . "' where servicename='$servicename' and username='$username'";
                $result = $this->runSQL($sql);
            }
            return $inputcommand;
        }
        return "ERROR 003: in getServiceCommand";
    }
   
    function deleteLastServiceJobs($wkey)
    {
        $sql="DELETE FROM jobs where wkey='$wkey' and service_id = (SELECT service_id FROM service_run where service_run_id = (select max(service_run_id) FROM service_run where wkey='$wkey'))";
        $result = $this->runSQL($sql);
    } 
 
    function startWorkflow( $params )
    {
        $inputparam=$params['inputparam'];
        $defaultparam=$params['defaultparam'];
        $workflowname=$params['workflow'];
        $username=$params['username'];
        $status =$params['status'];
        $outdir = $params['outdir'];
        $services= $params['services'];

        $status = "exist";
        $wkey=$params['wkey'];
        if($wkey=='' || $wkey=='start')
        {
            $wkey = $this->getKey();
            $status = "new";
        }

        if ($status == "new") {
            $workflow_id = $this->getId("workflow", $username, $workflowname, $wkey, $defaultparam);
            // sql query for INSERT INTO workflowrun
            $sql = "INSERT INTO `workflow_run` ( `workflow_id`, `username`, `wkey`, `inputparam`, `outdir`, `result`, `start_time`, `services`) VALUES ('$workflow_id', '$username', '$wkey', '$inputparam', '$outdir', '0', now(), $services)";
            if ($workflowname != "")
            {
              $this->updateDefaultParam($workflowname, $username, $defaultparam);
            }
            if ($result = $this->runSQL($sql)) {
                $ret = $result;
            }
        } else {
            $inputparam = $this->updateInputParam($wkey, $username, $inputparam);
            $ret =  $inputparam;
            $this->deleteLastServiceJobs($wkey);
        }
        if(preg_match('/^ERROR/', $ret))
        {
           return $ret;
        }
        return $wkey;
    }
    
    function startService($params)
    {
     $this->readINI();
     $servicename  = $params['servicename'];
     $wkey         = $params['wkey'];
     $inputcommand = $params['command'];
     

     $result_stat = $this->checkStatus($params);
     if ( preg_match('/START/', $result_stat)) # Job hasn't started yet 
     {
        #The service will start. Get general workflow information to start the service
        $wf = $this->getWorkflowInformation($wkey);
        if (is_array($wf)) {
            $username     = $wf[0];
            $inputparam   = $wf[1];
            $outdir       = $wf[2];
            $defaultparam = $wf[3];
            $this->username = $username;
            #Get service ID and check if that service started before or not.
            $service_id = $this->getId("service", $username, $servicename, $wkey, $defaultparam);
            $sql = "SELECT service_id FROM service_run where wkey='$wkey' and service_id='$service_id';";
            $s_id = $this->queryAVal($sql);
            #If service hasn't started before, add the service info to service_run table.
           
             if ($s_id==0) {
                // sql query for INSERT INTO service_run
                $sql = "INSERT INTO `service_run` (`service_id`, `wkey`, `input`,`result`, `start_time`) VALUES ('$service_id', '$wkey', '', '0', now())";
                $this->runSQL($sql); 
             }
             
             $sql = "SELECT max(job_id) FROM jobs where wkey='$wkey' and service_id='$service_id' and (result=1 or result=2);";
             #If a job is still running for this service, the system won't start this service until all the jobs are finished or killed
             $ajobisrunning = $this->queryAVal($sql);
             
             if ($ajobisrunning==0) {
                $command = $this->getCommand($servicename, $username, $inputcommand, $defaultparam);
                $ipf = "";
                if ($inputparam != "" && $inputparam != "None") 
                    $ipf = "-i \"$inputparam\"";
                $dpf = "";
                if ($defaultparam != "" && $defaultparam != "None")
                    $dpf = "-p $defaultparam";
                    
                $edir = $this->tool_path;
                $command=str_replace("\"", "\\\"", $command);
                if($this->schedular == "LSF" || $this->schedular == "SGE")
                {
                   $command=str_replace("\\\"", "\\\\\"", $command);
                }
                $com = $this->python . " " . $edir . "/runService.py -f ".$this->config." $ipf $dpf -o $outdir -u $username -k $wkey -c \"$command\" -n $servicename -s $servicename";
                $com=$this->getCMDs($com);
                $retval = $this->sysback($com);
             }
             if (preg_match('/Error/', $retval)) {
                 return "ERROR: $retval: : $com";
             }
             return "RUNNING(2):$inputcommand:$com";
        } else {
             return $wf;
        }
      }
      return $result_stat;
    }
    
    function checkLastServiceJobs($wkey)
    {
        $sql    = "SELECT username, job_num from jobs where service_id=(SELECT service_id FROM service_run where wkey='$wkey' order by service_run_id desc limit 1)  and wkey='$wkey';";
        $result = $this->runSQL($sql);
        #Get how many jobs hasn't finished
        $ret    = 1;
        return $ret;
        if (is_object($result)) {
            while ($row = $result->fetch_row()) {
                $username = $row[0];
                $jobnum   = $row[1];
                $retval   = $this->isJobRunning($wkey, $jobnum, $username);
                if (preg_match('/^EXIT/', $retval)) {
                    $ret = 0;
                }
            }
        }
        return $ret;
    }
    function endWorkflow($params)
    {
        $wkey=$params['wkey'];
        $sql    = "update workflow_run set result='1', end_time=now() where wkey='$wkey'";
        $result = $this->runSQL($sql);
        $sql    = "update ngs_runparams set run_status='1' where wkey='$wkey'";
        $result = $this->runSQL($sql);
        return "Success!!!";
    }
    
    #Insert a job to the database
    function insertJob($params)
    {
        $username=$params['username']; 
        $wkey=$params['wkey'];
        $com=$params['com'];
        $jobname=$params['jobname'];
        $servicename=$params['servicename']; 
        $jobnum=$params['jobnum'];
        $result=$params['result'];
        $resources=$params['resources'];
 
        $workflow_id = $this->getWorkflowId($wkey);
        $service_id  = $this->getId("service", $username, $servicename, $wkey, "");
        $sql="select job_id from jobs where `wkey`='$wkey' and `jobname`='$jobname' and jobstatus=1";
        $previous_jobs = $this->queryTable($sql);

        $job_ids="";
        foreach($previous_jobs as $job){
           $job_ids.=$job['job_id'].",";
        }
        $job_ids=rtrim($job_ids, ",");
        $sql = "insert into jobs(`username`, `wkey`, `run_script`, `jobname`, `workflow_id`, `service_id`, `resources`, `result`, `submit_time`, `job_num`) values ('$username','$wkey','$com','$jobname','$workflow_id','$service_id', '$resources', '$result', now(), '$jobnum')";

        $res = $this->runSQL($sql);
        if ($res && $job_ids!="")
        {
            $sql="update jobs set jobstatus=0 where job_id in ($job_ids)";
            $res = $this->runSQL($sql);
        }
        
        return $res;
    }
    
    #Update a job to the database
    function updateJob($params)
    {
        $username=$params['username']; 
        $wkey=$params['wkey'];
        $jobname=$params['jobname'];
        $servicename=$params['servicename']; 
        $field=$params['field']; 
        $jobnum=$params['jobnum'];
        $result=$params['result'];
        $workflow_id = $this->getWorkflowId($wkey);
        $service_id  = $this->getId("service", $username, $servicename, $wkey, "");
        if ($field=="end_time")
        {
               $this->checkStartTime($wkey, $jobnum, $username);
        }
        
        $sql = "update jobs set `$field`=now(), `result`='$result' where `wkey`='$wkey' and `job_num`='$jobnum'";
        
        $res = $this->runSQL($sql);
        return $res;
    }
    
    #Check if all jobs are finished or not for a service
    function checkAllJobsFinished($params)
    {
        $username=$params['username']; 
        $wkey=$params['wkey'];
        $servicename=$params['servicename']; 
    
        $workflow_id = $this->getWorkflowId($wkey);
        $service_id  = $this->getId("service", $username, $servicename, $wkey, "");
        $select      = "select count(job_id) c from jobs ";
        $where1      = " where `username`= '$username' and `wkey`='$wkey' and `workflow_id`='$workflow_id' and `service_id`='$service_id' and `jobstatus`=1";
        $where2      = " and `result`=3";
        $sql         = "select s1.c, s2.c from ( $select  $where1) s1,  ($select  $where1 $where2) s2";
        $result      = $this->runSQL($sql);
        #Get how many service successfuly finished
        if (is_object($result) && $row = $result->fetch_row()) {
            $s1 = $row[0];
            $s2 = $row[1];
            
            if ($s1 == $s2) {
                $res = $this->updateService($wkey, $service_id, 1);
                $res=1;
            } else {
                $res = "Should be still running 1 [$s1:$s2]\n";
                $res=0;           
            }
        } else {
            $res = "Should be still running 2 \n ";
            $res=0;
        }
        return $res;
    }
    function updateService($wkey, $service_id, $result)
    {
        $sql = "update service_run set `end_time`=now(), `result`='$result' where `wkey`='$wkey' and `service_id`='$service_id' and end_time=0";
        $res = $this->runSQL($sql);
        
        return $res;
    }
    #Insert a job output to the database
    function insertJobOut($params)
    {
        $username=$params['username']; 
        $wkey=$params['wkey'];
        $jobnum=$params['jobnum']; 
        $jobout=$params['jobout']; 
        
        $sql = "insert into jobsout(`username`, `wkey`, `jobnum`, `jobout`) values ('$username','$wkey','$jobnum','$jobout')";
        $res = $this->runSQL($sql);
        
        return $res;
    }
    
    #Insert a job output to the database
    function insertJobStats($params)
    {
        $username=$params['username']; 
        $wkey=$params['wkey'];
        $jobnum=$params['jobnum']; 
        $stats=$params['stats']; 
        $stats = json_decode($stats, true);
        
        $sql = "select id from jobstats where wkey='$wkey' and jobnum='$jobnum' and username='$username'";
        $res = $this->queryAVal($sql);
        if ($res > 0) {
            $sql = "update jobstats set `cputime`=" . $stats['CPU time'] . ", `maxmemory`=" . $stats['Max Memory'] . ",
                 `averagememory`=" . $stats['Average Memory'] . ",`totalrequested`=" . $stats['Total Requested Memory'] . ", `deltamemory`=" . $stats['Delta Memory'] . ",
                 `maxprocess`=" . $stats['Max Processes'] . ", `maxthreads`=" . $stats['Max Threads'] . ", `date_created` = now()
                 where wkey='$wkey' and jobnum='$jobnum' and username='$username'";
        } else {
            $sql = "insert into jobstats(`username`, `wkey`, `jobnum`, `cputime`, `maxmemory`, `averagememory`,`totalrequested`, `deltamemory`, `maxprocess`, `maxthreads`, `date_created`)
                         values ('$username','$wkey','$jobnum', " . $stats['CPU time'] . ", " . $stats['Max Memory'] . ", " . $stats['Average Memory'] . "," . $stats['Total Requested Memory'] . ", " . $stats['Delta Memory'] . ", " . $stats['Max Processes'] . ", " . $stats['Max Threads'] . ", now() )";
        }
        $res = $this->runSQL($sql);
        
        return $res;
    }
    #get job numbers
    function getJobNums($params)
    {
        $wkey=$params['wkey'];
        $sql = "select job_num from jobs where wkey='$wkey'";
        return $this->queryTable($sql);
    }

    /** updates run params table sets the status to 2   
     *
     * @return string Response
     */
     function updateRunParams($params)
     {
         $wkey        = $params['wkey'];
         $runparamsid = $params['runparamsid'];
         $res=0;
         if ($runparamsid>0)
         {
           $sql = "UPDATE ngs_runparams set run_status=2, wkey='$wkey' where id=$runparamsid";
           $res = $this->runSQL($sql);
         }

         return $res;
     }

    /** inserts report table to db                   
     *
     * @return string Response
     */
     function insertReportTable($params)
     {
         $wkey=$params['wkey'];
         $version= $params['version']; 
         $type=$params['type'];
         $file=$params['file'];

         $sql = "select id from report_list where wkey='$wkey' and file='$file'";
         $res = $this->queryAVal($sql);
         if ($res == 0) {
            $sql = "INSERT INTO report_list(wkey, version, type, file) VALUES ('$wkey', '$version', '$type', '$file')";
            $res = $this->runSQL($sql);
         } else {
            $res=1;
         }
         return $res;
     }
     
     function checkJob($params)
     {
          $jobname=$params['jobname'];
          $wkey=$params['wkey'];
         
          $result="DONE"; 
          $sql = "select job_num, result, username from jobs where wkey='$wkey' and jobname='$jobname' and jobstatus=1";
          $res = $this->queryTable($sql); 
          if (isset($res[0]))
          {
            $jobnum=$res[0]['job_num'];
            $jobres=$res[0]['result'];
            $username=$res[0]['username'];
            
            if($jobres<3)
            {
              $retval   = $this->isJobRunning($wkey, $jobnum, $username);
              #$result=$retval;
              if (preg_match('/^EXIT/', $retval)) {
                 $result="START";
              }
            }
          }
          else
          {
             $result="START";
          }

          $result = '{"Result":"'.$result.'"}'; 
          return $result;
     
     }

      /** getJob Parameters for a submission
      *
      * @return string Response
      */

      function getJobParams($params)
      { 
          $servicename=$params['servicename'];
          $name=$params['name'];
          $wkey=$params['wkey']; 
          $libname=preg_replace("/".$servicename."/", "", $name);
          $predvals = $this->getPredVals($libname, $servicename);
             
          #$res = '{"'.$servicename.'":"'.$libname.':'.$wkey.'"}'; 
          $res = '{"'.$predvals.'"}'; 
          return $res;
      }
      private function getPredVals($libname, $servicename) 
      {
         
          $predvals = $servicename.'":"'.$libname; 
          $totalreads=0;
          if ($servicename == "stepCheck")
          {
	     $sql="SELECT DISTINCT total_reads FROM ngs_temp_sample_files where file_name like '%$libname%';";
             $totalreads = $this->queryAVal($sql);
             if ($totalreads==0)
             {
	       $sql="SELECT DISTINCT total_reads FROM ngs_temp_lane_files where file_name like '%$libname%';";
               $totalreads = $this->queryAVal($sql);
             }
          }
          else
          {
             $sql="SELECT DISTINCT total_reads FROM ngs_fastq_files nff, ngs_samples ns where ns.id=nff.sample_id and ns.samplename='$libname';";
             $totalreads = $this->queryAVal($sql);
          }
          $sql="SELECT field2, floor(a + abs(x)*$totalreads) val  from predjob p where p.set='$servicename'";
          $res=$this->queryTable($sql); 
          if (isset($res) && isset($res[0]) && isset($res[1]))
          {
            return join($res[0], "\":\"")."\", \"".join($res[1], "\":\"");
          }
          return "cputime\":\"240\",\"maxmemory\":\"4096";
      }
      
      //For stepBackupS3 ######################
      function getSampleList($params)
      {
          $runparamsid=$params['runparamsid'];
          $barcode=$params['barcode'];

          if (strtolower($barcode) != "none")
          {
             $sql = "SELECT DISTINCT ns.id sample_id, sf.id file_id, d.id dir_id, ns.lane_id, ns.samplename, sf.file_name, d.fastq_dir, d.backup_dir, d.amazon_bucket, ns.owner_id, ns.group_id, ns.perms FROM ngs_runlist nr, ngs_samples ns, ngs_temp_lane_files sf, ngs_dirs d where sf.lane_id=ns.lane_id and d.id=sf.dir_id and ns.id=nr.sample_id and nr.run_id='$runparamsid'";
          }
          else
          {
             $sql = "SELECT DISTINCT ns.id sample_id, sf.id file_id, d.id dir_id, ns.lane_id, ns.samplename, sf.file_name, d.fastq_dir, d.backup_dir, d.amazon_bucket, ns.owner_id, ns.group_id, ns.perms FROM ngs_runlist nr, ngs_samples ns, ngs_temp_sample_files sf, ngs_dirs d where sf.sample_id=ns.id and d.id=sf.dir_id and ns.id=nr.sample_id and nr.run_id='$runparamsid'";
          }

          return $this->queryTable($sql);
      }
      
      function getAmazonCredentials($params)
      {
         $username=$params['username'];
         $sql = "SELECT DISTINCT ac.* FROM amazon_credentials ac, group_amazon ga, users u where ac.id=ga.amazon_id and ga.group_id=u.group_id and (u.clusteruser='$username' or u.username='$username')";
         return $this->queryTable($sql);
      }
      function updateInitialFileCounts($params)
      {
        $tablename=$params['tablename'];
        $total_reads=$params['total_reads'];
        $file_id=$params['file_id'];
        $res=0;
        if ($file_id>0)
        {
           $sql = "UPDATE $tablename set total_reads=$total_reads where id=$file_id";
           $res = $this->runSQL($sql);
        }

         return $res;        
      }
      
      function getFastqFileId($params)
      {
         $sample_id=$params['sample_id'];
         $res=0;
         if ($sample_id>0)
         {
           $sql="select sample_id from ngs_fastq_files where sample_id=$sample_id";
           $res = $this->queryTable($sql);
         }
         return $res;  
       }
       
       function upadateFastqFile($params)
       {
         $sample_id=$params['sample_id'];
         $md5sum=$params['md5sum'];
         $total_reads=$params['total_reads'];
         $owner_id=$params['owner_id'];

         $res=0;
         if ($sample_id>0)
         {
            $sql="update ngs_fastq_files set checksum='$md5sum', original_checksum='$md5sum', total_reads=$total_reads, date_modified=now(), last_modified_user=$owner_id where sample_id=$sample_id ";
            $res = $this->runSQL($sql);
         }
         return $res;
       }
       
      function insertFastqFile($params)
      {
         $filename=$params['filename'];
         $total_reads=$params['total_reads'];
         $checksum=$params['checksum'];
         $sample_id=$params['sample_id'];
         $lane_id=$params['lane_id'];
         $dir_id=$params['dir_id'];  
         $owner_id=$params['owner_id'];
         $group_id=$params['group_id'];
         $perms=$params['perms'];
         
         $res=0;
         if ($sample_id>0)
         {
            $sql="INSERT INTO ngs_fastq_files ( `file_name`, `total_reads`, `checksum`, `original_checksum`, `sample_id`, `lane_id`,`dir_id`,`owner_id`, `group_id`,`perms`,`date_created`,`date_modified`,`last_modified_user`) VALUES('$filename', '$total_reads','$checksum', '$checksum', '$sample_id','$lane_id','$dir_id','$owner_id', '$group_id', '$perms', now(), now(), '$owner_id')";
            $res = $this->runSQL($sql);
         }
         return $res; 
         
      }
      
      function checkReadCounts($params)
      {
        $sample_id=$params['sample_id'];
        $tablename=$params['tablename'];
        
        $sql="SELECT sum(total_reads) FROM $tablename where sample_id=$sample_id";
        $temp_count=$this->queryAVal($sql);

        $sql="SELECT total_reads FROM ngs_fastq_files where sample_id=$sample_id";
        $merged_count=$this->queryAVal($sql);
        
        $res=0;
        if ($temp_count==$merged_count)
        {
           $res=1;
        }
        
        return "{'Result':$res, 'temp_count':$temp_count, 'merged_count':$merged_count}";
      }

      function getSelectedSampleList($params)
      {
      
        $runparamsid=$params['sampleids'];

        $sql = "SELECT DISTINCT ns.id sample_id, sf.id file_id, d.id dir_id, ns.lane_id, ns.samplename, sf.file_name, d.fastq_dir, d.backup_dir, d.amazon_bucket, ns.owner_id, ns.group_id, ns.perms FROM ngs_runlist nr, ngs_samples ns, ngs_fastq_files sf, ngs_dirs d where sf.sample_id=ns.id and d.id=sf.dir_id and ns.id=nr.sample_id and ns.id in ($runparamsid)";

        return $this->queryTable($sql); 
      }

      //#######################################

}
?>
