<?php
function getPartnerbills($request,$data)
{	
	$period=$data['period'];
	if(!isset($period))
	{
		sendRequestError($request, "invalid period");
	}
	$where="period LIKE \"$period\"";
	$json['partnerbills']=getPartnerbillsByCondition($request,$where);
	sendRequestResult($request, json_encode($json));
}
function getPartnerbillsByCondition($request,$where)
{
	global $db;
	$period=$data['period'];
	$sql = "SELECT *  FROM  partnerbill  WHERE  $where AND `ignore` = 0   ORDER BY partnerID";	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get cases sql:  $sql error: $error");

	}
	$bills=Array();
	while ($bill = mysql_fetch_assoc($sqlresult)) {
		foreach ($bill as $key => $value) {
			$bill[$key] = urlencode(($value));
			
		}
		$billid=$bill['id'];
		$positions=getPartnerBillPositions($request,$billid);
		$bill['positions']=$positions;
		$bills[] = $bill;
	}
	return $bills;
}
function getPartnerBillPositions($request,$id)
{
	global $db;
	$sql = "SELECT *  FROM  partnerBillposition  WHERE  partnerbillID=$id AND `ignore` = 0  ORDER BY positiontype";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partnerBillposition  sql:  $sql error: $error");
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