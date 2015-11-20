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
		if(isset($_SESSION['fastlane_values'])){$fastlane_values = $_SESSION['fastlane_values'];}
		if(isset($_SESSION['barcode_array'])){$barcode_array = $_SESSION['barcode_array'];}
		if(isset($_SESSION['pass_fail_values'])){$pass_fail_values = $_SESSION['pass_fail_values'];}
		if(isset($_SESSION['bad_samples'])){$bad_samples = $_SESSION['bad_samples'];}
		if(isset($_SESSION['group_selected'])){$group_selected = $_SESSION['group_selected'];}
		
		if(isset($_SESSION['fastlane_values'])){
			$fastlane_array = explode(",",$fastlane_values);
			$pass_fail_array = explode(",",$pass_fail_values);
			$bad_samples_array = explode(",",$bad_samples);
			
			$fastlane_values = str_replace("\n", ":", $fastlane_values);
		}
		if($pass_fail_array != []){
			if($pass_fail_array[0] == "true" || $pass_fail_array == "false"){
				$text.= "<h4>Errors found during submission:</h4><br>";
			}else{
				$text.= "<h4>Successful Fastlane submission!</h4><br>";
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
						$text.="Input Directory is either empty or contains improper white space.<br><br>";
						$text.="You may not have the proper credentials within the cluster!!!<br>";
						$text.="If you're not sure if you have cluster access, visit <a href='http://umassmed.edu/biocore/resources/galaxy-group/'>this website</a> for more help.<br><br>";
						$text.="If you're already within the cluster, run this script to give dolphin access to your user account to run on your behalf:<br>";
						$text.="/project/umw_biocore/bin/addKey.bash <br>";
					}else if($key == 6){
						$text.="There was an error with the file information you sent us.<br>";
						$text.="Check to make sure that the file names are correct.<br><br>";
						$text.="You may not have the proper credentials within the cluster!!!<br>";
						$text.="If you're not sure if you have cluster access, visit <a href='http://umassmed.edu/biocore/resources/galaxy-group/'>this website</a> for more help.<br><br>";
						$text.="If you're already within the cluster, run this script to give dolphin access to your user account to run on your behalf:<br>";
						$text.="/project/umw_biocore/bin/addKey.bash <br>";
					}else if($key == 7){
						$text.="Backup directory is either empty or contains improper white space.<br><br>";
						$text.="You may not have the proper credentials within the cluster!!!<br>";
						$text.="If you're not sure if you have cluster access, visit <a href='http://umassmed.edu/biocore/resources/galaxy-group/'>this website</a> for more help.<br><br>";
						$text.="If you're already within the cluster, run this script to give dolphin access to your user account to run on your behalf:<br>";
						$text.="/project/umw_biocore/bin/addKey.bash <br>";
					}else if($key >= 9){
						$database_sample_bool = true;
					}
				}else if($index != 'true' && $index != 'false'){
					$text.= "Sample created with id #".$index."<br><br>";
				}
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