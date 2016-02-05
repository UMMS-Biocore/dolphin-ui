<?php

class StatController extends VanillaController {
	private $username;
	
	function beforeAction() {
        
	}
	
	function index(){
	}
	
	function status(){
		$this->set('field','NGS Excel Import');
		$this->username=$_SESSION['user'];
		$this->set('title','NGS Excel Import');
		$this->set('groups',$this->Stat->getGroups($this->username));
        
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Stat->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
	}

    function advstatus(){
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Stat->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
        
        $this->set('field', 'Status');
    }
	
	function afterAction(){
		
	}
}