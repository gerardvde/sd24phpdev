<?php
require_once ('LWEVDBinclude.php');
$caseID=$_REQUEST['id'];
$request=$_REQUEST['request'];
echo " CaseID $caseID<br>";
cleanCase($request,$caseID);
function cleanCase($request,$caseID)
{
	global $db;
	$condition="";
	if(isset($caseID))
	{
		$condition=" AND caseID=\"$caseID\" ";
	}
	
	$sql = "SELECT id  FROM  caseBill  $condition";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "select sql:  $sql error: $error");
	};
	$numrowsbills=mysql_num_rows ( $sqlresult );
	$nopositions=0;
	while($billrec = mysql_fetch_assoc($sqlresult))
	{
		$recID=$billrec['id'];
		$sql = "SELECT id FROM  `caseBillposition`  WHERE billID=$recID";
		$sqlresult1 = mysql_query($sql, $db);
		if (!$sqlresult1) {
			$error = mysql_error($db);
			sendRequestError($request, "select sql:  $sql error: $error");
		};
		$numrows=mysql_num_rows ( $sqlresult1 );
		if($numrows==0)
			{
			$nopositions++;
			$sql = "DELETE  FROM  caseBill  WHERE id=$recID ";
			$sqlresult2 = mysql_query($sql, $db);
			}
	}	
	echo "Bills found $numrowsbills  no positions $nopositions<br>";
	$sql = "SELECT caseID,id FROM  caseEstimate  $condition";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "select sql:  $sql error: $error");	};
	$numrowsest=mysql_num_rows ( $sqlresult );
	$nopositions=0;
	while($estimaterec = mysql_fetch_assoc($sqlresult))
	{
		$recID=$estimaterec['id'];
		$sql = "SELECT id FROM  `caseEstimateposition`  WHERE estimateID=$recID";
		$sqlresult1 = mysql_query($sql, $db);
		if (!$sqlresult1) {
			$error = mysql_error($db);
			sendRequestError($request, "select sql:  $sql error: $error");		};
		$numrows=mysql_num_rows ( $sqlresult1 );
		if($numrows==0)
			{
			$nopositions++;
	
			$sql = "DELETE  FROM  caseEstimate  WHERE id=$recID ";
			$sqlresult2 = mysql_query($sql, $db);
			}
	}	
	echo "Estimates found $numrowsest  no positions $nopositions<br>";
}

?>