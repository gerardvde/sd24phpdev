<?php
require_once ('LWEVDBinclude.php');
getCaseEstimate("14A1A3F6C23");

function getCaseEstimate($caseID)
{
	global $db;
	$sql = "SELECT *  FROM  caseEstimate  WHERE caseID=\"$caseID\" AND status < 9 AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseEstimate  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )==0)
	{
		return "";
	}
	$est = mysql_fetch_assoc($sqlresult);
	foreach ($est  as $key => $value) {
		echo "key $value";
		$est[$key] = urlencode(($value));
	}	
	$estID=$est['id'];
	$sql = "SELECT *  FROM  caseEstimateposition  WHERE caseID=\"$caseID\" AND estimateID=\"$estID\" AND `ignore` = 0  ORDER BY `id` ASC ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseEstpos  sql:  $sql error: $error");
	};
	while ($service = mysql_fetch_assoc($sqlresult))
		{
			foreach ($service  as $key => $value) {
				echo "key $value".'<br>';
				echo urlencode($value).'<br>';
				echo urlencode(utf8_encode ($value)).'<br>';
				$service [$key] = urlencode(($value));
			}
			 $services[]=$service;
		}	
	$est['services']=$services;
}
?>