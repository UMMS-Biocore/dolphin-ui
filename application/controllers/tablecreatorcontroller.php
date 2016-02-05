<?php
 
class TablecreatorController extends VanillaController {
    function beforeAction() {
    }
    function index() {
		$this->set('field', "Generation");
		
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Tablecreator->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
    }
	
	function tablereports(){
		$this->set('field', "Reports");
		
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Tablecreator->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
	}
	
	function table(){
		$this->set('field', "Table Viewer");
		
		$this->set('uid', $_SESSION['uid']);
        $gids = $this->Tablecreator->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
		$this->set('groups', $this->Tablecreator->getGroups($_SESSION['user']));
	}
    function afterAction() {
    }
}
