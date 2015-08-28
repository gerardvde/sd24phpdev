<?php
require_once ('LWEVDBinclude.php');
$data['year']="2014";
getCasesnew('getcases',$data);
function getCasesnew($request,$data)
{
	global $db;
	$conditions=Array();
	if(isset($data['partnerID']))
	{
		$partnerID=$data['partnerID'];
		$conditions[]= ' partnerID =  "'.$partnerID.'" ';
	}
	if(isset($data['status']))
	{
		$status=$data['status'];
		$conditions[]=  " status = $status ";
	}
	if(isset($data['year']))
	{
		$year=$data['year'];
		$year="\"$year%\"";
		$conditions[]=  " `date` LIKE $year ";
	}

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
	$sql = "SELECT * FROM `case` $whereClause  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get cases sql:  $sql error: $error");

	}
	$reltables=Array('caseTech','caseClient','caseObject','caseExpert','caseClerk','caseTask','caseDamage');
	$json['cases']=Array();
	while ($case = mysql_fetch_assoc($sqlresult)) {
		foreach ($case as $key => $value) {
			$case[$key] = urlencode(($value));
			
		}
		$caseID=$case['id'];
		foreach($reltables as $reltable)
		{
			$records=getCaseRec($request,$caseID,$reltable);
			if(count($records)==0)
				continue;
			if(count($records)==1)
				$case[$reltable]=array_shift ($records);
			if(count($records)>1)
				$case[$reltable]=$records;
		}
		if($case['status']<9)
		{
			$case['caseBill']=getCaseBill($request,$caseID);
			$case['caseEstimate']=getCaseEstimate($request,$caseID);
		}
		$case['caseImages']=getCaseImages($request,$caseID);
		$json['cases'][] = $case;
	}
	sendRequestResult($request, json_encode($json));
}

function getCase($request,$caseID)
{
	global $db;
	$sql = "SELECT * FROM `case`  WHERE id=\"$caseID\"";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get case sql:  $sql error: $error");

	}
	$reltables=Array('caseTech','caseClient','caseObject','caseExpert','caseTask','caseDamage');
	$case = mysql_fetch_assoc($sqlresult);
	foreach ($case as $key => $value) {
			$case[$key] = urlencode(($value));	
	}
	$caseID=$case['id'];
	foreach($reltables as $reltable)
	{
		$records=getCaseRec($request,$caseID,$reltable);
		if(count($records)==0)
			continue;
		if(count($records)==1)
			$case[$reltable]=array_shift ($records);
		if(count($records)>1)
			$case[$reltable]=$records;
	}
	$case['caseBill']=getCaseBill($request,$caseID);
	$case['caseEstimate']=getCaseEstimate($request,$caseID);
	$case['caseImages']=getCaseImages($request,$caseID);
	$json['case'] = $case;
	sendRequestResult($request, json_encode($json));
}

function getCaseRec($request,$caseID,$table)
{
	global $db;
	$sql = "SELECT *  FROM  $table  WHERE caseID=\"$caseID\"";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseTech:  sql:  $sql error: $error");
	};

		while ($record = mysql_fetch_assoc($sqlresult))
		{
			foreach ($record  as $key => $value) {
				$record [$key] = urlencode(($value));
			}
			 $records[]=$record;
		}
		return $records;	
}
function getCaseBill($request,$caseID)
{
	global $db;
	$sql = "SELECT *  FROM  caseBill  WHERE caseID=\"$caseID\" AND  status< 9";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseBill  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )==0)
	{
		return "";
	}
	$bill = mysql_fetch_assoc($sqlresult);
	foreach ($bill  as $key => $value) {
				$bill [$key] = urlencode(($value));
	}	
	$billID=$bill['id'];
	$sql = "SELECT *  FROM  caseBillposition  WHERE caseID=\"$caseID\" AND billID=\"$billID\"   ORDER BY `id` ASC ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseBill  sql:  $sql error: $error");
	};
	while ($service = mysql_fetch_assoc($sqlresult))
		{
			foreach ($service  as $key => $value) {
				$service [$key] = urlencode(($value));
			}
			 $services[]=$service;
		}	
	$bill['services']=$services;
	return $bill;	
}

function getCaseEstimate($request,$caseID)
{
	global $db;
	$sql = "SELECT *  FROM  caseEstimate  WHERE caseID=\"$caseID\" AND status < 9 ";
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
		$est[$key] = urlencode(($value));
	}	
	$estID=$est['id'];
	$sql = "SELECT *  FROM  caseEstimateposition  WHERE caseID=\"$caseID\" AND estimateID=\"$estID\"  ORDER BY `id` ASC ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseEstpos  sql:  $sql error: $error");
	};
	while ($service = mysql_fetch_assoc($sqlresult))
		{
			foreach ($service  as $key => $value) {
				$service [$key] = urlencode(($value));
			}
			 $services[]=$service;
		}	
	$est['services']=$services;
	return $est;	
}
function getCaseImages($request,$caseID)
{
	global $db;
	$sql = "SELECT *  FROM  caseImage  WHERE caseID=\"$caseID\" ORDER BY `id` ASC ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseEstimate  sql:  $sql error: $error");
	};

	while ($record = mysql_fetch_assoc($sqlresult))
		{
			foreach ($record  as $key => $value) {
				$record [$key] = urlencode(($value));
			}
			 $records[]=$record;
		}	
		return $records;	
}
?>