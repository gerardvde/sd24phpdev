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
		$mediatortype=$case['mediatorType'];
		$mediatorID=$case['mediatorID'];

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


function createCrediting($period,$cases,$creditorID,$type)
{
	global $db;
	global $billnr;
	writeMessageLog("Start createCrediting cerdirorID: $creditorID");

	//Check if partner bill exists, we cannot rewrite ist!!
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
	
	switch($type)
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

	$values="$creditorID,$type,$period,$billnr,$brand,$name,$description,$street,$zip,$town,$totalnet,$vat,$date";
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
	$lastCaseID;
	$lastCase;
	foreach($cases as $case)
	{
		/*
			We calcualte what should be payed for mediation total per case is a position
		*/
		$caseID=$case['caseID'];
		writeMessageLog("Start Check ids: $lastCaseID $caseID");

		if(isset($lastCaseID) && $lastCaseID != $caseID)
		{
			$addTurnover=$lastCase['addTurnover'];
			$provisionRecord=calculateProvision($addTurnover);
			$addTurnover=	$provisionRecord['mediatorprovision'];
			$positionAmount+=$addTurnover;	
			$values="\"$caseID\",$creditingID,$positiontype,$description,$positionAmount";
			$sql = "INSERT INTO creditingposition ( `caseID`, `creditingID`, `positiontype`, `description`, `amount`) VALUES ($values)";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "insert creditingposition  sql:  $sql error: $error");
			};
			writeMessageLog("Positioamount : $positionAmount");
			$positionAmount=0;
		}		
		
		$caseAmount=($case['billType']==CREDIT)?-$case['billNet']:$case['billNet'];
		$provisionRecord=calculateProvision($caseAmount);
		$caseAmount=$provisionRecord['mediatorprovision'];
		writeMessageLog("Caseprovisoon $caseAmount");

		$mediatorType=$case['mediatorType'];	
		switch($mediatorType)
		{
			case 1:
				$description=enquote("Vermittlung durch Partnervertrag");
				break;
			case 2:
				$description=enquote("Vermittlung durch Systempartner");
				break;
		}
		$positionAmount+=$caseAmount;
		$positiontype=$mediatorType+2;
		$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
		$lastCaseID=$caseID;
		$lastCase=$case;
		$totamount+=$positionAmount;
		writeMessageLog("End Case\n\n");
	}
	if( 	$lastCase)
	{
		writeMessageLog("New Position for last  $caseID \n\n");

		$addTurnover=$lastCase['addTurnover'];
		$provisionRecord=calculateProvision($addTurnover);
		$addTurnover=	$provisionRecord['mediatorprovision'];
		$positionAmount+=$addTurnover;	
		$values="\"$caseID\",$creditingID,$positiontype,$description,$positionAmount";
		$sql = "INSERT INTO creditingposition ( `caseID`, `creditingID`, `positiontype`, `description`, `amount`) VALUES ($values)";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "insert creditingposition  sql:  $sql error: $error");
		};
		$positionAmount=0;
	}	
	$sql = "UPDATE  `crediting` SET totalNet=$totAmount WHERE id=$creditingID";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update creditin  sql:  $sql error: $error");
	};	
	
	$billnr++;
}

?>
