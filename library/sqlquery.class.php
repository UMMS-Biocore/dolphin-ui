<?php

class SQLQuery {
    protected $_dbHandle;
    protected $_result;

    /** Connects to database **/

    function connect($dbhost, $dbuser, $dbpass, $db) {
        $this->_dbHandle = new mysqli($dbhost, $dbuser, $dbpass, $db);
        if (mysqli_connect_errno()) {
                exit('Connect failed: '. mysqli_connect_error());
        }
        return 1;
    }

    /** Disconnects from database **/

    function disconnect() {
        if ($this->_dbHandle->close() != 0) {
            return 1;
        }  else {
            return 0;
        }
    }

    function esc($str)
    {
      return $this->_dbHandle->real_escape_string($str);
    }
    
    function selectAll() {
        $query = 'select * from `'.$this->_table.'`';
        return $this->query($query);
    }
    
    function select($id) {
        $query = 'select * from `'.$this->_table.'` where `id` = \''.$id.'\' '.$userstr.'';
        return $this->query($query, 1);    
    }

    function runSQL($sql)
    {
        $result=$this->_dbHandle->query($sql);
        if ($result)
        {
            return $result;
        }
        return 0;
    }

        
    /** Custom SQL Query **/

    function query($query, $singleResult = 0) {

       $this->_result = $this->runSQL($query);
       //print $query;

       if (preg_match("/select/i",$query)) {

	 if (is_object($this->_result)) {
           $num_rows =$this->_result->num_rows;
           $result = array();
       
           if ($num_rows>0) {
              if ($singleResult == 1) {
                 $row=$this->_result->fetch_array();
                 return $row[0];
              } 
              else {
                 while(($row=$this->_result->fetch_assoc())){$result[]=$row;}
              }
           }
           return json_encode($result);
         }
       }
    }
    /** Get data table **/
    function getDataTable($tablename) {
        $table = $this->query("select * from datatables where tablename='$tablename'");
        return json_decode($table, true);
    }
    /** Get data fields **/
    function getDataFields($tablename) {
        $fields = $this->query("select * from datatables dt, datafields df where dt.tablename='$tablename' and df.table_id=dt.id");
        return json_decode($fields, true);
    }
    function checkPerms($uid, $tablename) {
        $table = $this->query("SELECT tablename  from
            (SELECT t.tablename FROM datatables t where ((t.perms>32) OR t.owner_id=".$uid.")
            UNION 
            SELECT t.tablename FROM datatables t, user_group ug
            where t.group_id in (select g_id from user_group where u_id=".$uid.") 
            and ug.group_id=t.group_id and t.perms>=15) s
	    where s.tablename='".$tablename."'", 1);

	if ($table=='[]'){
	    return 0;
	}
        return 1;
    }
    
    /** Get groups for the user **/
    function getGroups($username) {
        $groups = $this->query("select g.id, g.name from user_group ug, users u, groups g where ug.u_id=u.id and ug.g_id=g.id and username='$username'");
        return json_decode($groups, true);
    }
	
    /** Get number of rows **/
    function getNumRows() {
        return $this->_result->num_rows;
    }

    /** Free resources allocated by a query **/

    function freeResult() {
        $this->_result->free();
    }

    /** Get error string **/

    function getError() {
        return $this->_dbHandle->error;
    }

}
