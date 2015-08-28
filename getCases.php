<?php
function getCases($request,$data)
{
	global $db;
	$conditions=Array();
	$conditions[]= " `ignore` = 0 ";
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
		$case['caseCost']=getCaseCost($request,$caseID);
		$json['cases'][] = $case;
	}
	sendRequestResult($request, json_encode($json));
}
function getMediatorCases($request,$data)
{
	global $db;
	if(!isset($data['mediatorID']))
	{
		sendRequestError($request, "mediatorID not set");
	}
	$mediatorID=$data['mediatorID'];
	$condition="mediatorID=\"$mediatorID\" AND `ignore` = 0";
	
	$sql = "SELECT id FROM `case`   WHERE $condition ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get medaitorcases sql:  $sql error: $error");
	}
	$json['cases']=Array();
	while ($case = mysql_fetch_assoc($sqlresult)) {
		$caseID=$case['id'];
		$condition="id=\"$caseID\"";
		$cases[]=getCaseDetails($request,$condition);
	}
	$json['cases']=$cases;
	sendRequestResult($request, json_encode($json));
}

function getCase($request,$caseID)
{
	$condition="id=\"$caseID\" AND `ignore` = 0";
	$json['case']=getCaseDetails($request,$condition);
	sendRequestResult($request, json_encode($json));
}
function getCaseDetails($request,$condition)
{
	global $db;
	$sql = "SELECT * FROM `case` WHERE  $condition ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get case sql:  $sql error: $error");

	}
	$reltables=Array('caseTech','caseClient','caseObject','caseExpert','caseTask','caseDamage','caseCost');
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
	$case['bills'] =getBills($request,$caseID);
	$case['caseEstimate']=getCaseEstimate($request,$caseID);
	$case['caseImages']=getCaseImages($request,$caseID);
	$case['caseCost']=getCaseCost($request,$caseID);
	$case['billedCaseCost']=getBilledCaseCost($request,$caseID);
	return $case;
	}

function getCaseOnly($caseID)
{
	global $db;
	$sql = "SELECT * FROM `case`  WHERE id=\"$caseID\" AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get case sql:  $sql error: $error");

	}
	$reltables=Array('caseTech','caseClient','caseObject','caseExpert','caseTask','caseDamage','caseCost');
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
	$json['case'] = $case;
	return $json;;
}

function getCaseRec($request,$caseID,$table)
{
	global $db;
	$sql = "SELECT *  FROM  $table  WHERE caseID=\"$caseID\" AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseTech:  sql:  $sql error: $error");
	};
		$records=Array();
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
	$sql = "SELECT *  FROM  caseBill  WHERE caseID=\"$caseID\" AND  status< 9 AND `ignore` = 0 ";
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
	$sql = "SELECT *  FROM  caseBillposition  WHERE caseID=\"$caseID\" AND billID=\"$billID\" AND `ignore` = 0   ORDER BY `id` ASC ";
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
				$service [$key] = urlencode(($value));
			}
			 $services[]=$service;
		}	
	$est['services']=$services;
	return $est;	
}

function getBilledCaseCost($request,$caseID)
{
	global $db;

	$sql = "SELECT *  FROM  partnerBillposition  WHERE caseID=\"$caseID\"  AND `ignore` = 0  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partnerBillposition  sql:  $sql error: $error");
	};
	$costs=Array();
	while ($cost = mysql_fetch_assoc($sqlresult))
		{
			foreach ($cost  as $key => $value) {
				$cost[$key] = urlencode(($value));
			}
			 $costs[]=$cost;
		}	
	return $costs;	
}

function getCaseCost($request,$caseID)
{
	global $db;
	$sql = "SELECT *  FROM  caseCost  WHERE caseID=\"$caseID\" AND `ignore` = 0  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseCost  sql:  $sql error: $error");
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

function getCaseImages($request,$caseID)
{
	global $db;
	$sql = "SELECT *  FROM  caseImage  WHERE caseID=\"$caseID\" AND `ignore` = 0  ORDER BY `id` ASC ";
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