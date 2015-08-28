<?php

function cleanBill($request,$caseID)
{
	global $db;
	$condition="WHERE status=0 ";
	if(isset($caseID))
	{
		$condition=" AND caseID=\"$caseID\" ";
	}
	$sql = "SELECT id  FROM  caseBill  $condition";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		return;
	};
	$numrowsbills=mysql_num_rows ( $sqlresult );
	$nopositions=0;
	while($billrec = mysql_fetch_assoc($sqlresult))
	{
		$recID=$billrec['id'];
		$sql = "SELECT id FROM  `caseBillposition`  WHERE billID=$recID";
		$sqlresult1 = mysql_query($sql, $db);
		if (!$sqlresult1) {
			return;
		};
		$numrows=mysql_num_rows ( $sqlresult1 );
		if($numrows==0)
			{
			$nopositions++;
			$sql = "DELETE  FROM  caseBill  WHERE id=$recID ";
			$sqlresult2 = mysql_query($sql, $db);
			$sql = "DELETE FROM  `caseBillposition`  WHERE billID=$recID";
			$sqlresult2 = mysql_query($sql, $db);
			}
	}	
	$sql = "SELECT caseID,id FROM  caseEstimate  $condition";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		return;
	};
	$numrowsest=mysql_num_rows ( $sqlresult );
	while($estimaterec = mysql_fetch_assoc($sqlresult))
	{
		$recID=$estimaterec['id'];
		$sql = "SELECT id FROM  `caseEstimateposition`  WHERE estimateID=$recID";
		$sqlresult1 = mysql_query($sql, $db);
		if (!$sqlresult1) {
			return;		};
		$numrows=mysql_num_rows ( $sqlresult1 );
		if($numrows==0)
			{
			$sql = "DELETE  FROM  caseEstimate  WHERE id=$recID ";
			$sqlresult2 = mysql_query($sql, $db);
			$sql = "DELETE FROM  `caseEstimate`  WHERE estimateID=$recID";
			$sqlresult2 = mysql_query($sql, $db);
			}
	}	
}

?>