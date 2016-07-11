<?php

class Stat extends VanillaModel {
	function getId($value, $idfield, $table) {
			$result = $this->query("select DISTINCT $idfield from $table where `id`='$value'", 1);
			return $result;
	}
    
    function getGroup($username) {
        $groups = json_decode($this->query("select g.id from user_group ug, users u, groups g where ug.u_id=u.id and ug.g_id=g.id and username='$username'"), true);
        $group_str='';
        foreach ($groups as $group):
            $group_str.=$group['id'].",";
        endforeach;
        return rtrim($group_str, ",");
    }
	
	function getRunId(){
		return $_SESSION['adv_status_id'];
	}
	
	function getRunDirectory($run_id){
		$outdir=json_decode($this->query("
		SELECT outdir
		FROM ngs_runparams
		WHERE id = $run_id
		"));
		return $outdir[0]->outdir;
	}
}
