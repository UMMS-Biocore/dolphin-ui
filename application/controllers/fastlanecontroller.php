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
		if(isset($_SESSION['bad_samples'])){$bad_samples = $_SESSION['bad_samples'];}
		if(isset($_SESSION['dir_used'])){$dir_used = $_SESSION['dir_used'];}
		if(isset($_SESSION['error_out'])){$error_out = $_SESSION['error_out'];}
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
			if(isset($error_out)){
				$error_out_array = explode(",", $error_out);
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
				foreach($error_out_array as $error){
					$text .= '<font color="red">' . $error . '</font><br><br>';
				}
				$text .= '<br>';
				/*
				$text.="If you're not sure if you have cluster access, visit <a href='http://umassmed.edu/biocore/resources/galaxy-group/'>this website</a> for more help.<br><br>";
				$text.="For all additional questions about fastlane, please see our <a href=\"http://dolphin.readthedocs.org/en/master/dolphin-ui/fastlane.html\">documentation</a><br><br>";
				*/
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
		unset($_SESSION['error_out']);
	}
	
	function afterAction(){
		
	}
}