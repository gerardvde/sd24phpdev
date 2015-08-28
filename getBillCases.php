<?php
function getBillCases($request,$data)
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
	$reltables=Array('caseTech','caseClient','caseObject','caseExpert','caseClerk','caseTask','caseDamage');
	$sql = "SELECT * FROM `case` $whereClause  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get cases sql:  $sql error: $error");

	}
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
		$bills=getBills($request,$caseID);
		$case['bills']=$bills;
		$json['cases'][] = $case;
	}
	sendRequestResult($request, json_encode($json));
}
function getBills($request,$caseID)
{
	global $db;
	
	$sql = "SELECT *  FROM  caseBill  WHERE caseID=\"$caseID\" AND  status=9 AND `ignore` = 0  ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseBill  sql:  $sql error: $error");
	};
	$amount=mysql_num_rows ( $sqlresult );
	//echo " getBills $request $caseID $amount $sql<br>";
	if($amount==0)
	{
		return "";
	}
	while ($bill = mysql_fetch_assoc($sqlresult)) 
		{
			$billID=$bill['id'];
			$type=$bill['type'];
			foreach ($bill  as $key => $value) 
			{
				$bill [$key] = urlencode(($value));
			}
			$sql = "SELECT *  FROM  caseBillposition  WHERE caseID=\"$caseID\" AND billID=\"$billID\" AND `ignore` = 0  ORDER BY `id` ASC ";
			$sqlresult2 = mysql_query($sql, $db);
			if (!$sqlresult2) 
			{
				$error = mysql_error($db);
				sendRequestError($request, "get caseBill  sql:  $sql error: $error");
			};
			 $services=Array();
			while ($service = mysql_fetch_assoc($sqlresult2))
			{
				foreach ($service  as $key => $value) 
				{
					$service [$key] = urlencode(($value));
				};
				 $services[]=$service;

			}
			 $numpositions=count($services);
			// echo " getBillsposition  $numpositions $sql<br>";
			 if($numpositions>0)
			 {
			 	$bill['services']=$services;
				 $allbills[]=$bill;
			 }
	}	

	return $allbills;	
}

?>