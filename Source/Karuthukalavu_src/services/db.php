<?php

class DataBase{
	var $link;
	var $result;
	var $server;
	var $uid;
	var $pwd;
	var $db;
	
	function DataBase($server, $uid, $pwd, $db){
		$this->server = $server;
		$this->uid = $uid;
		$this->pwd = $pwd;
		$this->db = $db;
	}

	function Open(){
		$this->link = mysqli_connect($this->server,$this->uid,$this->pwd) or die("Could not connect : " . mysqli_error());
	}
	
	function OpenRecordSet($qry){
		$this->Open();
		mysqli_select_db($this->link,$this->db) or die("Could not select database");
		$this->result = mysqli_query($this->link,$qry) or die("Query failed : " . mysqli_error($this->link));
		return $this->result;
	}

	function ExecuteQuery($qry, $types, $params){
		$records_affected = 0;
		$mysqli = new mysqli($this->server,$this->uid,$this->pwd,$this->db);
		
		/* check connection */
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		
		if($stmt = $mysqli->prepare($qry)){
			$stmt->bind_param($types, ...$params);
			$stmt->execute();
			$records_affected = $mysqli->records_affected;
		}
		$mysqli->close();
		
		return $records_affected;
	}
	
	function FetchRecords($qry){
		$list = array();
		$this->result = $this->OpenRecordSet($qry);
		while ($line = mysqli_fetch_array($this->result, MYSQLI_ASSOC)) {
			array_push($list,$line);
		}
		$this->Close();
		return $list;
	}
	
	function Close(){
		mysqli_free_result($this->result);
		mysqli_close($this->link);
	}
	
}

?>
