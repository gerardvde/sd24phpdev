<?php
function updatecase($request,$data)
{
	$screen=$data['screen'];
	$json=$data['json'];
	if (get_magic_quotes_gpc()) {
		$json = stripslashes($json);
	}
	$jsonArray=json_decode($json,true);
	if($jsonArray== NULL)
	{
		$message.='Invalid JSON<br>';
		switch (json_last_error()) {
       		 case JSON_ERROR_NONE:
           		 $message.= ' - No errors';
        			break;
        		case JSON_ERROR_DEPTH:
           		 $message.= ' - Maximum stack depth exceeded';
        			break;
        		case JSON_ERROR_STATE_MISMATCH:
            		$message.= ' - Underflow or the modes mismatch';
        			break;
        		case JSON_ERROR_CTRL_CHAR:
           		 $message.= ' - Unexpected control character found';
        			break;
       		 case JSON_ERROR_SYNTAX:
           		 $message.= ' - Syntax error, malformed JSON';
        			break;
        		case JSON_ERROR_UTF8:
            		$message.= ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        			break;
        		default:
           		 $message.= ' - Unknown error';
        			break;
   	 	}
		sendRequestError($request, $message.'JSONERROR>'.$json);
	}
	
	foreach($jsonArray  as $jsonObject)
	{
		foreach ($jsonObject as $key=>$value)
		{
			$case[$key]=$value;
		}
	}
	global $db;
	mysql_query('SET NAMES utf8', $db);
	
	updateDBCase($request,$case,$screen);
	sendRequestResult($request, 'OK');
			
}
function updateDBCase($request,$case,$screen)
{
	if (!$case) {
		sendRequestError($request, 'nocase');
	}
	if (!isset ($case['id']) || $case['id'] == "" || $case['id'] == 0) {
		sendRequestError($request, 'no rec id');
	}
	$caseID=$case['id'];
	if(!isset($screen))
	{
		updateCompleteCase($case);
		sendRequestResult($request, 'OK');
		return;
	}
	switch($screen)
	{
	
		case 'case':
		case 'cases':
			$table='case';
			updateCaseRecord($case,$table);
			$table='caseTech';
			updateCaseRecord($case['technician'],$table);
			updateCaseCosts($case['id'],$case['caseCost']);
			break;
		case 'client':
			$table='caseClient';
			updateCaseRecord($case['client'],$table);
			break;
		case 'expert':
			updateCaseExperts($case['id'],$case['experts']);
			break;
		case 'object':
			$table='case';
			updateCaseRecord($case,$table);
			$table='caseObject';
			updateCaseRecord($case['object'],$table);
			break;
		case 'task':
		case 'damage':
			$table='case';
			updateCaseRecord($case,$table);
			$table='caseDamage';
			updateCaseRecord($case['damage'],$table);
			$table='caseTask';
			updateCaseRecord($case['task'],$table);
			break;
			break;
		case 'bill':
			updateCaseBill($case['bill']);
			break;
		case 'estimate':
			updateCaseEstimate($case['estimate']);
			break;
		case 'contract':
			$table='case';
			updateCaseRecord($case,$table);
			break;
		case 'control':
			$table='caseClerk';
			if($case['clerk'])
				updateCaseRecord($case['clerk'],$table);
			else
				deleteCaseRecord($case['id'],$table);
			$table='case';
			updateCaseRecord($case,$table);
			break;
		case 'images':
			updateCaseImages($case['id'],$case['images']);
			break;
	}
	if($case['status']!=7)
	{
		deleteCaseToSend($case['caseID']);
	}
	sendRequestResult($request, 'OK');
	$dataFolder = FILEDIR.$caseID;
	if(!is_dir($dataFolder))
	{
		mkdir($dataFolder);
	}
}
function deleteCaseToSend($caseID)
{
	global $db;
	global $request;
	
	$sql = "DELETE  FROM  `caseToSend`  WHERE caseID=\"$caseID\" ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "delete: caseToSend sql:  $sql error: $error");
	};
}

function updateCompleteCase($case)
{
			$table='case';
			updateCaseRecord($case,$table);
			$table='caseTech';
			updateCaseRecord($case['technician'],$table);
			$table='caseClient';
			updateCaseRecord($case['client'],$table);
			$table='caseExpert';
			updateCaseRecord($case['expert'],$table);
			$table='caseObject';
			updateCaseRecord($case['object'],$table);
			$table='caseTask';
			updateCaseRecord($case['task'],$table);
			$table='caseDamage';
			updateCaseRecord($case['damage'],$table);
			if($case['bill'])
				updateCaseBill($case['bill']);
			if($case['estimate'])
				updateCaseEstimate($case['estimate']);
			$table='caseClerk';
			if($case['clerk'])
				updateCaseRecord($case['clerk'],$table);
			else
				deleteCaseRecord($case['id'],$table);
			updateCaseImages($case['id'],$case['images']);
			updateCaseExperts($case['id'],$case['experts']);
}
function deleteCaseRecord($caseID,$table){
	return;
	global $db;
	global $request;
	
	$sql = "DELETE  FROM  `$table`  WHERE caseID=\"$caseID\" ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "delete: $table sql:  $sql error: $error");
	};
}
function updateCaseRecord($record,$table){
	global $db;
	global $request;
	$fields=Array();
	$tabelfields=getFieldnames($table);
	foreach ($tabelfields as $tabelfield) {
	$tabfields[$tabelfield]=true;
	if(isset($record[$tabelfield])){
		$fields[]=$tabelfield;
		}
	}
	foreach ($fields as $field) {
		$col="`".$field."`";
		$columns[] = $col;
		$value = enquote($record[$field]);
		$values[] = $value;
		$set[]="$col=$value";
	}
	$recordcols = implode($columns, ',');
	$recordvals = implode($values, ',');
	$setvals = implode($set, ',');
	$new=true;
	if($tabfields['id'] )
	{
		if (!isset ($record['id']) || $record['id'] == "" || $record['id'] == 0) {
			unset ($record['id']);
		} 
		if(isset ($record['id']))
			{
				$recordID=$record['id'];
				if($table=='case')
				{
					$recordID=enquote($recordID);
				}
				$sql = "SELECT * FROM `$table`  WHERE id=$recordID AND `ignore` = 0 ";
				$sqlresult = mysql_query($sql, $db);
				if (!$sqlresult) {
					$error = mysql_error($db);
					sendRequestError($request, "get case sql:  $sql error: $error");
				}
				if(mysql_num_rows (  $sqlresult )>0)
				{
					$new=false;
				}
		}
		if($new)
			{
				$sql = "INSERT INTO  `$table` ($recordcols) VALUES ($recordvals)";
			}
		else
			{
				$sql = "UPDATE  `$table` SET $setvals WHERE id=$recordID ";
			}
		}
	else
	{
		if (!isset ($record['caseID']) || $record['caseID'] == "" || $record['caseID'] == 0) {
			unset ($record['caseID']);
		} else {
			$caseID = enquote($record['caseID']);
			$sql = "SELECT * FROM `$table`  WHERE caseID=$caseID AND `ignore` = 0 ";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "get case sql:  $sql error: $error");
			}
			if(mysql_num_rows (  $sqlresult )>0)
			{
				$new=false;
			}
		}
					
		if($new)
		{
			$sql = "INSERT INTO  `$table` ($recordcols) VALUES ($recordvals)";
		}
		else
		{
			$sql = "UPDATE  `$table` SET $setvals WHERE caseID=$caseID";
		}
	}
	
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: $table sql:  $sql error: $error");
	};
}

function updateCaseBill($record)
{
	global $request;
	global $db;
	if (!$record) {
		sendRequestError($request, 'nobill');
	}
	$services=$record['services'];
	$caseID=$record['caseID'];
	if(count($services)==0)
	{
		$recID=$record['id'];
		if(isset($recID))
		{
			$sql = "DELETE  FROM  caseBill  WHERE id=$recID ";
			$sqlresult = mysql_query($sql, $db);
			$sql = "DELETE FROM  `caseBillposition`  WHERE billID=$recID";
			$sqlresult = mysql_query($sql, $db);
		}
		return;
	};

	$tabelfields=getFieldnames("caseBill");
	foreach ($tabelfields as $tabelfield) {
	if(isset($record[$tabelfield])){
		$fields[]=$tabelfield;
		}
	}

	if(isset($record['id']) && $record['id']>0)
	{
		$recID=$record['id'];
	}
	foreach ($fields as $field) {
		$columns[] = "`".$field."`";
		$value = enquote($record[$field]);
		$values[] = $value;
	}
	
	$recordcols = implode($columns, ',');
	$recordvals = implode($values, ',');
	$sql = "REPLACE INTO  `caseBill`  ($recordcols) VALUES ($recordvals)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: $table sql:  $sql error: $error");
	};
	$idcondition="";
	if(isset($recID))
	{
		$idcondition=" AND id=$recID";
	}
	$sql = "SELECT *  FROM  caseBill  WHERE caseID=\"$caseID\" AND status=0 $idcondition";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "select sql:  $sql error: $error");
	};
	$bill = mysql_fetch_assoc($sqlresult);
	$recID=$bill['id'];
	$sql = "DELETE FROM  `caseBillposition`  WHERE billID=$recID";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "deletebillpossql:  $sql error: $error");
	};
	foreach($services as $service)
	{
		$service['billID']=$recID;
		$service['caseID']=$caseID;
		setCaseBillservice($service);
	}
	
}
function setCaseBillservice($record)
{
	global $request;
	if (!$record) {
		sendRequestError($request, 'nobillposition');
	}
	global $db;
	unset($record['id']);
    	$tabelfields=getFieldnames("caseBillposition");
	foreach ($tabelfields as $tabelfield) {
	if(isset($record[$tabelfield])){
		$fields[]=$tabelfield;
		}
	}
	
	foreach ($fields as $field) {
		$columns[] = "`".$field."`";
		$value = enquote($record[$field]);
		$values[] = $value;
	}
	
	$recordcols = implode($columns, ',');
	$recordvals = implode($values, ',');
	$sql = "INSERT INTO  `caseBillposition`  ($recordcols) VALUES ($recordvals)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: caesBillpsoition sql:  $sql error: $error");
	};
	
}
function updateCaseEstimate($record)
{
	global $request;
	global $db;
	if (!$record) {
		sendRequestError($request, 'nobill');
	}
	$services=$record['services'];
	$caseID=$record['caseID'];
	if(count($services)==0)
	{
		$recID=$record['id'];
		if(isset($recID))
		{
			$sql = "DELETE FROM  `caseEstimate`  WHERE id=$recID";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "delete estimate   $sql error: $error");
			};
			$sql = "DELETE FROM  `caseEstimateposition`  WHERE estimateID=$recID";			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "delete estimatepositions   $sql error: $error");
			};
		}
		return;
	};

	$tabelfields=getFieldnames("caseEstimate");
	foreach ($tabelfields as $tabelfield) {
	if(isset($record[$tabelfield])){
		$fields[]=$tabelfield;
		}
	}
	if(isset($record['id']) && $record['id']>0)
	{
		$recID=$record['id'];
	}
	foreach ($fields as $field) {
		$columns[] = "`".$field."`";
		$value = enquote($record[$field]);
		$values[] = $value;
	}
	
	$recordcols = implode($columns, ',');
	$recordvals = implode($values, ',');
	$sql = "REPLACE INTO  caseEstimate  ($recordcols) VALUES ($recordvals)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: $table sql:  $sql error: $error");
	};
	$idcondition="";
	if(isset($recID))
	{
		$idcondition=" AND id=$recID";
	}
	$sql = "SELECT *  FROM  caseEstimate  WHERE caseID=\"$caseID\" AND status=0 $idcondition" ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "select sql:  $sql error: $error");
	};
	$est = mysql_fetch_assoc($sqlresult);
	$recID=$est['id'];
	$sql = "DELETE FROM  `caseEstimateposition`  WHERE estimateID=$recID";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "deleteestpossql:  $sql error: $error");
	};
	foreach($services as $service)
	{
		$service['estimateID']=$recID;
		$service['caseID']=$caseID;
		setCaseEstimateservice($service);
	}
	
}
function setCaseEstimateservice($record)
{
	global $request;
	if (!$record) {
		sendRequestError($request, 'nobillposition');
	}
	global $db;
	unset($record['id']);
  	$tabelfields=getFieldnames("caseEstimateposition");
	foreach ($tabelfields as $tabelfield) {
	if(isset($record[$tabelfield])){
		$fields[]=$tabelfield;
		}
	}
	foreach ($fields as $field) {
		$columns[] = "`".$field."`";
		$value = enquote($record[$field]);
		$values[] = $value;
	}
	
	$recordcols = implode($columns, ',');
	$recordvals = implode($values, ',');
	$sql = "INSERT INTO  `caseEstimateposition`  ($recordcols) VALUES ($recordvals)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: caesEstimatepsoition sql:  $sql error: $error");
	};
	
}
function updateCaseImages($caseID,$images)
{
	global $request;
	global $db;
	$sql = "DELETE  FROM  caseImage  WHERE caseID=\"$caseID\" ";
	$sqlresult = mysql_query($sql, $db);
	if(count($images)==0)
	{
		return;
	}
	foreach($images as $image)
	{
		unset($image['id']);
		updateCaseRecord($image,"caseImage");
	}
}
function updateCaseCosts($caseID,$costs)
{
	global $request;
	global $db;
	$sql = "DELETE  FROM  caseCost  WHERE caseID=\"$caseID\" ";
	$sqlresult = mysql_query($sql, $db);
	if(count($costs)==0)
	{
		return;
	}
	foreach($costs as $cost)
	{
		unset($cost['id']);
		updateCaseRecord($cost,"caseCost");
	}
}


function updateCaseExperts($caseID,$experts)
{
	global $request;
	if (!$experts) {
		return;
	}
	global $db;
	$sql = "DELETE  FROM  caseExpert  WHERE caseID=\"$caseID\" ";
	$sqlresult = mysql_query($sql, $db);
	if(count($experts)==0)
	{
		return;
	}
	foreach($experts as $expert)
	{
		unset($expert['id']);
		updateCaseRecord($expert,"caseExpert");
	}
}

?>