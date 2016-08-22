<?php

class StatController extends VanillaController {
	private $username;
	
	function beforeAction() {
        
	}
	
	function index(){
	}
	
	function status(){
		$this->set('field','Status');
		$this->username=$_SESSION['user'];
		$this->set('title','Status');
		$this->set('groups',$this->Stat->getGroups($this->username));
        
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Stat->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
	}

    function advstatus(){
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Stat->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
        $this->set('field', 'Adv Status');
		$run_id = $this->Stat->getRunId();
		$this->set('run_id', $run_id);
		$this->set('outdir', $this->Stat->getRunDirectory($run_id));
    }
	
	function reroute($run_id){
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Stat->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
        $this->set('field', 'Reroute');
		$this->set('run_id', $run_id);
	}
	
	function afterAction(){
		
	}
}