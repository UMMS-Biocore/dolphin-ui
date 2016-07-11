<?php

class PipelineController extends VanillaController {
	private $username;
	
	function beforeAction() {

	}

	function index() {
		$this->set('field', "Pipeline");
	}

	function selected($selection) {
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Pipeline->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
		$this->set('groups', $this->Pipeline->getGroups($_SESSION['user']));
		$this->set('field', "Selected");
		$this->set('selection', $selection);
		$this->set('genomes', $this->Pipeline->getGenomes());
	}
    
	function rerun($run_id, $selection){
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Pipeline->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
        $this->set('groups', $this->Pipeline->getGroups($_SESSION['user']));
        $this->set('selection', $selection);
		$this->set('run_id', $run_id);
		$this->set('field', 'Status');
		$this->set('genomes', $this->Pipeline->getGenomes());
	}

	function report(){
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Pipeline->getGroup($_SESSION['user']);
		$this->set('gids', $gids);
		$this->set('field', 'Status');
		$run_id = $this->Pipeline->getRunId();
		$this->set('run_id', $run_id);
		$this->set('outdir', $this->Pipeline->getRunDirectory($run_id));
	}

	function plots($selection){
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Pipeline->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
		$this->set('field', 'Plots');
		$this->set('selection', $selection);
	}

	function afterAction() {

	}

}
