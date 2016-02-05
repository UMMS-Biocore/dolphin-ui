<?php
class VanillaModel extends SQLQuery {
	protected $_model;

	function __construct() {
		
		$inflect = new Inflection();

		$this->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$this->_model = get_class($this);
		$this->_table = strtolower($inflect->pluralize($this->_model));
	}

	function __destruct() {
	}
}
