<?php
 
class GeoimportController extends VanillaController {

    function beforeAction() {

    }
 
    function index() {
		$this->username=$_SESSION['user'];
		$this->set('title','Geo Import');
		$this->set('field','Geo Import');
		$this->set('uid', $_SESSION['uid']);
		$this->set('groups',$this->Geoimport->getGroups($this->username));
        $gids = $this->Geoimport->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
    }

	function process() {
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Geoimport->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
		$text = '';
		
		if(isset($_SESSION['geo_values'])){$geo_values = $_SESSION['geo_values'];}
		if(isset($_SESSION['geo_values'])){
			if(isset($geo_values)){
				$geo_values = str_replace("\n", ":", $geo_values);
			}
		}
		
		$text.= "<h3>Successful GEO submission!</h3><br><br>";
		$text.="<script type='text/javascript'>";
		if(isset($geo_values)){
			$text.="var initialSubmission = '" . $geo_values . "';";
		}
		$text.="</script>";
			
		$text.='<div class="callout callout-info lead"><h4>We are currently processing your samples to obtain read counts and additional information.<br><br>
				You can check the status of these initial runs on your NGS Status page.</h4></div>';
		$text.="<br><br>";
		$text.= '<div>
				<input type="button" class="btn btn-primary" value="Return to GEO Submission" onclick="backToGeo()">
				<input type="button" class="btn btn-primary" value="Go to Status" onclick="sendToStatus()">
				</div>';
		$this->set('mytext', $text);
		unset($_SESSION['geo_values']);
	}
	
    function afterAction() {

    }

}
