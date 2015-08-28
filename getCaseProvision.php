<?php
function getCaseProvision($request,$data)
{	
	$period=$data['period'];
	if(!isset($period))
	{
		sendRequestError($request, "invalid period");
	}
	$where="period LIKE \"$period\"";
	$json['provision']=getCreditingByCondition($request,$where);
	sendRequestResult($request, json_encode($json));
}

function getCreditingByCondition($request,$where)
{
	global $db;
	$period=$data['period'];
	$sql = "SELECT *  FROM  crediting  WHERE  $where AND `ignore` = 0   ORDER BY creditorID";	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get crediting sql:  $sql error: $error");

	}
	$bills=Array();
	while ($bill = mysql_fetch_assoc($sqlresult)) {
		foreach ($bill as $key => $value) {
			$bill[$key] = urlencode(($value));
			
		}
		$billid=$bill['id'];
		$positions=getCreditingPositions($request,$billid);
		$bill['positions']=$positions;
		$bills[] = $bill;
	}
	return $bills;
}
function getCreditingPositions($request,$id)
{
	global $db;
	$sql = "SELECT *  FROM  creditingposition  WHERE  creditingID=$id AND `ignore` = 0  ORDER BY positiontype";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get creditingposition  sql:  $sql error: $error");
	};
	$amount=mysql_num_rows ( $sqlresult );
	if($amount==0)
	{
		return "";
	}
	while ($position = mysql_fetch_assoc($sqlresult)) 
		{
			foreach ($position  as $key => $value) 
			{
				$position [$key] = urlencode(($value));
			}
			$positions[]=$position;
	}	
	return $positions;	
}

?>