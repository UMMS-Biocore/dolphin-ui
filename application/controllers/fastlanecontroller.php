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
		$failed_test = false;
		if(isset($_SESSION['fastlane_values'])){$fastlane_values = $_SESSION['fastlane_values'];}
		if(isset($_SESSION['barcode_array'])){$barcode_array = $_SESSION['barcode_array'];}
		if(isset($_SESSION['pass_fail_values'])){$pass_fail_values = $_SESSION['pass_fail_values'];}
		if(isset($_SESSION['bad_files'])){$bad_files = $_SESSION['bad_files'];}
		if(isset($_SESSION['bad_samples'])){$bad_samples = $_SESSION['bad_samples'];}
		if(isset($_SESSION['fastlane_values'])){
			if(isset($fastlane_values)){
				$fastlane_values = str_replace("\n", ":", $fastlane_values);
				$fastlane_array = explode(",",$fastlane_values);
			}
			if(isset($barcode_array)){
				$pass_fail_array = explode(",",$pass_fail_values);
			}
			if(isset($pass_fail_values)){
				$bad_samples_array = explode(",",$bad_samples);
			}
			if(isset($bad_files)){
				$bad_files_array = explode(",", $bad_files);
			}
		}
		if($pass_fail_array != []){
			if($pass_fail_array[0] == "true" || $pass_fail_array == "false"){
				$text.= "<h3>Errors found during submission:</h3><br>";
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
						$text.="Barcode selection is either empty or not properly formatted<br>";
					}else if($key == 3){
						$text.="Experiment Series field is empty<br>";
					}else if($key == 4){
						$text.="Experiment field is either empty or contains improper white space<br>";
					}else if($key == 5){
						$text.="<h3>Input Directory</h3>";
						if($fastlane_array[6]  == ''){
							$text.="<font color=\"red\">Input Directory is Empty</font><br><br>";
						}else{
							$text.="Input Directory either contains improper white space or you do not have permissions to access it:<br>";
							$text.="<font color=\"red\">".$fastlane_array[6]."</font><br><br>";
						}
					}else if($key == 6){
						$text.="<h3>Files</h3>";
						$text.="There was an error with the file information:<br>";
						if(count($bad_files_array) > 0){
							foreach($bad_files_array as $bfa){
								$text.="<font color=\"red\">".$bfa."</font><br>";
							}
							$text.="<br>";
						}else{
							$text.="<font color=\"red\">The files listed are not in the proper fastlane format.</font><br><br>";
						}
					}else if($key == 7){
						$text.="<h3>Process Directory</h3>";
						if($fastlane_array[8]  == ''){
							$text.="<font color=\"red\">Process Directory is Empty</font><br><br>";
						}else{
							$text.="Process Directory either contains improper white space or you do not have permissions to access it:<br>";
							$text.="<font color=\"red\">".$fastlane_array[8]."</font><br><br>";
						}
					}else if($key >= 9){
						$database_sample_bool = true;
					}
					$failed_test = true;
				}else if($index != 'true' && $index != 'false'){
					$text.= "Sample created with id #".$index."<br><br>";
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
			
			if($database_sample_bool == true){
				$text.="Samples given are already contained within the database:<br>";
				foreach($bad_samples_array as $bad){
					$text.="Sample with name: ".$bad."<br>";
				}
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
	}
	
	function afterAction(){
		
	}
}