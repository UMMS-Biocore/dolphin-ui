<?php

class FastlaneController extends VanillaController {
	private $username;
	
	function beforeAction() {

	}
 
	function index() {
		$this->set('field', "Fastlane");
		$this->username=$_SESSION['user'];
		$this->set('title','NGS Fastlane');
		
		$this->set('uid', $_SESSION['uid']);
		$this->set('groups',$this->Fastlane->getGroups($this->username));
        $gids = $this->Fastlane->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
	}
	
	function process() {
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Fastlane->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
		$pass_fail_array = [];
		
		$text = '';
		$bad_samples = '';
		$bad_files_array = [];
		$failed_test = false;
		if(isset($_SESSION['fastlane_values'])){$fastlane_values = $_SESSION['fastlane_values'];}
		if(isset($_SESSION['barcode_array'])){$barcode_array = $_SESSION['barcode_array'];}
		if(isset($_SESSION['pass_fail_values'])){$pass_fail_values = $_SESSION['pass_fail_values'];}
		if(isset($_SESSION['bad_files'])){$bad_files = $_SESSION['bad_files'];}
		if(isset($_SESSION['bad_samples'])){$bad_samples = $_SESSION['bad_samples'];}
		if(isset($_SESSION['dir_used'])){$dir_used = $_SESSION['dir_used'];}
		if(isset($_SESSION['fastlane_values'])){
			if(isset($fastlane_values)){
				$fastlane_values = str_replace("\n", ":", $fastlane_values);
				$fastlane_array = explode(",",$fastlane_values);
			}
			if(isset($pass_fail_values)){
				$pass_fail_array = explode(",",$pass_fail_values);
			}
			if(isset($bad_samples)){
				$bad_samples_array = explode(",",$bad_samples);
			}
			if(isset($bad_files)){
				$bad_files_array = explode(",", $bad_files);
			}
		}
		if($pass_fail_array != []){
			if($pass_fail_array[0] == "true" || $pass_fail_array == "false"){
				$text.= "<h3>Errors found during submission:</h3><br>";
				$text.="<script type='text/javascript'>";
				$text.="var initialSubmission = undefined;";
				if(isset($_SESSION['barcode_array'])){$text.="var barcode_array = undefined;";}
				$text.="</script>";
			}else{
				$text.= "<h3>Successful Fastlane submission!</h3><br>";
				$text.= "Don't forget to add more information about your samples!<br><br>";
				$text.="<script type='text/javascript'>";
				$text.="var initialSubmission = '" . $fastlane_values . "';";
				if(isset($_SESSION['barcode_array'])){$text.="var barcode_array = '" . $barcode_array . "';";}
				$text.="</script>";
			}
			$database_sample_bool = false;
			foreach($pass_fail_array as $key=>$index){
				if($index == 'false'){
					if($key == 1){
						$text.="<font color=\"red\">Barcode selection is either empty, not properly formatted, or does not match the number of samples given.</font><br><br>";
					}else if($key == 3){
						$text.="<font color=\"red\">Experiment Series field is empty or contains improper characters. Please use alpha-numerics, underscores, spaces, and dashes only.</font><br><br>";
					}else if($key == 4){
						$text.="<font color=\"red\">Import field is either empty or contains improper characters. Please use alpha-numerics, underscores, spaces, and dashes only.</font><br><br>";
					}else if($key == 5){
						$text.="<h3>Input Directory</h3>";
						if($fastlane_array[6]  == ''){
							$text.="<font color=\"red\">Input Directory is Empty</font><br><br>";
						}else{
							$text.="Input Directory either contains improper white space or you do not have permissions to access it:<br>";
							$text.="<font color=\"red\">".$fastlane_array[6]."<br>Please make sure to list the full path to the files. Please use alpha-numerics, underscores, dashes, backslashes and periods only.</font><br><br>";
						}
					}else if($key == 6){
						$text.="<h3>Files</h3>";
						$text.="There was an error with the file information:<br>";
						if(count($bad_files_array) > 0){
							foreach($bad_files_array as $bfa){
								$text.="<font color=\"red\">".$bfa."</font><br>";
							}
							$text.="<br>";
						}
						if(isset($dir_used)){
								$text.="<font color=\"red\">Directory given file selection contains an error. If using paired end reads, please make sure that both reads are selected.<br>
								Order is important for multiple file selection for one sample name.  Make sure your sample names contain alpha-numerics, underscores, and dashes only.</font><br><br>"; 
						}else{
								$text.="<font color=\"red\">If the files given are not in the proper fastlane format, please use alpha-numerics, underscores, and dashes only.</font><br><br>";
						}
					}else if($key == 7){
						$text.="<h3>Process Directory</h3>";
						if($fastlane_array[8]  == ''){
							$text.="<font color=\"red\">Process Directory is Empty</font><br><br>";
						}else{
							$text.="Process Directory either contains one of the following:<br>
									-- Improper white space/characters.<br>
									-- You do not have permissions to access it.<br>
									-- The processed directory is being used by another import.<br>For:";
							$text.="<font color=\"red\">".$fastlane_array[8]."<br><br>
									Please make sure to list the full path, use alpha-numerics, underscores, dashes, backslashes and periods only,
									and that the directory is not currently being used by another import before resubmitting.</font><br><br>";
						}
					}else if($key >= 9){
						$database_sample_bool = true;
					}
					$failed_test = true;
				}else if($index != 'true' && $index != 'false'){
					$text.= "Sample created with id #".$index."<br><br>";
				}
			}
			if($database_sample_bool == true){
				$text.="Samples given are already contained within the database:<br>";
				foreach($bad_samples_array as $bad){
					$text.="Sample with name: <font color=\"red\">".$bad."</font><br>";
				}
			}
			if($failed_test){
				$text.="If you're not sure if you have cluster access, visit <a href='http://umassmed.edu/biocore/resources/galaxy-group/'>this website</a> for more help.<br><br>";
				$text.="For all additional questions about fastlane, please see our <a href=\"http://dolphin.readthedocs.org/en/master/dolphin-ui/fastlane.html\">documentation</a><br><br>";
			}
			if($index != 'true' && $index != 'false'){
				$text.='<div class="callout callout-info lead"><h4>We are currently processing your samples to obtain read counts and additional information.<br><br>
						You can check the status of these initial runs on your NGS Status page.</h4></div>';
			}
		}
		$text.="<br><br>";
		$text.= '<div>
				<input type="button" class="btn btn-primary" value="Return to Fastlane" onclick="backToFastlane()">
				<input type="button" class="btn btn-primary" value="Go to Status" onclick="sendToStatus()">
				</div>';
		$this->set('mytext', $text);
		unset($_SESSION['fastlane_values']);
		unset($_SESSION['bar_distance']);
		unset($_SESSION['pass_fail_values']);
		unset($_SESSION['bad_samples']);
		unset($_SESSION['bad_files']);
		unset($_SESSION['dir_used']);
	}
	
	function afterAction(){
		
	}
}