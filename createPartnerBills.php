<?php
//require_once ('LWEVDBinclude.php');
//$request='createPartnerBilld';
//$data['period']="20153";
//createPartnerBills($request,$data);
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
	$feecases=getPartnerCasesAddFee($period);
	$amountcases=count($feecases);
	writeMessageLog("Total feecaserecords $amountcases");	
	$casebilltotals=getPartnerCasesProvision($period);
	$amountcases=count($casebilltotals);
	writeMessageLog("Total provisionrecords $amountcases");
	$mergedcases=Array();
	foreach ($feecases as $caseID => $feecase) {
		$casetotal=$casebilltotals[$caseID];
		if(isset($casetotal))
		{
			foreach ($casetotal  as $key => $value)
			{
				$feecase[$key]= $value;
			}				
		}
		$mergedcases[]=$feecase;	
		
	}
	
	$amountcases=count($mergedcases);
	writeMessageLog("Total caserecords $amountcases");	
	$partnercases=multiSort($mergedcases,'partnerID','caseID' );
	
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
		if(isset($case['mediatorID']) && $case['status']>=11 )
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
	$date=enquote(date("Ymd"));
	$description="\"Leistungn\"";
	$period=enquote($period);
	$brand=enquote($record['brand']);
	$name=enquote($record['name']);
	$street=enquote($record['street']." ".$record['nr']);
	$zip=enquote($record['zip']);
	$town=enquote($record['town']);
	$directdebit=$record['directdebit'];
	$totalnet=0;
	$vat=19;
	//TODO VAT
	$values="$partnerID,$period,$billnr,$brand,$name,$description,$street,$zip,$town,$totalnet,$vat,$date,$directdebit";
	$sql = "INSERT INTO partnerbill ( `partnerID`, `period`, `billnr`, `brand`, `name`, `description`, `street`, `zip`, `town`, `totalNet`, `vat`, `date`, `directdebit`) VALUES ($values)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partner  sql:  $sql error: $error");
	};
	$partnerbillID=mysql_insert_id ();
	
	$sql = "DELETE FROM partnerBillposition WHERE  partnerbillID=$partnerbillID";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "delete partnerbillposition  sql:  $sql error: $error");
		};
	$totAmount=0;
	$amountForProvison=0;
	$lastCaseID;
	$lastCase;
	$caseFees=0;
	$extracaseFees=0;
	//{"caseID":"14F17FE52A6","partnerID":"4","addTurnover":"1000","extraCost":"55","mediatorType":"1","mediatorID":"7","totalNet":275}

	foreach($cases as $case)
	{

		$caseID=$case['caseID'];
		$status=$case['status'];
		$json=json_encode($case);
		if(isset($lastCaseID) && $lastCaseID!=$caseID)
		{
			//Casefees 
			
			$positiontype=1;
			$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
			$values="\"$lastCaseID\",$partnerbillID,$positiontype,$description,$caseFees";
			$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "get partner  sql:  $sql error: $error");
			};	
			
			//and case extracost
			if($extracaseFees>0 && $status>=11 )
			{
				$positiontype=2;
				$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
				$values="\"$lastCaseID\",$partnerbillID,$positiontype,$description,$extracaseFees";
				$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
				$sqlresult = mysql_query($sql, $db);
				if (!$sqlresult) {
					$error = mysql_error($db);
					sendRequestError($request, "get partner  sql:  $sql error: $error");
				};
				writeMessageLog("ExtraCasefeesposition update $sql");
				$totAmount+=$extracaseFees;
			}
			$caseFees=0;
			$extracaseFees=0;
		}
		
		$mediatorType=$case['mediatorType'];
		
		if(isset($mediatorType) && $mediatorType>0  && $status>=11)
		{
			$positiontype=$mediatorType+2;
			$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
			$totalBills=(isset($case['totalNet']))?$case['totalNet']:0;
			$addTurnover=(isset($case['addTurnover']))?$case['addTurnover']:0;
			$amountForProvison=$addTurnover+$totalBills;
			if($amountForProvison>0)
			{
				
				$provisionRecord=calculateProvision($amountForProvison);
				$amount=$provisionRecord['mediatorprovision']+$provisionRecord['centralprovision'];
				$values="\"$lastCaseID\",$partnerbillID,$positiontype,$description,$amount";
				$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
				$sqlresult = mysql_query($sql, $db);
				if (!$sqlresult) {
					$error = mysql_error($db);
					sendRequestError($request, "get partner  sql:  $sql error: $error");
				};
				writeMessageLog("Totalnet $totalBills addTurnover $addTurnover provison $amount");
				writeMessageLog("Caseprovision position update $sql");
				$totAmount+=$amount;
			}
		
		}		
		$extrafee=(isset($case['extraCost']))?$case['extraCost']:0;
		$lastCaseID=$caseID;
		$lastCase=$case;
		$caseFees+=CREATION_FEE;
		$extracaseFees+=$extrafee;
		$totAmount+=CREATION_FEE;	
		$totAmount+=$extrafee;		
	}//End LOOP

	if($lastCase)
	{
		$positiontype=1;
		$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
		$values="\"$lastCaseID\",$partnerbillID,$positiontype,$description,$caseFees";
		$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "get partner  sql:  $sql error: $error");
		};	
		//and case extracost
		if($extracaseFees>0 &&  $status>=11)
		{
			$positiontype=2;
			$description=enquote(	$GLOBALS['POSITION_TYPE'][$positiontype]);
			$values="\"$lastCaseID\",$partnerbillID,$positiontype,$description,$extracaseFees";
			$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "get partner  sql:  $sql error: $error");
			};
		}		
	}
	
	$sql = "UPDATE  `partnerbill` SET totalNet=$totAmount WHERE id=$partnerbillID";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update partnerbill  sql:  $sql error: $error");
	};	
	writeMessageLog("Partnerbill update $sql");
	$billnr++;
}

function getPartnerCasesAddFee($period)
{
	global $db;
	global $request;
	$like=createSQLLikeQuart($period,'case.changedate');
		$sql = "SELECT case.id as caseID, case.partnerID as partnerID,case.addTurnover as addTurnover, caseCost.price as extraCost , case.status as status 
		FROM   `case` LEFT JOIN `caseCost` ON   caseCost.caseID LIKE case.id AND caseCost.caseCostID=2  
		WHERE case.ignore = 0   AND   $like ORDER BY partnerID";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "get case  sql:  $sql error: $error");
		};
		
		$nrOfRecords=mysql_num_rows (  $sqlresult );
		 $records=array();
		while ($record = mysql_fetch_assoc($sqlresult))
			{
				foreach ($record  as $key => $value) {
					$record [$key] = urlencode(($value));
				}
				$caseID=$record['caseID'];
				$records[$caseID]=$record;
			}
			return $records;	
}
function getPartnerCasesProvision($period)
{
	global $db;
	global $request;
	$like=createSQLLikeQuart($period,'case.changedate');
	$sql = "SELECT case.id as caseID, case.mediatorType as mediatorType, case.mediatorID as mediatorID, 
			caseBill.totalNet as billNet, caseBill.id as billID, caseBill.type as billType			FROM   `case` INNER JOIN `caseBill` ON  caseBill.caseID LIKE case.id  AND caseBill.totalNet>0 AND caseBill.ignore=0 AND caseBill.status>=9 
				WHERE case.mediatorID>0  AND case.status = 11 AND case.ignore = 0   AND   $like ORDER BY caseID";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get case  sql:  $sql error: $error");
	};
	writeMessageLog($sql);
	$nrOfRecords=mysql_num_rows (  $sqlresult );
	 $records=array();
	 $lastCaseID;
	 $lastCase;
	 $amount=0;
	while ($case = mysql_fetch_assoc($sqlresult))
		{
			foreach ($case  as $key => $value) {
				$case [$key] = urlencode(($value));
		}
		$caseID=$case['caseID'];
		if($lastCaseID && $lastCaseID!=$caseID)
		{
			$case['totalNet']=$amount;
			unset($case['billType']);
			unset($case['billID']);
			unset($case['billNet']);
			$records[$caseID]=$case;
			 $amount=0;
		}
		if($case['billType']==CREDIT)
		{
			$amount-=$case['billNet'];
		}
		else
		{
			$amount+=$case['billNet'];
		}
		$lastCaseID=$caseID;
	}
	if($case)
	{
		$case['totalNet']=$amount;
		unset($case['billType']);
		unset($case['billID']);
		unset($case['billNet']);
		$records[$caseID]=$case;
	}
	$json=json_encode( $records);
	return $records;	
}
// Help functions
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
function calculateProvision($totamount)
{
	foreach($GLOBALS['PROVISION_TABLE'] as $provisionrecord)
	{
		$record=$provisionrecord;
		if($totamount<$provisionrecord['max'])
		{
			break;
		}
		
	}
	return $record;
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
			 $mediatortype[$record['id']]=$record['label'];
		}

		$GLOBALS['PROVISION_TABLE'] = $provisionrecords;
		$GLOBALS['POSITION_TYPE'] = $positiontype;
		$GLOBALS['MEDIATOR_TYPE'] = $mediatortype;
		
}
?>
