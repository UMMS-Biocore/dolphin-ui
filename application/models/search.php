<?php

class Search extends VanillaModel {

    public $innerJoin = "
                LEFT JOIN ngs_source
                ON ngs_samples.source_id = ngs_source.id
                LEFT JOIN ngs_organism
                ON ngs_samples.organism_id = ngs_organism.id
                LEFT JOIN ngs_molecule
                ON ngs_samples.molecule_id = ngs_molecule.id
                LEFT JOIN ngs_genotype
                ON ngs_samples.genotype_id = ngs_genotype.id
                LEFT JOIN ngs_library_type
                ON ngs_samples.library_type_id = ngs_library_type.id
                LEFT JOIN ngs_sample_conds
                ON ngs_samples.id = ngs_sample_conds.sample_id
                LEFT JOIN ngs_conds
                ON ngs_sample_conds.cond_id = ngs_conds.id
                LEFT JOIN ngs_instrument_model
                ON ngs_samples.instrument_model_id = ngs_instrument_model.id
				LEFT JOIN ngs_antibody_target
				ON ngs_samples.target_id = ngs_antibody_target.id
				LEFT JOIN ngs_donor
				ON ngs_samples.donor_id = ngs_donor.id";
                
/** Get menuitems for this user **/
	function getAccItems($fieldname, $tablename, $uid, $gids) {
        
        if($gids == ''){
            $gids = -1;
        }

        if($tablename == 'ngs_samples'){
            $result = $this->query("select $fieldname name, count($fieldname) count from $tablename where $fieldname !='' AND (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid)) group by $fieldname");
        }else{
            $result = $this->query("select $fieldname name, count(ngs_samples.id) count
                                                FROM `ngs_samples`
                                                INNER JOIN $tablename
                                                ON ngs_samples."."$fieldname"."_id = $tablename.id
                                                WHERE (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid))
                                                GROUP BY $fieldname");
        }
		return json_decode($result, true);
	}
	function getAccItemsCont($fieldname, $tablename, $search, $uid, $gids){
        if($gids == ''){
            $gids = -1;
        }
		if($search != ""){
			$advQuery = "";
            $advJoin = "";
			foreach(explode('$', $search) as $s){
				$s = urldecode($s);
				$split = explode('=', $s);
                if($split[0] != $fieldname){
                    $advJoin.= "INNER JOIN ngs_".$split[0]."
                                ON ngs_samples.".$split[0]."_id = ngs_".$split[0].".id ";
                }
				$advQuery.= " AND " . $split[0]. " = " . '"'. $split[1] . '"';
			}
			if($tablename == 'ngs_samples'){
                $result = $this->query("SELECT $fieldname name, count($fieldname) count FROM $tablename WHERE $fieldname !='' $advQuery AND (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid)) group by $fieldname");
            }else{
                $result = $this->query("SELECT $fieldname name, count(ngs_samples.id) count
                                        FROM `ngs_samples`
                                        INNER JOIN $tablename
                                        ON ngs_samples."."$fieldname"."_id = $tablename.id
                                        $advJoin
                                        WHERE (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid))
                                        $advQuery
                                        GROUP BY $fieldname");
        }
		}else{
			if($tablename == 'ngs_samples'){
                $result = $this->query("SELECT $fieldname name, count($fieldname) count FROM $tablename WHERE $fieldname !='' AND (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid)) group by $fieldname");
            }else{
                $result = $this->query("SELECT $fieldname name, count(ngs_samples.id) count
                                        FROM `ngs_samples`
                                        INNER JOIN $tablename
                                        ON ngs_samples."."$fieldname"."_id = $tablename.id
                                        WHERE (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid))
                                        GROUP BY $fieldname");
            }
		}
		return json_decode($result, true);
	}
	function getId($value, $idfield, $table) {
		$result = $this->query("select DISTINCT $idfield from $table where `id`='$value'", 1);
		return $result;
	}
	function getValues($value, $table) {
        if($table == 'ngs_samples'){
            $result = $this->query("SELECT $table.*, groups.name as group_name, ngs_source.source, ngs_organism.organism, ngs_molecule.molecule,
								   ngs_genotype.genotype, ngs_conds.condition, ngs_library_type.library_type, ngs_instrument_model.instrument_model,
								   ngs_donor.donor, ngs_antibody_target.target,
								   ngs_experiment_series.experiment_name as series_name, ngs_lanes.name as lane_name, ngs_protocols.name as proto_name
								   FROM $table
								   LEFT JOIN groups
								   ON $table.group_id = groups.id
								   LEFT JOIN ngs_experiment_series
								   ON ngs_experiment_series.id = $table.series_id
								   LEFT JOIN ngs_lanes
								   ON ngs_lanes.id = $table.lane_id
								   LEFT JOIN ngs_protocols
								   ON ngs_protocols.id = $table.protocol_id
								   ".$this->innerJoin."
								   WHERE $table.`id`='$value'");
        }elseif($table == 'ngs_lanes'){
            $result = $this->query("select $table.*, ngs_facility.facility, groups.name as group_name, ngs_experiment_series.experiment_name as series_name
									FROM $table
									LEFT JOIN ngs_facility
									ON ngs_lanes.facility_id = ngs_facility.id
									LEFT JOIN groups
									ON $table.group_id = groups.id
									LEFT JOIN ngs_experiment_series
									ON $table.series_id = ngs_experiment_series.id
									WHERE $table.`id`='$value'");
        }else{
            $result = $this->query("select $table.experiment_name, $table.summary, $table.design, $table.group_id, $table.perms, groups.name
								   from $table LEFT JOIN groups ON $table.group_id = groups.id where $table.`id`='$value'");
        }
		return json_decode($result, true);
	}
	function getFields($table){
		$result = $this->query("select df.fieldname, df.title from datatables dt, datafields df where df.table_id=dt.id and dt.tablename='$table'");
		return json_decode($result, true);
	}
	function getRuns($sample, $gids){
		$query = 	'SELECT id, run_name
					FROM ngs_runparams
					WHERE id in(
						SELECT run_id
						FROM ngs_runlist
						WHERE sample_id = '.$sample.'
						)';
		if($_SESSION['uid'] != '1'){
			$perms = ' AND (((group_id in ('.$gids.')) AND (perms >= 15)) OR (owner_id = '.$_SESSION['uid'].'))';
		}else{
			$perms = '';
		}
		$result = $this->query($query . $perms);
		return json_decode($result);
	}
	function getTables($runlist, $gids){
		$query = 	'SELECT id, name
					FROM ngs_createdtables
					WHERE parameters LIKE ';
		if($_SESSION['uid'] != '1'){
			$perms = ' AND (((group_id in ('.$gids.')) AND (perms >= 15)) OR (owner_id = '.$_SESSION['uid'].'))';
		}else{
			$perms = '';
		}
		foreach($runlist as $rl){
			$query.= "'%;".$rl->id."%' ";
			if(end($runlist) != $rl){
				$query.= 'OR parameters LIKE ';
			}
		}
		$result = $this->query($query . $perms);
		return json_decode($result);
	}
    function getGroup($username) {
        $groups = json_decode($this->query("select g.id from user_group ug, users u, groups g where ug.u_id=u.id and ug.g_id=g.id and username='$username'"), true);
        $group_str='';
        foreach ($groups as $group):
            $group_str.=$group['id'].",";
        endforeach;
        return rtrim($group_str, ",");
    }
    function getSampleFileLocation($value){
        $result = $this->query("select ngs_temp_sample_files.id, ngs_dirs.id as dir_id, file_name, fastq_dir, backup_dir, amazon_bucket from ngs_temp_sample_files left join ngs_dirs on ngs_temp_sample_files.dir_id = ngs_dirs.id where sample_id = $value");
		return json_decode($result, true);
    }
    function getSampleFastqFileLocation($value){
        $result = $this->query("select ngs_fastq_files.id, ngs_dirs.id as dir_id, file_name, fastq_dir, backup_dir, amazon_bucket from ngs_fastq_files left join ngs_dirs on ngs_fastq_files.dir_id = ngs_dirs.id where sample_id = $value");
		return json_decode($result, true);
    }
	function getLaneFileLocation($value){
        $result = $this->query("select ngs_temp_lane_files.id, ngs_dirs.id as dir_id, file_name, fastq_dir, backup_dir, amazon_bucket from ngs_temp_lane_files left join ngs_dirs on ngs_temp_lane_files.dir_id = ngs_dirs.id where lane_id = $value");
		return json_decode($result, true);
    }
	function getInputSampleDirectories($value){
		$result = $this->query("select ngs_dirs.fastq_dir, ngs_dirs.id from ngs_dirs where id in (select dir_id from ngs_temp_sample_files where sample_id = $value)");
		return json_decode($result, true);
	}
	function getInputLaneDirectories($value){
		$result = $this->query("select ngs_dirs.fastq_dir, ngs_dirs.id from ngs_dirs where id in (select dir_id from ngs_temp_lane_files where lane_id = $value)");
		return json_decode($result, true);
	}
}
