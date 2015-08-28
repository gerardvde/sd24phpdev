<?php
require_once ('LWEVDBinclude.php');
$data["all"];
getTurnover("getturnover",$data);
function getTurnover($request,$data)
{
	global $db;
	$conditions=Array();
	$conditions[]="`ignore` = 0 ";
	if(isset($data['partnerID']))
	{
		$partnerID=$data['partnerID'];
		$conditions[]= ' partnerID =  "'.$partnerID.'" ';
	}
	$conditions[]= ' status >=  9';
	$whereClause= "";
	foreach($conditions as $cond)
	{
		if(strlen($whereClause)>0)
		{
			$whereClause.=" AND ";
		}
		$whereClause.=$cond;
		
	}
	if(strlen($whereClause)>0)
	{
		$whereClause= "WHERE ".$whereClause;
	}
	else
	{
		$whereClause="";
	}
	$sql = "SELECT id AS  caseID,partnerID,addTurnover, date,changedate,status FROM `case` $whereClause  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get cases sql:  $sql error: $error");

	}
	$json['caseturnover']=Array();
	while ($case = mysql_fetch_assoc($sqlresult)) {
		foreach ($case as $key => $value) {
			$case[$key] = urlencode(($value));
			
		}
		$case['totalNet']=getBillsTotal($request,$caseID);
		$case['taskType']=getTasktype($request,$caseID);
		$json['caseturnover'][] = $case;
	}
	sendRequestResult($request, json_encode($json));
}
function getBillsTotal($request,$caseID)
{
	global $db;
	$sql = "SELECT totalNet FROM  caseBill  WHERE caseID=\"$caseID\" AND  status=9 AND `ignore` = 0  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseBill  sql:  $sql error: $error");
	};
	$billstotal=0;
	$amount=mysql_num_rows ( $sqlresult );
	if($amount==0)
	{
		return $billstotal;
	}
	
	while ($bill = mysql_fetch_assoc($sqlresult)) 
		{
			$billstotal+=$bill['totalNet'];
	}	

	return $billstotal;	
}
function getClient($request,$caseID)
{
	global $db;
	$sql = "SELECT title,firstname,lastname,description FROM  caseClient  WHERE caseID=\"$caseID\" AND  status=9 AND `ignore` = 0  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseClient  sql:  $sql error: $error");
	};
	$client = mysql_fetch_assoc($sqlresult);
	return $client;	
}
function getObject($request,$caseID)
{
	global $db;
	$sql = "SELECT street,zip,town FROM  caseObject  WHERE caseID=\"$caseID\" AND  status=9 AND `ignore` = 0  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseObject  sql:  $sql error: $error");
	};
	$object = mysql_fetch_assoc($sqlresult);
	return $object;	
}
function getTasktype($request,$caseID)
{
	global $db;
	$sql = "SELECT type FROM  caseTask  WHERE caseID=\"$caseID\" AND `ignore` = 0  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseTask  sql:  $sql error: $error");
	};
	$amount=mysql_num_rows ( $sqlresult );
	if($amount==0)
	{
		return "";
	}
	
	$task = mysql_fetch_assoc($sqlresult);
	return $task['type'];	
}


?>