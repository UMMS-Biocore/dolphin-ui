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

    function afterAction() {

    }

}
