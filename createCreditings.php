<?php
function createCreditings($period,$creditingcases)
{
	global $billnr;
	global $request;
	$oldmediatorID;
	$billnr=getMaxBillNr();
	if(isset($billnr))
	{
		$billnr++;
	}
	else
	{
		sendRequestError($request, "Keine Rechnungsnummer");
	}
	foreach($creditingcases as $case)
	{
		$json=json_encode($case);
		$mediatortype=$case['mediatorType'];
		$mediatorID=$case['mediatorID'];
		writeMessageLog("Crediting Case $json");
		if(!isset($mediatorID)|| !isset($mediatortype)|| $mediatortype==3)//Central doesnt get credit
		{
			continue;
		}
		if( $mediatorID!=$oldmediatorID)
		{
			if(isset($oldmediatorID) )
			{
				createCrediting($period,$cases,$oldmediatorID,$mediatortype);
			}
			$cases=array();
			$oldmediatorID=$mediatorID;
		}
		$cases[]=$case;
	}
	if(isset($oldmediatorID) )
	{
		createCrediting($period,$cases,$oldmediatorID,$mediatortype);
	}
}


function createCrediting($period,$cases,$creditorID,$mediatortype)
{
	global $db;
	global $billnr;
	$json=json_encode($cases);
	writeMessageLog("Start createCrediting cerdirorID: $creditorID  $json");

	//Check if credit bill exists, we cannot rewrite ist!!
	$sql = "SELECT period  FROM   `crediting`  WHERE period LIKE \"$period\" AND creditorID=$creditorID AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseBill  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )>0)
	{
		return ;
	}
	
	switch($mediatortype)
  	{
  	case 1://Mediator when provison=0 he/she doenst wants provision an we can ignore theis mediator
  		$sql = "SELECT name as brand,owner as name,street,zip, town  FROM   `mediator`  WHERE  id=$creditorID AND provision=1  ";
  		break;
  	case 2://Partner
  		$sql = "SELECT brand,name,street,nr,zip, town,directdebit  FROM   `partner`  WHERE  id=$creditorID  ";
  		break;
  	}
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partner  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )==0)
	{
		writeMessageLog("No valid partner/mediator for type $type > $sql ");
		return ;
	}
	$record = mysql_fetch_assoc($sqlresult);
	$totalnet=0;

	$vat=19;
	$date=enquote(date("Ymd"));
	$description="\"Gutschrift\"";
	$period=enquote($period);

	$brand=enquote($record['brand']);
	$name=enquote($record['name']);
	$street=enquote($record['street']." ".$record['nr']);
	$zip=enquote($record['zip']);
	$town=enquote($record['town']);
	writeMessageLog("Start createCrediting mediatortor: $brand");
	$values="$creditorID,$mediatortype,$period,$billnr,$brand,$name,$description,$street,$zip,$town,$totalnet,$vat,$date";
	$sql = "INSERT INTO crediting ( `creditorID`, `creditorType`,`period`, `billnr`, `brand`, `name`, `description`, `street`, `zip`, `town`, `totalNet`, `vat`, `date`) VALUES ($values)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "insert crediting  sql:  $sql error: $error");
	};
	$creditingID=mysql_insert_id ();
	$sql = "DELETE FROM creditingposition WHERE  creditingID=$creditingID";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "delete creditingposition  sql:  $sql error: $error");
		};

	$totAmount=0;
	$caseAmount=0;
	$positionAmount=0;

	foreach($cases as $case)
	{
		/*
			We calcualte what should be payed for mediation total per case is a position
				writeMessageLog("Start Check ids: $caseID");
			*/
			//{"caseID":"14F649E8240","partnerID":"4","addTurnover":"1500","extraCost":"40","status":"11","mediatorType":"1","mediatorID":"55","totalNet":878}
		$caseID=$case['caseID'];
		$mediatorType=$case['mediatorType'];
		$positiontype=$mediatorType+2;
		$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);

		$addTurnover=(isset($case['addTurnover']))?$case['addTurnover']:0;
		$totalNet=(isset($case['totalNet']))?$case['totalNet']:0;
		$caseTotal=$addTurnover+$totalNet;
		
		if($caseTotal>0)
		{
			$provisionRecord=calculateProvision($caseTotal);
			$provision=	$provisionRecord['mediatorprovision'];
			writeMessageLog("totalNet $totalNet $addTurnover   caseTotal $caseTotal provision $provision");

			$values="\"$caseID\",$creditingID,$positiontype,$description,$provision";
			$sql = "INSERT INTO creditingposition ( `caseID`, `creditingID`, `positiontype`, `description`, `amount`) VALUES ($values)";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "insert creditingposition  sql:  $sql error: $error");
			};
	
			writeMessageLog("Psoition Caseprovisoon $sql");
	
			$totAmount+=$provision;
		}
		writeMessageLog("End Case\n\n");
		

	}
	
	$sql = "UPDATE  `crediting` SET totalNet=$totAmount WHERE id=$creditingID";
	writeMessageLog("Update createCrediting  $sql");
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update creditin  sql:  $sql error: $error");
	};	
	
	$billnr++;
}

?>
