<?php
function ignoreRecord($request,$table,$id)
{
	global $db;
	mysql_query('SET NAMES utf8', $db);
	$fields=Array();
	$tabelfields=getFieldnamesObject($table);
	if(!$tabelfields["id"] || !$tabelfields["ignore"]) 
	{
		sendRequestError($request, "Error delete: $table $id: ".json_encode($tabelfields));
	}
	$sql = "UPDATE  `$table`  SET `ignore`=1 WHERE `id`=$id";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: $table sql:  $sql error: $error");
	};
	sendRequestResult($request, 'OK');		
}
?>