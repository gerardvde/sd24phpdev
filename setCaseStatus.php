<?php
function setCaseStatus($request,$data){
	global $db;
	global $request;
	$caseID=$data['caseID'];
	$caseStatus=$data['doc'];
	$billStatus=$data['bill'];
	$estimateStatus=$data['estimate'];

	if(isset($caseStatus))
	{
	
		$sql = "UPDATE `case`  SET status = $caseStatus WHERE id=\"$caseID\" ";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "update: case sql:  $sql error: $error");
		};
	}
	if(isset($billStatus))
	{
	
		$sql = "UPDATE `caseBill`  SET status = $billStatus WHERE caseID=\"$caseID\" ";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "update: case sql:  $sql error: $error");
		};
	}
	if(isset($estimateStatus))
	{
	
		$sql = "UPDATE `caseEstimate`  SET status = $estimateStatus WHERE caseID=\"$caseID\" ";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "update: case sql:  $sql error: $error");
		};
	}
	sendRequestResult($request, 'OK');

}


?>