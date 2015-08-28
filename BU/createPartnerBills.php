<?php
function createPartnerBills($request,$data)
{
	require_once ('createCreditings.php');
	global $billnr;
	global $request;
	
	$period=$data['period'];
	if(!isset($period))
	{
		sendRequestError($request, "no valid period");
	}
	createBaseTables();
	emptyTables();
	$partnercases=array_merge( getPartnerCasesCreateFee($period),getPartnerCasesAddFee($period),getPartnerCasesProvision($period) );
	$amountcases=count($partnercases);
	
	$partnercases=multiSort($partnercases,'partnerID','caseID','type'  );
	
	$billnr=getMaxBillNr();
	if(isset($billnr) &&is_numeric($billnr))
	{
		$billnr++;
	}
	else
	{
		$billnr=DEFAULT_BILLNR;
	}
	$startbillnr=$billnr;
	$oldpartnerID;
	foreach($partnercases as $case)
	{
		$partnerID=$case['partnerID'];
		if( $partnerID!=$oldpartnerID)
		{
			if(isset($oldpartnerID) )
			{
				createPartnerBill($period,$cases,$oldpartnerID);
			}
			$cases=array();
			$oldpartnerID=$partnerID;
		}
		$cases[]=$case;
		if($case['type']==3)
		{
			$mediatorType=$case['mediatorType'];
			if(isset($mediatorType) && $mediatorType>0 && $mediatorType<3)//No Central
			{	
				$creditingcases[]=$case;
			}
		}
	}
	if(isset($oldpartnerID) )
	{
		createPartnerBill($period,$cases,$oldpartnerID);
	}
	$totalbill=$billnr-$startbillnr-1;
	
	/*
		CREATE crediting
					
	*/
	$creditingcases=multiSort($creditingcases,'mediatorType','mediatorID','caseID');
	createCreditings($period,$creditingcases);
	$totalcrediting=$billnr-$startbillnr-$totalbill-1;
	if($totalbill<0)
	{
		$totalbill=0;
	}
	$result="$totalbill Rechnungen und $totalcrediting Gutschriften erstellt für $period";
	writeMessageLog($result);
	saveMessageLog();
	sendRequestResult($request, $result);	
}

//
function getMaxBillNr()
{
	global $db;
	global $request;
	$sql = "SELECT MAX(billnr)  FROM   `partnerbill`  WHERE `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partnerbill  sql:  $sql error: $error");
	};
	$record = mysql_fetch_assoc($sqlresult);
	$maxbill=$record['MAX(billnr)'];
	$sql = "SELECT MAX(billnr)  FROM   `crediting`  WHERE `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get crediting  sql:  $sql error: $error");
	};
	$credrecord = mysql_fetch_assoc($sqlresult);
	$maxcred=$credrecord['MAX(billnr)'];
	if(isset($maxbill) &&is_numeric($maxbill))
	{
		if(isset($maxcred) &&is_numeric($maxcred))
		{
			if($maxbill>$maxcred)
			{
				return $maxbill;
			}
			return $maxcred;
		}
		return $maxbill;
	}
	return ;
}
function createSQLLikeQuart($period,$date)
{

	$year=substr($period,0,4);
	$quart=substr($period,4,1);
	$m1=1+(($quart-1)*3);
	$m2=$m1+1;
	$m3=$m1+2;
	
	while(strlen($m1)<2)
	{
		$m1='0'.$m1;
	}
	while(strlen($m2)<2)
	{
		$m2='0'.$m2;
	}
	while(strlen($m3)<2)
	{
		$m3='0'.$m3;
	}
	$like="($date LIKE \"$year$m1%\" OR $date LIKE \"$year$m2%\" OR $date LIKE \"$year$m3%\")";
	return $like;

}

function createPartnerBill($period,$cases,$partnerID)
{
	global $db;
	global $billnr;
	global $request;
	//Check if partner bill exists, we cannot rewrite ist!!
	//echo "start create $period $partnerID<br>";
	$sql = "SELECT period  FROM   `partnerbill`  WHERE period LIKE \"$period\" AND partnerID=$partnerID AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseBill  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )>0)
	{
		return ;
	}
  
	$sql = "SELECT brand,name,street,nr,zip, town,directdebit  FROM   `partner`  WHERE  id=$partnerID ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partner  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )==0)
	{
		//no valid partner!
		return ;
	}
	$record = mysql_fetch_assoc($sqlresult);
	$totalnet=0;
	foreach($cases as $case)
	{
		$totalnet+=8;
		if(isset($case['extraCost']) && $case['extraCost']>0)
		{
			$totalnet+=$case['extraCost'];
		}
	}
	$vat=19;
	$date=enquote(date("Ymd"));
	$description="\"Leistungen\"";
	$period=enquote($period);
	$brand=enquote($record['brand']);
	$name=enquote($record['name']);
	$street=enquote($record['street']." ".$record['nr']);
	$zip=enquote($record['zip']);
	$town=enquote($record['town']);
	$directdebit=$record['directdebit'];
	$values="$partnerID,$period,$billnr,$brand,$name,$description,$street,$zip,$town,$totalnet,$vat,$date,$directdebit";
	$sql = "INSERT INTO partnerbill ( `partnerID`, `period`, `billnr`, `brand`, `name`, `description`, `street`, `zip`, `town`, `totalNet`, `vat`, `date`, `directdebit`) VALUES ($values)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partner  sql:  $sql error: $error");
	};
	$billid=mysql_insert_id ();
	
	$sql = "DELETE FROM partnerBillposition WHERE  partnerbillID=$billid";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "delete partnerbillposition  sql:  $sql error: $error");
		};
	$totAmount=0;
	$amountForProvison=0;
	$lastCaseID;
	$lastCase;
	foreach($cases as $case)
	{
		if(!$case['type'] || $case['type']==0 || $case['type']>3)continue;
		
		if(isset($lastCaseID) && $lastCaseID!=$caseID)
		{
			$addTurnover=$lastCase['addTurnover'];
			if($amountForProvison>0  ||  $addTurnover>0)
			{
				writeMessageLog("New partnerPosition for provision for  $lastCaseID \n\n");
				$positiontype=$mediatorType+2;
				$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
				$amountForProvison+=$addTurnover;
				$provisionRecord=calculateProvision($caseamount);
				$amount=$provisionRecord['mediatorprovision']+$provisionRecord['centralprovision'];
				$values="\"$lastCaseID\",$partnerbillID,$positiontype,$description,$amount";
				$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
				$sqlresult = mysql_query($sql, $db);
				if (!$sqlresult) {
					$error = mysql_error($db);
					sendRequestError($request, "get partner  sql:  $sql error: $error");
				};	
				$amountForProvison=0;
			}
		}
		$recordType=$case['type'];
		$caseID=$case['caseID'];
		$mediatorType=$case['mediatorType'];
		$partnerbillID=$billid;
		switch($recordType)
		{
			case CASE_FEE:
				$positiontype=1;
				$description=enquote($GLOBALS['POSITION_TYPE'][$positiontype]);
				$amount=CREATION_FEE;
				$values="\"$caseID\",$partnerbillID,$positiontype,$description,$amount";
				$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
				$sqlresult = mysql_query($sql, $db);
				if (!$sqlresult) {
					$error = mysql_error($db);
					sendRequestError($request, "get partner  sql:  $sql error: $error");
				};	
				break;
			case ADD_FEE:
				$extraCost=$case['extraCost'];
				if(isset($extraCost)&& is_numeric($extraCost))
				{
					$positiontype=2;
					$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
					$amount=$case['extraCost'];
					$values="\"$caseID\",$partnerbillID,$positiontype,$description,$amount";
					$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
					$sqlresult = mysql_query($sql, $db);
					if (!$sqlresult) {
						$error = mysql_error($db);
						sendRequestError($request, "get partner  sql:  $sql error: $error");
					};	
				}
				break;
			case CASE_PROVISION:
				if(isset($mediatorType) && $mediatorType>0)
				{
					if($case['billType']!=CREDIT)
					{
						$amountForProvison+=$case['billNet'];
						
					}
				}
				break;
		}
		$lastCaseID=$caseID;
		$lastCase=$case;
		$totAmount+=$caseAmount;
		
	}	
	if($lastCase)
	{

		$addTurnover=$lastCase['addTurnover'];
		if(  $addTurnover>0)
		{
			writeMessageLog("New partnerPosition for provision for  $lastCaseID \n\n");
			$positiontype=$mediatorType+2;
			$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
			$amountForProvison=$addTurnover;
			$provisionRecord=calculateProvision($caseamount);
			$amount=$provisionRecord['mediatorprovision']+$provisionRecord['centralprovision'];
			$values="\"$lastCaseID\",$partnerbillID,$positiontype,$description,$amount";
			$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "get partner  sql:  $sql error: $error");
			};	
		}
		switch($recordType)
		{
			case CASE_FEE:
				$positiontype=1;
				$description=enquote($GLOBALS['POSITION_TYPE'][$positiontype]);
				$amount=CREATION_FEE;
				$values="\"$caseID\",$partnerbillID,$positiontype,$description,$amount";
				$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
				$sqlresult = mysql_query($sql, $db);
				if (!$sqlresult) {
					$error = mysql_error($db);
					sendRequestError($request, "get partner  sql:  $sql error: $error");
				};	
				break;
			case ADD_FEE:
				$extraCost=$case['extraCost'];
				if(isset($extraCost)&& is_numeric($extraCost))
				{
					$positiontype=2;
					$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
					$amount=$case['extraCost'];
					$values="\"$caseID\",$partnerbillID,$positiontype,$description,$amount";
					$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
					$sqlresult = mysql_query($sql, $db);
					if (!$sqlresult) {
						$error = mysql_error($db);
						sendRequestError($request, "get partner  sql:  $sql error: $error");
					};	
				}
				break;
			case CASE_PROVISION:
				if(isset($mediatorType) && $mediatorType>0)
				{
					if($case['billType']!=CREDIT)
					{
						$amountForProvison+=$case['billNet'];
						
					}
				}
				break;
		}
	
	}
	
	$sql = "UPDATE  `partnerbill` SET totalNet=$totAmount WHERE id=$partnerbillID";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update partnerbill  sql:  $sql error: $error");
	};	
	$billnr++;
}

function calculateProvision($totamount)
{
	foreach($GLOBALS['PROVISION_TABLE'] as $provisionrecord)
	{
		if($totamount<$provisionrecord['max'])
		{
			break;
		}
		$record=$provisionrecord;
	}
	if(!isset($record))
	{
		$record=$provisionrecord;
	}
	return $record;
}

function getPartnerCasesCreateFee($period)
{
	global $request;
	global $db;
	$like=createSQLLikeQuart($period,'date');
		$sql = "SELECT id as caseID, partnerID as partnerID FROM `case` WHERE `ignore`  = 0   AND   $like ORDER BY partnerID";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "get case  sql:  $sql error: $error");
		};
		$records=array();
		while ($record = mysql_fetch_assoc($sqlresult))
			{
				foreach ($record  as $key => $value) {
					$record [$key] = urlencode(($value));
				}
				$record['caseFee']=CREATION_FEE;
				$record['type']=CASE_FEE;
				 $records[]=$record;
			}
		return $records;	
}
function getPartnerCasesAddFee($period)
{
	global $db;
	global $request;
	$like=createSQLLikeQuart($period,'case.changedate');
		$sql = "SELECT case.id as caseID, case.partnerID as partnerID, caseCost.price as extraCost 
		FROM   `case` LEFT JOIN `caseCost` ON   caseCost.caseID LIKE case.id AND caseCost.caseCostID=2  
		WHERE caseCost.price>0 AND case.status = 11 AND case.ignore = 0   AND   $like ORDER BY partnerID";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "get case  sql:  $sql error: $error");
		};
		 $records=array();
		while ($record = mysql_fetch_assoc($sqlresult))
			{
				foreach ($record  as $key => $value) {
					$record [$key] = urlencode(($value));
				}
				$record['type']=ADD_FEE;
				 $records[]=$record;
			}

			return $records;	
}
function getPartnerCasesProvision($period)
{
	global $db;
	global $request;
	$like=createSQLLikeQuart($period,'case.changedate');
	$sql = "SELECT case.id as caseID, case.partnerID as partnerID,case.mediatorType as mediatorType, case.mediatorID as mediatorID, 
		caseBill.totalNet as billNet, caseBill.id as billID, caseBill.type as billType,case.addTurnover as addTurnover 
		FROM   `case` LEFT JOIN `caseBill` ON  caseBill.caseID LIKE case.id AND caseBill.ignore=0 AND caseBill.status>=9 
		WHERE case.mediatorID>0 AND ( caseBill.totalNet >0 OR case.addTurnover>0 ) AND case.status = 11 AND case.ignore = 0   AND   $like ORDER BY partnerID";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get case  sql:  $sql error: $error");
	};
	 $records=array();
	while ($record = mysql_fetch_assoc($sqlresult))
		{
			foreach ($record  as $key => $value) {
				$record [$key] = urlencode(($value));
			}
		$record['type']=CASE_PROVISION;
		$records[]=$record;
		}
	$json=json_encode( $records);
	return $records;	
}
function emptyTables()
{
	global $db;
	global $request;
	$sql="TRUNCATE TABLE `partnerbill`";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "truncate partnerbill  sql:  $sql error: $error");
	};
	$sql="TRUNCATE TABLE `partnerBillposition`";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "truncate partnerBillposition  sql:  $sql error: $error");
	};
	$sql="TRUNCATE TABLE `crediting`";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "truncate crediting  sql:  $sql error: $error");
	};
	$sql="TRUNCATE TABLE `creditingposition`";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "truncate creditingposition  sql:  $sql error: $error");
	};
}

function createBaseTables()
{
	global $db;
	global $request;
	$sql = "SELECT *
			FROM `caseprovision`
			WHERE `ignore` =0
			ORDER BY `max`";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseprovision  sql:  $sql error: $error");
		};
	while ($record = mysql_fetch_assoc($sqlresult))
		{
			foreach ($record  as $key => $value) {
				$record [$key] = urlencode(($value));
			}
			 $provisionrecords[]=$record;
		}
		
	$sql = "SELECT *
			FROM `partnerbillpositiontype`
			WHERE `ignore` =0
			ORDER BY `id`";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partnerbillpositiontype  sql:  $sql error: $error");
		};
	while ($record = mysql_fetch_assoc($sqlresult))
		{
			foreach ($record  as $key => $value) {
				$record [$key] = urlencode(($value));
			}
			 $positiontype[$record['id']]=$record['description'];
		}
	$sql = "SELECT *
			FROM `mediatorType`
			WHERE `ignore` =0
			ORDER BY `id`";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "mediatorType  sql:  $sql error: $error");
		};
	while ($record = mysql_fetch_assoc($sqlresult))
		{
			foreach ($record  as $key => $value) {
				$record [$key] = urlencode(($value));
			}
			 $mediatortype[$record['id']]=$record['label'];
		}

		$GLOBALS['PROVISION_TABLE'] = $provisionrecords;
		$GLOBALS['POSITION_TYPE'] = $positiontype;
		$GLOBALS['MEDIATOR_TYPE'] = $mediatortype;
		
}
?>
