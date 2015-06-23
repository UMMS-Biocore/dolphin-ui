<?php

class SearchController extends VanillaController {

	function beforeAction() {
        
	}

	function index() {
		$this->set('field', "Search");
		$this->set('segment', "index");
        
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Search->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
        
		$this->set('assay', $this->Search->getAccItems("library_type", "ngs_samples", $_SESSION['uid'], $gids));
		$this->set('organism', $this->Search->getAccItems("organism", "ngs_samples", $_SESSION['uid'], $gids));
		$this->set('molecule', $this->Search->getAccItems("molecule", "ngs_samples", $_SESSION['uid'], $gids));
		$this->set('source', $this->Search->getAccItems("source", "ngs_source", $_SESSION['uid'], $gids));
		$this->set('genotype', $this->Search->getAccItems("genotype", "ngs_samples", $_SESSION['uid'], $gids));
	}

	function browse($field, $value, $search) {
		$this->set('field', "Search");
		$this->set('segment', "browse");
		$this->set('table', $field);
		$this->set('value', $value);
		$this->set('search', $search);
        
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Search->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
        
		$this->set('assay', $this->Search->getAccItemsCont("library_type", "ngs_samples", $search, $_SESSION['uid'], $gids), $search);
		$this->set('organism', $this->Search->getAccItemsCont("organism", "ngs_samples", $search, $_SESSION['uid'], $gids), $search);
		$this->set('molecule', $this->Search->getAccItemsCont("molecule", "ngs_samples", $search, $_SESSION['uid'], $gids), $search);
		$this->set('source', $this->Search->getAccItemsCont("source", "ngs_source", $search, $_SESSION['uid'], $gids), $search);
		$this->set('genotype', $this->Search->getAccItemsCont("genotype", "ngs_samples", $search, $_SESSION['uid'], $gids), $search);
	}
	function details($table, $value, $search) {
		$this->set('field', "Search");
		$this->set('segment', "details");
		$this->set('value', $value);
		$this->set('table', $table);
		$this->set('search', $search);
        
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Search->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
        
		$this->set('assay', $this->Search->getAccItemsCont("library_type", "ngs_samples", $search, $_SESSION['uid'], $gids), $search);
		$this->set('organism', $this->Search->getAccItemsCont("organism", "ngs_samples", $search, $_SESSION['uid'], $gids), $search);
		$this->set('molecule', $this->Search->getAccItemsCont("molecule", "ngs_samples", $search, $_SESSION['uid'], $gids), $search);
		$this->set('source', $this->Search->getAccItemsCont("source", "ngs_source", $search, $_SESSION['uid'], $gids), $search);
		$this->set('genotype', $this->Search->getAccItemsCont("genotype", "ngs_samples", $search, $_SESSION['uid'], $gids), $search);
        
		if ($table=='experiment_series')
		{
			$this->set('experiment_series', $this->Search->getValues($value, 'ngs_experiment_series'));
			$this->set('experiment_series_fields', $this->Search->getFields('ngs_experiment_series'));
		}
		if ($table=='experiments')
		{
			$this->set('experiment_series_fields', $this->Search->getFields('ngs_experiment_series'));
			$this->set('experiment_fields', $this->Search->getFields('ngs_lanes'));

			$this->set('experiment_series', $this->Search->getValues($this->Search->getId($value, 'series_id', 'ngs_lanes'), 'ngs_experiment_series'));
			$this->set('experiments', $this->Search->getValues($value, 'ngs_lanes'));
            $this->set('lane_file', $this->Search->getLaneFileLocation($value));
		}
		if ($table=='samples')
		{
			$this->set('experiment_series_fields', $this->Search->getFields('ngs_experiment_series'));
			$this->set('experiment_fields', $this->Search->getFields('ngs_lanes'));
			$this->set('sample_fields', $this->Search->getFields('ngs_samples'));

			$this->set('experiment_series', $this->Search->getValues($this->Search->getId($value, 'series_id', 'ngs_samples'), 'ngs_experiment_series'));
			$this->set('experiments', $this->Search->getValues($this->Search->getId($value, 'lane_id', 'ngs_samples'), 'ngs_lanes'));
            $this->set('lane_file', $this->Search->getLaneFileLocation($this->Search->getId($value, 'lane_id', 'ngs_samples')));
			$this->set('samples', $this->Search->getValues($value, 'ngs_samples'));
            $this->set('sample_file', $this->Search->getSampleFileLocation($value));
		}
	}


	function afterAction() {

	}

}
