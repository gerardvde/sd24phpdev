<?php

/*
 * generische uodate function
*/
function updateTable($request, $data,$table) {
	global $db;
	mysql_query('SET NAMES utf8', $db);
	$record = json_decode($data, true);
	if (!$record) {
		sendRequestError($request, 'json' . $data);
	}
	if (!isset ($record['id']) || $record['id'] == "" || $record['id'] == 0) {
		unset ($record['id']);
	} else {
		$recordID = $record['id'];
	}
	$tabelfields=getFieldnames($table);
	foreach ($tabelfields as $tabelfield) {
	if(isset($record[$tabelfield])){
		$fields[]=$tabelfield;
		}
	}
	foreach ($fields as $field) {
		$columns[] = "`".$field."`";
		$value = enquote($record[$field]);
		$values[] = $value;
	}

	//
	if (isset ($recordID)) {
		$recordcols = implode($columns, ',');
		$recordvals = implode($values, ',');
		$sql = "REPLACE INTO  $table ($recordcols) VALUES ($recordvals)";
		$sqlresult = mysql_query($sql, $db);
	} else {
		$recordcols = implode($columns, ',');
		$recordvals = implode($values, ',');
		$sql = "INSERT INTO  $table ($recordcols) VALUES ($recordvals)";
		$sqlresult = mysql_query($sql, $db);
		$recordID = mysql_insert_id();
	}
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: $table sql:  $sql error: $error");
	};
	
	$sql = "SELECT * FROM $table WHERE ID=$recordID ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "select $table sql:$sql Error: $error<br>");
	};
	$record = mysql_fetch_assoc($sqlresult);
	foreach ($record as $key => $value) {
			$record[$key] = urlencode( utf8_decode($value));
		};
	$json[$table][] = $record;
	sendRequestResult($request, json_encode($json));
}
function deleteRecord($request, $id,$table) {
	global $db;
	mysql_query('SET NAMES utf8', $db);
	$sql = "DELETE FROM $table WHERE ID=$id ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "select $table sql:$sql Error: $error<br>");
	};
	sendRequestResult($request, 'OK');
}

?>