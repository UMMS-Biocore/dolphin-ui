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
		
		if(isset($_SESSION['fastlane_values'])){$fastlane_values = $_SESSION['fastlane_values'];}
		if(isset($_SESSION['barcode_array'])){$barcode_array = $_SESSION['barcode_array'];}
		if(isset($_SESSION['fastlane_values'])){
			if(isset($fastlane_values)){
				$fastlane_values = str_replace("\n", ":", $fastlane_values);
				$fastlane_array = explode(",",$fastlane_values);
			}
		}
		
		$text.= "<h3>Successful Fastlane submission!</h3><br>";
		$text.= "Don't forget to add more information about your samples!<br><br>";
		$text.="<script type='text/javascript'>";
		$text.="var initialSubmission = '" . $fastlane_values . "';";
		if(isset($_SESSION['barcode_array'])){$text.="var barcode_array = '" . $barcode_array . "';";}
		$text.="</script>";
			
		$text.='<div class="callout callout-info lead"><h4>We are currently processing your samples to obtain read counts and additional information.<br><br>
				You can check the status of these initial runs on your NGS Status page.</h4></div>';
		$text.="<br><br>";
		$text.= '<div>
				<input type="button" class="btn btn-primary" value="Return to Fastlane" onclick="backToFastlane()">
				<input type="button" class="btn btn-primary" value="Go to Status" onclick="sendToStatus()">
				</div>';
		$this->set('mytext', $text);
		unset($_SESSION['fastlane_values']);
		unset($_SESSION['barcode_array']);
		unset($_SESSION['pass_fail']);
	}
	
	function afterAction(){
		
	}
}