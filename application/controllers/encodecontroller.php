<?php

class EncodeController extends VanillaController {

	function beforeAction() {
        
	}
	
	function index() {
		$this->set('field', "Encode");
		
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Encode->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
	}
	
	function afterAction() {
        
	}

}