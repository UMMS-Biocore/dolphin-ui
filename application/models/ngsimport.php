<?php
class Ngsimport extends VanillaModel {
	//	Variable Storage
	public $series_id;
	public $sid;
	public $gid;
	public $uid;
	public $username;
	private $worksheet;
	private $sheetData;
	public $fastq_dir;
	public $backup_dir;
	public $amazon_bucket;
	public $samples = array();
	public $sample_ids = array();
	
	public $organismCheck;
	public $pairedEndCheck;
	public $laneArrayCheck;
	public $barcode;
	public $namesList;
	public $laneList;
	public $initialSubmission = array();
	
	//	Variable Classes
	//	METADATA
	public $experiment_name;
	public $summary;
	public $design;
	public $conts = array();
	
	//	LANES
	public $lane_arr = array();
	public $lane_ids = array();
	
	//	PROTOCOLS
	public $prot_arr = array();
	
	//	SAMPLES
	public $sample_arr = array();
	public $char_arr = array();
	
	//	DIRECTORIES
	public $dir_arr = array();
	public $dir_tags = array();
	public $dir_fastq = array();
	public $dir_ids = array();
	
	//	FILES
	public $file_arr = array();
	public $file_name_arr = array();
	
	//	Sheet Check bools
	public $final_check;
	
	//	Cluster name
	public $clustername;
	
	/*
	* num2alpha
	*
	* Converts a number to an alphabetical representation used in excel
	*
	* @param int $n number to convert
	* @return string
	*/
	function num2alpha($n){
		for($r = ""; $n >= 0; $n = intval($n / 26) - 1){
			$r = chr($n%26 + 0x41) . $r;
		}
		return $r;
	}
	
	/*
	* columnNumber
	*
	* Converts a string column letter into 
	*
	* @param int $n number to convert
	* @return string
	*/
	function columnNumber($col){
		$col = str_pad($col,2,'0',STR_PAD_LEFT);
		$i = ($col{0} == '0') ? 0 : (ord($col{0}) - 64) * 26;
		$i += ord($col{1}) - 64;
		return $i;
	}
	
	function getGroup($username) {
        $groups = json_decode($this->query("select g.id from user_group ug, users u, groups g where ug.u_id=u.id and ug.g_id=g.id and username='$username'"), true);
        $group_str='';
        foreach ($groups as $group):
            $group_str.=$group['id'].",";
        endforeach;
        return rtrim($group_str, ",");
    }
	
	/*
	* directoryCheck
	*
	* Makes sure a given directory is of the right format
	*
	* @param string $directory directory string to check
	* @return string
	*/
	function directoryCheck($directory){
		if(substr($directory, 0, 1) != '/' && strpos($directory, '/') > -1){
 			$directory = "/" . $directory;
 		}
		if(substr($directory, strlen($directory)-1, strlen($directory)) != '/' && strpos($directory, '/') > -1){
 			$directory = $directory . "/";
 		}
		return $directory;
	}
	
	/*
	* createSampleName
	*
	* Returns a specifically formatted samplename based on donor, source symbol, antibody target, condition symbol, time, and replicas
	* Otherwise just returns the samples given name
	*
	* @param sample $sample sample object containing the correct information
	* @return string
	*/
	function createSampleName($sample){
		$samplename = '';
		$underscore_mark = true;
		//	Donor
		if(isset($sample->donor)){
			if($sample->donor != NULL && $sample->donor != '' && $sample->donor != null && $sample->donor != 'null'){
				$samplename.= $sample->donor;
				if($underscore_mark){
					$underscore_mark = false;
				}
			}
		}
		//	Source
		if(isset($sample->source_symbol)){
			if($sample->source_symbol != NULL && $sample->source_symbol != '' && $sample->source_symbol != null && $sample->source_symbol != 'null'){
				if(!$underscore_mark){
					$samplename.="_";
				}
				$samplename.= $sample->source_symbol;
				if($underscore_mark){
					$underscore_mark = false;
				}
			}
		}
		//	Antibody Target
		if(isset($sample->target)){
			if($sample->target != NULL && $sample->target != '' && $sample->target != null && $sample->target != 'null'){
				if(!$underscore_mark){
					$samplename.="_";
				}
				$samplename.= $sample->target;
				if($underscore_mark){
					$underscore_mark = false;
				}
			}
		}
		//	Conditions
		if(isset($sample->condition_symbol)){
			if($sample->condition_symbol != NULL && $sample->condition_symbol != '' && $sample->condition_symbol != null && $sample->condition_symbol != 'null'){
				if(!$underscore_mark){
					$samplename.="_";
				}
				$conds = explode(",", $sample->condition_symbol);
				foreach($conds as $c){
					$samplename.= strtoupper(substr(trim($c), 0, 1)) . strtolower(substr(trim($c), 1, strlen($c)));
				}
				if($underscore_mark){
					$underscore_mark = false;
				}
			}
		}
		//	Time
		if(isset($sample->time)){
			if($sample->time != NULL && $sample->time != '' && $sample->time != null && $sample->time != 'null'){
				if(!$underscore_mark){
					$samplename.="_";
				}
				if($sample->time < 60 && $sample->time > 0){
					$samplename.= $sample->time."m";
				}else{
					$samplename.= floor($sample->time/60)."h";
				}
				if($underscore_mark){
					$underscore_mark = false;
				}
			}
		}
		//	Biological Replicas
		if(isset($sample->biological_replica)){
			if(is_numeric($sample->biological_replica)){
				if(!$underscore_mark){
					$samplename.="_";
				}
				$samplename.= "b".$sample->biological_replica;
				if($underscore_mark){
					$underscore_mark = false;
				}
			}
		}
		//	Technical Replicas
		if(isset($sample->technical_replica)){
			if(is_numeric($sample->technical_replica)){
				if(!$underscore_mark){
					$samplename.="_";
				}
				$samplename.= "t".$sample->technical_replica;
				if($underscore_mark){
					$underscore_mark = false;
				}
			}
		}
		if(strpos(strtolower($sample->name), 'nobarcode') !== false){
			if(!$underscore_mark){
					$samplename.="_";
				}
			$samplename.= "$sample->name";
		}
		//$this->model->query("UPDATE `ngs_samples` SET `samplename` = '$samplename' WHERE `id` = $sample_id");
		return $samplename;
	}
	
	/*
	* parseExcel
	*
	* Parses the Excel Spreadsheet for all values
	* Also checks if values are valid for submission
	*
	* @param string $gid group id used
	* @param string $sid security id
	* @param worksheet $worksheet worksheet information passed from excel
	* @param sheetData $sheetData data passed from excel
	* @param bool $passed_final_check passing final_check for multiple iterations.
	* @return [Bool, String]
	*/
	function parseExcel($gid, $sid, $worksheet, $sheetData, $passed_final_check) {
		$this->worksheet=$worksheet;
		$this->sheetData=$sheetData;
		$this->sid=$sid;
		$this->gid=$gid;
		$this->final_check = $passed_final_check;
		$this->clustername = json_decode($this->query("SELECT clusteruser FROM users WHERE username = '".$_SESSION['user']."'"))[0]->clusteruser;
		
		$text = '<li>'.$this->worksheet['worksheetName'].'<br />';

		$this->username=$_SESSION['user'];
		$sql="select id from users where `username`='$this->username'";
		$this->uid=$this->query($sql, 1);
		
		//	Check Data
		if ( $this->worksheet['worksheetName']=="METADATA"){
			$text.=$this->getMeta();
		}
		elseif ( $worksheet['worksheetName']=="LANES"){
			$text.=$this->getLanes();
		}
		elseif ( $worksheet['worksheetName']=="IMPORTS"){
			$text.=$this->getLanes();
		}
		elseif ( $worksheet['worksheetName']=="PROTOCOLS"){
			$text.=$this->getProtocols();
		}
		elseif ( $worksheet['worksheetName']=="SAMPLES"){
			$text.=$this->getSamples();
		}
		elseif ( $worksheet['worksheetName']=="DIRS"){
			$text.=$this->getDirs();
		}
		elseif ( $worksheet['worksheetName']=="FILES"){
			$text.=$this->getFiles();
		}
		
		//	Process Data
		$parseArray = [$this->final_check, $text];
		return $parseArray;
	}
	
	/*
	* finalizeExcel
	*
	* submits data passed in throught the excel sheet
	* creates javascript variables for initial run submission.
	*
	* @param worksheet $worksheet worksheet information passed from excel
	* @param sheetData $sheetData data passed from excel
	* @return String
	*/
	function finalizeExcel($worksheet, $sheetData){
		$this->worksheet=$worksheet;
		$this->sheetData=$sheetData;
		
		$text = "";
		
		$text='<li>'.$this->worksheet['worksheetName'].'<br />';
		if ( $this->worksheet['worksheetName']=="METADATA"){
			$text.=$this->processMeta();
		}elseif ( $worksheet['worksheetName']=="LANES"){
			$text.=$this->processLanes();
		}elseif ( $worksheet['worksheetName']=="IMPORTS"){
			$text.=$this->processLanes();
		}elseif ( $worksheet['worksheetName']=="PROTOCOLS"){
			$text.=$this->processProtocols();
		}elseif ( $worksheet['worksheetName']=="SAMPLES"){
			$text.=$this->processSamples();
		}elseif ( $worksheet['worksheetName']=="DIRS"){
			$text.=$this->processDirs();
		}elseif ( $worksheet['worksheetName']=="FILES"){
			$text.=$this->processFiles();
			$text.=$this->successText("<BR><BR>Excel import successful!<BR>");
		}
		if(!isset($this->initialSubmission[5])){
			array_push($this->initialSubmission, $this->laneList);
		}
		if($worksheet['worksheetName']=="FILES"){
			array_push($this->initialSubmission, $_SESSION['group_id']);
			array_push($this->initialSubmission, $_SESSION['security_id']);
		}
		$text.="<script type='text/javascript'>";
		$text.="var initialSubmission = '" . implode(",", $this->initialSubmission) . "';";
		$text.="var initialNameList = '" . $this->namesList . "';";
		$text.="var initialSampleIDS = '" .implode(",",$this->sample_ids). "';";
		$text.="</script>";
		
		return $text;
	}
	
	/*
	* errorText
	*
	* Returns the html version for the error color
	*
	* @param string $text text to paint with error color
	* @return string
	*/
	function errorText($text){
		return "<font color='red'>" . $text . "</font><BR>";
	}
	
	/*
	* warningText
	*
	* Returns the html version for the warning color
	*
	* @param string $text text to paint with warning color
	* @return string
	*/
	function warningText($text){
		return "<font color='#B45F04'>" . $text . "</font><BR>";
	}
	
	/*
	* successText
	*
	* Returns the html version for the success color
	*
	* @param string $text text to paint with success color
	* @return string
	*/
	function successText($text){
		return "<font color='green'>" . $text . "</font><BR>";
	}
	
	/*
	* checkAlphaNumWithAddChars
	*
	* Checks string for alpha-numeric characters and any other extra characters specified
	*
	* @param string $extraChars extra characters to allow within the match
	* @param string $data string to check matching characters against
	* @return bool
	*/
	function checkAlphaNumWithAddChars($extraChars, $data){
		return preg_match('/^[a-zA-Z0-9' . $extraChars . ']+$/', $data);
	}
	
	/*
	* checkNumeric
	*
	* Checks string for numeric characters only
	*
	* @param string $data string to check
	* @return bool
	*/
	function checkNumeric($data){
		return preg_match('/^[0-9]+$/', $data);
	}
	
	
	/*
	* checkUserPermissions
	*
	* Runs the service api to find if use has read permissions
	*
	* @param string $clustername cluster username
	* @return string
	*/
	function checkUserPermissions($clustername){
		$request = API_PATH.'/api/service.php?func=checkPermissions&username='.$clustername;
		$valid_fastq = json_decode('['.json_decode(file_get_contents($request)).']');
		if(isset($valid_fastq[0]->ERROR)){
			$this->final_check = false;
			return $valid_fastq[0]->ERROR;
		}
		return 'pass';
	}
	
	/*
	* checkDirPermissions
	*
	* Runs the service api to find if use has write permissions over a directory
	*
	* @param string $dir the directory to check
	* @param string $clustername cluster username
	* @return string
	*/
	function checkDirPermissions($dir, $clustername){
		$request = API_PATH.'/api/service.php?func=checkPermissions&username='.$clustername.'&outdir='.$this->directoryCheck($dir);
		$valid_fastq = json_decode('['.json_decode(file_get_contents($request)).']');
		if(isset($valid_fastq[0]->ERROR)){
			$this->final_check = false;
			return $valid_fastq[0]->ERROR;
		}
		return 'pass';
	}
	
	/*
	 * checkNewRun
	 */
	function checkNewRun($dir){
		$request = API_PATH.'/api/service.php?func=checkNewRun&outdir='.$this->directoryCheck($dir);
		$valid_dir = json_decode('['.json_decode(file_get_contents($request)).']');
		if(isset($valid_dir[0]->ERROR)){
			$this->final_check = false;
			return $valid_dir[0]->ERROR;
		}
		return 'pass';
	}
	
	/*
	* checkFilePermissions
	*
	* Runs the service api to find if use has permissions over a specific file
	*
	* @param string dir directory of the file
	* @param string $file the file to check
	* @param string $clustername cluster username
	* @return string
	*/
	function checkFilePermissions($clustername){
		chdir(getcwd().'/api');
		require_once('funcs.php');
		chdir('../');
		$funcs = new funcs();
		$request = "";
		foreach($this->file_name_arr as $fna){
			if(end($this->file_name_arr) == $fna){
				$request .= $fna;
			}else{
				$request .= $fna . ',';
			}
		}
		$params['username'] = $clustername;
		$params['file'] = $request;
		$result = stripslashes($funcs->checkFile($params));
		$valid_fastq = json_decode('['.str_replace("\n","",$result).']', true);
		if(isset($valid_fastq[0]['ERROR'])){
			$this->final_check = false;
			$error_array = explode("ls: ",$valid_fastq[0]['ERROR']);
			return implode("<br>", array_splice($error_array, 1));
		}
		return 'pass';
	}

	
	/*
	 *	getMeta()
	 *
	 *	Obtains metadata information from the excel spreadsheet and checks the validity of the entries.
	 *
	 *	If an entry is not valid, a bool flag will switch.
	 *	Checks will continue but actual database submission will halt.
	 */
	function getMeta() {
		$meta_check = true;
		$text = "";
		//	For the data within the metadata tab
		for ($i=1;$i<=$this->worksheet['totalRows'];$i++)
		{
			if($this->sheetData[$i]["A"]=="title"){
				$this->experiment_name=trim($this->esc($this->sheetData[$i]["B"]));
				array_push($this->initialSubmission, trim($this->esc($this->sheetData[$i]["B"])));
			}
			if($this->sheetData[$i]["A"]=="summary"){$this->summary=trim($this->esc($this->sheetData[$i]["B"]));}
			if($this->sheetData[$i]["A"]=="overall design"){$this->design=trim($this->esc($this->sheetData[$i]["B"]));}
			if($this->sheetData[$i]["A"]=="organization"){$this->organization=trim($this->esc($this->sheetData[$i]["B"]));}
			if($this->sheetData[$i]["A"]=="lab"){$this->lab=trim($this->esc($this->sheetData[$i]["B"]));}
			if($this->sheetData[$i]["A"]=="grant"){$this->grant=trim($this->esc($this->sheetData[$i]["B"]));}
			if($this->sheetData[$i]["A"]=="contributor"){array_push($this->conts, trim($this->esc($this->sheetData[$i]["B"])));}
			
			//	Older versions or single/multiple directory versions might contain different names.
			if($this->sheetData[$i]["A"]=="fastq directory" || $this->sheetData[$i]["A"]=="input directory"){
				$this->fastq_dir=$this->directoryCheck(trim($this->esc($this->sheetData[$i]["B"])));
			}
			if($this->sheetData[$i]["A"]=="backup directory" || $this->sheetData[$i]["A"]=="processed directory" || $this->sheetData[$i]["A"]=="process directory"){
				$this->backup_dir=$this->directoryCheck(trim($this->esc($this->sheetData[$i]["B"])));
				array_push($this->initialSubmission, $this->directoryCheck(trim($this->esc($this->sheetData[$i]["B"]))));
			}
			
			if($this->sheetData[$i]["A"]=="amazon bucket"){$this->amazon_bucket=trim($this->esc($this->sheetData[$i]["B"]));}
			
			//	Fastq Directory
			if($this->fastq_dir == null && $this->sheetData[$i]["A"]=="fastq directory"){
				$text.= $this->errorText("Fastq directory is required for submission");
				$this->final_check = false;
				$meta_check = false;
			}
			
			//	Backup Directory
			if($this->backup_dir == null && ($this->sheetData[$i]["A"]=="backup directory" || $this->sheetData[$i]["A"]=="temporary directory" || $this->sheetData[$i]["A"]=="processed directory" || $this->sheetData[$i]["A"]=="process directory")){
				$text.= $this->errorText("Processed directory is required for submission");
				$this->final_check = false;
				$meta_check = false;
			}
			
			//	Directory validity tests
			if(isset($this->fastq_dir) && ( $this->sheetData[$i]["A"]=="fastq directory" || $this->sheetData[$i]["A"]=="input directory")){
				$permCheck = $this->checkUserPermissions($this->clustername);
				if($permCheck != 'pass'){
					$text.= $this->errorText("Fastq Directory error (".$this->fastq_dir."). ".$permCheck);
					$this->final_check = false;
					$meta_check = false;
				}
				if(!$this->checkAlphaNumWithAddChars('\_\-\.\/\+', $this->fastq_dir)){
					$text.= $this->errorText("Fastq Directory does not contain proper characters, please use alpha-numerics, dashes, underscores, periods, and backslashes");
					$this->final_check = false;
					$meta_check = false;
				}
			}
			
			if(isset($this->backup_dir) && ( $this->sheetData[$i]["A"]=="backup directory" || $this->sheetData[$i]["A"]=="processed directory" || $this->sheetData[$i]["A"]=="process directory" )){
				$permCheck = $this->checkUserPermissions($this->clustername);
				$dirCheck = $this->checkDirPermissions($this->backup_dir, $this->clustername);
				if($permCheck != 'pass'){
					$text.= $this->errorText("Process Directory error (".$this->backup_dir."). ".$permCheck);
					$this->final_check = false;
					$meta_check = false;
				}
				if($dirCheck != 'pass'){
					$text.= $this->errorText("Process Directory error (".$this->backup_dir."). ".$dirCheck);
					$this->final_check = false;
					$meta_check = false;
				}
				if(!$this->checkAlphaNumWithAddChars('\_\-\.\/', $this->backup_dir)){
					$text.= $this->errorText("Process Directory does not contain proper characters, please use alpha-numerics, dashes, underscores, periods, and backslashes");
					$this->final_check = false;
					$meta_check = false;
				}
			}
		}
		
		/*
		 *	Check for proper data input
		 */
		//	Experiment Name
		if(isset($this->experiment_name)){
			if(!$this->checkAlphaNumWithAddChars('\s\_\-', $this->experiment_name)){
				$text.= $this->errorText("title does not contain proper characters, please use alpha-numeric characters and spaces");
				$this->final_check = false;
				$meta_check = false;
			}
		}else{
			$text.= $this->errorText("title is required for submission");
			$this->final_check = false;
			$meta_check = false;
		}
		
		//	Summary
		if(!isset($this->summary)){
			$text.= $this->errorText("summary is required for submission");
			$this->final_check = false;
			$meta_check = false;
		}
		
		//	Design
		if(!isset($this->design)){
			$text.= $this->errorText("overall design is required for submission");
			$this->final_check = false;
			$meta_check = false;
		}
		
		//	Organization
		if(!isset($this->organization)){
			$this->organization = NULL;
		}
		
		//	Lab
		if(!isset($this->lab)){
			$this->lab = NULL;
		}
		
		//	Grant
		if(!isset($this->grant)){
			$this->grant = NULL;
		}
		
		if($meta_check){
			$text.= $this->successText('Formatting passed inspection!<BR>');
		}
		return $text;
	}
	
	/*
	 *	processMeta()
	 *
	 *	Processes and submits the metadata to the database with the data obtained from getMeta()
	 */
	function processMeta(){
		$text = "";
		$new_series = new series($this, $this->experiment_name,$this->summary,$this->design,
								$this->organization, $this->lab, $this->grant);
		$this->series_id=$new_series->getId();
		$text.="SERIES:".$new_series->getStat()."<BR>";
		$new_conts = new contributors($this, $this->conts);
		$text.= "CONT:".$new_conts->getStat()."<BR>";
		
		//	If single directory entry, create directory object and use.
		if(isset($this->fastq_dir)){
			$dir = new dir();
			$dir->dir_tag="old_import_template";
			$dir->fastq_dir=$this->fastq_dir;
			$dir->backup_dir=$this->backup_dir;
			$dir->amazon_bucket=$this->amazon_bucket;
			$this->dir_arr[$dir->dir_tag]=$dir;
			
			$new_dirs = new dirs($this, $this->dir_arr, $dir->backup_dir, $dir->amazon_bucket);
			$text="DIR:".$new_dirs->getStat()."<BR>";
			$dir_id = json_decode($this->query("SELECT id FROM ngs_dirs
											   WHERE fastq_dir = '".$this->fastq_dir."'
											   AND backup_dir = '".$this->backup_dir."'"));
			array_push($this->dir_ids, $dir_id);
		}
		return $text;
	}

	
	/*
	 *	getLanes()
	 *
	 *	Obtains lane/import information from the excel spreadsheet and checks the validity of the entries.
	 *
	 *	If an entry is not valid, a bool flag will switch.
	 *	Checks will continue but actual database submission will halt.
	 */
	function getLanes(){
		$lane_check = true;
		$text = "";
		//	For the data within the lane/import tab
		for ($i=4;$i<=$this->worksheet['totalRows'];$i++)
		{
			$lane = new lane();
			for ($j='A';$j<=$this->worksheet['lastColumnLetter'];$j++)
			{
				if($this->sheetData[3][$j]=="Import name" || $this->sheetData[3][$j]=="Lane name"){
					$lane->name=trim($this->esc($this->sheetData[$i][$j]));
				}
				if($this->sheetData[3][$j]=="Sequencing id" || $this->sheetData[3][$j]=="Lane id"){
					$lane->lane_id=trim($this->esc($this->sheetData[$i][$j]));
				}
				if($this->sheetData[3][$j]=="Sequencing facility"){$lane->facility=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Cost"){$lane->cost=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Date submitted"){$lane->date_submitted=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Date received"){$lane->date_received=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="% PhiX requested"){$lane->phix_requested=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="% PhiX in lane"){$lane->phix_in_lane=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="# of Samples"){$lane->total_samples=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Resequenced?"){$lane->resequenced=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Notes"){$lane->notes=trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Total reads"){$lane->total_reads=trim($this->esc($this->sheetData[$i][$j]));}
			}
			
			//	Error Checks
			//	Lane Name
			if(isset($lane->name)){
				if($this->checkAlphaNumWithAddChars('\s\_\-', $lane->name)){
					$this->lane_arr[$lane->name]=$lane;
				}else{
					$text.= $this->errorText("Import name does not contain proper characters, please use alpha-numeric characters and underscores (row " . $i . ")");
					$this->final_check = false;
					$lane_check = false;
				}
			}else{
				$text.= $this->errorText("Import name is required for submission (row " . $i . ")");
				$this->final_check = false;
				$lane_check = false;
			}
			if(!isset($lane->lane_id)){
				$lane->lane_id = NULL;
			}
			if(!isset($lane->total_reads)){
				$lane->total_reads = NULL;
			}
		}
		
		if($lane_check){
			$text.= $this->successText('Formatting passed inspection!<BR>');
		}
		return $text;
	}
	
	/*
	 *	processLanes()
	 *
	 *	Processes and submits the lanes/imports to the database with the data obtained from getLanes()
	 */
	function processLanes(){
		$text = "";
		//echo json_encode($this->lane_arr);
		$new_lanes = new lanes($this, $this->lane_arr);
		$text="LANE:".$new_lanes->getStat()."</br>";
		#$text.="LANE:".$new_lanes->getSQL();
		foreach(explode(",",$this->laneList) as $ll){
			$ll_id = json_decode($this->query("SELECT id FROM ngs_lanes WHERE name = '$ll' and series_id in 
											(SELECT id FROM ngs_experiment_series WHERE experiment_name = '".$this->experiment_name."')"))[0]->id;
			array_push($this->lane_ids, $ll_id);
		}
		return $text;
	}

	/*
	 *	getProtocols()
	 *
	 *	Obtains protocols information from the excel spreadsheet and checks the validity of the entries.
	 *
	 *	If an entry is not valid, a bool flag will switch.
	 *	Checks will continue but actual database submission will halt.
	 */
	function getProtocols(){
		$prot_check = true;
		$text = "";
		//	For the data within the protocols tab
		for ($i=4;$i<=$this->worksheet['totalRows'];$i++)
		{
			$prot = new prot();
			for ($j='A';$j<=$this->worksheet['lastColumnLetter'];$j++)
			{
				if($this->sheetData[3][$j]=="protocol name"){$prot->name= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="growth protocol"){$prot->growth= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="extract protocol"){$prot->extraction= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="library construction protocol"){$prot->library_construction= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="crosslinking method"){$prot->crosslinking_method= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="fragmentation method"){$prot->fragmentation_method= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="strand-specific"){$prot->strand_specific= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="library strategy"){$prot->library_strategy= trim($this->esc($this->sheetData[$i][$j]));}
			}

			//	Check Protocols
			//	Protocol Name
			if(isset($prot->name)){
				$this->prot_arr[$prot->name]=$prot;	
			}else{
				$text.= $this->errorText("protocol name is required for submission (row " . $i . ")");
				$this->final_check = false;
				$prot_check = false;
			}
			
			//	Crosslinking Method
			if(!isset($prot->crosslinking_method)){
				$prot->crosslinking_method = NULL;
			}
			
			//	Fragmentation Method
			if(!isset($prot->fragmentation_method)){
				$prot->fragmentation_method = NULL;
			}
			
			//	Strand Specific
			if(!isset($prot->strand_specific)){
				$prot->strand_specific = NULL;
			}
		}
		if($prot_check){
			$text.= $this->successText('Formatting passed inspection!<BR>');
		}
		return $text;
	}
	
	/*
	 *	processProtocols()
	 *
	 *	Processes and submits the protocols to the database with the data obtained from getLanes()
	 */
	function processProtocols(){
		$text = "";
		//echo json_encode($this->prot_arr);
		$new_protocol = new protocols($this, $this->prot_arr);
		$text.= "PROT:".$new_protocol->getStat();
		return $text;
	}

	/*
	 *	getSamples()
	 *
	 *	Obtains samples information from the excel spreadsheet and checks the validity of the entries.
	 *
	 *	If an entry is not valid, a bool flag will switch.
	 *	Checks will continue but actual database submission will halt.
	 */
	function getSamples(){
		$samp_check = true;
		$text = "";
		/*
		 *	For each row in the samples worksheet
		 */
		for ($i=4;$i<=$this->worksheet['totalRows'];$i++)
		{
			/*
			 *	Read data columns
			 */
			$samp = new sample();
			$tag = new tag();
			for ($k=0;$k!=$this->columnNumber($this->worksheet['lastColumnLetter']);$k++)
			{
				$j = $this->num2alpha($k);
				if($this->sheetData[3][$j]=="Sample name"){$samp->name= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Lane name" || $this->sheetData[3][$j]=="Import name"){
					$samp->lane_name= trim($this->esc($this->sheetData[$i][$j]));
				}
				if($this->sheetData[3][$j]=="Protocol name"){$samp->protocol_name= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="barcode"){$samp->barcode= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="title"){$samp->title= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="batch id"){$samp->batch= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="source symbol"){$samp->source_symbol= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="source name"){$samp->source= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="organism"){$samp->organism= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="biosample type"){$samp->biosample_type= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="molecule"){$samp->molecule= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="description"){$samp->description= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="instrument model"){$samp->instrument_model= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="average insert size"){$samp->avg_insert_size= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="read length"){$samp->read_length= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Genotype"){$samp->genotype= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Condition Symbol"){$samp->condition_symbol= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Condition"){$samp->condition= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="concentration"){$samp->concentration= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="treatment manufacturer"){$samp->treatment_manufacturer= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Donor"){$samp->donor= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Time"){$samp->time= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Biological Replica"){$samp->biological_replica= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Technical Replica"){$samp->technical_replica= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="spikeIns"){$samp->spikeins= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="3' Adapter sequence"){$samp->adapter= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Notebook reference"){$samp->notebook_ref= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Notes"){$samp->notes= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Library type"){$samp->lib_type= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Antibody Target"){$samp->target= trim($this->esc($this->sheetData[$i][$j]));}
				
				if($this->sheetData[3][$j]=="Sample name" && $samp->name != NULL){
					if($this->namesList == null){
						$this->namesList = $samp->name;
					}else{
						$this->namesList .= "," . $samp->name;
					}
				}
				if(($this->sheetData[3][$j]=="Lane name" || $this->sheetData[3][$j]=="Import name") && $samp->lane_name != NULL){
					if ($this->laneList == null){
						$this->laneList = $samp->lane_name;
					}else if(in_array($samp->lane_name, explode(",",$this->laneList)) === false){
						$this->laneList .= ','.$samp->lane_name;
					}
				}
				if($this->sheetData[3][$j]=="organism" && $this->organismCheck == null){
					array_push($this->initialSubmission, trim($this->esc($this->sheetData[$i][$j])));
					$this->organismCheck = 'check';
				}
	
				$valid = "/^characteristics:\s+?(.*)/";
	
				if(preg_match( $valid, $this->sheetData[3][$j], $matches) == 1)
				{
					if ($matches[1] != "tag" && $this->sheetData[$i][$j]!="")
					{
						$tag->tag=$matches[1];
						$tag->value=$this->sheetData[$i][$j];
						$tag->sample_name=$samp->name;
						$this->char_arr[$samp->name]=$tag;
					}
				}
			}

			//	Samplename
			if($this->experiment_name == 'Dendritic Cell Transcriptional Landscape'){
				$samp->samplename = $this->createSampleName($samp);
			}else{
				$samp->samplename = $samp->name;
			}
			
			$samplename_bool = true;
			if(isset($this->sample_arr)){
				if($this->experiment_name == 'Dendritic Cell Transcriptional Landscape'){
					foreach($this->sample_arr as $sa){
						if($samp->samplename == $sa->samplename && $samp->samplename != '' && $samplename_bool){
							$text.= $this->errorText("samplename naming scheme already exists for another sample (row " . $i . "). <br>
													 DC project sample naming scheme = Donor_Source_Conditions_Time_Bio-rep_Tech-rep.");
							$this->final_check = false;
							$samp_check = false;
							$samplename_bool = false;
						}
					}
				}else{
					foreach($this->sample_arr as $sa){
						if($samp->samplename == $sa->samplename && $samp->samplename != '' && $samplename_bool){
							$text.= $this->errorText("samplename naming scheme already exists for another sample (row " . $i . ")");
							$this->final_check = false;
							$samp_check = false;
							$samplename_bool = false;
						}
					}
				}
			}
			
			//	Name
			if(isset($samp->samplename)){
				if($this->checkAlphaNumWithAddChars('\_\-', $samp->samplename)){
					//	Need to check the database for similar names as well at a later date
					if(isset($this->sample_arr[$samp->name])){
						$text.= $this->errorText("Sample name already exists in that Import (row " . $i . ")");
						$this->final_check = false;
						$samp_check = false;
					}elseif(ctype_digit($samp->name[0])){
						$text.= $this->errorText("Sample name cannot not start with a number (row " . $i . ")");
						$this->final_check = false;
						$samp_check = false;
					}else{
						$this->sample_arr[$samp->name]=$samp;
					}
				}else{
					$text.= $this->errorText("Sample name does not contain proper characters, please use alpha-numeric characters and underscores (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}
			}else{
				$text.= $this->errorText("Sample name is required for submission (row " . $i . ")");
				$this->final_check = false;
				$samp_check = false;
			}
			
			//	Lane Name
			if(isset($samp->lane_name)){
				if(!isset($this->lane_arr[$samp->lane_name])){
					$text.= $this->errorText("Import name does not match any import given in the excel file (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}else if($samp->lane_name == $samp->name){
					$text.= $this->errorText("Import name and Sample name cannot be identical (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}else{
					foreach($this->lane_arr as $key => $value){
						if($samp->name == $key){
							$text.= $this->errorText("Sample name cannot match a different Import name (row " . $i . ")");
							$this->final_check = false;
							$samp_check = false;
						}
					}
				}
			}else{
				$text.= $this->errorText("Import name is required for submission (row " . $i . ")");
				$this->final_check = false;
				$samp_check = false;
			}
			
			//	Protocol Name
			if(!isset($this->prot_arr[$samp->protocol_name])){
				$text.= $this->errorText("Protocol name does not match any protocol given in the excel file (row " . $i . ")");
				$this->final_check = false;
				$samp_check = false;
			}
			
			//	Batch ID
			if(!isset($samp->batch)){
				$samp->batch = NULL;
			}
			
			//	Source Symbol
			if(!isset($samp->source_symbol)){
				$samp->source_symbol = NULL;
			}else{
				if(!$this->checkAlphaNumWithAddChars('\_\-\,', $samp->source_symbol) && $samp->source_symbol != NULL){
					$text.= $this->errorText("Source symbol does not contain proper characters, please use alpha-numeric characters and underscores (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}
			}
			
			//	Condition Symbol
			if(!isset($samp->condition_symbol)){
				$samp->condition_symbol = NULL;
			}else{
				if(!$this->checkAlphaNumWithAddChars('\_\-\,', $samp->condition_symbol) && $samp->condition_symbol != NULL){
					$text.= $this->errorText("Condition symbol does not contain proper characters, please use alpha-numeric characters and underscores/dashes and separate multiple conditions with , (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}
			}
			
			//	Condition
			if(!isset($samp->condition)){
				$samp->condition = NULL;
			}else{
				if(!$this->checkAlphaNumWithAddChars('\s\_\-\,', $samp->condition) && $samp->condition != NULL){
					$text.= $this->errorText("Condition does not contain proper characters, please use alpha-numeric characters and underscores/dashes and separate multiple conditions with , (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}
			}
			
			//	Concentration
			if(!isset($samp->concentration)){
				$samp->concentration = NULL;
			}
			
			//	Treatment Manufacturer
			if(!isset($samp->treatment_manufacturer)){
				$samp->treatment_manufacturer = NULL;
			}
			
			//	Biosample Type
			if(!isset($samp->biosample_type)){
				$samp->biosample_type = NULL;
			}
			
			//	Donor
			if(!isset($samp->donor)){
				$samp->donor = NULL;
			}elseif(!$this->checkAlphaNumWithAddChars('\_\-\,', $samp->donor) && $samp->donor != NULL){
				$text.= $this->errorText("Donor does not contain proper characters, please use alpha-numeric characters and underscores (row " . $i . ")");
				$this->final_check = false;
				$samp_check = false;
			}
			
			//	Time
			if(!isset($samp->time)){
				$samp->time = NULL;
			}else{
				if(!$this->checkNumeric($samp->time) && $samp->time != NULL){
					$text.= $this->errorText("Time bust me an integer expressed in minutes (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}
			}
			
			//	Biological Replica
			if(!isset($samp->biological_replica)){
				$samp->biological_replica = NULL;
			}else{
				if(!$this->checkNumeric($samp->biological_replica) && $samp->biological_replica != NULL){
					$text.= $this->errorText("Biological Replica bust me an integer expressed in minutes (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}
			}
			
			//	Technical Replica
			if(!isset($samp->technical_replica)){
				$samp->technical_replica = NULL;
			}else{
				if(!$this->checkNumeric($samp->technical_replica) && $samp->technical_replica != NULL){
					$text.= $this->errorText("Technical Replica bust me an integer (row " . $i . ")");
					$this->final_check = false;
					$samp_check = false;
				}
			}
			
			//	Spikeins
			if(!isset($samp->spikeins)){
				$samp->spikeins = NULL;
			}
			
			//	Library Type
			if(!isset($samp->lib_type)){
				$samp->lib_type = NULL;
			}
			
			//	Antibody Target
			if(!isset($samp->target)){
				$samp->target = NULL;
			}elseif(!$this->checkAlphaNumWithAddChars('\_\-\,', $samp->target) && $samp->target != NULL){
				$text.= $this->errorText("Target does not contain proper characters, please use alpha-numeric characters and underscores (row " . $i . ")");
				$this->final_check = false;
				$samp_check = false;
			}
		}
		if($samp_check){
			$text.= $this->successText('Formatting passed inspection!<BR>');
		}
		return $text;
	}
	
	/*
	 *	processSamples()
	 *
	 *	Processes and submits the samples to the database with the data obtained from getLanes()
	 */
	function processSamples(){
		$text = "";
		$this->samples=$this->sample_arr;
		$new_samples = new samples($this, $this->sample_arr);
		$text="SAMPLE:".$new_samples->getStat()."<BR>";
		$new_chars = new characteristics($this, $this->char_arr);
		$text.="CHAR:".$new_chars->getStat();
		foreach(explode(",",$this->namesList) as $nl){
			$nl_id = json_decode($this->query("SELECT id FROM ngs_samples WHERE name = '$nl' and lane_id in
																   (SELECT id FROM ngs_lanes WHERE name in ('".implode("','", explode(",",$this->laneList))."') and series_id in 
																   (SELECT id FROM ngs_experiment_series WHERE experiment_name = '$this->experiment_name'))"))[0]->id;
			array_push($this->sample_ids, $nl_id);
		}
		return $text;
	}
		
	/*
	 *	getDirs()
	 *
	 *	Obtains directory information from the excel spreadsheet and checks the validity of the entries.
	 *
	 *	If an entry is not valid, a bool flag will switch.
	 *	Checks will continue but actual database submission will halt.
	 */
	function getDirs(){
		$dir_check = true;
		$text = "";
		//	for each sample given
		for ($i=4;$i<=$this->worksheet['totalRows'];$i++)
		{
			$dir = new dir();
			for ($j='A';$j<=$this->worksheet['lastColumnLetter'];$j++)
			{
				if($this->sheetData[3][$j]=="Directory ID"){$dir->dir_tag= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Fastq directory" || $this->sheetData[3][$j]=="Processed directory" || $this->sheetData[3][$j]=="Process directory" || $this->sheetData[3][$j]=="Input directory"){
					$dir->fastq_dir= $this->directoryCheck(trim($this->esc($this->sheetData[$i][$j])));
				}
			}
			
			//	Missing directory information
			if(!isset($dir->dir_tag) || $dir->dir_tag == ''){
				$text.= $this->errorText("Dir ID required for submission (row " . $i . ")");
				$this->final_check = false;
				$dir_check = false;
			}else{
				array_push($this->dir_tags, $dir->dir_tag);
			}
			if(!isset($dir->fastq_dir) || $dir->fastq_dir == ''){
				$text.= $this->errorText("Fastq directory required for submission (row " . $i . ")");
				$this->final_check = false;
				$dir_check = false;
			}else{
				array_push($this->dir_fastq, $dir->fastq_dir);
			}
			
			//	Directory validity tests
			if(isset($this->fastq_dir) && ( $this->sheetData[$i]["A"]=="fastq directory" || $this->sheetData[$i]["A"]=="input directory")){
				$permCheck = $this->checkUserPermissions($this->clustername);
				if($permCheck != 'pass'){
					$text.= $this->errorText("Fastq Directory error (".$dir->fastq_dir."). ".$permCheck);
					$this->final_check = false;
					$dir_check = false;
				}
				if(!$this->checkAlphaNumWithAddChars('\_\-\.\/\+', $this->fastq_dir)){
					$text.= $this->errorText("Fastq Directory ".$this->fastq_dir." does not contain proper characters, please use alpha-numerics, dashes, underscores, periods, and backslashes");
					$this->final_check = false;
					$meta_check = false;
				}
			}
			if(isset($this->dir_arr[$dir->dir_tag])){
				$text.= $this->errorText("Fastq Directory error.  Cannot have duplicate Directory IDs. (".$dir->fastq_dir.").");
				$this->final_check = false;
				$dir_check = false;
			}
			
			if($dir_check){
				$this->dir_arr[$dir->dir_tag]=$dir;
			}
		}
		if($dir_check){
			$text.= $this->successText('Formatting passed inspection!<BR>');
		}
		return $text;
	}
	
	/*
	 *	processDirs()
	 *
	 *	Processes and submits the directories to the database with the data obtained from getLanes()
	 */
	function processDirs(){
		$text = "";
		$new_dirs = new dirs($this, $this->dir_arr, $this->backup_dir, $this->amazon_bucket);
		$text="DIR:".$new_dirs->getStat()."<BR>";
		foreach($this->dir_fastq as $df){
			$dir_id = json_decode($this->query("SELECT id FROM ngs_dirs
											   WHERE fastq_dir = '".$df."'
											   AND backup_dir = '". $this->backup_dir."'"));
			array_push($this->dir_ids, $dir_id);
		}
		return $text;
	}
	
	/*
	 *	getFiles()
	 *
	 *	Obtains file information from the excel spreadsheet and checks the validity of the entries.
	 *
	 *	If an entry is not valid, a bool flag will switch.
	 *	Checks will continue but actual database submission will halt.
	 */
	function getFiles(){
		$file_check = true;
		$text = "";
		//	for each file
		for ($i=4;$i<=$this->worksheet['totalRows'];$i++)
		{
			$file = new file();
			for ($j='A';$j<=$this->worksheet['lastColumnLetter'];$j++)
			{
				if($this->sheetData[3][$j]=="Sample or Lane Name (Enter same name for multiple files)" || $this->sheetData[3][$j]=="Sample or Import Name (Enter same name for multiple files)"){
					$file->name= trim($this->esc($this->sheetData[$i][$j]));
					if(isset($this->lane_arr[$file->name]) && $this->laneArrayCheck == null){
						array_push($this->initialSubmission, 'lane');
						$this->laneArrayCheck = 'lane';
					}else if(isset($this->sample_arr[$file->name]) && $this->laneArrayCheck == null){
						array_push($this->initialSubmission, 'sample');
						$this->laneArrayCheck = 'sample';
					}
				}
				if($this->sheetData[3][$j]=="Directory ID"){$file->dir_tag= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="file name(comma separated for paired ends)"){
					$file->file_name= trim($this->esc($this->sheetData[$i][$j]));
					$file->file_name= preg_replace('/\s/', '', $file->file_name);
					//	If user accidentally adds samples for multiple columns
					if($j == 'B' && isset($this->sheetData[$i]['C'])){
						$additional_files = trim($this->sheetData[$i]['C']);
					}else if ($j == 'C' && isset($this->sheetData[$i]['D'])){
						$additional_files = trim($this->sheetData[$i]['D']);
					}
					if(isset($additional_files)){
						$comma_check = strpos($file->file_name, ",");
						if($comma_check === false){
							$file->file_name .= ','.$additional_files;
						}else{
							$text.= $this->errorText("Incorrect File formatting, Make sure files are submitted in the specific column. (row " . $i . ")");
							$this->final_check = false;
							$file_check = false;
						}
						unset($additional_files);
						unset($comma_check);
					}
					if (strpos($file->file_name, ',') !== false && $this->pairedEndCheck == null){
						array_push($this->initialSubmission, 'paired');
						$this->pairedEndCheck = 'paired';
					}
				}
				if($this->sheetData[3][$j]=="file checksum"){$file->checksum= trim($this->esc($this->sheetData[$i][$j]));}
				if($this->sheetData[3][$j]=="Directory ID"){
					if(in_array($file->dir_tag, $this->dir_tags) && isset($this->dir_fastq[array_search($file->dir_tag, $this->dir_tags)])){
						$file->fastq_dir = $this->dir_fastq[array_search($file->dir_tag, $this->dir_tags)];
						$file->backup_dir = $this->backup_dir;
					}
				}
			}
			
			//	Sample/Lane Name checks
			if(isset($file->name)){
				if($this->checkAlphaNumWithAddChars('\_\-', $file->name)){
					if(!(isset($this->sample_arr[$file->name])) & !(isset($this->lane_arr[$file->name]))){
						$text.= $this->errorText("sample/import name does not match the samples/imports given (row " . $i . ")");
						$this->final_check = false;
						$file_check = false;
					}
				}else{
					$text.= $this->errorText("sample/import name does not contain proper characters, please use alpha-numeric characters, dashes, and underscores (row " . $i . ")");
					$this->final_check = false;
					$file_check = false;
				}
			}else{
				$text.= $this->errorText("sample/import name is required for submission (row " . $i . ")");
				$this->final_check = false;
				$file_check = false;
			}
			
			//	File Name checks
			if(isset($file->file_name)){
				array_push($this->file_arr,$file);
				if(!$this->checkAlphaNumWithAddChars('\_\-\,\.\+', $file->file_name)){
					$text.= $this->errorText("File(s) ".$file->file_name." does not contain proper characters, please use alpha-numerics, dashes, underscores, and periods");
					$this->final_check = false;
					$meta_check = false;
				}
			}else{
				$text.= $this->errorText("file name is required for submission (row " . $i . ")");
				$this->final_check = false;
				$file_check = false;
			}
			
			//	File Directory checks
			if(!isset($file->fastq_dir) && isset($file->dir_tag)){
				$text.= $this->errorText("Directory information is incorrect (row " . $i . ")");
				$this->final_check = false;
				$file_check = false;
			}elseif($this->fastq_dir != null){
				$file->dir_tag="old_import_template";
				$file->fastq_dir = $this->fastq_dir;
				$file->backup_dir = $this->backup_dir;
			}
			
			//	File Validity Check
			if(isset($file->fastq_dir)){
				if($this->pairedEndCheck == 'paired'){
					array_push($this->file_name_arr, $file->fastq_dir . explode(",",$file->file_name)[0]);
					array_push($this->file_name_arr, $file->fastq_dir . explode(",",$file->file_name)[1]);
				}else{
					array_push($this->file_name_arr, $file->fastq_dir . $file->file_name);
				}
			}
		}
		//	Check All Files for Validity
		$single_file_check = $this->checkFilePermissions($this->clustername);
		if($single_file_check != 'pass'){
			$text.= $this->errorText($single_file_check);
			$this->final_check = false;
			$file_check = false;
		}
		
		if($file_check){
			$text.= $this->successText('Formatting passed inspection!<BR>');
		}
		return $text;
	}
	
	/*
	 *	processFiles()
	 *
	 *	Processes and submits the files to the database with the data obtained from getLanes()
	 */
	function processFiles(){
		$text = "";
		//echo json_encode($this->file_arr);
		$new_files = new files($this, $this->file_arr, $this->samples);
		$text.="FILES:".$new_files->getStat();
		
		//	Checks for new file names of same samples/lanes
		if($this->laneArrayCheck == 'lane'){
			//For every directory added, find the id from the fastq directory and the backup directory
			$dir_ids_check = [];
			foreach($this->dir_arr as $da){
				$dir_id_ret = json_decode($this->query("SELECT id FROM ngs_dirs WHERE fastq_dir = '" . $da->fastq_dir . "' and backup_dir = '" . $da->backup_dir . "'"));
				array_push($dir_ids_check, $dir_id_ret[0]->id);
			}
			//Find the lane Ids based on the directory ID
			$lane_name_removal = json_decode($this->query("SELECT lane_id FROM ngs_temp_lane_files WHERE dir_id in (" . implode(",", $dir_ids_check) . ")"));
			
			//For every lane id found within the directory that isn't in the samples listed, remove them.
			foreach($lane_name_removal as $lnr){
				if(!in_array($lnr->lane_id, $this->lane_ids)){
					$this->query("DELETE FROM ngs_temp_lane_files where lane_id = $lnr->lane_id");
					$this->query("DELETE FROM ngs_lanes where id = $lnr->lane_id");
					$this->query("DELETE FROM ngs_samples where lane_id = $lnr->lane_id");
				}
			}
		}else{
			//For every directory added, find the id from the fastq directory and the backup directory
			$dir_ids_check = [];
			foreach($this->dir_arr as $da){
				$dir_id_ret = json_decode($this->query("SELECT id FROM ngs_dirs WHERE fastq_dir = '" . $da->fastq_dir . "' and backup_dir = '" . $this->backup_dir . "'"));
				array_push($dir_ids_check, $dir_id_ret[0]->id);
			}
			
			//Find the sample Ids based on the directory ID
			$sample_name_removal = json_decode($this->query("SELECT sample_id FROM ngs_temp_sample_files WHERE dir_id in (" . implode(",", $dir_ids_check) . ")"));
			
			//For every sample id found within the directory that isn't in the samples listed, remove them.
			foreach($sample_name_removal as $snr){
				if(!in_array($snr->sample_id, $this->sample_ids)){
					$this->query("DELETE FROM ngs_temp_sample_files where sample_id = $snr->sample_id AND dir_id in (" . implode(",", $dir_ids_check) . ")");
					$this->query("DELETE FROM ngs_samples where id = $snr->sample_id");
				}
			}
		}
		
		return $text;
	}
}

/* main class */
class main{
	public $model;
	public $insert=0;
	public $update=0;
	public $sql;

	function process($my) {
		return ($my->getId()>0 ? $my->update(): $my->insert());
	}

	function processArr($arr) {
		foreach ($arr as $val) {
			$this->getId($val)>0 ? $this->update($val): $this->insert($val);
		}
	}
	function correct_date($date) {
		if ($date=='') {
	 return "null";
		}
		else
		{
	 return "STR_TO_DATE('$date', '%m-%d-%Y')";
		}
	}
	function correct_bool($field) {
		return $field=="yes" ? "true" : "false";
	}
}

/* Series Definitions */
class series extends main{
	private $experiment_name;
	private $summary;
	private $design;
	private $organization;
	private $lab;
	private $grant;

	function __construct($model, $experiment_name, $summary, $design, $organization, $lab, $grant)
	{
		$this->experiment_name=$experiment_name;
		$this->summary=$summary;
		$this->design=$design;
		$this->organization=$organization;
		$this->lab=$lab;
		$this->grant=$grant;
		$this->model = $model;
		$this->process($this);
	}
	function getStat()
	{
		return "Update:$this->update, Insert:$this->insert";
	}

	function getId()
	{
		$sql="select id from ngs_experiment_series where `experiment_name`='$this->experiment_name'";
		return $this->model->query($sql, 1);
	}
	function insert()
	{

		$sql="insert into ngs_experiment_series(`experiment_name`, `summary`, `design`,
			`grant`, `owner_id`, `group_id`, `perms`, `date_created`,
			`date_modified`, `last_modified_user`)
			values('$this->experiment_name', '$this->summary', '$this->design',
			'$this->grant',
			'".$this->model->uid."', '".$this->model->gid."', '".$this->model->sid."',
			now(), now(), '".$this->model->uid."');";

		$this->insert++;
		
		$returned_sql = $this->model->query($sql);
		$experiment_id = $this->getId();
		
		//	Organization
		if($this->organization != NULL || $this->organization != ''){
			$check = "SELECT `id`, `organization`
						FROM ngs_organization
						WHERE `organization` = '".$this->organization."'";
			$check_result = json_decode($this->model->query($check));
			if($check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_organization` (`organization`) VALUES ('".$this->organization."')");
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_organization` WHERE `organization` = '".$this->organization."'"));
				$this->model->query("UPDATE `ngs_experiment_series` SET `organization_id` = ".$id[0]->id." WHERE `id` = $experiment_id");
			}else{
				//	Exists
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_organization` WHERE `organization` = '".$this->organization."'"));
				$this->model->query("UPDATE `ngs_experiment_series` SET `organization_id` = ".$id[0]->id." WHERE `id` = $experiment_id");
			}
		}
		
		//	Lab
		if($this->lab != NULL || $this->lab != ''){
			$check = "SELECT `id`, `lab`
						FROM ngs_lab
						WHERE `lab` = '".$this->lab."'";
			$check_result = json_decode($this->model->query($check));
			if($check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_lab` (`lab`) VALUES ('".$this->lab."')");
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_lab` WHERE `lab` = '".$this->lab."'"));
				$this->model->query("UPDATE `ngs_experiment_series` SET `lab_id` = ".$id[0]->id." WHERE `id` = $experiment_id");
			}else{
				//	Exists
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_lab` WHERE `lab` = '".$this->lab."'"));
				$this->model->query("UPDATE `ngs_experiment_series` SET `lab` = ".$id[0]->id." WHERE `id` = $experiment_id");
			}
		}
		
		return $returned_sql;
	}

	function update()
	{
		$sql="update ngs_experiment_series set
			`summary`='$this->summary',
			`design`='$this->design',
			`grant`='$this->grant',
			`group_id`='".$this->model->gid."',
			`perms`='".$this->model->sid."',
			`date_modified`=now(),
			`last_modified_user`='".$this->model->uid."' where `id` = ".$this->getId();
		$this->update++;
		
		$returned_sql = $this->model->query($sql);
		$experiment_id = $this->getId();
		
		//	Organization
		if($this->organization != NULL || $this->organization != ''){
			$check = "SELECT `id`, `organization`
						FROM ngs_organization
						WHERE `organization` = '".$this->organization."'";
			$check_result = json_decode($this->model->query($check));
			if($check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_organization` (`organization`) VALUES ('".$this->organization."')");
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_organization` WHERE `organization` = '".$this->organization."'"));
				$this->model->query("UPDATE `ngs_experiment_series` SET `organization_id` = ".$id[0]->id." WHERE `id` = $experiment_id");
			}else{
				//	Exists
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_organization` WHERE `organization` = '".$this->organization."'"));
				$this->model->query("UPDATE `ngs_experiment_series` SET `organization_id` = ".$id[0]->id." WHERE `id` = $experiment_id");
			}
		}
		
		//	Lab
		if($this->lab != NULL || $this->lab != ''){
			$check = "SELECT `id`, `lab`
						FROM ngs_lab
						WHERE `lab` = '".$this->lab."'";
			$check_result = json_decode($this->model->query($check));
			if($check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_lab` (`lab`) VALUES ('".$this->lab."')");
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_lab` WHERE `lab` = '".$this->lab."'"));
				$this->model->query("UPDATE `ngs_experiment_series` SET `lab_id` = ".$id[0]->id." WHERE `id` = $experiment_id");
			}else{
				//	Exists
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_lab` WHERE `lab` = '".$this->lab."'"));
				$this->model->query("UPDATE `ngs_experiment_series` SET `lab_id` = ".$id[0]->id." WHERE `id` = $experiment_id");
			}
		}
		
		return $returned_sql;
	}
}

/* Contributors class */
class contributors extends main{
	private $conts=[];

	function __construct($model, $conts = [])
	{
		$this->conts = $conts;
		$this->model = $model;

		$this->processArr($conts);
	}

	function getStat()
	{
		return "Update:$this->update, Insert:$this->insert";
	}


	function getId($val)
	{
		$sql="select id from ngs_contributors where `contributor`='$val'";
		return $this->model->query($sql,1);
	}
	function insert($val)
	{
		$sql="insert into ngs_contributors(`series_id`, `contributor`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`)
			values('".$this->model->series_id."', '$val', '".$this->model->uid."', '".$this->model->gid."', '".$this->model->sid."', now(), now(), '".$this->model->uid."');";
		$this->insert++;
		return $this->model->query($sql);
	}

	function update($val)
	{
		$sql="update ngs_contributors set `contributor`='$val',
		`group_id`='".$this->model->gid."', `perms`='".$this->model->sid."',
		`date_modified`=now(), `last_modified_user`='".$this->model->uid."'where `id` =".$this->getId($val);
		$this->update++;

		return $this->model->query($sql);
	}

}


/* Lanes class */
class lane{}
class lanes extends main{
	private $lane_arr=[];

	function __construct($model, $lane_arr = [])
	{
		$this->lane_arr = $lane_arr;
		$this->model=$model;

		$this->processArr($lane_arr);
	}

	function getStat()
	{
		return "Update:$this->update, Insert:$this->insert";
	}

	function getId($lane)
	{
		$sql="select id from ngs_lanes where `name`='$lane->name' and `series_id`='".$this->model->series_id."'";
		return $this->model->query($sql,1);
	}

	function getSQL()
	{
		return $this->sql;
	}
	function simpleNormalize($lane, $php_name, $lane_id, $database_name, $database_id_name)
	{
		if($lane->$php_name != NULL || $lane->$php_name != ''){
			$check = "SELECT `id`, `$database_name`
						FROM ngs_$database_name
						WHERE `$database_name` = '".$lane->$php_name."'";
			$check_result = json_decode($this->model->query($check));
			if($check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_$database_name` (`$database_name`) VALUES ('".$lane->$php_name."')");
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_$database_name` WHERE $database_name = '".$lane->$php_name."'"));
				$this->model->query("UPDATE `ngs_lanes` SET `$database_id_name` = ".$id[0]->id." WHERE `id` = $lane_id");
			}else{
				//	Exists
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_$database_name` WHERE $database_name = '".$lane->$php_name."'"));
				$this->model->query("UPDATE `ngs_lanes` SET `$database_id_name` = ".$id[0]->id." WHERE `id` = $lane_id");
			}
		}
	}
	function insert($lane)
	{
		$sql="insert into `ngs_lanes`(`series_id`, `name`, `lane_id`,`cost`,
			`date_submitted`, `date_received`, `phix_requested`,
			`phix_in_lane`, `total_samples`, `resequenced`, `notes`,
			`owner_id`, `group_id`, `perms`, `date_created`,
			`date_modified`, `last_modified_user`)
			values('".$this->model->series_id."','$lane->name','$lane->lane_id','$lane->cost',
			".$this->correct_date($lane->date_submitted).",".$this->correct_date($lane->date_received).",
			'$lane->phix_requested',
			'$lane->phix_in_lane','$lane->total_samples',
			".$this->correct_bool($lane->resequenced).",'$lane->notes',
			'".$this->model->uid."', '".$this->model->gid."', '".$this->model->sid."',
			now(), now(), '".$this->model->uid."');";
		$this->insert++;
		
		$returned_sql = $this->model->query($sql);
		$lane_id = $this->getId($lane);
		
		//	Facilities
		$this->simpleNormalize($lane, 'facility', $lane_id, 'facility', 'facility_id');
		
		return $returned_sql;
	}

	function update($lane)
	{
		$sql="UPDATE `ngs_lanes`
		SET
		`series_id` = '".$this->model->series_id."',
		`lane_id` = '$lane->lane_id',
		`cost` = '$lane->cost',
		`date_submitted` = ".$this->correct_date($lane->date_submitted).",
		`date_received` = ".$this->correct_date($lane->date_received).",
		`phix_requested` = '$lane->phix_requested',
		`phix_in_lane` = '$lane->phix_in_lane',
		`total_samples` = '$lane->total_samples',
		`resequenced` = ".$this->correct_bool($lane->resequenced).",
		`notes` = '$lane->notes',
		`group_id`='".$this->model->gid."',
		`perms`='".$this->model->sid."',
		`date_modified`=now(),
		`last_modified_user`='".$this->model->uid."'
		where `id` = ".$this->getId($lane);
		$this->update++;
		
		$returned_sql = $this->model->query($sql);
		$lane_id = $this->getId($lane);
		
		//	Facilities
		$this->simpleNormalize($lane, 'facility', $lane_id, 'facility', 'facility_id');
		
		return $returned_sql;
	}

}


/* Protocol class */
class prot{}
class protocols extends main{
	private $prot_arr=[];

	function __construct($model, $prot_arr = [])
	{
		$this->prot_arr = $prot_arr;
		$this->model=$model;

		$this->processArr($prot_arr);
	}

	function getStat()
	{
		return "Update:$this->update, Insert:$this->insert";
	}

	function getId($prot)
	{
		$sql="select id from ngs_protocols where `name`='$prot->name'";
		return $this->model->query($sql,1);
	}
	function simpleNormalize($prot, $php_name, $prot_id, $database_name, $database_id_name)
	{
		if($prot->$php_name != NULL || $prot->$php_name != ''){
			$check = "SELECT `id`, `$database_name`
				FROM ngs_$database_name
				WHERE `$database_name` = '".$prot->$php_name."'";
			$check_result = json_decode($this->model->query($check));
			if($check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_$database_name` (`$database_name`) VALUES ('".$prot->$php_name."')");
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_$database_name` WHERE $database_name = '".$prot->$php_name."'"));
				$this->model->query("UPDATE `ngs_protocols` SET `$database_id_name` = ".$id[0]->id." WHERE `id` = $prot_id");
			}else{
				//	Exists
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_$database_name` WHERE $database_name = '".$prot->$php_name."'"));
				$this->model->query("UPDATE `ngs_protocols` SET `$database_id_name` = ".$id[0]->id." WHERE `id` = $prot_id");
			}
		}
	}
	function insert($prot)
	{
		$sql="insert into ngs_protocols(
		`name`, `growth`,
		`extraction`, `library_construction`, `crosslinking_method`,
		`fragmentation_method`, `strand_specific`,
		`owner_id`, `group_id`, `perms`,
		`date_created`, `date_modified`, `last_modified_user`)
		values
		('$prot->name', '$prot->growth',
		'$prot->extraction', '$prot->library_construction', '$prot->crosslinking_method',
		'$prot->fragmentation_method', '$prot->strand_specific',
		'".$this->model->uid."', '".$this->model->gid."', '".$this->model->sid."',
		now(), now(), '".$this->model->uid."');";
		$this->insert++;
		$returned_sql = $this->model->query($sql);
		$prot_id = json_decode($this->getId($prot));
		
		//	Library Strategy
		$this->simpleNormalize($prot, 'library_strategy', $prot_id, 'library_strategy', 'library_strategy_id');
		
		return $returned_sql;
	}

	function update($prot)
	{
		$sql="update ngs_protocols set
		`growth`='$prot->growth',
		`extraction`='$prot->extraction',
		`library_construction`='$prot->library_construction',
		`crosslinking_method`='$prot->crosslinking_method',
		`fragmentation_method`='$prot->fragmentation_method',
		`strand_specific`='$prot->strand_specific',
		`owner_id`='".$this->model->uid."',
		`group_id`='".$this->model->gid."',
		`perms`='".$this->model->sid."',
		`group_id`='".$this->model->gid."',
		`perms`='".$this->model->sid."',
		`date_modified`=now(),
		`last_modified_user`='".$this->model->uid."'
		where `id` = ".$this->getId($prot);
		$this->update++;
		
		$prot_id = json_decode($this->getId($prot));
		$returned_sql = $this->model->query($sql);
		$prot_id = json_decode($this->getId($prot));
		
		//	Library Strategy
		$this->simpleNormalize($prot, 'library_strategy', $prot_id, 'library_strategy', 'library_strategy_id');
		
		return $returned_sql;
	}
}


/* samples class */
class sample{}
class samples extends main{
	private $sample_arr=[];

	function __construct($model, $sample_arr = [])
	{
		$this->sample_arr=$sample_arr;
		$this->model=$model;

		$this->processArr($sample_arr);
	}

	function getSQL()
	{
		return $this->sql;
	}

	function getStat()
	{
		
		return "<script type='text/javascript'>
		var sample_inserts = ".$this->insert . 
		"</script>
		Update:$this->update, Insert:$this->insert";
	}

	function getId($sample)
	{
		$lane_id=$this->getLaneId($sample->lane_name);

		$sql="select `id` from ngs_samples where `name`='".$sample->name."' and `lane_id`='$lane_id' and `series_id`='".$this->model->series_id."'";
		return $this->model->query($sql,1);
	}
	function getLaneId($name)
	{
		$sql="select id from ngs_lanes where `name`='$name' and `series_id`='".$this->model->series_id."'";
		return $this->model->query($sql,1);
	}
	function getProtocolId($name)
	{
		$sql="select id from ngs_protocols where `name`='$name'";
		return $this->model->query($sql,1);
	}
	function simpleNormalize($sample, $php_name, $sample_id, $database_name, $database_id_name)
	{
		if($sample->$php_name != NULL && $sample->$php_name != '' && $sample->$php_name != null && $sample->$php_name != 'null' && $sample->$php_name != 'NULL'){
			$check = "SELECT `id`, `$database_name`
				FROM ngs_$database_name
				WHERE `$database_name` = '".$sample->$php_name."'";
			$check_result = json_decode($this->model->query($check));
			if($check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_$database_name` (`$database_name`) VALUES ('".$sample->$php_name."')");
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_$database_name` WHERE $database_name = '".$sample->$php_name."'"));
				$this->model->query("UPDATE `ngs_samples` SET `$database_id_name` = ".$id[0]->id." WHERE `id` = $sample_id");
			}else{
				//	Exists
				$id = json_decode($this->model->query("SELECT `id` FROM `ngs_$database_name` WHERE $database_name = '".$sample->$php_name."'"));
				$this->model->query("UPDATE `ngs_samples` SET `$database_id_name` = ".$id[0]->id." WHERE `id` = $sample_id");
			}
		}
	}
	function insert($sample)
	{
		$lane_id=$this->getLaneId($sample->lane_name);
		$protocol_id=$this->getProtocolId($sample->protocol_name);
		
		$sql="INSERT INTO `ngs_samples`
		(`series_id`, `protocol_id`, `lane_id`,
		`name`, `barcode`, `title`, `batch_id`,
		`concentration`,
		`description`,
		`avg_insert_size`, `read_length`,
		`adapter`,
		`time`, `biological_replica`,
		`spike_ins`,
		`technical_replica`, `notebook_ref`, `notes`,
		`owner_id`, `group_id`, `perms`, `date_created`,
		`date_modified`, `last_modified_user`)
		VALUES
		(
		'".$this->model->series_id."', '$protocol_id', '$lane_id',
		'$sample->name', '$sample->barcode', '$sample->title',
		'$sample->batch',
		'$sample->concentration',
		'$sample->description',
		'$sample->avg_insert_size', '$sample->read_length',
		'$sample->adapter',
		'$sample->time', '$sample->biological_replica',
		'$sample->spikeins',
		'$sample->technical_replica', '$sample->notebook_ref', '$sample->notes',
		'".$this->model->uid."', '".$this->model->gid."', '".$this->model->sid."',
		now(), now(), '".$this->model->uid."');";
		$this->insert++;
		
		$returned_sql = $this->model->query($sql);
		$sample_id = $this->getId($sample);
		
		//	Conditions
		if($sample->condition != NULL && $sample->condition != '' && $sample->condition != null && $sample->condition != 'null'){
			$conds = explode(",", $sample->condition);
			$conds_symbs = explode(",", $sample->condition_symbol);
			$returned_ids = array();
			$returned_cond = array();
			
			$cond_check="SELECT `id`,`condition`
				FROM ngs_conds
				WHERE `condition` in (";
			foreach($conds as $c){
				if($c == end($conds)){
					$cond_check.="'".trim($c)."'";
				}else{
					$cond_check.="'".trim($c)."',";
				}
			}
			$cond_check.=");";
			$cond_check_result = json_decode($this->model->query($cond_check));
			if(isset($cond_check_result)){
				foreach ($cond_check_result as $key => $object) {
					if(!in_array($object->id, $returned_ids)){
						array_push($returned_ids, $object->id);
						array_push($returned_cond, $object->condition);
					}
				}
			}
			//	ngs_conds
			for($x = 0; $x < count($conds); $x++){
				if(!in_array(trim($conds[$x]), $returned_cond) && isset($cond_check_result)){
					$this->model->query("INSERT INTO `ngs_conds` (`cond_symbol`, `condition`) VALUES ('".trim($conds_symbs[$x])."', '".trim($conds[$x])."')");
					$new_cond_id = $this->model->query("SELECT `id` FROM `ngs_conds` WHERE cond_symbol = '".trim($conds_symbs[$x])."' AND condition = '".trim($conds[$x])."'");
					array_push($returned_ids, $new_cond_id);
					array_push($returned_cond, trim($conds[$x]));
				}
			}
			//	ngs_sample_conds
			foreach($returned_ids as $id){		
				if($this->model->query("SELECT `id` FROM `ngs_sample_conds` WHERE `sample_id` = '".$this->getId($sample)."' AND cond_id = $id") == "[]" && isset($cond_check_result)){		
					$this->model->query("INSERT INTO `ngs_sample_conds` (`sample_id`, `cond_id`) VALUES ('".$this->getId($sample)."', '$id')");		
				}		
			}		
			//	Incorrect ngs_sample_conds
			$all_sample_cond = json_decode($this->model->query("SELECT `cond_id` FROM `ngs_sample_conds` WHERE `sample_id` = '".$this->getId($sample)."'"));		
			foreach($all_sample_cond as $key => $object){		
				if(!in_array($object->cond_id, $returned_ids) && isset($cond_check_result)){		
					$this->model->query("DELETE FROM `ngs_sample_conds` WHERE `sample_id` = '".$this->getId($sample)."' AND `cond_id` = ".$object->cond_id);		
				}		
			}
		}
		
		//	Source
		if($sample->source != NULL && $sample->source != '' && $sample->source != null && $sample->source != 'null'){
			$source_check="SELECT `id`,`source`
				FROM ngs_source
				WHERE `source` = '" . $sample->source . "'";
			$source_check_result = json_decode($this->model->query($source_check));
			if($source_check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_source` (`source`, `source_symbol`) VALUES ('".$sample->source."', '".$sample->source_symbol."')");
				$source_id = json_decode($this->model->query("SELECT `id` FROM `ngs_source` WHERE source = '".$sample->source."'"));
			}else{
				//	Source exists
				$source_id = json_decode($this->model->query("SELECT `id` FROM `ngs_source` WHERE source = '".$sample->source."'"));
				$this->model->query("UPDATE `ngs_samples` SET `source_id` = ".$source_id[0]->id." WHERE `id` = $sample_id");
			}
		}
		
		//	Source
		if($sample->source != NULL && $sample->source != '' && $sample->source != null && $sample->source != 'null'){
			$source_check="SELECT `id`,`source`
				FROM ngs_source
				WHERE `source` = '" . $sample->source . "'";
			$source_check_result = $this->model->query($source_check);
			
			if($source_check_result == "[]"){
				//	Empty
				$this->model->query("INSERT INTO `ngs_source` (`source`, `source_symbol`) VALUES ('".$sample->source."', '".$sample->source_symbol."')");
				$source_id = json_decode($this->model->query("SELECT `id` FROM `ngs_source` WHERE source = '".$sample->source."'"));
				$this->model->query("UPDATE `ngs_samples` SET `source_id` = ".$source_id[0]->id." WHERE `id` = $sample_id");	
			}else{
				//	Source exists
				$source_id = json_decode($this->model->query("SELECT `id` FROM `ngs_source` WHERE source = '".$sample->source."'"));
				$this->model->query("UPDATE `ngs_samples` SET `source_id` = ".$source_id[0]->id." WHERE `id` = $sample_id");
			}
		}
		
		//	Organism
		$this->simpleNormalize($sample, 'organism', $sample_id, 'organism', 'organism_id');
		
		//	Genotype
		$this->simpleNormalize($sample, 'genotype', $sample_id, 'genotype', 'genotype_id');
		
		//	Molecule
		$this->simpleNormalize($sample, 'molecule', $sample_id, 'molecule', 'molecule_id');
		
		//	Library Type
		$this->simpleNormalize($sample, 'lib_type', $sample_id, 'library_type', 'library_type_id');
		
		//	Donor
		$this->simpleNormalize($sample, 'donor', $sample_id, 'donor', 'donor_id');
		
		//	Biosample Type
		$this->simpleNormalize($sample, 'biosample_type', $sample_id, 'biosample_type', 'biosample_type_id');
		
		//	Instrument Model
		$this->simpleNormalize($sample, 'instrument_model', $sample_id, 'instrument_model', 'instrument_model_id');
		
		//	Treatment Manufacturer
		$this->simpleNormalize($sample, 'treatment_manufacturer', $sample_id, 'treatment_manufacturer', 'treatment_manufacturer_id');
		
		//	Antibody Target
		if($sample->target != NULL){
			if($sample->target != NULL && $sample->target != '' && $sample->target != null && $sample->target != 'null'){
				$check = "SELECT `id`, `target`
					FROM ngs_antibody_target
					WHERE `target` = '".$sample->target."'";
				$check_result = json_decode($this->model->query($check));
				if($check_result == array()){
					//	Empty
					$this->model->query("INSERT INTO `ngs_antibody_target` (`target`) VALUES ('".$sample->target."')");
					$id = json_decode($this->model->query("SELECT `id` FROM `ngs_antibody_target` WHERE target = '".$sample->target."'"));
					$this->model->query("UPDATE `ngs_samples` SET `target_id` = ".$id[0]->id." WHERE `id` = $sample_id");
				}else{
					//	Exists
					$id = json_decode($this->model->query("SELECT `id` FROM `ngs_antibody_target` WHERE target = '".$sample->target."'"));
					$this->model->query("UPDATE `ngs_samples` SET `target_id` = ".$id[0]->id." WHERE `id` = $sample_id");
				}
			}
		}
		
		$DC_PROJECT = json_decode($this->model->query("SELECT experiment_name FROM ngs_experiment_series WHERE `id` = ".$this->model->series_id));
		
		//	Samplename
		if(strtolower($DC_PROJECT[0]->experiment_name) == 'dendritic cell transcriptional landscape'){
			$this->model->query("UPDATE `ngs_samples` SET `samplename` = '".$sample->samplename."' WHERE `id` = $sample_id");
		}else{
			$this->model->query("UPDATE `ngs_samples` SET `samplename` = '".$sample->name."' WHERE `id` = $sample_id");
		}
		
		return $returned_sql;
	}

	function update($sample)
	{
		$lane_id=$this->getLaneId($sample->lane_name);
		$protocol_id=$this->getProtocolId($sample->protocol_name);

		$sql="UPDATE `ngs_samples`
		SET
		`series_id` = '".$this->model->series_id."',
		`protocol_id` = '$protocol_id',
		`lane_id` = '$lane_id',
		`name` = '$sample->name',
		`barcode` = '$sample->barcode',
		`title` = '$sample->title',
		`batch_id` = '$sample->batch',
		`concentration` = '$sample->concentration',
		`description` = '$sample->description',
		`avg_insert_size` = '$sample->avg_insert_size',
		`read_length` = '$sample->read_length',
		`adapter` = '$sample->adapter',
		`time` = '$sample->time',
		`biological_replica` = '$sample->biological_replica',
		`spike_ins` = '$sample->spikeins',
		`technical_replica` = '$sample->technical_replica',
		`notebook_ref` = '$sample->notebook_ref',
		`notes` = '$sample->notes',
		`group_id`='".$this->model->gid."',
		`perms`='".$this->model->sid."',
		`date_modified`=now(),
		`last_modified_user`='".$this->model->uid."'
		where `id` = ".$this->getId($sample);
		$this->update++;
		
		$returned_sql = $this->model->query($sql);
		$sample_id = $this->getId($sample);
		
		//	Conditions
		if($sample->condition != NULL && $sample->condition != '' && $sample->condition != null && $sample->condition != 'null'){
			$conds = explode(",", $sample->condition);
			$conds_symbs = explode(",", $sample->condition_symbol);
			$returned_ids = array();
			$returned_cond = array();
			
			$cond_check="SELECT `id`,`condition`
				FROM ngs_conds
				WHERE `condition` in (";
			foreach($conds as $c){
				if($c == end($conds)){
					$cond_check.="'".trim($c)."'";
				}else{
					$cond_check.="'".trim($c)."',";
				}
			}
			$cond_check.=");";
			$cond_check_result = json_decode($this->model->query($cond_check));
			if(isset($cond_check_result)){
				foreach ($cond_check_result as $key => $object) {
					if(!in_array($object->id, $returned_ids)){
						array_push($returned_ids, $object->id);
						array_push($returned_cond, $object->condition);
					}
				}
			}
			//	ngs_conds
			for($x = 0; $x < count($conds); $x++){
				if(!in_array(trim($conds[$x]), $returned_cond) && isset($cond_check_result)){
					$this->model->query("INSERT INTO `ngs_conds` (`cond_symbol`, `condition`) VALUES ('".trim($conds_symbs[$x])."', '".trim($conds[$x])."')");
					$new_cond_id = $this->model->query("SELECT `id` FROM `ngs_conds` WHERE cond_symbol = '".trim($conds_symbs[$x])."' AND condition = '".trim($conds[$x])."'");
					array_push($returned_ids, $new_cond_id);
					array_push($returned_cond, trim($conds[$x]));
				}
			}
			//	ngs_sample_conds
			foreach($returned_ids as $id){		
				if($this->model->query("SELECT `id` FROM `ngs_sample_conds` WHERE `sample_id` = '".$this->getId($sample)."' AND cond_id = $id") == "[]" && isset($cond_check_result)){		
					$this->model->query("INSERT INTO `ngs_sample_conds` (`sample_id`, `cond_id`) VALUES ('".$this->getId($sample)."', '$id')");		
				}		
			}		
			//	Incorrect ngs_sample_conds
			$all_sample_cond = json_decode($this->model->query("SELECT `cond_id` FROM `ngs_sample_conds` WHERE `sample_id` = '".$this->getId($sample)."'"));		
			foreach($all_sample_cond as $key => $object){		
				if(!in_array($object->cond_id, $returned_ids) && isset($cond_check_result)){		
					$this->model->query("DELETE FROM `ngs_sample_conds` WHERE `sample_id` = '".$this->getId($sample)."' AND `cond_id` = ".$object->cond_id);		
				}		
			}
		}
		
		//	Source
		if($sample->source != NULL && $sample->source != '' && $sample->source != null && $sample->source != 'null'){
			$source_check="SELECT `id`,`source`
				FROM ngs_source
				WHERE `source` = '" . $sample->source . "'";
			$source_check_result = json_decode($this->model->query($source_check));
			if($source_check_result == array()){
				//	Empty
				$this->model->query("INSERT INTO `ngs_source` (`source`, `source_symbol`) VALUES ('".$sample->source."', '".$sample->source_symbol."')");
				$source_id = json_decode($this->model->query("SELECT `id` FROM `ngs_source` WHERE source = '".$sample->source."'"));
			}else{
				//	Source exists
				$source_id = json_decode($this->model->query("SELECT `id` FROM `ngs_source` WHERE source = '".$sample->source."'"));
				$this->model->query("UPDATE `ngs_samples` SET `source_id` = ".$source_id[0]->id." WHERE `id` = $sample_id");
			}
		}
		
		//	Organism
		$this->simpleNormalize($sample, 'organism', $sample_id, 'organism', 'organism_id');
		
		//	Genotype
		$this->simpleNormalize($sample, 'genotype', $sample_id, 'genotype', 'genotype_id');
		
		//	Molecule
		$this->simpleNormalize($sample, 'molecule', $sample_id, 'molecule', 'molecule_id');
		
		//	Library Type
		$this->simpleNormalize($sample, 'lib_type', $sample_id, 'library_type', 'library_type_id');
		
		//	Donor
		$this->simpleNormalize($sample, 'donor', $sample_id, 'donor', 'donor_id');
		
		//	Biosample Type
		$this->simpleNormalize($sample, 'biosample_type', $sample_id, 'biosample_type', 'biosample_type_id');
		
		//	Instrument Model
		$this->simpleNormalize($sample, 'instrument_model', $sample_id, 'instrument_model', 'instrument_model_id');
		
		//	Treatment Manufacturer
		$this->simpleNormalize($sample, 'treatment_manufacturer', $sample_id, 'treatment_manufacturer', 'treatment_manufacturer_id');
		
		//	Antibody Target
		if($sample->target != NULL){
			if($sample->target != NULL && $sample->target != '' && $sample->target != null && $sample->target != 'null'){
				$check = "SELECT `id`, `target`
					FROM ngs_antibody_target
					WHERE `target` = '".$sample->target."'";
				$check_result = json_decode($this->model->query($check));
				if($check_result == array()){
					//	Empty
					$this->model->query("INSERT INTO `ngs_antibody_target` (`target`) VALUES ('".$sample->target."')");
					$id = json_decode($this->model->query("SELECT `id` FROM `ngs_antibody_target` WHERE target = '".$sample->target."'"));
					$this->model->query("UPDATE `ngs_samples` SET `target_id` = ".$id[0]->id." WHERE `id` = $sample_id");
				}else{
					//	Exists
					$id = json_decode($this->model->query("SELECT `id` FROM `ngs_antibody_target` WHERE target = '".$sample->target."'"));
					$this->model->query("UPDATE `ngs_samples` SET `target_id` = ".$id[0]->id." WHERE `id` = $sample_id");
				}
			}
		}
		
		$DC_PROJECT = json_decode($this->model->query("SELECT experiment_name FROM ngs_experiment_series WHERE `id` = ".$this->model->series_id));
		
		//	Samplename
		if(strtolower($DC_PROJECT[0]->experiment_name) == 'dendritic cell transcriptional landscape'){
			$this->model->query("UPDATE `ngs_samples` SET `samplename` = '".$sample->samplename."' WHERE `id` = $sample_id");
		}else{
			$this->model->query("UPDATE `ngs_samples` SET `samplename` = '".$sample->name."' WHERE `id` = $sample_id");
		}
		
		return $returned_sql;
	}
}



/* characteristics class */
class tag{}
class characteristics extends main{
	private $char_arr=[];

	function __construct($model, $char_arr = [])
	{
		$this->char_arr=$char_arr;
		$this->model=$model;

		$this->processArr($char_arr);
	}

	function getStat()
	{
		return "Update:$this->update, Insert:$this->insert";
	}

	function getSampleId($name)
	{
		$sql="select id from ngs_samples where `name`='$name' and `series_id`='$this->model->series_id'";
		return $this->model->query($sql,1);
	}

	function getId($tag)
	{
		$sample_id = $this->getSampleId($tag->sample_name);
		$sql="select id from ngs_characteristics where `sample_id` = $sample_id and `tag`='$tag->tag'";
		return $this->model->query($sql,1);
	}

	function insert($tag)
	{
		$sample_id = $this->getSampleId($tag->sample_name);
		$sql=" INSERT INTO `ngs_characteristics`(`sample_id`, `tag`,`value`,
		`owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`)
		VALUES('$sample_id','$tag->tag', '$tag->value',
		'".$this->model->uid."', '".$this->model->gid."', '".$this->model->sid."',
		now(), now(), '".$this->model->uid."');";
		$this->insert++;
		//return $sql;
		return $this->model->query($sql);
	}

	function update($tag)
	{
		$sql="update `ngs_characteristics` set `value`='$tag->value',
		`group_id`='".$this->model->gid."', `perms`='".$this->model->sid."',
		`date_modified`=now(), `last_modified_user`='".$this->model->uid."'
		where `id` = ".$this->getId($tag);
		$this->update++;

		return $this->model->query($sql);
	}
}

/* files class */
class file{}
class files extends main{
	private $files_arr=[];
	private $sample_arr=[];
	public $sample_id;
	private $lane_id;
	private $tablename;
	private $fieldname;
	private $value;
	private $dir_id;

	function __construct($model, $files_arr = [], $sample_arr=[])
	{
		$this->files_arr=$files_arr;
		$this->sample_arr=$sample_arr;
		$this->model=$model;

		$this->processArr($files_arr);
	}

	function getStat()
	{
		return "Update:$this->update, Insert:$this->insert";
	}

	function getLaneId($name)
	{
		$sql="select id from ngs_lanes where `name`='$name' and `series_id`='".$this->model->series_id."'";
		return $this->model->query($sql,1);
	}
	function getLaneIdFromSample($name){
		$lane_name=$this->sample_arr[$name]->lane_name;
		$sql="SELECT id FROM ngs_lanes where name='$lane_name' and `series_id`='".$this->model->series_id."'";
		return $this->model->query($sql,1);
	}
	function getSampleId($name)
	{
		$testsql="select id from ngs_lanes where `name`='$name' and `series_id`='".$this->model->series_id."'";
		$laneresult = $this->model->query($testsql,1);
		if($laneresult == '[]'){
			$lane_id=$this->getLaneIdFromSample($name);
			$sql="select id from ngs_samples where `name`='$name' and `lane_id`='$lane_id' and `series_id`='".$this->model->series_id."'";
			return $this->model->query($sql,1);
		}else{
			return 0;
		}
	}
	function getDirId($fastq_dir, $backup_dir)
	{
		$sql="select id from ngs_dirs where `fastq_dir`='$fastq_dir' and `backup_dir` = '$backup_dir'";
		return $this->model->query($sql,1);
	}

	function getId($file)
	{
		$this->sample_id = $this->getSampleId($file->name);
		$this->lane_id = ($this->sample_id==0 ? $this->getLaneId($file->name) : $this->getLaneIdFromSample($file->name));
		$this->dir_id = $this->getDirId($file->fastq_dir, $file->backup_dir);
		if ($this->sample_id>0)
		{
			$this->tablename="ngs_temp_sample_files";
			$this->fieldname="sample_id";
			$this->value=$this->sample_id;
			
			$sql="select id
				from `$this->tablename`
				where `file_name`='$file->file_name'
				and `sample_id`='$this->sample_id'
				and `dir_id` = $this->dir_id";
			return $this->model->query($sql,1);
		}
		else
		{
			$this->tablename="ngs_temp_lane_files";
			$this->fieldname="lane_id";
			$this->value=$this->lane_id;
			
			$sql="select id
				from `$this->tablename`
				where `file_name`='$file->file_name'
				and `lane_id`='$this->lane_id'
				and `dir_id` = $this->dir_id";
			return $this->model->query($sql,1);
		}
		
	}

	function insert($file)
	{
		require_once("api/funcs.php");
		$funcs = new funcs();
		
		//	Gather information to remove success files
		$clusteruser = json_decode($this->model->query("SELECT clusteruser FROM users WHERE id = '".$_SESSION['uid']."'"));
		$samplename=json_decode($this->model->query("SELECT samplename FROM ngs_samples WHERE id = ".$this->getSampleId($file->name)));
		$outdirs = json_decode($this->model->query("SELECT outdir FROM ngs_runparams WHERE id in (SELECT run_id FROM ngs_runlist WHERE sample_id = ".$this->getSampleId($file->name).")"));
		//	Remove success files
		foreach($outdirs as $o){
			$data = $funcs->removeAllSampleSuccessFiles($o->outdir, [$samplename[0]->samplename], $clusteruser[0]->clusteruser);
		}
		//	Remove fastq_file info
		$this->model->query("DELETE FROM ngs_fastq_files WHERE sample_id = ".$this->getSampleId($file->name));
		
		$sql="INSERT INTO `$this->tablename`
		(`file_name`,
		`$this->fieldname`, `dir_id`,
		`owner_id`, `group_id`, `perms`,
		`date_created`, `date_modified`,`last_modified_user`)
		VALUES
		('$file->file_name', '$this->value',
		'$this->dir_id', '".$this->model->uid."', '".$this->model->gid."', '".$this->model->sid."',
		now(), now(), '".$this->model->uid."');";
		$this->insert++;
		//return $sql;
		
		return $this->model->query($sql);
	}

	function update($file)
	{
		$original_file_name = $this->model->query("SELECT `file_name` FROM `$this->tablename` WHERE
												`$this->fieldname` = `$this->value` and `file_name` = `$file->file_name`");
		$sql="update `$this->tablename` set
		`$this->fieldname`='$this->value', `dir_id`='$this->dir_id',
		`group_id`='".$this->model->gid."', `perms`='".$this->model->sid."',
		`date_modified`=now(), `last_modified_user`='".$this->model->uid."'
		where `id` = ".$this->getId($file);
		$this->update++;

		return $this->model->query($sql);
	}
}

/* diretories for the files class */
class dir{}
class dirs extends main{
	private $amazon_bucket;
	
	function __construct($model, $dir_arr = [], $backup_dir, $amazon_bucket)
	{
		$this->model=$model;
		$this->dir_arr=$dir_arr;
		$this->backup_dir = $backup_dir;
		$this->amazon_bucket = $amazon_bucket;
		$this->processArr($dir_arr);
	}

	function getSQL()
	{
		return $this->sql;
	}

	function getStat()
	{
		return "Update:$this->update, Insert:$this->insert";
	}

	function getId($dir)
	{
		$sql="select id from ngs_dirs where `fastq_dir`='".$dir->fastq_dir."' and `backup_dir` = '" . $this->backup_dir."'";
		return $this->model->query($sql,1);
	}

	function insert($dir)
	{
		$sql=" INSERT INTO `ngs_dirs`(`fastq_dir`,`backup_dir`, `amazon_bucket`,
		`owner_id`, `group_id`, `perms`,
		`date_created`, `date_modified`, `last_modified_user`)
		VALUES('".$dir->fastq_dir."', '".$this->backup_dir."', '".$this->amazon_bucket."',
		'".$this->model->uid."', '".$this->model->gid."', '".$this->model->sid."',
		now(), now(), '".$this->model->uid."');";
		$this->insert++;
		
		return $this->model->query($sql);
	}

	function update($dir)
	{
		$sql="update `ngs_dirs` set
		`backup_dir`='".$this->backup_dir."', `amazon_bucket`='".$this->amazon_bucket."',
		`group_id`='".$this->model->gid."', `perms`='".$this->model->sid."',
		`date_modified`=now(), `last_modified_user`='".$this->model->uid."'
		where `id` = ".$this->getId($dir);
		$this->update++;
		return $this->model->query($sql);
	}
}

?>
