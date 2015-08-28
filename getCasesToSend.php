<?php
function getCasesToSend($request)
{
	global $db;
	$sql = "SELECT *  FROM  caseToSend";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get getCasesToSend  sql:  $sql error: $error");
	};
	$case['caseToSend']=array();
	while($est = mysql_fetch_assoc($sqlresult))
	{		
		$estID=$est['id'];
		$sql2 = "SELECT *  FROM  caseEstimateposition  WHERE caseID=\"$caseID\" AND estimateID=\"$estID\"";
		$sqlresult2 = mysql_query($sql2, $db);
		if (!$sqlresult2) {
			$error = mysql_error($db);
			sendRequestError($request, "get caseEstpos  sql:  $sql error: $error");
		};
		while ($service = mysql_fetch_assoc($sqlresult2))
		{
			foreach ($service  as $key => $value) {
				$service [$key] = urlencode(($value));
			}
			 $services[]=$service;
		}	
		$est['services']=$services;
		$case['caseEstimate'][]=$est;
	}
	sendRequestResult($request, json_encode($case));
}
?>